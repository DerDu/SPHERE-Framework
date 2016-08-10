<?php
namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketVerification")
 * @Cache(usage="READ_ONLY")
 */
class TblBasketVerification extends Element
{

    const ATTR_TBL_BASKET = 'tblBasket';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_ITEM = 'serviceTblItem';

    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="integer")
     */
    protected $Quantity;
    /**
     * @Column(type="bigint")
     */
    protected $tblBasket;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblItem;

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {

        return $this->Quantity;
    }

    /**
     * @param int $Quantity
     */
    public function setQuantity($Quantity)
    {

        $this->Quantity = $Quantity;
    }

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
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblItem
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
    public function setServiceTblItem(TblItem $tblItem = null)
    {

        $this->serviceTblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }

    /**
     * @return string
     * single ItemPrice
     */
    public function getPrice()
    {

        return number_format($this->Value, 2).' €';
    }

    /**
     * @return string
     */
    public function getSummaryPrice()
    {
        if ($this->Quantity !== 0) {
            $result = $this->Value * $this->Quantity;
        } else {
            $result = $this->Value;
        }
        return number_format($result, 2).' €';
    }


}
