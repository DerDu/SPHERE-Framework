<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Subject
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Subject extends Transfer
{

    /**
     * @param string $Identifier
     * @param string $Name
     *
     * @return TblStudentSubjectType
     */
    public function createStudentSubjectType($Identifier, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentSubjectType')->findOneBy(array(
            TblStudentSubjectType::ATTR_IDENTIFIER => $Identifier
        ));
        if (null === $Entity) {
            $Entity = new TblStudentSubjectType();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblStudentSubjectType $tblStudentSubjectType
     * @param                       $Name
     *
     * @return bool
     */
    public function updateStudentSubjectType(TblStudentSubjectType $tblStudentSubjectType, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblStudentSubjectType', $tblStudentSubjectType->getId());

        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param string $Identifier
     * @param string $Name
     *
     * @return TblStudentSubjectRanking
     */
    public function createStudentSubjectRanking($Identifier, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentSubjectRanking')->findOneBy(array(
            TblStudentSubjectRanking::ATTR_IDENTIFIER => $Identifier
        ));
        if (null === $Entity) {
            $Entity = new TblStudentSubjectRanking();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubject
     */
    public function getStudentSubjectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubject', $Id
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubjectType
     */
    public function getStudentSubjectTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectType', $Id
        );
    }

    /**
     * @return bool|TblStudentSubjectType[]
     */
    public function getStudentSubjectTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectType'
        );
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudentSubjectType
     */
    public function getStudentSubjectTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectType', array(
                TblStudentSubjectType::ATTR_IDENTIFIER => strtoupper($Identifier)
            ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubjectRanking
     */
    public function getStudentSubjectRankingById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectRanking', $Id
        );
    }

    /**
     * @return bool|TblStudentSubjectRanking[]
     */
    public function getStudentSubjectRankingAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectRanking'
        );
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudentSubjectRanking
     */
    public function getStudentSubjectRankingByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectRanking', array(
                TblStudentSubjectRanking::ATTR_IDENTIFIER => strtoupper($Identifier)
            ));
    }

    /**
     * if already exists -> update entry
     *
     * @param TblStudent               $tblStudent
     * @param TblStudentSubjectType    $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     * @param TblSubject               $tblSubject
     *
     * @param TblLevel                 $tblLevelFrom
     * @param TblLevel                 $tblLevelTill
     *
     * @return TblStudentSubject
     */
    public function addStudentSubject(
        TblStudent $tblStudent,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking,
        TblSubject $tblSubject,
        TblLevel $tblLevelFrom = null,
        TblLevel $tblLevelTill = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblStudentSubject $Entity */
        $Entity = $Manager->getEntity('TblStudentSubject')->findOneBy(array(
            TblStudentSubject::ATTR_TBL_STUDENT                 => $tblStudent->getId(),
            TblStudentSubject::ATTR_TBL_STUDENT_SUBJECT_TYPE    => $tblStudentSubjectType->getId(),
            TblStudentSubject::ATTR_TBL_STUDENT_SUBJECT_RANKING => $tblStudentSubjectRanking->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblStudentSubject();
            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentSubjectType($tblStudentSubjectType);
            $Entity->setTblStudentSubjectRanking($tblStudentSubjectRanking);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setServiceTblLevelFrom($tblLevelFrom);
            $Entity->setServiceTblLevelTill($tblLevelTill);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentSubjectType($tblStudentSubjectType);
            $Entity->setTblStudentSubjectRanking($tblStudentSubjectRanking);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setServiceTblLevelFrom($tblLevelFrom);
            $Entity->setServiceTblLevelTill($tblLevelTill);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblStudentSubject $tblStudentSubject
     *
     * @return bool
     */
    public function removeStudentSubject(TblStudentSubject $tblStudentSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentSubject $Entity */
        $Entity = $Manager->getEntityById('TblStudentSubject', $tblStudentSubject->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentSubject[]
     */
    public function getStudentSubjectAllByStudent(TblStudent $tblStudent)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubject', array(TblStudentSubject::ATTR_TBL_STUDENT => $tblStudent->getId())
        );
    }

    /**
     * @param TblStudent               $tblStudent
     * @param TblStudentSubjectType    $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     *
     * @return false|TblStudentSubject
     */
    public function getStudentSubjectByStudentAndSubjectAndSubjectRanking(
        TblStudent $tblStudent,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubject', array(TblStudentSubject::ATTR_TBL_STUDENT                 => $tblStudent->getId(),
                                       TblStudentSubject::ATTR_TBL_STUDENT_SUBJECT_TYPE    => $tblStudentSubjectType->getId(),
                                       TblStudentSubject::ATTR_TBL_STUDENT_SUBJECT_RANKING => $tblStudentSubjectRanking->getId(),
            )
        );
    }

    /**
     * @param TblStudent            $tblStudent
     * @param TblStudentSubjectType $tblStudentSubjectType
     *
     * @return bool|TblStudentSubject[]
     */
    public function getStudentSubjectAllByStudentAndSubjectType(
        TblStudent $tblStudent,
        TblStudentSubjectType $tblStudentSubjectType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubject',
            array(
                TblStudentSubject::ATTR_TBL_STUDENT              => $tblStudent->getId(),
                TblStudentSubject::ATTR_TBL_STUDENT_SUBJECT_TYPE => $tblStudentSubjectType->getId(),
            )
        );
    }
}
