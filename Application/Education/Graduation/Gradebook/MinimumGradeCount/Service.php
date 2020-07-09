<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.09.2016
 * Time: 08:37
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Data;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success as TextSuccess;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount
 */
abstract class Service extends AbstractService
{

    /**
     * @param $Id
     *
     * @return false|TblMinimumGradeCount
     */
    public function getMinimumGradeCountById($Id)
    {

        return (new Data($this->getBinding()))->getMinimumGradeCountById($Id);
    }

    /**
     * @param TblLevel $tblLevel
     * @param TblSubject|null $tblSubject
     * @param TblMinimumGradeCount|null $tblMinimumGradeCount
     * @return false|TblMinimumGradeCount
     */
    public function getMinimumGradeCountBy(
        TblLevel $tblLevel,
        TblSubject $tblSubject = null,
        TblMinimumGradeCount $tblMinimumGradeCount = null
    ) {

        return (new Data($this->getBinding()))->getMinimumGradeCountBy($tblLevel, $tblSubject, $tblMinimumGradeCount);
    }

    /**
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAll()
    {

        return (new Data($this->getBinding()))->getMinimumGradeCountAll();
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param boolean isSekII
     *
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAllByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject,
        $isSekII
    ) {

        if (($tblMinimumGradeCountList = (new Data($this->getBinding()))->getMinimumGradeCountAllByDivisionSubject($tblDivisionSubject))) {
            if ($isSekII
                && ($tblGroup = $tblDivisionSubject->getTblSubjectGroup())
            ) {
                $list = array();
                foreach ($tblMinimumGradeCountList as $tblMinimumGradeCount) {

                    if ($tblMinimumGradeCount->getCourse() == SelectBoxItem::COURSE_ADVANCED) {
                        if (!$tblGroup->isAdvancedCourse()) {
                            continue;
                        }
                    } elseif ($tblMinimumGradeCount->getCourse() == SelectBoxItem::COURSE_BASIC) {
                        if ($tblGroup->isAdvancedCourse()) {
                            continue;
                        }
                    }

                    $list[] = $tblMinimumGradeCount;
                }

                return empty($list) ? false : $list;
            } else {
                return $tblMinimumGradeCountList;
            }
        }

        return false;
    }

    /**
     * @param IFormInterface|null $form
     * @param $MinimumGradeCount
     * @param TblMinimumGradeCount|null $tblMinimumGradeCount
     *
     * @return IFormInterface|string
     */
    public function updateMinimumGradeCount(IFormInterface $form = null, $MinimumGradeCount, TblMinimumGradeCount $tblMinimumGradeCount = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $MinimumGradeCount) {
            return $form;
        }

        $Error = false;
        if (isset($MinimumGradeCount['Count']) && empty($MinimumGradeCount['Count'])) {
            $form->setError('MinimumGradeCount[Count]', 'Bitte geben Sie eine Anzahl an');
            $Error = true;
        }
        // message for level required
        if (!isset($MinimumGradeCount['Levels'])) {
            $form->prependGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie mindestens eine Klassenstufe aus.', new Exclamation())))));
            $Error = true;
        }

        if (!$Error) {
            if ($MinimumGradeCount['GradeType'] < 0) {
                $highlighted = -$MinimumGradeCount['GradeType'];
                $tblGradeType = false;
            } else {
                $highlighted = SelectBoxItem::HIGHLIGHTED_ALL;
                $tblGradeType = Gradebook::useService()->getGradeTypeById($MinimumGradeCount['GradeType']);
            }

            if ($tblMinimumGradeCount) {
                $tblMinimumGradeCountList = $this->getMinimumGradeCountAllBy(
                    $tblMinimumGradeCount->getHighlighted(),
                    $tblMinimumGradeCount->getTblGradeType() ? $tblMinimumGradeCount->getTblGradeType() : null,
                    $tblMinimumGradeCount->getPeriod(),
                    $tblMinimumGradeCount->getCourse(),
                    $tblMinimumGradeCount->getCount()
                );

                // delete all by nur bei update
                if ($tblMinimumGradeCountList) {
                    (new Data($this->getBinding()))->destroyBulkMinimumGradeCountList($tblMinimumGradeCountList);
                }
            }
//            else {
//                $tblMinimumGradeCountList = $this->getMinimumGradeCountAllBy(
//                    $highlighted,
//                    $tblGradeType ? $tblGradeType : null,
//                    $MinimumGradeCount['Period'],
//                    $MinimumGradeCount['Course'],
//                    $MinimumGradeCount['Count']
//                );
//            }

            (new Data($this->getBinding()))->createBulkMinimumGradeCountList(
                $MinimumGradeCount,
                $highlighted,
                $tblGradeType ? $tblGradeType : null
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Mindestnotenanzahl ist erfasst/geändert worden')
            . new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return bool
     */
    public function destroyMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {

        return (new Data($this->getBinding()))->destroyMinimumGradeCount($tblMinimumGradeCount);
    }

    /**
     * @param $tblMinimumGradeCountList
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function destroyBulkMinimumGradeCountList($tblMinimumGradeCountList)
    {

        return (new Data($this->getBinding()))->destroyBulkMinimumGradeCountList($tblMinimumGradeCountList);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return TextSuccess|Warning
     */
    public function getMinimumGradeCountInfo(
        TblDivisionSubject $tblDivisionSubject,
        TblPerson $tblPerson,
        TblMinimumGradeCount $tblMinimumGradeCount
    ) {

        $count = $this->getMinimumGradeCountNumber($tblDivisionSubject, $tblPerson, $tblMinimumGradeCount);

        if ($count < $tblMinimumGradeCount->getCount()){
            return new Warning(new Disable() . ' '. new Bold($count));
        } else {
            return new TextSuccess(new Ok() . ' ' . new Bold($count));
        }
    }

    /**
     * @param $highlighted
     * @param TblGradeType|null $tblGradeType
     * @param $period
     * @param $course
     * @param $count
     *
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAllBy(
        $highlighted,
        TblGradeType $tblGradeType = null,
        $period,
        $course,
        $count
    ){

        return (new Data($this->getBinding()))->getMinimumGradeCountAllBy($highlighted, $tblGradeType, $period, $course, $count);
    }

    /**
     * @param $Count
     * @param TblLevel $tblLevel
     * @param TblSubject|null $tblSubject
     * @param TblGradeType|null $tblGradeType
     * @param integer $Period
     * @param integer $Highlighted
     * @param $Course
     *
     * @return TblMinimumGradeCount
     */
    public function createMinimumGradeCount(
        $Count,
        TblLevel $tblLevel,
        TblSubject $tblSubject = null,
        TblGradeType $tblGradeType = null,
        $Period,
        $Highlighted,
        $Course
    ) {

        return (new Data($this->getBinding()))->createMinimumGradeCount(
            $Count,
            $tblLevel,
            $tblSubject,
            $tblGradeType,
            $Period,
            $Highlighted,
            $Course
        );
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return int
     */
    public function getMinimumGradeCountNumber(
        TblDivisionSubject $tblDivisionSubject,
        TblPerson $tblPerson,
        TblMinimumGradeCount $tblMinimumGradeCount
    ) {

        $count = 0;

        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
            && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
        ) {

            $tblGradeType = $tblMinimumGradeCount->getTblGradeType();
            $tblPeriod = false;
            if ($tblMinimumGradeCount->getPeriod() != SelectBoxItem::PERIOD_FULL_YEAR) {
                $index = $tblMinimumGradeCount->getPeriod() - 1;
                $tblLevel = $tblDivision->getTblLevel();
                if (($tblYear = $tblDivision->getServiceTblYear())
                    && ($tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear,
                        $tblLevel && $tblLevel->getName() == '12'))
                    && isset($tblPeriodList[$index])
                ) {
                    $tblPeriod = $tblPeriodList[$index];
                }
            }

            $tblGradeList = Gradebook::useService()->getGradesByStudent($tblPerson, $tblDivision, $tblSubject,
                $tblTestType,
                $tblPeriod ? $tblPeriod : null);
            if ($tblGradeList) {
                /** @var TblGrade $tblGrade */
                foreach ($tblGradeList as $tblGrade) {
                    if (($tblGrade->getGrade() || $tblGrade->getGrade() === '0')
                        && $tblGrade->getServiceTblTest()
                        && ($tblGradeTypeItem = $tblGrade->getTblGradeType())
                    ) {
                        if ($tblGradeType) {
                            if ($tblGradeType->getId() == $tblGradeTypeItem->getId()) {
                                $count++;
                            }
                        } elseif ($tblMinimumGradeCount->getHighlighted() == SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED) {
                            if ($tblGradeTypeItem->isHighlighted()) {
                                $count++;
                            }
                        } elseif ($tblMinimumGradeCount->getHighlighted() == SelectBoxItem::HIGHLIGHTED_IS_NOT_HIGHLIGHTED) {
                            if (!$tblGradeTypeItem->isHighlighted()) {
                                $count++;
                            }
                        } else {
                            // Alle Zensuren-Typen
                            $count++;
                        }
                    }
                }
            }
        }

        return $count;
    }
}