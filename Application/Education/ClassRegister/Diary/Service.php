<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use SPHERE\Application\Education\ClassRegister\Diary\Service\Data;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryDivision;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryStudent;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Setup;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Diary
 */
class Service extends AbstractService
{

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
     * @param $Id
     *
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return (new Data($this->getBinding()))->getDiaryById($Id);
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool $withPredecessorDivision
     *
     * @return false|TblDiary[]
     */
    public function getDiaryAllByDivision(TblDivision $tblDivision, $withPredecessorDivision = false)
    {
        if ($withPredecessorDivision) {
            $divisionList = array();
            $resultList = array();
            $this->getPredecessorDivisionList($tblDivision, $divisionList);
            /** @var TblDivision $tblDivisionItem */
            foreach ($divisionList as $tblDivisionItem) {
                if (($list = $this->getDiaryAllByDivision($tblDivisionItem, false))) {
                    $resultList = array_merge($resultList, $list);
                }
            }

            return $resultList;
        } else {
            return (new Data($this->getBinding()))->getDiaryAllByDivision($tblDivision);
        }
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return false|TblGroup[]
     */
    public function getDiaryAllByGroup(TblGroup $tblGroup)
    {
        return (new Data($this->getBinding()))->getDiaryAllByGroup($tblGroup);
    }

    /**
     * @param TblDivision $tblDivision
     * @param $resultList
     */
    private function getPredecessorDivisionList(TblDivision $tblDivision, &$resultList) {
        $resultList[$tblDivision->getId()] = $tblDivision;
        if (($tblDiaryDivisionList = $this->getDiaryDivisionByDivision($tblDivision))) {
            foreach ($tblDiaryDivisionList as $tblDiaryDivision) {
                if (($tblPredecessorDivision = $tblDiaryDivision->getServiceTblPredecessorDivision())) {
                    $this->getPredecessorDivisionList($tblPredecessorDivision, $resultList);
                }
            }
        }
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByDiary(TblDiary $tblDiary)
    {
        return (new Data($this->getBinding()))->getDiaryStudentAllByDiary($tblDiary);
    }

    /**
     * @param $Data
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblDiary|null $tblDiary
     *
     * @return bool|Form
     */
    public function checkFormDiary(
        $Data,
        TblDivision $tblDivision = null,
        TblGroup $tblGroup = null,
        TblDiary $tblDiary = null
    ) {
        $error = false;

        $form = Diary::useFrontend()->formDiary(
            $tblDivision ? $tblDivision : null, $tblGroup ? $tblGroup : null, $tblDiary ? $tblDiary->getId() : null
        );
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            $form->setSuccess('Data[Date]');
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblDivision $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return bool
     */
    public function createDiary($Data, TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        $tblPerson = false;
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount))
        ) {
            $tblPerson = $tblPersonAllByAccount[0];
        }

        if ($tblPerson) {
            if ($tblDivision) {
                $tblYear = $tblDivision->getServiceTblYear();
            } elseif ($tblGroup) {
                if (($tblYearList = Term::useService()->getYearByNow())) {
                    $tblYear = reset($tblYearList);
                } else {
                    $tblYear = false;
                }
            } else {
                $tblYear = false;
            }

            $tblDiary = (new Data($this->getBinding()))->createDiary(
                $Data['Subject'],
                $Data['Content'],
                $Data['Date'],
                $Data['Place'],
                $tblPerson,
                $tblYear ? $tblYear : null,
                $tblDivision ? $tblDivision : null,
                $tblGroup ? $tblGroup : null
            );

            if ($tblDiary) {
                if (isset($Data['Students'])) {
                    foreach($Data['Students'] as $personId => $value) {
                        if (($tblPersonItem = Person::useService()->getPersonById($personId))) {
                            (new Data($this->getBinding()))->addDiaryStudent($tblDiary, $tblPersonItem);
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param TblDiary $tblDiary
     * @param $Data
     *
     * @return bool
     */
    public function updateDiary(TblDiary $tblDiary, $Data)
    {
        $tblPerson = false;
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount))
        ) {
            $tblPerson = $tblPersonAllByAccount[0];
        }

        if ($tblPerson) {
            $tblDivision = $tblDiary->getServiceTblDivision();
            $tblGroup = $tblDiary->getServiceTblGroup();
            $tblYear = $tblDiary->getServiceTblYear();

            (new Data($this->getBinding()))->updateDiary(
                $tblDiary,
                $Data['Subject'],
                $Data['Content'],
                $Data['Date'],
                $Data['Place'],
                $tblPerson,
                $tblYear ? $tblYear : null,
                $tblDivision ? $tblDivision : null,
                $tblGroup ? $tblGroup : null
            );

            if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
                foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                    if (($tblPersonRemove = $tblDiaryStudent->getServiceTblPerson())
                        && !isset($Data['Students'][$tblPersonRemove->getId()])
                    ) {
                        (new Data($this->getBinding()))->removeDiaryStudent($tblDiaryStudent);
                    }
                }
            }

            if (isset($Data['Students'])) {
                foreach($Data['Students'] as $personId => $value) {
                    if (($tblPersonAdd = Person::useService()->getPersonById($personId))) {
                        (new Data($this->getBinding()))->addDiaryStudent($tblDiary, $tblPersonAdd);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return bool
     */
    public function destroyDiary(TblDiary $tblDiary)
    {
        if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
            foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                (new Data($this->getBinding()))->removeDiaryStudent($tblDiaryStudent);
            }
        }

        return (new Data($this->getBinding()))->destroyDiary($tblDiary);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblDivision $tblPredecessorDivision
     *
     * @return TblDiaryDivision
     */
    public function addDiaryDivision(TblDivision $tblDivision, TblDivision $tblPredecessorDivision)
    {
        return (new Data($this->getBinding()))->addDiaryDivision($tblDivision, $tblPredecessorDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDiaryDivision[]
     */
    private function getDiaryDivisionByDivision(TblDivision $tblDivision)
    {
        return (new Data($this->getBinding()))->getDiaryDivisionByDivision($tblDivision);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblDiary[]|bool
     */
    public function getDiaryAllByStudent(TblPerson $tblPerson)
    {
        $resultList = array();
        if (($tblDiaryStudentList = (new Data($this->getBinding()))->getDiaryStudentAllByStudent($tblPerson))) {
            foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                if (($tblDiary = $tblDiaryStudent->getTblDiary())) {
                    $resultList[$tblDiary->getId()] = $tblDiary;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }
}