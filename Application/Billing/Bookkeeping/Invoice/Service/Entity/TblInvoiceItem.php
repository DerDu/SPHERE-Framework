<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoiceItem")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoiceItem extends Element
{

    const ATTR_TBL_INVOICE = 'tblInvoice';
    const ATTR_TBL_ITEM = 'tblItem';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_DEBTOR = 'tblDebtor';

    /**
     * @Column(type="bigint")
     */
    protected $tblInvoice;
    /**
     * @Column(type="bigint")
     */
    protected $tblItem;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;

    /**
     * @return bool|TblInvoice
     */
    public function getTblInvoice()
    {

        if (null === $this->tblInvoice) {
            return false;
        } else {
            return Invoice::useService()->getInvoiceById($this->tblInvoice);
        }
    }

    /**
     * @param null|TblInvoice $tblInvoice
     */
    public function setTblInvoice(TblInvoice $tblInvoice = null)
    {

        $this->tblInvoice = ( null === $tblInvoice ? null : $tblInvoice->getId() );
    }

    /**
     * @return bool|TblItem
     */
    public function getTblItem()
    {

        if (null === $this->tblItem) {
            return false;
        } else {
            return Invoice::useService()->getItemById($this->tblItem);
        }
    }

    /**
     * @param null|TblItem $tblItem
     */
    public function setTblItem(TblItem $tblItem = null)
    {

        $this->tblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblDebtor
     */
    public function getServiceTblDebtor()
    {

        if (null === $this->tblDebtor) {
            return false;
        } else {
            return Invoice::useService()->getDebtorById($this->tblDebtor);
        }
    }

    /**
     * @param null|TblDebtor $tblDebtor
     */
    public function setServiceTblDebtor(TblDebtor $tblDebtor = null)
    {

        $this->tblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }
}
