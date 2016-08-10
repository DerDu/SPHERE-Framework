<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItemCalculation")
 * @Cache(usage="READ_ONLY")
 */
class TblItemCalculation extends Element
{

    const ATTR_TBL_ITEM = 'tblItem';
    const ATTR_TBL_CALCULATION = 'tblCalculation';

    /**
     * @Column(type="bigint")
     */
    protected $tblItem;
    /**
     * @Column(type="bigint")
     */
    protected $tblCalculation;


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
     * @param TblItem $tblItem
     */
    public function setTblItem(TblItem $tblItem)
    {

        $this->tblItem = $tblItem->getId();
    }

    /**
     * @return bool|TblItem
     */
    public function getTblCalculation()
    {

        if (null === $this->tblCalculation) {
            return false;
        } else {
            return Item::useService()->getCalculationById($this->tblCalculation);
        }
    }

    /**
     * @param TblCalculation $tblCalculation
     */
    public function setTblCalculation(TblCalculation $tblCalculation)
    {

        $this->tblCalculation = $tblCalculation->getId();
    }
}
