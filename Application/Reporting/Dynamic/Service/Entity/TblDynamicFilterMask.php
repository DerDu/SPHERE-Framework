<?php
namespace SPHERE\Application\Reporting\Dynamic\Service\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Reporting\Dynamic\Dynamic;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDynamicFilterMask")
 * @Cache(usage="READ_ONLY")
 */
class TblDynamicFilterMask extends Element
{

    const TBL_DYNAMIC_FILTER = 'tblDynamicFilter';
    const PROPERTY_FILTER_PILE_ORDER = 'FilterPileOrder';
    /**
     * @Column(type="bigint")
     */
    protected $tblDynamicFilter;
    /**
     * @Column(type="integer")
     */
    protected $FilterPileOrder;
    /**
     * @Column(type="string")
     */
    protected $FilterClassName;

    /**
     * @return int
     */
    public function getFilterPileOrder()
    {

        return (int)$this->FilterPileOrder;
    }

    /**
     * @param int $FilterPileOrder
     *
     * @return TblDynamicFilterMask
     */
    public function setFilterPileOrder($FilterPileOrder)
    {

        $this->FilterPileOrder = (int)$FilterPileOrder;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilterClassName()
    {

        return (string)$this->FilterClassName;
    }

    /**
     * @param string $FilterClassName
     *
     * @return TblDynamicFilterMask
     */
    public function setFilterClassName($FilterClassName)
    {

        $this->FilterClassName = (string)$FilterClassName;
        return $this;
    }

    /**
     * @return AbstractView
     */
    public function getFilterClassInstance()
    {

        return new $this->FilterClassName();
    }

    /**
     * @return bool|TblDynamicFilter
     */
    public function getTblDynamicFilter()
    {

        return Dynamic::useService()->getDynamicFilterById($this->tblDynamicFilter);
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     */
    public function setTblDynamicFilter(TblDynamicFilter $tblDynamicFilter)
    {

        $this->tblDynamicFilter = $tblDynamicFilter->getId();
    }
}
