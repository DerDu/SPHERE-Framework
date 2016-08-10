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
 * @Table(name="tblItem")
 * @Cache(usage="READ_ONLY")
 */
class TblItem extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="text")
     */
    protected $Name;
    /**
     * @Column(type="bigint")
     */
    protected $tblItemType;


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
     * @return bool|TblItem
     */
    public function getTblItemType()
    {

        if (null === $this->tblItemType) {
            return false;
        } else {
            return Item::useService()->getItemTypeById($this->tblItemType);
        }
    }

    /**
     * @param TblItemType $tblItemType
     */
    public function setTblItemType(TblItemType $tblItemType)
    {

        $this->tblItemType = ( null === $tblItemType ? null : $tblItemType->getId() );
    }

    /**
     * @return string
     */
    public function getDisplayDescription()
    {
        return nl2br($this->getDescription());
    }
}
