<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.07.2016
 * Time: 09:05
 */

namespace SPHERE\Application\Education\ClassRegister\Absence;

use SPHERE\Application\Education\ClassRegister\Absence\Service\Data;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\ViewAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence
 */
class Service extends AbstractService
{
    /**
     * @return false|ViewAbsence[]
     */
    public function viewAbsence()
    {

        return ( new Data($this->getBinding()) )->viewAbsence();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision|null $tblDivision
     * @param bool $isForced
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, TblDivision $tblDivision = null, $isForced = false)
    {

        return (new Data($this->getBinding()))->getAbsenceAllByPerson($tblPerson, $tblDivision, $isForced);
    }

    /**
     * @param $Id
     *
     * @return false|TblAbsence
     */
    public function getAbsenceById($Id)
    {

        return (new Data($this->getBinding()))->getAbsenceById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param string $BasicRoute
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createAbsence(
        IFormInterface $Stage = null,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        $BasicRoute = '',
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $Stage->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['FromDate']) && !empty($Data['FromDate'])
            && isset($Data['ToDate']) && !empty($Data['ToDate'])
        ) {
            $fromDate = new \DateTime($Data['FromDate']);
            $toDate = new \DateTime($Data['ToDate']);
            if ($toDate->format('Y-m-d') < $fromDate->format('Y-m-d')){
                $Stage->setError('Data[ToDate]', 'Das "Datum bis" darf nicht kleiner sein Datum als das "Datum von"');
                $Error = true;
            }
        }

        $minDate = false;
        $maxDate = false;
        if (($tblYear = $tblDivision->getServiceTblYear())) {
            $tblLevel = $tblDivision->getTblLevel();
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $tblLevel && $tblLevel->getName() == '12');
            if ($tblPeriodList) {
                foreach ($tblPeriodList as $tblPeriod) {
                    if (!$minDate) {
                        $minDate = new \DateTime($tblPeriod->getFromDate());
                    } elseif ($minDate >= new \DateTime($tblPeriod->getFromDate())) {
                        $minDate = new \DateTime($tblPeriod->getFromDate());
                    }
                    if (!$maxDate) {
                        $maxDate = new \DateTime($tblPeriod->getToDate());
                    } elseif ($maxDate <= new \DateTime($tblPeriod->getToDate())) {
                        $maxDate = new \DateTime($tblPeriod->getToDate());
                    }
                }
            }
        }
        if (!$Error && $minDate && $maxDate) {
            if (new \DateTime($Data['FromDate']) < $minDate) {
                $Stage->setError('Data[FromDate]',
                    'Eingabe außerhalb des Schuljahres ('.$minDate->format('d.m.Y').' - '.$maxDate->format('d.m.Y').')');
                $Error = true;
            }
            if (new \DateTime($Data['ToDate']) > $maxDate) {
                $Stage->setError('Data[ToDate]',
                    'Eingabe außerhalb des Schuljahres ('.$minDate->format('d.m.Y').' - '.$maxDate->format('d.m.Y').')');
                $Error = true;
            }
        }

        // ToDo setError for RadioBox
        if (!isset($Data['Status'])) {
            $Stage->setError('Data[Status]', 'Bitte geben Sie einen Status an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createAbsence(
                $tblPerson,
                $tblDivision,
                $Data['FromDate'],
                $Data['ToDate'],
                $Data['Status'],
                $Data['Remark']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Fehlzeit ist erfasst worden.')
            . new Redirect('/Education/ClassRegister/Absence', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblDivision->getId(),
                'PersonId' => $tblPerson->getId(),
                'BasicRoute' => $BasicRoute
            ));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblAbsence $tblAbsence
     * @param string $BasicRoute
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateAbsence(IFormInterface $Stage = null, TblAbsence $tblAbsence, $BasicRoute = '', $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $Stage->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['FromDate']) && !empty($Data['FromDate'])
            && isset($Data['ToDate']) && !empty($Data['ToDate'])
        ) {
            $fromDate = new \DateTime($Data['FromDate']);
            $toDate = new \DateTime($Data['ToDate']);
            if ($toDate->format('Y-m-d') < $fromDate->format('Y-m-d')){
                $Stage->setError('Data[ToDate]', 'Das "Datum bis" darf nicht kleiner sein Datum als das "Datum von"');
                $Error = true;
            }
        }

        // ToDo setError for RadioBox
        if (!isset($Data['Status'])) {
            $Stage->setError('Data[Status]', 'Bitte geben Sie einen Status an');
            $Error = true;
        }

        $minDate = false;
        $maxDate = false;
        if (($tblDivision = $tblAbsence->getServiceTblDivision())) {
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                $tblLevel = $tblDivision->getTblLevel();
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $tblLevel && $tblLevel->getName() == '12');
                if ($tblPeriodList) {
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (!$minDate) {
                            $minDate = new \DateTime($tblPeriod->getFromDate());
                        } elseif ($minDate >= new \DateTime($tblPeriod->getFromDate())) {
                            $minDate = new \DateTime($tblPeriod->getFromDate());
                        }
                        if (!$maxDate) {
                            $maxDate = new \DateTime($tblPeriod->getToDate());
                        } elseif ($maxDate <= new \DateTime($tblPeriod->getToDate())) {
                            $maxDate = new \DateTime($tblPeriod->getToDate());
                        }
                    }
                }
            }
            if (!$Error && $minDate && $maxDate) {
                if (new \DateTime($Data['FromDate']) < $minDate) {
                    $Stage->setError('Data[FromDate]',
                        'Eingabe außerhalb des Schuljahres ('.$minDate->format('d.m.Y').' - '.$maxDate->format('d.m.Y').')');
                    $Error = true;
                }
                if (new \DateTime($Data['ToDate']) > $maxDate) {
                    $Stage->setError('Data[ToDate]',
                        'Eingabe außerhalb des Schuljahres ('.$minDate->format('d.m.Y').' - '.$maxDate->format('d.m.Y').')');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateAbsence(
                $tblAbsence,
                $Data['FromDate'],
                $Data['ToDate'],
                $Data['Status'],
                $Data['Remark']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Fehlzeit ist geändert worden.')
            . new Redirect('/Education/ClassRegister/Absence', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblAbsence->getServiceTblDivision()->getId(),
                'PersonId' => $tblAbsence->getServiceTblPerson()->getId(),
                'BasicRoute' => $BasicRoute
            ));
        }

        return $Stage;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyAbsence(TblAbsence $tblAbsence, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyAbsence($tblAbsence, $IsSoftRemove);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param \DateTime|null $tillDate
     *
     * @return int
     */
    function getUnexcusedDaysByPerson(TblPerson $tblPerson, TblDivision $tblDivision, \DateTime $tillDate = null)
    {

        $list = array();
        // Fehlzeiten aus alle Klassen des Schuljahrs
        if (($tblDivisionList = Division::useService()->getOtherDivisionsByStudent($tblDivision, $tblPerson, true))) {
            foreach ($tblDivisionList as $tblDivisionItem) {
                if (($absenceList = $this->getAbsenceAllByPerson($tblPerson, $tblDivisionItem))) {
                    $list = array_merge($list, $absenceList);
                }
            }
        }

        $days = 0;
        foreach ($list as $item) {
            if ($item->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED) {
                $days += $item->getDays($tillDate);
            }
        }

        return $days;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param \DateTime|null $tillDate
     *
     * @return int
     */
    public function getExcusedDaysByPerson(TblPerson $tblPerson, TblDivision $tblDivision, \DateTime $tillDate = null)
    {

        $list = array();
        // Fehlzeiten aus alle Klassen des Schuljahrs
        if (($tblDivisionList = Division::useService()->getOtherDivisionsByStudent($tblDivision, $tblPerson, true))) {
            foreach ($tblDivisionList as $tblDivisionItem) {
                if (($absenceList = $this->getAbsenceAllByPerson($tblPerson, $tblDivisionItem))) {
                    $list = array_merge($list, $absenceList);
                }
            }
        }

        $days = 0;
        /** @var TblAbsence $item */
        foreach ($list as $item) {
            if ($item->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED) {
                $days += $item->getDays($tillDate);
            }
        }

        return $days;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function destroyAbsenceAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblAbsenceList = $this->getAbsenceAllByPerson($tblPerson))){
            foreach($tblAbsenceList as $tblAbsence){
                $this->destroyAbsence($tblAbsence, $IsSoftRemove);
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param \DateTime $date
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceListByDate(TblPerson $tblPerson, TblDivision $tblDivision, \DateTime $date)
    {

        $resultList = array();
        if (($tblAbsenceList = $this->getAbsenceAllByPerson($tblPerson, $tblDivision))){
            foreach ($tblAbsenceList as $tblAbsence){
                $fromDate = new \DateTime($tblAbsence->getFromDate());
                if ($tblAbsence->getToDate()) {
                    $toDate = new \DateTime($tblAbsence->getToDate());
                    if ($toDate >= $fromDate) {
                        if ($fromDate <= $date && $date<= $toDate) {
                            $resultList[] = $tblAbsence;
                        }
                    }
                } else {
                    if ($date->format('d.m.Y') == $fromDate->format('d.m.Y')) {
                        $resultList[] = $tblAbsence;
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblDivision $tblDivision
     * @param string $BasicRoute
     * @param \DateTime|null $date
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createAbsenceList(
        IFormInterface $Stage = null,
        TblDivision $tblDivision,
        $BasicRoute = '',
        \DateTime $date = null,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data || null === $date) {
            return $Stage;
        }

        foreach ($Data as $personId => $value) {
            if (($tblPerson = Person::useService()->getPersonById($personId))) {
                if ($value != TblAbsence::VALUE_STATUS_NULL) {
                    if (($tblAbsenceList = $this->getAbsenceListByDate($tblPerson, $tblDivision, $date))) {
                        if (count($tblAbsenceList) == 1) {
                            $tblAbsence = current($tblAbsenceList);
                            if ($tblAbsence->getStatus() != $value) {
                                if ($tblAbsence->isSingleDay()) {
                                    (new Data($this->getBinding()))->updateAbsence(
                                        $tblAbsence,
                                        $tblAbsence->getFromDate(),
                                        $tblAbsence->getToDate(),
                                        $value,
                                        $tblAbsence->getRemark());
                                } else {
                                    (new Data($this->getBinding()))->createAbsence(
                                        $tblPerson,
                                        $tblDivision,
                                        $date->format('d.m.Y'),
                                        '',
                                        $value,
                                        ''
                                    );
                                }
                            }
                        } else {
                            $exists = false;
                            foreach($tblAbsenceList as $tblAbsence) {
                                if ($tblAbsence->getStatus() == $value) {
                                    $exists = true;
                                    break;
                                } elseif ($tblAbsence->isSingleDay()) {
                                    (new Data($this->getBinding()))->updateAbsence(
                                        $tblAbsence,
                                        $tblAbsence->getFromDate(),
                                        $tblAbsence->getToDate(),
                                        $value,
                                        $tblAbsence->getRemark());
                                    $exists = true;
                                    break;
                                }
                            }
                            if (!$exists) {
                                (new Data($this->getBinding()))->createAbsence(
                                    $tblPerson,
                                    $tblDivision,
                                    $date->format('d.m.Y'),
                                    '',
                                    $value,
                                    ''
                                );
                            }
                        }
                    } else {
                        (new Data($this->getBinding()))->createAbsence(
                            $tblPerson,
                            $tblDivision,
                            $date->format('d.m.Y'),
                            '',
                            $value,
                            ''
                        );
                    }
                } else {
                    // delete Absence
                    if (($tblAbsenceList = $this->getAbsenceListByDate($tblPerson, $tblDivision, $date))) {
                        foreach ($tblAbsenceList as $tblAbsence) {
                            if ($tblAbsence->isSingleDay()) {
                                $this->destroyAbsence($tblAbsence);
                                break;
                            }
                        }
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Fehlzeiten sind erfasst worden.')
            . new Redirect('/Education/ClassRegister/Absence/Month', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblDivision->getId(),
                'BasicRoute' => $BasicRoute,
                'Month' => $date->format('m'),
                'Year' => $date->format('Y'),
            ));
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return bool
     */
    public function restoreAbsence(TblAbsence $tblAbsence)
    {

        return (new Data($this->getBinding()))->restoreAbsence($tblAbsence);
    }

    /**
     * @return false|TblAbsence[]
     */
    public function getAbsenceAll()
    {

        return (new Data($this->getBinding()))->getAbsenceAll();
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getAbsenceAllByDivision($tblDivision);
    }

    /**
     * @param \DateTime $dateTime
     * @param TblType|null $tblType
     * @param array $divisionList
     * @param array $groupList
     *
     * @return array
     */
    public function getAbsenceAllByDay(\DateTime $dateTime, TblType $tblType = null, $divisionList = array(), $groupList = array())
    {
        $resultList = array();
        $tblAbsenceList = array();
        $isGroup = false;
        $groupPersonList = array();
        if (!empty($divisionList)
            && ($tblDivisionAll = Division::useService()->getDivisionAll())
        ) {
            foreach ($divisionList as $tblDivision) {
                if (($tblAbsenceDivisionList = $this->getAbsenceAllByDivision($tblDivision))) {
                    $tblAbsenceList = array_merge($tblAbsenceList, $tblAbsenceDivisionList);
                }
            }
        } elseif (!empty($groupList)) {
            $isGroup = true;
            foreach ($groupList as $tblGroup) {
                if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                    foreach ($tblPersonList as $tblPerson) {
                        $groupPersonList[$tblPerson->getId()] = $tblGroup->getName();
                        if (($tblAbsencePersonList = $this->getAbsenceAllByPerson($tblPerson))) {
                            $tblAbsenceList = array_merge($tblAbsenceList, $tblAbsencePersonList);
                        }
                    }
                }
            }
        } else {
            $tblAbsenceList = $this->getAbsenceAll();
        }

        if ($tblAbsenceList) {
            foreach ($tblAbsenceList as $tblAbsence) {
                $isAdd = false;
                $fromDate = new \DateTime($tblAbsence->getFromDate());
                if ($fromDate->format('d.m.Y') == $dateTime->format('d.m.Y')) {
                    $isAdd = true;
                } elseif ($tblAbsence->getToDate()) {
                    $toDate = new \DateTime($tblAbsence->getToDate());
                    if ($fromDate <= $dateTime && $toDate >= $dateTime) {
                        $isAdd = true;
                    }
                }

                if ($isAdd
                    && ($tblPerson = $tblAbsence->getServiceTblPerson())
                    && ($tblDivision = $tblAbsence->getServiceTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblTypeItem = $tblLevel->getServiceTblType())
                ) {
                    if (!$tblType || ($tblType->getId() == $tblTypeItem->getId())) {
                        $resultList[] = array(
                            'Type' => $tblTypeItem->getName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Group' => $isGroup && isset($groupPersonList[$tblPerson->getId()]) ? $groupPersonList[$tblPerson->getId()] : '',
                            'Person' => $tblPerson->getLastFirstName(),
                            'DateSpan' => $tblAbsence->getDateSpan(),
                            'Status' => $tblAbsence->getStatusDisplayName(),
                            'Remark' => $tblAbsence->getRemark()
                        );
                    }
                }
            }
        }

        // Liste sortieren
        if (!empty($resultList)) {
            $type = $division = $group = $person = array();
            foreach ($resultList as $key => $row) {
                $type[$key] = strtoupper($row['Type']);
                $division[$key] = strtoupper($row['Division']);
                $group[$key] = strtoupper($row['Group']);
                $person[$key] = strtoupper($row['Person']);
            }

            if ($isGroup) {
                array_multisort($type, SORT_ASC, $group, SORT_NATURAL, $person, SORT_ASC, $resultList);
            } else {
                array_multisort($type, SORT_ASC, $division, SORT_NATURAL, $person, SORT_ASC, $resultList);
            }
        }

        return $resultList;
    }
}