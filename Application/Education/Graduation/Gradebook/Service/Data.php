<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\Service
 */
class Data extends \SPHERE\Application\Education\Graduation\Gradebook\ScoreRule\Data
{

    public function setupDatabaseContent()
    {

        $tblScoreType = $this->createScoreType('Noten (1-6)', 'GRADES');
        $this->updateScoreType($tblScoreType, $tblScoreType->getName(), $tblScoreType->getIdentifier(), '^[1-6]{1}$');

        $tblScoreType = $this->createScoreType('Punkte (0-15)', 'POINTS');
        $this->updateScoreType($tblScoreType, $tblScoreType->getName(), $tblScoreType->getIdentifier(), '^([0-9]{1}|1[0-5]{1})$');

        $this->createScoreType('Verbale Bewertung', 'VERBAL');

        $tblScoreType = $this->createScoreType('Noten (1-5) mit Komma', 'GRADES_V1');
        $this->updateScoreType($tblScoreType, $tblScoreType->getName(), $tblScoreType->getIdentifier(), '^[1-5]{1}((\.|,)[0-9]+)?');

        $TestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        if ($TestType) {
            $this->createGradeType('Betragen', 'KBE', 'Kopfnote Betragen', 0, $TestType);
            $this->createGradeType('Fleiß', 'KFL', 'Kopfnote Fleiß', 0, $TestType);
            $this->createGradeType('Mitarbeit', 'KMI', 'Kopfnote Mitarbeit', 0, $TestType);
            $this->createGradeType('Ordnung', 'KOR', 'Kopfnote Ordnung', 0, $TestType);
        }
    }

    /**
     * @param             $Name
     * @param             $Code
     * @param             $Description
     * @param             $IsHighlighted
     * @param TblTestType $tblTestType
     *
     * @return null|TblGradeType
     */
    public function createGradeType($Name, $Code, $Description, $IsHighlighted, TblTestType $tblTestType)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGradeType')
            ->findOneBy(array(
                TblGradeType::ATTR_NAME => $Name,
                'EntityRemove' => null
            ));

        if (null === $Entity) {
            $Entity = new TblGradeType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCode($Code);
            $Entity->setHighlighted($IsHighlighted);
            $Entity->setServiceTblTestType($tblTestType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param $Name
     * @param $Code
     * @param $Description
     * @param $IsHighlighted
     * @param TblTestType $tblTestType
     * @return bool
     */
    public function updateGradeType(
        TblGradeType $tblGradeType,
        $Name,
        $Code,
        $Description,
        $IsHighlighted,
        TblTestType $tblTestType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGradeType $Entity */
        $Entity = $Manager->getEntityById('TblGradeType', $tblGradeType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setCode($Code);
            $Entity->setDescription($Description);
            $Entity->setHighlighted($IsHighlighted);
            $Entity->setServiceTblTestType($tblTestType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblPeriod|null $tblPeriod
     * @param TblGradeType|null $tblGradeType
     * @param TblTest $tblTest
     * @param TblTestType $tblTestType
     * @param $Grade
     * @param string $Comment
     * @param int $Trend
     * @param null $Date
     *
     * @return TblGrade
     */
    public function createGrade(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPeriod $tblPeriod = null,
        TblGradeType $tblGradeType = null,
        TblTest $tblTest,
        TblTestType $tblTestType,
        $Grade,
        $Comment,
        $Trend = 0,
        $Date = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblGrade();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblSubjectGroup($tblSubjectGroup);
        $Entity->setServiceTblPeriod($tblPeriod);
        $Entity->setTblGradeType($tblGradeType);
        $Entity->setServiceTblTest($tblTest);
        $Entity->setServiceTblTestType($tblTestType);
        $Entity->setGrade($Grade);
        $Entity->setComment($Comment);
        $Entity->setTrend($Trend);
        $Entity->setDate($Date ? new \DateTime($Date) : null);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblGrade $tblGrade
     *
     * @return bool
     */
    public function destroyGrade(TblGrade $tblGrade)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGrade $Entity */
        $Entity = $Manager->getEntityById('TblGrade', $tblGrade->getId());
        if (null !== $Entity) {

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGradeType', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Code
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByCode($Code)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGradeType')
            ->findOneBy(array(TblGradeType::ATTR_CODE => $Code));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGradeType')
            ->findOneBy(array(TblGradeType::ATTR_NAME => $Name));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblTestType $tblTestType
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAllByTestType(TblTestType $tblTestType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeType',
            array(
                TblGradeType::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|TblGrade[]
     */
    public function getGradesByStudent(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        if ($tblSubjectGroup === null) {
            if ($tblPeriod === null) {
                $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                    array(
                        TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                        TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                        TblGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                    )
                );
            } else {
                $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                    array(
                        TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                        TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                        TblGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                        TblGrade::ATTR_SERVICE_TBL_PERIOD => $tblPeriod->getId()
                    )
                );
            }
        } else {
            if ($tblPeriod === null) {
                $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                    array(
                        TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                        TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                        TblGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
                    )
                );
            } else {
                $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                    array(
                        TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                        TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                        TblGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                        TblGrade::ATTR_SERVICE_TBL_PERIOD => $tblPeriod->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
                    )
                );
            }
        }

//        if ($list) {
//            /** @var TblGrade $item */
//            foreach ($list as &$item){
//                if (!$item->getTblGradeType()){
//                    $item = false;
//                }
//            }
//            $list = array_filter($list);
//
//            return empty($list) ? false : $list;
//        } else {
//            return false;
//        }

        return $list;
    }

    /**
     * @param TblGrade $tblGrade
     * @param $Grade
     * @param string $Comment
     * @param int $Trend
     * @param null $Date
     *
     * @return bool
     */
    public function updateGrade(
        TblGrade $tblGrade,
        $Grade,
        $Comment = '',
        $Trend = 0,
        $Date = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGrade $Entity */
        $Entity = $Manager->getEntityById('TblGrade', $tblGrade->getId());

        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);
            $Entity->setTrend($Trend);
            $Entity->setDate($Date ? new \DateTime($Date) : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return bool|TblGrade
     */
    public function getGradeById($Id)
    {

//        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade', $Id);
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGrade', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     *
     * @return bool|TblGrade
     */
    public function getGradeByTestAndStudent(
        TblTest $tblTest,
        TblPerson $tblPerson
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
            array(
                TblGrade::ATTR_SERVICE_TBL_TEST => $tblTest->getId(),
                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param string $Grade
     * @param string $Comment
     *
     * @return null|TblGrade
     */
    public function createGradeToTest(
        TblTest $tblTest,
        TblPerson $tblPerson,
        $Grade = '',
        $Comment = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGrade')
            ->findOneBy(array(
                TblGrade::ATTR_SERVICE_TBL_TEST => $tblTest->getId(),
                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblGrade();
            $Entity->setServiceTblTest($tblTest);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblDivision($tblTest->getServiceTblDivision());
            $Entity->setServiceTblSubject($tblTest->getServiceTblSubject());
            $Entity->setServiceTblPeriod($tblTest->getServiceTblPeriod());
            $Entity->setTblGradeType($tblTest->getServiceTblGradeType());
            $Entity->setServiceTblTestType(Evaluation::useService()->getTestTypeByIdentifier('TEST'));
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     *
     * @return TblGrade[]|bool
     */
    public function getGradeAllByTest(TblTest $tblTest)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
            array(
                TblGrade::ATTR_SERVICE_TBL_TEST => $tblTest->getId()
            ));

//        if ($EntityList) {
//            /** @var TblGrade $item */
//            foreach ($EntityList as &$item) {
//                // filter deleted persons
//                if (!$item->getServiceTblPerson() || !$item->getTblGradeType()) {
//                    $item = false;
//                }
//            }
//            $EntityList = array_filter($EntityList);
//        }
//
//        return empty($EntityList) ? false : $EntityList;

        return $EntityList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @return false|TblGrade[]
     */
    public function getGradesByGradeType(TblPerson $tblPerson, TblSubject $tblSubject, TblGradeType $tblGradeType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblGrade', array(
                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                TblGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()
            ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function existsGrades(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        if( $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade', array(
            TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
            TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
        ))) {
            return true;
        }
        return false;
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function destroyGradeType(TblGradeType $tblGradeType)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGradeType $Entity */
        $Entity = $Manager->getEntityById('TblGradeType', $tblGradeType->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }
}
