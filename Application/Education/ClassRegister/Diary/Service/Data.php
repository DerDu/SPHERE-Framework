<?php

namespace SPHERE\Application\Education\ClassRegister\Diary\Service;

use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryDivision;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiaryStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Education\ClassRegister\Diary\Service\Entity
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Subject
     * @param $Content
     * @param $Date
     * @param $Location
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return TblDiary
     */
    public function createDiary(
        $Subject,
        $Content,
        $Date,
        $Location,
        TblPerson $tblPerson,
        TblYear $tblYear = null,
        TblDivision $tblDivision = null,
        TblGroup $tblGroup = null
    ) {

        $Manager = $this->getEntityManager();

        $Entity = new TblDiary();
        $Entity->setSubject($Subject);
        $Entity->setContent($Content);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setLocation($Location);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblYear($tblYear);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblGroup($tblGroup);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblDiary $tblDiary
     * @param $Subject
     * @param $Content
     * @param $Date
     * @param $Location
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return bool
     */
    public function updateDiary(
        TblDiary $tblDiary,
        $Subject,
        $Content,
        $Date,
        $Location,
        TblPerson $tblPerson,
        TblYear $tblYear = null,
        TblDivision $tblDivision = null,
        TblGroup $tblGroup = null
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDiary $Entity */
        $Entity = $Manager->getEntityById('TblDiary', $tblDiary->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setSubject($Subject);
            $Entity->setContent($Content);
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setLocation($Location);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblYear($tblYear);
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setServiceTblGroup($tblGroup);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return bool
     */
    public function destroyDiary(TblDiary $tblDiary)
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDiary $Entity */
        $Entity = $Manager->getEntityById('TblDiary', $tblDiary->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDiary', $Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDiary[]
     */
    public function getDiaryAllByDivision(TblDivision $tblDivision)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiary', array(
            TblDiary::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
        ));
    }

    /**
     * @param TblGroup $tblGroup
     * @param TblYear $tblYear
     *
     * @return false|TblGroup[]
     */
    public function getDiaryAllByGroup(TblGroup $tblGroup, TblYear $tblYear)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiary', array(
            TblDiary::ATTR_SERVICE_TBL_GROUP => $tblGroup->getId(),
            TblDiary::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()
        ));
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByDiary(TblDiary $tblDiary)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiaryStudent', array(
            TblDiaryStudent::ATTR_TBL_DIARY => $tblDiary->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByStudent(TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiaryStudent', array(
            TblDiaryStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblDiary $tblDiary
     * @param TblPerson $tblPerson
     *
     * @return TblDiaryStudent
     */
    public function addDiaryStudent(TblDiary $tblDiary, TblPerson $tblPerson)
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblDiaryStudent')
            ->findOneBy(array(
                TblDiaryStudent::ATTR_TBL_DIARY => $tblDiary->getId(),
                TblDiaryStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblDiaryStudent();
            $Entity->setTblDiary($tblDiary);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDiaryStudent $tblDiaryStudent
     *
     * @return bool
     */
    public function removeDiaryStudent(TblDiaryStudent $tblDiaryStudent)
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDiaryStudent $Entity */
        $Entity = $Manager->getEntityById('TblDiaryStudent', $tblDiaryStudent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDiaryDivision[]
     */
    public function getDiaryDivisionByDivision(TblDivision $tblDivision)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiaryDivision', array(
            TblDiaryDivision::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
        ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblDivision $tblPredecessorDivision
     *
     * @return TblDiaryDivision
     */
    public function addDiaryDivision(TblDivision $tblDivision, TblDivision $tblPredecessorDivision)
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblDiaryDivision')
            ->findOneBy(array(
                TblDiaryDivision::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblDiaryDivision::ATTR_SERVICE_TBL_PREDECESSOR_DIVISION => $tblPredecessorDivision->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblDiaryDivision();
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setServiceTblPredecessorDivision($tblPredecessorDivision);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDiary $tblDiary
     * @param TblPerson $tblPerson
     *
     * @return false|TblDiaryStudent
     */
    public function existsDiaryStudent(TblDiary $tblDiary, TblPerson $tblPerson)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDiaryStudent', array(
            TblDiaryStudent::ATTR_TBL_DIARY => $tblDiary->getId(),
            TblDiaryStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }
}