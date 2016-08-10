<?php
namespace SPHERE\Application\Contact\Address\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Address\Service\Entity\TblType;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\ColumnHydrator;
use SPHERE\System\Database\Fitting\IdHydrator;

/**
 * Class Data
 *
 * @package SPHERE\Application\Contact\Address\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewAddressToPerson[]
     */
    public function viewAddressToPersonAll()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewAddressToPerson'
        );
    }

    /**
     * @return false|ViewAddressToCompany[]
     */
    public function viewAddressToCompanyAll()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewAddressToCompany'
        );
    }

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {

        $this->createType('Hauptadresse');
        $this->createType('Zweit-/Nebenadresse');
        $this->createType('Rechnungsadresse');
        $this->createType('Lieferadresse');

        $this->createState('Baden-Württemberg');
        $this->createState('Bremen');
        $this->createState('Niedersachsen');
        $this->createState('Sachsen');
        $this->createState('Bayern');
        $this->createState('Hamburg');
        $this->createState('Nordrhein-Westfalen');
        $this->createState('Sachsen-Anhalt');
        $this->createState('Berlin');
        $this->createState('Hessen');
        $this->createState('Rheinland-Pfalz');
        $this->createState('Schleswig-Holstein');
        $this->createState('Brandenburg');
        $this->createState('Mecklenburg-Vorpommern');
        $this->createState('Saarland');
        $this->createState('Thüringen');
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblType
     */
    public function createType($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblType')->findOneBy(array(
            TblType::ATTR_NAME        => $Name,
            TblType::ATTR_DESCRIPTION => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Name
     *
     * @return TblState
     */
    public function createState($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblState')->findOneBy(array(
            TblState::ATTR_NAME => $Name,
        ));
        if (null === $Entity) {
            $Entity = new TblState($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblState
     */
    public function getStateById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblState', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblState
     */
    public function getStateByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblState', array(
            TblType::ATTR_NAME => $Name,
        ));
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCity
     */
    public function getCityById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCity', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAddress
     */
    public function getAddressById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddress', $Id);
    }

    /**
     * @return bool|TblCity[]
     */
    public function getCityAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCity');
    }

    /**
     * @return bool|TblState[]
     */
    public function getStateAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblState');
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType');
    }

    /**
     * @return bool|TblAddress[]
     */
    public function getAddressAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddress');
    }

    /**
     * @param string $Code
     * @param string $Name
     * @param string $District
     *
     * @return TblCity
     */
    public function createCity($Code, $Name, $District)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCity')->findOneBy(array(
            TblCity::ATTR_CODE     => $Code,
            TblCity::ATTR_NAME     => $Name,
            TblCity::ATTR_DISTRICT => $District
        ));
        if (null === $Entity) {
            $Entity = new TblCity();
            $Entity->setCode($Code);
            $Entity->setName($Name);
            $Entity->setDistrict($District);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblState $tblState
     * @param TblCity  $tblCity
     * @param string   $StreetName
     * @param string   $StreetNumber
     * @param string   $PostOfficeBox
     * @param          $County
     * @param          $Nation
     *
     * @return TblAddress
     */
    public function createAddress(
        TblState $tblState = null,
        TblCity $tblCity,
        $StreetName,
        $StreetNumber,
        $PostOfficeBox,
        $County,
        $Nation
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAddress')
            ->findOneBy(array(
                TblAddress::ATTR_TBL_STATE       => ( $tblState ? $tblState->getId() : null ),
                TblAddress::ATTR_TBL_CITY        => $tblCity->getId(),
                TblAddress::ATTR_STREET_NAME     => $StreetName,
                TblAddress::ATTR_STREET_NUMBER   => $StreetNumber,
                TblAddress::ATTR_POST_OFFICE_BOX => $PostOfficeBox,
                TblAddress::ATTR_COUNTY          => $County,
                TblAddress::ATTR_NATION          => $Nation,
            ));
        if (null === $Entity) {
            $Entity = new TblAddress();
            $Entity->setStreetName($StreetName);
            $Entity->setStreetNumber($StreetNumber);
            $Entity->setPostOfficeBox($PostOfficeBox);
            $Entity->setTblState($tblState);
            $Entity->setTblCity($tblCity);
            $Entity->setCounty($County);
            $Entity->setNation($Nation);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson  $tblPerson
     * @param TblAddress $tblAddress
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToPerson
     */
    public function addAddressToPerson(TblPerson $tblPerson, TblAddress $tblAddress, TblType $tblType, $Remark)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblToPerson')
            ->findOneBy(array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblToPerson::ATT_TBL_ADDRESS    => $tblAddress->getId(),
                TblToPerson::ATT_TBL_TYPE       => $tblType->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblToPerson();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setTblAddress($tblAddress);
            $Entity->setTblType($tblType);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getAddressToPersonById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getAddressToCompanyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany', $Id);
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblAddress $tblAddress
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToCompany
     */
    public function addAddressToCompany(TblCompany $tblCompany, TblAddress $tblAddress, TblType $tblType, $Remark)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblToCompany')
            ->findOneBy(array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::ATT_TBL_ADDRESS     => $tblAddress->getId(),
                TblToCompany::ATT_TBL_TYPE        => $tblType->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblToCompany();
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setTblAddress($tblAddress);
            $Entity->setTblType($tblType);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getAddressAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
            array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType   $tblType
     *
     * @return bool|Entity\TblToPerson[]
     */
    public function getAddressAllByPersonAndType(TblPerson $tblPerson, TblType $tblType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
            array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblToPerson::ATT_TBL_TYPE       => $tblType->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblAddress
     */
    public function getAddressByPerson(TblPerson $tblPerson)
    {

        // TODO: Persistent Types
        $Type = $this->getTypeById(1);
        /** @var TblToPerson $Entity */
        if (( $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
            array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblToPerson::ATT_TBL_TYPE       => $Type->getId()
            ))
        )
        ) {
            return $Entity->getTblAddress();
        } else {
            return false;
        }
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', $Id);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblAddress
     */
    public function getAddressByCompany(TblCompany $tblCompany)
    {

        // TODO: Persistent Types
        $Type = $this->getTypeById(1);
        /** @var TblToPerson $Entity */
        if (( $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::ATT_TBL_TYPE        => $Type->getId()
            ))
        )
        ) {
            return $Entity->getTblAddress();
        } else {
            return false;
        }
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getAddressAllByCompany(TblCompany $tblCompany)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ));
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     *
     * @return bool|Entity\TblToCompany[]
     */
    public function getAddressAllByCompanyAndType(TblCompany $tblCompany, TblType $tblType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::ATT_TBL_TYPE        => $tblType->getId()
            ));
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function removeAddressToPerson(TblToPerson $tblToPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeAddressToCompany(TblToCompany $tblToCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToCompany $Entity */
        $Entity = $Manager->getEntityById('TblToCompany', $tblToCompany->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array of TblAddress->Id
     */
    public function fetchIdAddressAllByPerson(TblPerson $tblPerson)
    {

        $Cache = $this->getCache(new MemcachedHandler());
        if (null === ( $IdList = $Cache->getValue($tblPerson->getId(), __METHOD__) )) {
            $Manager = $this->getConnection()->getEntityManager();

            $Builder = $Manager->getQueryBuilder();
            $Query = $Builder->select('L.tblAddress')
                ->from(__NAMESPACE__.'\Entity\TblToPerson', 'L')
                ->where($Builder->expr()->eq('L.serviceTblPerson', '?1'))
                ->setParameter(1, $tblPerson->getId())
                ->getQuery();

            $IdList = $Query->useQueryCache(true)->getResult(ColumnHydrator::HYDRATION_MODE);

            $Cache->setValue($tblPerson->getId(), $IdList, 0, __METHOD__);
        }

        return $IdList;
    }

    /**
     * @param array $IdArray of TblAddress->Id
     *
     * @return TblAddress[]
     */
    public function fetchAddressAllByIdList($IdArray)
    {

        $Key = md5(json_encode($IdArray));
        $Cache = $this->getCache(new MemcachedHandler());
        if (null === ( $tblAddressAll = $Cache->getValue($Key, __METHOD__) )) {

            $Manager = $this->getConnection()->getEntityManager();

            $Builder = $Manager->getQueryBuilder();
            $Query = $Builder->select('A')
                ->from(__NAMESPACE__.'\Entity\TblAddress', 'A')
                ->where($Builder->expr()->in('A.Id', '?1'))
                ->setParameter(1, $IdArray)
                ->getQuery();
            $tblAddressAll = $Query->useQueryCache(true)->getResult(IdHydrator::HYDRATION_MODE);

            $Cache->setValue($Key, $tblAddressAll, 0, __METHOD__);
        }

        return $tblAddressAll;
    }
}
