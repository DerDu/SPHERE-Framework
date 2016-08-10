<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoicePayment")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoicePayment extends Element
{

    const ATTR_TBL_PAYMENT = 'tblPayment';
    const ATTR_SERVICE_TBL_INVOICE = 'serviceTblInvoice';

    /**
     * @Column(type="bigint")
     */
    protected $tblPayment;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblInvoice;


    /**
     * @return bool|TblPayment
     */
    public function getTblPayment()
    {

        if (null === $this->tblPayment) {
            return false;
        } else {
            return Balance::useService()->getPaymentById($this->tblPayment);
        }
    }

    /**
     * @param TblPayment|null $tblPayment
     */
    public function setTblPayment(TblPayment $tblPayment = null)
    {

        $this->tblPayment = ( null === $tblPayment ? null : $tblPayment->getId() );
    }

    /**
     * @return bool|TblInvoice
     */
    public function getServiceTblInvoice()
    {

        if (null === $this->serviceTblInvoice) {
            return false;
        } else {
            return Invoice::useService()->getInvoiceById($this->serviceTblInvoice);
        }
    }

    /**
     * @param TblInvoice|null $serviceTblInvoice
     */
    public function setServiceTblInvoice(TblInvoice $serviceTblInvoice = null)
    {

        $this->serviceTblInvoice = ( null === $serviceTblInvoice ? null : $serviceTblInvoice->getId() );
    }

}
