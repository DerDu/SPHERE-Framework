<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasket;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasketVerification;
use SPHERE\Application\Api\Billing\Sepa\ApiSepa;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\FolderClosed;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\RedirectScript;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Basket
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool $IsArchive
     *
     * @return Stage
     */
    public function frontendBasketList($IsArchive = false)
    {

        if($IsArchive){
            $Stage = new Stage('Archiv', 'Abrechnung');
            $Stage->setMessage('Zeigt alle archivierten Abrechnungen an');

            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft()));
            $Stage->setContent(
                ApiBasket::receiverService('')
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                ApiBasket::receiverContent($this->getBasketTable($IsArchive))
                            )
                        )
                    )
                )
            );
            return $Stage;
        }

        $Stage = new Stage('Abrechnung', 'Übersicht');
        $Stage->setMessage('Zeigt alle aktiven Abrechnungen an');

        $Stage->addButton((new Primary('Abrechnung hinzufügen', '#', new Plus()))
            ->ajaxPipelineOnClick(ApiBasket::pipelineOpenAddBasketModal('addBasket')));
        $Stage->addButton(new Standard('Archiv', '/Billing/Bookkeeping/Basket', new FolderClosed(), array('IsArchive' => true)));

        $Stage->setContent(
            ApiBasket::receiverService('')
            .ApiBasket::receiverModal('Erstellen einer neuen Abrechnung', 'addBasket')
            .ApiBasket::receiverModal('Bearbeiten der Abrechnung', 'editBasket')
            .ApiBasket::receiverModal('Entfernen der Abrechnung', 'deleteBasket')
            .ApiSepa::receiverModal()
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ApiBasket::receiverContent($this->getBasketTable($IsArchive))
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param bool $IsArchive
     *
     * @return TableData
     */
    public function getBasketTable($IsArchive = false)
    {

        // kommt manchmal als string
        if($IsArchive == 'false' || $IsArchive === false){
            $IsArchive = false;
        } else {
            $IsArchive = true;
        }

        $tblBasketAll = Basket::useService()->getBasketAll($IsArchive);
        $TableContent = array();
        if(!empty($tblBasketAll)){
            array_walk($tblBasketAll, function(TblBasket &$tblBasket) use (&$TableContent, $IsArchive){

                $Item['Number'] = $tblBasket->getId();
                $Item['Name'] = $tblBasket->getName().' '.new Muted(new Small($tblBasket->getDescription()));
//                $Item['CreateDate'] = $tblBasket->getCreateDate();

                $Item['TimeTarget'] = $tblBasket->getTargetTime();
                $Item['TimeBill'] = $tblBasket->getBillTime();
                $Item['Time'] = $tblBasket->getYear().'.'.$tblBasket->getMonth(true);

                $Item['Item'] = '';
                $tblItemList = Basket::useService()->getItemAllByBasket($tblBasket);
                $ItemArray = array();
                if($tblItemList){
                    foreach($tblItemList as $tblItem) {
                        $ItemArray[] = $tblItem->getName();
                    }
                    sort($ItemArray);
                    $Item['Item'] = implode('<br/>', $ItemArray);
                }

                $Item['Sepa'] = '';
                $Item['Datev'] = '';

                $Item['BasketType'] = '';
                if(($tblBasketType = $tblBasket->getTblBasketType())){
                    $Item['BasketType'] = $tblBasketType->getName();
                }

                if($tblBasket->getSepaDate()){
                    $Item['Sepa'] = new Small($tblBasket->getSepaUser().' - ('.$tblBasket->getSepaDate().')');
                }
                if($tblBasket->getDatevDate()){
                    $Item['Datev'] = new Small($tblBasket->getDatevUser().' - ('.$tblBasket->getDatevDate().')');
                }
                $TypeName = '';
                $DivisionName = '';
                if(($tblType = $tblBasket->getServiceTblType())){
                    $TypeName = $tblType->getName();
                }
                if(($tblDivision = $tblBasket->getServiceTblDivision())){
                    $DivisionName = $tblDivision->getDisplayName();
                }
                $Item['Filter'] = ($TypeName ? $TypeName.' ': '').($DivisionName ? 'Klasse '.$DivisionName: '');

//                $tblBasketVerification = Basket::useService()->getBasketVerificationAllByBasket($tblBasket);

                if($tblBasket->getIsDone()){
                    $Buttons = new Standard('', __NAMESPACE__.'/View', new EyeOpen(),
                        array('BasketId' => $tblBasket->getId()),
                        'Inhalt der Abrechnung');
                    if(!$IsArchive){
                        $Buttons .= $this->getDownloadButtons($tblBasket);
                        $Buttons .= (new Standard('', ApiBasket::getEndpoint(), new FolderClosed(), array(), 'Abrechnung in das Archiv schieben'))
                            ->ajaxPipelineOnClick(ApiBasket::pipelineBasketArchive($tblBasket->getId(), $IsArchive));
                    } else {
                        $Buttons .= (new Standard('', ApiBasket::getEndpoint(), new Repeat(), array(), 'Abrechnung aus dem Archiv holen'))
                            ->ajaxPipelineOnClick(ApiBasket::pipelineBasketArchive($tblBasket->getId(), $IsArchive));
                    }
                    $Item['Option'] = $Buttons;
                } else {
                    $Item['Option'] = (new Standard('', ApiBasket::getEndpoint(), new Edit(), array(),
                            'Abrechnung bearbeiten'))
                            ->ajaxPipelineOnClick(ApiBasket::pipelineOpenEditBasketModal('editBasket',
                                $tblBasket->getId()))
                        .new Primary('', __NAMESPACE__.'/View', new CogWheels(),
                            array('BasketId' => $tblBasket->getId()),
                            'Erstellung der Abrechnung')
                        .(new Standard('', ApiBasket::getEndpoint(), new Remove(), array(), 'Abrechnung entfernen'))
                            ->ajaxPipelineOnClick(ApiBasket::pipelineOpenDeleteBasketModal('deleteBasket',
                                $tblBasket->getId()));
                }


                array_push($TableContent, $Item);
            });
        }

        return new TableData($TableContent, null,
            array(
                'Number'     => 'Nr.',
                'Name'       => 'Name',
                'TimeTarget' => 'Fälligkeit',
                'Time'       => 'Abrechnungsmonat',
                'TimeBill'   => 'Rechnungsdatum',
                'Filter'     => 'Filter',
                'Item'       => 'Beitragsart(en)',
                'BasketType' => 'Typ',
                'Sepa'       => 'Letzter SEPA-Download',
                'Datev'      => 'Letzter DATEV-Download',
                'Option'     => ''
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(0)),
                    array('type' => 'de_date', 'targets' => array(2)),
                    array("orderable" => false, "targets" => -1),
                ),
                'order'      => array(
//                    array(1, 'desc'),
                    array(0, 'desc')
                ),
            )
        );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return string
     */
    private function getDownloadButtons(TblBasket $tblBasket)
    {

        $Buttons = '';
        $IsSepa = false;
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_SEPA))){
            $IsSepa = $tblSetting->getValue();
        }
        $IsDatev = false;
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV))){
            $IsDatev = $tblSetting->getValue();
        }
            // credit
        if(($tblBasketType = $tblBasket->getTblBasketType()) && $tblBasketType->getName() == TblBasketType::IDENT_AUSZAHLUNG){
            if($IsSepa){
                if($tblBasket->getDatevDate()){
                    $Buttons .= (new Standard('SEPA', '\Api\Billing\Sepa\Credit\Download', new Download(),
                        array('BasketId' => $tblBasket->getId()), 'SEPA Download'));
                } else {
                    $Buttons .= (new Primary('SEPA', '\Api\Billing\Sepa\Credit\Download', new Download(),
                        array('BasketId' => $tblBasket->getId()), 'SEPA Download'));
                }
            }
//            // Datev für diesen Vorgang erstmal verschoben
//            if($tblBasket->getDatevDate()){
//                $Buttons .= (new Standard('DATEV', '\Api\Billing\Datev\Download', new Download(),
//                    array('BasketId' => $tblBasket->getId()), 'DATEV Download'));
//            } else {
//                $Buttons .= (new Primary('DATEV', '\Api\Billing\Datev\Download', new Download(),
//                    array('BasketId' => $tblBasket->getId()), 'DATEV Download'));
//            }
        } else {
            // debit
            if($IsSepa){
                if($tblBasket->getSepaDate()){
                    $Buttons .= (new Standard('SEPA', ApiSepa::getEndpoint(), new Download(), array(), 'SEPA Download'))
                        ->ajaxPipelineOnClick(ApiSepa::pipelineOpenCauserModal($tblBasket->getId()));
                } else {
                    $Buttons .= (new Primary('SEPA', ApiSepa::getEndpoint(), new Download(), array(), 'SEPA Download'))
                        ->ajaxPipelineOnClick(ApiSepa::pipelineOpenCauserModal($tblBasket->getId()));
                }
            }
            if($IsDatev){
                if ($tblBasket->getDatevDate()){
                    $Buttons .= (new Standard('DATEV', '\Api\Billing\Datev\Download', new Download(),
                        array('BasketId' => $tblBasket->getId()), 'DATEV Download'));
                } else {
                    $Buttons .= (new Primary('DATEV', '\Api\Billing\Datev\Download', new Download(),
                        array('BasketId' => $tblBasket->getId()), 'DATEV Download'));
                }
            }
        }
        return $Buttons;
    }


    /**
     * @param null $BasketId
     *
     * @return Stage
     */
    public function frontendBasketView($BasketId = null)
    {

        // out of memory (Test with 3300 entrys)
        ini_set('memory_limit', '-1');
        $Stage = new Stage('Abrechnung', 'Inhalt');

        $PanelHead = $Time = $TargetTime = $BillTime = '';
        if($tblBasket = Basket::useService()->getBasketById($BasketId)){
            $PanelHead = new Bold($tblBasket->getName()).' '.$tblBasket->getDescription();
            $Time = $tblBasket->getMonth(true).'.'.$tblBasket->getYear();
            $TargetTime = $tblBasket->getTargetTime();
            $BillTime = $tblBasket->getBillTime();

            if($tblBasket->getIsArchive()){
                $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft(), array('IsArchive' => $tblBasket->getIsArchive())));
            } else {
                $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));
            }
        }

        $Stage->setContent(
            ApiBasketVerification::receiverModal('Bearbeiten')
            .ApiBasketVerification::receiverModal('Entfernen einer Zahlung', 'deleteDebtorSelection')
            .ApiBasketVerification::receiverService()
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('', new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(new InfoText('<span style="font-size: large">'.$PanelHead.'</span>'),
                                    6),
                                new LayoutColumn('Rechnungsdatum:'.new Container($BillTime), 2),
                                new LayoutColumn('Abrechnungszeitraum:'.new Container($Time), 2),
                                new LayoutColumn('Fälligkeitsdatum:'.new Container($TargetTime), 2),
                            )))), Panel::PANEL_TYPE_INFO)
                        )
                    )
                )
            )
            .ApiBasketVerification::receiverTableLayout($this->getBasketVerificationLayout($BasketId))
        );

        return $Stage;
    }

    /**
     * @param null $BasketId
     *
     * @return Layout|string
     */
    public function getBasketVerificationLayout($BasketId = null)
    {

        $tblBasket = Basket::useService()->getBasketById($BasketId);
        if(!$tblBasket){
            return new Danger('Abrechnung wurde nicht gefunden')
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $CountArray = array();
        $TableContent = array();
        $PanelContent = '';
        $IsDebtorNumberNeed = false;
        if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV)){
            if($tblSetting->getValue() == 1){
                $IsDebtorNumberNeed = true;
            }
        }
        if(($tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket))){
            $CountArray['AllCount'] = count($tblBasketVerificationList);
            $DebtorMiss = 0;
            $DebtorNumberMiss = 0;
            array_walk($tblBasketVerificationList,
                function(TblBasketVerification $tblBasketVerification) use (
                    &$TableContent,
                    $tblBasket,
                    &$CountArray,
                    &$DebtorNumberMiss,
                    &$DebtorMiss,
                    $IsDebtorNumberNeed
                ){
                    $Item['PersonCauser'] = '';
                    $Item['PersonDebtor'] = '';
                    $Item['Item'] = '';
                    $Item['Price'] = '';
                    $Item['Quantity'] = '';
                    $Item['Summary'] = '';
                    $DebtorWarningContent = '';
                    $IsShowPriceString = false;
                    if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())){
                        $Item['PersonCauser'] = $tblPersonCauser->getLastFirstName();
                        if($tblBasket->getIsDone()){
                            $Item['PersonDebtor'] = '';
                        } else {
                            $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor(
                                new DangerText($tblPersonCauser->getLastFirstName().' '.
                                    new ToolTip(new WarningIcon(), 'Beitragszahler nicht gefunden')),
                                $tblBasketVerification->getId());
                        }
                        $DebtorWarningContent = new DangerText(new WarningIcon());
                    }

                    $InfoDebtorNumber = '';
                    // new DebtorNumber
                    if($IsDebtorNumberNeed){
                        $InfoDebtorNumber = new ToolTip(new DangerText(new WarningIcon()), 'Debitoren-Nr. wird benötigt!');
                    }
                    if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())){
                        $IsShowPriceString = true;
                        // ignore FailMessage if not necessary
                        if(Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor)){
                            $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor($tblPersonDebtor->getLastFirstName(),
                                $tblBasketVerification->getId());
                            $DebtorWarningContent = '';
                        } else {
                            $DebtorNumberMiss++;
                            $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor($tblPersonDebtor->getLastFirstName().' '.$InfoDebtorNumber,
                                $tblBasketVerification->getId());
                            if(!$IsDebtorNumberNeed){
                                $DebtorWarningContent = '';
                            }
                        }

                    } else {
                        $DebtorMiss++;
                    }
                    $Item['PersonDebtorFail'] = ($DebtorWarningContent ? '<span hidden> 1 </span>' : '').ApiBasketVerification::receiverWarning($DebtorWarningContent, $tblBasketVerification->getId());

                    if(($tblItem = $tblBasketVerification->getServiceTblItem())){
                        $Item['Item'] = $tblItem->getName();
                    }
                    if(($Price = $tblBasketVerification->getPrice())){
                        if($IsShowPriceString){
                            // Hide Sort by Integer
                            $StringCount = strlen($Price) - 5;
                            $SortPrice = substr(str_replace(',', '', $Price), 0, $StringCount);
                            $Item['Price'] = '<span hidden>'.$SortPrice.'</span>'.ApiBasketVerification::receiverItemPrice($Price,
                                    $tblBasketVerification->getId());
                        } else {
                            $Item['Price'] = '---';
                        }
                        // Add ChangeButton to PersonDebtor
                        if(!$tblBasket->getIsDone()){
                            $Item['Price'] .= '&nbsp;'.new ToolTip((new Link('', ApiBasketVerification::getEndpoint(), new Pencil()))
                                    ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenEditDebtorSelectionModal($tblBasketVerification->getId()))
                                    , 'Preis ändern');
                        }
                    }
                    if(($Quantity = $tblBasketVerification->getQuantity())|| 0 === $tblBasketVerification->getQuantity()){
                        if($tblBasket->getIsDone()){
                            $Item['Quantity'] = $Quantity;
                        } else {
                            $Item['Quantity'] = ApiBasketVerification::receiverItemQuantity(
                                new Form(new FormGroup(new FormRow(new FormColumn(
                                    (new TextField('Quantity['.$tblBasketVerification->getId().']', '', ''))
                                        ->ajaxPipelineOnChange(ApiBasketVerification::pipelineChangeQuantity($tblBasketVerification->getId()))
                                ))))
                                , $tblBasketVerification->getId());
                            // setDefaultValue don't work -> use POST
                            $_POST['Quantity'][$tblBasketVerification->getId()] = $Quantity;
                        }
                    }
                    if(($Summary = $tblBasketVerification->getSummaryPrice())){
                        // Hide Sort by Integer
                        $StringCount = strlen($Summary) - 5;
                        $SortSummary = substr(str_replace(',', '', $Summary), 0, $StringCount);
                        $Item['Summary'] = '<span hidden>'.$SortSummary.'</span>'.ApiBasketVerification::receiverItemSummary($Summary,
                                $tblBasketVerification->getId());
                        if(!$IsShowPriceString){
                            $Item['Summary'] = '---';
                        }
                    }

                    // Add ChangeButton to PersonDebtor
                    if(!$tblBasket->getIsDone()){
                        $Item['PersonDebtor'] .= '&nbsp;'.new ToolTip((new Link('', ApiBasketVerification::getEndpoint(), new Pencil()))
                                ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenEditDebtorSelectionModal($tblBasketVerification->getId()))
                                , 'Beitragszahler ändern');
                    }

                    if($tblBasket->getIsDone()){
                        $Item['Option'] = '';
                    } else {
                        $Item['Option'] = (new Standard(new DangerText(new Disable()),
                            ApiBasketVerification::getEndpoint(), null
                        /*, array(),'Eintrag löschen'*/))
                            ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenDeleteDebtorSelectionModal('deleteDebtorSelection',
                                $tblBasketVerification->getId()));
                    }

                    array_push($TableContent, $Item);
                });
            $CountArray['DebtorNumberMiss'] = $DebtorNumberMiss;
            $CountArray['DebtorMiss'] = $DebtorMiss;

            $Title = '';
            $DebtorNumberMissCount = 0;
            $DebtorMissCount = 0;
            foreach($CountArray as $Key => $Count) {
                switch($Key) {
                    case 'AllCount':
                        $Title = 'Anzahl der Zahlungszuordnungen:';
                        break;
                    case 'DebtorNumberMiss':
                        $Title = 'Anzahl der fehlenden Debitoren-Nr.:';
                        if($Count > 0 && $IsDebtorNumberNeed){
                            $Title = new DangerText($Title);
                            $DebtorNumberMissCount = $Count;
                        }
                        break;
                    case 'DebtorMiss':
                        $Title = 'Anzahl der fehlenden Zahlungszuweisungen:';
                        if($Count > 0){
                            $Title = new DangerText($Title);
                            $DebtorMissCount = $Count;
                        }
                        break;
                }
                $PanelContent .= new Container(new Bold($Title).' '.$Count);
            }
            if($tblBasket->getIsDone()){
                $ButtonInvoice = '';
            } else {
                $ButtonInvoice = new Primary('Abrechnung starten', '/Billing/Bookkeeping/Basket/InvoiceLoad'
                    , null, array('BasketId' => $BasketId));
                $reloadButton = new Standard('', '', new Repeat(), array(), 'Kontrolle erneut starten');
                if($IsDebtorNumberNeed){
                    if($DebtorMissCount || $DebtorNumberMissCount){
                        $ButtonInvoice->setDisabled();
                        $ButtonInvoice .= $reloadButton;
//                    } else {
//                        $ButtonInvoice = (new Primary('Rechnungen erstellen', '/Billing/Bookkeeping/Basket/InvoiceLoad'
//                            , null, array('BasketId' => $BasketId)));
                    }
                } else {
                    if($DebtorMissCount){
                        $ButtonInvoice->setDisabled();
                        $ButtonInvoice .= $reloadButton;
//                    } else {
//                        $ButtonInvoice = (new Primary('Rechnungen erstellen', '/Billing/Bookkeeping/Basket/InvoiceLoad'
//                            , null, array('BasketId' => $BasketId)));
                    }
                }

            }

            $PanelContent = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $PanelContent
                    , 10),
                new LayoutColumn(
                    $ButtonInvoice, 2)
            ))));
        }
        $PanelCount = new Panel('Übersicht', $PanelContent);
        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        $PanelCount
                    ),
                    new LayoutColumn(
                        new TableData($TableContent, null,
                            array(
                                'PersonCauser'     => 'Beitragsverursacher',
                                'PersonDebtorFail' => 'Fehler',
                                'PersonDebtor'     => 'Beitragszahler',
                                'Item'             => 'Beitragsart',
                                'Price'            => 'Einzelpreis',
                                'Quantity'         => 'Anzahl',
                                'Summary'          => 'Gesamtpreis',
                                'Option'           => ''
                            ), array(
                                'columnDefs' => array(
                                    array('type'    => Consumer::useService()->getGermanSortBySetting(),
                                          'targets' => array(0, 2)
                                    ),
                                    array('type' => 'natural', 'targets' => array(4, 6)),
                                    array("orderable" => false, "targets" => array(5, -1)),
                                ),
                                'order'      => array(
                                    array(1, 'desc'),
                                    array(0, 'asc')
                                ),
                                // First column should not be with Tabindex
                                // solve the problem with responsive false
                                "responsive" => false,
                            )
                        )
                    ),
                ))
            )
        );
    }

    /**
     * @param string $BasketId
     *
     * @return Stage|string
     */
    public function frontendInvoiceLoad($BasketId = '')
    {

        $Stage = new Stage('Rechnungen', 'in Arbeit');

        if(!($tblBasket = Basket::useService()->getBasketById($BasketId))){
            return $Stage->setContent(new Danger('Die Abrechnung wird nicht mehr gefunden.'))
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        (new ProgressBar(0, 100, 0, 10))
                            ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)
                    ),
                    new LayoutColumn(
                        new RedirectScript('/Billing/Bookkeeping/Basket/DoInvoice', 0, array('BasketId' => $BasketId))
                    ),
                ))
            )
        ));
        return $Stage;
    }

    /**
     * @param string $BasketId
     *
     * @return Stage|string
     */
    public function frontendDoInvoice($BasketId = '')
    {

        $Stage = new Stage('Rechnungen', 'in Arbeit');

        if(!($tblBasket = Basket::useService()->getBasketById($BasketId))){
            return $Stage->setContent(new Danger('Die Abrechnung wird nicht mehr gefunden.'))
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Success('Rechnungen erstellt')
                    ),
                    new LayoutColumn(
                        new Container(Invoice::useService()->createInvoice($tblBasket))
                    ),
                ))
            )
        ));
        return $Stage;
    }
}
