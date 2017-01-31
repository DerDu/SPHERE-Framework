<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\ScoreRule\Service as ServiceScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Data;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
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
use SPHERE\System\Cache\Handler\MemoryHandler;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Service extends ServiceScoreRule
{

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

        $Cache = $this->getCache( new MemoryHandler() );
        if( !($Result = $Cache->getValue( $Id, __METHOD__ )) ) {
            $Result = (new Data($this->getBinding()))->getGradeTypeById($Id);
            $Cache->setValue( $Id, $Result, 0, __METHOD__ );
        }
        return $Result;
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

            if ($tblScoreType && $tblScoreType->getIdentifier() !== 'VERBAL') {
                $tblGradeList = $this->getGradeAllByTest($tblTest);
                if ($tblGradeList) {
                    $sum = 0;
                    $count = 0;
                    foreach ($tblGradeList as $tblGrade) {
                        if ($tblGrade->getGrade() && $tblGrade->getGrade() !== '' && is_numeric($tblGrade->getGrade())) {
                            $sum += floatval($tblGrade->getGrade());
                            $count++;
                        }
                    }

                    if ($count > 0) {
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
     * @param null $studentTestList
     *
     * @return IFormInterface|string
     */
    public function updateGradeToTest(
        IFormInterface $Stage = null,
        $TestId = null,
        $Grade = null,
        $BasicRoute,
        TblScoreType $tblScoreType = null,
        $studentTestList = null
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
                if (!isset($value['Attendance']) && $gradeValue !== '') {
                    if (!preg_match('!' . $tblScoreType->getPattern() . '!is', $gradeValue)) {
                        $errorRange = true;
                        break;
                    }
                }
            }
        }

        $errorEdit = false;
        // Grund bei Noten-Änderung angeben
        $errorNoGrade = false;
        // Datum ist Pflichtfeld bei einem fortlaufenden Test
        $errorNoDate = false;
        if (!empty($Grade)) {
            foreach ($Grade as $personId => $value) {
                $gradeValue = str_replace(',', '.', trim($value['Grade']));
                $tblPerson = Person::useService()->getPersonById($personId);
                if ($studentTestList && isset($studentTestList[$personId])) {
                    $tblTestOfPerson = $studentTestList[$personId];
                } else {
                    $tblTestOfPerson = $tblTest;
                }
                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTestOfPerson, $tblPerson);
                if ($tblGrade && empty($value['Comment']) && !isset($value['Attendance'])
                    && ($gradeValue != $tblGrade->getGrade()
                        || (isset($value['Trend']) && $value['Trend'] != $tblGrade->getTrend()))
                ) {
                    $errorEdit = true;
                }
                if ($tblGrade  && $gradeValue === ''
                    && !isset($value['Attendance'])
                    && (!isset($value['Text']) || (isset($value['Text']) && !$this->getGradeTextById($value['Text'])))
                ) {
                    $errorNoGrade = true;
                }
                if ($tblTest->isContinues() && !isset($value['Attendance']) && $gradeValue && empty($value['Date'])) {
                    $errorNoDate = true;
                }
            }
        }

        if ($errorRange || $errorEdit || $errorNoGrade || $errorNoDate) {
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
            if ($errorNoDate) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Bei einem fortlaufenden Datum muss zu jeder Zensur ein Datum angegeben werden.
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                    ))));
            }

            return $Stage;
        }

        if (!empty($Grade)) {
            foreach ($Grade as $personId => $value) {
                $tblPerson = Person::useService()->getPersonById($personId);
                if ($studentTestList && isset($studentTestList[$personId])) {
                    $tblTestByPerson = $studentTestList[$personId];
                } else {
                    $tblTestByPerson = $tblTest;
                }

                if ($tblTestByPerson->getServiceTblDivision() && $tblTestByPerson->getServiceTblSubject()) {

                    // set trend
                    if (isset($value['Trend'])) {
                        $trend = $value['Trend'];
                    } else {
                        $trend = 0;
                    }

                    $grade = str_replace(',', '.', trim($value['Grade']));

                    if (!($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTestByPerson,
                        $tblPerson))
                    ) {
                        if (isset($value['Attendance'])) {
                            (new Data($this->getBinding()))->createGrade(
                                $tblPerson,
                                $tblTestByPerson->getServiceTblDivision(),
                                $tblTestByPerson->getServiceTblSubject(),
                                $tblTestByPerson->getServiceTblSubjectGroup() ? $tblTestByPerson->getServiceTblSubjectGroup() : null,
                                $tblTestByPerson->getServiceTblPeriod() ? $tblTestByPerson->getServiceTblPeriod() : null,
                                $tblTestByPerson->getServiceTblGradeType() ? $tblTestByPerson->getServiceTblGradeType() : null,
                                $tblTestByPerson,
                                $tblTestByPerson->getTblTestType(),
                                null,
                                trim($value['Comment']),
                                0,
                                null,
                                isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                    ? $tblGradeText : null
                            );
                        } elseif (trim($value['Grade']) !== '') {
                            (new Data($this->getBinding()))->createGrade(
                                $tblPerson,
                                $tblTestByPerson->getServiceTblDivision(),
                                $tblTestByPerson->getServiceTblSubject(),
                                $tblTestByPerson->getServiceTblSubjectGroup() ? $tblTestByPerson->getServiceTblSubjectGroup() : null,
                                $tblTestByPerson->getServiceTblPeriod() ? $tblTestByPerson->getServiceTblPeriod() : null,
                                $tblTestByPerson->getServiceTblGradeType() ? $tblTestByPerson->getServiceTblGradeType() : null,
                                $tblTestByPerson,
                                $tblTestByPerson->getTblTestType(),
                                $grade,
                                trim($value['Comment']),
                                $trend,
                                isset($value['Date']) ? $value['Date'] : null,
                                isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                    ? $tblGradeText : null
                            );
                        } elseif (isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))){
                            (new Data($this->getBinding()))->createGrade(
                                $tblPerson,
                                $tblTestByPerson->getServiceTblDivision(),
                                $tblTestByPerson->getServiceTblSubject(),
                                $tblTestByPerson->getServiceTblSubjectGroup() ? $tblTestByPerson->getServiceTblSubjectGroup() : null,
                                $tblTestByPerson->getServiceTblPeriod() ? $tblTestByPerson->getServiceTblPeriod() : null,
                                $tblTestByPerson->getServiceTblGradeType() ? $tblTestByPerson->getServiceTblGradeType() : null,
                                $tblTestByPerson,
                                $tblTestByPerson->getTblTestType(),
                                $grade,
                                trim($value['Comment']),
                                $trend,
                                isset($value['Date']) ? $value['Date'] : null,
                                $tblGradeText
                            );
                        }
                    } elseif ($tblGrade) {

                        if (isset($value['Attendance'])) {
                            (new Data($this->getBinding()))->updateGrade(
                                $tblGrade,
                                null,
                                trim($value['Comment']),
                                0,
                                null,
                                isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                    ? $tblGradeText : null
                            );
                        } else {
                            (new Data($this->getBinding()))->updateGrade(
                                $tblGrade,
                                $grade,
                                trim($value['Comment']),
                                $trend,
                                isset($value['Date']) ? $value['Date'] : null,
                                isset($value['Text']) && ($tblGradeText = $this->getGradeTextById($value['Text']))
                                    ? $tblGradeText : null
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

        // entfernen aller Noten nach dem Stichtag (bei Stichtagsnotenauftägen)
        if ($taskDate && $tblGradeList) {
            $tempGradeList = array();
            $taskDate = new \DateTime($taskDate);
            foreach ($tblGradeList as $item) {
                if ($item->getServiceTblTest()) {
                    // Zensuren-Datum
                    if ($item->getServiceTblTest()->isContinues() && $item->getDate()) {
                        $gradeDate = new \DateTime($item->getDate());
                        // Noten nur vom vor dem Stichtag
                        if ($taskDate->format('Y-m-d') >= $gradeDate->format('Y-m-d')) {
                            $tempGradeList[] = $item;
                        }
                    } // Test-Datum
                    elseif ($item->getServiceTblTest()->getDate()) {
                        $testDate = new \DateTime($item->getServiceTblTest()->getDate());
                        // Noten nur vom vor dem Stichtag
                        if ($taskDate->format('Y-m-d') >= $testDate->format('Y-m-d')) {
                            $tempGradeList[] = $item;
                        }
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
                    // Noten-Datum
                    if ($tblTest->isContinues() && $tblGrade->getDate()) {
                        $gradeDate = (new \DateTime($tblGrade->getDate()))->format("Y-m-d");
                        $now = (new \DateTime('now'))->format("Y-m-d");
                        if ($gradeDate <= $now) {
                            $filteredGradeList[$tblGrade->getId()] = $tblGrade;
                        }
                    } // Test-Datum
                    elseif ($tblTest->getReturnDate()) {
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
                            $this->getSorter($tblScoreConditionsByRule)->sortObjectBy('Priority');
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
                            if (!empty($group)
                                && ($tblScoreGroupItem = Gradebook::useService()->getScoreGroupById($groupId))
                            ) {

                                $countGrades = 0;
                                foreach ($group as $value) {
                                    if ($tblScoreGroupItem->isEveryGradeASingleGroup()) {
                                        $countGrades++;
                                        $averageGroup[$conditionId][$groupId][$countGrades]['Value'] = $value['Value'];
                                        $averageGroup[$conditionId][$groupId][$countGrades]['Multiplier'] = $value['Multiplier'];
                                    } else {
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
                }

                if (!empty($averageGroup[$tblScoreCondition->getId()])) {
                    $average = 0;
                    $totalMultiplier = 0;
                    foreach ($averageGroup[$tblScoreCondition->getId()] as $groupId => $group) {
                        if (($tblScoreGroup = Gradebook::useService()->getScoreGroupById($groupId))) {
                            $multiplier = floatval($tblScoreGroup->getMultiplier());
                            if ($tblScoreGroup->isEveryGradeASingleGroup() && is_array($group)) {

                                foreach ($group as $itemValue) {
                                    if (isset($itemValue['Value']) && isset($itemValue['Multiplier']) && $itemValue['Value'] > 0) {
                                        $totalMultiplier += $multiplier;
                                        $average += $multiplier * ($itemValue['Value'] / $itemValue['Multiplier']);
                                    }
                                }

                            } else {

                                if (isset($group['Value']) && isset($group['Multiplier']) && $group['Value'] > 0) {
                                    $totalMultiplier += $multiplier;
                                    $average += $multiplier * ($group['Value'] / $group['Multiplier']);
                                }

                            }
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
     * @param TblScoreType $tblScoreType
     * @param TblYear $tblYear
     * @param $Data
     *
     * @return IFormInterface|string
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
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     *
     * @return false|Service\Entity\TblGrade[]
     */
    public function getGradesByGradeType(TblPerson $tblPerson, TblDivision $tblDivision, TblSubject $tblSubject, TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->getGradesByGradeType($tblPerson, $tblDivision, $tblSubject, $tblGradeType);
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
     * @param $Id
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeById($Id)
    {

        return (new Data($this->getBinding()))->getScoreTypeById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblGradeText
     */
    public function getGradeTextById($Id)
    {

        return (new Data($this->getBinding()))->getGradeTextById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblGradeText
     */
    public function getGradeTextByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getGradeTextByIdentifier($Identifier);
    }

    /**
     * @return false|TblGradeText[]
     */
    public function getGradeTextAll()
    {

        return (new Data($this->getBinding()))->getGradeTextAll();
    }
}
