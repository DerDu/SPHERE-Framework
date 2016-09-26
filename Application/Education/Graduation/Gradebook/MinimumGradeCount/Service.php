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
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Message\Repository\Success;
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
     *
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAllByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        return (new Data($this->getBinding()))->getMinimumGradeCountAllByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $MinimumGradeCount
     *
     * @return IFormInterface|string
     */
    public function createMinimumGradeCount(IFormInterface $Stage = null, $MinimumGradeCount)
    {

        /**
         * Skip to Frontend
         */
        if (null === $MinimumGradeCount) {
            return $Stage;
        }

        $Error = false;
        if (isset($MinimumGradeCount['Count']) && empty($MinimumGradeCount['Count'])) {
            $Stage->setError('MinimumGradeCount[Count]', 'Bitte geben Sie eine Anzahl an');
            $Error = true;
        }
        if (!($tblLevel = Division::useService()->getLevelById($MinimumGradeCount['Level']))) {
            $Stage->setError('MinimumGradeCount[Level]', 'Bitte wählen Sie eine Klassenstufe aus');
            $Error = true;
        }

        if (!$Error) {
            $tblSubject = Subject::useService()->getSubjectById($MinimumGradeCount['Subject']);
            $tblGradeType = Gradebook::useService()->getGradeTypeById($MinimumGradeCount['GradeType']);

            (new Data($this->getBinding()))->createMinimumGradeCount(
                $MinimumGradeCount['Count'],
                $tblLevel,
                $tblSubject ? $tblSubject : null,
                $tblGradeType ? $tblGradeType : null
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Mindestnotenanzahl ist erfasst worden')
            . new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     * @param $Count
     *
     * @return IFormInterface|string
     */
    public function updateMinimumGradeCount(
        IFormInterface $Stage = null,
        TblMinimumGradeCount $tblMinimumGradeCount,
        $Count
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Count) {
            return $Stage;
        }

        $Error = false;
        if (isset($Count) && empty($Count)) {
            $Stage->setError('Count', 'Bitte geben Sie eine Anzahl an');
            $Error = true;
        }

        if (!$Error) {

            (new Data($this->getBinding()))->updateMinimumGradeCount(
                $tblMinimumGradeCount,
                $Count
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Mindestnotenanzahl ist geändert worden')
            . new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
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

        $count = 0;

        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
            && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
        ) {

            $tblGradeType = $tblMinimumGradeCount->getTblGradeType();

            $tblGradeList = Gradebook::useService()->getGradesByStudent($tblPerson, $tblDivision, $tblSubject, $tblTestType);
            if ($tblGradeList){
                /** @var TblGrade $tblGrade */
                foreach ($tblGradeList as $tblGrade){
                   if ($tblGrade->getGrade()
                       && $tblGrade->getTblGradeType()
                   ){
                       if ($tblGradeType){
                           if ($tblGradeType->getId() == $tblGrade->getTblGradeType()){
                               $count++;
                           }
                       } else {
                           $count++;
                       }
                   }
                }
            }
        }

        if ($count < $tblMinimumGradeCount->getCount()){
            return new Warning(new Disable() . ' '. $count);
        } else {
            return new TextSuccess(new Ok() . ' ' . $count);
        }
    }
}