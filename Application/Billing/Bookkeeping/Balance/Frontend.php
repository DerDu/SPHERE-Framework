<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBalance()
    {

        $Stage = new Stage();
        $Stage->setTitle('Posten');
        $Stage->setDescription('Offen');
//        new Backward();

//        $Stage->addButton(new Primary('Herunterladen', '/Billing/Bookkeeping/Export/All', new Download()));

        $tblInvoiceList = Invoice::useService()->getInvoiceByIsPaid(false);
        $TableContent = array();
        if ($tblInvoiceList) {

            array_walk($tblInvoiceList, function (TblInvoice $tblInvoice) use (&$TableContent) {
                $Content['FullName'] = $tblInvoice->getServiceTblPerson()->getFullName();
                $Content['InvoiceNumber'] = $tblInvoice->getInvoiceNumber();
                $Content['DebtorNumber'] = '';
                $Content['Reference'] = '';
                $Content['DebtorNumber'] = '';
                $Content['TargetTime'] = $tblInvoice->getTargetTime();
                $Content['Paid'] = Balance::useService()->getPriceString(Balance::useService()->getPaidFromInvoice($tblInvoice));
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
                $Content['Option'] = new Standard('', '/Billing/Bookkeeping/Balance/View', new Edit(),
                    array('Id' => $tblInvoice->getId()), 'Rechnung einsehen');

                array_push($TableContent, $Content);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new ListingTable().' Übersicht', 'der offenen Posten'),
                            ( empty( $TableContent ) ? new Warning('Keine offenen Rechnungen vorhanden') :
                                new TableData($TableContent, null,
                                    array('InvoiceNumber' => 'Rechnungs Nr.',
                                          'DebtorNumber'  => 'Debitoren Nr.',
                                          'FullName'      => 'Debitor',
                                          'Reference'     => 'Mandatsreferenz(en)',
                                          'TargetTime'    => 'Fällig am',
                                          'ItemList'      => 'Artikel',
                                          'Paid'          => 'Bezahlt',
                                          'Price'         => 'Gesamtpreis',
                                          'Option'        => ''),
                                    array(
                                        'order'      => array(
                                            array(0, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 4),
                                        )
                                    )
                                )
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
     * @param null $Payment
     *
     * @return Stage|string
     */
    public function frontendBalanceView($Id = null, $Payment = null)
    {

        $Stage = new Stage('Rechung', '');
        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Balance', new ChevronLeft()));
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
                $Content['Time'] = $tblPayment->getLastDate();
                $Content['Purpose'] = $tblPayment->getPurpose();
                $Content['Value'] = $tblPayment->getValueString();
                $Content['PaymentType'] = $tblPayment->getTblPaymentType()->getName();
                $Content['Option'] = new Standard('', '/Billing/Bookkeeping/Balance/Payment/Edit', new Edit(),
                    array('Id'        => $tblInvoice->getId(),
                          'PaymentId' => $tblPayment->getId()
                    ));
                array_push($TablePaid, $Content);
            });
        }

        $Form = $this->formPayment();
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $tblPaymenttype = Balance::useService()->getPaymentTypeByName('Bar');
        if ($tblPaymenttype && empty( $Payment )) {
            $Global = $this->getGlobal();
            $Global->POST['Payment']['Payment'] = $tblPaymenttype->getId();
            $Global->savePost();
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
                                          'PaymentType' => 'Bezahlart',
                                          'Option'      => '',),
                                    false) ),

                            new Well(
                                Balance::useService()->createPayment(
                                    $Form, $tblInvoice, $Payment))
                        ), 6)
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param            $SummaryPrice
     * @param bool       $Buttons
     *
     * @return string
     */
    public function layoutInvoice(TblInvoice $tblInvoice, $SummaryPrice, $Buttons = true)
    {

        $Content = array();
        $Content[] = 'Gesamtbetrag: '.new PullRight(Balance::useService()->getPriceString($SummaryPrice));
        if (( $PaidMoney = Balance::useService()->getPaidFromInvoice($tblInvoice) ) > 0) {
            $result = $SummaryPrice - $PaidMoney;
            if ($result >= 0) {
                $Content[] = new Bold('Fehlender Betrag: '.new PullRight(Balance::useService()->getPriceString($result)));
            } else {
                $Content[] = new Danger(new Bold('Betrag überschritten: '.new PullRight(Balance::useService()->getPriceString($result))));
            }
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
                            ( $Buttons ? new Standard('', '/Billing/Bookkeeping/Balance/Paid', new Check(),
                                    array('Id' => $tblInvoice->getId()), 'Bezahlen')
                                .new PullRight(
                                    new Standard('', '/Billing/Bookkeeping/Balance/Reversal', new Disable(),
                                    array('Id' => $tblInvoice->getId()), 'Stornieren')) :
                                '' )
                        )
                        , 4),
                ))
            )
        );
    }

    /**
     * @return Form
     */
    public function formPayment()
    {

        $tblPaymentTypeList = Balance::useService()->getPaymentTypeAll();
        if (!$tblPaymentTypeList) {
            $tblPaymentTypeList = array(new TblPaymentType());
        }
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Verwendungszweck', array(
                            new TextField('Payment[Purpose]', '', '', new CommodityItem())
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Betrag', array(
                            new TextField('Payment[Value]', '', '', new Money())
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Bezahlart', array(
                            new SelectBox('Payment[Payment]', '', array('{{ Name }}' => $tblPaymentTypeList))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                ))
            )
        );
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendBalancePaid($Id = null)
    {
        $Stage = new Stage('Bezahlen der Rechnung', 'Manuelles');

        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        if ($tblInvoice->getIsPaid()) {
            $Stage->setContent(new Warning('Rechnung bereits bezahlt'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        if ($tblInvoice->getIsReversal()) {
            $Stage->setContent(new Warning('Rechnung bereits storniert'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( Invoice::useService()->changeInvoiceIsPaid($tblInvoice, true) ?
                                new Success('Rechnung ist bezahlt.').new Redirect('/Billing/Bookkeeping/Balance/', Redirect::TIMEOUT_SUCCESS) :
                                new Warning('Rechnung konnte nicht bezahlt werden'
                                    .new Redirect('/Billing/Bookkeeping/Balance/', Redirect::TIMEOUT_SUCCESS))
                            )
                        )
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
    public function frontendBalanceReversal($Id = null)
    {
        $Stage = new Stage('Stornieren der Rechnung');

        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        if ($tblInvoice->getIsPaid()) {
            $Stage->setContent(new Warning('Rechnung bereits bezahlt'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        if ($tblInvoice->getIsReversal()) {
            $Stage->setContent(new Warning('Rechnung bereits storniert'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( Invoice::useService()->changeInvoiceIsReversal($tblInvoice, true) ?
                                new Success('Rechnung ist storniert.').new Redirect('/Billing/Bookkeeping/Balance/', Redirect::TIMEOUT_SUCCESS) :
                                new Warning('Rechnung konnte nicht storniert werden'
                                    .new Redirect('/Billing/Bookkeeping/Balance/', Redirect::TIMEOUT_SUCCESS))
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $PaymentId
     * @param null $Payment
     *
     * @return Stage|string
     */
    public function frontendPaymentEdit($Id = null, $PaymentId = null, $Payment = null)
    {
        $Stage = new Stage('Teilzahlung', 'Bearbeiten');
        $tblInvoice = ( $Id == null ? false : Invoice::useService()->getInvoiceById($Id) );
        if (!$tblInvoice) {
            $Stage->setContent(new Warning('Rechnung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
        $tblPayment = ( $Id == null ? false : Balance::useService()->getPaymentById($PaymentId) );
        if (!$tblPayment) {
            $Stage->setContent(new Warning('Teilzahlung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance/View', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblInvoice->getId()));
        }
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Balance/View', new ChevronLeft(),
            array('Id' => $tblInvoice->getId())));
        $Form = $this->formPayment();
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Global = $this->getGlobal();
        if ($Payment == null) {
            $Value = number_format($tblPayment->getValue(), 2);
            $Global->POST['Payment']['Value'] = str_replace('.', ',', $Value);
            $Global->POST['Payment']['Purpose'] = $tblPayment->getPurpose();
            $Global->POST['Payment']['Payment'] = $tblPayment->getTblPaymentType()->getId();
            $Global->savePost();
        }

        $SumPrice = Invoice::useService()->getInvoicePrice($tblInvoice);

        $Stage->setContent(
            $this->layoutInvoice($tblInvoice, $SumPrice, false)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Balance::useService()->changePayment(
                                    $Form, $tblInvoice, $tblPayment, $Payment
                                ))
                        )
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }
}
