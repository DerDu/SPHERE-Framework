<?php
namespace SPHERE\Application\Reporting\SerialLetter\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterCategory;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterField;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialPerson;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Reporting\SerialLetter\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createFilterCategory(TblFilterCategory::IDENTIFIER_PERSON_GROUP);
        $this->createFilterCategory(TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT);
        $this->createFilterCategory(TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT);
        $this->createFilterCategory(TblFilterCategory::IDENTIFIER_COMPANY_GROUP);
    }

    /**
     * @param string $Name
     *
     * @return null|TblFilterCategory
     */
    public function createFilterCategory($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFilterCategory')
            ->findOneBy(array(
                TblFilterCategory::ATTR_NAME => $Name,
            ));

        if (null === $Entity) {
            $Entity = new TblFilterCategory();
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter',
            $Id);
    }

    /**
     * @param string $Name
     *
     * @return false|TblSerialLetter
     */
    public function getSerialLetterByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter',
            array(TblSerialLetter::ATTR_NAME => $Name));
    }

    /**
     * @param int $Id
     *
     * @return false|TblSerialPerson
     */
    public function getSerialPersonById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialPerson',
            $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblFilterCategory
     */
    public function getFilterCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterCategory',
            $Id);
    }

    /**
     * @return bool|TblSerialLetter[]
     */
    public function getSerialLetterAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter');
    }

    /**
     * @return false|TblFilterCategory[]
     */
    public function getFilterCategoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterCategory');
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblFilterField[]
     */
    public function getFilterFieldAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterField',
            array(
                TblFilterField::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()
            ));
    }

    /**
     * @param TblSerialLetter   $tblSerialLetter
     * @param TblFilterCategory $tblFilterCategory
     *
     * @return false|TblFilterField[]
     */
    public function getFilterFieldActiveAllBySerialLetter(TblSerialLetter $tblSerialLetter, TblFilterCategory $tblFilterCategory)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterField',
            array(
                TblFilterField::ATTR_TBL_SERIAL_LETTER   => $tblSerialLetter->getId(),
                TblFilterField::ATTR_TBL_FILTER_CATEGORY => $tblFilterCategory->getId()
            ));
    }

    /**
     * @param $Name
     *
     * @return false|TblFilterCategory
     */
    public function getFilterCategoryByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterCategory',
            array(TblFilterCategory::ATTR_NAME => $Name));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|int
     */
    public function getSerialLetterCount(TblSerialLetter $tblSerialLetter)
    {

        return $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddressPerson',
            array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblSerialPerson[]
     */
    public function getSerialPersonBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialPerson',
            array(TblSerialPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return false|TblSerialPerson
     */
    public function getSerialPersonBySerialLetterAndPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialPerson',
            array(
                TblSerialPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                TblSerialPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblPerson[]
     */
    public function getPersonBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialPerson',
            array(TblSerialPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
        $tblPersonList = array();
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                /** @var TblSerialPerson $Entity */
                $tblPersonList[] = $Entity->getServiceTblPerson();
            }
        }
        $tblPersonList = array_filter($tblPersonList);

        return ( empty($tblPersonList) ? false : $tblPersonList );
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllBySerialLetterAndPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddressPerson', array(
            TblAddressPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
            TblAddressPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddressPerson', array(
            TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()
        ));
    }

    /**
     * @param string                 $Name
     * @param string                 $Description
     * @param null|TblFilterCategory $tblFilterCategory
     *
     * @return TblSerialLetter
     */
    public function createSerialLetter(
        $Name,
        $Description = '',
        TblFilterCategory $tblFilterCategory = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSerialLetter')
            ->findOneBy(array(TblSerialLetter::ATTR_NAME => $Name,));

        if (null === $Entity) {
            $Entity = new TblSerialLetter();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            if ($tblFilterCategory !== null) {
                $Entity->setFilterCategory($tblFilterCategory);
            }

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter   $tblSerialLetter
     * @param TblFilterCategory $tblFilterCategory
     * @param                   $FilterField
     * @param                   $FilterValue
     *
     * @return TblFilterField
     */
    public function createFilterField(TblSerialLetter $tblSerialLetter, TblFilterCategory $tblFilterCategory, $FilterField, $FilterValue)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFilterField')
            ->findOneBy(array(
                TblFilterField::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
//                TblFilterField::ATTR_TBL_FILTER_CATEGORY => $tblFilterCategory->getId(),
                TblFilterField::ATTR_FIELD             => $FilterField
            ));

        /** @var TblFilterField $Entity */
        if ($Entity === null) {
            $Entity = new TblFilterField();
            $Entity->setTblSerialLetter($tblSerialLetter);
            $Entity->setFilterCategory($tblFilterCategory);
            $Entity->setField($FilterField);
            $Entity->setValue($FilterValue);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            $Entity->setFilterCategory($tblFilterCategory);
            $Entity->setValue($FilterValue);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return null|object|TblSerialPerson
     */
    public function addSerialPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSerialPerson')
            ->findOneBy(array(
                TblSerialPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                TblSerialPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblSerialPerson();
            $Entity->setTblSerialLetter($tblSerialLetter);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param string          $Name
     * @param string          $Description
     *
     * @return TblSerialLetter|bool
     */
    public function updateSerialLetter(
        TblSerialLetter $tblSerialLetter,
        $Name,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSerialLetter $Entity */
        $Entity = $Manager->getEntityById('TblSerialLetter', $tblSerialLetter->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return $Entity;
        }

        return false;
    }

    /**
     * @param TblSerialLetter    $tblSerialLetter
     * @param TblPerson          $tblPerson
     * @param TblPerson          $tblPersonToAddress
     * @param TblToPerson        $tblToPerson
     * @param TblToCompany       $tblToCompany
     * @param TblSalutation|null $tblSalutation
     *
     * @return TblAddressPerson
     */
    public function createAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblPerson $tblPersonToAddress,
        TblToPerson $tblToPerson = null,
        TblToCompany $tblToCompany = null,
        TblSalutation $tblSalutation = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblAddressPerson')
            ->findOneBy(array(
                TblAddressPerson::ATTR_TBL_SERIAL_LETTER             => $tblSerialLetter->getId(),
                TblAddressPerson::ATTR_SERVICE_TBL_PERSON            => $tblPerson->getId(),
                TblAddressPerson::ATTR_SERVICE_TBL_PERSON_TO_ADDRESS => $tblPersonToAddress->getId(),
                TblAddressPerson::ATTR_SERVICE_TBL_TO_PERSON         => $tblToPerson ? $tblToPerson->getId() : null
            ));

        if (null === $Entity) {
            $Entity = new TblAddressPerson();
            $Entity->setTblSerialLetter($tblSerialLetter);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblPersonToAddress($tblPersonToAddress);
            $Entity->setServiceTblToPerson($tblToPerson, $tblToCompany);
            $Entity->setServiceTblSalutation($tblSalutation);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroyAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblAddressPerson')
            ->findBy(array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        return true;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroyFilterFiledAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblFilterField')
            ->findBy(array(TblFilterField::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        return true;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool
     */
    public function destroyAddressPersonAllBySerialLetterAndPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $EntityItems = $Manager->getEntity('TblAddressPerson')
            ->findBy(array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                           TblAddressPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        return true;
    }

    /**
     * @param TblAddressPerson $tblAddressPerson
     *
     * @return bool
     */
    public function destroyAddressPerson(TblAddressPerson $tblAddressPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntityById('TblAddressPerson', $tblAddressPerson->getId());
        /** @var TblAddressPerson $Entity */
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $this->destroyAddressPersonAllBySerialLetter($tblSerialLetter);
        $this->destroyFilterFiledAllBySerialLetter($tblSerialLetter);

        /** @var TblSerialLetter $Entity */
        $Entity = $Manager->getEntityById('TblSerialLetter', $tblSerialLetter->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool
     */
    public function removeSerialPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSerialPerson')
            ->findOneBy(array(
                TblSerialPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                TblSerialPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        /** @var TblSerialPerson $Entity */
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSerialPerson $tblSerialPerson
     *
     * @return bool
     */
    public function destroySerialPerson(TblSerialPerson $tblSerialPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntityById('TblSerialPerson', $tblSerialPerson->getId());

        /** @var TblSerialPerson $Entity */
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}