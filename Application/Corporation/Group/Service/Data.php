<?php
namespace SPHERE\Application\Corporation\Group\Service;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\Corporation\Group\Service\Entity\TblMember;
use SPHERE\Application\Corporation\Group\Service\Entity\ViewCompanyGroupMember;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Corporation\Group\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewCompanyGroupMember[]
     */
    public function viewCompanyGroupMember()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewCompanyGroupMember'
        );
    }

    public function setupDatabaseContent()
    {

        $this->createGroup('Alle', 'Institutionendaten', '', true, 'COMMON');
        $this->createGroup('Schulen', '', '', true, 'SCHOOL');
        $this->createGroup('Kita', 'Kindertagesstätte', '', true, 'NURSERY');
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
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGroup
     */
    public function getGroupByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', array(
            TblGroup::ATTR_NAME => $Name
        ));
    }

    /**
     * @param string $MetaTable
     *
     * @return bool|TblGroup
     */
    public function getGroupByMetaTable($MetaTable)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', array(
            TblGroup::ATTR_META_TABLE => $MetaTable,
            TblGroup::ATTR_IS_LOCKED  => true
        ));
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblCompany[]
     */
    public function getCompanyAllByGroup(TblGroup $tblGroup)
    {

        $EntityList = $this->getMemberAllByGroup($tblGroup);

        $Cache = new DataCacheHandler(
            __METHOD__.$tblGroup->getId(), array(
                new TblGroup(''),
                new TblMember(),
                new TblCompany(),
            )
        );
        if (null === ( $ResultList = $Cache->getData() )
            && !empty( $EntityList )
        ) {

            array_walk($EntityList, function (TblMember &$V) {

                $V = $V->getServiceTblCompany();
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
     * @return bool|TblCompany[]
     */
    public function getCompanyAllHavingNoGroup()
    {

        $Exclude = $this->getConnection()->getEntityManager()->getQueryBuilder()
            ->select('M.serviceTblCompany')
            ->from('\SPHERE\Application\Corporation\Group\Service\Entity\TblMember', 'M')
            ->distinct()
            ->getQuery()
            ->getResult("COLUMN_HYDRATOR");

        $tblCompanyAll = Company::useService()->getCompanyAll();
        if ($tblCompanyAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblCompanyAll, function (TblCompany &$tblCompany) use ($Exclude) {

                if (in_array($tblCompany->getId(), $Exclude)) {
                    $tblCompany = false;
                }
            });
            $EntityList = array_filter($tblCompanyAll);
        } else {
            $EntityList = null;
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param TblCompany $tblCompany
     *
     * @return bool|TblMember[]
     */
    public function getMemberAllByCompany(TblCompany $tblCompany)
    {

        /** @var TblMember[] $EntityList */
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember',
            array(
                TblMember::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ));
        /** @var TblMember[] $EntityList */
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param TblCompany $tblCompany
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllByCompany(TblCompany $tblCompany)
    {

        /** @var TblMember[] $EntityList */
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember',
            array(
                TblMember::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ));
        $Cache = (new CacheFactory())->createHandler(new MemcachedHandler());
        if (null === ( $ResultList = $Cache->getValue($tblCompany->getId(), __METHOD__) )
            && !empty( $EntityList )
        ) {

            array_walk($EntityList, function (TblMember &$V) {

                $V = $V->getTblGroup();
            });
            $Cache->setValue($tblCompany->getId(), $EntityList, 0, __METHOD__);
        } else {
            $EntityList = $ResultList;
        }
        /** @var TblGroup[] $EntityList */
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblGroup   $tblGroup
     * @param TblCompany $tblCompany
     *
     * @return TblMember
     */
    public function addGroupCompany(TblGroup $tblGroup, TblCompany $tblCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblMember')
            ->findOneBy(array(
                TblMember::ATTR_TBL_GROUP      => $tblGroup->getId(),
                TblMember::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblMember();
            $Entity->setTblGroup($tblGroup);
            $Entity->setServiceTblCompany($tblCompany);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblGroup   $tblGroup
     * @param TblCompany $tblCompany
     *
     * @return bool
     */
    public function removeGroupCompany(TblGroup $tblGroup, TblCompany $tblCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntity('TblMember')
            ->findOneBy(array(
                TblMember::ATTR_TBL_GROUP      => $tblGroup->getId(),
                TblMember::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
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
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblMember $tblMember
     *
     * @return bool
     */
    public function destroyMember(TblMember $tblMember)
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
     * @param TblCompany $tblCompany
     *
     * @return bool|TblMember
     */
    public function existsGroupCompany(TblGroup $tblGroup, TblCompany $tblCompany)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMember', array(
            TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
            TblMember::SERVICE_TBL_COMPANY => $tblCompany->getId()
        ));
    }

    /**
     * @param TblGroup $tblGroup
     * @return int
     */
    public function countMemberByGroup(TblGroup $tblGroup)
    {
        return $this->getEntityManager()->getEntity((new TblMember())->getEntityShortName())->countBy(array(
            TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
        ));
    }
}
