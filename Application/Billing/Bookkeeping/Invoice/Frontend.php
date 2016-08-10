<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendInvoiceList()
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnungen');
        $Stage->setDescription('Übersicht');
//        $Stage->addButton(new Standard('Aufträge', '/Billing/Bookkeeping/Invoice/Control', new Ok(), null, 'Freigeben von Rechnungen'));
//        new Backward();

        $tblInvoiceAll = Invoice::useService()->getInvoiceAll();
        $TableContent = array();
        if ($tblInvoiceAll) {

            array_walk($tblInvoiceAll, function (TblInvoice $tblInvoice) use (&$TableContent) {
                $Content['CreateDate'] = $tblInvoice->getEntityCreate()->format('d.m.Y');
                $Content['TargetDate'] = $tblInvoice->getTargetTime();
                $Content['FullName'] = $tblInvoice->getServiceTblPerson()->getFullName();
                $Content['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $Content['DebtorNumber'] = '';
                $Content['Reference'] = '';

                $tblDebtorList = Invoice::useService()->getDebtorAllByInvoice($tblInvoice);
                $DebtorNumberArray = array();
                $DebtorReferenceArray = array();
                if ($tblDebtorList) {
                    foreach ($tblDebtorList as $tblDebtor) {
                        $DebtorNumberArray[] = $tblDebtor->getDebtorNumber();
                        $DebtorReferenceArray[] = $tblDebtor->getBankReference();
                    }
                    $DebtorNumberArray = array_filter(array_unique($DebtorNumberArray));
                    $DebtorReferenceArray = array_filter(array_unique($DebtorReferenceArray));
                    $DebtorNumberString = implode(', ', $DebtorNumberArray);
                    $DebtorReferenceString = implode(', ', $DebtorReferenceArray);
                    $Content['DebtorNumber'] = $DebtorNumberString;
                    $Content['Reference'] = $DebtorReferenceString;
                }

                $tblItemList = Invoice::useService()->getItemAllByInvoice($tblInvoice);
                $Price = 0.00;
                $ItemCount = array();
                if (!empty( $tblItemList )) {
                    foreach ($tblItemList as &$tblItem) {
                        $Price += $tblItem->getSummaryPriceInt();
                        if (!empty( $ItemCount[$tblItem->getName()] )) {
                            $ItemCount[$tblItem->getName()] += $tblItem->getQuantity();
                        } else {
                            $ItemCount[$tblItem->getName()] = $tblItem->getQuantity();
                        }
                        $tblItem = $tblItem->getName();
                    }
                    $tblItemList = array_unique($tblItemList);
                    foreach ($tblItemList as &$Item) {
                        foreach ($ItemCount as $ItemName => $value) {
                            if ($Item === $ItemName) {
                                $Item = $value.'x '.$Item;
                            }
                        }
                    }

                    $ItemString = implode(', ', $tblItemList);
                    $Content['ItemList'] = $ItemString;
                } else {
                    $Content['ItemList'] = '';
                }
                $Content['Price'] = Invoice::useService()->getPriceString($Price);
                if ($tblInvoice->getIsPaid()) {
                    $Content['Paid'] = new Success(new Check());
                } else {
                    $Content['Paid'] = new Danger(new Unchecked());
                }
                if ($tblInvoice->getIsReversal()) {
                    $Content['Reversal'] = new Danger(new Check());
                } else {
                    $Content['Reversal'] = '';
                }
                $Content['Option'] = new Standard('', '/Billing/Bookkeeping/Invoice/View', new EyeOpen(),
                    array('Id' => $tblInvoice->getId()), 'Auswahl');

                array_push($TableContent, $Content);
            });
        }


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new ListingTable().' Übersicht', 'aller vorhandenen Rechnungen'),
                            ( empty( $TableContent ) ? new Warning('Keine Rechnungen vorhanden') :
                                new TableData($TableContent, null,
                                    array('InvoiceNumber' => 'Rechnungsnummer',
                                          'CreateDate'    => 'Erstellungsdatum',
                                          'TargetDate'    => 'Fälligkeitsdatum',
                                          'DebtorNumber'  => 'Debitorennummer',
                                          'FullName'      => 'Debitor',
                                          'Reference'     => 'Mandatsreferenz(en)',
                                          'ItemList'      => 'Artikel',
                                          'Price'         => 'Gesamtpreis',
                                          'Paid'          => 'Bezahlt',
                                          'Reversal'      => 'Storniert',
                                          'Option'        => ''),
                                    array(
                                        'order'      => array(
                                            array(0, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 1),
                                            array('type' => 'de_date', 'targets' => 2),
                                        )
                                    ))
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendInvoiceView($Id = null)
    {

        $Stage = new Stage('Rechung', '');
        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Invoice', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice', new ChevronLeft()));
        }
        $tblItemList = Invoice::useService()->getItemAllByInvoice($tblInvoice);

        $TableContent = array();
        $SumPrice = 0;
        if ($tblItemList) {
            array_walk($tblItemList, function (TblItem $tblItem) use (&$TableContent, &$SumPrice, $tblInvoice) {
                $Item['Name'] = $tblItem->getName();
                $Item['Quantity'] = $tblItem->getQuantity();
                $Item['SinglePrice'] = $tblItem->getPriceString();
                $Item['SumPrice'] = $tblItem->getSummaryPrice();

                $tblDebtor = Invoice::useService()->getDebtorByInvoiceAndItem($tblInvoice, $tblItem);
                if ($tblDebtor) {
                    $tblPaymentType = $tblDebtor->getServiceTblPaymentType();
                    if ($tblPaymentType) {
                        $Item['Payment'] = $tblDebtor->getServiceTblPaymentType()->getName();
                    }
                }
                $SumPrice += $tblItem->getValue() * $tblItem->getQuantity();

                array_push($TableContent, $Item);
            });
        }

        // Sortierung nach Name && Preis aufsteigend
        foreach ($TableContent as $key => $row) {
            $Name[$key] = strtoupper($row['Name']);
            $SummaryPrice[$key] = ( $row['SumPrice'] );
        }
        array_multisort($Name, SORT_ASC, $SummaryPrice, SORT_ASC, $TableContent);

        $TablePaid = array();
        $tblPaymentList = Balance::useService()->getPaymentAllByInvoice($tblInvoice);
        if ($tblPaymentList) {
            array_walk($tblPaymentList, function (TblPayment $tblPayment) use (&$TablePaid, $tblInvoice) {
                $Item['Time'] = $tblPayment->getLastDate();
                $Item['Purpose'] = $tblPayment->getPurpose();
                $Item['Value'] = $tblPayment->getValueString();
                $Item['PaymentType'] = $tblPayment->getTblPaymentType()->getName();
                array_push($TablePaid, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn($this->layoutInvoice($tblInvoice, $SumPrice)
                                , 12)
                        ),
                        new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title(new ListingTable().' Übersicht der Artikel'),
                                    new TableData($TableContent, null,
                                        array('Name'        => 'Artikel',
                                              'SinglePrice' => 'Einzelpreis',
                                              'Quantity'    => 'Anzahl',
                                              'SumPrice'    => 'Gesamtpreis',
                                              'Payment'     => 'Bezahlart',
                                        )
                                        , false)
                                ), 6),
                                new LayoutColumn(array(
                                        new Title(new Money().' Teilzahlung'),
                                        ( empty( $TablePaid ) ? new Warning('Keine Teilzahlungen vorhanden') :
                                            new TableData($TablePaid, null,
                                                array('Time'        => 'Datum',
                                                      'Purpose'     => 'Verwendungszweck',
                                                      'Value'       => 'Betrag',
                                                      'PaymentType' => 'Bezahlart',),
                                                false) ))
                                    , 6)
                            )
                        ))
                ))
        );

        return $Stage;
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param            $SummaryPrice
     *
     * @return string
     */
    public function layoutInvoice(TblInvoice $tblInvoice, $SummaryPrice)
    {

        $Content = array();
        $Content[] = 'Gesamtbetrag: '.new PullRight(Balance::useService()->getPriceString($SummaryPrice));
        if (( $PaidMoney = Balance::useService()->getPaidFromInvoice($tblInvoice) ) > 0) {
            $result = $SummaryPrice - $PaidMoney;
            if ($result >= 0) {
                $Content[] = new Bold('Fehlender Betrag: '.new PullRight(Balance::useService()->getPriceString($result)));
            } else {
                $Content[] = new \SPHERE\Common\Frontend\Message\Repository\Danger(new Bold('Betrag überschritten: '.new PullRight(Balance::useService()->getPriceString($result))));
            }
        }
        $Button = false;
        if ($tblInvoice->getIsPaid()) {
            $Button = new Standard('', '/Billing/Bookkeeping/Invoice/View/Remove/Paid', new Repeat(),
                array('Id' => $tblInvoice->getId()), 'Bezahlt aufheben');
        }
        if ($tblInvoice->getIsReversal()) {
            $Button = new Standard('', '/Billing/Bookkeeping/Invoice/View/Remove/Storno', new Repeat(),
                array('Id' => $tblInvoice->getId()), 'Storno aufheben');
        }

        $tblPersonFrom = $tblInvoice->getServiceTblPerson();
        return new Title('Eckdaten der Rechnung')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Rechnungsnummer:', $tblInvoice->getInvoiceNumber(), Panel::PANEL_TYPE_SUCCESS)
                        , 4),
                    new LayoutColumn(
                        new Panel('Rechnungsempfänger:', $tblPersonFrom->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                        , 4),
                    new LayoutColumn(
                        new Panel('Gesamtbetrag:', $Content, Panel::PANEL_TYPE_SUCCESS,
                            ( $Button ? new PullRight($Button) : '' )
                        )
                        , 4),
                ))
            )
        );
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function frontendRemovePaid($Id = null)
    {

        $Stage = new Stage('Rechnung', 'den Offenen Posten hinzufügen');
        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Invoice', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice/View', new ChevronLeft(),
                array('Id' => $tblInvoice->getId())));
        }
        if (Invoice::useService()->changeInvoiceIsPaid($tblInvoice, false)) {
            return $Stage
            .new \SPHERE\Common\Frontend\Message\Repository\Success('Rechnung wurde wieder geöffnet')
            .new Redirect('/Billing/Bookkeeping/Balance/View', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblInvoice->getId()));
        }
        return $Stage
        .new Warning('Rechnung kann nicht wieder geöffnet werden')
        .new Redirect('/Billing/Bookkeeping/Invoice/View', Redirect::TIMEOUT_SUCCESS,
            array('Id' => $tblInvoice->getId()));
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function frontendRemoveStorno($Id = null)
    {

        $Stage = new Stage('Rechnung', 'den Offenen Posten hinzufügen');
        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Invoice', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice/View', new ChevronLeft(),
                array('Id' => $tblInvoice->getId())));
        }
        if (Invoice::useService()->changeInvoiceIsReversal($tblInvoice, false)) {
            return $Stage
            .new \SPHERE\Common\Frontend\Message\Repository\Success('Rechnung wurde wieder geöffnet')
            .new Redirect('/Billing/Bookkeeping/Balance/View', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblInvoice->getId()));
        }
        return $Stage
        .new Warning('Rechnung kann nicht wieder geöffnet werden')
        .new Redirect('/Billing/Bookkeeping/Invoice/View', Redirect::TIMEOUT_SUCCESS,
            array('Id' => $tblInvoice->getId()));
    }
}
