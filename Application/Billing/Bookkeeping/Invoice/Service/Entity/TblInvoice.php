<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoice")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoice extends Element
{

    const ATTR_INVOICE_NUMBER = 'InvoiceNumber';
    const ATTR_IS_PAID = 'IsPaid';
    const ATTR_IS_REVERSAL = 'IsReversal';
    const ATTR_TARGET_TIME = 'TargetTime';

    /**
     * @Column(type="string")
     */
    protected $InvoiceNumber;
    /**
     * @Column(type="datetime")
     */
    protected $TargetTime;
    /**
     * @Column(type="string")
     */
    protected $SchoolName;
    /**
     * @Column(type="string")
     */
    protected $SchoolOwner;
    /**
     * @Column(type="string")
     */
    protected $SchoolBankName;
    /**
     * @Column(type="string")
     */
    protected $SchoolIBAN;
    /**
     * @Column(type="string")
     */
    protected $SchoolBIC;
//    /**
//     * @Column(type="decimal", precision=14, scale=4)
//     */
//    protected $Discount;
    /**
     * @Column(type="boolean")
     */
    protected $IsPaid;
    /**
     * @Column(type="boolean")
     */
    protected $IsReversal;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAddress;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblMail;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPhone;

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {

        return $this->InvoiceNumber;
    }

    /**
     * @param string $InvoiceNumber
     */
    public function setInvoiceNumber($InvoiceNumber)
    {

        $this->InvoiceNumber = $InvoiceNumber;
    }

    /**
     * @return bool|string
     */
    public function getTargetTime()
    {

        if (null === $this->TargetTime) {
            return false;
        }
        /** @var \DateTime $InvoiceDate */
        $TargetDate = $this->TargetTime;
        if ($TargetDate instanceof \DateTime) {
            return $TargetDate->format('d.m.Y');
        } else {
            return (string)$TargetDate;
        }
    }

    /**
     * @param \DateTime|null $Date
     */
    public function setTargetTime(\DateTime $Date = null)
    {

        $this->TargetTime = $Date;
    }

    /**
     * @return string
     */
    public function getSchoolName()
    {

        return $this->SchoolName;
    }

    /**
     * @param string $SchoolName
     */
    public function setSchoolName($SchoolName)
    {

        $this->SchoolName = $SchoolName;
    }

    /**
     * @return string
     */
    public function getSchoolOwner()
    {

        return $this->SchoolOwner;
    }

    /**
     * @param string $SchoolOwner
     */
    public function setSchoolOwner($SchoolOwner)
    {

        $this->SchoolOwner = $SchoolOwner;
    }

    /**
     * @return string
     */
    public function getSchoolBankName()
    {

        return $this->SchoolBankName;
    }

    /**
     * @param string $SchoolBankName
     */
    public function setSchoolBankName($SchoolBankName)
    {

        $this->SchoolBankName = $SchoolBankName;
    }

    /**
     * @return string
     */
    public function getSchoolIBAN()
    {

        return $this->SchoolIBAN;
    }

    /**
     * @param string $SchoolIBAN
     */
    public function setSchoolIBAN($SchoolIBAN)
    {

        $this->SchoolIBAN = $SchoolIBAN;
    }

    /**
     * @return string
     */
    public function getSchoolBIC()
    {

        return $this->SchoolBIC;
    }

    /**
     * @param string $SchoolBIC
     */
    public function setSchoolBIC($SchoolBIC)
    {

        $this->SchoolBIC = $SchoolBIC;
    }

//    /**
//     * @return (type="decimal", precision=14, scale=4)
//     */
//    public function getDiscount()
//    {
//
//        return $this->Discount;
//    }
//
//    /**
//     * @param (type="decimal", precision=14, scale=4) $Price
//     */
//    public function setDiscount($Discount)
//    {
//
//        $this->Discount = $Discount;
//    }

    /**
     * @return boolean
     */
    public function getIsPaid()
    {

        return $this->IsPaid;
    }

    /**
     * @param boolean $isPaid
     */
    public function setIsPaid($isPaid)
    {

        $this->IsPaid = $isPaid;
    }

    /**
     * @return boolean
     */
    public function getIsReversal()
    {

        return $this->IsReversal;
    }

    /**
     * @param boolean $IsReversal
     */
    public function setIsReversal($IsReversal)
    {

        $this->IsReversal = $IsReversal;
    }

    /**
     * @return bool|TblAddress
     */
    public function getServiceTblAddress()
    {

        if (null === $this->serviceTblAddress) {
            return false;
        } else {
            return Address::useService()->getAddressById($this->serviceTblAddress);
        }
    }

    /**
     * @param TblAddress $tblAddress
     */
    public function setServiceTblAddress(TblAddress $tblAddress = null)
    {

        $this->serviceTblAddress = ( null === $tblAddress ? null : $tblAddress->getId() );
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
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblMail
     */
    public function getServiceTblMail()
    {

        if (null === $this->serviceTblMail) {
            return false;
        } else {
            return Mail::useService()->getMailById($this->serviceTblMail);
        }
    }

    /**
     * @param TblMail $tblMail
     */
    public function setServiceTblMail(TblMail $tblMail = null)
    {

        $this->serviceTblMail = ( null === $tblMail ? null : $tblMail->getId() );
    }

    /**
     * @return bool|TblPhone
     */
    public function getServiceTblPhone()
    {

        if (null === $this->serviceTblPhone) {
            return false;
        } else {
            return Phone::useService()->getPhoneById($this->serviceTblPhone);
        }
    }

    /**
     * @param TblPhone $tblPhone
     */
    public function setServiceTblPhone(TblPhone $tblPhone = null)
    {

        $this->serviceTblPhone = ( null === $tblPhone ? null : $tblPhone->getId() );
    }
}
