<?php
namespace SPHERE\Application\Reporting\Dynamic\Service\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Reporting\Dynamic\Dynamic;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDynamicFilterOption")
 * @Cache(usage="READ_ONLY")
 */
class TblDynamicFilterOption extends Element
{

    const TBL_DYNAMIC_FILTER_MASK = 'tblDynamicFilterMask';
    const PROPERTY_FILTER_FIELD_NAME = 'FilterFieldName';

    /**
     * @Column(type="bigint")
     */
    protected $tblDynamicFilterMask;
    /**
     * @Column(type="string")
     */
    protected $FilterFieldName;
    /**
     * @Column(type="boolean")
     */
    protected $IsMandatory;

    /**
     * @return string
     */
    public function getFilterFieldName()
    {

        return (string)$this->FilterFieldName;
    }

    /**
     * @param string $FilterFieldName
     *
     * @return TblDynamicFilterOption
     */
    public function setFilterFieldName($FilterFieldName)
    {

        $this->FilterFieldName = (string)$FilterFieldName;
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
     * @return TblDynamicFilterOption
     */
    public function setTblDynamicFilterMask(TblDynamicFilterMask $tblDynamicFilterMask)
    {

        $this->tblDynamicFilterMask = $tblDynamicFilterMask->getId();
        return $this;
    }

    /**
     * @return bool
     */
    public function isMandatory()
    {

        return (bool)$this->IsMandatory;
    }

    /**
     * @param bool $IsMandatory
     *
     * @return TblDynamicFilterOption
     */
    public function setMandatory($IsMandatory)
    {

        $this->IsMandatory = (bool)$IsMandatory;
        return $this;
    }
}
