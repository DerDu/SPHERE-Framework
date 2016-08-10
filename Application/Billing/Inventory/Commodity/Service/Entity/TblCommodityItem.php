<?php
namespace SPHERE\Application\Billing\Inventory\Commodity\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCommodityItem")
 * @Cache(usage="READ_ONLY")
 */
class TblCommodityItem extends Element
{

    const ATTR_TBL_COMMODITY = 'tblCommodity';
    const ATTR_TBL_ITEM = 'tblItem';

//    /**
//     * @Column(type="decimal", precision=14, scale=4)
//     */
//    protected $Quantity;
    /**
     * @Column(type="bigint")
     */
    protected $tblCommodity;
    /**
     * @Column(type="bigint")
     */
    protected $tblItem;

//    /**
//     * @return string
//     */
//    public function getTotalPriceString()
//    {
//
//        $tblItem = $this->getTblItem();
//        $quantity = $this->getQuantity();
//        $result = 0.00;
//        if ($tblItem && $tblItem->getPrice() > 0 && $quantity > 0) {
//            $result = sprintf("%01.4f", $tblItem->getPrice() * $quantity);
//        }
//
//        return str_replace('.', ',', $result)." €";
//    }

    /**
     * @return bool|TblItem
     */
    public function getTblItem()
    {

        if (null === $this->tblItem) {
            return false;
        } else {
            return Item::useService()->getItemById($this->tblItem);
        }
    }

    /**
     * @param null|TblItem $tblItem
     */
    public function setTblItem(TblItem $tblItem = null)
    {

        $this->tblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }

//    /**
//     * @return (type="decimal", precision=14, scale=4)
//     */
//    public function getQuantity()
//    {
//
//        return $this->Quantity;
//    }
//
//    /**
//     * @param (type="decimal", precision=14, scale=4) $Quantity
//     */
//    public function setQuantity($Quantity)
//    {
//
//        $this->Quantity = $Quantity;
//    }

    /**
     * @return bool|TblCommodity
     */
    public function getTblCommodity()
    {

        if (null === $this->tblCommodity) {
            return false;
        } else {
            return Commodity::useService()->getCommodityById($this->tblCommodity);
        }
    }

    /**
     * @param null|TblCommodity $tblCommodity
     */
    public function setTblCommodity(TblCommodity $tblCommodity = null)
    {

        $this->tblCommodity = ( null === $tblCommodity ? null : $tblCommodity->getId() );
    }
}
