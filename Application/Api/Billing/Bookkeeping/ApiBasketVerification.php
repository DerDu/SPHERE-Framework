<?php

namespace SPHERE\Application\Api\Billing\Bookkeeping;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtorSelection;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiBasketVerification
 * @package SPHERE\Application\Api\Billing\Bookkeeping
 */
class ApiBasketVerification extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload ColumnContent
        $Dispatcher->registerMethod('getWarning');
        $Dispatcher->registerMethod('getItemPrice');
        $Dispatcher->registerMethod('getItemSummary');
        $Dispatcher->registerMethod('getItemAllSummary');
        $Dispatcher->registerMethod('getDebtor');
        $Dispatcher->registerMethod('getTableLayout');

        //Quantity
        $Dispatcher->registerMethod('changeQuantity');
        // DebtorSelection
        $Dispatcher->registerMethod('showEditDebtorSelection');
        $Dispatcher->registerMethod('saveEditDebtorSelection');

        $Dispatcher->registerMethod('showDeleteBasketSelection');
        $Dispatcher->registerMethod('deleteDebtorSelection');


        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Header
     * @param string $Identifier
     *
     * @return ModalReceiver
     */
    public static function receiverModal($Header = '', $Identifier = '')
    {

        return (new ModalReceiver($Header, new Close()))->setIdentifier('Modal'.$Identifier);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverTableLayout($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BasketVerificationLayout');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverService()
    {

        return (new InlineReceiver())->setIdentifier('Service');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverWarning($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Warning'.$Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemPrice($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Price'.$Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemQuantity($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Quantity'.$Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemSummary($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Summary'.$Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverItemAllSummary($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Summary'.$Identifier);
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineChangeQuantity($BasketVerificationId = '')
    {

        $Receiver = self::receiverService();
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changeQuantity'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Emitter->setLoadingMessage('Speichern erfolgreich!');
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     * @param array  $Quantity
     *
     * @return string
     */
    public function changeQuantity($BasketVerificationId = '', $Quantity = array())
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            if($Quantity[$BasketVerificationId] && is_numeric($Quantity[$BasketVerificationId]) || '0' === $Quantity[$BasketVerificationId]){
                $Quantity = str_replace('-', '', $Quantity[$BasketVerificationId]);
                Basket::useService()->changeBasketVerificationInQuantity($tblBasketVerification, $Quantity);
            }
        }
        return ''.self::pipelineReloadSummary($BasketVerificationId);
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineReloadSummary($BasketVerificationId = '')
    {
        $Pipeline = new Pipeline(false);
        // reload columns
        // reload Summary column
        $Emitter = new ServerEmitter(self::receiverItemSummary('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemSummary'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        $Emitter = new ServerEmitter(self::receiverItemAllSummary('', 'SumAll'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemAllSummary'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $BasketId
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineReloadTable($BasketId = '', $Identifier = '')
    {
        $Pipeline = new Pipeline(false);
        // reload columns
        // reload Summary column
        $Emitter = new ServerEmitter(self::receiverTableLayout(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getTableLayout'
        ));
        $Emitter->setPostPayload(array(
            'BasketId' => $BasketId
        ));
        $Pipeline->appendEmitter($Emitter);
        if($Identifier){
            $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        }
        return $Pipeline;
    }

    public function getTableLayout($BasketId = '')
    {


        if($BasketId){
            return Basket::useFrontend()->getBasketVerificationLayout($BasketId);
        }
        return 'Fehler';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getItemPrice($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            return $tblBasketVerification->getPrice();
        }
        return '';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getWarning($BasketVerificationId)
    {

        $IsDebtorNumberNeed = false;
        if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV)){
            if($tblSetting->getValue() == 1){
                $IsDebtorNumberNeed = true;
            }
        }

        $DebtorWarningContent = new DangerText(new WarningIcon());
        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())){
                // ignore FailMessage if not necessary
                if(Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor)){
                    $DebtorWarningContent = '';
                } else {
                    if(!$IsDebtorNumberNeed){
                        $DebtorWarningContent = '';
                    }
                }

            }
        }
        return $DebtorWarningContent;
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getItemSummary($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            return $tblBasketVerification->getSummaryPrice();
        }
        return '';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getItemAllSummary($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            if(($tblBasket = $tblBasketVerification->getTblBasket())){
                return Basket::useService()->getItemAllSummery($tblBasket->getId());
            }
        }
        return 'Fehler';
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function getDebtor($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $InfoDebtorNumber = '';
            $IsDebtorNumberNeed = false;
            if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV)){
                if($tblSetting->getValue() == 1){
                    $IsDebtorNumberNeed = true;
                }
            }
            // new DebtorNumber
            if($IsDebtorNumberNeed){
                $InfoDebtorNumber = new ToolTip(new DangerText(new Disable()), 'Debitoren-Nr. wird benötigt!');
            }

            if(($tblPerson = $tblBasketVerification->getServiceTblPersonDebtor())){
                $DebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPerson);
                if($DebtorNumberList){
                    $InfoDebtorNumber = '';
                }
                return $tblPerson->getLastFirstName().' '.$InfoDebtorNumber;
            }
        }
        return new DangerText('Fehler!');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverDebtor($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Debtor'.$Identifier);
    }

    /**
     * @param       $BasketVerificationId
     * @param array $DebtorSelection
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDebtorSelectionModal(
        $BasketVerificationId,
        $DebtorSelection = array()
    ){

        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId,
            'DebtorSelection'      => $DebtorSelection
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditDebtorSelection(
        $BasketVerificationId = ''
    ){

        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDebtorSelectionModal($Identifier = '', $BasketVerificationId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteBasketSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'           => $Identifier,
            'BasketVerificationId' => $BasketVerificationId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDebtorSelection($Identifier = '', $BasketVerificationId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'           => $Identifier,
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineCloseDebtorSelectionModal($BasketVerificationId = '')
    {
        $Pipeline = new Pipeline();
        // reload columns
        // reload debtor column
        $Emitter = new ServerEmitter(self::receiverDebtor('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getDebtor'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        // reload Price column
        $Emitter = new ServerEmitter(self::receiverItemPrice('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemPrice'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        // reload Summary column
        $Emitter = new ServerEmitter(self::receiverItemSummary('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemSummary'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);
        // reload All Summary Header
        $Emitter = new ServerEmitter(self::receiverItemAllSummary('', 'SumAll'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemAllSummary'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        // reload Warning column
        $Emitter = new ServerEmitter(self::receiverWarning('', $BasketVerificationId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getWarning'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);
        // close Modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Form
     */
    public function formDebtorSelection($BasketVerificationId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditDebtorSelection($BasketVerificationId));

        $PaymentTypeList = array();
        $PaymentTypeList[] = new Balance();
        // post Type if not Exist

        $tblPaymentTypeAll = Balance::useService()->getPaymentTypeAll();
        foreach($tblPaymentTypeAll as $tblPaymentType) {
            $PaymentTypeList[$tblPaymentType->getId()] = $tblPaymentType->getName();
            // nicht mehr vorbefüllt
//            if($tblPaymentType->getName() == 'SEPA-Lastschrift'/*'Bar' // Test*/){
//                if(!isset($_POST['DebtorSelection']['PaymentType'])){
//                    $_POST['DebtorSelection']['PaymentType'] = $tblPaymentType->getId();
//                }
//            }
        }
        $RadioBoxListVariant = array();
        $PersonDebtorList = array();
        $SelectBoxDebtorList = array();
        $SelectBoxDebtorList[] = new Person();
        $tblBankReferenceList = array();
        $ItemName = '';
        $PersonTitle = '';
        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){

            if(($tblItemVariant = $tblBasketVerification->getServiceTblItemVariant())){
                $PostVariantId = $tblItemVariant->getId();
            } else {
                //get First Variant to Select
                $PostVariantId = '-1';
            }
            if(!isset($_POST['DebtorSelection']['Variant'])){
                $_POST['DebtorSelection']['Variant'] = $PostVariantId;
            }

            $tblItem = $tblBasketVerification->getServiceTblItem();
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            // ToDO Preisvariante nicht mehr eindeutig, da sich der Preis geändert haben kann.
//            if($tblItem && $tblPersonCauser){
//                if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPersonCauser, $tblItem))){
//                    /** @var TblDebtorSelection $tblDebtorSelection */
//                    $tblDebtorSelection = current($tblDebtorSelectionList);
//                    if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())) {
//                        $PostVariantId = $tblItemVariant->getId();
//                    }
//                }
//            }
            if($tblItem){
                $ItemName = $tblItem->getName();
                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))){
                    foreach($tblItemVariantList as $tblItemVariant) {
                        $PriceString = new DangerText('Nicht verfügbar');
                        $tblBasket = $tblBasketVerification->getTblBasket();
                        if(($tblItemCalculation = Item::useService()->getItemCalculationByDate($tblItemVariant, new \DateTime($tblBasket->getTargetTime())))){
                            $PriceString = $tblItemCalculation->getPriceString();
                        }

                        $RadioBoxListVariant[] = new RadioBox('DebtorSelection[Variant]',
                            $tblItemVariant->getName().': '.$PriceString, $tblItemVariant->getId());
                    }
                }
            }
            // gibt es immer (auch ohne Varianten)
            $RadioBoxListVariant[] = new RadioBox('DebtorSelection[Variant]',
                'Individuelle Preiseingabe:'.new TextField('DebtorSelection[Price]', '', ''), -1);
            if($tblPersonCauser){
                $PersonTitle = ' für '.new Bold($tblPersonCauser->getFirstName().' '.$tblPersonCauser->getLastName());
                $ObjectList = ApiDebtorSelection::getSelectBoxDebtor($tblPersonCauser);
                if(isset($ObjectList['SelectBoxDebtorList']) && $ObjectList['SelectBoxDebtorList']){
                    $SelectBoxDebtorList = $ObjectList['SelectBoxDebtorList'];
                }
                if(isset($ObjectList['PersonDebtorList']) && $ObjectList['PersonDebtorList']){
                    $PersonDebtorList = $ObjectList['PersonDebtorList'];
                }
            }

            if($tblPersonCauser){
                $tblBankReferenceList = Debtor::useService()->getBankReferenceByPerson($tblPersonCauser);
                if($tblBankReferenceList){
                    // Post first entry if PaymentType = SEPA-Lastschrift
                    if(isset($_POST['DebtorSelection']['PaymentType'])
                        && ($tblPaymentType = Balance::useService()->getPaymentTypeById($_POST['DebtorSelection']['PaymentType']))
                        && $tblPaymentType->getName() == 'SEPA-Lastschrift'){
                        if(!isset($_POST['DebtorSelection']['BankReference'])){
                            $_POST['DebtorSelection']['BankReference'] = $tblBankReferenceList[0]->getId();
                        }
                    }
                }
            }
        }

        // no BankAccount available
        if(!isset($_POST['DebtorSelection']['BankAccount'])){
            $_POST['DebtorSelection']['BankAccount'] = '-1';
        }
        $RadioBoxListBankAccount = ApiDebtorSelection::getBankAccountRadioBoxList($PersonDebtorList);

        return (new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(new Title($ItemName, $PersonTitle))
                ),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('DebtorSelection[PaymentType]', 'Zahlungsart',
                            $PaymentTypeList))->setRequired()
                        //ToDO Change follow Content
//                        ->ajaxPipelineOnChange()
                        , 6),
                    new FormColumn(
                        (new SelectBox('DebtorSelection[Debtor]', 'Beitragszahler',
                            $SelectBoxDebtorList, null, true, null))->setRequired()
                        //ToDO Change follow Content
//                        ->ajaxPipelineOnChange()
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        array(
                            new Bold('Varianten '.new DangerText('*')),
                            new Listing($RadioBoxListVariant)
                        )
                        , 6),
                    new FormColumn(
                        array(
                            new Bold('Konten '),
                            new Listing($RadioBoxListBankAccount)
                        )
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new Bold('Option'),
                        new Listing(array(
                            new CheckBox('DebtorSelection[SaveSetting]', 'dauerhaft speichern '
                                .new ToolTip(new InfoIcon(), 'Wird&nbsp;für&nbsp;zukünfitge&nbsp;Abrechnungen mit berücksichtigt.'), '1'),
                        ))
                        ), 6
                    ),
                    new FormColumn(
                        new SelectBox('DebtorSelection[BankReference]', 'Mandatsreferenznummer',
                            array('{{ReferenceNumber}} - (ab: {{ReferenceDate}}) {{Description}}' => $tblBankReferenceList))
                        , 6
                    )
                )),
                new FormRow(
                    new FormColumn(
                        $SaveButton
                    )
                )
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param string $BasketVerificationId
     * @param array  $DebtorSelection
     *
     * @return false|string|Form
     */
    private function checkInputDebtorSelection(
        $BasketVerificationId = '',
        $DebtorSelection = array()
    ){

        $Error = false;
        $Warning = '';
        $form = $this->formDebtorSelection($BasketVerificationId);
        if(isset($DebtorSelection['PaymentType']) && empty($DebtorSelection['PaymentType'])){
            $form->setError('DebtorSelection[PaymentType]', 'Bitte geben Sie eine Zahlungsart an');
            $Error = true;
        }
        if(isset($DebtorSelection['Variant']) && empty($DebtorSelection['Variant'])){
            $Warning .= new Danger('Bitte geben Sie eine Bezahlvariante an, steht keine zur Auswahl, stellen Sie bitte eine bei den Beitragsarten ein.');
            $form->setError('DebtorSelection[Variant]', 'Bitte geben Sie eine Bezahlvariante an');
            $Error = true;
        } elseif(isset($DebtorSelection['Variant']) && $DebtorSelection['Variant'] == '-1') {
            // is price empty (is requiered vor no Variant)
            if(isset($DebtorSelection['Price']) && empty($DebtorSelection['Price']) && $DebtorSelection['Price'] !== '0'){
                $Warning .= new Danger('Bitte geben Sie einen individuellen Preis an.');
//                $form->setError('DebtorSelection[Price]', 'Bitte geben Sie einen Individuellen Preis an');
                $Error = true;
            } elseif(isset($DebtorSelection['Price']) && !is_numeric(str_replace(',', '.',
                    $DebtorSelection['Price']))) {
                $Warning .= new Danger('Bitte geben Sie eine '.new Bold('Zahl').' als individuellen Preis an.');
//                $form->setError('DebtorSelection[Price]', 'Bitte geben Sie einen Individuellen Preis an');
                $Error = true;
            } elseif(isset($DebtorSelection['Price']) && preg_match('!-!', $DebtorSelection['Price'])){
                $Warning .= new Danger('Bitte geben Sie eine '.new Bold('Positive Zahl').' als individuellen Preis an.');
                $Error = true;
            }
        }
        if(isset($DebtorSelection['Debtor']) && empty($DebtorSelection['Debtor'])){
            $form->setError('DebtorSelection[Debtor]', 'Bitte geben Sie einen Beitragszahler an');
            $Error = true;
        }

        if(($tblPaymentType = Balance::useService()->getPaymentTypeById($DebtorSelection['PaymentType']))){
            $IsSepaAccountNeed = false;
            if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_SEPA)){
                if($tblSetting->getValue() == 1){
                    $IsSepaAccountNeed = true;
                }
            }
            if($tblPaymentType->getName() == 'SEPA-Lastschrift'){
                if($IsSepaAccountNeed){
                    if(isset($DebtorSelection['BankAccount']) && empty($DebtorSelection['BankAccount'])){
                        $Warning .= new Warning('Bitte geben Sie eine Bankverbindung an. (Eine Bankverbindung wird benötigt,
                         um ein SEPA-Lastschriftverfahren zu hinterlegen) Wahlweise andere Bezahlart auswählen.');
                        $form->setError('DebtorSelection[BankAccount]', 'Bitte geben Sie eine Bankverbindung an');
                        $Error = true;
                    } elseif(isset($DebtorSelection['BankAccount']) && $DebtorSelection['BankAccount'] == '-1') {
                        $Warning .= new Warning('Bitte geben Sie eine Bankverbindung an. (Eine Bankverbindung wird benötigt,
                         um ein SEPA-Lastschriftverfahren zu hinterlegen) Wahlweise andere Bezahlart auswählen.');
                        $form->setError('DebtorSelection[BankAccount]', 'Bitte geben Sie eine Bankverbindung an');
                        $Error = true;
                    }
                    if (isset($DebtorSelection['BankReference']) && empty($DebtorSelection['BankReference'])) {
                        $form->setError('DebtorSelection[BankReference]', 'Bitte geben Sie eine Mandatsreferenznummer an');
                        $Error = true;
                    } else {
                        $tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId);
                        if(($tblBankReference = Debtor::useService()->getBankReferenceById($DebtorSelection['BankReference']))){
                            $tblBasket = $tblBasketVerification->getTblBasket();
                            if($tblBasket && new \DateTime($tblBankReference->getReferenceDate()) > new \DateTime($tblBasket->getTargetTime())){
                                $form->setError('DebtorSelection[BankReference]', 'Die ausgewählte Mandatsreferenznummer 
                                ist zum akuellen Fälligkeitsdatum ('.$tblBasket->getTargetTime().') noch nicht verfügbar.');
                                $Error = true;
                            }
                        }
                    }
                }
            }
        }


        if($Error){
            // Debtor::useFrontend()->getPersonPanel($PersonId).
            return new Well($Warning.$form);
        }

        return $Error;
    }

    /**
     * @param       $BasketVerificationId
     * @param array $DebtorSelection
     *
     * @return false|Form|Danger|string
     */
    public function saveEditDebtorSelection(
        $BasketVerificationId,
        $DebtorSelection = array()
    ){

        // Handle error's
        if($form = $this->checkInputDebtorSelection($BasketVerificationId, $DebtorSelection)){

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['DebtorSelection']['PaymentType'] = $DebtorSelection['PaymentType'];
            $Global->POST['DebtorSelection']['Variant'] = $DebtorSelection['Variant'];
            $Global->POST['DebtorSelection']['Price'] = $DebtorSelection['Price'];
            $Global->POST['DebtorSelection']['Debtor'] = $DebtorSelection['Debtor'];
            $Global->POST['DebtorSelection']['BankAccount'] = $DebtorSelection['BankAccount'];
            $Global->POST['DebtorSelection']['BankReference'] = $DebtorSelection['BankReference'];
            $Global->POST['DebtorSelection']['SaveSetting'] = (isset($DebtorSelection['SaveSetting']) ? $DebtorSelection['SaveSetting']: '');
            $Global->savePost();
            return $form;
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId);
        if($tblBasketVerification){
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            $tblItem = $tblBasketVerification->getServiceTblItem();
        } else {
            $tblPersonCauser = false;
            $tblItem = false;
        }

        $tblPersonDebtor = Person::useService()->getPersonById($DebtorSelection['Debtor']);
        $tblPaymentType = Balance::useService()->getPaymentTypeById($DebtorSelection['PaymentType']);
        $tblItemVariant = Item::useService()->getItemVariantById($DebtorSelection['Variant']);
        $ItemPrice = '';
        $Value = '';
        // ItemPrice only if Variant is "-1"
        if($DebtorSelection['Variant'] == '-1'){
            $Value = $ItemPrice = $DebtorSelection['Price'];
        }
        if($tblPaymentType && $tblPaymentType->getName() == 'Bar'){
            $tblBankAccount = false;
            $tblBankReference = false;
        } elseif($tblPaymentType->getName() == 'SEPA-Überweisung') {
            $tblBankAccount = Debtor::useService()->getBankAccountById($DebtorSelection['BankAccount']);
            $tblBankReference = false;
        } elseif($tblPaymentType->getName() == 'SEPA-Lastschrift') {
            $tblBankAccount = Debtor::useService()->getBankAccountById($DebtorSelection['BankAccount']);
            $tblBankReference = Debtor::useService()->getBankReferenceById($DebtorSelection['BankReference']);
        }

        $IsChange = false;
        if($tblPersonCauser && $tblPersonDebtor && $tblPaymentType && $tblItem){

            // Change BasketVerification
            if($tblItemVariant){
                $tblBasket = $tblBasketVerification->getTblBasket();
                if(($tblItemCalculation = Item::useService()->getItemCalculationByDate($tblItemVariant, new \DateTime($tblBasket->getTargetTime())))){
                    $Value = $tblItemCalculation->getValue(true);
                }
            } else {
                $tblItemVariant = null;
            }
            // switch false to null
            ($tblBankAccount === false ? $tblBankAccount = null : '');
            ($tblBankReference === false ? $tblBankReference = null : '');
            if(Basket::useService()->changeBasketVerificationDebtor($tblBasketVerification, $tblPersonDebtor,
                $tblPaymentType, $Value, $tblItemVariant, $tblBankAccount, $tblBankReference)){
                // Add Person to PaymentGroup
                if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR))){
                    Group::useService()->addGroupPerson($tblGroup, $tblPersonDebtor);
                }
                $IsChange = true;
            }

            // edit DebtorSelection if Checkbox is checked
            if(isset($DebtorSelection['SaveSetting']) && $DebtorSelection['SaveSetting'] == 1
                && $IsChange){
                $tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId);
                $tblDebtorSelection = $tblBasketVerification->getServiceTblDebtorSelection();
                if($tblDebtorSelection){
                    $FromDate = $tblDebtorSelection->getFromDate();
                    if(!$FromDate){
                        $FromDate = (new \DateTime())->format('d.m.Y');
                    }
                    $ToDate = null;
                    // DebtorSelection on ID (Update current one)
                    Debtor::useService()->changeDebtorSelection($tblDebtorSelection, $tblPersonDebtor, $tblPaymentType,
                        $tblDebtorSelection->getTblDebtorPeriodType(), $FromDate, $ToDate,
                        ($tblItemVariant ? $tblItemVariant : null), $ItemPrice,
                        ($tblBankAccount ? $tblBankAccount : null),
                        ($tblBankReference ? $tblBankReference : null));

                } else {
                    // no DebtorSelection on ID (create new one)
                    $FromDate = (new \DateTime())->format('d.m.Y');
                    $ToDate = null;

                    //ToDO richtigen Zahlungszeitraum ziehen
                    $tblDebtorPeriodType = Debtor::useService()->getDebtorPeriodTypeByName('Monatlich');

                    $tblDebtorSelection = Debtor::useService()->createDebtorSelection($tblPersonCauser, $tblPersonDebtor,
                        $tblPaymentType, $tblItem, $tblDebtorPeriodType, $FromDate, $ToDate,
                        ($tblItemVariant ? $tblItemVariant : null),
                        $ItemPrice,
                        ($tblBankAccount ? $tblBankAccount : null),
                        ($tblBankReference ? $tblBankReference : null));
                    Basket::useService()->changeBasketVerificationInDebtorSelection($tblBasketVerification, $tblDebtorSelection);
                }
            }
        } else {
            return new Danger('Die Zuordnung des Beitragszahlers konnte nicht gengelegt werden (Person/Typ/Item)');
        }
        if($IsChange){
            return new Success('Die Zuordnung des Beitragszahlers wurde erfolgreich angelegt')
                .self::pipelineCloseDebtorSelectionModal($tblBasketVerification->getId());
        } else {
            return new Danger('Die Zuordnung des Beitragszahlers konnte nicht gengelegt werden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return string
     */
    public function showDeleteBasketSelection($Identifier = '', $BasketVerificationId = '')
    {

        $tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId);
        if($tblBasketVerification){

            $ItemName = '';
            $PersonCauser = '';
            $PersonDebtor = '';
            $Price = $tblBasketVerification->getPrice();
            $Quantity = $tblBasketVerification->getQuantity();
            $Summary = $tblBasketVerification->getSummaryPrice();
            if(($tblItem = $tblBasketVerification->getServiceTblItem())){
                $ItemName = $tblItem->getName();
            }
            if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())){
                $PersonCauser = $tblPersonCauser->getLastFirstName();
            }
            if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())){
                $PersonDebtor = $tblPersonDebtor->getLastFirstName();
            }
            //column width
            $left = 4;
            $right = 8;

            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragsart: ', $left),
                new LayoutColumn(new Bold($ItemName), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragsverursacher: ', $left),
                new LayoutColumn(new Bold($PersonCauser), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragszahler: ', $left),
                new LayoutColumn(new Bold($PersonDebtor), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Einzelpreis: ', $left),
                new LayoutColumn(new Bold($Price), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Anzahl: ', $left),
                new LayoutColumn(new Bold($Quantity), $right),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Gesamtpreis: ', $left),
                new LayoutColumn(new Bold($Summary), $right),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Zahlung wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteDebtorSelection($Identifier,
                                    $BasketVerificationId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Zahlungszuweisung wurde nicht gefunden');
        }
    }

    public function deleteDebtorSelection($Identifier = '', $BasketVerificationId = '')
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $tblBasket = $tblBasketVerification->getTblBasket();
            Basket::useService()->destroyBasketVerification($tblBasketVerification);
            if($tblBasket){
                return new Success('Abrechnung wurde erfolgreich entfernt').self::pipelineReloadTable($tblBasket->getId(),
                        $Identifier);
            }
        }
        return new Danger('Zahlung konnte nicht entfernt werden');
    }

    /**
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function showEditDebtorSelection($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $Global = $this->getGlobal();
            $tblPaymentType = $tblBasketVerification->getServiceTblPaymentType();
            ($tblPaymentType ? $Global->POST['DebtorSelection']['PaymentType'] = $tblPaymentType->getId() : '');
            // Preis als Individuell eintragen
//            $tblItemVariant = $tblBasketVerification->getServiceTblItemVariant();
//            ($tblItemVariant ? $_POST['DebtorSelection']['Variant'] = $tblItemVariant->getId() : '');
            $Value = $tblBasketVerification->getValue(true);
            ($Value !== '0,00' ? $Global->POST['DebtorSelection']['Price'] = $Value : '0,00');
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            ($tblPersonDebtor ? $Global->POST['DebtorSelection']['Debtor'] = $tblPersonDebtor->getId() : '');
            $tblBankAccount = $tblBasketVerification->getServiceTblBankAccount();
            ($tblBankAccount ? $Global->POST['DebtorSelection']['BankAccount'] = $tblBankAccount->getId()
                : $Global->POST['DebtorSelection']['BankAccount'] = '-1');
            $tblBankReference = $tblBasketVerification->getServiceTblBankReference();
            ($tblBankReference ? $Global->POST['DebtorSelection']['BankReference'] = $tblBankReference->getId() : '');
            $Global->savePost();
        }

//        // info of Handling with insert Data
//        $InfoMessage = new Info('Daten werden '.new Bold('für die aktuelle Abrechnung und in den Einstellungen').' übernommen '
//            .new ToolTip(new InfoIcon(), 'Es sind noch keine Daten vorhanden.'));
//        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
//            $tblItem = $tblBasketVerification->getServiceTblItem();
//            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
//            if($tblItem && $tblPersonCauser){
//                if(Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPersonCauser, $tblItem)){
//                    $InfoMessage = new Info('Daten werden '.new Bold('nur für die aktuelle Abrechnung').' übernommen '
//                        .new ToolTip(new InfoIcon(),
//                            'Daten für neue Abrechnung werden aus den vorhandenen Einstellungen gezogen.'));
//                }
//            }
//        }

        return new Well(self::formDebtorSelection($BasketVerificationId));
    }

}