<?php
namespace SPHERE\Application\Education\Lesson\Term\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblHoliday;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblHolidayType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearHoliday;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYearPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Lesson\Term\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewYear[]
     */
    public function viewYear()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewYear'
        );
    }

    /**
     * @return false|ViewYearPeriod[]
     */
    public function viewYearPeriod()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewYearPeriod'
        );
    }

    public function setupDatabaseContent()
    {

        $this->createHolidayType('Ferien', 'HOLIDAY');
        $this->createHolidayType('Feiertag', 'PUBLIC_HOLIDAY');
        $this->createHolidayType('Unterrichtsfreier Tag', 'SCHOOL_FREE_DAY');
    }

    /**
     * @param              $Year
     * @param string $Description
     *
     * @return TblYear
     */
    public function createYear($Year, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblYear')->findOneBy(array(
            TblYear::ATTR_YEAR => $Year,
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
            TblPeriod::ATTR_NAME => $Name,
            TblPeriod::ATTR_FROM_DATE => (new \DateTime($From)),
            TblPeriod::ATTR_TO_DATE => (new \DateTime($To)),
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
     * @param TblYear $tblYear
     * @param              $Year
     * @param null $Description
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
     * @param TblYear $tblYear
     * @param TblPeriod $tblPeriod
     *
     * @return TblYearPeriod
     */
    public function addYearPeriod(TblYear $tblYear, TblPeriod $tblPeriod)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblYearPeriod')
            ->findOneBy(array(
                TblYearPeriod::ATTR_TBL_YEAR => $tblYear->getId(),
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
     * @param TblYear $tblYear
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
                TblYearPeriod::ATTR_TBL_YEAR => $tblYear->getId(),
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
        if (null === ($ResultList = $Cache->getValue($tblYear->getId(), __METHOD__))
            && !empty($EntityList)
        ) {

            array_walk($EntityList, function (TblYearPeriod &$V) {

                $V = $V->getTblPeriod();
            });
            /** @var TblPeriod[] $EntityList */
            $EntityList = $this->getSorter($EntityList)->sortObjectBy(TblPeriod::ATTR_FROM_DATE, new DateTimeSorter());

            $Cache->setValue($tblYear->getId(), $EntityList, 0, __METHOD__);
        } else {
            $EntityList = $ResultList;
        }
        return (null === $EntityList ? false : $EntityList);
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
        return (null === $Entity ? false : true);
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
            TblYear::ATTR_YEAR => $Year,
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

        return (!empty($EntityList) ? $EntityList : false);
    }

    /**
     * @param $String
     *
     * @return false|TblYear[]
     */
    public function getYearByName($String)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblYear', array(
                TblYear::ATTR_YEAR => $String
            ));
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

    /**
     * @param $Id
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblHolidayType',
            $Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblHolidayType',
            array(
                TblHolidayType::ATTR_IDENTIFIER => $Identifier
            )
        );
    }

    /**
     * @return false|TblHolidayType[]
     */
    public function getHolidayTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblHolidayType');
    }

    /**
     * @param $Id
     *
     * @return false|TblHoliday
     */
    public function getHolidayById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblHoliday',
            $Id);
    }

    /**
     * @return false|TblHoliday[]
     */
    public function getHolidayAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblHoliday');
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblHoliday[]
     */
    public function getHolidayAllByYear(TblYear $tblYear)
    {

        $resultList = array();
        $list = $this->getYearHolidayAllByYear($tblYear);
        if ($list) {
            foreach ($list as $tblYearHoliday) {
                $resultList[$tblYearHoliday->getTblHoliday()->getId()] = $tblYearHoliday->getTblHoliday();
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * Alle möglichen Holidays innerhalb des Schuljahres
     *
     * @param TblYear $tblYear
     *
     * @return false|TblHoliday[]
     */
    public function getHolidayAllWhereYear(TblYear $tblYear)
    {

        $fromDate = false;
        $toDate = false;
        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
        if ($tblPeriodList) {
            foreach ($tblPeriodList as $tblPeriod) {
                if ($fromDate) {
                    if ($fromDate > new \DateTime($tblPeriod->getFromDate())) {
                        $fromDate = new \DateTime($tblPeriod->getFromDate());
                    }
                } else {
                    $fromDate = new \DateTime($tblPeriod->getFromDate());
                }

                if ($toDate) {
                    if ($toDate < new \DateTime($tblPeriod->getToDate())) {
                        $toDate = new \DateTime($tblPeriod->getToDate());
                    }
                } else {
                    $toDate = new \DateTime($tblPeriod->getToDate());
                }
            }
        }

        $resultList = array();
        if ($fromDate && $toDate) {
            $Manager = $this->getConnection()->getEntityManager();
            $queryBuilder = $Manager->getQueryBuilder();

            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblHoliday', 't')
                ->where($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->between('t.FromDate', '?1', '?2'),
                    $queryBuilder->expr()->between('t.ToDate', '?1', '?2')
                ))
                ->setParameter(1, $fromDate)
                ->setParameter(2, $toDate)
                ->getQuery();

            $resultList = $query->getResult();
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblYear $tblYear
     * @param \DateTime $date
     *
     * @return false|TblHoliday
     */
    public function getHolidayByDay(TblYear $tblYear, \DateTime $date)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('y')
            ->from(__NAMESPACE__ . '\Entity\TblHoliday', 'h')
            ->join(__NAMESPACE__ . '\Entity\TblYearHoliday', 'y')
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('h.FromDate', '?1'),
                        $queryBuilder->expr()->isNull('h.ToDate'),

                        $queryBuilder->expr()->eq('y.tblHoliday', 'h.Id'),
                        $queryBuilder->expr()->eq('y.tblYear', '?2')
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->lte('h.FromDate', '?1'),
                        $queryBuilder->expr()->gte('h.ToDate', '?1'),

                        $queryBuilder->expr()->eq('y.tblHoliday', 'h.Id'),
                        $queryBuilder->expr()->eq('y.tblYear', '?2')
                    )
                )
            )
            ->setParameter(1, $date)
            ->setParameter(2, $tblYear->getId())
            ->getQuery();

        $resultList = $query->getResult();

        if (!empty($resultList)) {
            /** @var TblYearHoliday $tblYearHoliday */
            $tblYearHoliday = current($resultList);
            $tblHoliday = $tblYearHoliday->getTblHoliday();

            return $tblHoliday;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblYearHoliday
     */
    public function getYearHolidayById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYearHoliday',
            $Id);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblYearHoliday[]
     */
    public function getYearHolidayAllByYear(TblYear $tblYear)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYearHoliday',
            array(
                TblYearHoliday::ATTR_TBL_YEAR => $tblYear->getId()
            )
        );
    }

    /**
     * @param TblHoliday $tblHoliday
     *
     * @return false|TblYearHoliday[]
     */
    public function getYearHolidayAllByHoliday(TblHoliday $tblHoliday)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYearHoliday',
            array(
                TblYearHoliday::ATTR_TBL_HOLIDAY => $tblHoliday->getId()
            )
        );
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblHolidayType
     */
    public function createHolidayType($Name, $Identifier)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblHolidayType')->findOneBy(array(
            TblHolidayType::ATTR_NAME => $Name,
            TblHolidayType::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));

        if (null === $Entity) {
            $Entity = new TblHolidayType();
            $Entity->setName($Name);
            $Entity->setIdentifier(strtoupper($Identifier));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblHolidayType $tblHolidayType
     * @param $Name
     * @param $FromDate
     * @param $ToDate
     *
     * @return TblHoliday
     */
    public function createHoliday(
        TblHolidayType $tblHolidayType,
        $Name,
        $FromDate,
        $ToDate
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblHoliday();
        $Entity->setTblHolidayType($tblHolidayType);
        $Entity->setName($Name);
        $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
        $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblHoliday $tblHoliday
     * @param TblHolidayType $tblHolidayType
     * @param $Name
     * @param $FromDate
     * @param $ToDate
     *
     * @return bool
     */
    public function updateHoliday(
        TblHoliday $tblHoliday,
        TblHolidayType $tblHolidayType,
        $Name,
        $FromDate,
        $ToDate
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblHoliday $Entity */
        $Entity = $Manager->getEntityById('TblHoliday', $tblHoliday->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblHolidayType($tblHolidayType);
            $Entity->setName($Name);
            $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
            $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblYear $tblYear
     * @param TblHoliday $tblHoliday
     * @param TblCompany|null $tblCompany
     *
     * @return TblYearHoliday
     */
    public function addYearHoliday(TblYear $tblYear, TblHoliday $tblHoliday, TblCompany $tblCompany = null)
    {

        $Manager = $this->getConnection()->getEntityManager();

        if ($tblCompany === null) {
            $Entity = $Manager->getEntity('TblYearHoliday')
                ->findOneBy(array(
                    TblYearHoliday::ATTR_TBL_YEAR => $tblYear->getId(),
                    TblYearHoliday::ATTR_TBL_HOLIDAY => $tblHoliday->getId()
                ));
        } else {
            $Entity = $Manager->getEntity('TblYearHoliday')
                ->findOneBy(array(
                    TblYearHoliday::ATTR_TBL_YEAR => $tblYear->getId(),
                    TblYearHoliday::ATTR_TBL_HOLIDAY => $tblHoliday->getId(),
                    TblYearHoliday::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId()
                ));
        }

        if (null === $Entity) {
            $Entity = new TblYearHoliday();
            $Entity->setTblYear($tblYear);
            $Entity->setTblHoliday($tblHoliday);
            $Entity->setServiceTblCompany($tblCompany ? $tblCompany : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblYearHoliday $tblYearHoliday
     *
     * @return bool
     */
    public function removeYearHoliday(TblYearHoliday $tblYearHoliday)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblHoliday $Entity */
        $Entity = $Manager->getEntity('TblYearHoliday')
            ->findOneBy(array(
                'Id' => $tblYearHoliday->getId(),
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblHoliday $tblHoliday
     *
     * @return bool
     */
    public function destroyHoliday(TblHoliday $tblHoliday)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblHoliday $Entity */
        $Entity = $Manager->getEntityById('TblHoliday', $tblHoliday->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}
