<?php

namespace SPHERE\Application\Billing\Inventory\Item\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemType;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Inventory\Item\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        /**
         * ItemType
         */
        $this->createItemType('Einzelleistung');
        $this->createItemType('Sammelleistung');
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblItem
     */
    public function getItemById($Id)
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblItem', $Id);
        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblItem', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemType
     */
    public function getItemTypeById($Id)
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblItemType', $Id);
        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblItemType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Name
     *
     * @return bool|Element
     */
    public function getItemTypeByName($Name)
    {

        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemType',
            array(TblItemType::ATTR_NAME => $Name));
    }

    /**
     * @return bool|TblItemType[]
     */
    public function getItemTypeAll()
    {

        return $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblItemType');
    }

    /**
     * @param $Id
     *
     * @return bool|TblCalculation
     */
    public function getCalculationById($Id)
    {

        return $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblCalculation', $Id);
    }

    /**
     * @param TblItem        $tblItem
     * @param TblCalculation $tblCalculation
     *
     * @return false|TblItemCalculation
     */
    public function getItemCalculationByItemAndCalculation(TblItem $tblItem, TblCalculation $tblCalculation)
    {

        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemCalculation',
            array(TblItemCalculation::ATTR_TBL_ITEM        => $tblItem->getId(),
                  TblItemCalculation::ATTR_TBL_CALCULATION => $tblCalculation->getId()));
    }

    /**
     * @param $Name
     *
     * @return bool|null|TblItem
     */
    public function getItemByName($Name)
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblItem')
//            ->findOneBy(array(TblItem::ATTR_NAME => $Name));
        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItem',
            array(TblItem::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblItem[]
     */
    public function getItemAll()
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblItem')->findAll();
        return $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblItem');

    }

    /**
     * @param $Id
     *
     * @return bool|TblItemAccount
     */
    public function getItemAccountById($Id)
    {

//        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblItemAccount', $Id);
        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblItemAccount', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItemAccount[]
     */
    public function getItemAccountAllByItem(TblItem $tblItem)
    {

//        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblItemAccount')
//            ->findBy(array(TblItemAccount::ATTR_TBL_ITEM => $tblItem->getId()));
        $EntityList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemAccount',
            array(TblItemAccount::ATTR_TBL_ITEM => $tblItem->getId()));

        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblCalculation[]
     */
    public function getCalculationAllByItem(TblItem $tblItem)
    {

        $TempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemCalculation',
            array(TblItemCalculation::ATTR_TBL_ITEM => $tblItem->getId()));


        $EntityList = array();
        if ($TempList) {
            /** @var TblItemCalculation $Temp */
            foreach ($TempList as $Temp) {
                $EntityList[] = $Temp->getTblCalculation();
            }
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblCalculation
     */
    public function getCalculationStandardValueAllByItem(TblItem $tblItem)
    {

        $TempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblItemCalculation',
            array(TblItemCalculation::ATTR_TBL_ITEM => $tblItem->getId()));


        $Entity = null;
        if ($TempList) {
            /** @var TblItemCalculation $Temp */
            foreach ($TempList as $Temp) {
                /** @var TblCalculation $tblCalculation */
                $tblCalculation = $Temp->getTblCalculation();
                if (!$tblCalculation->getServiceTblType() && !$tblCalculation->getServiceTblSiblingRank()) {
                    $Entity = $tblCalculation;
                }
            }
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblItem $tblItem
     *
     * @return int
     */
    public function countCalculationByItem(TblItem $tblItem)
    {

        return (int)$this->getConnection()->getEntityManager()->getEntity('TblItemCalculation')->countBy(array(
            TblItemCalculation::ATTR_TBL_ITEM => $tblItem->getId()
        ));
    }

    /**
     * @param TblItem $tblItem
     * @param         $SchoolType
     * @param         $SiblingRank
     *
     * @return bool|TblCalculation
     */
    public function existsCalculation(TblItem $tblItem, $SchoolType, $SiblingRank)
    {

        if ($SchoolType === 0) {
            $SchoolType = null;
        }
        if ($SiblingRank === 0) {
            $SiblingRank = null;
        }
        $Exists = false;

        $tblCalculationList = $this->getCalculationAllByItem($tblItem);
        if ($tblCalculationList) {
            /** @var TblCalculation $tblCalculation */
            foreach ($tblCalculationList as $tblCalculation) {
                $tblSchoolType = '0';
                $tblChildRank = '0';
                if ($tblCalculation->getServiceTblType()) {
                    $tblSchoolType = $tblCalculation->getServiceTblType()->getId();
                }
                if ($tblCalculation->getServiceTblSiblingRank()) {
                    $tblChildRank = $tblCalculation->getServiceTblSiblingRank()->getId();
                }
                if ($tblSchoolType === $SchoolType && $tblChildRank === $SiblingRank) {
                    $Exists = true;
                }
            }
        }

        return $Exists;
    }

    /**
     * @param $Name
     *
     * @return bool|TblItem
     */
    public function existsItem($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblItem',
            array(TblItem::ATTR_NAME => $Name));
    }

    /**
     * @param TblItemType             $tblItemType
     * @param                         $Name
     * @param string                  $Description
     *
     * @return null|object|TblItem
     */
    public function createItem(
        TblItemType $tblItemType,
        $Name,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItem')->findOneBy(array(
            TblItem::ATTR_NAME => $Name,
        ));

        if ($Entity === null) {
            $Entity = new TblItem();
            $Entity->setName($Name);
            $Entity->setTblItemType($tblItemType);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return TblItemType
     */
    public function createItemType($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItemType')->findOneBy(array(
            TblItemType::ATTR_NAME => $Name,
        ));

        if (null === $Entity) {
            $Entity = new TblItemType();
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param                     $Value
     * @param TblType|null        $Course
     * @param TblSiblingRank|null $ChildRank
     *
     * @return TblCalculation
     */
    public function createCalculation($Value, TblType $Course = null, TblSiblingRank $ChildRank = null)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblCalculation();
        $Entity->setValue(str_replace(',', '.', $Value));
        if (null !== $Course) {
            $Entity->setServiceTblType($Course);
        }
        if (null !== $ChildRank) {
            $Entity->setServiceTblSiblingRank($ChildRank);
        }
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);
        return $Entity;
    }

    /**
     * @param TblItem        $tblItem
     * @param TblCalculation $tblCalculation
     *
     * @return TblItemCalculation
     */
    public function createItemCalculation(TblItem $tblItem, TblCalculation $tblCalculation)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblItemCalculation();
        $Entity->setTblItem($tblItem);
        $Entity->setTblCalculation($tblCalculation);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblItem     $tblItem
     * @param             $Name
     * @param             $Description
     *
     * @return bool
     */
    public function updateItem(
        TblItem $tblItem,
        $Name,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblItem $Entity */
        $Entity = $Manager->getEntityById('TblItem', $tblItem->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
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
     * @param TblCalculation   $tblCalculation
     * @param                  $Value
     *
     * @return bool
     */
    public function updateCalculation(TblCalculation $tblCalculation, $Value)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCalculation $Entity */
        $Entity = $Manager->getEntityById('TblCalculation', $tblCalculation->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($Value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCalculation $tblCalculation
     * @param TblItem        $tblItem
     *
     * @return bool
     */
    public function destroyCalculation(
        TblCalculation $tblCalculation,
        TblItem $tblItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $this->removeCalculationFromItem($tblItem, $tblCalculation);

        $Entity = $Manager->getEntity('TblCalculation')->findOneBy(array('Id' => $tblCalculation->getId()));
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
     * @param TblItem        $tblItem
     * @param TblCalculation $tblCalculation
     *
     * @return bool
     */
    public function removeCalculationFromItem(TblItem $tblItem, TblCalculation $tblCalculation)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $tblItemCalculation = $this->getItemCalculationByItemAndCalculation($tblItem, $tblCalculation);

        if ($tblItemCalculation) {
            $Entity = $Manager->getEntity('TblItemCalculation')->findOneBy(array('Id' => $tblCalculation->getId()));
            if (null !== $Entity) {
                /** @var Element $Entity */
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
                return true;
            }
        }
        return false;
    }

    /**
     * @param TblItem    $tblItem
     * @param TblAccount $tblAccount
     *
     * @return TblItemAccount|null
     */
    public function addItemAccount(
        TblItem $tblItem,
        TblAccount $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItemAccount')->findOneBy(
            array(
                TblItemAccount::ATTR_TBL_ITEM            => $tblItem->getId(),
                TblItemAccount::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblItemAccount();
            $Entity->setTblItem($tblItem);
            $Entity->setTblAccount($tblAccount);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblItemAccount $tblItemAccount
     *
     * @return bool
     */
    public function removeItemAccount(
        TblItemAccount $tblItemAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblItemAccount')->findOneBy(
            array(
                'Id' => $tblItemAccount->getId()
            ));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
