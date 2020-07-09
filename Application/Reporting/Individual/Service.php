<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Reporting\Individual\Service\Data;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPresetSetting;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Individual
 */
class Service extends ServiceView
{

    /**
     * @param $Id
     *
     * @return false|TblWorkSpace
     */
    public function getWorkSpaceById($Id)
    {

        return (new Data($this->getBinding()))->getWorkSpaceById($Id);
    }

    /**
     * @param string $ViewType
     *
     * @return bool|TblWorkSpace[]
     * TblWorkSpace list by Account and Type
     */
    public function getWorkSpaceAll($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return (new Data($this->getBinding()))->getWorkSpaceAllByAccount($tblAccount, $ViewType);
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $ViewType
     *
     * @return bool|TblWorkSpace[]
     */
    public function getWorkSpaceAllByAccount(TblAccount $tblAccount, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        return (new Data($this->getBinding()))->getWorkSpaceAllByAccount($tblAccount, $ViewType);
    }

    /**
     * @param $Id
     *
     * @return false|TblPreset
     */
    public function getPresetById($Id)
    {

        return (new Data($this->getBinding()))->getPresetById($Id);
    }

    /**
     * @return bool|TblWorkSpace[]
     */
    public function getPresetAll()
    {
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPresetList = array();
            $PresetPublic = (new Data($this->getBinding()))->gePresetAllByPublic();
            $PresetOwn = (new Data($this->getBinding()))->gePresetAllByAccount($tblAccount);

            // add Own Preset's
            if($PresetOwn){
                foreach($PresetOwn as $tblPreset){
                    $tblPresetList[$tblPreset->getId()] = $tblPreset;
                }
            }
            // add Public Preset's
            if($PresetPublic){
                foreach($PresetPublic as $tblPreset){
                    $tblPresetList[$tblPreset->getId()] = $tblPreset;
                }
            }
            // return false if empty
            if(empty($tblPresetList)){
                $tblPresetList = false;
            }
            return $tblPresetList;
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblPresetSetting
     */
    public function getPresetSettingById($Id)
    {

        return (new Data($this->getBinding()))->getPresetSettingById($Id);
    }

    /**
     * @param TblPreset $tblPreset
     * @param string    $ViewType
     *
     * @return false|TblPresetSetting[]
     */
    public function getPresetSettingAllByPreset(TblPreset $tblPreset, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        return (new Data($this->getBinding()))->getPresetSettingAllByPreset($tblPreset, $ViewType);
    }

    /**
     * @param string         $Field
     * @param string         $View
     * @param int            $Position
     * @param TblPreset|null $tblPreset
     * @param string         $ViewType
     *
     * @return bool|TblWorkSpace
     */
    public function addWorkSpaceField($Field, $View, $Position, $ViewType, TblPreset $tblPreset = null)
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return (new Data($this->getBinding()))->createWorkSpace($tblAccount, $Field, $View, $Position, $ViewType, $tblPreset);
        }
        return false;
    }

    /**
     * @param string $Name
     * @param bool   $IsPublic
     * @param array  $Post
     *
     * @return bool|TblPreset
     */
    public function createPreset($Name = '', $IsPublic = false, $Post = array())
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $PersonCreator = '';
            if(($tblPersonList = Account::useService()->getPersonAllByAccount($tblAccount))){
                /** @var TblPerson $tblPerson */
                $tblPerson = current($tblPersonList);
//                $FirstLetter = substr($tblPerson->getFirstName(), 0, 1);
                $PersonCreator = $tblPerson->getLastFirstName();
            }

            return (new Data($this->getBinding()))->createPreset($tblAccount, $Name, $IsPublic, $PersonCreator, $Post);
        }
        return false;
    }

    /**
     * @param TblPreset    $tblPreset
     * @param TblWorkSpace $tblWorkSpace
     *
     * @return bool|TblPresetSetting
     */
    public function createPresetSetting(TblPreset $tblPreset, TblWorkSpace $tblWorkSpace)
    {

        $FieldName = $tblWorkSpace->getField();
        $View = $tblWorkSpace->getView();
        $ViewType = $tblWorkSpace->getViewType();
        $Position = $tblWorkSpace->getPosition();

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return (new Data($this->getBinding()))->createPresetSetting($tblPreset, $FieldName, $View, $ViewType, $Position);
        }
        return false;
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

        return (new Data($this->getBinding()))->changeWorkSpace($tblWorkSpace, $Position, $FieldCount);
    }

    /**
     * @param TblWorkSpace   $tblWorkSpace
     * @param TblPreset|null $tblPreset
     *
     * @return bool
     */
    public function changeWorkSpacePreset(TblWorkSpace $tblWorkSpace, TblPreset $tblPreset = null)
    {

        return (new Data($this->getBinding()))->changeWorkSpacePreset($tblWorkSpace, $tblPreset);
    }

    /**
     *
     * @param TblPreset $tblPreset
     * @param bool      $IsPublic
     *
     * @return bool
     */
    public function changePresetIsPublic(TblPreset $tblPreset, $IsPublic)
    {

        return (new Data($this->getBinding()))->changePresetIsPublic($tblPreset, $IsPublic);
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     *
     * @return bool
     */
    public function removeWorkSpace(TblWorkSpace $tblWorkSpace)
    {

        return (new Data($this->getBinding()))->removeWorkSpace($tblWorkSpace);
    }

    /**
     * @param string $ViewType
     *
     * @return bool
     */
    public function removeWorkSpaceAll($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblWorkspaceList = Individual::useService()->getWorkSpaceAll($ViewType);
        if ($tblWorkspaceList) {
            foreach ($tblWorkspaceList as $tblWorkspace) {
                (new Data($this->getBinding()))->removeWorkSpace($tblWorkspace);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblPreset $tblPreset
     * @param string    $ViewType
     *
     * @return bool
     */
    public function removePreset(TblPreset $tblPreset, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblPresetSettingList = Individual::useService()->getPresetSettingAllByPreset($tblPreset, $ViewType);
        if ($tblPresetSettingList) {
            foreach ($tblPresetSettingList as $tblPresetSetting) {
                (new Data($this->getBinding()))->removePresetSetting($tblPresetSetting);
            }
        }

        return (new Data($this->getBinding()))->removePreset($tblPreset);
    }
}
