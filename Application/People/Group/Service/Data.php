<?php
namespace SPHERE\Application\People\Group\Service;

use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\ColumnHydrator;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Group\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewPeopleGroupMember[]
     */
    public function viewPeopleGroupMember()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewPeopleGroupMember'
        );
    }
    
    public function setupDatabaseContent()
    {

        $this->createGroup('Alle', 'Personendaten', '', true, 'COMMON');
        $this->createGroup('Interessent', 'Schüler die zur Aufnahme vorgemerkt sind', '', true, 'PROSPECT');
        $this->createGroup('Schüler', 'Alle aktiv verfügbaren Schüler', '', true, 'STUDENT');
        $this->createGroup('Sorgeberechtigt', '', '', true, 'CUSTODY');
        $this->createGroup('Mitarbeiter', 'Alle Mitarbeiter', '', true, 'STAFF');
        $this->createGroup('Lehrer', 'Alle Mitarbeiter, welche einer Lehrtätigkeit nachgehen', '', true, 'TEACHER');
        $this->createGroup('Vereinsmitglieder', '', '', true, TblGroup::META_TABLE_CLUB);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $Remark
     * @param bool   $IsLocked
     * @param string $MetaTable
     *
     * @return TblGroup
     */
    public function createGroup($Name, $Description, $Remark, $IsLocked = false, $MetaTable = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblGroup')->findOneBy(array(
                TblGroup::ATTR_IS_LOCKED  => $IsLocked,
                TblGroup::ATTR_META_TABLE => $MetaTable
            ));
        } else {
            $Entity = $Manager->getEntity('TblGroup')->findOneBy(array(
                TblGroup::ATTR_NAME => $Name
            ));
        }

        if (null === $Entity) {
            $Entity = new TblGroup($Name);
            $Entity->setDescription($Description);
            $Entity->setRemark($Remark);
            $Entity->setLocked($IsLocked);
            $Entity->setMetaTable($MetaTable);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGroup $tblGroup
     * @param string   $Name
     * @param string   $Description
     * @param string   $Remark
     *
     * @return bool
     */
    public function updateGroup(TblGroup $tblGroup, $Name, $Description, $Remark)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblGroup $Entity */
        $Entity = $Manager->getEntityById('TblGroup', $tblGroup->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return TblGroup[]|bool
     */
    public function getGroupAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup');
    }

    /**
     * @return TblMember[]|bool
     */
    public function getMemberAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember');
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblMember
     */
    public function getMemberById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGroup
     */
    public function getGroupByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGroup')
            ->findOneBy(array(TblGroup::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $MetaTable
     *
     * @return bool|TblGroup
     */
    public function getGroupByMetaTable($MetaTable)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGroup')
            ->findOneBy(array(
                TblGroup::ATTR_META_TABLE => $MetaTable,
                TblGroup::ATTR_IS_LOCKED  => true
            ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByGroup(TblGroup $tblGroup)
    {

        $EntityList = $this->getMemberAllByGroup($tblGroup);
        if ($EntityList) {
            array_walk($EntityList, function (TblMember &$V) {

                $V = $V->getServiceTblPerson();
            });
            $EntityList = array_filter($EntityList);
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return false|TblMember[]
     */
    public function getMemberAllByGroup(TblGroup $tblGroup)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember',
            array(
                TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
            ));
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAllHavingNoGroup()
    {

        $Exclude = $this->getConnection()->getEntityManager()->getQueryBuilder()
            ->select('M.serviceTblPerson')
            ->from('\SPHERE\Application\People\Group\Service\Entity\TblMember', 'M')
            ->distinct()
            ->getQuery()
            ->getResult("COLUMN_HYDRATOR");

        $tblPersonAll = Person::useService()->getPersonAll();
        if ($tblPersonAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblPersonAll, function (TblPerson &$tblPerson) use ($Exclude) {

                if (in_array($tblPerson->getId(), $Exclude)) {
                    $tblPerson = false;
                }
            });
            $EntityList = array_filter($tblPersonAll);
        } else {
            $EntityList = null;
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllByPerson(TblPerson $tblPerson)
    {

        /** @var TblMember[] $EntityList */
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember',
            array(
                TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if ($EntityList) {
            array_walk($EntityList, function (TblMember &$V) {

                $V = $V->getTblGroup();
            });
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblGroup  $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return TblMember
     */
    public function addGroupPerson(TblGroup $tblGroup, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblMember')
            ->findOneBy(array(
                TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
                TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblMember();
            $Entity->setTblGroup($tblGroup);
            $Entity->setServiceTblPerson($tblPerson);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblGroup  $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removeGroupPerson(TblGroup $tblGroup, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntity('TblMember')
            ->findOneBy(array(
                TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
                TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool
     */
    public function destroyGroup(TblGroup $tblGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntityById('TblGroup', $tblGroup->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return array of TblPerson->Id
     */
    public function fetchIdPersonAllByGroup(TblGroup $tblGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Builder = $Manager->getQueryBuilder();
        $Query = $Builder->select('M.serviceTblPerson')
            ->from(__NAMESPACE__.'\Entity\TblMember', 'M')
            ->where($Builder->expr()->eq('M.tblGroup', '?1'))
            ->setParameter(1, $tblGroup->getId())
            ->getQuery();
        return $Query->getResult(ColumnHydrator::HYDRATION_MODE);
    }

    /**
     * @param TblGroup $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return bool|TblMember
     */
    public function existsGroupPerson(TblGroup $tblGroup, TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember', array(
            TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
            TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

}
