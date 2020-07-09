<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceCreditor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
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
     * @return false|TblInvoiceItemDebtor
     */
    public function getInvoiceItemDebtorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblInvoiceItemDebtor',
            $Id);
    }

    /**
     * @param bool $IsPaid
     *
     * @return false|TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByIsPaid($IsPaid = false)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblInvoiceItemDebtor', array(
                TblInvoiceItemDebtor::ATTR_IS_PAID => $IsPaid
            ));
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByInvoice(TblInvoice $tblInvoice)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblInvoiceItemDebtor',
            array(
                TblInvoiceItemDebtor::ATTR_TBL_INVOICE => $tblInvoice->getId()
            ));
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     *
     * @return false|TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByInvoiceAndItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblInvoiceItemDebtor',
            array(
                TblInvoiceItemDebtor::ATTR_TBL_INVOICE      => $tblInvoice->getId(),
                TblInvoiceItemDebtor::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()
            ));
    }

    /**
     * @param int $Id
     *
     * @return false|TblInvoiceCreditor
     */
    public function getInvoiceCreditorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoiceCreditor',
            $Id);
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblInvoice')->findAll();
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param $InvoiceNumber
     *
     * @return TblInvoice|bool
     */
    public function getInvoiceByNumber($InvoiceNumber)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInvoice|null $Entity */
        return $this->getCachedEntityBy(__METHOD__, $Manager, 'TblInvoice',
            array(
                TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber
            ));
    }

    /**
     * @param TblPerson $tblPersonCauser
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByPersonCauser(TblPerson $tblPersonCauser)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInvoice|null $Entity */
        return $this->getCachedEntityListBy(__METHOD__, $Manager, 'TblInvoice',
            array(
                TblInvoice::ATTR_SERVICE_TBL_PERSON_CAUSER => $tblPersonCauser->getId()
            ));
    }

    /**
     * @param TblPerson $tblPersonCauser
     * @param string    $Year
     * @param string    $Month
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByPersonCauserAndTime(TblPerson $tblPersonCauser, $Year = '', $Month = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblInvoice|null $Entity */
        return $this->getCachedEntityListBy(__METHOD__, $Manager, 'TblInvoice',
            array(
                TblInvoice::ATTR_SERVICE_TBL_PERSON_CAUSER => $tblPersonCauser,
                TblInvoice::ATTR_YEAR => $Year,
                TblInvoice::ATTR_MONTH => $Month
            ));
    }

    /**
     * @param $Year
     * @param $Month
     *
     * @return int
     */
    public function getMaxInvoiceNumberByYearAndMonth($Year, $Month)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Builder = $Manager->getQueryBuilder();

        $Query = $Manager->getQueryBuilder()
            ->select('MAX(I.IntegerNumber)')
            ->from(__NAMESPACE__.'\Entity\TblInvoice', 'I')
            ->where($Builder->expr()->andX(
                $Builder->expr()->eq('I.Year', '?1'),
                $Builder->expr()->eq('I.Month', '?2')
            ))
            ->setParameter(1, $Year)
            ->setParameter(2, $Month)
            ->getQuery();

        $resultList = $Query->getResult();
        $result = false;
        //get Result
        if(!empty($resultList)){
            if(isset($resultList[0][1])){
                $result = (int)$resultList[0][1];
            }
        }

        return ($result ? $result : 0);
    }

    /**
     * @param string $Year
     * @param string $Month
     * @param string $BasketName
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByYearAndMonth($Year, $Month, $BasketName = '')
    {

        $FilterArray = array(
            TblInvoice::ATTR_YEAR  => $Year,
            TblInvoice::ATTR_MONTH => $Month,
        );
        if($BasketName){
            $FilterArray[TblInvoice::ATTR_BASKET_NAME] = $BasketName;
        }
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice',
            $FilterArray);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblInvoice[]
     */
    public function getInvoiceByBasket(TblBasket $tblBasket)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice',
            array(
                TblInvoice::ATTR_SERVICE_TBL_BASKET  => $tblBasket->getId()
            )
        );
    }

    /**
     * @param $IntegerNumber
     * @param $Year
     * @param $Month
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceByIntegerAndYearAndMonth($IntegerNumber, $Year, $Month)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblInvoice',
            array(
                TblInvoice::ATTR_INTEGER_NUMBER => $IntegerNumber,
                TblInvoice::ATTR_YEAR           => $Year,
                TblInvoice::ATTR_MONTH          => $Month,
            ));
    }

    /**
     * @param string             $IntegerNumber
     * @param string             $Month
     * @param string             $Year
     * @param \DateTime          $TargetTime
     * @param \DateTime|null     $BillTime
     * @param TblPerson          $tblPerson
     * @param TblInvoiceCreditor $tblInvoiceCreditor
     * @param TblBasket          $tblBasket
     *
     * @return object|TblInvoice|null
     */
    public function createInvoice(
        $IntegerNumber,
        $Month,
        $Year,
        $TargetTime,
        $BillTime,
        TblPerson $tblPerson,
        TblInvoiceCreditor $tblInvoiceCreditor,
        TblBasket $tblBasket
    ){

        $Manager = $this->getConnection()->getEntityManager();
        //ToDO $InvoiceNumberLength from Setting?
        $InvoiceNumberLength = 5;
        $InvoiceNumber = $Year.str_pad($Month, 2, '0', STR_PAD_LEFT).str_pad($IntegerNumber,
                $InvoiceNumberLength, '0', STR_PAD_LEFT);

        $Entity = $Manager->getEntity('TblInvoice')->findOneBy(
            array(TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber));

        if($Entity === null){
            $Entity = new TblInvoice();
            $Entity->setInvoiceNumber($InvoiceNumber);
            $Entity->setIntegerNumber($IntegerNumber);
            $Entity->setMonth($Month);
            $Entity->setYear($Year);
            $Entity->setTargetTime($TargetTime);
            $Entity->setBillTime($BillTime);
            $Entity->setFirstName($tblPerson->getFirstName());
            $Entity->setLastName($tblPerson->getLastName());
            $Entity->setServiceTblPersonCauser($tblPerson);
            $Entity->setTblInvoiceCreditor($tblInvoiceCreditor);
            $Entity->setServiceTblBasket($tblBasket);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param array          $InvoiceList
     * @param string         $Month
     * @param string         $Year
     * @param \DateTime      $TargetTime
     * @param \DateTime|null $BillTime
     * @param TblBasket      $tblBasket
     *
     * @return bool
     */
    public function createInvoiceList($InvoiceList, $Month, $Year, $TargetTime, $BillTime, TblBasket $tblBasket)
    {
        //ToDO $InvoiceNumberLength from Setting?
        $InvoiceNumberLength = 5;
        if(!empty($InvoiceList)){
            $Manager = $this->getConnection()->getEntityManager();
            foreach($InvoiceList as $Content) {
                $IntegerNumber = $Content['Identifier'];
                $InvoiceNumber = $Year.str_pad($Month, 2, '0', STR_PAD_LEFT).str_pad($IntegerNumber,
                        $InvoiceNumberLength, '0', STR_PAD_LEFT);
                /** @var TblPerson $tblPerson */
                $tblPerson = $Content['servicePersonCauser'];
                /** @var TblInvoiceCreditor $tblInvoiceCreditor */
                $tblInvoiceCreditor = $Content['InvoiceCreditor'];
                $Entity = $Manager->getEntity('TblInvoice')->findOneBy(
                    array(TblInvoice::ATTR_INVOICE_NUMBER => $InvoiceNumber));

                if($Entity === null){
                    $Entity = new TblInvoice();
                    $Entity->setInvoiceNumber($InvoiceNumber);
                    $Entity->setIntegerNumber($IntegerNumber);
                    $Entity->setMonth($Month);
                    $Entity->setYear($Year);
                    $Entity->setTargetTime($TargetTime);
                    $Entity->setBillTime($BillTime);
                    $Entity->setFirstName($tblPerson->getFirstName());
                    $Entity->setLastName($tblPerson->getLastName());
                    $Entity->setBasketName($tblBasket->getName());
                    $Entity->setServiceTblPersonCauser($tblPerson);
                    $Entity->setTblInvoiceCreditor($tblInvoiceCreditor);
                    $Entity->setServiceTblBasket($tblBasket);

                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                        $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param $InvoiceCauserList
     *
     * @return bool
     */
    public function createInvoiceItemDebtorList($InvoiceCauserList)
    {

        if(!empty($InvoiceCauserList)){
            $Manager = $this->getConnection()->getEntityManager();
            foreach($InvoiceCauserList as $ItemList) {
                foreach($ItemList as $Item) {
                    /** @var TblInvoice $tblInvoice */
                    $tblInvoice = $Item['Invoice'];
                    /** @var TblPerson $tblPerson */
                    $tblPerson = $Item['serviceTblPersonDebtor'];
                    /** @var TblBankAccount $tblBankAccount */
                    $tblBankAccount = $Item['serviceTblBankAccount'];
                    /** @var TblBankReference $tblBankReference */
                    $tblBankReference = $Item['serviceTblBankReference'];
                    /** @var TblPaymentType $tblPaymentType */
                    $tblPaymentType = $Item['serviceTblPaymentType'];
                    /** @var TblItem $tblItem */
                    $tblItem = $Item['TblItem'];
                    $DebtorNumber = $Item['DebtorNumber'];
                    $PersonDebtor = $tblPerson;
                    $BankReference = $Item['BankReference'];
                    $Owner = $Item['Owner'];
                    $BankName = $Item['BankName'];
                    $IBAN = $Item['IBAN'];
                    $BIC = $Item['BIC'];
                    $Name = $Item['Name'];
                    $Description = $Item['Description'];
                    $Value = $Item['Value'];
                    $Quantity = $Item['Quantity'];
                    $IsPaid = $Item['IsPaid'];

                    $Entity = $Manager->getEntity('TblInvoiceItemDebtor')->findOneBy(
                        array(
                            TblInvoiceItemDebtor::ATTR_TBL_INVOICE                   => $tblInvoice->getId(),
                            TblInvoiceItemDebtor::ATTR_NAME                          => $Name,
                            TblInvoiceItemDebtor::ATTR_QUANTITY                      => $Quantity,
                            TblInvoiceItemDebtor::ATTR_VALUE                         => $Value,
                            TblInvoiceItemDebtor::ATTR_SERVICE_TBL_PERSON_DEBTOR     => $tblPerson->getId(),
                            TblInvoiceItemDebtor::ATTR_SERVICE_TBL_PAYMENT_TYPE      => $tblPaymentType->getId(),
                            TblInvoiceItemDebtor::ATTR_SERVICE_TBL_BANKING_REFERENCE => ($tblBankReference ? $tblBankReference->getId() : null),
                        ));

                    if($Entity === null){
                        $Entity = new TblInvoiceItemDebtor();
                        $Entity->setName($Name);
                        $Entity->setDescription($Description);
                        $Entity->setQuantity($Quantity);
                        $Entity->setValue($Value);
                        $Entity->setDebtorNumber($DebtorNumber);
                        $Entity->setDebtorPerson($PersonDebtor);
                        $Entity->setBankReference($BankReference);
                        $Entity->setOwner($Owner);
                        $Entity->setBankName($BankName);
                        $Entity->setIBAN($IBAN);
                        $Entity->setBIC($BIC);
                        $Entity->setIsPaid($IsPaid);
                        $Entity->setServiceTblItem($tblItem);
                        $Entity->setServiceTblPersonDebtor($tblPerson);
                        $Entity->setServiceTblBankAccount($tblBankAccount);
                        $Entity->setServiceTblBankReference($tblBankReference);
                        $Entity->setServiceTblPaymentType($tblPaymentType);
                        $Entity->setTblInvoice($tblInvoice);

                        $Manager->bulkSaveEntity($Entity);
                        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                            $Entity, true);
                    }
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param array $InvoiceCreditor
     *
     * @return TblInvoiceCreditor|null
     */
    public function createInvoiceCreditorList($InvoiceCreditor = array())
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCreditor $tblCreditor */
        $tblCreditor = $InvoiceCreditor['serviceTblCreditor'];
        $CreditorId = $InvoiceCreditor['CreditorId'];
        $Owner = $InvoiceCreditor['Owner'];
        $BankName = $InvoiceCreditor['BankName'];
        $IBAN = $InvoiceCreditor['IBAN'];
        $BIC = $InvoiceCreditor['BIC'];
        $Entity = $Manager->getEntity('TblInvoiceCreditor')->findOneBy(
            array(
                TblInvoiceCreditor::ATTR_CREDITOR_ID => $CreditorId,
                TblInvoiceCreditor::ATTR_OWNER       => $Owner,
                TblInvoiceCreditor::ATTR_BANK_NAME   => $BankName,
                TblInvoiceCreditor::ATTR_IBAN        => $IBAN,
                TblInvoiceCreditor::ATTR_BIC         => $BIC,
            ));

        if($Entity === null){
            $Entity = new TblInvoiceCreditor();
            $Entity->setCreditorId($CreditorId);
            $Entity->setOwner($Owner);
            $Entity->setBankName($BankName);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);
            $Entity->setServiceTblCreditor($tblCreditor);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblInvoiceItemDebtor $tblInvoiceItemDebtor
     * @param bool                 $isPaid
     *
     * @return bool
     */
    public function changeInvoiceItemDebtorIsPaid(TblInvoiceItemDebtor $tblInvoiceItemDebtor, $isPaid = true)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblInvoiceItemDebtor $Entity */
        $Entity = $Manager->getEntityById('TblInvoiceItemDebtor', $tblInvoiceItemDebtor->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setIsPaid($isPaid);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }
}
