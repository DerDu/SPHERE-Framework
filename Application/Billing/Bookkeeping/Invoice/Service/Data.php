<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor as TblDebtorAccounting;
use SPHERE\Application\Billing\Accounting\SchoolAccount\Service\Entity\TblSchoolAccount;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param int $Id
     *
     * @return false|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice', $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblItem
     */
    public function getItemById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblItem', $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor', $Id);
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * IsReversal = false
     *
     * @param bool $Check
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByIsPaid($Check = true)
    {
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice',
            array(TblInvoice::ATTR_IS_PAID     => $Check,
                  TblInvoice::ATTR_IS_REVERSAL => false));
        return $EntityList;
    }

    /**
     * IsReversal = false
     *
     * @param bool $Check
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByIsReversal($Check = true)
    {
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice',
            array(TblInvoice::ATTR_IS_PAID     => false,
                  TblInvoice::ATTR_IS_REVERSAL => $Check));
        return $EntityList;
    }

    /**
     * @param $InvoiceNumber
     *
     * @return TblInvoice|bool
     */
    public function getInvoiceByNumber($InvoiceNumber)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')
            ->findOneBy(array(TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return TblInvoiceItem[]|bool
     */
    public function getInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblInvoiceItem')
            ->findBy(array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $IsPaid
     * @param bool      $IsReversal
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByPerson(TblPerson $tblPerson, $IsPaid = false, $IsReversal = false)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItem',
            array(TblInvoiceItem::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
        if ($EntityList) {
            /** @var TblInvoiceItem $Entity */
            foreach ($EntityList as &$Entity) {
                $tblInvoice = $Entity->getTblInvoice();
                if ($tblInvoice) {
                    if ($tblInvoice->getIsPaid() == $IsPaid && !$tblInvoice->getIsReversal() == $IsReversal) {
                        $Entity = $tblInvoice;
                    } else {
                        $Entity = false;
                    }
                }
            }
            $EntityList = array_filter($EntityList);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblItem[]
     */
    public function getItemAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItem',
            array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId()));
        if ($EntityList) {
            /** @var TblInvoiceItem $Entity */
            foreach ($EntityList as &$Entity) {
                $Entity = $Entity->getTblItem();
            }
            $EntityList = array_filter($EntityList);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblPerson[]
     */
    public function getPersonAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItem',
            array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId()));
        if ($EntityList) {
            /** @var TblInvoiceItem $Entity */
            foreach ($EntityList as &$Entity) {
                $Entity = $Entity->getServiceTblPerson();
            }
            $EntityList = array_filter($EntityList);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblDebtor[]
     */
    public function getDebtorAllByInvoice(TblInvoice $tblInvoice)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItem',
            array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId()));
        if ($EntityList) {
            /** @var TblInvoiceItem $Entity */
            foreach ($EntityList as &$Entity) {
                $Entity = $Entity->getServiceTblDebtor();
            }
            $EntityList = array_filter($EntityList);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     *
     * @return bool|TblPerson
     */
    public function getPersonByInvoiceAndItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {
        /** @param TblInvoiceItem $Entity */
        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItem',
            array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId(),
                  TblInvoiceItem::ATTR_TBL_ITEM    => $tblItem->getId()));
        if ($Entity) {
            /** @var TblInvoiceItem $Entity */
            $Entity = $Entity->getServiceTblPerson();
        }
        return ( $Entity == false ? false : $Entity );
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblPerson  $tblPerson
     *
     * @return bool|TblInvoiceItem
     */
    public function getItemAllInvoiceAndPerson(TblInvoice $tblInvoice, TblPerson $tblPerson)
    {
        /** @param TblInvoiceItem $Entity */
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItem',
            array(TblInvoiceItem::ATTR_TBL_INVOICE        => $tblInvoice->getId(),
                  TblInvoiceItem::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
        if ($EntityList) {
            /** @var TblInvoiceItem $Entity */
            foreach ($EntityList as &$Entity) {
                $Entity = $Entity->getTblItem();
            }
            $EntityList = array_filter($EntityList);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     *
     * @return bool|TblDebtor
     */
    public function getDebtorByInvoiceAndItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {
        /** @param TblInvoiceItem $Entity */
        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceItem',
            array(TblInvoiceItem::ATTR_TBL_INVOICE => $tblInvoice->getId(),
                  TblInvoiceItem::ATTR_TBL_ITEM    => $tblItem->getId()));
        if ($Entity) {
            /** @var TblInvoiceItem $Entity */
            $Entity = $Entity->getServiceTblDebtor();
        }
        return ( $Entity == false ? false : $Entity );
    }

    /**
     * @param \DateTime      $From
     * @param \DateTime|null $To
     * @param int            $Status "Invoice" 1 = open, 2 = paid, 3 = storno
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByDate(\DateTime $From, \DateTime $To = null, $Status = 1)
    {

        if ($To == null) {
            $To = new \DateTime('now');
        }
        $IsPaid = false;
        $IsStorno = false;
        if ($Status == 2) {
            $IsPaid = true;
        }
        if ($Status == 3) {
            $IsStorno = true;
        }

        $EntityList = $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice');
        if ($EntityList) {
            /** @var TblInvoice $Entity */
            foreach ($EntityList as &$Entity) {
                if (new \DateTime($Entity->getTargetTime()) < $From || new \DateTime($Entity->getTargetTime()) > $To) {
                    $Entity = false;
                } else {
                    if ($Entity->getIsPaid() != $IsPaid || $Entity->getIsReversal() != $IsStorno) {
                        $Entity = false;
                    }
                }
            }
            $EntityList = array_filter($EntityList);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param \DateTime $Date
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByYearAndMonth(\DateTime $Date)
    {

        $EntityList = $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice');
        if ($EntityList) {
            /** @var TblInvoice $Entity */
            foreach ($EntityList as &$Entity) {
                if ((new \DateTime($Entity->getTargetTime()))->format('ym') != $Date->format('ym')) {
                    $Entity = false;
                }
            }
            $EntityList = array_filter($EntityList);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblPerson             $tblPerson
     * @param TblSchoolAccount|null $tblSchoolAccount
     * @param                       $InvoiceNumber
     * @param                       $Date
     * @param TblAddress|null       $tblAddress
     * @param TblMail|null          $tblMail
     * @param TblPhone|null         $tblPhone
     *
     * @return null|object|TblInvoice
     */
    public function createInvoice(
        TblPerson $tblPerson,
        TblSchoolAccount $tblSchoolAccount = null,
        $InvoiceNumber,
        $Date,
        TblAddress $tblAddress = null,
        TblMail $tblMail = null,
        TblPhone $tblPhone = null
    ) // Todo Tabelle erweitern
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = null;
        $Entity = $Manager->getEntity('TblInvoice')->findOneBy(
            array(TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber));

        if ($Entity === null) {
            $Entity = new TblInvoice();
            $Entity->setInvoiceNumber($InvoiceNumber);
            $Entity->setTargetTime(( $Date ? new \DateTime($Date) : null ));
            if ($tblSchoolAccount != null) {
                $Entity->setSchoolName($tblSchoolAccount->getServiceTblCompany()->getDisplayName());
                $Entity->setSchoolOwner($tblSchoolAccount->getOwner());
                $Entity->setSchoolBankName($tblSchoolAccount->getBankName());
                $Entity->setSchoolIBAN($tblSchoolAccount->getIBAN());
                $Entity->setSchoolBIC($tblSchoolAccount->getBIC());
            } else {
                $Entity->setSchoolName('');
                $Entity->setSchoolOwner('');
                $Entity->setSchoolBankName('');
                $Entity->setSchoolIBAN('');
                $Entity->setSchoolBIC('');
            }
            $Entity->setIsPaid(false);
            $Entity->setIsReversal(false);
            if (null !== $tblAddress) {
                $Entity->setServiceTblAddress($tblAddress);
            }
            if (null !== $tblMail) {
                $Entity->setServiceTblMail($tblMail);
            }
            if (null !== $tblPhone) {
                $Entity->setServiceTblPhone($tblPhone);
            }
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param bool       $isReversal
     *
     * @return bool
     */
    public function changeInvoiceIsReversal(TblInvoice $tblInvoice, $isReversal = true)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsReversal($isReversal);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param bool       $isPaid
     *
     * @return bool
     */
    public function changeInvoiceIsPaid(TblInvoice $tblInvoice, $isPaid = true)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoice $Entity */
        $Entity = $Manager->getEntityById('TblInvoice', $tblInvoice->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsPaid($isPaid);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtorAccounting   $tblDebtor
     * @param TblPaymentType        $tblPaymentType
     * @param TblBankReference|null $tblBankReference
     *
     * @return null|object|TblDebtor
     */
    public function createDebtor(
        TblDebtorAccounting $tblDebtor,
        TblPaymentType $tblPaymentType,
        TblBankReference $tblBankReference = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = null;

        if ($tblBankReference) {

            $Entity = $Manager->getEntity('TblDebtor')->findOneBy(
                array(TblDebtor::ATTR_SERVICE_TBL_DEBTOR            => $tblDebtor->getId(),
                      TblDebtor::ATTR_SERVICE_TBL_BANKING_REFERENCE => $tblBankReference->getId(),
                      TblDebtor::ATTR_SERVICE_TBL_PAYMENT_TYPE      => $tblPaymentType->getId()));

            if ($Entity === null) {
                $Entity = new TblDebtor();
                $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
                $Entity->setDebtorPerson($tblDebtor->getServiceTblPerson());
                $Entity->setBankReference($tblBankReference->getReference());
                $Entity->setOwner($tblBankReference->getOwner());
                $Entity->setBankName($tblBankReference->getBankName());
                $Entity->setIBAN($tblBankReference->getIBAN());
                $Entity->setBIC($tblBankReference->getBIC());
                $Entity->setServiceTblDebtor($tblDebtor);
                $Entity->setServiceTblBankReference($tblBankReference);
                $Entity->setServiceTblPaymentType($tblPaymentType);

                $Manager->saveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                    $Entity);
            }
        } else {
            $Entity = $Manager->getEntity('TblDebtor')->findOneBy(
                array(TblDebtor::ATTR_SERVICE_TBL_DEBTOR            => $tblDebtor->getId(),
                      TblDebtor::ATTR_SERVICE_TBL_BANKING_REFERENCE => null));

            if ($Entity === null) {
                $Entity = new TblDebtor();
                $Entity->setDebtorNumber($tblDebtor->getDebtorNumber());
                $Entity->setDebtorPerson($tblDebtor->getServiceTblPerson());
                $Entity->setBankReference('');
                $Entity->setOwner('');
                $Entity->setBankName('');
                $Entity->setIBAN('');
                $Entity->setBIC('');
                $Entity->setServiceTblDebtor($tblDebtor);
                $Entity->setServiceTblBankReference(null);
                $Entity->setServiceTblPaymentType($tblPaymentType);

                $Manager->saveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                    $Entity);
            }
        }

        return $Entity;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return TblItem
     */
    public function createItem(TblBasketVerification $tblBasketVerification)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = null;
        if ($tblBasketVerification->getServiceTblItem()) {
            $Entity = $Manager->getEntity('TblItem')->findOneBy(
                array(TblItem::ATTR_NAME             => $tblBasketVerification->getServiceTblItem()->getName(),
                      TblItem::ATTR_DESCRIPTION      => $tblBasketVerification->getServiceTblItem()->getDescription(),
                      TblItem::ATTR_VALUE            => $tblBasketVerification->getValue(),
                      TblItem::ATTR_QUANTITY         => $tblBasketVerification->getQuantity(),
                      TblItem::ATTR_SERVICE_TBL_ITEM => $tblBasketVerification->getServiceTblItem()->getId()));
        }

        if ($Entity === null) {
            $Entity = new TblItem();
            $Entity->setName($tblBasketVerification->getServiceTblItem()->getName());
            $Entity->setDescription($tblBasketVerification->getServiceTblItem()->getDescription());
            $Entity->setValue($tblBasketVerification->getValue());
            $Entity->setQuantity($tblBasketVerification->getQuantity());
            $Entity->setServiceTblItem($tblBasketVerification->getServiceTblItem());

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     * @param TblPerson  $tblPerson
     * @param TblDebtor  $tblDebtor
     *
     * @return TblInvoiceItem
     */
    public function createInvoiceItem(TblInvoice $tblInvoice, TblItem $tblItem, TblPerson $tblPerson, TblDebtor $tblDebtor)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblInvoiceItem();
        $Entity->setTblInvoice($tblInvoice);
        $Entity->setTblItem($tblItem);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblDebtor($tblDebtor);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }
}
