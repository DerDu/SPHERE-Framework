<?php
namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketItem")
 * @Cache(usage="READ_ONLY")
 */
class TblBasketItem extends Element
{

    const ATTR_TBL_BASKET = 'tblBasket';
    const ATTR_SERVICE_TBL_ITEM = 'serviceTblItem';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblItem;
    /**
     * @Column(type="bigint")
     */
    protected $tblBasket;

//    /**
//     * @return string
//     */
//    public function getPriceString()
//    {
//
//        $result = sprintf("%01.4f", $this->Price);
//        return str_replace('.', ',', $result)." €";
//    }

    /**
     * @return bool|TblBasket
     */
    public function getTblBasket()
    {

        if (null === $this->tblBasket) {
            return false;
        } else {
            return Basket::useService()->getBasketById($this->tblBasket);
        }
    }

    /**
     * @param null|TblBasket $tblBasket
     */
    public function setTblBasket($tblBasket = null)
    {

        $this->tblBasket = ( null === $tblBasket ? null : $tblBasket->getId() );
    }

    /**
     * @return bool|TblItem $tblItem
     */
    public function getServiceTblItem()
    {

        if (null === $this->serviceTblItem) {
            return false;
        } else {
            return Item::useService()->getItemById($this->serviceTblItem);
        }
    }

    /**
     * @param null|TblItem $tblItem
     */
    public function setServiceTblItem($tblItem = null)
    {

        $this->serviceTblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }
}
