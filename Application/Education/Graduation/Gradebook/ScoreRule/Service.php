<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 21.09.2016
 * Time: 11:55
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\ScoreRule;

use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\Service as ServiceMinimumGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Data;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleDivisionSubject;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleSubjectGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\ScoreRule
 */
abstract class Service extends ServiceMinimumGrade
{

    const PREG_MATCH_DECIMAL_NUMBER = '!^[0-9]+((\.|,)[0-9]+)?$!is';

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
                $ScoreGroup['Multiplier'],
                isset($ScoreGroup['IsEveryGradeASingleGroup'])
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zensuren-Gruppe ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreGroup $tblScoreGroup
     * @param $Multiplier
     *
     * @return string
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
     * @return string
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
     * @return string
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
                $ScoreGroup['Multiplier'],
                isset($ScoreGroup['IsEveryGradeASingleGroup'])
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
     * @param IFormInterface|null $Stage
     * @param TblScoreRule $tblScoreRule
     * @param TblYear $tblYear
     * @param $Data
     *
     * @return IFormInterface|string
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
            /** @var TblScoreRuleDivisionSubject $tblScoreRuleDivisionSubject */
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
     * @param IFormInterface|null $Stage
     * @param TblScoreRule $tblScoreRule
     * @param TblYear $tblYear
     * @param $Data
     *
     * @return IFormInterface|string
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