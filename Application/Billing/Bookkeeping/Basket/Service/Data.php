<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service;

use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Bookkeeping\Basket\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasket', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketVerification
     */
    public function getBasketVerificationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketVerification', $Id);
    }

    /**
     * @return bool|TblBasket[]
     */
    public function getBasketAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasket');
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasket(TblBasket $tblBasket)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketItem',
            array(TblBasketItem::ATTR_TBL_BASKET => $tblBasket->getId()));
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return false|TblBasketItem
     */
    public function getBasketItemByBasketAndItem(TblBasket $tblBasket, TblItem $tblItem)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketItem',
            array(TblBasketItem::ATTR_TBL_BASKET       => $tblBasket->getId(),
                  TblBasketItem::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()));
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function getBasketItemById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketItem', $Id);
    }

    /***
     * @param $Id
     *
     * @return bool|TblBasketPerson
     */
    public function getBasketPersonById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketPerson', $Id);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketPerson[]
     */
    public function getBasketPersonAllByBasket(TblBasket $tblBasket)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketPerson',
            array(TblBasketPerson::ATTR_TBL_BASKET => $tblBasket->getId()));
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     *
     * @return bool|TblBasketPerson
     */
    public function getBasketPersonByBasketAndPerson(TblBasket $tblBasket, TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketPerson',
            array(TblBasketPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                  TblBasketPerson::ATTR_TBL_BASKET         => $tblBasket->getId()));
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationByBasket(TblBasket $tblBasket)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketVerification',
            array(TblBasketVerification::ATTR_TBL_BASKET => $tblBasket->getId()));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationByPersonAndBasket(TblPerson $tblPerson, TblBasket $tblBasket)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketVerification',
            array(TblBasketVerification::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            , TblBasketVerification::ATTR_TBL_BASKET             => $tblBasket->getId()));
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     *
     * @return bool
     */
    public function checkBasketVerificationIsSet(TblBasket $tblBasket, TblPerson $tblPerson, TblItem $tblItem)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketVerification',
            array(TblBasketVerification::ATTR_TBL_BASKET         => $tblBasket->getId(),
                  TblBasketVerification::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                  TblBasketVerification::ATTR_SERVICE_TBL_ITEM   => $tblItem->getId()));
        return ( $Entity === false ? false : true );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return int
     */
    public function countPersonByBasket(TblBasket $tblBasket)
    {

        return $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketPerson',
            array(TblBasketPerson::ATTR_TBL_BASKET => $tblBasket->getId()));
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     * @param           $Price
     *
     * @return null|object|TblBasketVerification
     */
    public function createBasketVerification(TblBasket $tblBasket, TblPerson $tblPerson, TblItem $tblItem, $Price)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketVerification')->findOneBy(array(
            TblBasketVerification::ATTR_TBL_BASKET         => $tblBasket->getId(),
            TblBasketVerification::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblBasketVerification::ATTR_SERVICE_TBL_ITEM   => $tblItem->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblBasketVerification();
            $Entity->setTblBasket($tblBasket);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblItem($tblItem);
            $Entity->setValue($Price);
            $Entity->setQuantity(1);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblBasket
     */
    public function createBasket(
        $Name,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblBasket();
        $Entity->setName($Name);
        $Entity->setDescription($Description);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return TblBasket
     */
    public function addItemToBasket(TblBasket $tblBasket, TblItem $tblItem)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(array(
            TblBasketItem::ATTR_TBL_BASKET       => $tblBasket->getId(),
            TblBasketItem::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblBasketItem();
            $Entity->setServiceTblItem($tblItem);
            $Entity->setTblBasket($tblBasket);

            $Manager->bulkSaveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        $Manager->flushCache();

        return $tblBasket;
    }

    /**
     * @param TblCommodity $tblCommodity
     * @param TblBasket    $tblBasket
     *
     * @return TblBasket
     */
    public function addBasketItemsByCommodity(
        TblBasket $tblBasket,
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $tblCommodityItemList = Commodity::useService()->getCommodityItemAllByCommodity($tblCommodity);


        /** @var TblCommodityItem $tblCommodityItem */
        foreach ($tblCommodityItemList as $tblCommodityItem) {
            $tblItem = $tblCommodityItem->getTblItem();
            if ($tblItem) {
                $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(array(
                    TblBasketItem::ATTR_TBL_BASKET       => $tblBasket->getId(),
                    TblBasketItem::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()
                ));

                if (null === $Entity) {
                    $Entity = new TblBasketItem();
                    $Entity->setServiceTblItem($tblItem);
                    $Entity->setTblBasket($tblBasket);

                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                        $Entity);
                }
            }
        }
        $Manager->flushCache();

        return $tblBasket;
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     *
     * @return TblBasketPerson
     */
    public function addBasketPerson(
        TblBasket $tblBasket,
        TblPerson $tblPerson
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBasketPerson')->findOneBy(array(
            TblBasketPerson::ATTR_TBL_BASKET         => $tblBasket->getId(),
            TblBasketPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblBasketPerson();
            $Entity->setTblBasket($tblBasket);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $Name
     *
     * @return bool
     */
    public function updateBasket(
        TblBasket $tblBasket,
        $Name,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasket $Entity */
        $Entity = $Manager->getEntityById('TblBasket', $tblBasket->getId());
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
     * @param TblBasketVerification $tblBasketVerification
     * @param                       $Price
     * @param                       $Quantity
     *
     * @return bool
     */
    public function updateBasketVerification(TblBasketVerification $tblBasketVerification, $Price, $Quantity)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasketVerification $Entity */
        $Entity = $Manager->getEntityById('TblBasketVerification', $tblBasketVerification->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue(str_replace(',', '.', $Price));
            $Entity->setQuantity($Quantity);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return bool
     */
    public function removeBasketItem(
        TblBasketItem $tblBasketItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(
            array(
                'Id' => $tblBasketItem->getId()
            ));
        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasketPerson $tblBasketPerson
     *
     * @return bool
     */
    public function removeBasketPerson(
        TblBasketPerson $tblBasketPerson
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketPerson')->findOneBy(
            array(
                'Id' => $tblBasketPerson->getId()
            ));
        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasket(
        TblBasket $tblBasket
    ) {

        if ($tblBasket !== null) {
            $Manager = $this->getConnection()->getEntityManager();

            $EntityList = $Manager->getEntity('TblBasketPerson')->findBy(array(TblBasketPerson::ATTR_TBL_BASKET => $tblBasket->getId()));
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }

            $EntityList = $Manager->getEntity('TblBasketItem')->findBy(array(TblBasketItem::ATTR_TBL_BASKET => $tblBasket->getId()));
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }
            $Entity = $Manager->getEntity('TblBasket')->findOneBy(array('Id' => $tblBasket->getId()));
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
//            $Manager->flushCache();
            return true;
        }

        return false;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return bool
     */
    public function destroyBasketVerification(TblBasketVerification $tblBasketVerification)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBasketVerification')->findOneBy(array('Id' => $tblBasketVerification->getId()));
        /**@var TblBasketVerification $Entity */
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);

            return true;
        }
        return false;
    }
}
