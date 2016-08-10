<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblInvoicePayment;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

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
        if (null === $Entity) {
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
     * @return false|TblPayment
     */
    public function getPaymentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPayment', $Id);
    }

    /**
     * @return bool|TblPayment[]
     */
    public function getPaymentAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPayment');
    }

    /**
     * @param $Id
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPaymentType', $Id);
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
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblPayment[]
     */
    public function getPaymentAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoicePayment',
            array(TblInvoicePayment::ATTR_SERVICE_TBL_INVOICE => $tblInvoice));
        if ($EntityList) {
            /** @var TblInvoicePayment $Entity */
            foreach ($EntityList as &$Entity) {
                $Entity = $Entity->getTblPayment();
            }
        }
        return ( $EntityList );
    }

    /**
     * @param TblPaymentType $tblPaymentType
     * @param                $Value
     * @param                $Purpose
     *
     * @return null|object|TblPayment
     */
    public function createPayment(TblPaymentType $tblPaymentType, $Value, $Purpose)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblPayment();
        $Entity->setTblPaymentType($tblPaymentType);
        $Entity->setValue(str_replace(',', '.', $Value));
        $Entity->setPurpose($Purpose);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);
        return $Entity;
    }

    /**
     * @param TblPayment     $tblPayment
     * @param TblPaymentType $tblPaymentType
     * @param                $Value
     * @param                $Purpose
     *
     * @return false|TblPayment
     */
    public function changePayment(TblPayment $tblPayment, TblPaymentType $tblPaymentType, $Value, $Purpose)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $this->getCachedEntityById(__METHOD__, $Manager, 'TblPayment', $tblPayment->getId());
        $Protocol = clone $Entity;
        /** @var TblPayment $Entity */
        if ($Entity) {
            $Entity->setTblPaymentType($tblPaymentType);
            $Entity->setValue(str_replace(',', '.', $Value));
            $Entity->setPurpose($Purpose);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return $Entity;
        }
        return $Entity;
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblPayment $tblPayment
     *
     * @return TblInvoicePayment
     */
    public function createInvoicePayment(TblInvoice $tblInvoice, TblPayment $tblPayment)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblInvoicePayment();
        $Entity->setServiceTblInvoice($tblInvoice);
        $Entity->setTblPayment($tblPayment);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblPayment $tblPayment
     *
     * @return bool
     */
    public function removePayment(TblPayment $tblPayment)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPayment')->findOneBy(
            array('Id' => $tblPayment->getId())
        );

        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}
