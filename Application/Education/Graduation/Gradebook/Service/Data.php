<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Element;

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
        $this->updateScoreType($tblScoreType, $tblScoreType->getName(), $tblScoreType->getIdentifier(), '^(5((\.|,)0+)?|[1-4]{1}((\.|,)[0-9]+)?)$');

        $this->createScoreType('Noten (1-6) mit Komma', 'GRADES_COMMA', '^(6((\.|,)0+)?|[1-5]{1}((\.|,)[0-9]+)?)$');

        $this->createScoreType('Noten (1-5) mit Tendenz', 'GRADES_BEHAVIOR_TASK', '^[1-5]{1}$');

        $TestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        if ($TestType) {
            $this->createGradeType('Betragen', 'KBE', 'Kopfnote Betragen', 0, $TestType);
            $this->createGradeType('Fleiß', 'KFL', 'Kopfnote Fleiß', 0, $TestType);
            $this->createGradeType('Mitarbeit', 'KMI', 'Kopfnote Mitarbeit', 0, $TestType);
            $this->createGradeType('Ordnung', 'KOR', 'Kopfnote Ordnung', 0, $TestType);
        }

        $this->createGradeText('nicht erteilt', 'NOT_GRANTED');
        $this->createGradeText('teilgenommen', 'ATTENDED');
        $this->createGradeText('Keine Benotung ', 'NO_GRADING');
        $this->createGradeText('befreit', 'LIBERATED');
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
            $Entity->setIsActive(true);

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
     * @param bool $IsHighlighted
     * @param TblTestType $tblTestType
     * @param bool $IsActive
     *
     * @return bool
     */
    public function updateGradeType(
        TblGradeType $tblGradeType,
        $Name,
        $Code,
        $Description,
        $IsHighlighted,
        TblTestType $tblTestType,
        $IsActive
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
            $Entity->setIsActive($IsActive);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblPerson $tblPersonTeacher
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
     * @param TblGradeText $tblGradeText
     *
     * @return TblGrade
     */
    public function createGrade(
        TblPerson $tblPerson,
        TblPerson $tblPersonTeacher = null,
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
        $Date = null,
        TblGradeText $tblGradeText = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblGrade();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblPersonTeacher($tblPersonTeacher);
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
        $Entity->setTblGradeText($tblGradeText);

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

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeType', $Id);
//        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGradeType', $Id);
//        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Code
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByCode($Code)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeType', array(TblGradeType::ATTR_CODE => $Code));

//        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGradeType')
//            ->findOneBy(array(TblGradeType::ATTR_CODE => $Code));
//        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByName($Name)
    {

        /** @var TblGradeType $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGradeType')
            ->findOneBy(array(TblGradeType::ATTR_NAME => $Name));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblTestType $tblTestType
     * @param bool $IsActive
     *
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAllByTestType(TblTestType $tblTestType, $IsActive = true)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeType',
            array(
                TblGradeType::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                TblGradeType::ATTR_IS_ACTIVE => $IsActive
            ),
            array(
                TblGradeType::ATTR_NAME => self::ORDER_ASC
            )
        );
    }

    /**
     * @return false|TblGradeType[]
     */
    public function getGradeTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeType',
            array(
                TblGradeType::ATTR_NAME => self::ORDER_ASC
            )
        );
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
     * @param TblGradeText $tblGradeText
     * @param TblPerson $tblPersonTeacher
     *
     * @return bool
     */
    public function updateGrade(
        TblGrade $tblGrade,
        $Grade,
        $Comment = '',
        $Trend = 0,
        $Date = null,
        TblGradeText $tblGradeText = null,
        TblPerson $tblPersonTeacher = null
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
            $Entity->setTblGradeText($tblGradeText);
            $Entity->setServiceTblPersonTeacher($tblPersonTeacher);

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
        /** @var TblGrade $Entity */
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
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     *
     * @return false|Entity\TblGrade[]
     */
    public function getGradesByGradeType(TblPerson $tblPerson, TblDivision $tblDivision, TblSubject $tblSubject, TblGradeType $tblGradeType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblGrade', array(
                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                TblGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()
            ), array(Element::ENTITY_CREATE => self::ORDER_ASC));
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

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblGradeText
     */
    public function createGradeText(
        $Name,
        $Identifier
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Identifier = strtoupper($Identifier);

        $Entity = $Manager->getEntity('TblGradeText')
            ->findOneBy(array(
                TblGradeText::ATTR_IDENTIFIER => $Identifier,
            ));

        if (null === $Entity) {
            $Entity = new TblGradeText();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return false|TblGradeText
     */
    public function getGradeTextById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeText', $Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblGradeText
     */
    public function getGradeTextByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeText',
            array(
                TblGradeText::ATTR_IDENTIFIER => $Identifier
            )
        );
    }

    /**
     * @return false|TblGradeText[]
     */
    public function getGradeTextAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeText');
    }

    /**
     * @param $Name
     *
     * @return false|TblGradeText
     */
    public function getGradeTextByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeText',
            array(
                TblGradeText::ATTR_NAME => $Name
            )
        );
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function existsGradeByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade', array(
            TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivisionSubject->getTblDivision()->getId(),
            TblGrade::ATTR_SERVICE_TBL_SUBJECT => $tblDivisionSubject->getServiceTblSubject()->getId(),
            TblGrade::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblDivisionSubject->getTblSubjectGroup()
                ? $tblDivisionSubject->getTblSubjectGroup() : null
        )) ? true : false;
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsedInGradebook(TblGradeType $tblGradeType)
    {

        if ($this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblGrade',
            array(
                TblGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()
            ))
        ) {
            return true;
        }

        if ($this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblScoreConditionGradeTypeList',
            array(
                TblScoreConditionGradeTypeList::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()
            ))
        ) {
            return true;
        }

        if ($this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblScoreGroupGradeTypeList',
            array(
                TblScoreGroupGradeTypeList::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()
            ))
        ) {
            return true;
        }

        if ($this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblMinimumGradeCount',
            array(
                TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()
            ))
        ) {
            return true;
        }

        return false;
    }
}
