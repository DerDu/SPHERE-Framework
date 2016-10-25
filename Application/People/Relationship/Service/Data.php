<?php
namespace SPHERE\Application\People\Relationship\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblGroup;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Relationship\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewRelationshipToPerson[]
     */
    public function viewRelationshipToPerson()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewRelationshipToPerson'
        );
    }
    
    public function setupDatabaseContent()
    {

        $tblGroupPerson = $this->createGroup('PERSON', 'Personenbeziehung', 'Person zu Person');
        $tblGroupCompany = $this->createGroup('COMPANY', 'Firmenbeziehungen', 'Person zu Firma');

        $tblType = $this->createType('Sorgeberechtigt', '', $tblGroupPerson);
        $this->updateType($tblType, false);

        $tblType = $this->createType('Vormund', '', $tblGroupPerson);
        $this->updateType($tblType, false);

        $tblType = $this->createType('Bevollmächtigt', '', $tblGroupPerson);
        $this->updateType($tblType, false);

        $tblType = $this->createType('Geschwisterkind', '', $tblGroupPerson);
        $this->updateType($tblType, true);

        $tblType = $this->createType('Arzt', '', $tblGroupPerson);
        $this->updateType($tblType, false);

        $tblType = $this->createType('Ehepartner', '', $tblGroupPerson);
        $this->updateType($tblType, true);

        $tblType = $this->createType('Lebenspartner', '', $tblGroupPerson);
        $this->updateType($tblType, true);

        $this->createType('Notfallkontakt', 'z.B. Elternteil ohne Sorgerecht', $tblGroupPerson, false, false);

        $this->createType('Geschäftsführer', '', $tblGroupCompany);
        $this->createType('Assistenz der Geschäftsleitung', '', $tblGroupCompany);
        $this->createType('Aufsichtsrat', '', $tblGroupCompany);
        $this->createType('Bereichsleiter', '', $tblGroupCompany);
        $this->createType('Betriebsleiter', '', $tblGroupCompany);
        $this->createType('Buchhaltung', '', $tblGroupCompany);
        $this->createType('Gesellschafter', '', $tblGroupCompany);
        $this->createType('Handlungsbevollmächtigter', '', $tblGroupCompany);
        $this->createType('Kindergartenleiter', '', $tblGroupCompany);
        $this->createType('Personalleiter', '', $tblGroupCompany);
        $this->createType('Prokurist', '', $tblGroupCompany);
        $this->createType('Sachgebietsleiter', '', $tblGroupCompany);
        $this->createType('Sekretariat', '', $tblGroupCompany);
        $this->createType('Schulleiter', '', $tblGroupCompany);
        $this->createType('Vorstandsmitglied', '', $tblGroupCompany);
        $this->createType('Abgeordneter', '', $tblGroupCompany);
        $this->createType('Pfarrer', '', $tblGroupCompany);
        $this->createType('Amtsleiter', '', $tblGroupCompany);

        $this->createSiblingRank('1. Geschwisterkind');
        $this->createSiblingRank('2. Geschwisterkind');
        $this->createSiblingRank('3. Geschwisterkind');
        $this->createSiblingRank('4. Geschwisterkind');
        $this->createSiblingRank('5. Geschwisterkind');
        $this->createSiblingRank('6. Geschwisterkind');
    }

    /**
     * @param string $Identifier
     * @param string $Name
     * @param string $Description
     *
     * @return TblGroup
     */
    public function createGroup($Identifier, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblGroup')->findOneBy(array(
            TblGroup::ATTR_IDENTIFIER => $Identifier
        ));
        if (null === $Entity) {
            $Entity = new TblGroup();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param null|TblGroup $tblGroup
     * @param bool $IsLocked
     * @param bool|null $IsBidirectional
     *
     * @return TblType
     */
    public function createType($Name, $Description = '', TblGroup $tblGroup = null, $IsLocked = false, $IsBidirectional = null)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblType')->findOneBy(array(
                TblType::ATTR_NAME      => $Name,
                TblType::ATTR_TBL_GROUP => ( $tblGroup ? $tblGroup->getId() : null ),
                TblType::ATTR_IS_LOCKED => $IsLocked
            ));
        } else {
            $Entity = $Manager->getEntity('TblType')->findOneBy(array(
                TblType::ATTR_NAME      => $Name,
                TblType::ATTR_TBL_GROUP => ( $tblGroup ? $tblGroup->getId() : null )
            ));
        }

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Entity->setTblGroup($tblGroup);
            $Entity->setBidirectional($IsBidirectional);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblType $tblType
     * @param null $IsBidirectional
     * @return bool
     */
    public function updateType(TblType $tblType, $IsBidirectional = null)
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblType $Entity */
        $Entity = $Manager->getEntityById('TblType', $tblType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setBidirectional($IsBidirectional);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Name
     *
     * @return TblSiblingRank
     */
    public function createSiblingRank($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSiblingRank')->findOneBy(array(
            TblSiblingRank::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblSiblingRank();
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblGroup
     */
    public function getGroupByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', array(
            TblGroup::ATTR_IDENTIFIER => $Identifier
        ));
    }

    /**
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup');
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
     * @param $Name
     * @return false|TblType
     */
    public function getTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', array(
            TblType::ATTR_NAME => $Name
        ));
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType');
    }

    /**
     * @param TblGroup|null $tblGroup
     *
     * @return bool|TblType[]
     */
    public function getTypeAllByGroup(TblGroup $tblGroup = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', array(
            TblType::ATTR_TBL_GROUP => ( $tblGroup ? $tblGroup->getId() : null )
        ));
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getRelationshipToPersonById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblToPerson', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getRelationshipToCompanyById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblToCompany', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipAllByPerson(TblPerson $tblPerson)
    {

        $From = $this->getPersonRelationshipFromByPerson($tblPerson);
        if (!$From) {
            $From = array();
        }
        $To = $this->getPersonRelationshipToByPerson($tblPerson);
        if (!$To) {
            $To = array();
        }

        $EntityList = array_merge(
            $From,
            $To
        );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipFromByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
            array(
                TblToPerson::SERVICE_TBL_PERSON_FROM => $tblPerson->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipToByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
            array(
                TblToPerson::SERVICE_TBL_PERSON_TO => $tblPerson->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getCompanyRelationshipAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getCompanyRelationshipAllByCompany(TblCompany $tblCompany)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ));
    }

    /**
     * @param TblPerson $tblPersonFrom
     * @param TblPerson $tblPersonTo
     * @param TblType   $tblType
     * @param string    $Remark
     *
     * @return TblToPerson
     */
    public function addPersonRelationshipToPerson(
        TblPerson $tblPersonFrom,
        TblPerson $tblPersonTo,
        TblType $tblType,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblToPerson();
        $Entity->setServiceTblPersonFrom($tblPersonFrom);
        $Entity->setServiceTblPersonTo($tblPersonTo);
        $Entity->setTblType($tblType);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removePersonRelationshipToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeCompanyRelationshipToPerson(TblToCompany $tblToCompany, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToCompany $Entity */
        $Entity = $Manager->getEntityById('TblToCompany', $tblToCompany->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblPerson  $tblPerson
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToCompany
     */
    public function addCompanyRelationshipToPerson(
        TblCompany $tblCompany,
        TblPerson $tblPerson,
        TblType $tblType,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblToCompany();
        $Entity->setServiceTblCompany($tblCompany);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblType($tblType);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSiblingRank
     */
    public function getSiblingRankById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblSiblingRank', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblSiblingRank[]
     */
    public function getSiblingRankAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSiblingRank');
    }
}
