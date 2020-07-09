<?php

namespace SPHERE\Application\Api\Billing\Bookkeeping;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiBasketRepayment
 * @package SPHERE\Application\Api\Billing\Accounting
 */
class ApiBasketRepayment extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        // Basket
        $Dispatcher->registerMethod('showAddBasket');
        $Dispatcher->registerMethod('saveAddBasket');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Identifier
     * @param string $Type
     * @param array  $Basket
     * @param array  $ErrorHelp
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddBasketModal($Identifier = '', $Type = '', $Basket = array(), $ErrorHelp = array())
    {

        $Receiver = ApiBasket::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Type'       => $Type,
            'Basket'     => $Basket,
            'ErrorHelp'  => $ErrorHelp
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $Type
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddBasket($Identifier = '', $Type = '')
    {

        $Receiver = ApiBasket::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();

        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Type'       => $Type,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier = '')
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(ApiBasket::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $Type
     *
     * @return IFormInterface $Form
     */
    public function formBasket($Identifier = '', $Type = '')
    {

        // SelectBox content
        $YearList = Invoice::useService()->getYearList(1, 1);
        $MonthList = Invoice::useService()->getMonthList();
        $CreditorList = Creditor::useService()->getCreditorAll();

        $FormContentLeft[] = (new TextField('Basket[Name]', 'Name der Abrechnug', 'Name'))->setRequired();
        $FormContentLeft[] = new TextField('Basket[Description]', 'Beschreibung', 'Beschreibung');
        $FormContentLeft[] = (new SelectBox('Basket[Creditor]', 'Gläubiger', array('{{ Owner }} - {{ CreditorId }}' => $CreditorList)))->setRequired();
        $FormContentLeft[] = (new DatePicker('Basket[TargetTime]', '', 'Fälligkeitsdatum'))->setRequired();
        //Rechnungsdatum ist nur bei Datev Pflichtfeld
        $IsDatev = false;
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV))){
            $IsDatev = $tblSetting->getValue();
        }
        if($IsDatev){
            $FormContentLeft[] = (new DatePicker('Basket[BillTime]', '', 'Rechnungsdatum'))->setRequired();
        } else {
            $FormContentLeft[] = new DatePicker('Basket[BillTime]', '', 'Rechnungsdatum');
        }

        if(!isset($_POST['Basket']['Creditor'])
            && $CreditorList
            && count($CreditorList) == 1){
            $_POST['Basket']['Creditor'] = $CreditorList[0]->getId();
        }

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());

        // set Date to now
        $Now = new \DateTime();
        $Month = (int)$Now->format('m');
        if(!isset($_POST['Basket']['Year'])){
            $_POST['Basket']['Year'] = $Now->format('Y');
        }
        if(!isset($_POST['Basket']['Month'])){
            $_POST['Basket']['Month'] = $Month;
        }

        $FormContentLeft[] = (new SelectBox('Basket[Year]', 'Jahr', $YearList))->setRequired();
        $FormContentLeft[] = (new SelectBox('Basket[Month]', 'Monat', $MonthList, null, true, null))->setRequired();

        $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddBasket($Identifier, $Type));
        $Content = (new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new Panel($Type, $FormContentLeft, Panel::PANEL_TYPE_INFO)
                , 6),
            new FormColumn(
                $this->getRightFormColumn()
                , 6),
            new FormColumn(
                $SaveButton
            )
        )))))->disableSubmitAction();

        /* @var Form $Content */
        return $Content;
    }

    /**
     * @return array
     */
    private function getRightFormColumn()
    {

        $Account = '(Standard) ';
        $ToAccount = '(Standard) ';
        if(($tblSettingAccount = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_ACCOUNT))){
            $Account .= $tblSettingAccount->getValue();
        }
        if(($tblSettingToAccount = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_TO_ACCOUNT))){
            $ToAccount .= $tblSettingToAccount->getValue();
        }
        $tblItemList = Item::useService()->getItemAll();
        $SelectBox = new SelectBox('Basket[Item]', '', array('{{ Name }}' => $tblItemList));

        return array(
            new Panel('Beitragsart', array($SelectBox), Panel::PANEL_TYPE_INFO),
            new Panel('Fibu'.new DangerText('*'), array(
                new TextField('Basket[FibuAccount]', $Account, 'Konto'),
                new TextField('Basket[FibuToAccount]', $ToAccount, 'Gegenkonto'),
            ), Panel::PANEL_TYPE_INFO));

    }

    /**
     * @param string $Identifier
     * @param string $Type
     * @param string $BasketId
     * @param array  $Basket
     *
     * @return bool|Well
     */
    private function checkInputBasket(
        $Identifier = '',
        $Type = '',
        $BasketId = '',
        $Basket = array()
    ){

        $Error = false;
        $form = $this->formBasket($Identifier, $Type);
        if(isset($Basket['Name']) && empty($Basket['Name'])){
            $form->setError('Basket[Name]', 'Bitte geben Sie einen Namen der Abrechnung an');
            $Error = true;
        } else {
            if(isset($Basket['Month']) && isset($Basket['Year'])){
                // Filtern doppelter Namen Mit Zeitangabe (Namen sind mit anderem Datum wiederverwendbar)
                if (($tblBasket = Basket::useService()->getBasketByName($Basket['Name'], $Basket['Month'],
                    $Basket['Year']))){
                    if ($BasketId !== $tblBasket->getId()){
                        $form->setError('Basket[Name]',
                            'Bitte geben sie einen noch nicht vergebenen Name für die Abrechnung '.$Basket['Month'].'.'.$Basket['Year'].' an');
                        $Error = true;
                    }
                }
            }
        }
        if(isset($Basket['TargetTime']) && empty($Basket['TargetTime'])){
            $form->setError('Basket[TargetTime]', 'Bitte geben Sie ein Fälligkeitsdatum an');
            $Error = true;
        }
        //Rechnungsdatum ist nur bei Datev Pflichtfeld
        $IsDatev = false;
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV))){
            $IsDatev = $tblSetting->getValue();
        }
        if($IsDatev && isset($Basket['BillTime']) && empty($Basket['BillTime'])){
            $form->setError('Basket[BillTime]', 'Bitte geben Sie ein Rechnungsdatum an');
            $Error = true;
        }
        if(isset($Basket['Creditor']) && empty($Basket['Creditor'])){
            $form->setError('Basket[Creditor]', 'Bitte geben Sie einen Gläubiger an');
            $Error = true;
        }
        // Gutschrift mit Selectbox (eine)
        if($BasketId == '' && $Basket['Item'] === '0'){
            $form->setError('Basket[Item]', 'Es wird eine Beitragsart benötigt');
            $Error = true;
        }

        if($Error){
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     * @param string $Type
     * @param array  $ErrorHelp
     *
     * @return string
     */
    public function showAddBasket($Identifier = '', $Type = '', $ErrorHelp = array())
    {

        if(!empty($ErrorHelp)){
            $ErrorHelp = new Warning(implode('<br/>', $ErrorHelp));
        } else {
            $ErrorHelp = '';
        }

        return $ErrorHelp.new Well($this->formBasket($Identifier, $Type));
    }

    /**
     * @param string $Identifier
     * @param string $Type
     * @param array  $Basket
     *
     * @return string
     */
    public function saveAddBasket($Identifier = '', $Type = '', $Basket = array())
    {

        // Handle error's
        if($form = $this->checkInputBasket($Identifier, $Type, '', $Basket)){

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Basket']['Name'] = $Basket['Name'];
            $Global->POST['Basket']['Description'] = $Basket['Description'];
            $Global->POST['Basket']['Year'] = $Basket['Year'];
            $Global->POST['Basket']['Month'] = $Basket['Month'];
            $Global->POST['Basket']['TargetTime'] = $Basket['TargetTime'];
            $Global->POST['Basket']['BillTime'] = $Basket['BillTime'];
            $Global->POST['Basket']['Creditor'] = $Basket['Creditor'];

            $Global->POST['Basket']['Item'] = $Basket['Item'];
            $Global->POST['Basket']['FibuAccount'] = $Basket['FibuAccount'];
            $Global->POST['Basket']['FibuToAccount'] = $Basket['FibuToAccount'];
            $Global->savePost();
            return $form;
        }

        $tblBasketType = Basket::useService()->getBasketTypeByName($Type);

        $FibuAccount = $FibuToAccount = '';
        // Fibu Daten durch Eingaben (wenn getätigt) füllen sonnst wird der Standard Wert gezogen.
        if(isset($Basket['FibuAccount']) && $Basket['FibuAccount']) {
            $FibuAccount = $Basket['FibuAccount'];
        }
        if(isset($Basket['FibuToAccount']) && $Basket['FibuToAccount']) {
            $FibuToAccount = $Basket['FibuToAccount'];
        }
        $tblBasket = Basket::useService()->createBasket($Basket['Name'], $Basket['Description'], $Basket['Year']
            , $Basket['Month'], $Basket['TargetTime'], $Basket['BillTime'], $tblBasketType, $Basket['Creditor'], null, null,
            null, $FibuAccount, $FibuToAccount);

        $tblItem = Item::useService()->getItemById($Basket['Item']);
        if($tblItem){
            Basket::useService()->createBasketItem($tblBasket, $tblItem);
        }

        if($Type == TblBasketType::IDENT_GUTSCHRIFT){
            // Gutschriften sind ohne Zahlungszuweisungen fertig
            return new Success('Abrechnung erfolgreich angelegt').self::pipelineCloseModal($Identifier)
                .ApiBasket::pipelineRefreshTable();
        }

        if($tblBasket){
            return new Success('Abrechnung erfolgreich angelegt').self::pipelineCloseModal($Identifier)
            .ApiBasket::pipelineRefreshTable();
        } else {
            return new Danger('Abrechnung konnte nicht gengelegt werden');
        }
    }
}