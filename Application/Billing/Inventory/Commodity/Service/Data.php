<?php

namespace SPHERE\Application\Billing\Inventory\Commodity\Service;

use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Inventory\Commodity\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCommodity
     */
    public function getCommodityById($Id)
    {

        return $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblCommodity', $Id);

    }

    /**
     * @param string $Name
     *
     * @return bool|TblCommodity
     */
    public function getCommodityByName($Name)
    {

        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblCommodity',
            array(TblCommodity::ATTR_NAME => $Name));
    }

    /**
     * @return bool|TblCommodity[]
     */
    public function getCommodityAll()
    {

        return $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblCommodity');
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodityItem
     */
    public function getCommodityItemById($Id)
    {

        return $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblCommodityItem', $Id);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblItem[]
     */
    public function getItemAllByCommodity(TblCommodity $tblCommodity)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCommodityItem',
            array(TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId()));
        if ($EntityList) {
            array_walk($EntityList, function (TblCommodityItem &$tblCommodityItem) {

                if ($tblCommodityItem->getTblItem()) {
                    $tblCommodityItem = $tblCommodityItem->getTblItem();
                } else {
                    $tblCommodityItem = false;
                }
            });
            $EntityList = array_filter($EntityList);
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param Entity\TblCommodity $tblCommodity
     *
     * @return bool|TblItem[]
     */
    public function getCommodityItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblCommodityItem',
            array(TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId()));
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblCommodity
     */
    public function createCommodity(
        $Name,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblCommodity();
        $Entity->setName($Name);
        $Entity->setDescription($Description);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblCommodity $tblCommodity
     * @param              $Name
     * @param              $Description
     *
     * @return bool
     */
    public function updateCommodity(
        TblCommodity $tblCommodity,
        $Name,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCommodity $Entity */
        $Entity = $Manager->getEntityById('TblCommodity', $tblCommodity->getId());
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
     * @param TblCommodity $tblCommodity
     * @param TblItem      $tblItem
     *
     * @return bool
     */
    public function addItemToCommodity(
        TblCommodity $tblCommodity,
        TblItem $tblItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCommodityItem')->findOneBy(array(
            TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId(),
            TblCommodityItem::ATTR_TBL_ITEM      => $tblItem->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblCommodityItem();
            $Entity->setTblCommodity($tblCommodity);
            $Entity->setTblItem($tblItem);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCommodityItem $tblCommodityItem
     *
     * @return bool
     */
    public function removeItemToCommodity(
        TblCommodityItem $tblCommodityItem
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCommodityItem')->findOneBy(array('Id' => $tblCommodityItem->getId()));
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
     * @param Entity\TblCommodity $tblCommodity
     *
     * @return bool
     */
    public function destroyCommodity(
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityItems = $Manager->getEntity('TblCommodityItem')
            ->findBy(array(TblCommodityItem::ATTR_TBL_COMMODITY => $tblCommodity->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        $Entity = $Manager->getEntity('TblCommodity')->findOneBy(array('Id' => $tblCommodity->getId()));
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
