<?php
namespace SPHERE\Application\Reporting\Dynamic\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Reporting\Dynamic\Dynamic;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDynamicFilterSearch")
 * @Cache(usage="READ_ONLY")
 */
class TblDynamicFilterSearch extends Element
{

    const TBL_DYNAMIC_FILTER_MASK = 'tblDynamicFilterMask';
    const TBL_DYNAMIC_FILTER_OPTION = 'tblDynamicFilterOption';

    /**
     * @Column(type="bigint")
     */
    protected $tblDynamicFilterMask;
    /**
     * @Column(type="bigint")
     */
    protected $tblDynamicFilterOption;
    /**
     * @Column(type="string")
     */
    protected $FilterFieldValue;

    /**
     * @return string
     */
    public function getFilterFieldValue()
    {

        return (string)$this->FilterFieldValue;
    }

    /**
     * @param string $FilterFieldValue
     *
     * @return TblDynamicFilterSearch
     */
    public function setFilterFieldValue($FilterFieldValue)
    {

        $this->FilterFieldValue = (string)$FilterFieldValue;
        return $this;
    }

    /**
     * @return bool|TblDynamicFilterMask
     */
    public function getTblDynamicFilterMask()
    {

        return Dynamic::useService()->getDynamicFilterMaskById($this->tblDynamicFilterMask);
    }

    /**
     * @param TblDynamicFilterMask $tblDynamicFilterMask
     *
     * @return TblDynamicFilterSearch
     */
    public function setTblDynamicFilterMask(TblDynamicFilterMask $tblDynamicFilterMask)
    {

        $this->tblDynamicFilterMask = $tblDynamicFilterMask->getId();
        return $this;
    }

    /**
     * @return bool|TblDynamicFilterOption
     */
    public function getTblDynamicFilterOption()
    {

        return Dynamic::useService()->getDynamicFilterOptionById($this->tblDynamicFilterOption);
    }

    /**
     * @param TblDynamicFilterOption $tblDynamicFilterOption
     *
     * @return TblDynamicFilterSearch
     */
    public function setTblDynamicFilterOption(TblDynamicFilterOption $tblDynamicFilterOption)
    {

        $this->tblDynamicFilterOption = $tblDynamicFilterOption->getId();
        return $this;
    }
}
