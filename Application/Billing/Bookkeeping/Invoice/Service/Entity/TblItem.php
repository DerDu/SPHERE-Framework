<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem as InventoryTblItem;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItem")
 * @Cache(usage="READ_ONLY")
 */
class TblItem extends Element
{

    const ATTR_SERVICE_TBL_ITEM = 'serviceTblItem';
    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';
    const ATTR_VALUE = 'Value';
    const ATTR_QUANTITY = 'Quantity';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="text")
     */
    protected $Value;
    /**
     * @Column(type="bigint")
     */
    protected $Quantity;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblItem;

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

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
     * @return integer
     */
    public function getQuantity()
    {

        return $this->Quantity;
    }

    /**
     * @param integer $Quantity
     */
    public function setQuantity($Quantity)
    {

        $this->Quantity = $Quantity;
    }

    /**
     * @return bool|InventoryTblItem
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
     * @param InventoryTblItem|null $tblItem
     */
    public function setServiceTblItem(InventoryTblItem $tblItem = null)
    {

        $this->serviceTblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }

    /**
     * @return string
     * single ItemPrice with " €"
     */
    public function getPriceString()
    {

        return number_format($this->Value, 2).' €';
    }

    /**
     * @return string
     * with " €"
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

    /**
     * @return int
     */
    public function getSummaryPriceInt()
    {
        if ($this->Quantity !== 0) {
            $result = $this->Value * $this->Quantity;
        } else {
            $result = $this->Value;
        }
        return number_format($result, 2);
    }
}