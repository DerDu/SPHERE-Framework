<?php
namespace SPHERE\Application\People\Person\Service;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\IdHydrator;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Person\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewPerson[]
     */
    public function viewPerson()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewPerson'
        );
    }
    
    public function setupDatabaseContent()
    {

        $this->createSalutation('Herr', true);
        $this->createSalutation('Frau', true);
        $this->createSalutation('Schüler', true);

    }

    /**
     * @param string $Salutation
     * @param bool   $IsLocked
     *
     * @return TblSalutation
     */
    public function createSalutation($Salutation, $IsLocked = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSalutation')->findOneBy(array(TblSalutation::ATTR_SALUTATION => $Salutation));
        if (null === $Entity) {
            $Entity = new TblSalutation($Salutation);
            $Entity->setLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param        $Salutation
     * @param string $Title
     * @param string $FirstName
     * @param string $SecondName
     * @param string $CallName
     * @param string $LastName
     * @param string $BirthName
     * @param string $ImportId
     *
     * @return TblPerson
     */
    public function createPerson($Salutation, $Title, $FirstName, $SecondName, $CallName, $LastName, $BirthName = '', $ImportId = '')
    {

        if ($Salutation === false) {
            $Salutation = null;
        }

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblPerson();
        $Entity->setTblSalutation($Salutation !== null ? $this->getSalutationById($Salutation) : $Salutation);
        $Entity->setTitle($Title);
        $Entity->setFirstName($FirstName);
        $Entity->setSecondName($SecondName);
        $Entity->setCallName($CallName);
        $Entity->setLastName($LastName);
        $Entity->setBirthName($BirthName);
        $Entity->setImportId($ImportId);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSalutation
     */
    public function getSalutationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSalutation', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Salutation
     * @param string $Title
     * @param string $FirstName
     * @param string $SecondName
     * @param string $CallName
     * @param string $LastName
     * @param string $BirthName
     *
     * @return bool
     */
    public function updatePerson(
        TblPerson $tblPerson,
        $Salutation,
        $Title,
        $FirstName,
        $SecondName,
        $CallName,
        $LastName,
        $BirthName = ''
    ) {

        if ($Salutation === false || $Salutation === '0') {
            $Salutation = null;
        }

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPerson $Entity */
        $Entity = $Manager->getEntityById('TblPerson', $tblPerson->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblSalutation($Salutation !== null ? $this->getSalutationById($Salutation) : $Salutation);
            $Entity->setTitle($Title);
            $Entity->setFirstName($FirstName);
            $Entity->setSecondName($SecondName);
            $Entity->setCallName($CallName);
            $Entity->setLastName($LastName);
            $Entity->setBirthName($BirthName);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param array $ProcessList
     *
     * @return bool
     */
    public function updatePersonAnonymousBulk(
        $ProcessList = array()
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($ProcessList)){
            foreach($ProcessList as $PersonData){
                /** @var TblPerson $tblPerson */
                $tblPerson = $PersonData['Person'];
                $firstName = $PersonData['FirstName'];
                $lastName = $PersonData['LastName'];
                /** @var TblPerson $Entity */
                $Entity = $Manager->getEntityById('TblPerson', $tblPerson->getId());
//                $Protocol = clone $Entity;
                if (null !== $Entity) {
                    $Entity->setFirstName($firstName);
                    $Entity->setSecondName('');
                    $Entity->setCallName('');
                    $Entity->setLastName($lastName);
                    $Entity->setBirthName('');
                    $Manager->bulkSaveEntity($Entity);
                    // no Protocol necessary
//                    Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
                }
            }
            $Manager->flushCache();
//            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
    */
    public function restorePerson(
        TblPerson $tblPerson
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPerson $Entity */
        $Entity = $Manager->getEntityById('TblPerson', $tblPerson->getId());
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
     * @param TblPerson     $tblPerson
     * @param TblSalutation $tblSalutation
     *
     * @return bool
     */
    public function updateSalutation(TblPerson $tblPerson, TblSalutation $tblSalutation)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPerson $Entity */
        $Entity = $Manager->getEntityById('TblPerson', $tblPerson->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblSalutation($tblSalutation);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblSalutation[]
     */
    public function getSalutationAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSalutation');
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPerson');
    }

    /**
     * @return false|TblPerson[]
     */
    public function getPersonAllBySoftRemove()
    {
        // direkt über DB ermitteln
//        return $this->getForceEntityListBy(__METHOD__, $this->getEntityManager(false), 'TblPerson', array(Element::ENTITY_REMOVE => !null));
        $resultList = array();
        if (($tblPersonList = $this->getForceEntityList(__METHOD__, $this->getEntityManager(false), 'TblPerson'))) {
            foreach ($tblPersonList as $tblPerson) {
                if ($tblPerson->getEntityRemove() != null) {
                    $resultList[] = $tblPerson;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param $FirstName
     * @param $LastName
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByFirstNameAndLastName($FirstName, $LastName)
    {

        return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPerson', array(
            TblPerson::ATTR_FIRST_NAME => $FirstName,
            TblPerson::ATTR_LAST_NAME  => $LastName,
            TblPerson::ENTITY_REMOVE => null
        ));
    }

    /**
     * @param $Name
     *
     * @return false|TblPerson[]
     */
    public function getPersonListLike($Name)
    {
        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();

        $split = explode(' ', $Name);

        $and = $queryBuilder->expr()->andX();
        $count = 0;
        foreach ($split as $item) {
            $count++;

            $or = $queryBuilder->expr()->orX();
            $or->add($queryBuilder->expr()->like('t.LastName', '?' . $count));
            $or->add($queryBuilder->expr()->like('t.FirstName', '?' . $count));
            $or->add($queryBuilder->expr()->like('t.SecondName', '?' . $count));
            $or->add($queryBuilder->expr()->like('t.CallName', '?' . $count));
            $and->add($or);

            // SSW-591 keine gelöschten Personen anzeigen
            $and->add($queryBuilder->expr()->isNull('t.EntityRemove'));

            $queryBuilder->setParameter($count, '%' . $item . '%');
        }

        $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblPerson', 't')
            ->where($and);

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return empty($result) ? false : $result;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     *
     * @return false|TblPerson[]
     */
    public function getPersonListLikeFirstNameAndLastName($firstName, $lastName)
    {
        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();
        $and = $queryBuilder->expr()->andX();

        $or = $queryBuilder->expr()->orX();
        $or->add($queryBuilder->expr()->like('t.FirstName', '?' . 1));
        $or->add($queryBuilder->expr()->like('t.SecondName', '?' . 1));
        $and->add($or);
        $and->add($queryBuilder->expr()->like('t.LastName', '?' . 2));
        // keine gelöschten Personen anzeigen
        $and->add($queryBuilder->expr()->isNull('t.EntityRemove'));
        $queryBuilder->setParameter(1, '%' . $firstName . '%');
        $queryBuilder->setParameter(2, '%' . $lastName . '%');

        $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblPerson', 't')
            ->where($and);

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @return int
     */
    public function countPersonAll()
    {

        return $this->getConnection()->getEntityManager()->getEntity('TblPerson')->count();
    }

    /**
     * @param integer $Id
     * @param bool $IsForced
     *
     * @return bool|TblPerson
     */
    public function getPersonById($Id, $IsForced = false)
    {

        if ($IsForced){
            return $this->getForceEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPerson', $Id);
        } else {
            return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPerson', $Id);
        }
    }

    /**
     * @param integer $ImportId
     *
     * @return bool|TblPerson
     */
    public function getPersonByImportId($ImportId)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPerson',
            array(
                TblPerson::ATTR_IMPORT_ID => $ImportId
            ));
    }

    /**
     * @param array $IdArray of TblPerson->Id
     *
     * @return TblPerson[]
     */
    public function fetchPersonAllByIdList($IdArray)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Builder = $Manager->getQueryBuilder();
        $Query = $Builder->select('P')
            ->from(__NAMESPACE__.'\Entity\TblPerson', 'P')
            ->where($Builder->expr()->in('P.Id', '?1'))
            ->setParameter(1, $IdArray)
            ->getQuery();
        return $Query->getResult(IdHydrator::HYDRATION_MODE);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function destroyPerson(TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPerson $Entity */
        $Entity = $Manager->getEntityById('TblPerson', $tblPerson->getId());
        if (null !== $Entity) {
            $this->softRemovePersonReferences($tblPerson);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function softRemovePersonReferences(TblPerson $tblPerson)
    {

        $IsSoftRemove = true;

        Address::useService()->removeAddressAllByPerson($tblPerson, $IsSoftRemove);
        Mail::useService()->removeSoftMailAllByPerson($tblPerson, $IsSoftRemove);
        Phone::useService()->removeSoftPhoneAllByPerson($tblPerson, $IsSoftRemove);
        Division::useService()->removePerson($tblPerson, $IsSoftRemove);
        if (($tblClub = Club::useService()->getClubByPerson($tblPerson))){
            Club::useService()->destroyClub($tblClub, $IsSoftRemove);
        }
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
            Common::useService()->destroyCommon($tblCommon, $IsSoftRemove);
        }
        if (($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson))){
            Custody::useService()->destroyCustody($tblCustody, $IsSoftRemove);
        }
        if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))){
            Prospect::useService()->destroyProspect($tblProspect, $IsSoftRemove);
        }
        if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
            Student::useService()->destroyStudent($tblStudent);
        }
        if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))){
            Teacher::useService()->destroyTeacher($tblTeacher, $IsSoftRemove);
        }
        Relationship::useService()->removeRelationshipAllByPerson($tblPerson, $IsSoftRemove);
        Absence::useService()->destroyAbsenceAllByPerson($tblPerson, $IsSoftRemove);
        Group::useService()->removeMemberAllByPerson($tblPerson, $IsSoftRemove);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblSalutation
     */
    public function getSalutationByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSalutation',
            array(
                TblSalutation::ATTR_SALUTATION => $Name
            ));
    }
}
