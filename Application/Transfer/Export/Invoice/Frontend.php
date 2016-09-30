<?php

namespace SPHERE\Application\Transfer\Export\Invoice;

use SPHERE\Application\Api\Response;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice as InvoiceBilling;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Transfer\Export\Invoice
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Prepare
     *
     * @return Stage
     */
    public function frontendExport($Prepare = null)
    {

        $Stage = new Stage('Export', 'aller offenen Posten');

        $TableContent = array();
        $tblInvoiceList = InvoiceBilling::useService()->getInvoiceByIsPaid(false);
        if($tblInvoiceList){
            array_walk($tblInvoiceList, function(TblInvoice $tblInvoice) use (&$TableContent)
            {

                $Content['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $Content['CreateDate'] = $tblInvoice->getEntityCreate()->format('d.m.Y');
                $Content['TargetDate'] = $tblInvoice->getTargetTime();
                $Content['Debtor'] = '';
                $Content['Billers'] = $tblInvoice->getSchoolName();
                $Content['Item'] = '';
                $Content['Price'] = InvoiceBilling::useService()->getPriceString(
                    InvoiceBilling::useService()->getInvoicePrice($tblInvoice));

                if(($ItemString = InvoiceBilling::useService()->getInvoiceItemsString($tblInvoice))){
                    $Content['Item'] = $ItemString;
                }
                $tblDebtorList = InvoiceBilling::useService()->getDebtorAllByInvoice($tblInvoice);
                if($tblDebtorList){
                    $tblDebtor = $tblDebtorList[0]->getServiceTblDebtor();
                    if($tblDebtor){
                        if(($tblPerson = $tblDebtor->getServiceTblPerson())){
                            $Content['Debtor'] = $tblPerson->getFullName();
                        }
                    }
                }

                array_push($TableContent, $Content);
            });

            $form = $this->formPrepare($Prepare);
            $form->appendFormButton(new Primary('Filtern', new Filter()));
        }

//        $Stage->addButton(new Standard('Auswahl Herunterladen', '\Billing\Bookkeeping\Export\Prepare', new Search()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ( isset( $form ) ?
                                array(
                                    new Title(new Search().' Filterung', 'der Rechnungen'),
                                    new Well(Invoice::useService()->controlPrepare($form, $Prepare))
                                ) : '' )),
                        new LayoutColumn(
                            new Title(new ListingTable().' Übersicht der offenen Posten')
//                            new TableData($TableContent, null, $TableHeader)
                            .new TableData($TableContent, null,
                                array('InvoiceNumber' => 'Rechnungsnummer',
                                    'CreateDate' => 'Rechnungsdatum',
                                    'TargetDate' => 'Fälligkeitsdatum',
                                    'Debtor' => 'Debitor',
                                    'Billers' => 'Rechnungssteller',
                                    'Item' => 'Enthaltene Artikel',
                                    'Price' => 'Gesamt Preis',
                                    ),
                                array(
                                    'order'      => array(
                                        array(0, 'desc')
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'de_date', 'targets' => 1),
                                        array('type' => 'de_date', 'targets' => 2),
                                    )
                                )
                            )
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

//    /**
//     * @param null $Prepare
//     *
//     * @return Stage
//     */
//    public function frontendPrepare($Prepare = null)
//    {
//
//        $Stage = new Stage('Export', 'nach Filterung');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Export'));
//
//        $form = $this->formPrepare($Prepare);
//        $form->appendFormButton(new Primary('Speichern', new Save()));
////        $form->setConfirm('Die Zuweisung der Personen wurde noch nicht gespeichert.');
//
//
//        $Stage->setContent(
//            new Layout(
//                new LayoutGroup(
//                    new LayoutRow(
//                        new LayoutColumn(array(
//                            new Title(new Search().' Filterung', 'der Rechnungen'),
//                            new Well(Invoice::useService()->controlPrepare($form, $Prepare))
//                        ))
//                    )
//                )
//            )
//        );
//
//        return $Stage;
//    }

    /**
     * @param $Prepare
     *
     * @return Form
     */
    public function formPrepare($Prepare)
    {

        $Global = $this->getGlobal();
        if ($Prepare == null) {
            $Now = new \DateTime('now');
            $Global->POST['Prepare']['DateFrom'] = $Now->format('d.m.Y');
            $Now->add(new \DateInterval('P1M'));
            $Global->POST['Prepare']['DateTo'] = $Now->format('d.m.Y');
            $Global->POST['Prepare']['Status'] = 1;
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                        new FormColumn(
                            new Panel('Zeitraum', array(new DatePicker('Prepare[DateFrom]', '', 'Fälligkeit ab:', new Time()),
                                    new DatePicker('Prepare[DateTo]', '', 'Fälligkeit bis:', new Time()))
                                , Panel::PANEL_TYPE_INFO)
                            , 3),
                        new FormColumn(array(
                            new Info('Hinzufügen von'),
                            new Panel('Personendaten', array(
                                    new CheckBox('Prepare[PersonFrom]', 'Leistungsbezieher', 1),
                                    new CheckBox('Prepare[StudentNumber]', 'Schülernummer', 1),
                                )
                                , Panel::PANEL_TYPE_INFO),
                            new Panel('Bankdaten', array(
                                    new CheckBox('Prepare[BankName]', 'Name der Bank', 1),
                                    new CheckBox('Prepare[Owner]', 'Besitzer des Konto\'s', 1),
                                    new CheckBox('Prepare[IBAN]', 'IBAN', 1),
                                    new CheckBox('Prepare[BIC]', 'BIC', 1)
                                )
                                , Panel::PANEL_TYPE_INFO),
                        ), 3),
                        new FormColumn(array(
                            new Info('Hinzufügen von'),
                            new Panel('Schuldaten', array(
                                    new CheckBox('Prepare[Client]', 'Mandant', 1),
                                    new CheckBox('Prepare[Billers]', 'Rechnungssteller', 1),
                                )
                                , Panel::PANEL_TYPE_INFO),
                            new Panel('Bankdaten des Rechnungssteller\'s', array(
                                    new CheckBox('Prepare[SchoolBankName]', 'Name der Bank', 1),
                                    new CheckBox('Prepare[SchoolOwner]', 'Besitzer des Konto\'s', 1),
                                    new CheckBox('Prepare[SchoolIBAN]', 'IBAN', 1),
                                    new CheckBox('Prepare[SchoolBIC]', 'BIC', 1)
                                )
                                , Panel::PANEL_TYPE_INFO)
                        ), 3),
                        new FormColumn(array(
                            new Panel('Rechnungsstatus', array(
                                new RadioBox('Prepare[Status]', 'Offene Rechnungen', 1),
                                new RadioBox('Prepare[Status]', 'Bezahlte Rechnungen', 2),
                                new RadioBox('Prepare[Status]', 'Stornierte Rechnungen', 3)
                            ), Panel::PANEL_TYPE_INFO)
                        ), 3)
                    )
                )
            ))
        );
    }

    /**
     * @param $Filter
     *
     * @return Stage|string
     */
    public function frontendPrepareView($Filter)
    {

        $Filter = json_decode($Filter);

        $Stage = new Stage('Export Filterung', 'Vorschau');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Export'));
        if (!empty( $Filter->Error )) {
            return $Stage.new Warning('Übergabe nicht auswertbar');
        }
        $Filter = current($Filter->Data);

        $tblInvoiceList = Invoice::useService()->getInvoiceListByDate($Filter->DateFrom, $Filter->DateTo, $Filter->Status);

        $TableHeader = Invoice::useService()->getHeader($Filter);

        $TableContent = array();
        if ($tblInvoiceList) {
            $TableContent = Invoice::useService()->createInvoiceListByPrepare(
//                $TableHeader,
                $tblInvoiceList,
                $Filter->PersonFrom,
                $Filter->StudentNumber,
                $Filter->IBAN,
                $Filter->BIC,
                $Filter->Client,
                $Filter->BankName,
                $Filter->Owner,
                $Filter->Billers,
                $Filter->SchoolIBAN,
                $Filter->SchoolBIC,
                $Filter->SchoolBankName,
                $Filter->SchoolOwner,
                false
            );
            if (!empty( $TableContent )) {
                $Stage->addButton(new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                    '/Api/Billing/Invoice/Download', new Download(),
                    array('Filter' => (new Response())->addData(array(
                        'DateFrom'       => $Filter->DateFrom,
                        'DateTo'         => $Filter->DateTo,
                        'BankName'       => $Filter->BankName,
                        'Owner'          => $Filter->Owner,
                        'IBAN'           => $Filter->IBAN,
                        'BIC'            => $Filter->BIC,
                        'Client'            => $Filter->Client,
                        'Billers' => $Filter->Billers,
                        'SchoolBankName' => $Filter->SchoolBankName,
                        'SchoolOwner'    => $Filter->SchoolOwner,
                        'SchoolIBAN'     => $Filter->SchoolIBAN,
                        'SchoolBIC'      => $Filter->SchoolBIC,
                        'StudentNumber'  => $Filter->StudentNumber,
                        'PersonFrom'     => $Filter->PersonFrom,
                        'Status'         => $Filter->Status,
                    ))->__toString()
                    )
                ));
            }
        }
        $Status = ( $Filter->Status == 1 ? 'Offene Rechnungen' : ( $Filter->Status == 2 ? 'Bezahlte Rechnungen' : 'Stornierte Rechnungen' ) );

        $ColumnDate = 1;
        if($Filter->PersonFrom == 1){
            $ColumnDate++;
        }
        if($Filter->StudentNumber == 1){
            $ColumnDate++;
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( empty( $TableContent ) ? new Warning('Keine Rechnung gefunden<br/>
                                                                    Datum "Fälligkeit" von: '.$Filter->DateFrom.'<br/>
                                                                    Datum "Fälligkeit" bis: '.$Filter->DateTo.'<br/>
                                                                    Status der Rechnungen: '.$Status)
                                : new TableData($TableContent, null, $TableHeader,
                                    array(
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => $ColumnDate),
                                            array('type' => 'de_date', 'targets' => $ColumnDate + 1),
                                        )
                                    )
                                )
                            )
                        )
                    )
                    , new Title(new ListingTable().' Übersicht'))
            )
        );
        return $Stage;
    }

}
