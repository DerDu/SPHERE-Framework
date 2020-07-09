<?php
namespace SPHERE\Application\People\Group\Service;

use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Handler\DataCacheHandler;
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

        $this->createGroup('Alle', 'Personendaten', '', true, TblGroup::META_TABLE_COMMON);
        $this->createGroup('Interessent', 'Schüler die zur Aufnahme vorgemerkt sind', '', true, TblGroup::META_TABLE_PROSPECT);
        $this->createGroup('Schüler', 'Alle aktiv verfügbaren Schüler', '', true, TblGroup::META_TABLE_STUDENT);
        $this->createGroup('Sorgeberechtigt', '', '', true, TblGroup::META_TABLE_CUSTODY);
        $this->createGroup('Mitarbeiter', 'Alle Mitarbeiter', '', true, TblGroup::META_TABLE_STAFF);
        $this->createGroup('Lehrer', 'Alle Mitarbeiter, welche einer Lehrtätigkeit nachgehen', '', true, TblGroup::META_TABLE_TEACHER);
        $this->createGroup('Vereinsmitglieder', '', '', true, TblGroup::META_TABLE_CLUB);
        $this->createGroup('Institutionen-Ansprechpartner', 'Institutionen Ansprechpartner', '', true, TblGroup::META_TABLE_COMPANY_CONTACT);
        $this->createGroup('Tutoren/Mentoren', '', '', true, TblGroup::META_TABLE_TUDOR);
        $this->createGroup('Beitragszahler', 'Personen, die für die Fakturierung gezogen werden', '', true, TblGroup::META_TABLE_DEBTOR);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $Remark
     * @param bool   $IsLocked
     * @param string $MetaTable
     * @param bool   $IsCoreGroup
     *
     * @return TblGroup
     */
    public function createGroup($Name, $Description, $Remark, $IsLocked = false, $MetaTable = '', $IsCoreGroup = false)
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
            $Entity->setCoreGroup($IsCoreGroup);
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
     * @param bool     $isCoreGroup
     *
     * @return bool
     */
    public function updateGroup(TblGroup $tblGroup, $Name, $Description, $Remark, $isCoreGroup = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblGroup $Entity */
        $Entity = $Manager->getEntityById('TblGroup', $tblGroup->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setRemark($Remark);
            $Entity->setCoreGroup($isCoreGroup);
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
        /** @var TblGroup $Entity */
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
        /** @var TblGroup $Entity */
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param bool $isCoreGroup
     *
     * @return false|TblGroup[]
     */
    public function getGroupListByIsCoreGroup($isCoreGroup = true)
    {

        $Entity = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblGroup',
            array(
                TblGroup::ATTR_IS_CORE_GROUP => $isCoreGroup
            ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByGroup(TblGroup $tblGroup)
    {

        $EntityList = $this->getMemberAllByGroup($tblGroup);

        $Cache = new DataCacheHandler(
            __METHOD__.$tblGroup->getId(), array(
                new TblGroup(''),
                new TblMember(),
                new TblPerson(),
            )
        );

        if ((null === ( $ResultList = $Cache->getData() )
            && !empty( $EntityList ))
        ) {
            array_walk($EntityList, function (TblMember &$V) {

                if (!$V->getServiceTblPerson()
                    && ($tblPerson = $V->getServiceTblPerson(true))
                ){
                    Person::useService()->softRemovePersonReferences($tblPerson);
                }

                $V = $V->getServiceTblPerson();
            });
            $EntityList = array_filter($EntityList);

            $Cache->setData($EntityList, 0);
        } else {
            $EntityList = $ResultList;
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblGroup $tblGroup
     * @param bool     $IsForced
     *
     * @return false|TblMember[]
     */
    public function getMemberAllByGroup(TblGroup $tblGroup, $IsForced = false)
    {
        if ($IsForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember',
                array(
                    TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
                ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember',
                array(
                    TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
                ));
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblGroup  $tblGroup
     * @param bool      $IsForced
     *
     * @return false|TblMember
     */
    public function getMemberByPersonAndGroup(TblPerson $tblPerson, TblGroup $tblGroup, $IsForced = false)
    {
        if ($IsForced) {
            return $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember',
                array(
                    TblMember::SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
                ));
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember',
                array(
                    TblMember::SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
                ));
        }
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
     * @param bool $isForced
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            /** @var TblMember[] $EntityList */
            $EntityList = $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblMember',
                array(
                    TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        } else {
            /** @var TblMember[] $EntityList */
            $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblMember',
                array(
                    TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        }

        if ($EntityList) {
            array_walk($EntityList, function (TblMember &$V) {

                $V = $V->getTblGroup();
            });
        }
        /** @var TblGroup[] $EntityList */
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblMember[]
     */
    public function getMemberAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {

            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblMember',
                array(
                    TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblMember',
                array(
                    TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        }
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
     * @param TblPerson[] $tblPersonList
     *
     * @return bool
     */
    public function addGroupPersonList(TblGroup $tblGroup, $tblPersonList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($tblPersonList)){
            foreach($tblPersonList as $tblPerson){
                $Entity = $Manager->getEntity('TblMember')
                    ->findOneBy(array(
                        TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
                        TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
                    ));
                if (null === $Entity) {
                    $Entity = new TblMember();
                    $Entity->setTblGroup($tblGroup);
                    $Entity->setServiceTblPerson($tblPerson);
                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                }
            }
        } else {
            return false;
        }
        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblGroup $tblGroup
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeGroupPerson(TblGroup $tblGroup, TblPerson $tblPerson, $IsSoftRemove = false )
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
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblMember $tblMember
     *
     * @return bool
     */
    public function removeMember(TblMember $tblMember)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntityById('TblMember', $tblMember->getId());
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

    /**
     * @param TblGroup $tblGroup
     * @return int
     */
    public function countMemberByGroup(TblGroup $tblGroup)
    {
        /** @var DataCacheHandler $Cache */
        $Cache = new DataCacheHandler(__METHOD__.'#'.$tblGroup->getId(),array( new TblMember() ));
        if( null === ($Result = $Cache->getData()) ) {
            $Result = $this->getEntityManager()->getEntity((new TblMember())->getEntityShortName())->countBy(array(
                TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
            ));
            $Cache->setData($Result);
        }
        return $Result;
    }

    /**
     * @param TblMember $tblMember
     *
     * @return bool
     */
    public function restoreMember(TblMember $tblMember)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntityById('TblMember', $tblMember->getId());
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
     * @param string $Name
     *
     * @return false|TblGroup[]
     */
    public function getGroupListLike($Name)
    {
        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();

        $and = $queryBuilder->expr()->andX();
        $and->add($queryBuilder->expr()->like('t.Name', '?1'));
        $and->add($queryBuilder->expr()->isNull('t.EntityRemove'));
        $queryBuilder->setParameter(1, '%' . $Name . '%');

        $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblGroup', 't')
            ->where($and);

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result;
    }
}
