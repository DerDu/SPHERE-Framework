<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor as BankingDebtor;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDebtor")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtor extends Element
{

    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';
    const ATTR_IBAN = 'IBAN';

    /**
     * @Column(type="string")
     */
    protected $DebtorNumber;
    /**
     * @Column(type="string")
     */
    protected $DebtorPerson;
    /**
     * @Column(type="string")
     */
    protected $BankReference;
    /**
     * @Column(type="string")
     */
    protected $Owner;
    /**
     * @Column(type="string")
     */
    protected $BankName;
    /**
     * @Column(type="string")
     */
    protected $IBAN;
    /**
     * @Column(type="string")
     */
    protected $BIC;
    /**
     * @Column(type="string")
     */
    protected $CashSign;
    /**
     * @Column(type="string")
     */
    protected $CreditorId;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDebtor;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblBankReference;

    /**
     * @return string
     */
    public function getDebtorNumber()
    {

        return $this->DebtorNumber;
    }

    /**
     * @param string $DebtorNumber
     */
    public function setDebtorNumber($DebtorNumber)
    {

        $this->DebtorNumber = $DebtorNumber;
    }

    /**
     * @return string
     */
    public function getDebtorPerson()
    {

        return $this->DebtorPerson;
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setDebtorPerson(TblPerson $tblPerson)
    {

        $this->DebtorPerson = ( $tblPerson !== false ? $tblPerson->getFullName() : '' );
    }

    /**
     * @return string
     */
    public function getBankReference()
    {

        $this->BankReference;
    }

    /**
     * @param $BankReference
     */
    public function setBankReference($BankReference)
    {

        $this->BankReference = $BankReference;
    }

    /**
     * @return string
     */
    public function getOwner()
    {

        $this->Owner;
    }

    /**
     * @param $Owner
     */
    public function setOwner($Owner)
    {

        $this->Owner = $Owner;
    }

    /**
     * @return string
     */
    public function getBankName()
    {

        $this->BankName;
    }

    /**
     * @param $BankName
     */
    public function setBankName($BankName)
    {

        $this->BankName = $BankName;
    }

    /**
     * @return string
     */
    public function getIBAN()
    {

        $this->IBAN;
    }

    /**
     * @param $IBAN
     */
    public function setIBAN($IBAN)
    {

        $this->IBAN = $IBAN;
    }

    /**
     * @return string
     */
    public function getBIC()
    {

        $this->BIC;
    }

    /**
     * @param $BIC
     */
    public function setBIC($BIC)
    {

        $this->BIC = $BIC;
    }

    /**
     * @return string
     */
    public function getCashSign()
    {

        $this->CashSign;
    }

    /**
     * @param $CashSign
     */
    public function setCashSign($CashSign)
    {

        $this->CashSign = $CashSign;
    }

    /**
     * @return string
     */
    public function getCreditorId()
    {

        $this->CreditorId;
    }

    /**
     * @param $CreditorId
     */
    public function setCreditorId($CreditorId)
    {

        $this->CreditorId = $CreditorId;
    }

    /**
     * @return bool|BankingDebtor
     */
    public function getServiceTblDebtor()
    {

        if (null === $this->serviceTblDebtor) {
            return false;
        } else {
            return Banking::useService()->getDebtorById($this->serviceTblDebtor);
        }
    }

    /**
     * @param BankingDebtor|null $tblDebtor
     */
    public function setServiceTblDebtor(BankingDebtor $tblDebtor = null)
    {

        $this->serviceTblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }

    /**
     * @return bool|TblBankReference
     */
    public function getServiceTblBankReference()
    {

        if (null === $this->serviceTblBankReference) {
            return false;
        } else {
            return Banking::useService()->getBankReferenceById($this->serviceTblBankReference);
        }
    }

    /**
     * @param TblBankReference|null $tblBankReference
     */
    public function setServiceTblBankReference(TblBankReference $tblBankReference = null)
    {

        $this->serviceTblBankReference = ( null === $tblBankReference ? null : $tblBankReference->getId() );
    }
}