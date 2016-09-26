<?php

namespace SPHERE\Application\Reporting\CheckList\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Reporting\CheckList\CheckList;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblListElementList")
 * @Cache(usage="READ_ONLY")
 */
class TblListElementList extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_TBL_LIST = 'tblList';
    const ATTR_TBL_ELEMENT_TYPE = 'tblElementType';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="bigint")
     */
    protected $tblList;

    /**
     * @Column(type="bigint")
     */
    protected $tblElementType;
    /**
     * @Column(type="integer")
     */
    protected $SortOrder;

    /**
     * @return mixed
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return bool|TblList
     */
    public function getTblList()
    {

        if (null === $this->tblList) {
            return false;
        } else {
            return CheckList::useService()->getListById($this->tblList);
        }
    }

    /**
     * @param TblList|null $tblList
     */
    public function setTblList($tblList)
    {

        $this->tblList = ( null === $tblList ? null : $tblList->getId() );
    }

    /**
     * @return bool|TblElementType
     */
    public function getTblElementType()
    {

        if (null === $this->tblElementType) {
            return false;
        } else {
            return CheckList::useService()->getElementTypeById($this->tblElementType);
        }
    }

    /**
     * @param TblElementType|null $tblElementType
     */
    public function setTblElementType($tblElementType)
    {

        $this->tblElementType = ( null === $tblElementType ? null : $tblElementType->getId() );
    }

    /**
     * @return integer|null
     */
    public function getSortOrder()
    {
        return $this->SortOrder;
    }

    /**
     * @param integer|null $SortOrder
     */
    public function setSortOrder($SortOrder)
    {
        $this->SortOrder = $SortOrder;
    }
}
