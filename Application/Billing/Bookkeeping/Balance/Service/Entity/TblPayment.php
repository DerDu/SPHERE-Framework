<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPayment")
 * @Cache(usage="READ_ONLY")
 */
class TblPayment extends Element
{

    const ATTR_TBL_PAYMENT_TYPE = 'tblPaymentType';
    const ATTR_PURPOSE = 'Purpose';

    /**
     * @Column(type="bigint")
     */
    protected $tblPaymentType;
    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="string")
     */
    protected $Purpose;

    /**
     * @return bool|TblPaymentType
     */
    public function getTblPaymentType()
    {

        if (null === $this->tblPaymentType) {
            return false;
        } else {
            return Balance::useService()->getPaymentTypeById($this->tblPaymentType);
        }
    }

    /**
     * @param TblPaymentType|null $tblPaymentType
     */
    public function setTblPaymentType(TblPaymentType $tblPaymentType = null)
    {

        $this->tblPaymentType = ( null === $tblPaymentType ? null : $tblPaymentType->getId() );
    }

    /**
     * @return string $Purpose
     */
    public function getPurpose()
    {

        return $this->Purpose;
    }

    /**
     * @param string $Purpose
     */
    public function setPurpose($Purpose)
    {

        $this->Purpose = $Purpose;
    }

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function getValueString()
    {

        $result = sprintf("%01.2f", $this->Value);
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @return string
     */
    public function getLastDate()
    {

        if ($this->getEntityUpdate() != null) {
            return $this->getEntityUpdate()->format('d.m.Y');
        } else {
            return $this->getEntityCreate()->format('d.m.Y');
        }
    }
}
