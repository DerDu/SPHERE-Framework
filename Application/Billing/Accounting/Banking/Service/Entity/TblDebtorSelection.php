<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDebtorSelection")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtorSelection extends Element
{

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_PERSON_PAYERS = 'serviceTblPersonPayers';
    const ATTR_SERVICE_TBL_PAYMENT_TYPE = 'serviceTblPaymentType';
    const ATTR_SERVICE_TBL_ITEM = 'serviceTblItem';
    const ATTR_TBL_DEBTOR = 'tblDebtor';
    const ATTR_TBL_BANK_REFERENCE = 'tblBankReference';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonPayers;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPaymentType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblItem;
    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;
    /**
     * @Column(type="bigint")
     */
    protected $tblBankReference;

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
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonPayers()
    {

        if (null === $this->serviceTblPersonPayers) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonPayers);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonPayers(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonPayers = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblPaymentType
     */
    public function getServiceTblPaymentType()
    {

        if (null === $this->serviceTblPaymentType) {
            return false;
        } else {
            return Balance::useService()->getPaymentTypeById($this->serviceTblPaymentType);
        }
    }

    /**
     * @param TblPaymentType|null $tblPaymentType
     */
    public function setServiceTblPaymentType(TblPaymentType $tblPaymentType = null)
    {

        $this->serviceTblPaymentType = ( null === $tblPaymentType ? null : $tblPaymentType->getId() );
    }

    /**
     * @return bool|TblItem
     */
    public function getServiceTblInventoryItem()
    {

        if (null === $this->serviceTblItem) {
            return false;
        } else {
            return Item::useService()->getItemById($this->serviceTblItem);
        }
    }

    /**
     * @param TblItem|null $tblItem
     */
    public function setServiceTblInventoryItem(TblItem $tblItem = null)
    {

        $this->serviceTblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }

    /**
     * @return bool|TblDebtor
     */
    public function getTblDebtor()
    {

        if (null === $this->tblDebtor) {
            return false;
        } else {
            return Banking::useService()->getDebtorById($this->tblDebtor);
        }
    }

    /**
     * @param null|TblDebtor $tblDebtor
     */
    public function setTblDebtor(TblDebtor $tblDebtor = null)
    {

        $this->tblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }

    /**
     * @return bool|TblBankReference
     */
    public function getTblBankReference()
    {

        if (null === $this->tblBankReference) {
            return false;
        } else {
            return Banking::useService()->getBankReferenceById($this->tblBankReference);
        }
    }

    /**
     * @param null|TblBankReference $tblBankReference
     */
    public function setTblBankReference(TblBankReference $tblBankReference = null)
    {

        $this->tblBankReference = ( null === $tblBankReference ? null : $tblBankReference->getId() );
    }
}
