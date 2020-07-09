<?php
namespace SPHERE\Application\Education\Lesson\Division\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionCustody;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroupFilter;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewSubjectTeacher;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Lesson\Division\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewDivision[]
     */
    public function viewDivision()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewDivision'
        );
    }

    /**
     * @return false|ViewDivisionStudent[]
     */
    public function viewDivisionStudent()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewDivisionStudent'
        );
    }

    /**
     * @return false|ViewDivisionTeacher[]
     */
    public function viewDivisionTeacher()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewDivisionTeacher'
        );
    }

    /**
     * @return false|ViewSubjectTeacher[]
     */
    public function viewSubjectTeacher()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewSubjectTeacher'
        );
    }

    /**
     * @return false|ViewDivisionSubject[]
     */
    public function viewDivisionSubject()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewDivisionSubject'
        );
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblType $tblType
     * @param         $Name
     * @param string $Description
     * @param bool $Checked
     *
     * @return null|object|TblLevel
     */
    public function createLevel(TblType $tblType, $Name, $Description = '', $Checked = false)
    {

        $Manager = $this->getConnection()->getEntityManager(false);
        $Entity = $Manager->getEntity('TblLevel')->findOneBy(array(
            TblLevel::ATTR_NAME => $Name,
            TblLevel::SERVICE_TBL_TYPE => $tblType->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblLevel();
            $Entity->setServiceTblType($tblType);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setIsChecked($Checked);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return $Entity;
    }

    /**
     * @param TblYear $tblYear
     * @param TblLevel $tblLevel
     * @param string $Name
     * @param string $Description
     * @param TblCompany|null $tblCompany
     *
     * @return null|object|TblDivision
     */
    public function createDivision(TblYear $tblYear, TblLevel $tblLevel, $Name, $Description = '', TblCompany $tblCompany = null)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivision')->findOneBy(array(
            TblDivision::ATTR_YEAR => $tblYear->getId(),
            TblDivision::ATTR_NAME => $Name,
            TblDivision::ATTR_LEVEL => ($tblLevel ? $tblLevel->getId() : null),
            'EntityRemove' => null
        ));

        if (null === $Entity) {
            $Entity = new TblDivision();
            $Entity->setServiceTblYear($tblYear);
            $Entity->setTblLevel($tblLevel);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setServiceTblCompany($tblCompany);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return $Entity;
    }

    /**
     * @param $Name
     * @param string $Description
     * @param null|boolean $IsAdvancedCourse
     * @return TblSubjectGroup
     */
    public function createSubjectGroup($Name, $Description = '', $IsAdvancedCourse = null)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblSubjectGroup();
        $Entity->setName($Name);
        $Entity->setDescription($Description);
        if ($IsAdvancedCourse !== null) {
            $Entity->setIsAdvancedCourse($IsAdvancedCourse);
        }
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel', $Id);
    }

    /**
     * @param $Name
     * @param TblType|null $tblType
     *
     * @return false|TblLevel[]
     */
    public function getLevelAllByName($Name, TblType $tblType = null)
    {

        if ($tblType) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel',
                array(
                    TblLevel::ATTR_NAME => $Name,
                    TblLevel::SERVICE_TBL_TYPE => $tblType->getId()
                ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel',
                array(
                    TblLevel::ATTR_NAME => $Name
                ));
        }
    }

    /**
     * @param int $Id
     * @param bool $IsForced
     *
     * @return bool|TblDivision
     */
    public function getDivisionById($Id, $IsForced = false)
    {

        if ($IsForced){
            return $this->getForceEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision', $Id);
        } else {
            return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision', $Id);
        }
    }

    /**
     * @return false|TblSubjectGroup[]
     */
    public function getSubjectGroupAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectGroup');
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectGroup
     */
    public function getSubjectGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectGroup',
            $Id);
    }

    /**
     * @param $Id
     *
     * @return false|Element
     */
    public function getDivisionSubjectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionSubject',
            $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionStudent
     */
    public function getDivisionStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionStudent',
            $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectStudent
     */
    public function getSubjectStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectStudent',
            $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectTeacher
     */
    public function getSubjectTeacherById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectTeacher',
            $Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectByDivision(TblDivision $tblDivision)
    {

        $EntityList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblDivisionSubject',
            array(
                TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId()
            ));

        if ($EntityList) {
            /** @var TblDivisionSubject $item */
            foreach ($EntityList as &$item) {
                if (!$item->getTblDivision() || !$item->getServiceTblSubject()) {
                    $item = false;
                }
            }
            $EntityList = array_filter($EntityList);
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblSubject $tblSubject
     * @param TblDivision $tblDivision
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectBySubjectAndDivision(TblSubject $tblSubject, TblDivision $tblDivision)
    {

        $EntityList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblDivisionSubject',
            array(
                TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
            ));

        if ($EntityList) {
            /** @var TblDivisionSubject $item */
            foreach ($EntityList as &$item) {
                if (!$item->getTblDivision() && !$item->getServiceTblSubject()) {
                    $item = false;
                }
            }
            $EntityList = array_filter($EntityList);
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblSubject $tblSubject
     * @param TblDivision $tblDivision
     *
     * @return false|TblDivisionSubject
     */
    public function getDivisionSubjectBySubjectAndDivisionWithoutGroup(TblSubject $tblSubject, TblDivision $tblDivision)
    {

        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblDivisionSubject',
            array(
                TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionSubject::ATTR_TBL_SUBJECT_GROUP => null,
            ));

        return $Entity;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getSubjectTeacherByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblSubjectTeacher',
            array(
                TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
            ));
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblSubjectStudent',
            array(
                TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
            ));
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param bool $withInActive
     * @return bool|TblPerson[]
     */
    public function getStudentByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject,
        $withInActive = false
    ) {

        $TempList = $this->getSubjectStudentByDivisionSubject($tblDivisionSubject);
        $tblDivision = $tblDivisionSubject->getTblDivision();

        $EntityList = array();
        if (!empty ($TempList)) {

            $isSorted = $this->isDivisionSorted($tblDivisionSubject->getTblDivision());
            if ($isSorted) {
                $max = $this->getDivisionStudentSortOrderMax($tblDivision);
            } else {
                $max = 1;
            }
            $count = 1;

            /** @var TblSubjectStudent $tblSubjectStudent */
            foreach ($TempList as $tblSubjectStudent) {
                if ($tblSubjectStudent->getServiceTblPerson()) {
                    if (($tblDivisionStudent = $this->getDivisionStudentByDivisionAndPerson(
                        $tblDivision, $tblSubjectStudent->getServiceTblPerson()
                    ))
                        && !$withInActive
                        && $tblDivisionStudent->isInActive()
                    ) {
                        continue;
                    }

                    if ($isSorted) {
                        if ($tblDivisionStudent) {
                            $key = $tblDivisionStudent->getSortOrder() !== null
                                ? $tblDivisionStudent->getSortOrder()
                                : $max + $count++;
                            $EntityList[$key] = $tblSubjectStudent->getServiceTblPerson();
                        }
                    } else {
                        $EntityList[$count++] = $tblSubjectStudent->getServiceTblPerson();
                    }
                }
            }

            if ($isSorted) {
                ksort($EntityList);
            } else {
                $EntityList = $this->getSorter($EntityList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
            }
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionByLevel(TblLevel $tblLevel)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblDivision',
            array(
                TblDivision::ATTR_LEVEL => $tblLevel->getId()
            ));
    }

    /**
     * @param TblLevel $tblLevel
     * @param TblYear  $tblYear
     *
     * @return false|TblDivision[]
     */
    public function getDivisionAllByLevelAndYear(TblLevel $tblLevel, TblYear $tblYear)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblDivision',
            array(
                TblDivision::ATTR_LEVEL => $tblLevel->getId(),
                TblDivision::ATTR_YEAR  => $tblYear->getId()
            ));
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return false|TblDivision[]
     */
    public function getDivisionAllByLevel(TblLevel $tblLevel)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblDivision',
            array(
                TblDivision::ATTR_LEVEL => $tblLevel->getId(),
            ));
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionByYear(TblYear $tblYear)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblDivision',
            array(
                TblDivision::ATTR_YEAR => $tblYear->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblSubjectStudent',
            array(
                TblSubjectStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param TblYear $tblYear
     * @param string $Name
     * @param TblLevel|null $tblLevel
     *
     * @return bool
     */
    public function checkDivisionExists(TblYear $tblYear, $Name, TblLevel $tblLevel = null)
    {

        if ($this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision',
            array(
                TblDivision::ATTR_YEAR => $tblYear->getId(),
                TblDivision::ATTR_NAME => $Name,
                TblDivision::ATTR_LEVEL => ($tblLevel ? $tblLevel->getId() : null),
            ))
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param string   $Name
     * @param TblLevel $tblLevel
     * @param TblYear  $tblYear
     *
     * @return false|TblDivision
     */
    public function getDivisionByDivisionNameAndLevelAndYear($Name, TblLevel $tblLevel, TblYear $tblYear)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision',
            array(
                TblDivision::ATTR_NAME  => $Name,
                TblDivision::ATTR_LEVEL => $tblLevel->getId(),
                TblDivision::ATTR_YEAR  => $tblYear->getId()
            ));

        return $Entity;
    }

    /**
     * @param string  $Name
     * @param TblYear $tblYear
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionByDivisionNameAndYear($Name, TblYear $tblYear)
    {
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision',
            array(
                TblDivision::ATTR_NAME => $Name,
                TblDivision::ATTR_YEAR => $tblYear->getId(),
            ));

        return $EntityList;
    }

    /**
     * @param TblLevel $tblLevel
     * @param TblYear  $tblYear
     *
     * @return false|TblDivision[]
     */
    public function getDivisionByLevelAndYear(TblLevel $tblLevel, TblYear $tblYear)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision',
            array(
                TblDivision::ATTR_LEVEL => $tblLevel->getId(),
                TblDivision::ATTR_YEAR  => $tblYear->getId(),
            ));

        return $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionTeacher
     */
    public function getDivisionTeacherByDivisionAndTeacher(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionTeacher',
            array(
                TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));
        return ($Entity ? $Entity : false);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionCustody
     */
    public function getDivisionCustodyByDivisionAndPerson(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionCustody',
            array(
                TblDivisionCustody::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionCustody::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));
        return ($Entity ? $Entity : false);
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return bool|TblLevel
     */
    public function checkSubjectExists($Name, $Description = '')
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel', array(
            TblSubjectGroup::ATTR_NAME => $Name,
            TblSubjectGroup::ATTR_DESCRIPTION => $Description
        ));
        return ($Entity ? $Entity : false);
    }

    /**
     * @param TblType $tblType
     * @param string $Name
     *
     * @return bool|TblLevel
     */
    public function checkLevelExists(TblType $tblType, $Name)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel', array(
            TblLevel::ATTR_NAME => $Name,
            TblLevel::SERVICE_TBL_TYPE => $tblType->getId()
        ));
        return ($Entity ? $Entity : false);
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return bool|TblSubjectGroup
     */
    public function checkSubjectGroupExists($Name, $Description)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectGroup',
            array(
                TblSubjectGroup::ATTR_NAME => $Name,
                TblSubjectGroup::ATTR_DESCRIPTION => $Description
            ));
        return ($Entity ? $Entity : false);
    }

    /**
     * @param TblType $serviceTblType
     *
     * @return bool|TblLevel[]
     */
    public function getLevelByServiceTblType(TblType $serviceTblType)
    {

        $Entity = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel',
            array(
                TblLevel::SERVICE_TBL_TYPE => $serviceTblType->getId(),
            ));
        return ($Entity ? $Entity : false);
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel');
    }

    /**
     * @return bool|TblDivision[]
     */
    public function getDivisionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision');
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool $withInActive
     *
     * @return bool|TblPerson[]
     */
    public function getStudentAllByDivision(TblDivision $tblDivision, $withInActive = false)
    {

        $TempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblDivisionStudent',
            array(
                TblDivisionStudent::ATTR_TBL_DIVISION => $tblDivision->getId()
            ));

        $EntityList = array();
        if (!empty ($TempList)) {
            // inaktive aussortieren
            if (!$withInActive) {
                $list = array();
                $now = new \DateTime('now');
                /** @var TblDivisionStudent $tblDivisionStudent */
                foreach ($TempList as $tblDivisionStudent) {
                    if ($tblDivisionStudent->getLeaveDateTime() !== null && $now > $tblDivisionStudent->getLeaveDateTime()) {

                    } else {
                        $list[] = $tblDivisionStudent;
                    }
                }

                $TempList = $list;
            }

            // ist Klassenliste sortiert
            $isSorted = false;
            /** @var TblDivisionStudent $tblDivisionStudent */
            foreach ($TempList as $tblDivisionStudent) {
                if ($tblDivisionStudent->getSortOrder() !== null) {
                    $isSorted = true;
                    break;
                }
            }

            if ($isSorted) {
                $TempList = $this->getSorter($TempList)->sortObjectBy('SortOrder');
            }

            /** @var TblDivisionStudent $tblDivisionStudent */
            foreach ($TempList as $tblDivisionStudent) {
                if ($tblDivisionStudent->getServiceTblPerson() && $tblDivisionStudent->getTblDivision()) {
                    array_push($EntityList, $tblDivisionStudent->getServiceTblPerson());
                }
            }

            if (!$isSorted) {
                $EntityList = $this->getSorter($EntityList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
            }
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getTeacherAllByDivision(TblDivision $tblDivision)
    {

        $TempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblDivisionTeacher',
            array(
                TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId()
            ), array('EntityCreate' => self::ORDER_ASC));

        $EntityList = array();
        if (!empty ($TempList)) {
            /** @var TblDivisionTeacher $tblDivisionTeacher */
            foreach ($TempList as $tblDivisionTeacher) {
                if ($tblDivisionTeacher->getServiceTblPerson() && $tblDivisionTeacher->getTblDivision()) {
                    array_push($EntityList, $tblDivisionTeacher->getServiceTblPerson());
                }
            }
        }
        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblDivision $tblDivisionCopy
     *
     * @return bool|TblDivisionTeacher[]
     */
    public function copyTeacherAllByDivision(TblDivision $tblDivision, TblDivision $tblDivisionCopy)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionTeacher')->findBy(array(
            TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId()
        ));

        if (!empty ($EntityList)) {
            /** @var TblDivisionTeacher $singleEntity */
            foreach ($EntityList as $singleEntity) {
                if ($singleEntity->getServiceTblPerson()) {
                    $Entity = new TblDivisionTeacher();
                    $Entity->setTblDivision($tblDivisionCopy);
                    $Entity->setServiceTblPerson($singleEntity->getServiceTblPerson());
                    $Entity->setDescription($singleEntity->getDescription());
                    $Manager->saveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
                }
            }
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getCustodyAllByDivision(TblDivision $tblDivision)
    {

        $TempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblDivisionCustody',
            array(
                TblDivisionCustody::ATTR_TBL_DIVISION => $tblDivision->getId()
            ));

//        $TempList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionCustody')->findBy(array(
//            TblDivisionCustody::ATTR_TBL_DIVISION => $tblDivision->getId()
//        ));
        $EntityList = array();

        if (!empty ($TempList)) {
            /** @var TblDivisionCustody $tblDivisionCustody */
            foreach ($TempList as $tblDivisionCustody) {
                if ($tblDivisionCustody->getServiceTblPerson()) {
                    array_push($EntityList, $tblDivisionCustody->getServiceTblPerson());
                }
            }
        }
        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblDivision $tblDivisionCopy
     *
     * @return bool|TblDivisionCustody[]
     */
    public function copyCustodyAllByDivision(TblDivision $tblDivision, TblDivision $tblDivisionCopy)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionCustody')->findBy(array(
            TblDivisionCustody::ATTR_TBL_DIVISION => $tblDivision->getId()
        ));

        if (!empty ($EntityList)) {
            /** @var TblDivisionCustody $singleEntity */
            foreach ($EntityList as $singleEntity) {
                if ($singleEntity->getServiceTblPerson()) {
                    $Entity = new TblDivisionCustody();
                    $Entity->setTblDivision($tblDivisionCopy);
                    $Entity->setServiceTblPerson($singleEntity->getServiceTblPerson());
                    $Entity->setDescription($singleEntity->getDescription());
                    $Manager->saveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
                }
            }
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblPerson[]
     */
    public function getTeacherAllByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $TempList = $this->getConnection()->getEntityManager()->getEntity('TblSubjectTeacher')->findBy(array(
            TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
        ));
        $EntityList = array();

        if (!empty ($TempList)) {
            /** @var TblSubjectTeacher $tblSubjectTeacher */
            foreach ($TempList as $tblSubjectTeacher) {
                if ($tblSubjectTeacher->getServiceTblPerson()) {
                    array_push($EntityList, $tblSubjectTeacher->getServiceTblPerson());
                }
            }
        }
        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblSubject[]
     */
    public function getSubjectAllByDivision(TblDivision $tblDivision)
    {

        $TempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblDivisionSubject',
            array(
                TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId()
            ));

//        $TempList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionSubject')->findBy(array(
//            TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId()
//        ));
        $EntityList = array();

        if (!empty ($TempList)) {
            /** @var TblDivisionSubject $tblDivisionSubject */
            foreach ($TempList as $tblDivisionSubject) {
                if (!$tblDivisionSubject->getTblSubjectGroup()) {
                    if ($tblDivisionSubject->getServiceTblSubject()) {
                        array_push($EntityList, $tblDivisionSubject->getServiceTblSubject());
                    }
                }
            }
        }
        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param null|integer $SortOrder
     *
     * @return TblDivisionStudent
     */
    public function addDivisionStudent(TblDivision $tblDivision, TblPerson $tblPerson, $SortOrder = null)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionStudent')
            ->findOneBy(array(
                TblDivisionStudent::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblDivisionStudent();
            $Entity->setTblDivision($tblDivision);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setSortOrder($SortOrder);
            $Entity->setUseGradesInNewDivision(false);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param null $Description
     *
     * @return null|object|TblDivisionTeacher
     */
    public function addDivisionTeacher(TblDivision $tblDivision, TblPerson $tblPerson, $Description = null)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionTeacher')
            ->findOneBy(array(
                TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblDivisionTeacher();
            $Entity->setTblDivision($tblDivision);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param null $Description
     *
     * @return null|object|TblDivisionCustody
     */
    public function addDivisionCustody(TblDivision $tblDivision, TblPerson $tblPerson, $Description = null)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionCustody')
            ->findOneBy(array(
                TblDivisionCustody::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionCustody::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblDivisionCustody();
            $Entity->setTblDivision($tblDivision);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param bool $HasGrading
     *
     * @return null|object|TblDivisionSubject
     */
    public function addDivisionSubject(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        $HasGrading = true
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        if ($tblSubjectGroup === null) {
            $Entity = $Manager->getEntity('TblDivisionSubject')
                ->findOneBy(array(
                    TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
                    TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblDivisionSubject::ATTR_TBL_SUBJECT_GROUP => null
                ));
        } else {
            $Entity = $Manager->getEntity('TblDivisionSubject')
                ->findOneBy(array(
                    TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
                    TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblDivisionSubject::ATTR_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
                ));
        }

        if (null === $Entity) {
            $Entity = new TblDivisionSubject();
            $Entity->setTblDivision($tblDivision);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setTblSubjectGroup($tblSubjectGroup);
            $Entity->setHasGrading($HasGrading);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return null|object|TblSubjectStudent
     */
    public function addSubjectStudent(TblDivisionSubject $tblDivisionSubject, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSubjectStudent')
            ->findOneBy(array(
                TblSubjectStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblSubjectStudent();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setTblDivisionSubject($tblDivisionSubject);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     *
     * @return null|object|TblSubjectTeacher
     */
    public function addSubjectTeacher(TblDivisionSubject $tblDivisionSubject, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSubjectTeacher')
            ->findOneBy(array(
                TblSubjectTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblSubjectTeacher();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setTblDivisionSubject($tblDivisionSubject);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param array $SubjectTeacherList [tblPerson => $tblPerson, tblDivisionSubject => $tblDivisionSubject]
     *
     * @return bool
     */
    public function addSubjectTeacherList($SubjectTeacherList)
    {

        $Manager = $this->getConnection()->getEntityManager();

        if ($SubjectTeacherList) {
            foreach ($SubjectTeacherList as $Content) {
                /** @var TblDivisionSubject $tblDivisionSubject */
                $tblDivisionSubject = $Content['tblDivisionSubject'];
                /** @var TblPerson $tblPerson */
                $tblPerson = $Content['tblPerson'];
                $Entity = $Manager->getEntity('TblSubjectTeacher')
                    ->findOneBy(array(
                        TblSubjectTeacher::ATTR_SERVICE_TBL_PERSON   => $tblPerson->getId(),
                        TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
                    ));

                if (null === $Entity) {
                    $Entity = new TblSubjectTeacher();
                    $Entity->setServiceTblPerson($tblPerson);
                    $Entity->setTblDivisionSubject($tblDivisionSubject);
                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param array $SubjectStudentList [tblPerson => $tblPerson, tblDivisionSubject => $tblDivisionSubject]
     *
     * @return bool
     */
    public function addSubjectStudentList($SubjectStudentList)
    {

        $Manager = $this->getConnection()->getEntityManager();

        if ($SubjectStudentList) {
            foreach ($SubjectStudentList as $Content) {
                /** @var TblDivisionSubject $tblDivisionSubject */
                $tblDivisionSubject = $Content['tblDivisionSubject'];
                /** @var TblPerson $tblPerson */
                $tblPerson = $Content['tblPerson'];
                $Entity = $Manager->getEntity('TblSubjectStudent')
                    ->findOneBy(array(
                        TblSubjectStudent::ATTR_SERVICE_TBL_PERSON   => $tblPerson->getId(),
                        TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
                    ));

                if (null === $Entity) {
                    $Entity = new TblSubjectStudent();
                    $Entity->setServiceTblPerson($tblPerson);
                    $Entity->setTblDivisionSubject($tblDivisionSubject);
                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeStudentToDivision(TblDivision $tblDivision, TblPerson $tblPerson, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionStudent')
            ->findOneBy(array(
                TblDivisionStudent::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null !== $Entity) {
            /** @var TblDivisionStudent $Entity */
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeTeacherToDivision(TblDivision $tblDivision, TblPerson $tblPerson, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionTeacher')
            ->findOneBy(array(
                TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null !== $Entity) {
            /** @var TblDivisionTeacher $Entity */
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removePersonToDivision(TblDivision $tblDivision, TblPerson $tblPerson, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionCustody')
            ->findOneBy(array(
                TblDivisionCustody::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionCustody::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null !== $Entity) {
            /** @var TblDivisionCustody $Entity */
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function removeSubjectToDivision(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblDivisionSubject')
            ->findBy(array(
                TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
            ));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                $Manager->killEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblDivisionSubject', $tblDivisionSubject->getId());
        if (null !== $Entity) {
            /** @var TblDivisionSubject $Entity */
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectStudent $tblSubjectStudent
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeSubjectStudent(TblSubjectStudent $tblSubjectStudent, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblSubjectStudent', $tblSubjectStudent->getId());
        if (null !== $Entity) {
            /** @var TblSubjectStudent $Entity */
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectStudent[] $tblSubjectStudentList
     *
     * @return string
     */
    public function removeSubjectStudentBulk(
        $tblSubjectStudentList = array()
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        if (!empty($tblSubjectStudentList)) {
            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                $Entity = $Manager->getEntityById('TblSubjectStudent', $tblSubjectStudent->getId());
                if (null !== $Entity) {
                    /** @var TblSubjectStudent $Entity */
                    $Manager->bulkKillEntity($Entity);
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                }
            }

            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeSubjectStudentByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblSubjectStudent')->findBy(
            array(TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                /** @var Element $Entity */
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeSubjectTeacherByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblSubjectTeacher')->findBy(
            array(TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                /** @var Element $Entity */
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectTeacher $tblSubjectTeacher
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeSubjectTeacher(TblSubjectTeacher $tblSubjectTeacher, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblSubjectTeacher', $tblSubjectTeacher->getId());
        if (null !== $Entity) {
            /** @var TblSubjectTeacher $Entity */
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectTeacher[] $tblSubjectTeacherList
     *
     * @return bool
     */
    public function removeSubjectTeacherList($tblSubjectTeacherList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if ($tblSubjectTeacherList) {
            foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                $Entity = $Manager->getEntityById('TblSubjectTeacher', $tblSubjectTeacher->getId());
                if (null !== $Entity) {
                    /** @var TblSubjectTeacher $Entity */
                    $Manager->bulkKillEntity($Entity);
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function removeSubjectGroup(TblSubjectGroup $tblSubjectGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblSubjectGroup', $tblSubjectGroup->getId());
        if (null !== $Entity) {
            /** @var TblSubjectGroup $Entity */
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param             $Name
     * @param string $Description
     * @param TblCompany|null $tblCompany
     *
     * @return bool
     */
    public function updateDivision(TblDivision $tblDivision, $Name, $Description = '', TblCompany $tblCompany = null)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDivision $Entity */
        $Entity = $Manager->getEntityById('TblDivision', $tblDivision->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setServiceTblCompany($tblCompany);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblLevel $tblLevel
     * @param TblType $tblType
     * @param string $Name
     * @param string $Description
     *
     * @return bool
     */
    public function updateLevel(TblLevel $tblLevel, TblType $tblType, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblLevel $Entity */
        $Entity = $Manager->getEntityById('TblLevel', $tblLevel->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblType($tblType);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param string $Name
     * @param string $Description
     * @param null|boolean $IsAdvancedCourse
     *
     * @return bool
     */
    public function updateSubjectGroup(TblSubjectGroup $tblSubjectGroup, $Name, $Description = '', $IsAdvancedCourse = null)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSubjectGroup $Entity */
        $Entity = $Manager->getEntityById('TblSubjectGroup', $tblSubjectGroup->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            if ($IsAdvancedCourse !== null) {
                $Entity->setIsAdvancedCourse($IsAdvancedCourse);
            }
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool
     */
    public function destroyDivision(TblDivision $tblDivision)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivision')->findOneBy(array('Id' => $tblDivision->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return bool
     */
    public function destroyLevel(TblLevel $tblLevel)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblLevel')->findOneBy(array('Id' => $tblLevel->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function destroySubjectGroup(TblSubjectGroup $tblSubjectGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblSubjectGroup', $tblSubjectGroup->getId());
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblDivisionStudent[]
     */
    public function getDivisionStudentAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            $EntityList = $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDivisionStudent', array(
                    TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        } else {
            $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDivisionStudent', array(
                    TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        }

        if ($EntityList) {
            /** @var TblDivisionStudent $item */
            foreach ($EntityList as &$item) {
                if (!$item->getTblDivision()) {
                    $item = false;
                }
            }
            $EntityList = array_filter($EntityList);
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionStudentAllByDivision(TblDivision $tblDivision)
    {

        if (($tblStudentList = $this->getStudentAllByDivision($tblDivision))) {
            return count($tblStudentList);
        }

        return 0;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionTeacherAllByDivision(TblDivision $tblDivision)
    {

        // Todo GCK getCachedCountBy anpassen --> ignorieren von removed entities bei Verkn�pfungstabelle
//        $result = $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionTeacher',
//            array(TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId()));
//
//        return $result ? $result : 0;

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDivisionTeacher',
            array(
                TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId()
            ));

        if ($EntityList) {
            $count = 0;
            /** @var TblDivisionTeacher $item */
            foreach ($EntityList as &$item) {
                if ($item->getServiceTblPerson() && $item->getTblDivision()) {
                    $count++;
                }
            }
            return $count;
        } else {
            return 0;
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionCustodyAllByDivision(TblDivision $tblDivision)
    {
        // Todo GCK getCachedCountBy anpassen --> ignorieren von removed entities bei Verkn�pfungstabelle
//        $result = $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionCustody',
//            array(TblDivisionCustody::ATTR_TBL_DIVISION => $tblDivision->getId()));
//
//        return $result ? $result : 0;

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDivisionCustody',
            array(
                TblDivisionCustody::ATTR_TBL_DIVISION => $tblDivision->getId()
            ));

        if ($EntityList) {
            $count = 0;
            /** @var TblDivisionCustody $item */
            foreach ($EntityList as &$item) {
                if ($item->getServiceTblPerson()) {
                    $count++;
                }
            }
            return $count;
        } else {
            return 0;
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionSubjectAllByDivision(TblDivision $tblDivision)
    {
        // Todo GCK getCachedCountBy anpassen --> ignorieren von removed entities bei Verkn�pfungstabelle
//        $result = $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionSubject',
//            array(TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId()));
//
//        return $result ? $result : 0;

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDivisionSubject',
            array(
                TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId()
            ));

        if ($EntityList) {
            $count = 0;
            /** @var TblDivisionSubject $item */
            foreach ($EntityList as &$item) {
                if ($item->getServiceTblSubject() && $item->getTblDivision()) {
                    $count++;
                }
            }
            return $count;
        } else {
            return 0;
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionSubjectGroupByDivision(TblDivision $tblDivision)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDivisionSubject',
            array(TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId()));
        $result = 0;
        if ($EntityList) {
            /** @var TblDivisionSubject $Entity */
            foreach ($EntityList as $Entity) {
                if ($Entity->getTblSubjectGroup()) {
                    $result = $result + 1;
                }
            }
        }
        return $result;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
        TblDivision $tblDivision,
        TblSubject $tblSubject
    ) {

        $resultList = array();
        $tempList = $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDivisionSubject',
            array(
                TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
            )
        );

        if ($tempList) {
            /** @var TblDivisionSubject $tblDivisionSubject */
            foreach ($tempList as $tblDivisionSubject) {
                if ($tblDivisionSubject->getTblSubjectGroup()) {
                    $resultList[] = $tblDivisionSubject;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getSubjectTeacherAllByTeacher(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectTeacher',
            array(
                TblSubjectTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|TblDivisionSubject
     */
    public function getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        if ($tblSubjectGroup === null) {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDivisionSubject',
                array(
                    TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
                    TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblDivisionSubject::ATTR_TBL_SUBJECT_GROUP => null
                )
            );
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDivisionSubject',
                array(
                    TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
                    TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblDivisionSubject::ATTR_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
                )
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionTeacher[]
     */
    public function getDivisionTeacherAllByTeacher(TblPerson $tblPerson)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDivisionTeacher',
            array(
                TblDivisionTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        );

        if ($EntityList) {
            /** @var TblDivisionTeacher $item */
            foreach ($EntityList as &$item) {
                if (!$item->getTblDivision()) {
                    $item = false;
                }
            }
            $EntityList = array_filter($EntityList);
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectStudent
     */
    public function getSubjectStudentByDivisionSubjectAndPerson(
        TblDivisionSubject $tblDivisionSubject,
        TblPerson $tblPerson
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectStudent',
            array(
                TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
                TblSubjectStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        );
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return int
     */
    public function countSubjectStudentByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        // Todo GCK getCachedCountBy anpassen --> ignorieren von removed entities bei Verkn�pfungstabelle
//        $count = $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectStudent',
//            array(
//                TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
//            )
//        );
//
//        return $count ? $count : 0;

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSubjectStudent',
            array(
                TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
            ));

        if ($EntityList) {
            $count = 0;
            /** @var TblSubjectStudent $item */
            foreach ($EntityList as &$item) {
                if ($item->getServiceTblPerson()) {
                    $count++;
                }
            }
            return $count;
        } else {
            return 0;
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function existsDivisionStudent(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionStudent',
            array(
                TblDivisionStudent::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        ) ? true : false;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function exitsSubjectStudent(TblDivisionSubject $tblDivisionSubject, TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectStudent',
            array(
                TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
                TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        ) ? true : false;
    }

    /**
     * @param TblDivisionStudent $tblDivisionStudent
     * @param integer $SortOrder
     *
     * @return bool
     */
    public function updateDivisionStudentSortOrder(TblDivisionStudent $tblDivisionStudent, $SortOrder)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDivisionStudent $Entity */
        $Entity = $Manager->getEntityById('TblDivisionStudent', $tblDivisionStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setSortOrder($SortOrder);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivisionStudent $tblDivisionStudent
     * @param \DateTime|null $LeaveDate
     * @param bool $UseGradesInNewDivision
     *
     * @return bool
     */
    public function updateDivisionStudentActivation(TblDivisionStudent $tblDivisionStudent, \DateTime $LeaveDate = null, $UseGradesInNewDivision = true)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDivisionStudent $Entity */
        $Entity = $Manager->getEntityById('TblDivisionStudent', $tblDivisionStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setLeaveDate($LeaveDate);
            $Entity->setUseGradesInNewDivision($UseGradesInNewDivision);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool $withInActive
     *
     * @return bool|TblDivisionStudent[]
     */
    public function getDivisionStudentAllByDivision(TblDivision $tblDivision, $withInActive = false)
    {

        $tempList = array();
        if (($tblStudentAll = $this->getStudentAllByDivision($tblDivision, $withInActive))) {
            foreach ($tblStudentAll as $tblPerson){
                if (($item = $this->getDivisionStudentByDivisionAndPerson($tblDivision, $tblPerson) )){
                    $tempList[] = $item;
                }
            }
        };

        return empty($tempList) ? false : $tempList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int|null
     */
    public function getDivisionStudentSortOrderMax(TblDivision $tblDivision)
    {

        $list = $this->getDivisionStudentAllByDivision($tblDivision, true);
        $max = 0;
        if ($list) {
            $max = 0;
            foreach ($list as $tblDivisionStudent) {
                if ($tblDivisionStudent->getSortOrder() !== null
                    && $tblDivisionStudent->getSortOrder() > $max
                ) {
                    $max = $tblDivisionStudent->getSortOrder();
                }
            }
        }

        return $max;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool
     */
    private function isDivisionSorted(TblDivision $tblDivision)
    {

        $TempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblDivisionStudent',
            array(
                TblDivisionStudent::ATTR_TBL_DIVISION => $tblDivision->getId()
            )
        );
        if ($TempList) {
            /** @var TblDivisionStudent $tblDivisionStudent */
            foreach ($TempList as $tblDivisionStudent) {
                if ($tblDivisionStudent->getSortOrder() !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return false|TblDivisionStudent
     */
    public function getDivisionStudentByDivisionAndPerson(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionStudent',
            array(
                TblDivisionStudent::ATTR_TBL_DIVISION => $tblDivision->getId(),
                TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @return false|TblDivisionCustody[]
     */
    public function getDivisionCustodyAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionCustody',
            array(
                TblDivisionCustody::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @return false|TblDivisionTeacher[]
     */
    public function getDivisionTeacherAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionTeacher',
            array(
                TblDivisionTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @return false|TblSubjectTeacher[]
     */
    public function getSubjectTeacherAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectTeacher',
            array(
                TblSubjectTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @return false|TblSubjectStudent[]
     */
    public function getSubjectStudentAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectStudent',
            array(
                TblSubjectStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            )
        );
    }

    /**
     * @param TblType $serviceTblType
     * @param $Name
     *
     * @return bool|TblLevel
     */
    public function getLevelBy(TblType $serviceTblType, $Name)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel',
            array(
                TblLevel::SERVICE_TBL_TYPE => $serviceTblType->getId(),
                TblLevel::ATTR_NAME => $Name
            ));
        return ($Entity ? $Entity : false);
    }

    /**
     * Bei Gruppen, ohne Ohne-Gruppe
     *
     * @param TblDivision $tblDivision
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectListByDivision(TblDivision $tblDivision)
    {

        $EntityList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblDivisionSubject',
            array(
                TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId()
            ));

        $resultList = array();
        if ($EntityList) {
            /** @var TblDivisionSubject $item */
            foreach ($EntityList as $item) {
                if ($item->getTblDivision() && $item->getServiceTblSubject()) {
                    if ($item->getTblSubjectGroup()) {
                        $resultList[] = $item;
                    } else {
                        if (!$this->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                            $item->getTblDivision(), $item->getServiceTblSubject()
                        )
                        ) {
                            $resultList[] = $item;
                        }
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblDivision[]
     */
    public function getDivisionAllByYear(TblYear $tblYear) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivision',
            array(TblDivision::ATTR_YEAR => $tblYear->getId()));
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param $HasGrading
     *
     * @return bool
     */
    public function updateDivisionSubject(TblDivisionSubject $tblDivisionSubject, $HasGrading)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDivisionSubject $Entity */
        $Entity = $Manager->getEntityById('TblDivisionSubject', $tblDivisionSubject->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setHasGrading($HasGrading);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param $field
     *
     * @return false|TblSubjectGroupFilter
     * @throws \Exception
     */
    public function getSubjectGroupFilterBy(TblSubjectGroup $tblSubjectGroup, $field)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblSubjectGroupFilter', array(
            TblSubjectGroupFilter::ATTR_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
            TblSubjectGroupFilter::ATTR_FIELD => $field
        ));
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return false|TblSubjectGroupFilter[]
     */
    public function getSubjectGroupFilterAllBySubjectGroup(TblSubjectGroup $tblSubjectGroup)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSubjectGroupFilter', array(
            TblSubjectGroupFilter::ATTR_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
        ));
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param $field
     * @param $value
     *
     * @return null|TblSubjectGroupFilter
     */
    public function createSubjectGroupFilter(TblSubjectGroup $tblSubjectGroup, $field, $value)
    {

        $Manager = $this->getConnection()->getEntityManager(false);
        $Entity = $Manager->getEntity('TblSubjectGroupFilter')->findOneBy(array(
            TblSubjectGroupFilter::ATTR_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
            TblSubjectGroupFilter::ATTR_FIELD => $field
        ));
        /** @var TblSubjectGroupFilter $Entity */
        if (null === $Entity) {
            $Entity = new TblSubjectGroupFilter();
            $Entity->setTblSubjectGroup($tblSubjectGroup);
            $Entity->setField($field);
            $Entity->setValue($value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

            return $Entity;
        }

        return $Entity;
    }

    /**
     * @param TblSubjectGroupFilter $tblSubjectGroupFilter
     * @param $value
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function updateSubjectGroupFilter(TblSubjectGroupFilter $tblSubjectGroupFilter, $value)
    {

        $Manager = $this->getConnection()->getEntityManager(false);

        /** @var TblSubjectGroupFilter $Entity */
        $Entity = $Manager->getEntityById('TblSubjectGroupFilter', $tblSubjectGroupFilter->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSubjectGroupFilter $tblSubjectGroupFilter
     *
     * @return bool
     */
    public function destroySubjectGroupFilter(TblSubjectGroupFilter $tblSubjectGroupFilter)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSubjectGroupFilter')->findOneBy(array('Id' => $tblSubjectGroupFilter->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson[] $personData
     *
     * @return bool
     */
    public function addAllAvailableStudentsToSubjectGroup(TblDivisionSubject $tblDivisionSubject, $personData)
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($personData as $tblPerson) {
            $Entity = $Manager->getEntity('TblSubjectStudent')
                ->findOneBy(array(
                    TblSubjectStudent::ATTR_SERVICE_TBL_PERSON   => $tblPerson->getId(),
                    TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
                ));

            if (null === $Entity) {
                $Entity = new TblSubjectStudent();
                $Entity->setServiceTblPerson($tblPerson);
                $Entity->setTblDivisionSubject($tblDivisionSubject);

                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeAllSelectedStudentsFromSubjectGroup(TblDivisionSubject $tblDivisionSubject)
    {
        $Manager = $this->getConnection()->getEntityManager();

        if (($tblStudentSubjectList = $this->getSubjectStudentByDivisionSubject($tblDivisionSubject))) {
            foreach ($tblStudentSubjectList as $tblSubjectStudent) {
                $Entity = $Manager->getEntityById('TblSubjectStudent', $tblSubjectStudent->getId());
                if (null !== $Entity) {
                    /** @var TblSubjectStudent $Entity */
                    $Manager->bulkKillEntity($Entity);
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                }
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeSubjectGroupFilterByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        if (($tblSubjectGroup =$tblDivisionSubject->getTblSubjectGroup())) {
            $Manager = $this->getConnection()->getEntityManager();
            $EntityList = $Manager->getEntity('TblSubjectGroupFilter')->findBy(
                array(
                    TblSubjectGroupFilter::ATTR_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
                )
            );
            if ($EntityList) {
                foreach ($EntityList as $Entity) {
                    /** @var Element $Entity */
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                        $Entity);
                    $Manager->killEntity($Entity);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblDivisionStudent $tblDivisionStudent
     *
     * @return bool
     */
    public function restoreDivisionStudent(TblDivisionStudent $tblDivisionStudent)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDivisionStudent $Entity */
        $Entity = $Manager->getEntityById('TblDivisionStudent', $tblDivisionStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDivisionTeacher[]
     */
    public function getDivisionTeacherAllByDivision(TblDivision $tblDivision)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionTeacher', array(
            TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId()
        ));
    }
}
