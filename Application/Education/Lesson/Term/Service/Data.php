<?php
namespace SPHERE\Application\Education\Lesson\Term\Service;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearPeriod;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Lesson\Term\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param              $Year
     * @param string       $Description
     *
     * @return TblYear
     */
    public function createYear($Year, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblYear')->findOneBy(array(
            TblYear::ATTR_YEAR        => $Year,
            TblYear::ATTR_DESCRIPTION => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblYear();
            $Entity->setName($Year);
            $Entity->setDescription($Description);
            $Entity->setYear($Year);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $From
     * @param string $To
     * @param string $Description
     *
     * @return TblPeriod
     */
    public function createPeriod($Name, $From, $To, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPeriod')->findOneBy(array(
            TblPeriod::ATTR_NAME        => $Name,
            TblPeriod::ATTR_FROM_DATE   => (new \DateTime($From)),
            TblPeriod::ATTR_TO_DATE     => (new \DateTime($To)),
            TblPeriod::ATTR_DESCRIPTION => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblPeriod();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setFromDate(new \DateTime($From));
            $Entity->setToDate(new \DateTime($To));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblYear      $tblYear
     * @param              $Year
     * @param null         $Description
     *
     * @return bool
     */
    public function updateYear(
        TblYear $tblYear,
        $Year,
        $Description = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblYear $Entity */
        $Entity = $Manager->getEntityById('TblYear', $tblYear->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Year);
            $Entity->setDescription($Description);
            $Entity->setYear($Year);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPeriod $tblPeriod
     * @param           $Name
     * @param           $Description
     * @param           $From
     * @param           $To
     *
     * @return bool
     */
    public function updatePeriod(
        TblPeriod $tblPeriod,
        $Name,
        $Description,
        $From,
        $To
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPeriod $Entity */
        $Entity = $Manager->getEntityById('TblPeriod', $tblPeriod->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setFromDate(new \DateTime($From));
            $Entity->setToDate(new \DateTime($To));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool
     */
    public function destroyYear(TblYear $tblYear)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblYear')->findOneBy(array('Id' => $tblYear->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPeriod $tblPeriod
     *
     * @return bool
     */
    public function destroyPeriod(TblPeriod $tblPeriod)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblPeriod')->findOneBy(array('Id' => $tblPeriod->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblYear   $tblYear
     * @param TblPeriod $tblPeriod
     *
     * @return TblYearPeriod
     */
    public function addYearPeriod(TblYear $tblYear, TblPeriod $tblPeriod)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblYearPeriod')
            ->findOneBy(array(
                TblYearPeriod::ATTR_TBL_YEAR   => $tblYear->getId(),
                TblYearPeriod::ATTR_TBL_PERIOD => $tblPeriod->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblYearPeriod();
            $Entity->setTblYear($tblYear);
            $Entity->setTblPeriod($tblPeriod);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblYear   $tblYear
     * @param TblPeriod $tblPeriod
     *
     * @return bool
     */
    public function removeYearPeriod(TblYear $tblYear, TblPeriod $tblPeriod)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblYearPeriod $Entity */
        $Entity = $Manager->getEntity('TblYearPeriod')
            ->findOneBy(array(
                TblYearPeriod::ATTR_TBL_YEAR   => $tblYear->getId(),
                TblYearPeriod::ATTR_TBL_PERIOD => $tblPeriod->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblPeriod[]
     */
    public function getPeriodAllByYear(TblYear $tblYear)
    {

        /** @var TblYearPeriod[] $EntityList */
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblYearPeriod',
            array(
                TblYearPeriod::ATTR_TBL_YEAR => $tblYear->getId()
            ));
        $Cache = (new CacheFactory())->createHandler(new MemcachedHandler());
        if (null === ( $ResultList = $Cache->getValue($tblYear->getId(), __METHOD__) )
            && !empty( $EntityList )
        ) {

            array_walk($EntityList, function (TblYearPeriod &$V) {

                $V = $V->getTblPeriod();
            });
            $Cache->setValue($tblYear->getId(), $EntityList, 0, __METHOD__);
        } else {
            $EntityList = $ResultList;
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblPeriod $tblPeriod
     *
     * @return bool
     */
    public function getPeriodExistWithYear(TblPeriod $tblPeriod)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblYearPeriod')->findOneBy(array(
            TblYearPeriod::ATTR_TBL_PERIOD => $tblPeriod->getId()
        ));
        return ( null === $Entity ? false : true );
    }

    /**
     * @return bool|TblYear[]
     */
    public function getYearAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYear');
    }

    /**
     * @return bool|TblPeriod[]
     */
    public function getPeriodAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPeriod');
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblYear[]
     */
    public function getYearsByYear(TblYear $tblYear)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYear', array(
            TblYear::ATTR_YEAR => $tblYear->getYear()
        ));
    }

    public function checkYearExist($Year, $Description)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYear', array(
            TblYear::ATTR_YEAR        => $Year,
            TblYear::ATTR_DESCRIPTION => $Description
        ));

    }

    /**
     * @param TblPeriod $tblPeriod
     *
     * @return array|bool
     */
    public function getYearByPeriod(TblPeriod $tblPeriod)
    {

        $TempList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblYearPeriod', array(
                TblYearPeriod::ATTR_TBL_PERIOD => $tblPeriod->getId()
            ));
        $EntityList = array();

        if ($TempList) {
            foreach ($TempList as $Temp) {
                /** @var TblYearPeriod $Temp */
                $EntityList[] = $Temp->getTblYear();
            }

        }

        return ( !empty( $EntityList ) ? $EntityList : false );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPeriod
     */
    public function getPeriodByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPeriod', array(
            TblPeriod::ATTR_NAME => $Name
        ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblPeriod
     */
    public function getPeriodById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblPeriod', $Id);
        return $Entity;

//        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPeriod', $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblYear
     */
    public function getYearById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYear', $Id);
    }
}
