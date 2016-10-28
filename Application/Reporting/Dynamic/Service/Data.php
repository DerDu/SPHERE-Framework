<?php
namespace SPHERE\Application\Reporting\Dynamic\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilter;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterMask;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterOption;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterSearch;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Reporting\Dynamic\Service
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    /**
     * @param $Id
     *
     * @return false|TblDynamicFilter
     */
    public function getDynamicFilterById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDynamicFilter',
            $Id
        );
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return false|TblDynamicFilter[]
     */
    public function getDynamicFilterAll(TblAccount $tblAccount = null)
    {

        if (null === $tblAccount) {
            return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDynamicFilter'
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDynamicFilter',
                array(
                    TblDynamicFilter::SERVICE_TBL_ACCOUNT => $tblAccount->getId()
                )
            );
        }
    }

    /**
     * @return false|TblDynamicFilter[]
     */
    public function getDynamicFilterAllByIsPublic()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDynamicFilter',
            array(
                TblDynamicFilter::PROPERTY_IS_PUBLIC => true
            )
        );
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return false|TblAccount[]
     */
    public function getDynamicFilterAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDynamicFilter',
            array(
                TblDynamicFilter::SERVICE_TBL_ACCOUNT => $tblAccount->getId()
            )
        );
    }

    /**
     * @param string     $FilterName
     * @param TblAccount $tblAccount
     *
     * @return false|TblDynamicFilter[]
     */
    public function getDynamicFilterAllByName($FilterName, TblAccount $tblAccount = null)
    {

        if (null === $tblAccount) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDynamicFilter',
                array(
                    TblDynamicFilter::PROPERTY_FILTER_NAME => $FilterName
                )
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDynamicFilter',
                array(
                    TblDynamicFilter::PROPERTY_FILTER_NAME => $FilterName,
                    TblDynamicFilter::SERVICE_TBL_ACCOUNT  => $tblAccount->getId()
                )
            );
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblDynamicFilterMask
     */
    public function getDynamicFilterMaskById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDynamicFilterMask', $Id
        );
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     * @param null|int         $FilterPileOrder
     *
     * @return false|Entity\TblDynamicFilterMask[]
     */
    public function getDynamicFilterMaskAllByFilter(TblDynamicFilter $tblDynamicFilter, $FilterPileOrder = null)
    {

        if ($FilterPileOrder === null) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDynamicFilterMask',
                array(
                    TblDynamicFilterMask::TBL_DYNAMIC_FILTER => $tblDynamicFilter->getId()
                )
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDynamicFilterMask',
                array(
                    TblDynamicFilterMask::TBL_DYNAMIC_FILTER         => $tblDynamicFilter->getId(),
                    TblDynamicFilterMask::PROPERTY_FILTER_PILE_ORDER => $FilterPileOrder
                )
            );
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblDynamicFilterOption
     */
    public function getDynamicFilterOptionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDynamicFilterOption', $Id
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblDynamicFilterSearch
     */
    public function getDynamicFilterSearchById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDynamicFilterSearch', $Id
        );
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $FilterName
     * @param bool       $IsPublic
     *
     * @return TblDynamicFilter
     */
    public function createDynamicFilter(TblAccount $tblAccount, $FilterName, $IsPublic = false)
    {

        $Entity = $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDynamicFilter',
            array(
                TblDynamicFilter::SERVICE_TBL_ACCOUNT  => $tblAccount->getId(),
                TblDynamicFilter::PROPERTY_FILTER_NAME => $FilterName
            ));

        if (!$Entity) {
            $Entity = new TblDynamicFilter();
            $Entity->setServiceTblAccount($tblAccount);
            $Entity->setFilterName($FilterName);
            $Entity->setPublic($IsPublic);

            $this->getConnection()->getEntityManager()->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     * @param                  $FilterName
     * @param                  $IsPublic
     *
     * @return bool
     */
    public function updateDynamicFilter(TblDynamicFilter $tblDynamicFilter, $FilterName, $IsPublic)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDynamicFilter $Entity */
        $Entity = $Manager->getEntityById('TblDynamicFilter', $tblDynamicFilter->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setFilterName($FilterName);
            $Entity->setPublic($IsPublic);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     *
     * @return bool
     */
    public function destroyDynamicFilter(TblDynamicFilter $tblDynamicFilter)
    {

        if ($tblDynamicFilter !== null) {
            $Manager = $this->getConnection()->getEntityManager();

            $Entity = $Manager->getEntity('TblDynamicFilter')->findOneBy(array('Id' => $tblDynamicFilter->getId()));
            if ($Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     * @param int              $FilterPileOrder
     * @param string           $FilterClassName
     *
     * @return TblDynamicFilterMask
     */
    public function addDynamicFilterMask(TblDynamicFilter $tblDynamicFilter, $FilterPileOrder, $FilterClassName)
    {

        $Entity = $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDynamicFilterMask',
            array(
                TblDynamicFilterMask::TBL_DYNAMIC_FILTER         => $tblDynamicFilter->getId(),
                TblDynamicFilterMask::PROPERTY_FILTER_PILE_ORDER => $FilterPileOrder
            ));

        if (!$Entity) {
            $Entity = new TblDynamicFilterMask();
            $Entity->setTblDynamicFilter($tblDynamicFilter);
            $Entity->setFilterPileOrder($FilterPileOrder);
            $Entity->setFilterClassName($FilterClassName);

            $this->getConnection()->getEntityManager()->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     * @param int              $FilterPileOrder
     *
     * @return bool
     */
    public function removeDynamicFilterMask(TblDynamicFilter $tblDynamicFilter, $FilterPileOrder)
    {

        /** @var TblDynamicFilterMask $Entity */
        $Entity = $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDynamicFilterMask',
            array(
                TblDynamicFilterMask::TBL_DYNAMIC_FILTER         => $tblDynamicFilter->getId(),
                TblDynamicFilterMask::PROPERTY_FILTER_PILE_ORDER => $FilterPileOrder
            ));

        if ($Entity) {

            // Kill Childs (MaskOption)
            if (( $OptionList = $this->getDynamicFilterOptionAll($Entity) )) {
                foreach ($OptionList as $Option) {
                    $this->removeDynamicFilterOption($Entity, $Option->getFilterFieldName());
                }
            }

            $this->getConnection()->getEntityManager()->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblDynamicFilterMask $tblDynamicFilterMask
     *
     * @return false|TblDynamicFilterOption[]
     */
    public function getDynamicFilterOptionAll(TblDynamicFilterMask $tblDynamicFilterMask = null)
    {

        if (null === $tblDynamicFilterMask) {
            return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDynamicFilterOption'
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
                'TblDynamicFilterOption',
                array(
                    TblDynamicFilterOption::TBL_DYNAMIC_FILTER_MASK => $tblDynamicFilterMask->getId()
                )
            );
        }
    }

    /**
     * @param TblDynamicFilterMask $tblDynamicFilterMask
     * @param string               $FilterFieldName
     *
     * @return bool
     */
    public function removeDynamicFilterOption(
        TblDynamicFilterMask $tblDynamicFilterMask,
        $FilterFieldName
    ) {

        $Entity = $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDynamicFilterOption', array(
                TblDynamicFilterOption::TBL_DYNAMIC_FILTER_MASK    => $tblDynamicFilterMask->getId(),
                TblDynamicFilterOption::PROPERTY_FILTER_FIELD_NAME => $FilterFieldName
            )
        );

        if ($Entity) {

            // Kill Child (MaskSearch)
//            if(($OptionList = $this->getDynamicFilterSearchById( $Entity ))) {
//                foreach($OptionList as $Option) {
//                    $this->removeDynamicFilterOption($Entity, $Option->getFilterFieldName());
//                }
//            }

            $this->getConnection()->getEntityManager()->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDynamicFilterMask $tblDynamicFilterMask
     * @param string               $FilterFieldName
     * @param bool                 $IsMandatory
     *
     * @return TblDynamicFilterOption
     */
    public function addDynamicFilterOption(
        TblDynamicFilterMask $tblDynamicFilterMask,
        $FilterFieldName,
        $IsMandatory = false
    ) {

        $Entity = $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDynamicFilterOption', array(
                TblDynamicFilterOption::TBL_DYNAMIC_FILTER_MASK    => $tblDynamicFilterMask->getId(),
                TblDynamicFilterOption::PROPERTY_FILTER_FIELD_NAME => $FilterFieldName
            )
        );

        if (!$Entity) {
            $Entity = new TblDynamicFilterOption();
            $Entity->setTblDynamicFilterMask($tblDynamicFilterMask);
            $Entity->setFilterFieldName($FilterFieldName);
            $Entity->setMandatory($IsMandatory);

            $this->getConnection()->getEntityManager()->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDynamicFilterMask   $tblDynamicFilterMask
     * @param TblDynamicFilterOption $tblDynamicFilterOption
     * @param string                 $FilterFieldValue
     *
     * @return TblDynamicFilterSearch
     */
    public function setDynamicFilterSearch(
        TblDynamicFilterMask $tblDynamicFilterMask,
        TblDynamicFilterOption $tblDynamicFilterOption,
        $FilterFieldValue
    ) {

        $Entity = $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDynamicFilterSearch',
            array(
                TblDynamicFilterSearch::TBL_DYNAMIC_FILTER_MASK   => $tblDynamicFilterMask->getId(),
                TblDynamicFilterSearch::TBL_DYNAMIC_FILTER_OPTION => $tblDynamicFilterOption->getId()
            ));

        if (!$Entity) {
            $Entity = new TblDynamicFilterSearch();
            $Entity->setTblDynamicFilterMask($tblDynamicFilterMask);
            $Entity->setTblDynamicFilterOption($tblDynamicFilterOption);
            $Entity->setFilterFieldValue($FilterFieldValue);

            $this->getConnection()->getEntityManager()->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            /** @var TblDynamicFilterSearch $Entity */
            $Protocol = clone $Entity;
            $Entity->setTblDynamicFilterMask($tblDynamicFilterMask);
            $Entity->setTblDynamicFilterOption($tblDynamicFilterOption);
            $Entity->setFilterFieldValue($FilterFieldValue);

            $this->getConnection()->getEntityManager()->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }
}
