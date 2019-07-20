<?php

namespace SPHERE\Application\Api\Billing\Bookkeeping;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
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
use SPHERE\System\Extension\Extension;

/**
 * Class ApiBasket
 * @package SPHERE\Application\Api\Billing\Accounting
 */
class ApiBasket extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Panel content
        $Dispatcher->registerMethod('getBasketTable');
        // Basket
        $Dispatcher->registerMethod('showAddBasket');
        $Dispatcher->registerMethod('saveAddBasket');
        $Dispatcher->registerMethod('showEditBasket');
        $Dispatcher->registerMethod('saveEditBasket');
        $Dispatcher->registerMethod('showDeleteBasket');
        $Dispatcher->registerMethod('deleteBasket');
        $Dispatcher->registerMethod('setArchiveBasket');

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
    public static function receiverContent($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockBasketTableContent');
    }

    /**
     * @param string $Content
     *
     * @return InlineReceiver
     */
    public static function receiverService($Content = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('ServiceBasket');
    }

    /**
     * @param string $Identifier
     * @param array  $Basket
     * @param array  $ErrorHelp
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddBasketModal($Identifier = '', $Basket = array(), $ErrorHelp = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'Basket'        => $Basket,
            'ErrorHelp'       => $ErrorHelp
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddBasket($Identifier = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();

        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     * @param array      $Basket
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditBasketModal(
        $Identifier = '',
        $BasketId = '',
        $Basket = array()
    ){

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId,
            'Basket'     => $Basket
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditBasket($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteBasketModal($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteBasket($Identifier = '', $BasketId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteBasket'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'BasketId'   => $BasketId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|string $BasketId
     * @param bool       $IsArchive
     *
     * @return Pipeline
     */
    public static function pipelineBasketArchive($BasketId = '', $IsArchive = false)
    {

        $Receiver = self::receiverService();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'setArchiveBasket'
        ));
        $Emitter->setPostPayload(array(
            'BasketId'  => $BasketId,
            'IsArchive' => $IsArchive,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param bool $IsArchive
     *
     * @return Pipeline
     */
    public static function pipelineRefreshTable($IsArchive = false)
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverContent(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getBasketTable'
        ));
        $Emitter->setPostPayload(array(
            'IsArchive' => $IsArchive
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
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverContent(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getBasketTable'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param bool $IsArchive
     *
     * @return string
     */
    public function getBasketTable($IsArchive = false)
    {

        return Basket::useFrontend()->getBasketTable($IsArchive);
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return IFormInterface $Form
     */
    public function formBasket($Identifier = '', $BasketId = '')
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
        if('' !== $BasketId){
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditBasket($Identifier,
                $BasketId));

            $Content = (new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new Panel('Abrechnung', $FormContentLeft)
                    , 6),
                new FormColumn(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(''))))
                    , 6),
                new FormColumn(
                    $SaveButton
                )
            )))))->disableSubmitAction();
        } else {
            // set Date to now
            $Now = new \DateTime();
            $Month = (int)$Now->format('m');
            if(!isset($_POST['Basket']['Year'])){
                $_POST['Basket']['Year'] = $Now->format('Y');
            }
            if(!isset($_POST['Basket']['Month'])){
                $_POST['Basket']['Month'] = $Month;
            }
            if(!isset($_POST['Basket']['DebtorPeriodType'])){
                if($tblDebtorPeriodType = Debtor::useService()->getDebtorPeriodTypeByName('Monatlich')){
                    $_POST['Basket']['DebtorPeriodType'] = $tblDebtorPeriodType->getId();
                } else {
                    $_POST['Basket']['DebtorPeriodType'] = '1';
                }
            }

            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddBasket($Identifier));
            $CheckboxList = '';
            if(($tblItemList = Item::useService()->getItemAll())){
                foreach($tblItemList as $tblItem) {
                    $CheckboxList .= new CheckBox('Basket[Item]['.$tblItem->getId().']', $tblItem->getName(),
                        $tblItem->getId());
                }
            }
            $tblDivisionList = array();
            if(($tblYear = Term::useService()->getYearByNow())){
                // Es sollte nur noch ein Jahr geben.
                $tblYear = current($tblYear);
                if(!($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))){
                    $tblDivisionList = array();
                }
            }
            $tblTypeList = array();
            if(($tblSchoolList = School::useService()->getSchoolAll())){
                foreach($tblSchoolList as $tblSchool){
                    $tblTypeList[] = $tblSchool->getServiceTblType();
                }
            }
            if(empty($tblTypeList)){
                $tblTypeList[] = Type::useService()->getTypeByName('Grundschule');
                $tblTypeList[] = Type::useService()->getTypeByName('Mittelschule / Oberschule');
                $tblTypeList[] = Type::useService()->getTypeByName('Gymnasium');
            }

            $FormContentLeft[] = (new SelectBox('Basket[Year]', 'Jahr', $YearList))->setRequired();
            $FormContentLeft[] = (new SelectBox('Basket[Month]', 'Monat', $MonthList, null, true, null))->setRequired();

            $PeriodRadioBox = array();
            if(($tblDebtorPeriodTypeAll = Debtor::useService()->getDebtorPeriodTypeAll())){
                foreach($tblDebtorPeriodTypeAll as $tblDebtorPeriodType){
                    $PeriodRadioBox[] = new RadioBox('Basket[DebtorPeriodType]', $tblDebtorPeriodType->getName(), $tblDebtorPeriodType->getId());
                }
            }

            $tblBasketType = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_AUSZAHLUNG);

            $Content = (new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new Panel('Abrechnung', $FormContentLeft)
                    , 6),
                new FormColumn(array(
                    new Panel('Beitragsarten '.new DangerText('*'), $CheckboxList),
                    new Panel('Erweiterte Personenfilterung', array(
                        new SelectBox('Basket[Division]', 'Klasse', array('{{ DisplayName }}' => $tblDivisionList)),
                        new SelectBox('Basket[SchoolType]', 'Schulart', array('{{ Name }}' => $tblTypeList))
                    )),
                    new Bold('Zahlungszeitraum '.new DangerText('*')),
                    new Panel('', $PeriodRadioBox),
                    new Panel('Auszahlung',
                        new CheckBox('Basket[BasketTypeId]', 'Auszahlung an Debitoren', $tblBasketType->getId())
                    ),
                ), 6),
                new FormColumn(
                    $SaveButton
                )
            )))))->disableSubmitAction();
        }
        /* @var Form $Content */
        return $Content;
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     * @param array  $Basket
     *
     * @return bool|Well
     */
    private function checkInputBasket(
        $Identifier = '',
        $BasketId = '',
        $Basket = array()
    ){

        $Error = false;
        $Warning = array();
        $form = $this->formBasket($Identifier, $BasketId);
        if(isset($Basket['Name']) && empty($Basket['Name'])){
            $form->setError('Basket[Name]', 'Bitte geben Sie einen Namen der Abrechnung an');
            $Error = true;
        } else {
            if(isset($Basket['Month']) && isset($Basket['Year'])){
                // Filtern doppelter Namen Mit Zeitangabe (Namen sind mit anderem Datum wiederverwendbar)
                if(($tblBasket = Basket::useService()->getBasketByName($Basket['Name'], $Basket['Month'],
                    $Basket['Year']))){
                    if($BasketId !== $tblBasket->getId()){
                        $form->setError('Basket[Name]',
                            'Bitte geben sie einen noch nicht vergebenen Name für die Abrechnung '.$Basket['Month'].'.'.$Basket['Year'].' an');
                        $Error = true;
                    }
                }
            } else {
                // Filtern doppelter Namen ohne Zeitangabe
                if($BasketId && ($tblBasketEdit = Basket::useService()->getBasketById($BasketId))){
                    $TargetMonth = $tblBasketEdit->getMonth();
                    $TargetYear = $tblBasketEdit->getYear();
                    if(($tblBasket = Basket::useService()->getBasketByName($Basket['Name'], $TargetMonth,
                        $TargetYear))){
                        if($BasketId !== $tblBasket->getId()){
                            $form->setError('Basket[Name]',
                                'Bitte geben sie einen noch nicht vergebenen Name für die Abrechnung an');
                            $Error = true;
                        }
                    }
                } else {
                    // fallback if error
                    if(($tblBasket = Basket::useService()->getBasketByName($Basket['Name']))){
                        $form->setError('Basket[Name]',
                            'Bitte geben sie einen noch nicht vergebenen Name für die Abrechnung an');
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
        if($BasketId == '' && !isset($Basket['Item'])){
            $form->setError('Basket[Item][2]', 'Test');
            $Warning[] = 'Es wird mindestens eine Beitragsart benötigt';
            $Error = true;
        }

        $WarningText = '';
        if(!empty($Warning)){
            $WarningText = new Warning(implode('<br/>', $Warning));
        }

        if($Error){
            return new Well($WarningText.$form);
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     * @param array $ErrorHelp
     *
     * @return string
     */
    public function showAddBasket($Identifier = '', $ErrorHelp = array())
    {

        if(!empty($ErrorHelp)){
            $ErrorHelp = new Warning(implode('<br/>', $ErrorHelp));
        } else {
            $ErrorHelp = '';
        }

        return $ErrorHelp.new Well($this->formBasket($Identifier));
    }

    /**
     * @param string $Identifier
     * @param array  $Basket
     *
     * @return string
     */
    public function saveAddBasket($Identifier = '', $Basket = array())
    {

        // Handle error's
        if($form = $this->checkInputBasket($Identifier, '', $Basket)){

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Basket']['Name'] = $Basket['Name'];
            $Global->POST['Basket']['Description'] = $Basket['Description'];
            $Global->POST['Basket']['Year'] = $Basket['Year'];
            $Global->POST['Basket']['Month'] = $Basket['Month'];
            $Global->POST['Basket']['TargetTime'] = $Basket['TargetTime'];
            $Global->POST['Basket']['BillTime'] = $Basket['BillTime'];
            $Global->POST['Basket']['Creditor'] = $Basket['Creditor'];
            $Global->POST['Basket']['Division'] = (isset($Basket['Division']) ? $Basket['Division'] : '');
            $Global->POST['Basket']['SchoolType'] = $Basket['SchoolType'];
            $Global->POST['Basket']['DebtorPeriodType'] = $Basket['DebtorPeriodType'];
            if(isset($Basket['BasketTypeId'])){
                $Global->POST['Basket']['BasketTypeId'] = $Basket['BasketTypeId'];
            }
            if(isset($Basket['Description']) && !empty($Basket['Description'])){
                foreach($Basket['Item'] as $ItemId) {
                    $Global->POST['Basket']['Item'][$ItemId] = $ItemId;
                }
            }
            $Global->savePost();
            return $form;
        }
        if(!isset($Basket['Division'])
            || !$Basket['Division']
            || !($tblDivision = Division::useService()->getDivisionById($Basket['Division']))){
            $tblDivision = null;
        }
        if(!isset($Basket['SchoolType'])
            || !$Basket['SchoolType']
            || !($tblType = Type::useService()->getTypeById($Basket['SchoolType']))){
            $tblType = null;
        }
        if(!($tblDebtorPeriodType = Debtor::useService()->getDebtorPeriodTypeById($Basket['DebtorPeriodType']))){
            $tblDebtorPeriodType = null;
        }
        if(isset($Basket['BasketTypeId'])){
            $tblBasketType = Basket::useService()->getBasketTypeById($Basket['BasketTypeId']);
        } else {
            $tblBasketType = Basket::useService()->getBasketTypeById(1);
        }

        $tblBasket = Basket::useService()->createBasket($Basket['Name'], $Basket['Description'], $Basket['Year']
            , $Basket['Month'], $Basket['TargetTime'], $Basket['BillTime'], $tblBasketType, $Basket['Creditor'], $tblDivision, $tblType,
            $tblDebtorPeriodType);
        $tblItemList = array();
        foreach($Basket['Item'] as $ItemId) {
            if(($tblItem = Item::useService()->getItemById($ItemId))){
                $tblItemList[] = $tblItem;
                $tblBasketItemList[] = Basket::useService()->createBasketItem($tblBasket, $tblItem);
            }
        }
        $ItemPriceFound = true;
        $MissingItemPriceList = array('Es existieren Preis-Varianten denen für das Fälligkeitsdatum '.$Basket['TargetTime']
            .' kein Preis hinterlegt ist.');
        $MissingItemPriceList[] = 'Bitte stellen Sie sicher, das alle Preisvarianten der Beitragsart gepflegt sind.';
        $MissingItemPriceList[] = '&nbsp;';
        $MissingItemPriceList[] = 'Beitragsart - Variante';
        $isCreate = false;
        $PersonMissing = array();
        if(!empty($tblItemList)){

            // Kontrolle, ob alle Varianten zum Fälligkeitsdatum ein gültigen Preis haben
            $DateNow = new \DateTime('now');
            /** @var TblItem $tblItemPriceControl */
            foreach($tblItemList as $tblItemPriceControl) {
                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItemPriceControl))){
                    foreach($tblItemVariantList as $tblItemVariant){
                        if(($tblItemCalculationList = Item::useService()->getItemCalculationByItemVariant($tblItemVariant))){
                            $IsCalculationTest = false;
                            foreach($tblItemCalculationList as $tblItemCalculation){
                                if($tblItemCalculation->getDateTo()
                                    && $tblItemCalculation->getDateFrom(true) <= $DateNow
                                    && $tblItemCalculation->getDateTo(true) >= $DateNow
                                    || !$tblItemCalculation->getDateTo()
                                    && $tblItemCalculation->getDateFrom(true) <= $DateNow
                                ){
                                    $IsCalculationTest = true;
                                    break;
                                }
                            }
                            if(!$IsCalculationTest){
                                $MissingItemPriceList[] = $tblItemPriceControl->getName().' - '.$tblItemVariant->getName();
                                $ItemPriceFound = false;
                            }
                        }
                    }
                }
            }
            // ungültige Preise hindern die Erstellung einer Abrechnung
            if(!$ItemPriceFound){
                Basket::useService()->destroyBasket($tblBasket);
                return self::pipelineOpenAddBasketModal($Identifier, $Basket, $MissingItemPriceList);
            }

            /** @var TblItem $tblItem */
            foreach($tblItemList as $tblItem) {
                $VerificationResult = Basket::useService()->createBasketVerificationBulk($tblBasket, $tblItem, $tblDivision, $tblType);
                if($isCreate == false){
                    $isCreate = $VerificationResult['IsCreate'];
                }
                if(!empty($VerificationResult))
                foreach($VerificationResult as $PersonId => $ErrorMessageList){
                    if(is_numeric($PersonId) && $tblPerson = Person::useService()->getPersonById($PersonId)){
                        $PersonMissing[] = new Bold($tblPerson->getLastFirstName()).':<br/>'.implode('<br/>', $ErrorMessageList);
                    }
                }
            }
        }
        // Abrechnung nicht gefüllt
        if(!$isCreate){
            Basket::useService()->destroyBasket($tblBasket);

            $ErrorHelp[] = 'Abrechnung kann nicht erstellt werden. Mögliche Ursachen:';
            $ErrorHelp[] = '- Es wurden im Abrechnungsmonat bereits für alle ausgewählten Beitragsarten
                und alle zutreffenden Personen eine Rechnung erstellt';
            $ErrorHelp[] = '- Aktuelle Filterung lässt keine Personen zur Abrechnung zu';
            $ErrorHelp[] = '- Es stehen keine aktiven Zahlungszuweisungen für das Fälligkeitsdatum bereit.';

            if(!empty($PersonMissing)){
                $ErrorHelp[] = '&nbsp;';
                $ErrorHelp[] = 'Folgende Zahlungszuweisungen wurden herausgefiltert:';
                $ErrorHelp = array_merge($ErrorHelp, $PersonMissing);
            }

            return self::pipelineOpenAddBasketModal($Identifier, $Basket, $ErrorHelp);
        }

        if($tblBasket){
            if(empty($PersonMissing)){
                return new Success('Abrechnung erfolgreich angelegt').self::pipelineCloseModal($Identifier);
            } else {
                return new Success('Abrechnung erfolgreich angelegt').
                    new Warning('Folgende Zahlungszuweisungen wurden herrausgefiltert:<br/>'
                    .implode('<br/>', $PersonMissing))
                    .ApiBasket::pipelineRefreshTable();
            }
        } else {
            return new Danger('Abrechnung konnte nicht gengelegt werden');
        }
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     * @param array      $Basket
     *
     * @return string
     */
    public function saveEditBasket(
        $Identifier = '',
        $BasketId = '',
        $Basket = array()
    ){

        // Handle error's
        if($form = $this->checkInputBasket($Identifier, $BasketId, $Basket)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Basket']['Name'] = $Basket['Name'];
            $Global->POST['Basket']['Description'] = $Basket['Description'];
            $Global->POST['Basket']['TargetTime'] = $Basket['TargetTime'];
            $Global->POST['Basket']['BillTime'] = $Basket['BillTime'];
            $Global->POST['Basket']['Creditor'] = $Basket['Creditor'];
            $Global->savePost();
            return $form;
        }

        $IsChange = false;
        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            $IsChange = Basket::useService()->changeBasket($tblBasket, $Basket['Name'], $Basket['Description']
                , $Basket['TargetTime'], $Basket['BillTime'], $Basket['Creditor']);
        }

        return ($IsChange
            ? new Success('Abrechnung erfolgreich geändert').self::pipelineCloseModal($Identifier)
            : new Danger('Abrechnung konnte nicht geändert werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $BasketId
     *
     * @return string
     */
    public function showEditBasket($Identifier = '', $BasketId = '')
    {

        if('' !== $BasketId && ($tblBasket = Basket::useService()->getBasketById($BasketId))){
            $Global = $this->getGlobal();
            $Global->POST['Basket']['Name'] = $tblBasket->getName();
            $Global->POST['Basket']['Description'] = $tblBasket->getDescription();
            $Global->POST['Basket']['Year'] = $tblBasket->getYear();
            $Global->POST['Basket']['Month'] = $tblBasket->getMonth();
            $Global->POST['Basket']['TargetTime'] = $tblBasket->getTargetTime();
            $Global->POST['Basket']['BillTime'] = $tblBasket->getBillTime();
            $Global->POST['Basket']['Creditor'] = ($tblBasket->getServiceTblCreditor() ? $tblBasket->getServiceTblCreditor()->getId() : '');
            $Global->savePost();
        }

        return new Well(self::formBasket($Identifier, $BasketId));
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return string
     */
    public function showDeleteBasket($Identifier = '', $BasketId = '')
    {

        $tblBasket = Basket::useService()->getBasketById($BasketId);
        if($tblBasket){

            $BasketVericationCount = 0;
            if(($tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket))){
                $BasketVericationCount = count($tblBasketVerificationList);
            }
            $ItemList = array();
            if(($tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket))){
                foreach($tblBasketItemList as $tblBasketItem) {
                    if(($tblItem = $tblBasketItem->getServiceTblItem())){
                        $ItemList[] = $tblItem->getName();
                    }
                }
            }
            $ItemString = implode(', ', $ItemList);

            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Anzahl der zu Fakturierende Beiträge: ', 4),
                new LayoutColumn(new Bold($BasketVericationCount), 8),
            ))));
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('zu Fakturierende Beitragsarten: ', 4),
                new LayoutColumn(new Bold($ItemString), 8),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Abrechnung '.new Bold($tblBasket->getName()).' wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteBasket($Identifier, $BasketId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Abrechnung wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $BasketId
     *
     * @return string
     */
    public function deleteBasket($Identifier = '', $BasketId = '')
    {

        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            Basket::useService()->destroyBasket($tblBasket);

            return new Success('Abrechnung wurde erfolgreich entfernt').self::pipelineCloseModal($Identifier);
        }
        return new Danger('Abrechnung konnte nicht entfernt werden');
    }

    /**
     * @param string $BasketId
     * @param bool   $IsArchive
     *
     * @return string
     */
    public function setArchiveBasket($BasketId = '', $IsArchive = false)
    {

        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            // Wert kommt als String an
            if('false' == $IsArchive){
                $IsArchiveOposite = true;
            } else {
                $IsArchiveOposite = false;
            }
            Basket::useService()->updateBasketArchive($tblBasket, $IsArchiveOposite);

            // Variable Archiv ist ein String, deswegen gleich das gegenteil vom ermittelten boolean
            return self::pipelineRefreshTable($IsArchive);
        }
        return '';
    }
}