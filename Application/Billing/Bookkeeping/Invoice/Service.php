<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Api\Billing\Invoice\ApiInvoiceIsPaid;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
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
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
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
     * @param TblPerson $tblPerson
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByPersonCauser(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getInvoiceByPersonCauser($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $Year
     * @param string    $Month
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByPersonCauserAndTime(TblPerson $tblPerson, $Year = '', $Month = '')
    {

        return (new Data($this->getBinding()))->getInvoiceAllByPersonCauserAndTime($tblPerson, $Year, $Month);
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     * @param string    $Year
     * @param string    $Month
     *
     * @return TblInvoice[]|bool
     */
    public function getInvoiceByPersonCauserAndItemAndYearAndMonth(
        TblBasket $tblBasket,
        TblPerson $tblPerson,
        TblItem $tblItem,
        $Year,
        $Month
    ){
        $isInvoice = false;
        // Abrechnung als Default
        $BasketTypeId = 1;
        if(($tblBasketType = $tblBasket->getTblBasketType())){
            $BasketTypeId = $tblBasketType->getId();
        }
        if(($tblInvoiceList = $this->getInvoiceAllByPersonCauserAndTime($tblPerson, $Year, $Month))){
            foreach($tblInvoiceList as $tblInvoice) {
                // wird der gleiche Abrechnungstyp gesucht?
                $IsSameType = false;
                if(($tempTblBasket = $tblInvoice->getServiceTblBasket())){
                    if(($tempTblBasketType = $tempTblBasket->getTblBasketType())){
                        if($BasketTypeId == $tempTblBasketType->getId()){
                            $IsSameType = true;
                        }
                    }
                }
                // Doppelte Invoice mit gleichem Abrechnungstyp
                if($IsSameType && ($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                        if(($tblInvoiceItem = $tblInvoiceItemDebtor->getServiceTblItem())){
                            if($tblInvoiceItem->getId() == $tblItem->getId()){
                                $isInvoice = true;
                            }
                        }
                    }
                }
            }
        }

        return $isInvoice;
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getInvoiceItemDebtorByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblItem    $tblItem
     *
     * @return TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByInvoiceAndItem(TblInvoice $tblInvoice, TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getInvoiceItemDebtorByInvoiceAndItem($tblInvoice, $tblItem);
    }

    /**
     * @param TblInvoiceItemDebtor $tblInvoiceItemDebtor
     * @param bool                 $IsPaid
     *
     * @return bool
     */
    public function changeInvoiceItemDebtorIsPaid(TblInvoiceItemDebtor $tblInvoiceItemDebtor, $IsPaid = false)
    {

        return (new Data($this->getBinding()))->changeInvoiceItemDebtorIsPaid($tblInvoiceItemDebtor, $IsPaid);
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
     * @return bool|TblInvoice
     */
    public function getInvoiceCreditorById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceCreditorById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblInvoiceItemDebtor
     */
    public function getInvoiceItemDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceItemDebtorById($Id);
    }

    /**
     * @param bool $IsPaid
     *
     * @return false|TblInvoiceItemDebtor[]
     */
    public function getInvoiceItemDebtorByIsPaid($IsPaid = false)
    {

        return (new Data($this->getBinding()))->getInvoiceItemDebtorByIsPaid($IsPaid);
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

        return (new Data($this->getBinding()))->getInvoiceByYearAndMonth($Year, $Month, $BasketName);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getInvoiceByBasket($tblBasket);
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

        return (new Data($this->getBinding()))->getInvoiceByIntegerAndYearAndMonth($IntegerNumber, $Year, $Month);
    }

    /**
     * @param $Year
     * @param $Month
     *
     * @return int
     */
    public function getMaxInvoiceNumberByYearAndMonth($Year, $Month)
    {

        return (new Data($this->getBinding()))->getMaxInvoiceNumberByYearAndMonth($Year, $Month);
    }

    /**
     * @param int $YearsAgo
     * @param int $YearFuture
     *
     * @return array
     */
    public function getYearList($YearsAgo = 3, $YearFuture = 1)
    {

        $Now = new \DateTime();
        $Year = $Now->format('Y');
        $YearList = array();
        for($i = $YearsAgo; $i > 0; $i--) {
            $YearList[(int)$Year - $i] = (int)$Year - $i;
        }
        for($j = 0; $j <= $YearFuture; $j++) {
            $YearList[(int)$Year + $j] = (int)$Year + $j;
        }


        return $YearList;
    }

    /**
     * @param null $From
     * @param null $To
     *
     * @return array
     */
    public function getMonthList($From = null, $To = null)
    {

        $MonthList[1] = 'Januar';
        $MonthList[2] = 'Februar';
        $MonthList[3] = 'März';
        $MonthList[4] = 'April';
        $MonthList[5] = 'Mai';
        $MonthList[6] = 'Juni';
        $MonthList[7] = 'Juli';
        $MonthList[8] = 'August';
        $MonthList[9] = 'September';
        $MonthList[10] = 'Oktober';
        $MonthList[11] = 'November';
        $MonthList[12] = 'Dezember';
        // Zeitraum eingrenzen
        if($From !== null & $To !== null){
            foreach($MonthList as $Key => &$Month) {
                if($Key < $From || $Key > $To){
                    $Month = false;
                }
            }
            $MonthList = array_filter($MonthList);
        }

        return $MonthList;
    }

    /**
     * @param null|int $Count
     *
     * @return string
     */
    public function getMonthName($Count = null)
    {

        $Month = '';
        $MonthList[1] = 'Januar';
        $MonthList[2] = 'Februar';
        $MonthList[3] = 'März';
        $MonthList[4] = 'April';
        $MonthList[5] = 'Mai';
        $MonthList[6] = 'Juni';
        $MonthList[7] = 'Juli';
        $MonthList[8] = 'August';
        $MonthList[9] = 'September';
        $MonthList[10] = 'Oktober';
        $MonthList[11] = 'November';
        $MonthList[12] = 'Dezember';
        if(isset($MonthList[(int)$Count])){
            $Month = $MonthList[(int)$Count];
        }
        return $Month;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|Redirect
     */
    public function createInvoice(TblBasket $tblBasket)
    {
        /** Shopping Content */
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket);
        if(!$tblBasketVerificationList){
            return false;
        }

        $DebtorInvoiceList = array();
        $Month = $tblBasket->getMonth();
        $Year = $tblBasket->getYear();
        if(($TargetTime = $tblBasket->getTargetTime())){
            $TargetTime = new \DateTime($TargetTime);
        } else {
            $TargetTime = null;
        }
        if(($BillTime = $tblBasket->getBillTime())){
            $BillTime = new \DateTime($BillTime);
        } else {
            $BillTime = null;
        }
        $MaxInvoiceNumber = Invoice::useService()->getMaxInvoiceNumberByYearAndMonth($Year, $Month);

        // erste Durchsicht, entfernnen vorhandener Rechnungen (gleiche Rechnungen im Abrechnungszeitraum
        // entfernen aller DebtorSelection zu welchen es schon in der aktuellen Rechnungsphase Rechnungen gibt
        array_walk($tblBasketVerificationList,
            function(TblBasketVerification &$tblBasketVerification) use ($Month, $Year, $tblBasket){
                $tblPerson = $tblBasketVerification->getServiceTblPersonCauser();
                $tblItem = $tblBasketVerification->getServiceTblItem();

                if($tblBasketVerification->getValue() === '0.0000'){
                    // entfernen der Beiträge mit 0€
                    $tblBasketVerification = false;
                } elseif($tblBasketVerification->getQuantity() == 0) {
                    // entfernen der Beiträge mit Anzahl 0
                    $tblBasketVerification = false;
                } elseif($tblPerson && $tblItem) { // Entfernen aller Beiträge
                    if(Invoice::useService()->getInvoiceByPersonCauserAndItemAndYearAndMonth($tblBasket, $tblPerson, $tblItem,
                        $Year, $Month)){
                        // Entfernen des Beitrag's aus der Abrechnung
                        Basket::useService()->destroyBasketVerification($tblBasketVerification);
                        // Rechnung vorhanden -> keine neue Rechnung anlegen!
                        $tblBasketVerification = false;
                    }
                }
            });
        /** @var TblBasketVerification[] $tblBasketVerificationList */
        $tblBasketVerificationList = array_filter($tblBasketVerificationList);

        /** fill Invoice/Creditor */
        if(($tblCreditor = $tblBasket->getServiceTblCreditor())){
            $CreditorId = $tblCreditor->getCreditorId();
            $Owner = $tblCreditor->getOwner();
            $BankName = $tblCreditor->getBankName();
            $IBAN = $tblCreditor->getIBAN();
            $BIC = $tblCreditor->getBIC();
        } else {
            $tblCreditor = null;
            $CreditorId = $Owner = $BankName = $IBAN = $BIC = '';
        }
        $InvoiceCreditorList['CreditorId'] = $CreditorId;
        $InvoiceCreditorList['Owner'] = $Owner;
        $InvoiceCreditorList['BankName'] = $BankName;
        $InvoiceCreditorList['IBAN'] = $IBAN;
        $InvoiceCreditorList['BIC'] = $BIC;
        $InvoiceCreditorList['serviceTblCreditor'] = $tblCreditor;

        $tblInvoiceCreditor = (new Data($this->getBinding()))->createInvoiceCreditorList($InvoiceCreditorList);

        // Vorbereiten aller Rechnugen
        array_walk($tblBasketVerificationList,
            function(TblBasketVerification $tblBasketVerification) use (
                &$MaxInvoiceNumber,
                &$DebtorInvoiceList,
                $tblInvoiceCreditor
            ){
                $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
                $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
                /** fill Invoice (groupByDebtorAndCauser) */
                $PersonDebtorId = $tblPersonDebtor->getId();
                $PersonCauserId = $tblPersonCauser->getId();

                $tblReferenceId = '';
                if($tblBankReference = $tblBasketVerification->getServiceTblBankReference()){
                    $tblReferenceId = $tblBankReference->getId();
                }

                $GroupString = $PersonDebtorId.'#'.$PersonCauserId.'#'.$tblReferenceId;
                /** Fill InvoiceList */
                if(!isset($DebtorInvoiceList[$GroupString])){
                    $MaxInvoiceNumber++;
                    $DebtorInvoiceList[$GroupString]['Identifier'] = $MaxInvoiceNumber;
                    $DebtorInvoiceList[$GroupString]['servicePersonCauser'] = $tblPersonCauser;
                    $DebtorInvoiceList[$GroupString]['InvoiceCreditor'] = $tblInvoiceCreditor;
                }
            });

        if(!empty($DebtorInvoiceList)){
            // Erstellen aller Rechnungen im Bulk
            (new Data($this->getBinding()))->createInvoiceList($DebtorInvoiceList, $Month, $Year, $TargetTime, $BillTime,
                $tblBasket);
        }

        $InvoiceItemDebtorList = array();
        $i = 0;
        array_walk($tblBasketVerificationList, function(TblBasketVerification $tblBasketVerification) use (
            &$i,
            &$InvoiceItemDebtorList,
            $Month,
            $Year,
            $DebtorInvoiceList,
            $tblBasket
        ){
            $i++;
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            /** get Invoice from BulkSave */
            $PersonDebtorId = $tblPersonDebtor->getId();
            $PersonCauserId = $tblPersonCauser->getId();
            $tblReferenceId = '';
            if($tblBankReference = $tblBasketVerification->getServiceTblBankReference()){
                $tblReferenceId = $tblBankReference->getId();
            }
            $GroupString = $PersonDebtorId.'#'.$PersonCauserId.'#'.$tblReferenceId;
            $tblInvoice = $this->getInvoiceByIntegerAndYearAndMonth($DebtorInvoiceList[$GroupString]['Identifier'],
                $Year, $Month);
            /** fill Invoice/ItemDebtor */
            if(($tblItem = $tblBasketVerification->getServiceTblItem())){
                $Name = $tblItem->getName();
                $Description = $tblItem->getDescription();
            } else {
                $tblItem = null;
                $Name = $Description = '';
            }
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            $tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor);
            if($tblDebtorNumberList && !empty($tblDebtorNumberList)){
                // ToDO mehrere Debitoren-Nr.?
                // erste Debtitor Nummer wird gezogen
                $tblDebtorNumber = current($tblDebtorNumberList);
                $DebtorNumber = $tblDebtorNumber->getDebtorNumber();
            } else {
                $DebtorNumber = '';
            }
            if(($tblBankReference = $tblBasketVerification->getServiceTblBankReference())){
                $Reference = $tblBankReference->getReferenceNumber();
            } else {
                $tblBankReference = null;
                $Reference = '';
            }
            if(($tblBankAccount = $tblBasketVerification->getServiceTblBankAccount())){
                $Owner = $tblBankAccount->getOwner();
                $BankName = $tblBankAccount->getBankName();
                $IBAN = $tblBankAccount->getIBAN();
                $BIC = $tblBankAccount->getBIC();
            } else {
                $tblBankAccount = null;
                $Owner = $BankName = $IBAN = $BIC = '';
            }
            if(!($tblPaymentType = $tblBasketVerification->getServiceTblPaymentType())){
                $tblPaymentType = null;
            }
            $InvoiceItemDebtorList[$GroupString][$i]['Invoice'] = $tblInvoice;
            $InvoiceItemDebtorList[$GroupString][$i]['Name'] = $Name;
            $InvoiceItemDebtorList[$GroupString][$i]['Description'] = $Description;
            $InvoiceItemDebtorList[$GroupString][$i]['Value'] = $tblBasketVerification->getValue();
            $InvoiceItemDebtorList[$GroupString][$i]['Quantity'] = $tblBasketVerification->getQuantity();
            $InvoiceItemDebtorList[$GroupString][$i]['Invoice'] = $tblInvoice;
            $InvoiceItemDebtorList[$GroupString][$i]['DebtorNumber'] = $DebtorNumber;
            $InvoiceItemDebtorList[$GroupString][$i]['BankReference'] = $Reference;
            $InvoiceItemDebtorList[$GroupString][$i]['Owner'] = $Owner;
            $InvoiceItemDebtorList[$GroupString][$i]['BankName'] = $BankName;
            $InvoiceItemDebtorList[$GroupString][$i]['IBAN'] = $IBAN;
            $InvoiceItemDebtorList[$GroupString][$i]['BIC'] = $BIC;
            $InvoiceItemDebtorList[$GroupString][$i]['TblItem'] = $tblItem;
            $InvoiceItemDebtorList[$GroupString][$i]['serviceTblPersonDebtor'] = $tblPersonDebtor;
            $InvoiceItemDebtorList[$GroupString][$i]['serviceTblBankReference'] = $tblBankReference;
            $InvoiceItemDebtorList[$GroupString][$i]['serviceTblBankAccount'] = $tblBankAccount;
            $InvoiceItemDebtorList[$GroupString][$i]['serviceTblPaymentType'] = $tblPaymentType;

            // Gutschriften erhalten keine offene Posten
            if($tblBasket->getTblBasketType()->getName() == TblBasketType::IDENT_GUTSCHRIFT){
                $isPaid = true;
            } else {
                // Alles was nicht Sepa ist wird als offener Posten hinterlegt
                // (Erwarteter Zahlungseingang muss nach erhalt im Programm bestätigt weden, damit dies nicht untergeht)
                if($tblPaymentType && $tblPaymentType->getName() == 'SEPA-Lastschrift'){
                    $isPaid = true;
                } else {
                    $isPaid = false;
                }
            }


            $InvoiceItemDebtorList[$GroupString][$i]['IsPaid'] = $isPaid;
        });

        // create
        if(!empty($InvoiceItemDebtorList)){
            (new Data($this->getBinding()))->createInvoiceItemDebtorList($InvoiceItemDebtorList);
        }

        // Abrechnung leeren
//        foreach ($tblBasketVerificationList as $tblBasketVerification) {
//            Basket::useService()->destroyBasketVerification($tblBasketVerification);
//        }
        // Set Basket to Done
        Basket::useService()->changeBasketDone($tblBasket);

        return new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_SUCCESS);
//        $Invoice = array('Year' => $tblBasket->getYear(), 'Month' => $tblBasket->getMonth());
//        return new Redirect('/Billing/Bookkeeping/Invoice/View', Redirect::TIMEOUT_SUCCESS, array(
//            'Invoice' => $Invoice
//        ));
    }

    /**
     * @param $Year
     * @param $Month
     * @param $BasketName
     * @param $ItemName
     * @param $IsFrontend
     *
     * @return array
     */
    public function getInvoiceCauserList(
        $Year,
        $Month,
        $BasketName,
        $ItemName,
        $IsFrontend
    ) {
        $TableContent = array();
        if (($tblInvoiceList = $this->getInvoiceByYearAndMonth($Year, $Month, $BasketName))) {
            array_walk($tblInvoiceList, function(TblInvoice $tblInvoice) use (&$TableContent, $ItemName, $IsFrontend){
                $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $item['Time'] = $tblInvoice->getYear().'/'.$tblInvoice->getMonth(true);
                $item['TargetTime'] = $tblInvoice->getTargetTime();
                $item['BillTime'] = $tblInvoice->getBillTime();
                $item['BasketName'] = $tblInvoice->getBasketName();
                $item['CauserPerson'] = '';
                $item['CauserIdent'] = '';
                if($tblPersonCauser = $tblInvoice->getServiceTblPersonCauser()){
                    $item['CauserPerson'] = $tblPersonCauser->getLastFirstName();
                    if(($tblStudent = Student::useService()->getStudentByPerson($tblPersonCauser))){
                        $item['CauserIdent'] = $tblStudent->getIdentifierComplete();
                    }
                }
                $item['BasketType'] = '';
                if(($tblBasket = $tblInvoice->getServiceTblBasket())){
                    if(($tblBasketType = $tblBasket->getTblBasketType())){
                        $item['BasketType'] = $tblBasketType->getName();
                    }
                }

                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                        // Beitragsart filtern
                        if ($ItemName != ''
                            && strtolower($tblInvoiceItemDebtor->getName()) != strtolower($ItemName)
                        ) {
                            continue;
                        }

                        $item['DebtorPerson'] = '';
                        $item['Item'] = '';
                        $item['ItemQuantity'] = '';
                        $item['ItemPrice'] = 0;
                        $item['ItemSumPrice'] = 0;

                        $item['DebtorPerson'] = $tblInvoiceItemDebtor->getDebtorPerson();
                        $item['Item'] = $tblInvoiceItemDebtor->getName();
                        $item['ItemQuantity'] = $tblInvoiceItemDebtor->getQuantity();
                        $item['ItemPrice'] = $tblInvoiceItemDebtor->getValue();
                        $item['ItemSumPrice'] = $tblInvoiceItemDebtor->getQuantity() * $tblInvoiceItemDebtor->getValue();
                        $item['PaymentType'] = $tblInvoiceItemDebtor->getServiceTblPaymentType()
                            ? $tblInvoiceItemDebtor->getServiceTblPaymentType()->getName() : '';

                        if($IsFrontend) {
                            $CheckBox = (new CheckBox('IsPaid', ' ',
                                $tblInvoiceItemDebtor->getId()))->ajaxPipelineOnClick(
                                ApiInvoiceIsPaid::pipelineChangeIsPaid($tblInvoiceItemDebtor->getId()));
                            if (!$tblInvoiceItemDebtor->getIsPaid()) {
                                $CheckBox->setChecked();
                            }

                            $item['IsPaid'] = ApiInvoiceIsPaid::receiverIsPaid($CheckBox,
                                $tblInvoiceItemDebtor->getId());
                        }

//                        $item['Option'] = '';
                        // convert to Frontend
                        $item['ItemPrice'] = str_replace('.',',', number_format($item['ItemPrice'], 2) . ($IsFrontend ? '&nbsp;€' : ''));
                        $item['ItemSumPrice'] = str_replace('.',',', number_format($item['ItemSumPrice'], 2) . ($IsFrontend ? '&nbsp;€' : ''));
                        array_push($TableContent, $item);
                    }
                }
            });
        }

        return $TableContent;
    }

    /**
     * @param $Year
     * @param $Month
     * @param $BasketName
     * @param $ItemName
     *
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createInvoiceCauserListExcel(
        $Year,
        $Month,
        $BasketName,
        $ItemName
    ) {
        $resultList =  $this->getInvoiceCauserList($Year, $Month, $BasketName, $ItemName, false);
        if(!empty($resultList)){
            // nach Beitragsverursacher sortieren
            foreach ($resultList as $key => $value) {
                $causerName[$key] = strtoupper($value['CauserPerson']);
            }
            array_multisort($causerName, SORT_NATURAL, $resultList);

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;

            $export->setValue($export->getCell($column++, $row), 'Beitragsverursacher');
            $export->setValue($export->getCell($column++, $row), 'Schülernummer');
            $export->setValue($export->getCell($column++, $row), 'Beitragsarten');
            $export->setValue($export->getCell($column++, $row), 'Beitragszahler');
            $export->setValue($export->getCell($column++, $row), 'Name der Abrechnung');
            $export->setValue($export->getCell($column++, $row), 'Abrechnungszeitraum');
            $export->setValue($export->getCell($column++, $row), 'Fällikeitsdatum');
            $export->setValue($export->getCell($column++, $row), 'Rechnungsdatum');
            $export->setValue($export->getCell($column++, $row), 'Rechnungsnummer');
            $export->setValue($export->getCell($column++, $row), 'Zahlungsart');
            $export->setValue($export->getCell($column++, $row), 'Menge');
            $export->setValue($export->getCell($column++, $row), 'Einzelpreis');
            $export->setValue($export->getCell($column++, $row), 'Gesamtpreis');
            $export->setValue($export->getCell($column, $row), 'Typ');

            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();
            foreach($resultList as $result) {
                $column = 0;
                $row++;

                $export->setValue($export->getCell($column++, $row), $result['CauserPerson']);
                $export->setValue($export->getCell($column++, $row), $result['CauserIdent']);
                $export->setValue($export->getCell($column++, $row), $result['Item']);
                $export->setValue($export->getCell($column++, $row), $result['DebtorPerson']);
                $export->setValue($export->getCell($column++, $row), $result['BasketName']);
                $export->setValue($export->getCell($column++, $row), $result['Time']);
                $export->setValue($export->getCell($column++, $row), $result['TargetTime']);
                $export->setValue($export->getCell($column++, $row), $result['BillTime']);
                $export->setValue($export->getCell($column++, $row), $result['InvoiceNumber']);
                $export->setValue($export->getCell($column++, $row), $result['PaymentType']);
                $export->setValue($export->getCell($column++, $row), $result['ItemQuantity']);
                $export->setValue($export->getCell($column++, $row), $result['ItemPrice']);
                $export->setValue($export->getCell($column++, $row), $result['ItemSumPrice']);
                $export->setValue($export->getCell($column, $row), $result['BasketType']);
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }

        return false;
    }

    /**
     * @param $Year
     * @param $Month
     * @param $BasketName
     * @param $IsFrontend
     *
     * @return array
     */
    public function getInvoiceDebtorList(
        $Year,
        $Month,
        $BasketName,
        $IsFrontend
    ) {
        $TableContent = array();
        if(($tblInvoiceList = $this->getInvoiceByYearAndMonth($Year, $Month, $BasketName))){
            array_walk($tblInvoiceList, function(TblInvoice $tblInvoice) use (&$TableContent, $IsFrontend){
                $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $item['Time'] = $tblInvoice->getYear().'/'.$tblInvoice->getMonth(true);
                $item['TargetTime'] = $tblInvoice->getTargetTime();
                $item['BillTime'] = $tblInvoice->getBillTime();
                $item['BasketName'] = $tblInvoice->getBasketName();
                $item['CauserPerson'] = '';
                $item['CauserIdent'] = '';
                if($tblPersonCauser = $tblInvoice->getServiceTblPersonCauser()){
                    $item['CauserPerson'] = $tblPersonCauser->getLastFirstName();
                    if(($tblStudent = Student::useService()->getStudentByPerson($tblPersonCauser))){
                        $item['CauserIdent'] = $tblStudent->getIdentifierComplete();
                    }
                }
                $item['DebtorPerson'] = '';
                $item['DebtorNumber'] = '';
                $item['BasketType'] = '';
                if(($tblBasket = $tblInvoice->getServiceTblBasket())){
                    if(($tblBasketType = $tblBasket->getTblBasketType())){
                        $item['BasketType'] = $tblBasketType->getName();
                    }
                }
                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                    $tblInvoiceItemDebtor = current($tblInvoiceItemDebtorList);
                    $item['DebtorPerson'] = $tblInvoiceItemDebtor->getDebtorPerson();
                    $item['DebtorNumber'] = $tblInvoiceItemDebtor->getDebtorNumber();
                    $ItemList = array();
                    $PaymentList = array();
                    $ItemPrice = 0;
                    $itemsForExcel = array();
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {
                        if (($tblPaymentType =$tblInvoiceItemDebtor->getServiceTblPaymentType())) {
                            $PaymentList[$tblPaymentType->getId()] = $tblPaymentType->getName();
                        }
                        $price = $tblInvoiceItemDebtor->getQuantity() * $tblInvoiceItemDebtor->getValue();
                        $ItemPrice += $price;
                        $ItemList[] = $tblInvoiceItemDebtor->getName() . ': '
                            . str_replace('.',',', number_format($price, 2)) . '&nbsp;€';
                        $itemsForExcel[] = array(
                            'Name' => $tblInvoiceItemDebtor->getName(),
                            'DisplayPrice' => str_replace('.',',', number_format($price, 2)),
                            'Price' => $price
                        );
                    }
                    $item['PaymentType'] = implode(', ', $PaymentList);
                    // convert to Frontend
                    $ItemString = implode(',<br>', $ItemList);
                    $item['DisplaySumPrice'] = str_replace('.',',', number_format($ItemPrice, 2))
                        . ($IsFrontend ? ' €&nbsp;&nbsp;&nbsp;' . (new ToolTip(new Info(), $ItemString))->enableHtml() : '');
                    $item['SumPrice'] = number_format($ItemPrice, 2);
                    $item['ItemsForExcel'] = $itemsForExcel;
                }
//                $item['Option'] = '';

                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $Year
     * @param $Month
     * @param $BasketName
     *
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createInvoiceDebtorListExcel(
        $Year,
        $Month,
        $BasketName
    ) {
        $resultList =  $this->getInvoiceDebtorList(
            $Year,
            $Month,
            $BasketName,
            false
        );
        if(!empty($resultList)){
            $extraHeaderList = array();
            $sum = array();
            $sum['Total'] = 0;
            // nach Beitragszahler sortieren
            foreach ($resultList as $key => $value) {
                $debtorName[$key] = strtoupper($value['DebtorPerson']);

                if (isset($value['ItemsForExcel'])) {
                    foreach ($value['ItemsForExcel'] as $subValue) {
                        if (!in_array($subValue['Name'], $extraHeaderList)) {
                            $extraHeaderList[] = $subValue['Name'];
                        }
                    }
                }
            }
            array_multisort($debtorName, SORT_NATURAL, $resultList);

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;

            $export->setValue($export->getCell($column++, $row), 'Beitragszahler');
            $export->setValue($export->getCell($column++, $row), 'Debitoren-Nr.');
            $export->setValue($export->getCell($column++, $row), 'Name der Abrechnung');
            $export->setValue($export->getCell($column++, $row), 'Beitragsverursacher');
            $export->setValue($export->getCell($column++, $row), 'Schülernummer');
            $export->setValue($export->getCell($column++, $row), 'Abrechnungszeitraum');
            $export->setValue($export->getCell($column++, $row), 'Fälligkeitsdatum');
            $export->setValue($export->getCell($column++, $row), 'Rechnungsdatum');
            $export->setValue($export->getCell($column++, $row), 'Rechnungsnummer');
            $export->setValue($export->getCell($column++, $row), 'Zahlungsart');
            $export->setValue($export->getCell($column++, $row), 'Typ');
            $export->setValue($export->getCell($column++, $row), 'Gesamtbetrag');
            foreach ($extraHeaderList as $item) {
                $export->setValue($export->getCell($column++, $row), $item);
                $sum[$item] = 0;
            }

            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();
            foreach($resultList as $result) {
                $column = 0;
                $row++;

                $export->setValue($export->getCell($column++, $row), $result['DebtorPerson']);
                $export->setValue($export->getCell($column++, $row), $result['DebtorNumber']);
                $export->setValue($export->getCell($column++, $row), $result['BasketName']);
                $export->setValue($export->getCell($column++, $row), $result['CauserPerson']);
                $export->setValue($export->getCell($column++, $row), $result['CauserIdent']);
                $export->setValue($export->getCell($column++, $row), $result['Time']);
                $export->setValue($export->getCell($column++, $row), $result['TargetTime']);
                $export->setValue($export->getCell($column++, $row), $result['BillTime']);
                $export->setValue($export->getCell($column++, $row), $result['InvoiceNumber']);
                $export->setValue($export->getCell($column++, $row), $result['PaymentType']);
                $export->setValue($export->getCell($column++, $row), $result['BasketType']);
                $export->setValue($export->getCell($column++, $row), $result['DisplaySumPrice']);

                $sum['Total'] += $result['SumPrice'];

                foreach ($extraHeaderList as $header) {
                    if (isset($result['ItemsForExcel'])) {
                         foreach ($result['ItemsForExcel'] as $subItem) {
                             if ($subItem['Name'] == $header) {
                                 $export->setValue($export->getCell($column, $row), $subItem['DisplayPrice']);
                                 $sum[$header] +=  $subItem['Price'];
                             }
                         }
                    }
                    $column++;
                }
            }

            // Aufsummierung
            $column = 9;
            $row++;
            $export->setValue($export->getCell($column++, $row), 'Summe');
            $export->setValue($export->getCell($column++, $row), str_replace('.', ',', number_format($sum['Total'], 2)));
            foreach ($extraHeaderList as $headerItem) {
                $export->setValue($export->getCell($column++, $row), str_replace('.', ',', number_format($sum[$headerItem], 2)));
            }
            $export->setStyle($export->getCell(7, $row), $export->getCell($column - 1, $row))->setFontBold();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getInvoiceUpPaidList()
    {

        $TableContent = array();
        if($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByIsPaid()){
            array_walk($tblInvoiceItemDebtorList, function(TblInvoiceItemDebtor $tblInvoiceItemDebtor) use (&$TableContent){
                $item['Salutation'] = $item['Title'] = '';
                $item['DebtorPerson'] = '';
                $item['Item'] = $tblInvoiceItemDebtor->getName();
                $item['ItemQuantity'] = $tblInvoiceItemDebtor->getQuantity();
                $item['ItemPrice'] = $tblInvoiceItemDebtor->getPriceString();
                $item['ItemSumPrice'] = $tblInvoiceItemDebtor->getSummaryPrice();
                $item['PaymentType'] = ($tblInvoiceItemDebtor->getServiceTblPaymentType()
                    ? $tblInvoiceItemDebtor->getServiceTblPaymentType()->getName()
                    : 'Zahlungsart nicht gefunden');
                $item['InvoiceNumber'] = '';
                $item['CauserPerson'] = '';
                $item['Time'] = '';
                $item['BasketName'] = '';
                $item['DebtorFirstName'] = $item['DebtorLastName'] = '';
                $item['Street'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                if($tblInvoiceItemDebtor->getDebtorPerson()){
                    $item['DebtorPerson'] = $tblInvoiceItemDebtor->getDebtorPerson();
                }
                // Person für die Adresse
                if(($tblPersonDebtor = $tblInvoiceItemDebtor->getServiceTblPersonDebtor())){
                    $item['Salutation'] = $tblPersonDebtor->getSalutation();
                    $item['Title'] = $tblPersonDebtor->getTitle();
                    $item['DebtorFirstName'] = $tblPersonDebtor->getFirstName();
                    $item['DebtorLastName'] = $tblPersonDebtor->getLastName();
                    if(($tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPersonDebtor))){
                        $item['Street'] = $tblAddress->getStreetName();
                        $item['StreetNumber'] = $tblAddress->getStreetNumber();
                        if(($tblCity = $tblAddress->getTblCity())){
                            $item['City'] = $tblCity->getName();
                            $item['District'] = $tblCity->getDistrict();
                            $item['Code'] = $tblCity->getCode();
                        }
                    }
                }
                if($tblInvoice = $tblInvoiceItemDebtor->getTblInvoice()){
                    $item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                    $item['CauserPerson'] = $tblInvoice->getLastName().', '.$tblInvoice->getFirstName();
                    $item['Time'] = $tblInvoice->getYear().'/'.$tblInvoice->getMonth(true);
                    $item['BasketName'] = $tblInvoice->getBasketName();
                }
                $CheckBox = (new CheckBox('IsPaid', ' ',
                    $tblInvoiceItemDebtor->getId()))->ajaxPipelineOnClick(
                    ApiInvoiceIsPaid::pipelineChangeIsPaid($tblInvoiceItemDebtor->getId(), 'true'));
                if (!$tblInvoiceItemDebtor->getIsPaid()) {
                    $CheckBox->setChecked();
                }

                $CheckBox = $CheckBox.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.(new External('', '/Api/Document/Standard/BillingDocumentWarning/Create',
                    new Download(), array('Data' => array('InvoiceItemDebtorId' => $tblInvoiceItemDebtor->getId()))
                        , 'Download Mahnung', External::STYLE_BUTTON_PRIMARY));
                // no line break
                $CheckBox = '<div style="width: 100px">'.$CheckBox.'</div>';

                $item['IsPaid'] = ApiInvoiceIsPaid::receiverIsPaid($CheckBox,
                    $tblInvoiceItemDebtor->getId());

                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
 * @return bool|\SPHERE\Application\Document\Storage\FilePointer
 */
    public function createInvoiceUpPaidListExcel() {
        $resultList = $this->getInvoiceUpPaidList();
        if(!empty($resultList)){
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;

            $export->setValue($export->getCell($column++, $row), 'Rechnungsnummer');
            $export->setValue($export->getCell($column++, $row), 'Abrechnungszeitraum');
            $export->setValue($export->getCell($column++, $row), 'Name der Abrechnung');
            $export->setValue($export->getCell($column++, $row), 'Beitragsverursacher');
            $export->setValue($export->getCell($column++, $row), 'Anrede');
            $export->setValue($export->getCell($column++, $row), 'Titel');
            $export->setValue($export->getCell($column++, $row), 'Beitragszahler Vorname');
            $export->setValue($export->getCell($column++, $row), 'Beitragszahler Nachname');
            $export->setValue($export->getCell($column++, $row), 'Straße');
            $export->setValue($export->getCell($column++, $row), 'Hausnummer');
            $export->setValue($export->getCell($column++, $row), 'PLZ');
            $export->setValue($export->getCell($column++, $row), 'Stadt');
            $export->setValue($export->getCell($column++, $row), 'Ortsteil');
            $export->setValue($export->getCell($column++, $row), 'Beitragsart');
            $export->setValue($export->getCell($column++, $row), 'Anzahl');
            $export->setValue($export->getCell($column++, $row), 'Einzelpreis');
            $export->setValue($export->getCell($column++, $row), 'Gesamtpreis');

            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();
            foreach($resultList as $result) {
                $column = 0;
                $row++;
                $export->setValue($export->getCell($column++, $row), $result['InvoiceNumber']);
                $export->setValue($export->getCell($column++, $row), $result['Time']);
                $export->setValue($export->getCell($column++, $row), $result['BasketName']);
                $export->setValue($export->getCell($column++, $row), $result['CauserPerson']);
                $export->setValue($export->getCell($column++, $row), $result['Salutation']);
                $export->setValue($export->getCell($column++, $row), $result['Title']);
                $export->setValue($export->getCell($column++, $row), $result['DebtorFirstName']);
                $export->setValue($export->getCell($column++, $row), $result['DebtorLastName']);
                $export->setValue($export->getCell($column++, $row), $result['Street']);
                $export->setValue($export->getCell($column++, $row), $result['StreetNumber']);
                $export->setValue($export->getCell($column++, $row), $result['Code']);
                $export->setValue($export->getCell($column++, $row), $result['City']);
                $export->setValue($export->getCell($column++, $row), $result['District']);
                $export->setValue($export->getCell($column++, $row), $result['Item']);
                $export->setValue($export->getCell($column++, $row), $result['ItemQuantity']);
                $export->setValue($export->getCell($column++, $row), $result['ItemPrice']);
                $export->setValue($export->getCell($column, $row), $result['ItemSumPrice']);
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }

        return false;
    }
}
