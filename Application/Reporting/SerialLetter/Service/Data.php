<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2016
 * Time: 14:51
 */

namespace SPHERE\Application\Reporting\SerialLetter\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Reporting\SerialLetter\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter',
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
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson $tblPerson
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllByPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddressPerson', array(
            TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
            TblAddressPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ) );
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
     * @param $Name
     * @param TblGroup $tblGroup
     * @param string $Description
     *
     * @return TblSerialLetter
     */
    public function createSerialLetter(
        $Name,
        TblGroup $tblGroup,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSerialLetter')
            ->findOneBy(array(
                TblSerialLetter::ATTR_NAME => $Name,
                TblSerialLetter::ATTR_SERVICE_TBL_GROUP => $tblGroup->getId()
            ));

        if (null === $Entity) {
            $Entity = new TblSerialLetter();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setServiceTblGroup($tblGroup);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param $Name
     * @param TblGroup $tblGroup
     * @param $Description
     *
     * @return bool
     */
    public function updateSerialLetter(
        TblSerialLetter $tblSerialLetter,
        $Name,
        TblGroup $tblGroup,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSerialLetter $Entity */
        $Entity = $Manager->getEntityById('TblSerialLetter', $tblSerialLetter->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setServiceTblGroup($tblGroup);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson $tblPerson
     * @param TblPerson $tblPersonToAddress
     * @param TblToPerson $tblToPerson
     * @param TblSalutation|null $tblSalutation
     *
     * @return TblAddressPerson
     */
    public function createAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblPerson $tblPersonToAddress,
        TblToPerson $tblToPerson,
        TblSalutation $tblSalutation = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblAddressPerson')
            ->findOneBy(array(
                TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
                TblAddressPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblAddressPerson::ATTR_SERVICE_TBL_PERSON_TO_ADDRESS => $tblPersonToAddress->getId(),
                TblAddressPerson::ATTR_SERVICE_TBL_TO_PERSON => $tblToPerson ? $tblToPerson->getId() : null
            ));

        if (null === $Entity) {
            $Entity = new TblAddressPerson();
            $Entity->setTblSerialLetter($tblSerialLetter);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblPersonToAddress($tblPersonToAddress);
            $Entity->setServiceTblToPerson($tblToPerson);
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

        $EntityItems = $Manager->getEntity('TblAddressPerson')
            ->findBy(array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
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
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $this->destroyAddressPersonAllBySerialLetter($tblSerialLetter);

        /** @var TblSerialLetter $Entity */
        $Entity = $Manager->getEntityById('TblSerialLetter', $tblSerialLetter->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}