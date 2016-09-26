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
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
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
                TblMinimumGradeCount::ATTR_SERVICE_TBL_SUBJECT => $tblSubject ?  $tblSubject->getId() : null,
                TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType ?  $tblGradeType->getId() : null
            )
        );
    }

    /**
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblMinimumGradeCount');
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
            if (!$levelList){
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

            return empty($levelList) ? false : $levelList;
        }

        return false;
    }

    /**
     * @param $Count
     * @param TblLevel $tblLevel
     * @param TblSubject|null $tblSubject
     * @param TblGradeType|null $tblGradeType
     * @return TblMinimumGradeCount
     */
    public function createMinimumGradeCount(
        $Count,
        TblLevel $tblLevel,
        TblSubject $tblSubject = null,
        TblGradeType $tblGradeType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblMinimumGradeCount')
            ->findOneBy(array(
                TblMinimumGradeCount::ATTR_SERVICE_TBL_LEVEL => $tblLevel->getId(),
                TblMinimumGradeCount::ATTR_SERVICE_TBL_SUBJECT => $tblSubject ?  $tblSubject->getId() : null,
                TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType ?  $tblGradeType->getId() : null
            ));

        if (null === $Entity) {
            $Entity = new TblMinimumGradeCount();
            $Entity->setCount($Count);
            $Entity->setServiceTblLevel($tblLevel);
            $Entity->setServiceTblSubject($tblSubject ? $tblSubject : null);
            $Entity->setTblGradeType($tblGradeType ? $tblGradeType : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
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
}