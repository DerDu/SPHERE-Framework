<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\SchoolAccount\SchoolAccount;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        return (new Data($this->getBinding()))->getInvoiceAll();
    }

    /**
     * @param bool $Check
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByIsPaid($Check = true)
    {

        return (new Data($this->getBinding()))->getInvoiceByIsPaid($Check);
    }

    /**
     * @param bool $Check
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByIsReversal($Check = true)
    {

        return (new Data($this->getBinding()))->getInvoiceByIsReversal($Check);
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param bool       $IsPaid
     *
     * @return bool
     */
    public function changeInvoiceIsPaid(TblInvoice $tblInvoice, $IsPaid = true)
    {

        return (new Data($this->getBinding()))->changeInvoiceIsPaid($tblInvoice, $IsPaid);
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param bool       $IsReversal
     *
     * @return bool
     */
    public function changeInvoiceIsReversal(TblInvoice $tblInvoice, $IsReversal = true)
    {

        return (new Data($this->getBinding()))->changeInvoiceIsReversal($tblInvoice, $IsReversal);
    }

    /**
     * @param $Id
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblItem
     */
    public function getItemById($Id)
    {

        return (new Data($this->getBinding()))->getItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorById($Id);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblInvoiceItem[]
     */
    public function getInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getInvoiceItemAllByInvoice($tblInvoice);
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

        return (new Data($this->getBinding()))->getInvoiceAllByPerson($tblPerson, $IsPaid, $IsReversal);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblItem[]
     */
    public function getItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getItemAllByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblPerson[]
     */
    public function getPersonAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getPersonAllByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|TblDebtor[]
     */
    public function getDebtorAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getDebtorAllByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     *
     * @return bool|TblPerson
     */
    public function getPersonByInvoiceAndItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getPersonByInvoiceAndItem($tblInvoice, $tblItem);
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblPerson  $tblPerson
     *
     * @return bool|TblPerson
     */
    public function getItemAllInvoiceAndPerson(TblInvoice $tblInvoice, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getItemAllInvoiceAndPerson($tblInvoice, $tblPerson);
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     *
     * @return bool|TblDebtor
     */
    public function getDebtorByInvoiceAndItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
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

        return (new Data($this->getBinding()))->getInvoiceAllByDate($From, $To, $Status);
    }

    /**
     * @param \DateTime $Date
     *
     * @return bool|Service\Entity\TblInvoice[]
     */
    public function getInvoiceAllByYearAndMonth(\DateTime $Date)
    {

        return (new Data($this->getBinding()))->getInvoiceAllByYearAndMonth($Date);
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getPriceString($value)
    {

        $result = number_format($value, 2, ',', '.');
        $result .= ' €';
        return $result;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return int
     */
    public function getInvoicePrice(TblInvoice $tblInvoice)
    {

        $result = 0;
        $tblItemList = Invoice::useService()->getItemAllByInvoice($tblInvoice);
        if ($tblItemList) {
            foreach ($tblItemList as $tblItem) {
                $result += $tblItem->getValue() * $tblItem->getQuantity();
            }
        }
        return $result;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|string
     */
    public function getInvoiceItemsString(TblInvoice $tblInvoice)
    {

        $ItemList = array();
        $tblItemList = Invoice::useService()->getItemAllByInvoice($tblInvoice);
        if ($tblItemList) {
            foreach ($tblItemList as $tblItem) {
                $Item = $tblItem->getServiceTblItem();
                if($Item){
                    $ItemList[] = $Item->getName();
                }
            }
            $ItemList = array_unique($ItemList);
        }
        $ItemString = implode(' ,', $ItemList);

        return (empty($ItemList) ? false : $ItemString);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return array|bool
     */
    public function reviewInvoiceData(TblBasket $tblBasket)
    {
        /** Shopping Content */
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerificationList) {
            return false;
        }
        $Invoice = array();
        $ItemCount = 0;
        foreach ($tblBasketVerificationList as $tblBasketVerification) {
            $tblPerson = $tblBasketVerification->getServiceTblPerson();
            $tblItem = $tblBasketVerification->getServiceTblItem();
            $Quantity = $tblBasketVerification->getQuantity();
            $Value = $tblBasketVerification->getValue();
            $Price = $tblBasketVerification->getPrice();
            $PriceSum = $tblBasketVerification->getSummaryPrice();

            /** search Customer */
            $tblDebtorSelect = Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
            if ($tblDebtorSelect) {
                $tblBankReference = $tblDebtorSelect->getTblBankReference();
                $tblDebtor = $tblDebtorSelect->getTblDebtor();

                if ($tblDebtorSelect->getServiceTblPaymentType()->getName() == 'SEPA-Lastschrift') {
                    $tblPaymentType = $tblDebtorSelect->getServiceTblPaymentType();
                } else {
                    $tblPaymentType = $tblDebtorSelect->getServiceTblPaymentType();
                }
                if ($tblBankReference && $tblDebtor) {
                    /** separator for Invoice */
                    $seperator = $tblDebtor->getId();
                    /** fill Invoice */
                    $Invoice[$seperator][$ItemCount]['PersonFrom'] = $tblPerson->getFullName();
                    $Invoice[$seperator][$ItemCount]['PersonFromId'] = $tblPerson->getId();
                    $Invoice[$seperator][$ItemCount]['PersonTo'] = $tblDebtor->getServiceTblPerson()->getFullName();
                    $Invoice[$seperator][$ItemCount]['PaymentType'] = $tblPaymentType->getName();
                    $Invoice[$seperator][$ItemCount]['Item'] = $tblBasketVerification->getServiceTblItem()->getName();
                    $Invoice[$seperator][$ItemCount]['Quantity'] = $Quantity;
                    $Invoice[$seperator][$ItemCount]['Price'] = $Price;
                    $Invoice[$seperator][$ItemCount]['PriceSum'] = $PriceSum;
                    $Invoice[$seperator][$ItemCount]['Value'] = $Value;
                    $Invoice[$seperator][$ItemCount]['Reference'] = $tblBankReference->getReference();
                    $Invoice[$seperator][$ItemCount]['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                    $ItemCount++;
                } elseif ($tblDebtor) {
                    /** separator for Invoice */
                    $seperator = $tblDebtor->getId();
                    /** fill Invoice */
                    $Invoice[$seperator][$ItemCount]['PersonFrom'] = $tblPerson->getFullName();
                    $Invoice[$seperator][$ItemCount]['PersonFromId'] = $tblPerson->getId();
                    $Invoice[$seperator][$ItemCount]['PersonTo'] = $tblDebtor->getServiceTblPerson()->getFullName();
                    $Invoice[$seperator][$ItemCount]['PaymentType'] = $tblPaymentType->getName();
                    $Invoice[$seperator][$ItemCount]['Item'] = $tblBasketVerification->getServiceTblItem()->getName();
                    $Invoice[$seperator][$ItemCount]['Quantity'] = $Quantity;
                    $Invoice[$seperator][$ItemCount]['Price'] = $Price;
                    $Invoice[$seperator][$ItemCount]['PriceSum'] = $PriceSum;
                    $Invoice[$seperator][$ItemCount]['Value'] = $Value;
                    $Invoice[$seperator][$ItemCount]['Reference'] = 'Bar/Überweisung';
                    $Invoice[$seperator][$ItemCount]['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                    $ItemCount++;
                }
            }
        }

        return $Invoice;
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $Date
     * @param null      $SchoolAccount
     *
     * @return bool
     */
    public function createInvoice(TblBasket $tblBasket, $Date, $SchoolAccount = null)
    {
        /** Shopping Content */
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerificationList) {
            return false;
        }
        $DebtorItemList = array();
        foreach ($tblBasketVerificationList as $tblBasketVerification) {


            $tblPerson = $tblBasketVerification->getServiceTblPerson();
            $tblItem = $tblBasketVerification->getServiceTblItem();
            $Quantity = $tblBasketVerification->getQuantity();
            $Price = $tblBasketVerification->getValue();

            /** search Customer */
            $tblDebtorSelect = Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
            if ($tblDebtorSelect) {
                $tblBankReference = $tblDebtorSelect->getTblBankReference();
                $tblDebtor = $tblDebtorSelect->getTblDebtor();
                $tblPaymentType = $tblDebtorSelect->getServiceTblPaymentType();

                if ($tblBankReference && $tblDebtor) {
                    /** fill Invoice/tblDebtor */
                    $DebtorId = $tblDebtor->getId();
                    /** fill Invoice/tblItem */
                    $DebtorItemList[$DebtorId]['DebtorInvoice'][] = (new Data($this->getBinding()))->createDebtor($tblDebtor, $tblPaymentType, $tblBankReference)->getId();
                    $DebtorItemList[$DebtorId]['Item'][] = (new Data($this->getBinding()))->createItem($tblBasketVerification)->getId();
                    $DebtorItemList[$DebtorId]['Quantity'][] = $Quantity;
                    $DebtorItemList[$DebtorId]['Value'][] = $Price;
                    $DebtorItemList[$DebtorId]['PersonId'][] = $tblPerson->getId();
                } elseif ($tblDebtor) {
                    /** fill Invoice/tblDebtor */
                    $DebtorId = $tblDebtor->getId();
                    /** fill Invoice/tblItem */
                    $DebtorItemList[$DebtorId]['DebtorInvoice'][] = (new Data($this->getBinding()))->createDebtor($tblDebtor, $tblPaymentType, null)->getId();
                    $DebtorItemList[$DebtorId]['Item'][] = (new Data($this->getBinding()))->createItem($tblBasketVerification)->getId();
                    $DebtorItemList[$DebtorId]['Quantity'][] = $Quantity;
                    $DebtorItemList[$DebtorId]['Value'][] = $Price;
                    $DebtorItemList[$DebtorId]['PersonId'][] = $tblPerson->getId();
                }
            }
        }

        $tblInvoiceList = Invoice::useService()->getInvoiceAllByYearAndMonth((new \DateTime($Date)));
        $date = (new \DateTime($Date))->format('ym');
        if (empty( $tblInvoiceList )) {
            $count = 1;
        } else {
            $count = count($tblInvoiceList) + 1;
        }
        /** fill Invoice/tblInvoice */
        foreach ($DebtorItemList as $DebtorId => $InvoiceList) {

            $countString = $date.'_'.str_pad($count, 5, 0, STR_PAD_LEFT);
            $InsertArray = array();
            foreach ($InvoiceList['DebtorInvoice'] as $DebtorInvoiceId) {
                $tblDebtor = Invoice::useService()->getDebtorById($DebtorInvoiceId);
                $InsertArray['tblPersonDebtor'] = $tblDebtor->getServiceTblDebtor()->getServiceTblPerson();
                if ($InsertArray['tblPersonDebtor']) {

                    //get Address
                    $InsertArray['tblAddress'] = Address::useService()->getAddressByPerson($InsertArray['tblPersonDebtor']);
                    if (empty( $InsertArray['tblAddress'] )) {
                        $InsertArray['tblAddress'] = null;
                    }
                    //get Mail
                    $InsertArray['tblMail'] = null;
                    $InsertArray['tblMail'] = Mail::useService()->getMailAllByPerson($InsertArray['tblPersonDebtor']);
                    if (!empty( $InsertArray['tblMail'] )) {
                        $InsertArray['tblMail'] = $InsertArray['tblMail'][0]->getTblMail();
                    }
                    //get Phone
                    $InsertArray['tblPhone'] = null;
                    $InsertArray['tblPhone'] = Phone::useService()->getPhoneAllByPerson($InsertArray['tblPersonDebtor']);
                    if (!empty( $InsertArray['tblPhone'] )) {
                        $InsertArray['tblPhone'] = $InsertArray['tblPhone'][0]->getTblPhone();
                    }
                }
            }

            $tblPerson = Person::useService()->getPersonById($InvoiceList['PersonId'][0]);
            $tblSchoolAccount = false;
            if ($tblPerson) {
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblType = $tblStudentTransfer->getServiceTblType();
                            $tblCompany = $tblStudentTransfer->getServiceTblCompany();
                            if ($tblType && $tblCompany) {
                                $tblSchoolAccount = SchoolAccount::useService()->getSchoolAccountByCompanyAndType($tblCompany, $tblType);
                            }
                        }
                    }
                }
            }
            if ($tblSchoolAccount) {
                $InsertArray['tblSchoolAccount'] = $tblSchoolAccount;
            } else {
//                $tblSchoolAccount = SchoolAccount::useService()->getSchoolAccountAll();
//                if(!empty($tblSchoolAccount)){
//                    $InsertArray['tblSchoolAccount'] = $tblSchoolAccount[0];
//                } else {
                $tblSchoolAccount = SchoolAccount::useService()->getSchoolAccountById($SchoolAccount);
                $InsertArray['tblSchoolAccount'] = $tblSchoolAccount;
//                }
            }

            $InvoiceId = (new Data($this->getBinding()))->createInvoice($InsertArray['tblPersonDebtor'], $InsertArray['tblSchoolAccount'], $countString, $Date,
                $InsertArray['tblAddress'],
                ( empty( $InsertArray['tblMail'] ) ? null : $InsertArray['tblMail'] ),
                ( empty( $InsertArray['tblPhone'] ) ? null : $InsertArray['tblPhone'] ))->getId();
            $DebtorItemList[$DebtorId]['InvoiceId'] = $InvoiceId;

            $count++;
        }

        /** fill Invoice/tblInvoiceItem */
        foreach ($DebtorItemList as $InvoiceList) {
            $tblInvoice = Invoice::useService()->getInvoiceById($InvoiceList['InvoiceId']);

            foreach ($InvoiceList['Item'] as $key => $Item) {

                $tblPerson = Person::useService()->getPersonById($InvoiceList['PersonId'][$key]);
                $tblDebtor = Invoice::useService()->getDebtorById($InvoiceList['DebtorInvoice'][$key]);
                if ($tblPerson && $tblDebtor) {
                    $tblItem = Invoice::useService()->getItemById($Item);
                    (new Data($this->getBinding()))->createInvoiceItem($tblInvoice, $tblItem, $tblPerson, $tblDebtor);
                }
            }
        }

        // Warenkorb leeren
        foreach ($tblBasketVerificationList as $tblBasketVerification) {
            Basket::useService()->destroyBasketVerification($tblBasketVerification);
        }

        return true;
    }
}
