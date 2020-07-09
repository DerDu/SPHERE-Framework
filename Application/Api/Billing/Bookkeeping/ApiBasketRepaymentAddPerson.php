<?php

namespace SPHERE\Application\Api\Billing\Bookkeeping;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtorSelection;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiBasketRepaymentAddPerson
 * @package SPHERE\Application\Api\Billing\Bookkeeping
 */
class ApiBasketRepaymentAddPerson extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // AddDebtorSelection
        $Dispatcher->registerMethod('showSearchModal');
        $Dispatcher->registerMethod('showSearch');
        $Dispatcher->registerMethod('addDebtorSelection');

        // reload Table
        $Dispatcher->registerMethod('getTableLayout');

        // Item Price
        $Dispatcher->registerMethod('showItemPrice');
        $Dispatcher->registerMethod('checkItemPrice');
        $Dispatcher->registerMethod('changeItemPrice');

        // BankAccount
        $Dispatcher->registerMethod('showEditBankAccount');
        $Dispatcher->registerMethod('saveEditBankAccount');

        // remove Entry
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
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverSearch($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('Block'.$Identifier);
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
    public static function receiverItemPrice($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('Price'.$Identifier);
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

    /**
     * @param string $BasketId
     *
     * @return Layout|string
     */
    public function getTableLayout($BasketId = '')
    {

        if($BasketId){
            return Basket::useFrontend()->getBasketVerificationRepaymentLayout($BasketId);
        }
        return 'Fehler';
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddPersonModal($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showSearchModal'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $searchIdentifier
     * @param string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineSearch($Identifier, $searchIdentifier = '', $BasketId = '')
    {

        $Receiver = self::receiverSearch('', $searchIdentifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showSearch'
        ));
        $PostArray = $_POST;
        $PostArray['Identifier'] = $Identifier;
        $PostArray['BasketId'] = $BasketId;
        $Emitter->setPostPayload($PostArray);
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineAddDebtorSelection($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverService();
//        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'addDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId' => $BasketId,
        ));
        $Pipeline->setLoadingMessage('Gutschrift hinzufügen');
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenItemPrice($BasketVerificationId = '')
    {

        $Receiver = self::receiverModal('', 'ItemPrice');
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showItemPrice'
        ));
        $Emitter->setPostPayload(array(
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
    public static function pipelineCheckItemPrice($BasketVerificationId = '')
    {

        $Receiver = self::receiverModal('', 'ItemPrice');
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'checkItemPrice'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketVerificationId
     * @param string $Price
     *
     * @return Pipeline
     */
    public static function pipelineChangePrice($BasketVerificationId = '', $Price = '')
    {

        $Receiver = self::receiverItemPrice('', $BasketVerificationId);
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changeItemPrice'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId,
            'Price'                => $Price,
        ));
        $Emitter->setLoadingMessage('Speichern erfolgreich!');
        $Pipeline->appendEmitter($Emitter);

        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', 'ItemPrice')))->getEmitter());

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
     * @param       $BasketVerificationId
     * @param array $DebtorSelection
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditBankAccountModal($BasketVerificationId, $DebtorSelection = array())
    {

        $Receiver = self::receiverModal('', 'BankAccount');
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditBankAccount'
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
    public static function pipelineSaveEditBankAccount($BasketVerificationId = '')
    {

        //Receiver der ausführt, was keinen einfluss auf das Modal haben soll (kein leeres Laden des Modal's)
        $Receiver = self::receiverService();
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditBankAccount'
        ));
        $Emitter->setPostPayload(array(
            'BasketVerificationId' => $BasketVerificationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return string
     */
    public function showSearchModal($Identifier = '', $BasketId = '')
    {
        // prefill price
        if(!isset($_POST['Price'])){
            $_POST['Price'] = 1;
        }

        return new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                (new TextField('Search', '', 'Beitragsverursacher suchen', new Search()))
                    ->ajaxPipelineOnKeyUp(self::pipelineSearch($Identifier, 'SearchPerson', $BasketId))
            , 8),
            new FormColumn(
                (new TextField('Price', '', 'Betrag der Gutschrift', new Search()))
                    ->ajaxPipelineOnKeyUp(self::pipelineSearch($Identifier, 'SearchPerson', $BasketId))
            , 4),
            new FormColumn(
                self::receiverSearch('', 'SearchPerson')
            ),
        ))));
    }

    /**
     * @param string $Identifier
     * @param string $Search
     * @param string $Price
     * @param string $BasketId
     *
     * @return string
     */
    public function showSearch($Identifier = '', $Search = '', $Price = '', $BasketId = '')
    {

        return self::loadPersonSearch($Identifier, $Search, $Price, $BasketId);
    }

    /**
     * @param string $Identifier
     * @param string $Search
     * @param string $Price
     * @param string $BasketId
     *
     * @return Warning|string
     */
    private static function loadPersonSearch($Identifier = '',$Search = '', $Price = '', $BasketId = '')
    {

        if ($Search != '' && strlen($Search) > 2 && $Price > 0) {
            // Kommen entfernen (Copy Paste überbleibsel)
            $Search = str_replace(',', '', $Search);
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                $tableData = array();
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$tableData, $BasketId){
                    $item['Select'] = '';
                    $item['FirstName'] = $tblPerson->getFirstSecondName();
                    $item['LastName'] = $tblPerson->getLastName();
                    $item['Address'] = ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : '';
                    $item['DebtorSelection'] = new Warning('Kein Beitragszahler (zur Beitragsart)', null, false, 2, 0);
                    $IsDebtorSelection = false;
                    if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
                        // Es kann nur eine Beitragsart hinterlegt sein
                        if(($tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket))){
                            $tblBasketItem = current($tblBasketItemList);
                            $tblItem = $tblBasketItem->getServiceTblItem();
                            if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson, $tblItem))){
                                foreach($tblDebtorSelectionList as $tblDebtorSelection){
                                    $IsDebtorSelection = true;
                                    $item['DebtorSelection'] = 'Zahlung vorhanden';
                                    $BankInfo = 'Keine Bankinforamtion gefunden!';
                                    if(($tblBankAccount = $tblDebtorSelection->getTblBankAccount())){
                                        $BankInfo = $tblBankAccount->getOwner().'<br/>'.$tblBankAccount->getBankName().'<br/>'.$tblBankAccount->getIBANFrontend();
                                    }
                                    if($tblPersonDebtor = $tblDebtorSelection->getServiceTblPersonDebtor()){
                                        $item['DebtorSelection'] = $tblPersonDebtor->getLastFirstName().' '.
                                            (new ToolTip(new InfoIcon(), htmlspecialchars($BankInfo)))->enableHtml();
                                    }
                                    $item['Select'] = new RadioBox('DebtorSelection', '&nbsp;', $tblDebtorSelection->getId());
                                    // Für jede Zahlungszuweisung einen eigenen Eintrag erzeugen
                                    array_push($tableData, $item);
                                }
                            }
                        }
                    }
                    // Eintrag wird nur angenommen, wenn keine Zahlungszuweisungen vorhanden sind.
                    // (Anzeige ohne Radiobox, damit der Nutzer sieht, was fehlt)
                    if(false === $IsDebtorSelection){
                        array_push($tableData, $item);
                    }
                });

                return new TableData(
                    $tableData,
                    null,
                    array(
                        'Select' => '',
                        'LastName' => 'Nachname',
                        'FirstName' => 'Vorname',
                        'Address' => 'Adresse',
                        'DebtorSelection' => 'Beitragszahler',
                    ),
                    array(
                        'order' => array(
                            array(1, 'asc'),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                ). (new Primary('Speichern', self::getEndpoint()))->ajaxPipelineOnClick(
                    self::pipelineAddDebtorSelection($Identifier, $BasketId)
                    );
            } else {
                $warning = new Warning('Es wurden keine entsprechenden Personen gefunden.', new Ban());
            }
        } elseif($Search == '' || strlen($Search) <= 2) {
            $warning = new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        } elseif($Price <= 0) {

            $warning = new Warning('Bitte geben Sie einen Betrag für die Gutschrift ein.', new Exclamation());
        }
        return $warning;
    }

    /**
     * @param string        $Identifier
     * @param string        $BasketId
     * @param string        $DebtorSelection
     * @param string        $Price
     *
     * @return Pipeline|string
     */
    public function addDebtorSelection($Identifier = '', $BasketId = '', $DebtorSelection = '', $Price = '')
    {

        $tblBasket = Basket::useService()->getBasketById($BasketId);
        $tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelection);
        $PriceCorrection = round(str_replace(',', '.', $Price), 2);
        if($PriceCorrection > 0){
            $tblBasketVerification = Basket::useService()->createBasketVerification($tblBasket, $tblDebtorSelection, $PriceCorrection);
            if($tblBasketVerification){
                // schließt das Modal nicht
                $Identifier = false;
                return self::pipelineReloadTable($tblBasket->getId(), $Identifier);
            } else {
                return self::pipelineReloadTable($tblBasket->getId(), $Identifier);
            }
        }
        return '';
    }

    /**
     * @param string $BasketVerificationId
     * @param string $Warning
     *
     * @return Well
     */
    public function showItemPrice($BasketVerificationId = '', $Warning = '')
    {

        $Causer = 'Beitragsverursacher nicht gefunden';
        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            if(!isset($_POST['Price'])){
                $_POST['Price'] = $tblBasketVerification->getValue(true);
            }
            if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())){
                $Causer = 'Beitragsverursacher: '.new Bold($tblPersonCauser->getLastFirstName());
            }


        }

        $form = new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new Panel($Causer, array(new TextField('Price', '', 'Betrag der Gutschrift')), Panel::PANEL_TYPE_INFO)
            ),
            new FormColumn(
                (new Primary('Speichern', self::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(self::pipelineCheckItemPrice($BasketVerificationId))
            )
        ))));
        $form->disableSubmitAction();
        if($Warning != ''){
            $form->setError('Price', $Warning);
        }

        return new Well($form);
    }

    /**
     * @param string $BasketVerificationId
     * @param string $Price
     *
     * @return string
     */
    public function checkItemPrice($BasketVerificationId = '', $Price = '')
    {

        $Price = round((float)trim(str_replace(',', '.', $Price)), 2);
        if($Price <= 0){
            $Warning = 'Eingabe muss eine Zahl und größer als 0,00 sein';
            return self::showItemPrice($BasketVerificationId, $Warning);
        }
        return new Success('Die Eingabe wurde erfolgreich gespeichert')
            .self::pipelineChangePrice($BasketVerificationId, $Price);
    }

    /**
     * @param string $BasketVerificationId
     * @param string $Price
     *
     * @return string
     */
    public function changeItemPrice($BasketVerificationId = '', $Price = '')
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            Basket::useService()->changeBasketVerificationInPrice($tblBasketVerification, $Price);
            $Price = Balance::useService()->getPriceString($Price);
            return $Price;
        }
        return new WarningText(new WarningIcon());
    }

    /**
     * @param string $BasketVerificationId
     *
     * @return Form
     */
    public function formBankAccount($BasketVerificationId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditBankAccount($BasketVerificationId));

        $PersonDebtorList = array();
        $SelectBoxDebtorList = array();
        $SelectBoxDebtorList[] = new Person();
        $ItemName = '';
        $PersonTitle = '';
        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){

            $tblItem = $tblBasketVerification->getServiceTblItem();
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            if($tblItem){
                $ItemName = $tblItem->getName();
            }
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
                        (new SelectBox('DebtorSelection[Debtor]', 'Beitragszahler',
                            $SelectBoxDebtorList, null, true, null))->setRequired()
                        , 6),
                    new FormColumn(
                        array(
                            new Bold('Konten '),
                            new Listing($RadioBoxListBankAccount)
                        ), 6),
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
     * @param $BasketVerificationId
     *
     * @return string
     */
    public function showEditBankAccount($BasketVerificationId)
    {

        if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
            $Global = $this->getGlobal();
//            $tblPaymentType = $tblBasketVerification->getServiceTblPaymentType();
//            ($tblPaymentType ? $Global->POST['DebtorSelection']['PaymentType'] = $tblPaymentType->getId() : '');
            $tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor();
            ($tblPersonDebtor ? $Global->POST['DebtorSelection']['Debtor'] = $tblPersonDebtor->getId() : '');
            $tblBankAccount = $tblBasketVerification->getServiceTblBankAccount();
            ($tblBankAccount ? $Global->POST['DebtorSelection']['BankAccount'] = $tblBankAccount->getId()
                : $Global->POST['DebtorSelection']['BankAccount'] = '-1');
//            $tblBankReference = $tblBasketVerification->getServiceTblBankReference();
//            ($tblBankReference ? $Global->POST['DebtorSelection']['BankReference'] = $tblBankReference->getId() : '');
            $Global->savePost();
        }

        return new Well(self::formBankAccount($BasketVerificationId));
    }

    /**
     * @param       $BasketVerificationId
     * @param array $DebtorSelection
     *
     * @return false|Form|Danger|string
     */
    public function saveEditBankAccount(
        $BasketVerificationId,
        $DebtorSelection = array()
    ){

        $tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId);
        if($tblBasketVerification){
            $tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser();
            $tblItem = $tblBasketVerification->getServiceTblItem();
        } else {
            $tblPersonCauser = false;
            $tblItem = false;
        }
        $tblBankAccount = Debtor::useService()->getBankAccountById($DebtorSelection['BankAccount']);
        $tblPersonDebtor = Person::useService()->getPersonById($DebtorSelection['Debtor']);
        if($tblPersonCauser && $tblPersonDebtor && $tblItem){
            // switch false to null
            ($tblBankAccount === false ? $tblBankAccount = null : '');
            (($tblPaymentType = $tblBasketVerification->getServiceTblPaymentType()) ? '' : $tblPaymentType = null);
            (($tblVerification = $tblBasketVerification->getServiceTblItemVariant()) ? '' : $tblVerification = null);
            $tblBankReference = null;
            // change basket

            $tblBasket = $tblBasketVerification->getTblBasket();

            Basket::useService()->changeBasketVerificationDebtor($tblBasketVerification, $tblPersonDebtor,
                $tblPaymentType, $tblBasketVerification->getValue(),
                $tblVerification, $tblBankAccount, $tblBankReference);
            return self::pipelineReloadTable($tblBasket->getId(), 'BankAccount');
        } else {
            return new Danger('Die Informationen konnten nicht gespeichert werden');
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
                            new Panel('Soll die Gutschrift wirklich entfernt werden?'
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

    /**
     * @param string $Identifier
     * @param string $BasketVerificationId
     *
     * @return Danger|string
     */
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
        return new Danger('Gutschrift konnte nicht entfernt werden');
    }
}