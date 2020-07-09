<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.09.2016
 * Time: 08:38
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount;

use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount
 */
abstract class Data extends AbstractData
{

    /**
     * @param $Id
     *
     * @return false|TblMinimumGradeCount
     */
    public function getMinimumGradeCountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblMinimumGradeCount', $Id);
    }

    /**
     * @param TblLevel $tblLevel
     * @param TblSubject|null $tblSubject
     * @param TblGradeType|null $tblGradeType
     * @return false|TblMinimumGradeCount
     */
    public function getMinimumGradeCountBy(
        TblLevel $tblLevel,
        TblSubject $tblSubject = null,
        TblGradeType $tblGradeType = null
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMinimumGradeCount',
            array(
                TblMinimumGradeCount::ATTR_SERVICE_TBL_LEVEL => $tblLevel->getId(),
                TblMinimumGradeCount::ATTR_SERVICE_TBL_SUBJECT => $tblSubject ? $tblSubject->getId() : null,
                TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType ? $tblGradeType->getId() : null
            )
        );
    }

    /**
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblMinimumGradeCount');
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAllByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblLevel = $tblDivision->getTblLevel())
            && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
        ) {

            $levelList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblMinimumGradeCount',
                array(
                    TblMinimumGradeCount::ATTR_SERVICE_TBL_LEVEL => $tblLevel->getId(),
                    TblMinimumGradeCount::ATTR_SERVICE_TBL_SUBJECT => null
                )
            );
            if (!$levelList) {
                $levelList = array();
            }

            if (!empty($levelList)) {
                $levelList = $this->getSorter($levelList)->sortObjectBy('GradeTypeDisplayName');
            }

            $subjectList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblMinimumGradeCount',
                array(
                    TblMinimumGradeCount::ATTR_SERVICE_TBL_LEVEL => $tblLevel->getId(),
                    TblMinimumGradeCount::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
                )
            );

            if ($subjectList) {
                $subjectList = $this->getSorter($subjectList)->sortObjectBy('GradeTypeDisplayName');
                $levelList = array_merge($levelList, $subjectList);
            }

            if ($levelList) {
                $levelList = $this->getSorter($levelList)->sortObjectBy('Period');
            }

            return empty($levelList) ? false : $levelList;
        }

        return false;
    }

    /**
     * @param $Count
     * @param TblLevel $tblLevel
     * @param TblSubject|null $tblSubject
     * @param TblGradeType|null $tblGradeType
     * @param integer $Period
     * @param integer $Highlighted
     * @param $Course
     *
     * @return TblMinimumGradeCount
     */
    public function createMinimumGradeCount(
        $Count,
        TblLevel $tblLevel,
        TblSubject $tblSubject = null,
        TblGradeType $tblGradeType = null,
        $Period,
        $Highlighted,
        $Course
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblMinimumGradeCount')
            ->findOneBy(array(
                TblMinimumGradeCount::ATTR_SERVICE_TBL_LEVEL => $tblLevel->getId(),
                TblMinimumGradeCount::ATTR_SERVICE_TBL_SUBJECT => $tblSubject ? $tblSubject->getId() : null,
                TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType ? $tblGradeType->getId() : null,
                TblMinimumGradeCount::ATTR_PERIOD => $Period,
                TblMinimumGradeCount::ATTR_HIGHLIGHTED => $Highlighted,
                TblMinimumGradeCount::ATTR_COURSE => $Course
            ));

        if (null === $Entity) {
            $Entity = new TblMinimumGradeCount();
            $Entity->setCount($Count);
            $Entity->setServiceTblLevel($tblLevel);
            $Entity->setServiceTblSubject($tblSubject ? $tblSubject : null);
            $Entity->setTblGradeType($tblGradeType ? $tblGradeType : null);
            $Entity->setPeriod($Period);
            $Entity->setHighlighted($Highlighted);
            $Entity->setCourse($Course);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $MinimumGradeCount
     * @param $highlighted
     * @param TblGradeType|null $tblGradeType
     */
    public function createBulkMinimumGradeCountList($MinimumGradeCount, $highlighted, TblGradeType $tblGradeType = null)
    {

        $Manager = $this->getEntityManager();

        if (isset($MinimumGradeCount['Levels'])) {
            foreach ($MinimumGradeCount['Levels'] as $levelId => $value) {
                if (($tblLevel = Division::useService()->getLevelById($levelId))) {
                    if (isset($MinimumGradeCount['Subjects'])) {
                        foreach ($MinimumGradeCount['Subjects'] as $subjectId => $subValue) {
                            if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {

                                $Entity = $Manager->getEntity('TblMinimumGradeCount')
                                    ->findOneBy(array(
                                        TblMinimumGradeCount::ATTR_SERVICE_TBL_LEVEL => $tblLevel->getId(),
                                        TblMinimumGradeCount::ATTR_SERVICE_TBL_SUBJECT => $tblSubject ? $tblSubject->getId() : null,
                                        TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType ? $tblGradeType->getId() : null,
                                        TblMinimumGradeCount::ATTR_PERIOD => $MinimumGradeCount['Period'],
                                        TblMinimumGradeCount::ATTR_HIGHLIGHTED => $highlighted,
                                        TblMinimumGradeCount::ATTR_COURSE => $MinimumGradeCount['Course']
                                    ));

                                if (null === $Entity) {
                                    $Entity = new TblMinimumGradeCount();
                                    $Entity->setCount($MinimumGradeCount['Count']);
                                    $Entity->setServiceTblLevel($tblLevel);
                                    $Entity->setServiceTblSubject($tblSubject ? $tblSubject : null);
                                    $Entity->setTblGradeType($tblGradeType ? $tblGradeType : null);
                                    $Entity->setPeriod($MinimumGradeCount['Period']);
                                    $Entity->setHighlighted($highlighted);
                                    $Entity->setCourse($MinimumGradeCount['Course']);

                                    $Manager->bulkSaveEntity($Entity);
                                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                                }
                            }
                        }
                    } else {
                        $Entity = $Manager->getEntity('TblMinimumGradeCount')
                            ->findOneBy(array(
                                TblMinimumGradeCount::ATTR_SERVICE_TBL_LEVEL => $tblLevel->getId(),
                                TblMinimumGradeCount::ATTR_SERVICE_TBL_SUBJECT => null,
                                TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType ? $tblGradeType->getId() : null,
                                TblMinimumGradeCount::ATTR_PERIOD => $MinimumGradeCount['Period'],
                                TblMinimumGradeCount::ATTR_HIGHLIGHTED => $highlighted,
                                TblMinimumGradeCount::ATTR_COURSE => $MinimumGradeCount['Course']
                            ));

                        if (null === $Entity) {
                            $Entity = new TblMinimumGradeCount();
                            $Entity->setCount($MinimumGradeCount['Count']);
                            $Entity->setServiceTblLevel($tblLevel);
                            $Entity->setServiceTblSubject(null);
                            $Entity->setTblGradeType($tblGradeType);
                            $Entity->setPeriod($MinimumGradeCount['Period']);
                            $Entity->setHighlighted($highlighted);
                            $Entity->setCourse($MinimumGradeCount['Course']);

                            $Manager->bulkSaveEntity($Entity);
                            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                        }
                    }
                }
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     * @param $Count
     *
     * @return bool
     */
    public function updateMinimumGradeCount(
        TblMinimumGradeCount $tblMinimumGradeCount,
        $Count
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblMinimumGradeCount $Entity */
        $Entity = $Manager->getEntityById('TblMinimumGradeCount', $tblMinimumGradeCount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setCount($Count);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return bool
     */
    public function destroyMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblMinimumGradeCount $Entity */
        $Entity = $Manager->getEntityById('TblMinimumGradeCount', $tblMinimumGradeCount->getId());
        if (null !== $Entity) {

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $tblMinimumGradeCountList
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function destroyBulkMinimumGradeCountList($tblMinimumGradeCountList)
    {
        $manager = $this->getEntityManager();
        /** @var TblMinimumGradeCount $tblMinimumGradeCount */
        foreach ($tblMinimumGradeCountList as $tblMinimumGradeCount) {
            $Entity = $manager->getEntityById('TblMinimumGradeCount', $tblMinimumGradeCount->getId());
            if (null !== $Entity) {
                /** @var TblMinimumGradeCount $Entity */
                $manager->bulkKillEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
        }

        $manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param $highlighted
     * @param TblGradeType|null $tblGradeType
     * @param $period
     * @param $course
     * @param $count
     *
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAllBy(
        $highlighted,
        TblGradeType $tblGradeType = null,
        $period,
        $course,
        $count
    ){

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblMinimumGradeCount', array(
            TblMinimumGradeCount::ATTR_HIGHLIGHTED => $highlighted,
            TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType,
            TblMinimumGradeCount::ATTR_PERIOD => $period,
            TblMinimumGradeCount::ATTR_COURSE => $course,
            TblMinimumGradeCount::ATTR_COUNT => $count
        ));
    }
}