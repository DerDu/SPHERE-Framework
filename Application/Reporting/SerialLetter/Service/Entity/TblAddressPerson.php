<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2016
 * Time: 14:54
 */

namespace SPHERE\Application\Reporting\SerialLetter\Service\Entity;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Reporting\SerialLetter\SerialLetter;
use SPHERE\System\Database\Fitting\Element;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity()
 * @Table(name="tblAddressPerson")
 * @Cache(usage="READ_ONLY")
 */
class TblAddressPerson extends Element
{

    const ATTR_TBL_SERIAL_LETTER = 'tblSerialLetter';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_PERSON_TO_ADDRESS = 'serviceTblPersonToAddress';
    const ATTR_SERVICE_TBL_TO_PERSON = 'serviceTblToPerson';

    const SALUTATION_FAMILY = 1000;

    /**
     * @Column(type="bigint")
     */
    protected $tblSerialLetter;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonToAddress;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblToPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSalutation;

    /**
     * @return bool|TblSerialLetter
     */
    public function getTblSerialLetter()
    {

        if (null === $this->tblSerialLetter) {
            return false;
        } else {
            return SerialLetter::useService()->getSerialLetterById($this->tblSerialLetter);
        }
    }

    /**
     * @param null|TblSerialLetter $tblSerialLetter
     */
    public function setTblSerialLetter(TblSerialLetter $tblSerialLetter = null)
    {

        $this->tblSerialLetter = ( null === $tblSerialLetter ? null : $tblSerialLetter->getId() );
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
     * @return bool|TblPerson
     */
    public function getServiceTblPersonToAddress()
    {

        if (null === $this->serviceTblPersonToAddress) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonToAddress);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonToAddress(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonToAddress = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @param TblFilterCategory|null $tblFilterCategory
     *
     * @return bool|TblToPerson|TblToCompany
     */
    public function getServiceTblToPerson(TblFilterCategory $tblFilterCategory = null)
    {

        if (null === $this->serviceTblToPerson) {
            return false;
        } else {
            if ($tblFilterCategory != null && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                return Address::useService()->getAddressToCompanyById($this->serviceTblToPerson);
            } else {
                return Address::useService()->getAddressToPersonById($this->serviceTblToPerson);
            }
        }
    }

    /**
     * @param TblToPerson|null $tblToPerson
     * @param TblToCompany     $tblToCompany
     */
    public function setServiceTblToPerson(TblToPerson $tblToPerson = null, TblToCompany $tblToCompany = null)
    {

        if ($tblToCompany === null) {
            $this->serviceTblToPerson = ( null === $tblToPerson ? null : $tblToPerson->getId() );
        } else {
            $this->serviceTblToPerson = $tblToCompany->getId();
        }
    }

    /**
     * @return bool|TblSalutation
     */
    public function getServiceTblSalutation()
    {

        if (null === $this->serviceTblSalutation) {
            return false;
        } else {
            if ($this->serviceTblSalutation == 1000){
                $tblSalutation = new TblSalutation('Familie');
                $tblSalutation->setId(TblAddressPerson::SALUTATION_FAMILY);

                return $tblSalutation;
            } else {
                return Person::useService()->getSalutationById($this->serviceTblSalutation);
            }
        }
    }

    /**
     * @param null|TblSalutation $tblSalutation
     */
    public function setServiceTblSalutation(TblSalutation $tblSalutation = null)
    {

        $this->serviceTblSalutation = ( null === $tblSalutation ? null : $tblSalutation->getId() );
    }
}