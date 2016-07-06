<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Data;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleDivisionSubject;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleSubjectGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Service extends AbstractService
{

    const PREG_MATCH_DECIMAL_NUMBER = '!^[0-9]+((\.|,)[0-9]+)?$!is';

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $GradeType
     *
     * @return IFormInterface|string
     */
    public function createGradeType(IFormInterface $Stage = null, $GradeType)
    {

        /**
         * Skip to Frontend
         */
        if (null === $GradeType) {
            return $Stage;
        }

        $Error = false;
        if (isset($GradeType['Name']) && empty($GradeType['Name'])) {
            $Stage->setError('GradeType[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($GradeType['Code']) && empty($GradeType['Code'])) {
            $Stage->setError('GradeType[Code]', 'Bitte geben Sie eine Abk&uuml;rzung an');
            $Error = true;
        }
        if (!($tblTestType = Evaluation::useService()->getTestTypeById($GradeType['Type']))) {
            $Stage->setError('GradeType[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createGradeType(
                $GradeType['Name'],
                $GradeType['Code'],
                $GradeType['Description'],
                isset($GradeType['IsHighlighted']) ? true : false,
                $tblTestType
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Der Zensuren-Typ ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $Id
     * @param                     $GradeType
     *
     * @return IFormInterface|string
     */
    public function updateGradeType(IFormInterface $Stage = null, $Id, $GradeType)
    {

        /**
         * Skip to Frontend
         */
        if (null === $GradeType || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($GradeType['Name']) && empty($GradeType['Name'])) {
            $Stage->setError('GradeType[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($GradeType['Code']) && empty($GradeType['Code'])) {
            $Stage->setError('GradeType[Code]', 'Bitte geben sie eine Abkürzung an');
            $Error = true;
        }

        $tblGradeType = $this->getGradeTypeById($Id);
        if (!$tblGradeType) {
            return new Danger(new Ban() . ' Zensuren-Typ nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateGradeType(
                $tblGradeType,
                $GradeType['Name'],
                $GradeType['Code'],
                $GradeType['Description'],
                isset($GradeType['IsHighlighted']) ? true : false,
                Evaluation::useService()->getTestTypeById($GradeType['Type'])
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Der Zensuren-Typ ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|Service\Entity\TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeTypeById($Id);
    }

    /**
     * @param string $Code
     *
     * @return bool|Service\Entity\TblGradeType
     */
    public function getGradeTypeByCode($Code)
    {

        return (new Data($this->getBinding()))->getGradeTypeByCode($Code);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Select
     * @param string $BasicRoute
     *
     * @return IFormInterface|Redirect
     */
    public function getGradeBook(IFormInterface $Stage = null, $DivisionSubjectId = null, $Select = null, $BasicRoute)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select || $DivisionSubjectId === null) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Select['ScoreCondition'])) {
            $Error = true;
            $Stage .= new Warning(new Ban() . ' Berechnungsvorschrift nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Select['ScoreCondition']);

        return new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS, array(
            'DivisionSubjectId' => $DivisionSubjectId,
            'ScoreConditionId' => $tblScoreCondition->getId()
        ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreCondition
     */
    public function getScoreConditionById($Id)
    {

        return (new Data($this->getBinding()))->getScoreConditionById($Id);
    }

    /**
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAllWhereTestOrBehavior()
    {

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        if (!$tblTestType || !($tblGradeTypeAllTest = $this->getGradeTypeAllByTestType($tblTestType))) {
            $tblGradeTypeAllTest = array();
        }

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        if (!$tblTestType || !($tblGradeTypeAllBehavior = $this->getGradeTypeAllByTestType($tblTestType))) {
            $tblGradeTypeAllBehavior = array();
        }

        $tblGradeTypeAll = array_merge($tblGradeTypeAllTest, $tblGradeTypeAllBehavior);

        return (empty($tblGradeTypeAll) ? false : $tblGradeTypeAll);
    }

    /**
     * @param TblTestType $tblTestType
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getGradeTypeAllByTestType($tblTestType);
    }

    /**
     * @param $Id
     *
     * @return bool|Service\Entity\TblGrade
     */
    public function getGradeById($Id)
    {

        return (new Data($this->getBinding()))->getGradeById($Id);
    }

    /**
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest $tblTest
     *
     * @return TblGrade[]|bool
     */
    public function getGradeAllByTest(TblTest $tblTest)
    {

        return (new Data($this->getBinding()))->getGradeAllByTest($tblTest);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return bool|float
     */
    public function getAverageByTest(TblTest $tblTest)
    {

        $tblDivision = $tblTest->getServiceTblDivision();
        $tblSubject = $tblTest->getServiceTblSubject();
        if ($tblDivision && $tblSubject) {
            $tblScoreType = $this->getScoreTypeByDivisionAndSubject(
                $tblDivision,
                $tblSubject
            );

            if ($tblScoreType && $tblScoreType->getIdentifier() !== 'VERBAL' ){
                $tblGradeList = $this->getGradeAllByTest($tblTest);
                if ($tblGradeList){
                    $sum = 0;
                    $count = 0;
                    foreach ($tblGradeList as $tblGrade){
                        if ($tblGrade->getGrade() && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                            $sum += floatval($tblGrade->getGrade());
                            $count++;
                        }
                    }

                    if ($count > 0){
                        return round($sum / $count, 2);
                    }
                }
            }
        }

        return false;

    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $TestId
     * @param null $Grade
     * @param $BasicRoute
     * @param TblScoreType|null $tblScoreType
     *
     * @return IFormInterface|string
     */
    public function updateGradeToTest(
        IFormInterface $Stage = null,
        $TestId = null,
        $Grade = null,
        $BasicRoute,
        TblScoreType $tblScoreType = null
    ) {

        /**
         * Skip to Frontend
         */
        if ($TestId === null) {
            return $Stage;
        }
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Stage;
        }

        $tblTest = Evaluation::useService()->getTestById($TestId);

        $errorRange = false;
        // check if grade has pattern
        if (!empty($Grade) && $tblScoreType && $tblScoreType->getPattern() !== '') {
            foreach ($Grade as $personId => $value) {
                $gradeValue = str_replace(',', '.', trim($value['Grade']));
                if (!isset($value['Attendance']) && $gradeValue !== '' ) {
                    if (!preg_match('!' . $tblScoreType->getPattern() . '!is', $gradeValue)) {
                        $errorRange = true;
                        break;
                    }
                }
            }
        }

        // grund bei Noten-Änderung angeben
        $errorEdit = false;
        $errorNoGrade = false;
        if (!empty($Grade)) {
            foreach ($Grade as $personId => $value) {
                $gradeValue = str_replace(',', '.', trim($value['Grade']));
                $tblPerson = Person::useService()->getPersonById($personId);
                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                if ($tblGrade && empty($value['Comment']) && !isset($value['Attendance'])
                    && ($gradeValue != $tblGrade->getGrade()
                        || (isset($value['Trend']) && $value['Trend'] != $tblGrade->getTrend()))
                ) {
                    $errorEdit = true;
                }
                if ($tblGrade && !isset($value['Attendance']) && $gradeValue === ''){
                    $errorNoGrade = true;
                }
            }
        }

        if ($errorRange || $errorEdit || $errorNoGrade) {
            if ($errorRange) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Nicht alle eingebenen Zensuren befinden sich im Wertebereich.
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                    ))));
            }
            if ($errorEdit) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Bei den Notenänderungen wurde nicht in jedem Fall ein Grund angegeben.
                             Die Daten wurden nicht gespeichert.', new Exclamation())
                    ))));
            }
            if ($errorNoGrade) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Bereits eingetragene Zensuren können nur über "Nicht teilgenommen" entfernt werden.
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                    ))));
            }

            return $Stage;
        }


        if (!empty($Grade)) {
            foreach ($Grade as $personId => $value) {
                $tblPerson = Person::useService()->getPersonById($personId);

                if ($tblTest->getServiceTblDivision() && $tblTest->getServiceTblSubject()) {

                    // set trend
                    if (isset($value['Trend'])) {
                        $trend = $value['Trend'];
                    } else {
                        $trend = 0;
                    }

                    $grade = str_replace(',', '.', trim($value['Grade']));

                    if (!($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson))) {
                        if (isset($value['Attendance'])) {
                            (new Data($this->getBinding()))->createGrade(
                                $tblPerson,
                                $tblTest->getServiceTblDivision(),
                                $tblTest->getServiceTblSubject(),
                                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null,
                                $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod() : null,
                                $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType() : null,
                                $tblTest,
                                $tblTest->getTblTestType(),
                                null,
                                trim($value['Comment']),
                                $trend
                            );
                        } elseif (trim($value['Grade']) !== '') {
                            (new Data($this->getBinding()))->createGrade(
                                $tblPerson,
                                $tblTest->getServiceTblDivision(),
                                $tblTest->getServiceTblSubject(),
                                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null,
                                $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod() : null,
                                $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType() : null,
                                $tblTest,
                                $tblTest->getTblTestType(),
                                $grade,
                                trim($value['Comment']),
                                $trend
                            );
                        }
                    } elseif ($tblGrade) {

                        if (isset($value['Attendance'])) {
                            (new Data($this->getBinding()))->updateGrade(
                                $tblGrade,
                                null,
                                trim($value['Comment']),
                                $trend
                            );
                        } else {
                            (new Data($this->getBinding()))->updateGrade(
                                $tblGrade,
                                $grade,
                                trim($value['Comment']),
                                $trend
                            );
                        }
                    }
                }
            }
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect($BasicRoute . '/Grade/Edit', Redirect::TIMEOUT_SUCCESS,
            array('Id' => $tblTest->getId()));
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     *
     * @return bool|TblGrade
     */
    public function getGradeByTestAndStudent(
        TblTest $tblTest,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getGradeByTestAndStudent($tblTest, $tblPerson);
    }

    /**
     * @param TblGrade $tblGrade
     *
     * @return bool
     */
    public function destroyGrade(TblGrade $tblGrade)
    {

        return (new Data($this->getBinding()))->destroyGrade($tblGrade);
    }

    /**
     * @return bool|TblScoreGroup[]
     */
    public function getScoreGroupAll()
    {

        return (new Data($this->getBinding()))->getScoreGroupAll();
    }

    /**
     * @return bool|TblScoreCondition[]
     */
    public function getScoreConditionAll()
    {

        return (new Data($this->getBinding()))->getScoreConditionAll();
    }

    /**
     * @return bool|TblScoreRule[]
     */
    public function getScoreRuleAll()
    {

        return (new Data($this->getBinding()))->getScoreRuleAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreRuleConditionList
     */
    public function getScoreRuleConditionListById($Id)
    {

        return (new Data($this->getBinding()))->getScoreRuleConditionListById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGradeTypeList
     */
    public function getScoreConditionGradeTypeListById($Id)
    {

        return (new Data($this->getBinding()))->getScoreConditionGradeTypeListById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGroupList
     */
    public function getScoreConditionGroupListById($Id)
    {

        return (new Data($this->getBinding()))->getScoreConditionGroupListById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreGroupGradeTypeList
     */
    public function getScoreGroupGradeTypeListById($Id)
    {

        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $ScoreCondition
     *
     * @return IFormInterface|string
     */
    public function createScoreCondition(IFormInterface $Stage = null, $ScoreCondition = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreCondition) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreCondition['Name']) && empty($ScoreCondition['Name'])) {
            $Stage->setError('ScoreCondition[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        if ($ScoreCondition['Priority'] == '') {
            $priority = 1;
        } else {
            $priority = $ScoreCondition['Priority'];
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreCondition(
                $ScoreCondition['Name'],
                isset($ScoreCondition['Round']) ? $ScoreCondition['Round'] : '',
                $priority
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvariante ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Condition', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $ScoreGroup
     *
     * @return IFormInterface|string
     */
    public function createScoreGroup(IFormInterface $Stage = null, $ScoreGroup = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreGroup) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreGroup['Name']) && empty($ScoreGroup['Name'])) {
            $Stage->setError('ScoreGroup[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($ScoreGroup['Multiplier']) && empty($ScoreGroup['Multiplier'])) {
            $Stage->setError('ScoreGroup[Multiplier]', 'Bitte geben sie einen Faktor an');
            $Error = true;
        } elseif (isset($ScoreGroup['Multiplier']) && !preg_match(self::PREG_MATCH_DECIMAL_NUMBER, $ScoreGroup['Multiplier'])) {
            $Stage->setError('ScoreGroup[Multiplier]', 'Bitte geben sie eine Zahl als Faktor an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreGroup(
                $ScoreGroup['Name'],
                isset($ScoreGroup['Round']) ? $ScoreGroup['Round'] : '',
                $ScoreGroup['Multiplier']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zensuren-Gruppe ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreGroup $tblScoreGroup
     * @param               $Multiplier
     *
     * @return TblScoreGroupGradeTypeList
     */
    public function addScoreGroupGradeTypeList(
        TblGradeType $tblGradeType,
        TblScoreGroup $tblScoreGroup,
        $Multiplier
    ) {

        if ((new Data($this->getBinding()))->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup, $Multiplier)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreGroup->getId()));
        }
    }

    /**
     * @param TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList
     *
     * @return string
     */
    public function removeScoreGroupGradeTypeList(
        TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList
    ) {

        $tblScoreGroup = $tblScoreGroupGradeTypeList->getTblScoreGroup();
        if ((new Data($this->getBinding()))->removeScoreGroupGradeTypeList($tblScoreGroupGradeTypeList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreGroup->getId()));
        }
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return string
     */
    public function addScoreConditionGroupList(
        TblScoreCondition $tblScoreCondition,
        TblScoreGroup $tblScoreGroup
    ) {

        if ((new Data($this->getBinding()))->addScoreConditionGroupList($tblScoreCondition, $tblScoreGroup)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Group/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger('Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Group/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreConditionGroupList $tblScoreConditionGroupList
     *
     * @return string
     */
    public function removeScoreConditionGroupList(
        TblScoreConditionGroupList $tblScoreConditionGroupList
    ) {

        $tblScoreCondition = $tblScoreConditionGroupList->getTblScoreCondition();
        if ((new Data($this->getBinding()))->removeScoreConditionGroupList($tblScoreConditionGroupList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Group/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Group/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return TblScoreConditionGradeTypeList
     */
    public function addScoreConditionGradeTypeList(
        TblGradeType $tblGradeType,
        TblScoreCondition $tblScoreCondition
    ) {

        if ((new Data($this->getBinding()))->addScoreConditionGradeTypeList($tblGradeType, $tblScoreCondition)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreConditionGradeTypeList $tblScoreConditionGradeTypeList
     *
     * @return string
     */
    public function removeScoreConditionGradeTypeList(
        TblScoreConditionGradeTypeList $tblScoreConditionGradeTypeList
    ) {

        $tblScoreCondition = $tblScoreConditionGradeTypeList->getTblScoreCondition();
        if ((new Data($this->getBinding()))->removeScoreConditionGradeTypeList($tblScoreConditionGradeTypeList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return TblScoreRuleConditionList
     */
    public function addScoreRuleConditionList(
        TblScoreRule $tblScoreRule,
        TblScoreCondition $tblScoreCondition
    ) {

        if ((new Data($this->getBinding()))->addScoreRuleConditionList($tblScoreRule, $tblScoreCondition)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreRule->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreRule->getId()));
        }
    }

    /**
     * @param TblScoreRuleConditionList $tblScoreRuleConditionList
     * @return string
     */
    public function removeScoreRuleConditionList(
        TblScoreRuleConditionList $tblScoreRuleConditionList
    ) {

        $tblScoreRule = $tblScoreRuleConditionList->getTblScoreRule();
        if ((new Data($this->getBinding()))->removeScoreRuleConditionList($tblScoreRuleConditionList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreRule->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
            new Redirect('/Education/Graduation/Gradebook/Score/Condition/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreRule->getId()));
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     * @param TblScoreRule $tblScoreRule
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param bool $isStudentView
     * @param bool $taskDate
     *
     * @return array|bool|float|string
     */
    public function calcStudentGrade(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType,
        TblScoreRule $tblScoreRule = null,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null,
        $isStudentView = false,
        $taskDate = false
    ) {

        $tblGradeList = $this->getGradesByStudent(
            $tblPerson, $tblDivision, $tblSubject, $tblTestType, $tblPeriod, $tblSubjectGroup
        );

        // entfernen aller Noten nach dem Stichtag (bei Stichtagsnotenauftäge
        if ($taskDate && $tblGradeList) {
            $tempGradeList = array();
            $taskDate = new \DateTime($taskDate);
            foreach ($tblGradeList as $item) {
                if ($item->getServiceTblTest() && $item->getServiceTblTest()->getDate()) {
                    $testDate = new \DateTime($item->getServiceTblTest()->getDate());
                    // Noten nur vom vor dem Stichtag
                    if ($taskDate->format('Y-m-d') >= $testDate->format('Y-m-d')) {
                        $tempGradeList[] = $item;
                    }
                }
            }
            $tblGradeList = empty($tempGradeList) ? false : $tempGradeList;
        }

        // filter by Test Return for StudentView
        if ($isStudentView && $tblGradeList) {
            $filteredGradeList = array();
            foreach ($tblGradeList as $tblGrade) {
                $tblTest = $tblGrade->getServiceTblTest();
                if ($tblTest) {
                    if ($tblTest->getReturnDate()) {
                        $testDate = (new \DateTime($tblTest->getReturnDate()))->format("Y-m-d");
                        $now = (new \DateTime('now'))->format("Y-m-d");
                        if ($testDate <= $now) {
                            $filteredGradeList[$tblGrade->getId()] = $tblGrade;
                        }
                    }
                }
            }

            $tblGradeList = empty($filteredGradeList) ? false : $filteredGradeList;
        }

        if ($tblGradeList) {
            $result = array();
            $averageGroup = array();
            $resultAverage = '';
            $count = 0;
            $sum = 0;

            // get ScoreCondition
            $tblScoreCondition = false;
            if ($tblScoreRule !== null) {
                $tblScoreConditionsByRule = Gradebook::useService()->getScoreConditionsByRule($tblScoreRule);
                if ($tblScoreConditionsByRule) {
                    if (count($tblScoreConditionsByRule) > 1) {
                        $tblScoreConditionsByRule =
                            $this->getSorter($tblScoreConditionsByRule)->sortObjectList('Priority');
                        if ($tblScoreConditionsByRule) {
                            /** @var TblScoreCondition $item */
                            foreach ($tblScoreConditionsByRule as $item) {
                                $tblScoreConditionGradeTypeListByCondition =
                                    Gradebook::useService()->getScoreConditionGradeTypeListByCondition(
                                        $item
                                    );
                                if ($tblScoreConditionGradeTypeListByCondition) {
                                    $hasConditions = true;
                                    foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeTypeList) {
                                        $hasGradeType = false;
                                        foreach ($tblGradeList as $tblGrade) {
                                            if (is_numeric($tblGrade->getGrade())
                                                && $tblGrade->getTblGradeType()
                                                && $tblScoreConditionGradeTypeList->getTblGradeType()
                                                && ($tblGrade->getTblGradeType()->getId()
                                                    == $tblScoreConditionGradeTypeList->getTblGradeType()->getId())
                                            ) {
                                                $hasGradeType = true;
                                                break;
                                            }
                                        }

                                        if (!$hasGradeType) {
                                            $hasConditions = false;
                                            break;
                                        }
                                    }

                                    if ($hasConditions) {
                                        $tblScoreCondition = $item;
                                        break;
                                    }

                                } else {
                                    // no Conditions
                                    $tblScoreCondition = $item;
                                    break;
                                }
                            }
                        }
                    } else {
                        $tblScoreCondition = $tblScoreConditionsByRule[0];
                    }
                }
            }

            $error = array();
            foreach ($tblGradeList as $tblGrade) {
                if ($tblScoreCondition) {
                    /** @var TblScoreCondition $tblScoreCondition */
                    if (($tblScoreConditionGroupListByCondition
                        = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition))
                    ) {
                        $hasfoundGradeType = false;
                        foreach ($tblScoreConditionGroupListByCondition as $tblScoreGroup) {
                            if (($tblScoreGroupGradeTypeListByGroup
                                = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup->getTblScoreGroup()))
                            ) {
                                foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeTypeList) {
                                    if ($tblGrade->getTblGradeType() && $tblScoreGroupGradeTypeList->getTblGradeType()
                                        && $tblGrade->getTblGradeType()->getId() === $tblScoreGroupGradeTypeList->getTblGradeType()->getId()
                                    ) {
                                        $hasfoundGradeType = true;
                                        if ($tblGrade->getGrade() && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                                            $count++;
                                            $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][$count]['Value']
                                                = floatval($tblGrade->getGrade()) * floatval($tblScoreGroupGradeTypeList->getMultiplier());
                                            $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][$count]['Multiplier']
                                                = floatval($tblScoreGroupGradeTypeList->getMultiplier());
                                        }

                                        break;
                                    }
                                }
                            }
                        }

                        if (!$hasfoundGradeType && $tblGrade->getGrade() && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                            if ($tblGrade->getTblGradeType()) {
                                $error[$tblGrade->getTblGradeType()->getId()] =
                                    new LayoutRow(
                                        new LayoutColumn(
                                            new Warning('Der Zensuren-Typ: ' . $tblGrade->getTblGradeType()->getName()
                                                . ' ist nicht in der Berechnungsvariante: ' . $tblScoreCondition->getName() . ' hinterlegt.',
                                                new Ban()
                                            )
                                        )
                                    );
                            }
                        }
                    }
                } else {
                    // alle Noten gleichwertig
                    if ($tblGrade->getGrade() && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                        $count++;
                        $sum = $sum + floatval($tblGrade->getGrade());
                    }
                }
            }
            if (!empty($error)) {
                return $error;
            }

            if (!$tblScoreCondition) {
                if ($count > 0) {
                    $average = $sum / $count;
                    return round($average, 2);
                } else {
                    return false;
                }
            }

            if (!empty($result)) {
                foreach ($result as $conditionId => $groups) {
                    if (!empty($groups)) {
                        foreach ($groups as $groupId => $group) {
                            if (!empty($group)) {
                                foreach ($group as $value) {
                                    if (isset($averageGroup[$conditionId][$groupId])) {
                                        $averageGroup[$conditionId][$groupId]['Value'] += $value['Value'];
                                        $averageGroup[$conditionId][$groupId]['Multiplier'] += $value['Multiplier'];
                                    } else {
                                        $averageGroup[$conditionId][$groupId]['Value'] = $value['Value'];
                                        $averageGroup[$conditionId][$groupId]['Multiplier'] = $value['Multiplier'];
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($averageGroup[$tblScoreCondition->getId()])) {
                    $average = 0;
                    $totalMultiplier = 0;
                    foreach ($averageGroup[$tblScoreCondition->getId()] as $groupId => $group) {
                        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($groupId);
                        $multiplier = floatval($tblScoreGroup->getMultiplier());
                        if ($group['Value'] > 0) {
                            $totalMultiplier += $multiplier;
                            $average += $multiplier * ($group['Value'] / $group['Multiplier']);
                        }
                    }

                    if ($totalMultiplier > 0) {
                        $average = $average / $totalMultiplier;
                        $resultAverage = round($average, 2);
                    } else {
                        $resultAverage = '';
                    }
                }
            }

            return $resultAverage == '' ? false : $resultAverage
                . ($tblScoreCondition ? '(' . $tblScoreCondition->getPriority() . ')' : '');
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType $tblTestType
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|Service\Entity\TblGrade[]
     */
    public function getGradesByStudent(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getGradesByStudent($tblPerson, $tblDivision, $tblSubject, $tblTestType,
            $tblPeriod, $tblSubjectGroup);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool|TblScoreCondition[]
     */
    public function getScoreConditionsByRule(TblScoreRule $tblScoreRule)
    {

        return (new Data($this->getBinding()))->getScoreConditionsByRule($tblScoreRule);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGradeTypeList[]
     */
    public function getScoreConditionGradeTypeListByCondition(TblScoreCondition $tblScoreCondition)
    {

        return (new Data($this->getBinding()))->getScoreConditionGradeTypeListByCondition($tblScoreCondition);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGroupList[]
     */
    public function getScoreConditionGroupListByCondition(TblScoreCondition $tblScoreCondition)
    {

        return (new Data($this->getBinding()))->getScoreConditionGroupListByCondition($tblScoreCondition);
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool|TblScoreGroupGradeTypeList[]
     */
    public function getScoreGroupGradeTypeListByGroup(TblScoreGroup $tblScoreGroup)
    {

        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreGroup
     */
    public function getScoreGroupById($Id)
    {

        return (new Data($this->getBinding()))->getScoreGroupById($Id);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool|TblScoreRuleConditionList[]
     */
    public function getScoreRuleConditionListByRule(TblScoreRule $tblScoreRule)
    {

        return (new Data($this->getBinding()))->getScoreRuleConditionListByRule($tblScoreRule);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     * @param string $Redirect
     *
     * @return IFormInterface|Redirect|string
     */
    public function getYear(IFormInterface $Stage = null, $Select = null, $Redirect = '')
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $Error = false;
        if (!isset($Select['Year'])) {
            $Error = true;
            $Stage .= new Warning('Schuljahr nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        return new Redirect($Redirect, Redirect::TIMEOUT_SUCCESS, array(
            'YearId' => $Select['Year'],
        ));
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $ScoreCondition
     * @return IFormInterface|string
     */
    public function updateScoreCondition(IFormInterface $Stage = null, $Id, $ScoreCondition)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreCondition || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreCondition['Name']) && empty($ScoreCondition['Name'])) {
            $Stage->setError('ScoreCondition[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        $tblScoreCondition = $this->getScoreConditionById($Id);
        if (!$tblScoreCondition) {
            return new Danger(new Ban() . ' Berechnungsvariante nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreCondition(
                $tblScoreCondition,
                $ScoreCondition['Name'],
                isset($ScoreCondition['Round']) ? $ScoreCondition['Round'] : '',
                $ScoreCondition['Priority']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvariante ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Condition', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $ScoreGroup
     * @return IFormInterface|string
     */
    public function updateScoreGroup(IFormInterface $Stage = null, $Id, $ScoreGroup)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreGroup || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreGroup['Name']) && empty($ScoreGroup['Name'])) {
            $Stage->setError('ScoreGroup[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($ScoreGroup['Multiplier']) && empty($ScoreGroup['Multiplier'])) {
            $Stage->setError('ScoreGroup[Multiplier]', 'Bitte geben sie einen Faktor an');
            $Error = true;
        } elseif (isset($ScoreGroup['Multiplier']) && !preg_match(self::PREG_MATCH_DECIMAL_NUMBER, $ScoreGroup['Multiplier'])) {
            $Stage->setError('ScoreGroup[Multiplier]', 'Bitte geben sie eine Zahl als Faktor an');
            $Error = true;
        }

        $tblScoreGroup = $this->getScoreGroupById($Id);
        if (!$tblScoreGroup) {
            return new Danger(new Ban() . ' Zensuren-Gruppe nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreGroup(
                $tblScoreGroup,
                $ScoreGroup['Name'],
                isset($ScoreGroup['Round']) ? $ScoreGroup['Round'] : '',
                $ScoreGroup['Multiplier']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zensuren-Gruppe ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $ScoreRule
     *
     * @return IFormInterface|string
     */
    public function createScoreRule(IFormInterface $Stage = null, $ScoreRule = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreRule) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreRule['Name']) && empty($ScoreRule['Name'])) {
            $Stage->setError('ScoreRule[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreRule(
                $ScoreRule['Name'],
                $ScoreRule['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvorschrift ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Id
     * @param $ScoreRule
     * @return IFormInterface|string
     */
    public function updateScoreRule(IFormInterface $Stage = null, $Id, $ScoreRule)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreRule || null === $Id) {
            return $Stage;
        }

        $Error = false;
        if (isset($ScoreRule['Name']) && empty($ScoreRule['Name'])) {
            $Stage->setError('ScoreRule[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        $tblScoreRule = $this->getScoreRuleById($Id);
        if (!$tblScoreRule) {
            return new Danger(new Ban() . ' Berechnungsvorschrift nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreRule(
                $tblScoreRule,
                $ScoreRule['Name'],
                $ScoreRule['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvorschrift ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreRule
     */
    public function getScoreRuleById($Id)
    {

        return (new Data($this->getBinding()))->getScoreRuleById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getScoreTypeByIdentifier($Identifier);
    }

    /**
     * @return bool|TblScoreType[]
     */
    public function getScoreTypeAll()
    {

        return (new Data($this->getBinding()))->getScoreTypeAll();
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param $tblDivision
     * @param $tblSubject
     */
    private function setScoreRuleForDivisionSubject(TblScoreRule $tblScoreRule, $tblDivision, $tblSubject)
    {
        if (($tblScoreRuleDivisionSubject = $this->getScoreRuleDivisionSubjectByDivisionAndSubject(
            $tblDivision, $tblSubject
        ))
        ) {
            if (!$tblScoreRuleDivisionSubject->getTblScoreRule()) {
                (new Data($this->getBinding()))->updateScoreRuleDivisionSubject(
                    $tblScoreRuleDivisionSubject, $tblScoreRule,
                    $tblScoreRuleDivisionSubject->getTblScoreType() ? $tblScoreRuleDivisionSubject->getTblScoreType() : null
                );
            }
        } else {
            (new Data($this->getBinding()))->createScoreRuleDivisionSubject(
                $tblDivision, $tblSubject, $tblScoreRule
            );
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblScoreRule $tblScoreRule
     * @param TblYear $tblYear
     * @param $Data
     *
     * @return IFormInterface
     */
    public function updateScoreRuleDivisionSubject(
        IFormInterface $Stage = null,
        TblScoreRule $tblScoreRule,
        TblYear $tblYear = null,
        $Data = null
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit']) || $tblYear == null) {
            return $Stage;
        }

        if (is_array($Data)) {
            foreach ($Data as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    if (is_array($subjectList)) {
                        // alle Fächer einer Klassen zuordnen
                        if (isset($subjectList[-1])) {
                            $tblSubjectAllByDivision = Division::useService()->getSubjectAllByDivision($tblDivision);
                            if ($tblSubjectAllByDivision) {
                                foreach ($tblSubjectAllByDivision as $tblSubject) {
                                    $this->setScoreRuleForDivisionSubject($tblScoreRule, $tblDivision, $tblSubject);
                                }
                            }
                        } else {
                            foreach ($subjectList as $subjectId => $value) {
                                $tblSubject = Subject::useService()->getSubjectById($subjectId);
                                if ($tblSubject) {
                                    $this->setScoreRuleForDivisionSubject($tblScoreRule, $tblDivision, $tblSubject);
                                }
                            }
                        }
                    }
                }
            }
        }

        // bei bereits vorhandenen Einträgen Berechnungsvorschrift zurücksetzten
        $tblScoreRuleDivisionSubjectList = $this->getScoreRuleDivisionSubjectByScoreRule($tblScoreRule);
        if ($tblScoreRuleDivisionSubjectList) {
            foreach ($tblScoreRuleDivisionSubjectList as $tblScoreRuleDivisionSubject) {
                $tblDivision = $tblScoreRuleDivisionSubject->getServiceTblDivision();
                $tblSubject = $tblScoreRuleDivisionSubject->getServiceTblSubject();
                if ($tblDivision && $tblSubject) {
                    if ($tblDivision->getServiceTblYear()
                        && $tblDivision->getServiceTblYear()->getId() == $tblYear->getId()
                    ) {
                        if (!isset($Data[$tblDivision->getId()][-1])                // alle Fächer
                            && !isset($Data[$tblDivision->getId()][$tblSubject->getId()])
                        ) {
                            (new Data($this->getBinding()))->updateScoreRuleDivisionSubject(
                                $tblScoreRuleDivisionSubject, null,
                                $tblScoreRuleDivisionSubject->getTblScoreType() ? $tblScoreRuleDivisionSubject->getTblScoreType() : null
                            );
                        }
                    }
                }
            }
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect('/Education/Graduation/Gradebook/Score/Division', Redirect::TIMEOUT_SUCCESS, array(
            'Id' => $tblScoreRule->getId(),
            'YearId' => $tblYear->getId()
        ));
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param $tblDivision
     * @param $tblSubject
     */
    private function setScoreTypeForDivisionSubject(TblScoreType $tblScoreType, $tblDivision, $tblSubject)
    {
        if (($tblScoreRuleDivisionSubject = $this->getScoreRuleDivisionSubjectByDivisionAndSubject(
            $tblDivision, $tblSubject
        ))
        ) {
            if (!$tblScoreRuleDivisionSubject->getTblScoreType()) {
                (new Data($this->getBinding()))->updateScoreRuleDivisionSubject(
                    $tblScoreRuleDivisionSubject,
                    $tblScoreRuleDivisionSubject->getTblScoreRule() ? $tblScoreRuleDivisionSubject->getTblScoreRule() : null,
                    $tblScoreType
                );
            }
        } else {
            (new Data($this->getBinding()))->createScoreRuleDivisionSubject(
                $tblDivision, $tblSubject, null, $tblScoreType
            );
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblScoreRule $tblScoreRule
     * @param TblYear $tblYear
     * @param $Data
     *
     * @return IFormInterface
     */
    public function updateScoreRuleSubjectGroup(
        IFormInterface $Stage = null,
        TblScoreRule $tblScoreRule,
        TblYear $tblYear = null,
        $Data = null
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit']) || $tblYear == null) {
            return $Stage;
        }

        // add
        if (is_array($Data)) {
            foreach ($Data as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision && is_array($subjectList)) {
                    foreach ($subjectList as $subjectId => $subjectGroupList) {
                        $tblSubject = Subject::useService()->getSubjectById($subjectId);
                        if ($tblSubject && is_array($subjectGroupList)) {
                            foreach ($subjectGroupList as $subjectGroupId => $value) {
                                $tblSubjectGroup = Division::useService()->getSubjectGroupById($subjectGroupId);
                                if ($tblSubjectGroup) {
                                    (new Data($this->getBinding()))->addScoreRuleSubjectGroup(
                                        $tblDivision,
                                        $tblSubject,
                                        $tblSubjectGroup,
                                        $tblScoreRule
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        // remove
        $tblScoreRuleSubjectGroupList = (new Data($this->getBinding()))->getScoreRuleSubjectGroupAllByScoreRule($tblScoreRule);
        if ($tblScoreRuleSubjectGroupList) {
            foreach ($tblScoreRuleSubjectGroupList as $tblScoreRuleSubjectGroup) {
                if ($tblScoreRuleSubjectGroup->getServiceTblDivision()
                    && $tblScoreRuleSubjectGroup->getServiceTblSubject()
                    && $tblScoreRuleSubjectGroup->getServiceTblSubjectGroup()
                    && !isset($Data[$tblScoreRuleSubjectGroup->getServiceTblDivision()->getId()]
                        [$tblScoreRuleSubjectGroup->getServiceTblSubject()->getId()]
                        [$tblScoreRuleSubjectGroup->getServiceTblSubjectGroup()->getId()])
                    && $tblScoreRuleSubjectGroup->getServiceTblDivision()->getServiceTblYear()
                    && $tblScoreRuleSubjectGroup->getServiceTblDivision()->getServiceTblYear()->getId() == $tblYear->getId()
                ) {
                    (new Data($this->getBinding()))->removeScoreRuleSubjectGroup($tblScoreRuleSubjectGroup);
                }
            }
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect('/Education/Graduation/Gradebook/Score/SubjectGroup', Redirect::TIMEOUT_SUCCESS, array(
            'Id' => $tblScoreRule->getId(),
            'YearId' => $tblYear->getId()
        ));
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblScoreType $tblScoreType
     * @param TblYear $tblYear
     * @param $Data
     *
     * @return IFormInterface
     */
    public function updateScoreTypeDivisionSubject(
        IFormInterface $Stage = null,
        TblScoreType $tblScoreType,
        TblYear $tblYear = null,
        $Data = null
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit']) || $tblYear == null) {
            return $Stage;
        }

        if (is_array($Data)) {
            foreach ($Data as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    if (is_array($subjectList)) {
                        // alle Fächer einer Klassen zuordnen
                        if (isset($subjectList[-1])) {
                            $tblSubjectAllByDivision = Division::useService()->getSubjectAllByDivision($tblDivision);
                            if ($tblSubjectAllByDivision) {
                                foreach ($tblSubjectAllByDivision as $tblSubject) {
                                    $this->setScoreTypeForDivisionSubject($tblScoreType, $tblDivision, $tblSubject);
                                }
                            }
                        } else {
                            foreach ($subjectList as $subjectId => $value) {
                                $tblSubject = Subject::useService()->getSubjectById($subjectId);
                                if ($tblSubject) {
                                    $this->setScoreTypeForDivisionSubject($tblScoreType, $tblDivision, $tblSubject);
                                }
                            }
                        }
                    }
                }
            }
        }

        // bei bereits vorhandenen Einträgen Berechnungsvorschrift zurücksetzten
        $tblScoreTypeDivisionSubjectList = $this->getScoreRuleDivisionSubjectAllByScoreType($tblScoreType);
        if ($tblScoreTypeDivisionSubjectList) {
            foreach ($tblScoreTypeDivisionSubjectList as $tblScoreTypeDivisionSubject) {
                $tblDivision = $tblScoreTypeDivisionSubject->getServiceTblDivision();
                $tblSubject = $tblScoreTypeDivisionSubject->getServiceTblSubject();
                if ($tblDivision && $tblSubject) {
                    if ($tblDivision->getServiceTblYear()->getId() == $tblYear->getId()
                        && !Gradebook::useService()->existsGrades($tblDivision, $tblSubject)
                    ) {
                        if (!isset($Data[$tblDivision->getId()][-1])                // alle Fächer
                            && !isset($Data[$tblDivision->getId()][$tblSubject->getId()])
                        ) {
                            (new Data($this->getBinding()))->updateScoreRuleDivisionSubject(
                                $tblScoreTypeDivisionSubject,
                                $tblScoreTypeDivisionSubject->getTblScoreRule() ? $tblScoreTypeDivisionSubject->getTblScoreRule() : null,
                                null
                            );
                        }
                    }
                }
            }
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect('/Education/Graduation/Gradebook/Type/Select', Redirect::TIMEOUT_SUCCESS, array(
            'Id' => $tblScoreType->getId(),
            'YearId' => $tblYear->getId()
        ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool|TblScoreRuleDivisionSubject
     */
    public function getScoreRuleDivisionSubjectByDivisionAndSubject(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->getScoreRuleDivisionSubjectByDivisionAndSubject($tblDivision,
            $tblSubject);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return false|TblScoreRule
     */
    public function getScoreRuleByDivisionAndSubjectAndGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        if ($tblSubjectGroup !== null) {
            $tblScoreRuleSubjectGroup = $this->getScoreRuleSubjectGroupByDivisionAndSubjectAndGroup(
                $tblDivision,
                $tblSubject,
                $tblSubjectGroup
            );
            if ($tblScoreRuleSubjectGroup) {

                return $tblScoreRuleSubjectGroup->getTblScoreRule();
            }
        }

        $tblScoreRuleDivisionSubject = $this->getScoreRuleDivisionSubjectByDivisionAndSubject(
            $tblDivision,
            $tblSubject
        );
        if ($tblScoreRuleDivisionSubject) {

            return $tblScoreRuleDivisionSubject->getTblScoreRule();
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @return false|TblScoreType
     */
    public function getScoreTypeByDivisionAndSubject(
        TblDivision $tblDivision,
        TblSubject $tblSubject
    ) {

        $tblScoreRuleDivisionSubject = $this->getScoreRuleDivisionSubjectByDivisionAndSubject(
            $tblDivision,
            $tblSubject
        );
        if ($tblScoreRuleDivisionSubject) {

            return $tblScoreRuleDivisionSubject->getTblScoreType();
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return false|TblScoreRuleSubjectGroup
     */
    public function getScoreRuleSubjectGroupByDivisionAndSubjectAndGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup
    ) {

        return (new Data($this->getBinding()))->getScoreRuleSubjectGroupByDivisionAndSubjectAndGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeById($Id)
    {

        return (new Data($this->getBinding()))->getScoreTypeById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @return false|TblGrade[]
     */
    public function getGradesByGradeType(TblPerson $tblPerson, TblSubject $tblSubject, TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->getGradesByGradeType($tblPerson, $tblSubject, $tblGradeType);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function existsGrades(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->existsGrades($tblDivision, $tblSubject);
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function destroyGradeType(TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->destroyGradeType($tblGradeType);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $Filter
     *
     * @return IFormInterface|Redirect
     */
    public function getFilteredDivisionSubjectList(IFormInterface $Stage = null, $Filter = null)
    {
        /**
         * Skip to Frontend
         */
        if ($Filter === null) {
            return $Stage;
        }

        return new Redirect('/Education/Graduation/Gradebook/Score/Division', Redirect::TIMEOUT_SUCCESS, array(
            'YearDivisionSubjectId' => $Filter['Year'],
            'TypeDivisionSubjectId' => $Filter['Type'],
            'LevelDivisionSubjectId' => $Filter['Level']
        ));
    }

    /**
     * @param $tblDivisionSubjectList
     * @param TblYear|false $filterYear
     * @param TblType|false $filterType
     * @param TblLevel|false $filterLevel
     * @return array
     */
    public function filterDivisionSubjectList(
        $tblDivisionSubjectList,
        TblYear $filterYear = null,
        TblType $filterType = null,
        TblLevel $filterLevel = null
    ) {

        $resultList = array();

        if ($tblDivisionSubjectList) {
            /** @var TblDivisionSubject $tblDivisionSubject */
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                $hasYear = false;
                $hasType = false;
                $hasLevel = false;

                if (($tblDivision = $tblDivisionSubject->getTblDivision())) {
                    if ($filterYear
                        && $tblDivision->getServiceTblYear()
                        && $filterYear->getId() == $tblDivisionSubject->getTblDivision()->getServiceTblYear()->getId()
                    ) {
                        $hasYear = true;
                    }
                    if ($filterType
                        && $tblDivision->getTblLevel()
                        && $tblDivision->getTblLevel()->getServiceTblType()
                        && $filterType->getId() == $tblDivision->getTblLevel()->getServiceTblType()->getId()
                    ) {
                        $hasType = true;
                    }
                    if ($filterLevel
                        && $tblDivision->getTblLevel()
                        && $filterLevel->getName() == $tblDivision->getTblLevel()->getName()
                    ) {
                        $hasLevel = true;
                    }
                }

                // Filter "Und"-Verknüpfen
                if ($filterYear && $filterLevel && $filterType) {
                    if ($hasYear && $hasLevel && $hasType) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterYear && $filterLevel) {
                    if ($hasYear && $hasLevel) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterYear && $filterType) {
                    if ($hasYear && $hasType) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterLevel && $filterType) {
                    if ($hasLevel && $hasType) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterYear) {
                    if ($hasYear) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterLevel) {
                    if ($hasLevel) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } elseif ($filterType) {
                    if ($hasType) {
                        array_push($resultList, $tblDivisionSubject);
                    }
                } else {
                    array_push($resultList, $tblDivisionSubject);
                }
            }
        }

        return $resultList;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return false|TblScoreRuleDivisionSubject[]
     */
    public function getScoreRuleDivisionSubjectByScoreRule(TblScoreRule $tblScoreRule)
    {

        return (new Data($this->getBinding()))->getScoreRuleDivisionSubjectAllByScoreRule($tblScoreRule);
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return false|TblScoreRuleDivisionSubject[]
     */
    public function getScoreRuleDivisionSubjectAllByScoreType(TblScoreType $tblScoreType)
    {

        return (new Data($this->getBinding()))->getScoreRuleDivisionSubjectAllByScoreType($tblScoreType);
    }

}
