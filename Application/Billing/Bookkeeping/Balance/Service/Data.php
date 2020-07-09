<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Bookkeeping\Balance\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        /**
         * TblPayment
         */
        $this->createPaymentType('SEPA-Lastschrift');
        $this->createPaymentType('SEPA-Überweisung');
        $this->createPaymentType('Bar');
    }

    /**
     * @param $PaymentType
     *
     * @return TblPaymentType|null|object
     */
    public function createPaymentType($PaymentType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPaymentType')->findOneBy(array(TblPaymentType::ATTR_NAME => $PaymentType));
        if(null === $Entity){
            $Entity = new TblPaymentType();
            $Entity->setName($PaymentType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType',
            $Id);
    }

    /**
     * @return false|TblPaymentType[]
     */
    public function getPaymentTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType');
    }

    /**
     * @param $Name
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType',
            array(TblPaymentType::ATTR_NAME => $Name));
    }

    /**
     * @param $Year
     * @param $MonthFrom
     * @param $MonthTo
     *
     * @return array|bool
     */
    public function getPersonIdListByInvoiceTime($Year, $MonthFrom, $MonthTo)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $tblInvoice = new TblInvoice();
        $tblInvoiceItemDebtor = new TblInvoiceItemDebtor();

        $query = $queryBuilder->select('i.serviceTblPersonCauser as PersonCauserId')
            ->from($tblInvoice->getEntityFullName(), 'i')
            ->leftJoin($tblInvoiceItemDebtor->getEntityFullName(), 'iid',
                'WITH', 'iid.tblInvoice = i.Id')
            ->where($queryBuilder->expr()->eq('i.Year', '?1'))
            ->andWhere($queryBuilder->expr()->between('i.Month', '?2', '?3'))
            ->setParameter(1, $Year)
            ->setParameter(2, $MonthFrom)
            ->setParameter(3, $MonthTo)
            ->groupBy('i.serviceTblPersonCauser')
            ->getQuery();
        $PersonIdList = $query->getResult();

        return !empty($PersonIdList) ? $PersonIdList : false;
    }

    /**
     * @param TblItem $tblItem
     * @param string  $Year
     * @param string  $BasketTypeId
     * @param string  $MonthFrom
     * @param string  $MonthTo
     *
     * @return array|bool
     */
    public function getPriceList(TblItem $tblItem, $Year, $BasketTypeId, $MonthFrom, $MonthTo)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $tblInvoice = new TblInvoice();
        $tblInvoiceItemDebtor = new TblInvoiceItemDebtor();
        $tblBasket = new TblBasket();
        $tblBasketType = new TblBasketType();

        $queryBuilder->select('i.Year, i.Month, i.serviceTblPersonCauser as PeronCauserId, iid.Value,
             iid.Quantity, iid.IsPaid, iid.serviceTblPersonDebtor as PersonDebtorId, bt.Id as BasketTypeId')
            ->from($tblInvoice->getEntityFullName(), 'i')
            ->leftJoin($tblInvoiceItemDebtor->getEntityFullName(), 'iid',
                'WITH', 'iid.tblInvoice = i.Id')
            ->leftJoin($tblBasket->getEntityFullName(), 'b',
                'WITH', 'b.Id = i.serviceTblBasket')
            ->leftJoin($tblBasketType->getEntityFullName(), 'bt',
                'WITH', 'bt.Id = b.tblBasketType')
            ->where($queryBuilder->expr()->eq('i.Year', '?1'))
            ->andWhere($queryBuilder->expr()->between('i.Month', '?2', '?3'))
            ->andWhere($queryBuilder->expr()->eq('iid.serviceTblItem', '?4'))
            ->setParameter(1, $Year)
            ->setParameter(2, $MonthFrom)
            ->setParameter(3, $MonthTo)
            ->setParameter(4, $tblItem->getId());
        // multi BasketType
        if($BasketTypeId == '-1'){
            $tblBasketTypeA = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_ABRECHNUNG);
            $tblBasketTypeB = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_GUTSCHRIFT);
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX($queryBuilder->expr()->eq('bt.Id', '?5'),
                    $queryBuilder->expr()->eq('bt.Id', '?6')))
                ->setParameter(5, $tblBasketTypeA->getId())
                ->setParameter(6, $tblBasketTypeB->getId());
        } else {
            // single BasketType
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('bt.Id', '?5'))
                ->setParameter(5, $BasketTypeId);
        }

        $query = $queryBuilder->getQuery();

        $PriceList = $query->getResult();

        return !empty($PriceList) ? $PriceList : false;
    }

    /**
     * @param TblItem   $tblItem
     * @param string    $Year
     * @param string    $MonthFrom
     * @param string    $MonthTo
     * @param TblPerson $tblPerson
     * @param string    $BasketTypeId
     *
     * @return array|bool
     */
    public function getPriceListByPerson(TblItem $tblItem, $Year, $MonthFrom, $MonthTo, TblPerson $tblPerson,
        $BasketTypeId = '')
    {
        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $tblInvoice = new TblInvoice();
        $tblInvoiceItemDebtor = new TblInvoiceItemDebtor();
        $tblBasket = new TblBasket();
        $tblBasketType = new TblBasketType();

        $queryBuilder->select('i.Year, i.Month, i.serviceTblPersonCauser as PeronCauserId, iid.Value,
             iid.Quantity, iid.IsPaid, iid.serviceTblPersonDebtor as PersonDebtorId, bt.Id as BasketTypeId')
            ->from($tblInvoice->getEntityFullName(), 'i')
            ->leftJoin($tblInvoiceItemDebtor->getEntityFullName(), 'iid',
                'WITH', 'iid.tblInvoice = i.Id')
            ->leftJoin($tblBasket->getEntityFullName(), 'b',
                'WITH', 'b.Id = i.serviceTblBasket')
            ->leftJoin($tblBasketType->getEntityFullName(), 'bt',
                'WITH', 'bt.Id = b.tblBasketType')
            ->where($queryBuilder->expr()->eq('i.Year', '?1'))
            ->andWhere($queryBuilder->expr()->between('i.Month', '?2', '?3'))
            ->andWhere($queryBuilder->expr()->eq('iid.serviceTblItem', '?4'))
            ->andWhere($queryBuilder->expr()->eq('i.serviceTblPersonCauser', '?5'))
            ->setParameter(1, $Year)
            ->setParameter(2, $MonthFrom)
            ->setParameter(3, $MonthTo)
            ->setParameter(4, $tblItem->getId())
            ->setParameter(5, $tblPerson->getId());

        // multi BasketType
        if($BasketTypeId == '-1'){
            $tblBasketTypeA = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_ABRECHNUNG);
            $tblBasketTypeB = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_GUTSCHRIFT);
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX($queryBuilder->expr()->eq('bt.Id', '?6'),
                    $queryBuilder->expr()->eq('bt.Id', '?7')))
                ->setParameter(6, $tblBasketTypeA->getId())
                ->setParameter(7, $tblBasketTypeB->getId());
        } else {
            // single BasketType
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('bt.Id', '?6'))
                ->setParameter(6, $BasketTypeId);
        }

        $query =$queryBuilder->getQuery();

        $PriceList = $query->getResult();

        return !empty($PriceList) ? $PriceList : false;
    }

    /**
     * @param         $Year
     * @param         $Month
     *
     * @return array|bool
     */
    public function getPriceSummaryByMonth($Year, $Month)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('iid.Name,i.Year, i.Month, iid.serviceTblItem as ItemId, sum(iid.Value * iid.Quantity) as Summary')
            ->from('SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice', 'i')
            ->leftJoin('SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor', 'iid',
                'WITH', 'i.Id = iid.tblInvoice')
            ->where($queryBuilder->expr()->eq('i.Year', '?1'))
            ->andWhere($queryBuilder->expr()->eq('i.Month', '?2'))
            ->groupBy('i.Year, i.Month, iid.serviceTblItem')
            ->setParameter(1, $Year)
            ->setParameter(2, $Month)
            ->getQuery();

        $MonthOverViewList = $query->getResult();
        return !empty($MonthOverViewList) ? $MonthOverViewList : false;
    }

    /**
     * @param string $Year
     *
     * @return array|bool
     */
    public function getPriceSummaryByYear($Year)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('iid.Name,i.Year, i.Month, iid.serviceTblItem as ItemId, sum(iid.Value * iid.Quantity) as Summary')
            ->from('SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice', 'i')
            ->leftJoin('SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor', 'iid',
                'WITH', 'i.Id = iid.tblInvoice')
            ->where($queryBuilder->expr()->eq('i.Year', '?1'))
            ->groupBy('i.Year, i.Month, iid.serviceTblItem')
            ->setParameter(1, $Year)
            ->getQuery();

        $YearOverViewList = $query->getResult();
        return !empty($YearOverViewList) ? $YearOverViewList : false;
    }
}
