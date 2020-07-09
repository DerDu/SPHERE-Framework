<?php

namespace SPHERE\Application\Reporting\Individual\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPresetSetting;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;

/**
 * Class Data
 *
 * @package SPHERE\Application\Reporting\Individual\Service
 */
class Data extends DataView
{

    /**
     * @param $Id
     *
     * @return false|TblWorkspace
     */
    public function getWorkspaceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblWorkSpace', $Id);
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $ViewType
     *
     * @return bool|TblWorkSpace[]
     */
    public function getWorkSpaceAllByAccount(TblAccount $tblAccount, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblWorkSpace',
            array(
                TblWorkSpace::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
                TblWorkSpace::ATTR_VIEW_TYPE => $ViewType
            ), array(
                TblWorkSpace::ATTR_POSITION => self::ORDER_ASC
            ));
    }

    /**
     * @param $Id
     *
     * @return false|TblPreset
     */
    public function getPresetById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPreset', $Id);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblWorkSpace[]
     */
    public function gePresetAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPreset', array(
            TblPreset::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()
        ));
    }

    /**
     * @return bool|TblWorkSpace[]
     */
    public function gePresetAllByPublic()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPreset', array(
            TblPreset::ATTR_IS_PUBLIC => true
        ));
    }

    /**
     * @param $Id
     *
     * @return false|TblPresetSetting
     */
    public function getPresetSettingById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPresetSetting',
            $Id);
    }

    /**
     * @param TblPreset $tblPreset
     * @param string    $ViewType
     *
     * @return false|TblPresetSetting[]
     */
    public function getPresetSettingAllByPreset(TblPreset $tblPreset, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPresetSetting',
            array(
                TblPresetSetting::ATTR_TBL_PRESET => $tblPreset->getId(),
                TblPresetSetting::ATTR_VIEW_TYPE => $ViewType
            ));
    }

    /**
     * @param TblAccount     $tblAccount
     * @param string         $Field
     * @param string         $View
     * @param int            $Position
     * @param string         $ViewType
     * @param TblPreset|null $tblPreset
     * @param int            $FieldCount
     *
     * @return TblWorkSpace
     */
    public function createWorkSpace(
        TblAccount $tblAccount,
        $Field,
        $View,
        $Position,
        $ViewType,
        TblPreset $tblPreset = null,
        $FieldCount = 1
    )
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblWorkSpace();
        $Entity->setTblPreset($tblPreset);
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setField($Field);
        $Entity->setView($View);
        $Entity->setViewType($ViewType);
        $Entity->setPosition($Position);
        $Entity->setFieldCount($FieldCount);
        // TODO: Expanded Parameter
        $Entity->setExpanded(false);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     * @param int|null     $Position
     * @param int|null     $FieldCount
     *
     * @return bool
     */
    public function changeWorkSpace(TblWorkSpace $tblWorkSpace, $Position = null, $FieldCount = null)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblWorkSpace $Entity */
        $Entity = $Manager->getEntityById('TblWorkSpace', $tblWorkSpace->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            if (null !== $Position) {
                $Entity->setPosition($Position);
            }
            if (null !== $FieldCount) {
                $Entity->setFieldCount($FieldCount);
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
     * @param TblWorkSpace   $tblWorkSpace
     * @param TblPreset|null $tblPreset
     *
     * @return bool
     */
    public function changeWorkSpacePreset(TblWorkSpace $tblWorkSpace, TblPreset $tblPreset = null)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblWorkSpace $Entity */
        $Entity = $Manager->getEntityById('TblWorkSpace', $tblWorkSpace->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblPreset($tblPreset);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;

    }

    /**
     * @param TblPreset $tblPreset
     * @param bool      $IsPublic
     *
     * @return bool
     */
    public function changePresetIsPublic(TblPreset $tblPreset, $IsPublic)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPreset $Entity */
        $Entity = $Manager->getEntityById('TblPreset', $tblPreset->getId());
        $Protocol = clone $Entity;

        if (null !== $Entity) {
            $Entity->setIsPublic($IsPublic);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;

    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $Name
     * @param bool       $IsPublic
     * @param string     $PersonCreator
     * @param array      $Post
     *
     * @return TblPreset
     */
    public function createPreset(TblAccount $tblAccount, $Name, $IsPublic = false, $PersonCreator = '', $Post = array())
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblPreset();
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setName($Name);
        $Entity->setIsPublic($IsPublic);
        $Entity->setPersonCreator($PersonCreator);
        $Entity->setPostValue($Post);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblPreset $tblPreset
     * @param string    $Field
     * @param string    $View
     * @param           $ViewType
     * @param int       $Position
     *
     * @return TblPresetSetting
     */
    public function createPresetSetting(TblPreset $tblPreset, $Field, $View, $ViewType, $Position)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblPresetSetting();
        $Entity->setTblPreset($tblPreset);
        $Entity->setField($Field);
        $Entity->setView($View);
        $Entity->setViewType($ViewType);
        $Entity->setPosition($Position);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     * @param int          $Position
     *
     * @return bool|TblWorkSpace
     */
    public function updateWorkSpacePosition(TblWorkSpace $tblWorkSpace, $Position = 0)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /**
         * @var TblWorkSpace $Protocol
         * @var TblWorkSpace $Entity
         */
        $Entity = $Manager->getEntityById('TblWorkSpace', $tblWorkSpace->getId());
        $Protocol = clone $Entity;
        if ($Entity !== null) {
            $Entity->setPosition($Position);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblPreset $tblPreset
     * @param string    $Name
     *
     * @return bool|TblPreset
     */
    public function updatePreset(TblPreset $tblPreset, $Name)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /**
         * @var TblPreset $Protocol
         * @var TblPreset $Entity
         */
        $Entity = $Manager->getEntityById('TblPreset', $tblPreset->getId());
        $Protocol = clone $Entity;
        if ($Entity !== null) {
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     *
     * @return bool
     */
    public function removeWorkSpace(TblWorkSpace $tblWorkSpace)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblWorkSpace $Entity */
        $Entity = $Manager->getEntityById('TblWorkSpace', $tblWorkSpace->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPreset $tblPreset
     *
     * @return bool
     */
    public function removePreset(TblPreset $tblPreset)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPreset $Entity */
        $Entity = $Manager->getEntityById('TblPreset', $tblPreset->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPresetSetting $tblPresetSetting
     *
     * @return bool
     */
    public function removePresetSetting(TblPresetSetting $tblPresetSetting)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPresetSetting $Entity */
        $Entity = $Manager->getEntityById('TblPresetSetting', $tblPresetSetting->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
