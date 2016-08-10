<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBankReference")
 * @Cache(usage="READ_ONLY")
 */
class TblBankReference extends Element
{

    const ATTR_REFERENCE_NUMBER = 'Reference';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_BANK_ACCOUNT = 'tblBankAccount';
    const ATTR_IBAN = 'IBAN';

    /**
     * @Column(type="string")
     */
    protected $Reference;
    /**
     * @Column(type="date")
     */
    protected $ReferenceDate;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
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
    protected $Owner;

    /**
     * @return string $Reference
     */
    public function getReference()
    {

        return $this->Reference;
    }

    /**
     * @param string $Reference
     */
    public function setReference($Reference)
    {

        $this->Reference = $Reference;
    }

    /**
     * @return string
     */
    public function getReferenceDate()
    {

        if (null === $this->ReferenceDate) {
            return false;
        }
        /** @var \DateTime $ReferenceDate */
        $ReferenceDate = $this->ReferenceDate;
        if ($ReferenceDate instanceof \DateTime) {
            return $ReferenceDate->format('d.m.Y');
        } else {
            return (string)$ReferenceDate;
        }
    }

    /**
     * @param \DateTime $ReferenceDate
     */
    public function setReferenceDate(\DateTime $ReferenceDate)
    {

        $this->ReferenceDate = $ReferenceDate;
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
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return string $BankName
     */
    public function getBankName()
    {

        return $this->BankName;
    }

    /**
     * @param string $BankName
     */
    public function setBankName($BankName)
    {

        $this->BankName = $BankName;
    }

    /**
     * @return string $IBAN
     */
    public function getIBAN()
    {

        return $this->IBAN;
    }

    /**
     * @param string $IBAN
     */
    public function setIBAN($IBAN)
    {

        $this->IBAN = strtoupper(substr(str_replace(' ', '', $IBAN), 0, 34));
    }

    /**
     * @return string $BIC
     */
    public function getBIC()
    {

        return $this->BIC;
    }

    /**
     * @param string $BIC
     */
    public function setBIC($BIC)
    {

        $this->BIC = strtoupper(substr(str_replace(' ', '', $BIC), 0, 11));
    }

    /**
     * @return string $Owner
     */
    public function getOwner()
    {

        return $this->Owner;
    }

    /**
     * @param string $Owner
     */
    public function setOwner($Owner)
    {

        $this->Owner = $Owner;
    }

    /**
     * @return string
     */
    public function getIBANFrontend()
    {

        $IBAN = $this->IBAN;
        $tmp = array();
        for ($i = 0, $j = strlen($IBAN); $i < $j; $i += 4) {
            array_push($tmp, substr($IBAN, $i, 4));
        }
        $result = implode(' ', $tmp);
        return $result;
    }

    /**
     * @return string
     */
    public function getBICFrontend()
    {

        $BIC = $this->BIC;
        $tmp = array();
        array_push($tmp, substr($BIC, 0, 4));
        array_push($tmp, substr($BIC, 4, 2));
        array_push($tmp, substr($BIC, 6, 2));
        array_push($tmp, substr($BIC, 8, 3));
        $result = implode(' ', $tmp);
        return $result;
    }
}
