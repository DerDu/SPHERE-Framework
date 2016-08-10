<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCalculation")
 * @Cache(usage="READ_ONLY")
 */
class TblCalculation extends Element
{

    const ATTR_SERVICE_TBL_TYPE = 'serviceTblType';
    const ATTR_SERVICE_TBL_SIBLING_RANK = 'serviceTblSiblingRank';

    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSiblingRank;

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
     * @return string
     */
    public function getPriceString()
    {

        $result = sprintf("%01.2f", $this->Value);
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblType()
    {

        if (null === $this->serviceTblType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblType);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceTblType(TblType $tblType = null)
    {

        $this->serviceTblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblSiblingRank
     */
    public function getServiceTblSiblingRank()
    {

        if (null === $this->serviceTblSiblingRank) {
            return false;
        } else {
            return Relationship::useService()->getSiblingRankById($this->serviceTblSiblingRank);
        }
    }

    /**
     * @param null|TblSiblingRank $tblSiblingRank
     */
    public function setServiceTblSiblingRank(TblSiblingRank $tblSiblingRank = null)
    {

        $this->serviceTblSiblingRank = ( null === $tblSiblingRank ? null : $tblSiblingRank->getId() );
    }
}
