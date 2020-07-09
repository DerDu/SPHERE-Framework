<?php
namespace SPHERE\Application\People\Relationship\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblGroup;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipFromPerson;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToCompany;
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

    /**
     * @return false|ViewRelationshipFromPerson[]
     */
    public function viewRelationshipFromPerson()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewRelationshipFromPerson'
        );
    }

    /**
     * @return false|ViewRelationshipToCompany[]
     */
    public function viewRelationshipToCompany()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewRelationshipToCompany'
        );
    }
    
    public function setupDatabaseContent()
    {

        $tblGroupPerson = $this->createGroup('PERSON', 'Personenbeziehung', 'Person zu Person');
        $tblGroupCompany = $this->createGroup('COMPANY', 'Institutionenbeziehungen', 'Person zu Institution');

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

        $tblType = $this->createType('Beitragszahler', 'z.B. Großeltern / Amt', $tblGroupPerson);
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
        $this->createType('Mitarbeiter', '', $tblGroupCompany);
        $this->createType('Leiter', '', $tblGroupCompany);
        $this->createType('Allgemein', '', $tblGroupCompany);
        $this->createType('Inhaber', '', $tblGroupCompany);
        $this->createType('Kuratoriumsmitglied', '', $tblGroupCompany);
        $this->createType('Partner', '', $tblGroupCompany);
        $this->createType('Verwaltungsleiter', '', $tblGroupCompany);
        $this->createType('Auszubildender', '', $tblGroupCompany);
        $this->createType('Praktikant', '', $tblGroupCompany);

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
     * @param TblGroup $tblGroup
     * @param string   $Name
     * @param string   $Description
     *
     * @return TblGroup
     */
    public function updateGroup(TblGroup $tblGroup, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGroup $Entity */
        $Entity = $Manager->getEntityById('TblGroup', $tblGroup->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return $Entity;
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
        /** @var TblToPerson $Entity */
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblPerson $tblPersonFrom
     * @param TblPerson $tblPersonTo
     *
     * @return bool|TblToPerson
     */
    public function getRelationshipToPersonByPersonFromAndPersonTo(TblPerson $tblPersonFrom,TblPerson $tblPersonTo)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $this->getCachedEntityBy(__METHOD__, $Manager, 'TblToPerson', array(
            TblToPerson::SERVICE_TBL_PERSON_FROM => $tblPersonFrom->getId(),
            TblToPerson::SERVICE_TBL_PERSON_TO => $tblPersonTo->getId(),
        ));
        /** @var TblToPerson $Entity */
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
        /** @var TblToCompany $Entity */
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType|null $tblType
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipAllByPerson(TblPerson $tblPerson, TblType $tblType = null, $isForced = false)
    {

        $From = $this->getPersonRelationshipFromByPerson($tblPerson, $tblType, $isForced);
        if (!$From) {
            $From = array();
        }
        $To = $this->getPersonRelationshipToByPerson($tblPerson, $tblType, $isForced);
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
     * @param TblType $tblType
     *
     * @return false|TblToPerson[]
     */
    public function getPersonRelationshipAllByType(TblType $tblType)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblToPerson', array(
             TblToPerson::ATTR_TBL_TYPE => $tblType->getId()
        ));
    }

    /**
     * @param TblType $tblType
     *
     * @return array array[PersonFromId[PersonToId]]
     */
    public function getPersonRelationshipArrayByType(TblType $tblType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $tblToPerson = new TblToPerson();

        $query = $queryBuilder->select('tTP.serviceTblPersonFrom, tTP.serviceTblPersonTo')
            ->from($tblToPerson->getEntityFullName(), 'tTP')
            ->where($queryBuilder->expr()->eq('tTP.tblType', '?1'))
            ->setParameter(1, $tblType->getId())
            ->getQuery();

        $FromToList = $query->getResult();

        // Combined "fromPerson" with multiple "toPerson"
        $resultList = array();
        if(!empty($FromToList)){
            foreach($FromToList as $Value){
                $resultList[$Value['serviceTblPersonFrom']][$Value['serviceTblPersonTo']] = $Value['serviceTblPersonTo'];
            }
        }

        return $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType|null $tblType
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipFromByPerson(TblPerson $tblPerson, TblType $tblType = null, $isForced = false)
    {
        $Parameter = array(
            TblToPerson::SERVICE_TBL_PERSON_FROM => $tblPerson->getId()
        );
        if( $tblType ) {
            $Parameter[TblToPerson::ATTR_TBL_TYPE] = $tblType->getId();
        }

        $orderBy = array('Ranking' => self::ORDER_ASC);

        if ($isForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblToPerson', $Parameter, $orderBy);
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblToPerson', $Parameter, $orderBy);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType|null $tblType
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipToByPerson(TblPerson $tblPerson, TblType $tblType = null, $isForced = false)
    {
        $Parameter = array(
            TblToPerson::SERVICE_TBL_PERSON_TO => $tblPerson->getId()
        );
        if( $tblType ) {
            $Parameter[TblToPerson::ATTR_TBL_TYPE] = $tblType->getId();
        }

        $orderBy = array('Ranking' => self::ORDER_ASC);

        if ($isForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblToPerson', $Parameter, $orderBy);
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblToPerson', $Parameter, $orderBy);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblToCompany[]
     */
    public function getCompanyRelationshipAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
                array(
                    TblToCompany::SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
                array(
                    TblToCompany::SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        }
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
     * @param TblType $tblType
     * @param string $Remark
     * @param null $Ranking
     * @param bool $IsSingleParent
     *
     * @return TblToPerson
     */
    public function addPersonRelationshipToPerson(
        TblPerson $tblPersonFrom,
        TblPerson $tblPersonTo,
        TblType $tblType,
        $Remark,
        $Ranking = null,
        $IsSingleParent = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblToPerson();
        $Entity->setServiceTblPersonFrom($tblPersonFrom);
        $Entity->setServiceTblPersonTo($tblPersonTo);
        $Entity->setTblType($tblType);
        $Entity->setRemark($Remark);
        $Entity->setRanking($Ranking);
        $Entity->setSingleParent($IsSingleParent);

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

        $Entity = $this->getCachedEntityBy( __METHOD__, $Manager, 'TblToCompany', array(
            TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
            TblToCompany::SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblToCompany::ATT_TBL_TYPE => $tblType->getId()
        ));

        if( !$Entity ) {
            $Entity = new TblToCompany();
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setServiceTblPerson($tblPerson);
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
     * @return bool|TblSiblingRank
     */
    public function getSiblingRankById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblSiblingRank', $Id);
        /** @var TblSiblingRank $Entity */
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblSiblingRank[]
     */
    public function getSiblingRankAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSiblingRank');
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function restoreToPerson(TblToPerson $tblToPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function restoreToCompany(TblToCompany $tblToCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToCompany $Entity */
        $Entity = $Manager->getEntityById('TblToCompany', $tblToCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $modifyList
     *
     * @return bool
     */
    public function updateRelationshipRanking($modifyList)
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($modifyList as $ToPersonId => $ranking) {
            /** @var TblToPerson $Entity */
            $Entity = $Manager->getEntityById('TblToPerson', $ToPersonId);
            $Protocol = clone $Entity;
            if (null !== $Entity) {
                $Entity->setRanking($ranking);

                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }
}
