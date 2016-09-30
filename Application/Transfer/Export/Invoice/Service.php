<?php

namespace SPHERE\Application\Transfer\Export\Invoice;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Api\Response;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice as InvoiceBilling;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 * @package SPHERE\Application\Transfer\Export\Invoice
 */
class Service
{

    /**
     * @param array $TableHeader
     *
     * @return array
     */
    public function createInvoiceList(&$TableHeader)
    {

        $tblInvoiceList = InvoiceBilling::useService()->getInvoiceByIsPaid(false);
        $tblPersonList = array();
        $tblItemArray = array();
        $TableContent = array();
        if ($tblInvoiceList) {
            // Personenliste aus allen offenen Rechnungen erstellen
            foreach ($tblInvoiceList as $tblInvoice) {
                $PersonList = InvoiceBilling::useService()->getPersonAllByInvoice($tblInvoice);
                if ($PersonList) {
                    foreach ($PersonList as $Person) {
                        if (empty( $tblPersonList )) {
                            $tblPersonList[] = $Person;
                        } else {
                            /** @var TblPerson $tblPerson */
                            $Found = false;
                            foreach ($tblPersonList as $tblPerson) {
                                if ($tblPerson->getId() == $Person->getId()) {
                                    $Found = true;
                                }
                            }
                            if (!$Found) {
                                $tblPersonList[] = $Person;
                            }
                        }
                    }
                }
                $tblItemList = InvoiceBilling::useService()->getItemAllByInvoice($tblInvoice);
                if ($tblItemList) {
                    foreach ($tblItemList as $Item) {
                        if (empty( $tblItemArray )) {
                            $InventoryItem = $Item->getServiceTblItem();
                            if ($InventoryItem) {
                                $tblItemArray[] = $InventoryItem;
                            }
                        } else {
                            /** @var TblItem $tblItem */
                            $Found = false;
                            $InventoryItem = $Item->getServiceTblItem();
                            if ($InventoryItem) {
                                foreach ($tblItemArray as $tblItem) {
                                    if ($tblItem->getId() == $InventoryItem->getId()) {
                                        $Found = true;
                                    }
                                }
                                if (!$Found) {
                                    $tblItemArray[] = $InventoryItem;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($tblInvoiceList as $tblInvoice) {
                if (!empty( $tblPersonList )) {
                    array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblInvoice, $tblItemArray, &$TableHeader) {

                        $tblItemList = InvoiceBilling::useService()->getItemAllInvoiceAndPerson($tblInvoice, $tblPerson);
                        if ($tblItemList) {

                            $Item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                            $Item['Debtor'] = '';
                            $Item['Name'] = $tblPerson->getLastFirstName();
                            $Item['StudentNumber'] = 'keine';
                            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                            if ($tblStudent && $tblStudent->getIdentifier() != '') {
                                $Item['StudentNumber'] = $tblStudent->getIdentifier();
                            }
                            $Item['Date'] = $tblInvoice->getTargetTime();

                            // trägt bei fehlenden angaben leere Zellen ein
                            foreach ($TableHeader as $key => $value) {
                                if (!isset( $Item[$key] )) {
                                    $Item[$key] = '';
                                }
                            }

                            /** @var TblItem $tblItem */
                            foreach ($tblItemList as $tblItem) {
                                /** @var \SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem $InventoryItem */
                                foreach ($tblItemArray as $key => $InventoryItem) {
                                    if ($InventoryItem->getId() == $tblItem->getServiceTblItem()->getId()) {
//                                        $Item[$key] = $tblItem->getName().' - '.( $tblItem->getValue() * $tblItem->getQuantity() );   // Item Kontrolle (mit Namen)
                                        $Item['Item'.$key] = number_format(( $tblItem->getValue() * $tblItem->getQuantity() ), 2).' €';

                                        if (empty( $tblDebtor )) {
                                            $tblDebtor = InvoiceBilling::useService()->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
                                        }

                                        // Header erstellen (dynamisch)
                                        if (!isset( $TableHeader['Item'.$key] )) {
                                            $TableHeader['Item'.$key] = $tblItem->getName();
                                        }
                                    }
                                }
                            }
                            if (isset( $tblDebtor )) {
                                if (( $tblDebtorService = $tblDebtor->getServiceTblDebtor() )) {
                                    if (( $tblPersonDebtor = $tblDebtorService->getServiceTblPerson() )) {
                                        $Item['Debtor'] = $tblPersonDebtor->getFullName();
                                    }
                                }
                            }

                            array_push($TableContent, $Item);
                        }
                    });
                }
            }
        }

        return $TableContent;
    }

    /**
     * @param array $TableContent
     * @param array $TableHeader
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInvoiceListExcel($TableContent, $TableHeader)
    {

        if ($TableContent) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            if (!empty( $TableHeader )) {
                $HeadTitleCount = 0;
                foreach ($TableHeader as $Title) {
                    $export->setValue($export->getCell($HeadTitleCount, "0"), $Title);
                    $HeadTitleCount++;
                }
            }

            $Row = 1;

            foreach ($TableContent as &$Content) {

                $ContentColumn = 0;

                foreach ($Content as &$Column) {

                    if (empty( $Column )) {
                        $Column = ' - ';
                    }
                    $export->setValue($export->getCell($ContentColumn, $Row), $Column);
                    $ContentColumn++;
                }
                $Row++;
            }

//            exit;

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param null $tblInvoiceList
     * @param      $PersonFrom
     * @param      $StudentNumber
     * @param      $IBAN
     * @param      $BIC
     * @param      $Client
     * @param      $BankName
     * @param      $Owner
     * @param      $Billers
     * @param      $SchoolIBAN
     * @param      $SchoolBIC
     * @param      $SchoolBankName
     * @param      $SchoolOwner
     * @param bool $Export
     *
     * @return array
     */
    public function createInvoiceListByPrepare(
//        &$TableHeader,
        $tblInvoiceList = null,
        $PersonFrom,
        $StudentNumber,
        $IBAN,
        $BIC,
        $Client,
        $BankName,
        $Owner,
        $Billers,
        $SchoolIBAN,
        $SchoolBIC,
        $SchoolBankName,
        $SchoolOwner,
        $Export = true
    ) {

        $tblPersonList = array();
        $tblItemArray = array();
        $TableContent = array();
        if ($tblInvoiceList != null) {
            // Personenliste aus allen offenen Rechnungen erstellen
            foreach ($tblInvoiceList as $tblInvoice) {
                $PersonList = InvoiceBilling::useService()->getPersonAllByInvoice($tblInvoice);
                if ($PersonList) {
                    foreach ($PersonList as $Person) {
                        if (empty( $tblPersonList )) {
                            $tblPersonList[] = $Person;
                        } else {
                            /** @var TblPerson $tblPerson */
                            $Found = false;
                            foreach ($tblPersonList as $tblPerson) {
                                if ($tblPerson->getId() == $Person->getId()) {
                                    $Found = true;
                                }
                            }
                            if (!$Found) {
                                $tblPersonList[] = $Person;
                            }
                        }
                    }
                }
                $tblItemList = InvoiceBilling::useService()->getItemAllByInvoice($tblInvoice);
                if ($tblItemList) {
                    foreach ($tblItemList as $Item) {
                        if (empty( $tblItemArray )) {
                            $InventoryItem = $Item->getServiceTblItem();
                            if ($InventoryItem) {
                                $tblItemArray[] = $InventoryItem;
                            }
                        } else {
                            /** @var TblItem $tblItem */
                            $Found = false;
                            $InventoryItem = $Item->getServiceTblItem();
                            if ($InventoryItem) {
                                foreach ($tblItemArray as $tblItem) {
                                    if ($tblItem->getId() == $InventoryItem->getId()) {
                                        $Found = true;
                                    }
                                }
                                if (!$Found) {
                                    $tblItemArray[] = $InventoryItem;
                                }
                            }
                        }
                    }
                }
            }

            /** @var TblInvoice $tblInvoice */
            foreach ($tblInvoiceList as $tblInvoice) {
                if (!empty( $tblPersonList )) {
                    array_walk($tblPersonList, function (TblPerson $tblPerson) use (
                        &$TableContent,
                        $tblInvoice,
                        $tblItemArray,
                        $PersonFrom,
                        $StudentNumber,
                        $IBAN,
                        $BIC,
                        $Client,
                        $BankName,
                        $Owner,
                        $Billers,
                        $SchoolIBAN,
                        $SchoolBIC,
                        $SchoolBankName,
                        $SchoolOwner,
                        $Export
                    ) {

                        $tblItemList = InvoiceBilling::useService()->getItemAllInvoiceAndPerson($tblInvoice, $tblPerson);
                        if ($tblItemList) {
                            /** @var TblItem $tblItem */
                            foreach ($tblItemList as $tblItem) {
                                $Item['Payer'] = '';
                                $Item['PersonFrom'] = $tblPerson->getFullName();
                                $Item['StudentNumber'] = '';
                                $Item['BillDate'] = $tblInvoice->getEntityCreate()->format('d.m.Y');
                                $Item['Date'] = $tblInvoice->getTargetTime();
                                $Item['DebtorNumber'] = '';
                                $Item['Reference'] = '';
                                $Item['Owner'] = '';
                                $Item['Bank'] = '';
                                $Item['IBAN'] = '';
                                $Item['BIC'] = '';
                                $Item['Client'] = '';
                                $Item['Billers'] = $tblInvoice->getSchoolName();
                                $Item['SchoolOwner'] = $tblInvoice->getSchoolOwner();
                                $Item['SchoolBankName'] = $tblInvoice->getSchoolBankName();
                                $Item['SchoolIBAN'] = $tblInvoice->getSchoolIBAN();
                                $Item['SchoolBIC'] = $tblInvoice->getSchoolBIC();
                                $Item['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                                $Item['Item'] = $tblItem->getName();
                                $Item['ItemPrice'] = '';
                                $Item['Quantity'] = $tblItem->getQuantity();
                                $Item['Sum'] = '';

                                if ($Export) {
                                    $Item['ItemPrice'] = (float)$tblItem->getValue();
                                    $Item['Sum'] = $tblItem->getValue() * $tblItem->getQuantity();
                                } else {
                                    $Item['ItemPrice'] = str_replace('.', ',', number_format(( $tblItem->getValue() ), 2).' €');
                                    $Item['Sum'] = str_replace('.', ',', number_format(( $tblItem->getValue() * $tblItem->getQuantity() ), 2)).' €';
                                }

                                $Item['Reference'] = '';
                                $tblDebtor = InvoiceBilling::useService()->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
                                if (isset( $tblDebtor )) {
                                    $tblServiceDebtor = $tblDebtor->getServiceTblDebtor();
                                    if ($tblServiceDebtor) {
                                        $tblPersonPayer = $tblServiceDebtor->getServiceTblPerson();
                                        if ($tblPersonPayer) {
                                            $Item['Payer'] = $tblPersonPayer->getFullName();
                                        }
                                    }
                                    $Item['IBAN'] = $tblDebtor->getIBAN();
                                    $Item['BIC'] = $tblDebtor->getBIC();
                                    $Item['Reference'] = $tblDebtor->getBankReference();
                                    $Item['Bank'] = $tblDebtor->getBankName();
                                    $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                                    $Item['Owner'] = $tblDebtor->getOwner();
                                }
                                if (Consumer::useService()->getConsumerBySession()->getName()) {
                                    $Item['Client'] = Consumer::useService()->getConsumerBySession()->getName();
                                }
                                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                if ($tblStudent) {
                                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                                }
                                if ($BankName == 0) {
                                    unset( $Item['Bank'] );
                                }
                                if ($Owner == 0) {
                                    unset( $Item['Owner'] );
                                }
                                if ($IBAN == 0) {
                                    unset( $Item['IBAN'] );
                                }
                                if ($BIC == 0) {
                                    unset( $Item['BIC'] );
                                }
                                if ($Client == 0) {
                                    unset( $Item['Client'] );
                                }
                                if ($StudentNumber == 0) {
                                    unset( $Item['StudentNumber'] );
                                }
                                if ($PersonFrom == 0) {
                                    unset( $Item['PersonFrom'] );
                                }
                                if ($Billers == 0) {
                                    unset( $Item['Billers'] );
                                }
                                if ($SchoolOwner == 0) {
                                    unset( $Item['SchoolOwner'] );
                                }
                                if ($SchoolBankName == 0) {
                                    unset( $Item['SchoolBankName'] );
                                }
                                if ($SchoolIBAN == 0) {
                                    unset( $Item['SchoolIBAN'] );
                                }
                                if ($SchoolBIC == 0) {
                                    unset( $Item['SchoolBIC'] );
                                }

                                array_push($TableContent, $Item);
                            }
                        }
                    });
                }
            }
        }
        return $TableContent;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Prepare
     *
     * @return IFormInterface|string
     */
    public function controlPrepare(IFormInterface &$Stage = null, $Prepare = null)
    {
        ini_set('display_errors', 1);
        /**
         * Skip to Frontend
         */

        if (null === $Prepare) {
            return $Stage;
        }
        $Error = false;
        if (!isset( $Prepare['DateFrom'] ) || empty( $Prepare['DateFrom'] )) {
            $Stage->setError('Prepare[DateFrom]', 'Bitte geben sie einen Anfangszeitraum für die Rechnungen an.');
            $Error = true;
        } else {
            $DateFrom = new \DateTime($Prepare['DateFrom']);
            if (!isset( $Prepare['DateTo'] ) || empty( $Prepare['DateTo'] )) {
                $Prepare['DateTo'] = (new \DateTime('now'))->format('d.m.Y');
            }
            $DateTo = new \DateTime($Prepare['DateTo']);
            $InvoiceList = InvoiceBilling::useService()->getInvoiceAllByDate($DateFrom, $DateTo);
            if (!$InvoiceList) {
                $Stage->setError('Prepare[DateFrom]', 'Keine Rechnung im angegebenem Zeitraum');
                $Stage->setError('Prepare[DateTo]', 'Keine Rechnung im angegebenem Zeitraum');
                $Error = true;
            }
        }

        if (!$Error) {
            $DateFrom = $Prepare['DateFrom'];
            if (!isset( $Prepare['DateTo'] ) || empty( $Prepare['DateTo'] )) {
                $Prepare['DateTo'] = (new \DateTime('now'))->format('d.m.Y');
            }
            $DateTo = $Prepare['DateTo'];
            $BankName = ( isset( $Prepare['BankName'] ) ? 1 : 0 );
            $Owner = ( isset( $Prepare['Owner'] ) ? 1 : 0 );
            $IBAN = ( isset( $Prepare['IBAN'] ) ? 1 : 0 );
            $BIC = ( isset( $Prepare['BIC'] ) ? 1 : 0 );
            $Client = ( isset( $Prepare['Client'] ) ? 1 : 0 );
            $Billers = ( isset( $Prepare['Billers'] ) ? 1 : 0 );
            $SchoolBankName = ( isset( $Prepare['SchoolBankName'] ) ? 1 : 0 );
            $SchoolOwner = ( isset( $Prepare['SchoolOwner'] ) ? 1 : 0 );
            $SchoolIBAN = ( isset( $Prepare['SchoolIBAN'] ) ? 1 : 0 );
            $SchoolBIC = ( isset( $Prepare['SchoolBIC'] ) ? 1 : 0 );
            $StudentNumber = ( isset( $Prepare['StudentNumber'] ) ? 1 : 0 );
            $PersonFrom = ( isset( $Prepare['PersonFrom'] ) ? 1 : 0 );
            $Status = $Prepare['Status'];

            return $Stage.new Redirect('/Billing/Bookkeeping/Export/Prepare/View', Redirect::TIMEOUT_SUCCESS
                , array('Filter' => (new Response())->addData(array(
                    'DateFrom'       => $DateFrom,
                    'DateTo'         => $DateTo,
                    'BankName'       => $BankName,
                    'Owner'          => $Owner,
                    'IBAN'           => $IBAN,
                    'BIC'            => $BIC,
                    'Client'         => $Client,
                    'Billers'        => $Billers,
                    'SchoolBankName' => $SchoolBankName,
                    'SchoolOwner'    => $SchoolOwner,
                    'SchoolIBAN'     => $SchoolIBAN,
                    'SchoolBIC'      => $SchoolBIC,
                    'StudentNumber'  => $StudentNumber,
                    'PersonFrom'     => $PersonFrom,
                    'Status'         => $Status
                ))->__toString()
                ));
        }

        return $Stage;
    }

    /**
     * @param      $DateFrom
     * @param null $DateTo
     * @param int  $Status "Invoice" 1 = open, 2 = paid, 3 = storno
     *
     * @return bool|\SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice[]
     */
    public function getInvoiceListByDate($DateFrom, $DateTo = null, $Status = 1)
    {

        $DateFrom = new \DateTime($DateFrom);
        if ($DateTo == null) {
            $DateTo = new \DateTime('now');
        } else {
            $DateTo = new \DateTime($DateTo);
        }
        $InvoiceList = InvoiceBilling::useService()->getInvoiceAllByDate($DateFrom, $DateTo, $Status);
        return $InvoiceList;
    }

    /**
     * @param $Filter
     *
     * @return array
     */
    public function getHeader($Filter)
    {
        $TableHeader = array();
        $TableHeader['Payer'] = 'Bezahler (Debitor)';
        if (isset( $Filter->PersonFrom ) && $Filter->PersonFrom != 0) {
            $TableHeader['PersonFrom'] = 'Leistungsbezieher';
        }
        if (isset( $Filter->StudentNumber ) && $Filter->StudentNumber != 0) {
            $TableHeader['StudentNumber'] = 'Schüler-Nr.';
        }
        $TableHeader['BillDate'] = 'Rechnungsdatum';
        $TableHeader['Date'] = 'Fälligkeitsdatum';
        $TableHeader['DebtorNumber'] = 'Debitoren-Nr.';
        $TableHeader['Reference'] = 'Mandats-Ref.';
        if (isset( $Filter->Owner ) && $Filter->Owner != 0) {
            $TableHeader['Owner'] = 'Kontoinhaber';
        }
        if (isset( $Filter->BankName ) && $Filter->BankName != 0) {
            $TableHeader['Bank'] = 'Name der Bank';
        }
        if (isset( $Filter->IBAN ) && $Filter->IBAN != 0) {
            $TableHeader['IBAN'] = 'IBAN';
        }
        if (isset( $Filter->BIC ) && $Filter->BIC != 0) {
            $TableHeader['BIC'] = 'BIC';
        }
        if (isset( $Filter->Client ) && $Filter->Client != 0) {
            $TableHeader['Client'] = 'Mandant';
        }
        if (isset( $Filter->Billers ) && $Filter->Billers != 0) {
            $TableHeader['Billers'] = 'Rechnungssteller (Schule)';
        }
        if (isset( $Filter->SchoolOwner ) && $Filter->SchoolOwner != 0) {
            $TableHeader['SchoolOwner'] = 'Kontoinhaber (Schule)';
        }
        if (isset( $Filter->SchoolBankName ) && $Filter->SchoolBankName != 0) {
            $TableHeader['SchoolBankName'] = 'Name der Bank (Schule)';
        }
        if (isset( $Filter->SchoolIBAN ) && $Filter->SchoolIBAN != 0) {
            $TableHeader['SchoolIBAN'] = 'IBAN (Schule)';
        }
        if (isset( $Filter->SchoolBIC ) && $Filter->SchoolBIC != 0) {
            $TableHeader['SchoolBIC'] = 'BIC (Schule)';
        }
        $TableHeader['InvoiceNumber'] = 'Buchungstext';
        $TableHeader['Item'] = 'Artikel';
        $TableHeader['ItemPrice'] = 'Einzelpreis';
        $TableHeader['Quantity'] = 'Anzahl';
        $TableHeader['Sum'] = 'Gesamtpreis';

        return $TableHeader;
    }
}