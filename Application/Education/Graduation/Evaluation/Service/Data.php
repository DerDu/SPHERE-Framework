<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:39
 */

namespace SPHERE\Application\Education\Graduation\Evaluation\Service;

use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createTestType('Test', 'TEST');
        $this->createTestType('AppointedDateTask', 'APPOINTED_DATE_TASK');
//        $this->createTestType('AppointedDateTaskDivision', 'APPOINTED_DATE_TASK_DIVISION');
        $this->createTestType('BehaviorTask', 'BEHAVIOR_TASK');
//        $this->createTestType('BehaviorTaskDivision', 'BEHAVIOR_TASK_DIVISION');
    }

    /**
     * @param $Id
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
     * @return bool|\SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest[]
     */
    public function getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType = null,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {
        if ($tblTestType === null){
            if ($tblSubjectGroup === null) {
                if ($tblPeriod === null) {
                    return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
                        )
                    );
                } else {
                    return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
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
                    return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
                        )
                    );
                } else {
                    return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
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
                    return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId(),
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
                        )
                    );
                } else {
                    return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
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
                    return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                        'TblTest',
                        array(
                            TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId(),
                            TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                            TblTest::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
                        )
                    );
                } else {
                    return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
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
    }

    /**
     * @param TblTestType $tblTestType
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
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
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
     * @param $Id
     * @return bool|TblTask
     */
    public function getTaskById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTask', $Id);
        return (null === $Entity ? false : $Entity);
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
        $ReturnDate = null
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

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     * @return bool
     */
    public function destroyTest(TblTest $tblTest)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblTest $Entity */
        $Entity = $Manager->getEntityById('TblTest', $tblTest->getId());
        if (null !== $Entity) {

            $tblGradeAllByTest = Gradebook::useService()->getGradeAllByTest($tblTest);
            if ($tblGradeAllByTest){
                foreach ($tblGradeAllByTest as $tblGrade){
                    Gradebook::useService()->destroyGrade($tblGrade);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblTest $tblTest
     * @param string $Description
     * @param null $Date
     * @param null $CorrectionDate
     * @param null $ReturnDate
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
     * @param $Name
     * @param $Identifier
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
     * @param TblTestType $tblTestType
     * @param $Name
     * @param null $Date
     * @param null $FromDate
     * @param null $ToDate
     * @return TblTask
     */
    public function createTask(
        TblTestType $tblTestType,
        $Name,
        $Date = null,
        $FromDate = null,
        $ToDate = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblTask();
        $Entity->setTblTestType($tblTestType);
        $Entity->setName($Name);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
        $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTestType $tblTestType
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
     * @param $Name
     * @param null $Date
     * @param null $FromDate
     * @param null $ToDate
     * @return bool
     */
    public function updateTask(
        TblTask $tblTask,
        $Name,
        $Date = null,
        $FromDate = null,
        $ToDate = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblTask $Entity */
        $Entity = $Manager->getEntityById('TblTask', $tblTask->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
            $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblTask $tblTask
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @return bool|Entity\TblTest[]
     */
    public function getTestAllByTaskAndTestType(
        TblTask $tblTask,
        TblTestType $tblTestType,
        TblDivision $tblDivision = null
    ) {

        if ($tblDivision === null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
                array(
                    TblTest::ATTR_TBL_TASK => $tblTask->getId(),
                    TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId()
                )
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest',
                array(
                    TblTest::ATTR_TBL_TASK => $tblTask->getId(),
                    TblTest::ATTR_TBL_TEST_TYPE => $tblTestType->getId(),
                    TblTest::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
                )
            );
        }
    }
}