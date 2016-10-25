<?php
namespace SPHERE\Application\Education\Graduation\Evaluation\Service;

use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestLink;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Graduation\Evaluation\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createTestType('Leistungsüberprüfung', 'TEST');
        $this->createTestType('Kopfnote', 'BEHAVIOR');
        $this->createTestType('Stichtagsnotenauftrag', 'APPOINTED_DATE_TASK');
        $this->createTestType('Kopfnotenauftrag', 'BEHAVIOR_TASK');
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return null|TblGradeType
     */
    public function createTestType($Name, $Identifier)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTestType')
            ->findOneBy(array(TblTestType::ATTR_IDENTIFIER => $Identifier));

        if (null === $Entity) {
            $Entity = new TblTestType();
            $Entity->setName($Name);
            $Entity->setIdentifier(strtoupper($Identifier));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|\SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest
     */
    public function getTestById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTest', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblPeriod|null $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|\SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest[]
     */
    public function getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType = null,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        if ($tblTestType === null) {
            if ($tblSubjectGroup === null) {
                if ($tblPeriod === null) {
                    $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
                        )
                    );
                } else {
                    $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                            TblTest::ATTR_SERVICE_TBL_PERIOD => $tblPeriod->getId()
                        )
                    );
                }
            } else {
                if ($tblPeriod === null) {
                    $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
                        )
                    );
                } else {
                    $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                            TblTest::ATTR_SERVICE_TBL_PERIOD => $tblPeriod->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
                        )
                    );
                }
            }
        } else {
            if ($tblSubjectGroup === null) {
                if ($tblPeriod === null) {
                    $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId(),
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
                        )
                    );
                } else {
                    $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId(),
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                            TblTest::ATTR_SERVICE_TBL_PERIOD => $tblPeriod->getId()
                        )
                    );
                }
            } else {
                if ($tblPeriod === null) {
                    $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId(),
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
                        )
                    );
                } else {
                    $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId(),
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                            TblTest::ATTR_SERVICE_TBL_PERIOD => $tblPeriod->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
                        )
                    );
                }
            }
        }

        return $list;
    }

    /**
     * @param TblTestType $tblTestType
     *
     * @return bool|TblTest[]
     */
    public function getTestAllByTestType(TblTestType $tblTestType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
            array(
                TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId()
            )
        );
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @return bool|TblTest[]
     */
    public function getTestAllByTestTypeAndDivision(TblTestType $tblTestType, TblDivision $tblDivision)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
            array(
                TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId(),
                TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblTask
     */
    public function getTaskById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTask', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblTask[]
     */
    public function getTaskAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTask');
    }

    /**
     * @param $Id
     *
     * @return bool|TblTestType
     */
    public function getTestTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTestType', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblTestType
     */
    public function getTestTypeByIdentifier($Identifier)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblTestType')
            ->findOneBy(array(TblTestType::ATTR_IDENTIFIER => strtoupper($Identifier)));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblTestType[]
     */
    public function getTestTypesForGradeTypes()
    {

        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();
        $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblTestType', 't')
            ->where($queryBuilder->expr()->notLike('t.Identifier', '?1'))
            ->setParameter(1, '%TASK%');

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @return bool|TblTestType[]
     */
    public function getTestTypeAllWhereTask()
    {

        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();
        $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblTestType', 't')
            ->where($queryBuilder->expr()->like('t.Identifier', '?1'))
            ->setParameter(1, '%TASK%');

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     * @param TblPeriod|null $tblPeriod
     * @param TblGradeType|null $tblGradeType
     * @param TblTestType|null $tblTestType
     * @param TblTask $tblTask
     * @param string $Description
     * @param null $Date
     * @param null $CorrectionDate
     * @param null $ReturnDate
     * @param bool $IsContinues
     *
     * @return TblTest
     */
    public function createTest(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPeriod $tblPeriod = null,
        TblGradeType $tblGradeType = null,
        TblTestType $tblTestType = null,
        TblTask $tblTask = null,
        $Description = '',
        $Date = null,
        $CorrectionDate = null,
        $ReturnDate = null,
        $IsContinues = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblTest();
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblSubjectGroup($tblSubjectGroup);
        $Entity->setServiceTblPeriod($tblPeriod);
        $Entity->setServiceTblGradeType($tblGradeType);
        $Entity->setTblTestType($tblTestType);
        $Entity->setTblTask($tblTask);
        $Entity->setDescription($Description);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setCorrectionDate($CorrectionDate ? new \DateTime($CorrectionDate) : null);
        $Entity->setReturnDate($ReturnDate ? new \DateTime($ReturnDate) : null);
        $Entity->setIsContinues($IsContinues);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     * @param string $Description
     * @param null $Date
     * @param null $CorrectionDate
     * @param null $ReturnDate
     *
     * @return bool
     */
    public function updateTest(
        TblTest $tblTest,
        $Description = '',
        $Date = null,
        $CorrectionDate = null,
        $ReturnDate = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest $Entity */
        $Entity = $Manager->getEntityById('TblTest', $tblTest->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($Description);
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setCorrectionDate($CorrectionDate ? new \DateTime($CorrectionDate) : null);
            $Entity->setReturnDate($ReturnDate ? new \DateTime($ReturnDate) : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblTestType $tblTestType
     * @param $Name
     * @param null $Date
     * @param null $FromDate
     * @param null $ToDate
     * @param TblPeriod|null $tblPeriod
     * @param TblScoreType $tblScoreType
     * @param TblYear $tblYear
     *
     * @return TblTask
     */
    public function createTask(
        TblTestType $tblTestType,
        $Name,
        $Date = null,
        $FromDate = null,
        $ToDate = null,
        TblPeriod $tblPeriod = null,
        TblScoreType $tblScoreType = null,
        TblYear $tblYear = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblTask();
        $Entity->setTblTestType($tblTestType);
        $Entity->setName($Name);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
        $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);
        $Entity->setServiceTblPeriod($tblPeriod);
        $Entity->setServiceTblScoreType($tblScoreType);
        $Entity->setServiceTblYear($tblYear);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTestType $tblTestType
     *
     * @return bool|TblTask[]
     */
    public function getTaskAllByTestType(TblTestType $tblTestType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTask',
            array(
                TblTask::ATTR_TBL_TEST_TYPE => $tblTestType->getId()
            )
        );
    }

    /**
     * @param TblTask $tblTask
     * @param TblTestType $tblTestType
     * @param $Name
     * @param null $Date
     * @param null $FromDate
     * @param null $ToDate
     * @param TblPeriod $tblPeriod
     *
     * @param TblScoreType $tblScoreType
     * @return bool
     */
    public function updateTask(
        TblTask $tblTask,
        TblTestType $tblTestType,
        $Name,
        $Date = null,
        $FromDate = null,
        $ToDate = null,
        TblPeriod $tblPeriod = null,
        TblScoreType $tblScoreType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblTask $Entity */
        $Entity = $Manager->getEntityById('TblTask', $tblTask->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblTestType($tblTestType);
            $Entity->setName($Name);
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
            $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);
            $Entity->setServiceTblPeriod($tblPeriod);
            $Entity->setServiceTblScoreType($tblScoreType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDivision[]
     */
    public function getTestAllByDivision(TblDivision $tblDivision)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest', array(
            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
        ));
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function existsTestByTask(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        if ($tblSubjectGroup === null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
                array(
                    TblTest::ATTR_TBL_TASK => $tblTask->getId(),
                    TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                )) ? true : false;
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
                array(
                    TblTest::ATTR_TBL_TASK => $tblTask->getId(),
                    TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblTest::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
                )) ? true : false;
        }
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool
     */
    public function existsTestByTaskAndGradeType(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblGradeType $tblGradeType,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        if ($tblSubjectGroup === null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
                array(
                    TblTest::ATTR_TBL_TASK => $tblTask->getId(),
                    TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblTest::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
                )) ? true : false;
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
                array(
                    TblTest::ATTR_TBL_TASK => $tblTask->getId(),
                    TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                    TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblTest::ATTR_SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId(),
                    TblTest::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
                )) ? true : false;
        }
    }

    /**
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function destroyTask(TblTask $tblTask)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblTask $Entity */
        $Entity = $Manager->getEntityById('TblTask', $tblTask->getId());
        if (null !== $Entity) {

            $tblTestAllByTask = $this->getTestAllByTask($tblTask);
            if ($tblTestAllByTask) {
                foreach ($tblTestAllByTask as $tblTest) {
                    $this->destroyTest($tblTest);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     *
     * @return bool|Entity\TblTest[]
     */
    public function getTestAllByTask(TblTask $tblTask, TblDivision $tblDivision = null)
    {

        if ($tblDivision === null) {

            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
                array(
                    TblTest::ATTR_TBL_TASK => $tblTask->getId()
                )
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
                array(
                    TblTest::ATTR_TBL_TASK => $tblTask->getId(),
                    TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
                )
            );
        }
    }

    /**
     * @param TblTest $tblTest
     *
     * @return bool
     */
    public function destroyTest(TblTest $tblTest)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblTest $Entity */
        $Entity = $Manager->getEntityById('TblTest', $tblTest->getId());
        if (null !== $Entity) {

            $tblGradeAllByTest = Gradebook::useService()->getGradeAllByTest($tblTest);
            if ($tblGradeAllByTest) {
                foreach ($tblGradeAllByTest as $tblGrade) {
                    Gradebook::useService()->destroyGrade($tblGrade);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @return false|TblTask[]
     */
    public function getTaskAllByDivision(TblDivision $tblDivision, TblTestType $tblTestType)
    {

        $resultList = array();
        $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
            array(
                TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId()
            )
        );

        if ($list) {
            /** @var TblTest $tblTest */
            foreach ($list as $tblTest) {
                if ($tblTest->getTblTask()) {
                    $resultList[$tblTest->getTblTask()->getId()] = $tblTest->getTblTask();
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblTest $tblTest
     * @param int $LinkId
     *
     * @return TblTestLink
     */
    public function createTestLink(TblTest $tblTest, $LinkId)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTestLink')
            ->findOneBy(
                array(
                    TblTestLink::ATTR_TBL_TEST => $tblTest->getId(),
                    TblTestLink::ATTR_TBL_LINK_ID => $LinkId
                )
            );

        if (null === $Entity) {
            $Entity = new TblTestLink();
            $Entity->setTblTest($tblTest);
            $Entity->setLinkId($LinkId);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @return int
     */
    public function getNextLinkId()
    {

        $list = $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTestLink');
        $max = 0;
        if ($list) {
            $max = 0;
            /** @var TblTestLink $tblTestLink */
            foreach ($list as $tblTestLink) {
                if ($tblTestLink->getLinkId() !== null
                    && $tblTestLink->getLinkId() > $max
                ) {
                    $max = $tblTestLink->getLinkId();
                }
            }
        }

        return $max + 1;
    }

    /**
     * @param TblTest $tblTest
     * @return false | TblTest[]
     */
    public function getTestLinkAllByTest(TblTest $tblTest)
    {

        $resultList = array();
        /** @var TblTestLink $tblTestLink */
        $tblTestLink = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTestLink',
            array(
                TblTestLink::ATTR_TBL_TEST => $tblTest->getId()
            )
        );
        if ($tblTestLink
            && ($LinkId = $tblTestLink->getLinkId())
        ) {
            $tblTestLinkList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblTestLink', array(
                    TblTestLink::ATTR_TBL_LINK_ID => $LinkId
                )
            );
            if ($tblTestLinkList){
                /** @var TblTestLink $item */
                foreach ($tblTestLinkList as $item){
                    if ($item->getTblTest()
                        && $item->getTblTest()->getId() != $tblTest->getId()
                    ){
                        $resultList[] = $item->getTblTest();
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }
}
