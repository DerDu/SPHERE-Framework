<?php

namespace SPHERE\Application\Billing\Inventory\Setting\Service;

use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSettingGroupPerson;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Inventory\Setting\Service
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {


        // SEPA Options
        $this->createSetting(TblSetting::IDENT_IS_SEPA, '1', TblSetting::TYPE_BOOLEAN, TblSetting::CATEGORY_SEPA);
        $this->createSetting(TblSetting::IDENT_SEPA_REMARK, '', TblSetting::TYPE_STRING, TblSetting::CATEGORY_SEPA);
        $tblSetting = $this->createSetting(TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER, '1', TblSetting::TYPE_BOOLEAN, TblSetting::CATEGORY_SEPA);
        // ToDO Update darf später wieder entfernt werden (nach 1.8.56)
        if($tblSetting->getCategory() == TblSetting::CATEGORY_REGULAR){
            $this->updateSettingCategory($tblSetting, TblSetting::CATEGORY_SEPA);
        }
        $this->createSetting(TblSetting::IDENT_SEPA_FEE, '', TblSetting::TYPE_STRING, TblSetting::CATEGORY_SEPA);

        // DATEV Options
        // ToDO Update darf später wieder entfernt werden (nach 1.8.56)
        $tblSetting = $this->getSettingByIdentifier('IsDebtorNumberNeed');
        if($tblSetting){
            $this->updateSettingIdentifier($tblSetting, TblSetting::IDENT_IS_DATEV);
            $this->updateSettingCategory($tblSetting, TblSetting::CATEGORY_DATEV);
        }
        $this->createSetting(TblSetting::IDENT_IS_DATEV, '1', TblSetting::TYPE_BOOLEAN, TblSetting::CATEGORY_DATEV);

        $tblSetting = $this->createSetting(TblSetting::IDENT_DEBTOR_NUMBER_COUNT, '5', TblSetting::TYPE_INTEGER, TblSetting::CATEGORY_DATEV);
        // ToDO Update darf später wieder entfernt werden (nach 1.8.56)
        if($tblSetting->getCategory() == TblSetting::CATEGORY_REGULAR){
            $this->updateSettingCategory($tblSetting, TblSetting::CATEGORY_DATEV);
        }
        $tblSetting = $this->createSetting(TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER, '1', TblSetting::TYPE_BOOLEAN, TblSetting::CATEGORY_DATEV);
        // ToDO Update darf später wieder entfernt werden (nach 1.8.56)
        if($tblSetting->getCategory() == TblSetting::CATEGORY_REGULAR){
            $this->updateSettingCategory($tblSetting, TblSetting::CATEGORY_DATEV);
        }
        $this->createSetting(TblSetting::IDENT_DATEV_REMARK, '', TblSetting::TYPE_STRING, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_FIBU_ACCOUNT, '', TblSetting::TYPE_STRING, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR, '0', TblSetting::TYPE_BOOLEAN, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_FIBU_TO_ACCOUNT, '', TblSetting::TYPE_STRING, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_CONSULT_NUMBER, '', TblSetting::TYPE_STRING, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_CLIENT_NUMBER, '', TblSetting::TYPE_STRING, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH, '8', TblSetting::TYPE_INTEGER, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_KOST_1, '0', TblSetting::TYPE_INTEGER, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_KOST_2, '0', TblSetting::TYPE_INTEGER, TblSetting::CATEGORY_DATEV);
        $this->createSetting(TblSetting::IDENT_BU_KEY, '0', TblSetting::TYPE_INTEGER, TblSetting::CATEGORY_DATEV);




        // Wird nur ausgeführt, wenn noch keine Personengruppen ausgewählt sind
        if(false === Setting::useService()->getSettingGroupPersonAll()){
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
            $this->createSettingGroupPerson($tblGroup);
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
            $this->createSettingGroupPerson($tblGroup);
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
            $this->createSettingGroupPerson($tblGroup);
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
            $this->createSettingGroupPerson($tblGroup);
        }
//        $tblSetting = $this->createSetting(TblSetting::IDENT_PERSON_GROUP_ACTIVE_LIST, '1;2;3;4;6');
//        $this->updateSetting($tblSetting, '1;2;3;4;6');
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSetting
     */
    public function getSettingById($Id)
    {

        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblSetting', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSettingGroupPerson
     */
    public function getSettingGroupPersonById($Id)
    {

        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(),
            'TblSettingGroupPerson', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblSetting
     */
    public function getSettingByIdentifier($Identifier)
    {

        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblSetting',
            array(
                TblSetting::ATTR_IDENTIFIER => $Identifier
            ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblSetting
     */
    public function getSettingGroupPersonByGroup(TblGroup $tblGroup)
    {

        $Entity = $this->getForceEntityBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblSettingGroupPerson',
            array(
                TblSettingGroupPerson::ATTR_SERVICE_TBL_GROUP_PERSON => $tblGroup->getId()
            ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblSetting[]
     */
    public function getSettingAll()
    {

        $Entity = $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblSetting');
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Category
     *
     * @return TblSetting[]|false
     */
    public function getSettingAllByCategory($Category = '')
    {

        $Entity = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblSetting',
            array(
                TblSetting::ATTR_CATEGORY => $Category
            ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblSetting[]
     */
    public function getSettingGroupPersonAll()
    {

        $Entity = $this->getForceEntityList(__Method__, $this->getConnection()->getEntityManager(),
            'TblSettingGroupPerson');
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Identifier
     * @param string $Value
     * @param string $Type
     * @param string $Category
     *
     * @return TblSetting
     */
    public function createSetting($Identifier, $Value, $Type = TblSetting::TYPE_STRING, $Category = TblSetting::CATEGORY_REGULAR)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntity('TblSetting')->findOneBy(array(
            TblSetting::ATTR_IDENTIFIER => $Identifier,
        ));

        if($Entity === null){

            // create if new
            $Entity = new TblSetting();
            $Entity->setIdentifier($Identifier);
            $Entity->setValue($Value);
            $Entity->setType($Type);
            $Entity->setCategory($Category);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return TblSettingGroupPerson
     */
    public function createSettingGroupPerson(TblGroup $tblGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSettingGroupPerson $Entity */
        $Entity = $Manager->getEntity('TblSettingGroupPerson')->findOneBy(array(
            TblSettingGroupPerson::ATTR_SERVICE_TBL_GROUP_PERSON => $tblGroup->getId(),
        ));

        if($Entity === null){

            // create if new
            $Entity = new TblSettingGroupPerson();
            $Entity->setServiceTblGroupPerson($tblGroup);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSetting $tblSetting
     * @param string     $Value
     *
     * @return TblSetting
     */
    public function updateSetting(TblSetting $tblSetting, $Value)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());

        if(null !== $Entity){
            // update if new Value
            if($Entity->getValue() !== $Value){
                $Protocol = clone $Entity;
                $Entity->setValue($Value);

                $Manager->saveEntity($Entity);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                    $Protocol,
                    $Entity);
            }
        }

        return $Entity;
    }

    /**
     * @param TblSetting $tblSetting
     * @param string     $Category
     *
     * @return TblSetting
     */
    public function updateSettingCategory(TblSetting $tblSetting, $Category = TblSetting::CATEGORY_REGULAR)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());

        if(null !== $Entity
            && $Entity->getCategory() != $Category){
            $Protocol = clone $Entity;
            $Entity->setCategory($Category);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSetting $tblSetting
     * @param string     $Identifier
     *
     * @return TblSetting
     */
    public function updateSettingIdentifier(TblSetting $tblSetting, $Identifier = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());

        if(null !== $Entity){
            $Protocol = clone $Entity;
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSetting $tblSetting
     *
     * @return bool
     */
    public function destroySetting(TblSetting $tblSetting)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());

        if($Entity !== null){

            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSettingGroupPerson $tblSettingGroupPerson
     *
     * @return bool
     */
    public function destroySettingGroupPerson(TblSettingGroupPerson $tblSettingGroupPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSettingGroupPerson $Entity */
        $Entity = $Manager->getEntityById('TblSettingGroupPerson', $tblSettingGroupPerson->getId());

        if($Entity !== null){

            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            return true;
        }
        return false;
    }
}