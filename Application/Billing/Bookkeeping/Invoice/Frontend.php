<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Api\Billing\Invoice\ApiInvoiceIsPaid;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param array $Invoice
     *
     * @return Stage
     */
    public function frontendInvoiceView($Invoice = array())
    {
        if(empty($Invoice)){
            $Now = new \DateTime();
            $Invoice['Year'] = $Now->format('Y');
            $Invoice['Month'] = (int)$Now->format('m');
        }
        if(isset($Invoice['Year'])){
            $_POST['Invoice']['Year'] = $Invoice['Year'];
        }
        if(isset($Invoice['Month'])){
            $_POST['Invoice']['Month'] = $Invoice['Month'];
        }
        if(!isset($Invoice['BasketName'])){
            $Invoice['BasketName'] = '';
        }

        $Stage = new Stage('Rechnungsliste', 'Sicht Beitragszahler');
        $TableContent = Invoice::useService()->getInvoiceDebtorList($Invoice['Year'], $Invoice['Month'], $Invoice['BasketName'], true);

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn($this->formInvoiceFilter())
                ),
                empty($TableContent) ? null : new LayoutRow(new LayoutColumn(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen', '/Api/Billing/Invoice/Debtor/Download',
                        new Download(), array(
                            'Year'   => $Invoice['Year'],
                            'Month'   => $Invoice['Month'],
                            'BasketName' => isset($Invoice['BasketName']) ? $Invoice['BasketName'] : ''
                        ))
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null, array(
                            'DebtorPerson'    => 'Beitragszahler',
                            'DebtorNumber'    => 'Debitoren-Nr.',
                            'BasketName'      => 'Name der Abrechnung',
                            'CauserPerson'    => 'Beitragsverursacher',
                            'CauserIdent'     => 'Schülernummer',
                            'Time'            => 'Abrechnungs&shy;zeitraum',
                            'TargetTime'      => 'Fälligkeits&shy;datum',
                            'BillTime'        => 'Rechnungs&shy;datum',
                            'InvoiceNumber'   => 'Rechnungs&shy;nummer',
                            'PaymentType'     => 'Zahlungs&shy;art',
                            'BasketType'      => 'Typ',
                            'DisplaySumPrice' => 'Gesamtbetrag',
//                            'Option' => '',
                        ), array(
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0,3)),
                                array('type' => 'natural', 'targets' => array(1,4,7)),
                                array('type' => 'de_date', 'targets' => array(6)),
//                                array("orderable" => false, "targets"   => -1),
                            ),
                            'order'      => array(
//                            array(1, 'desc'),
                                array(7, 'desc')
                            ),
                            'responsive' => false,
                        ))
                    )
                )
            ))
        ));

        return $Stage;
    }

    /**
     * @param array $Invoice
     *
     * @return Stage
     */
    public function frontendInvoiceCauserView($Invoice = array())
    {
        if(empty($Invoice)){
            $Now = new \DateTime();
            $Invoice['Year'] = $Now->format('Y');
            $Invoice['Month'] = (int)$Now->format('m');
        }
        if(isset($Invoice['Year'])){
            $_POST['Invoice']['Year'] = $Invoice['Year'];
        }
        if(isset($Invoice['Month'])){
            $_POST['Invoice']['Month'] = $Invoice['Month'];
        }
        if(!isset($Invoice['BasketName'])){
            $Invoice['BasketName'] = '';
        }

        $Stage = new Stage('Rechnungsliste', 'Sicht Beitragsverursacher');
        $TableContent = Invoice::useService()->getInvoiceCauserList(
            $Invoice['Year'],
            $Invoice['Month'],
            isset($Invoice['BasketName']) ? $Invoice['BasketName'] : '',
            isset($Invoice['ItemName']) ? $Invoice['ItemName'] : '',
            true
        );

        $Stage->setContent(
            ApiInvoiceIsPaid::receiverService()
            .new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn($this->formInvoiceFilter(true))
                    ),
                    empty($TableContent) ? null : new LayoutRow(new LayoutColumn(
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen', '/Api/Billing/Invoice/Causer/Download',
                            new Download(), array(
                                'Year'   => $Invoice['Year'],
                                'Month'   => $Invoice['Month'],
                                'BasketName' => isset($Invoice['BasketName']) ? $Invoice['BasketName'] : '',
                                'ItemName' => isset($Invoice['ItemName']) ? $Invoice['ItemName'] : ''
                            ))
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'CauserPerson'  => 'Beitragsverursacher',
                                'CauserIdent'   => 'Schülernummer',
                                'Item'          => 'Beitragsarten',
                                'DebtorPerson'  => 'Beitragszahler',
                                'BasketName'    => 'Name der Abrechnung',
                                'Time'          => 'Abrechnungs&shy;zeitraum',
                                'TargetTime'    => 'Fälligkeits&shy;datum',
                                'BillTime'      => 'Rechnungs&shy;datum',
                                'InvoiceNumber' => 'Rechnungs&shy;nummer',
                                'PaymentType'   => 'Zahlungs&shy;art',
                                'ItemQuantity'  => 'Menge',
                                'ItemPrice'     => new ToolTip('EP', 'Einzelpreis'),
                                'ItemSumPrice'  => new ToolTip('GP', 'Gesamtpreis'),
                                'BasketType'    => 'Typ',
                                'IsPaid'        => 'Offene Posten',
//                                'Option' => '',
                            ), array(
                                'columnDefs' => array(
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0,3)),
                                    array('type' => 'natural', 'targets' => array(1,6)),
//                                    array('type' => 'de_date', 'targets' => array(2)),
                                    array("orderable" => false, "targets" => -1),
                                ),
                                'order'      => array(
//                            array(1, 'desc'),
                                    array(6, 'desc')
                                ),
                                'responsive' => false,
                            ))
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param bool $HasItemFilter
     *
     * @return Well
     */
    public function formInvoiceFilter($HasItemFilter = false)
    {

        $YearList = Invoice::useService()->getYearList(3, 1);
        $MonthList = Invoice::useService()->getMonthList();

        $BasketNameList = array();
        if(($tblBasketList = Basket::useService()->getBasketAll())){
            foreach($tblBasketList as $tblBasket) {
                $BasketNameList[] = $tblBasket->getName();
            }
            $BasketNameList = array_unique($BasketNameList);
        }

        $width = $HasItemFilter ? 3 : 4;
        $columns[] = new FormColumn(new SelectBox('Invoice[Year]', 'Jahr', $YearList), $width);
        $columns[] = new FormColumn(new SelectBox('Invoice[Month]', 'Monat', $MonthList, null, true, null), $width);
        $columns[] = new FormColumn(new AutoCompleter('Invoice[BasketName]', 'Name der Abrechnung', '', $BasketNameList), $width);
        if ($HasItemFilter) {
            $ItemNameList = array();
            if(($tblItemList = Item::useService()->getItemAll())){
                foreach($tblItemList as $tblItem) {
                    $ItemNameList[] = $tblItem->getName();
                }
                $ItemNameList = array_unique($ItemNameList);
            }

            $columns[] = new FormColumn(new AutoCompleter('Invoice[ItemName]', 'Beitragsart', '', $ItemNameList), $width);
        }

        return new Well(new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(new Title('Rechnungen filtern'))
                ),
                new FormRow($columns),
                new FormRow(array(
//                    new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn('')))), 2),
//                    new FormColumn(new Primary('Filter', new Filter()), 10),
                    new FormColumn(new Primary('Filter', new Filter())),
                ))
            ))
        ));
    }

    public function frontendUnPaid()
    {

        $Stage = new Stage('Offene Posten', 'Übersicht');
        $TableContent = Invoice::useService()->getInvoiceUpPaidList();

        $Stage->setContent(ApiInvoiceIsPaid::receiverService()
            .new Layout(
            new LayoutGroup(array(
                empty($TableContent) ? null : new LayoutRow(new LayoutColumn(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen', '/Api/Billing/Invoice/UnPaid/Download',
                        new Download())
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null, array(
                            'InvoiceNumber' => 'Rechnungsnummer',
                            'Time'          => 'Abrechnungs&shy;zeitraum',
                            'BasketName'    => 'Name der Abrechnung',
                            'CauserPerson'  => 'Beitragsverursacher',
                            'DebtorPerson'  => 'Beitragszahler',
                            'Item'          => 'Beitragsart',
                            'ItemQuantity'  => 'Anzahl',
                            'ItemPrice'     => 'Einzelpreis',
                            'ItemSumPrice'  => 'Gesamtpreis',
                            'IsPaid'        => 'Offene Posten'
                        ), array(
                            'columnDefs' => array(
                                array('type' => 'natural', 'targets' => array(0, 6, 7, 8)),
//                                array('type' => 'de_date', 'targets' => array(2)),
//                                array("orderable" => false, "targets"   => -1),
                            ),
                            'order'      => array(
//                            array(1, 'desc'),
                                array(0, 'desc')
                            ),
                            'responsive' => false,
                        ))
                    )
                )
            ))
        ));

        return $Stage;
    }
}
