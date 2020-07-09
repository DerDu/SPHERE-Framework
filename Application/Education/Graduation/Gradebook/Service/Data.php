<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblProposalBehaviorGrade;
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

        $tblScoreType = $this->createScoreType('Noten (1-6) mit Tendenz', 'GRADES');
        $this->updateScoreType($tblScoreType, 'Noten (1-6) mit Tendenz', $tblScoreType->getIdentifier(), '^([1-6]{1}|[1-5]{1}[+-]{1})$');

        $tblScoreType = $this->createScoreType('Punkte (0-15)', 'POINTS');
        $this->updateScoreType($tblScoreType, $tblScoreType->getName(), $tblScoreType->getIdentifier(), '^([0-9]{1}|1[0-5]{1})$');

        $this->createScoreType('Verbale Bewertung', 'VERBAL');

        $tblScoreType = $this->createScoreType('Noten (1-5) mit Komma', 'GRADES_V1');
        $this->updateScoreType($tblScoreType, $tblScoreType->getName(), $tblScoreType->getIdentifier(), '^(5((\.|,)0+)?|[1-4]{1}((\.|,)[0-9]+)?)$');

        $this->createScoreType('Noten (1-6) mit Komma', 'GRADES_COMMA', '^(6((\.|,)0+)?|[1-5]{1}((\.|,)[0-9]+)?)$');

        $tblScoreType = $this->createScoreType('Noten (1-5) mit Tendenz', 'GRADES_BEHAVIOR_TASK');
        $this->updateScoreType($tblScoreType, $tblScoreType->getName(), $tblScoreType->getIdentifier(),  '^([1-5]{1}|[1-4]{1}[+-]{1})$');

        $TestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        if ($TestType) {
            $this->createGradeType('Betragen', 'KBE', 'Kopfnote Betragen', 0, $TestType);
            $this->createGradeType('Fleiß', 'KFL', 'Kopfnote Fleiß', 0, $TestType);
            $this->createGradeType('Mitarbeit', 'KMI', 'Kopfnote Mitarbeit', 0, $TestType);
            $this->createGradeType('Ordnung', 'KOR', 'Kopfnote Ordnung', 0, $TestType);
        }

        $this->createGradeText('nicht erteilt', 'NOT_GRANTED');
        $this->createGradeText('teilgenommen', 'ATTENDED');
        if (($tblGradeText = $this->createGradeText('keine Benotung', 'NO_GRADING'))
            && $tblGradeText->getName() == 'Keine Benotung '
        ) {
             $this->updateGradeText($tblGradeText, 'keine Benotung');
        }
        $this->createGradeText('befreit', 'LIBERATED');
    }

    /**
     * @param             $Name
     * @param             $Code
     * @param             $Description
     * @param             $IsHighlighted
     * @param TblTestType $tblTestType
     * @param bool        $IsPartGrade
     *
     * @return null|TblGradeType
     */
    public function createGradeType(
        $Name,
        $Code,
        $Description,
        $IsHighlighted,
        TblTestType $tblTestType,
        $IsPartGrade = false
    ) {

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
            $Entity->setIsPartGrade($IsPartGrade);

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
     * @param $IsPartGrade
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
        $IsActive,
        $IsPartGrade
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
            $Entity->setIsPartGrade($IsPartGrade);

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
     * @param string $PublicComment
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
        TblGradeText $tblGradeText = null,
        $PublicComment = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGrade')
            ->findOneBy(array(
                TblGrade::ATTR_SERVICE_TBL_TEST => $tblTest->getId(),
                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        if (null === $Entity) {
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
            $Entity->setPublicComment($PublicComment);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

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
     * @param TblPerson|null $tblPerson
     * @param TblDivision|null $tblDivision
     * @param TblPeriod|null $tblPeriod
     * @param TblTestType|null $tblTestType
     *
     * @return bool|TblGrade[]
     */
    public function getGradeAllBy(
        TblPerson $tblPerson = null,
        TblDivision $tblDivision = null,
        TblPeriod $tblPeriod = null,
        TblTestType $tblTestType = null
    ) {
        $Parameter = array(
            TblGrade::ENTITY_REMOVE => null
        );
        if( $tblPerson ) {
            $Parameter[TblGrade::ATTR_SERVICE_TBL_PERSON] = $tblPerson->getId();
        }
        if( $tblDivision ) {
            $Parameter[TblGrade::ATTR_SERVICE_TBL_DIVISION] = $tblDivision->getId();
        }
        if( $tblPeriod ) {
            $Parameter[TblGrade::ATTR_SERVICE_TBL_PERIOD] = $tblPeriod->getId();
        }
        if( $tblTestType ) {
            $Parameter[TblGrade::ATTR_SERVICE_TBL_TEST_TYPE] = $tblTestType->getId();
        }

        return $this->getForceEntityListBy(__METHOD__,$this->getEntityManager(),(new TblGrade())->getEntityShortName(), $Parameter);
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
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblGrade[]
     */
    public function getGradesByStudentAndGradeType(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblGradeType $tblGradeType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblGrade', array(
           TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
           TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
           TblGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()
        ));
    }

    /**
     * @param TblGrade $tblGrade
     * @param $Grade
     * @param string $Comment
     * @param string $PublicComment
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
        $PublicComment = '',
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
            $Entity->setPublicComment($PublicComment);
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
     * @param bool $IsForced
     *
     * @return bool|TblGrade
     */
    public function getGradeByTestAndStudent(
        TblTest $tblTest,
        TblPerson $tblPerson,
        $IsForced = false
    ) {

        if ($IsForced) {
            return $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                array(
                    TblGrade::ATTR_SERVICE_TBL_TEST => $tblTest->getId(),
                    TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                array(
                    TblGrade::ATTR_SERVICE_TBL_TEST => $tblTest->getId(),
                    TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        }
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
     * @param TblSubject  $tblSubject
     * @param TblTestType $tblTestType
     *
     * @return bool
     */
    public function existsGrades(TblDivision $tblDivision, TblSubject $tblSubject, TblTestType $tblTestType)
    {

        if( $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade', array(
            TblGrade::ATTR_SERVICE_TBL_DIVISION  => $tblDivision->getId(),
            TblGrade::ATTR_SERVICE_TBL_SUBJECT   => $tblSubject->getId(),
            TblGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId()
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

    /**
     * @param TblGradeType $tblGradeType
     * @param $tblGradeList
     */
    public function updateGradesGradeType(
        TblGradeType $tblGradeType,
        $tblGradeList
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGrade $tblGrade */
        foreach ($tblGradeList as $tblGrade) {
            /** @var TblGrade $Entity */
            $Entity = $Manager->getEntityById('TblGrade', $tblGrade->getId());

            $Protocol = clone $Entity;
            if (null !== $Entity) {
                $Entity->setTblGradeType($tblGradeType);

                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity, true);
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPersonTeacher
     * @param TblPerson $tblPersonStudent
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblGrade[]
     */
    public function getGradesByDivisionAndTeacher(TblDivision $tblDivision, TblPerson $tblPersonTeacher, TblPerson $tblPersonStudent, TblGradeType $tblGradeType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblGrade', array(
            TblGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
            TblGrade::ATTR_SERVICE_TBL_PERSON_TEACHER => $tblPersonTeacher->getId(),
            TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPersonStudent->getId(),
            TblGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId(),
        ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTask $tblTask
     * @param TblGradeType $tblGradeType
     * @param TblPerson $tblPerson
     *
     * @return false|TblProposalBehaviorGrade
     */
    public function getProposalBehaviorGrade(TblDivision $tblDivision, TblTask $tblTask, TblGradeType $tblGradeType, TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblProposalBehaviorGrade', array(
            TblProposalBehaviorGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
            TblProposalBehaviorGrade::ATTR_SERVICE_TBL_TASK => $tblTask->getId(),
            TblProposalBehaviorGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId(),
            TblProposalBehaviorGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTask $tblTask
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblProposalBehaviorGrade[]
     */
    public function getProposalBehaviorGradeAllBy(TblDivision $tblDivision, TblTask $tblTask, TblGradeType $tblGradeType = null)
    {

        if ($tblGradeType) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblProposalBehaviorGrade',
                array(
                    TblProposalBehaviorGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblProposalBehaviorGrade::ATTR_SERVICE_TBL_TASK => $tblTask->getId(),
                    TblProposalBehaviorGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()
                ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblProposalBehaviorGrade',
                array(
                    TblProposalBehaviorGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblProposalBehaviorGrade::ATTR_SERVICE_TBL_TASK => $tblTask->getId()
                ));
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTask $tblTask
     * @param TblGradeType $tblGradeType
     * @param TblPerson $tblPerson
     * @param TblPerson $tblPersonTeacher
     * @param $Grade
     * @param $Trend
     * @param $Comment
     *
     * @return TblProposalBehaviorGrade
     */
    public function createProposalBehaviorGrade(
        TblDivision $tblDivision,
        TblTask $tblTask,
        TblGradeType $tblGradeType,
        TblPerson $tblPerson,
        TblPerson $tblPersonTeacher,
        $Grade,
        $Trend,
        $Comment
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblProposalBehaviorGrade')
            ->findOneBy(array(
                TblProposalBehaviorGrade::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblProposalBehaviorGrade::ATTR_SERVICE_TBL_TASK => $tblTask->getId(),
                TblProposalBehaviorGrade::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId(),
                TblProposalBehaviorGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));

        if (null === $Entity) {
            $Entity = new TblProposalBehaviorGrade();
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setServiceTblTask($tblTask);
            $Entity->setTblGradeType($tblGradeType);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblPersonTeacher($tblPersonTeacher);
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);
            $Entity->setTrend($Trend);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblProposalBehaviorGrade $tblProposalBehaviorGrade
     * @param $Grade
     * @param string $Comment
     * @param int $Trend
     * @param TblPerson|null $tblPersonTeacher
     *
     * @return bool
     */
    public function updateProposalBehaviorGrade(
        TblProposalBehaviorGrade $tblProposalBehaviorGrade,
        $Grade,
        $Comment = '',
        $Trend = 0,
        TblPerson $tblPersonTeacher = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblProposalBehaviorGrade $Entity */
        $Entity = $Manager->getEntityById('TblProposalBehaviorGrade', $tblProposalBehaviorGrade->getId());

        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);
            $Entity->setTrend($Trend);
            $Entity->setServiceTblPersonTeacher($tblPersonTeacher);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblGradeText $tblGradeText
     * @param $Name
     *
     * @return bool
     */
    public function updateGradeText(
        TblGradeText $tblGradeText,
        $Name
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGradeText $Entity */
        $Entity = $Manager->getEntityById('TblGradeText', $tblGradeText->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }
}
