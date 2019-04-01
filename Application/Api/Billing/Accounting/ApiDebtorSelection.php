<?php

namespace SPHERE\Application\Api\Billing\Accounting;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Accounting\Causer\Causer;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
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
use SPHERE\System\Extension\Extension;

/**
 * Class ApiDebtorSelection
 * @package SPHERE\Application\Api\Billing\Accounting
 */
class ApiDebtorSelection extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Panel content
        $Dispatcher->registerMethod('getItemPanelContent');
        // DebtorSelection
        $Dispatcher->registerMethod('showAddDebtorSelection');
        $Dispatcher->registerMethod('saveAddDebtorSelection');
        $Dispatcher->registerMethod('showEditDebtorSelection');
        $Dispatcher->registerMethod('saveEditDebtorSelection');
        $Dispatcher->registerMethod('showDeleteDebtorSelection');
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
    public static function receiverPanelContent($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockPanelContent'.$Identifier);
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     * @param array  $DebtorSelection
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddDebtorSelectionModal(
        $Identifier = '',
        $PersonId = '',
        $ItemId = '',
        $DebtorSelection = array()
    ){

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showAddDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'      => $Identifier,
            'PersonId'        => $PersonId,
            'ItemId'          => $ItemId,
            'DebtorSelection' => $DebtorSelection
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveAddDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'PersonId'   => $PersonId,
            'ItemId'     => $ItemId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     * @param array      $DebtorSelection
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDebtorSelectionModal(
        $Identifier = '',
        $PersonId = '',
        $ItemId = '',
        $DebtorSelectionId = '',
        $DebtorSelection = array()
    ){

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'ItemId'            => $ItemId,
            'DebtorSelectionId' => $DebtorSelectionId,
            'DebtorSelection'   => $DebtorSelection
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '',
        $DebtorSelectionId = ''
    ){

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'ItemId'            => $ItemId,
            'DebtorSelectionId' => $DebtorSelectionId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDebtorSelectionModal(
        $Identifier = '',
        $PersonId = '',
        $ItemId = '',
        $DebtorSelectionId = ''
    ){

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showDeleteDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'ItemId'            => $ItemId,
            'DebtorSelectionId' => $DebtorSelectionId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '',
        $DebtorSelectionId = ''
    ){

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteDebtorSelection'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'        => $Identifier,
            'PersonId'          => $PersonId,
            'ItemId'            => $ItemId,
            'DebtorSelectionId' => $DebtorSelectionId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier = '', $PersonId = '', $ItemId = '')
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverPanelContent('', $ItemId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemPanelContent'
        ));
        $Emitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'ItemId'   => $ItemId
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $ItemId
     *
     * @return string
     */
    public function getItemPanelContent($PersonId, $ItemId)
    {

        $IsOpen = true;
        return Causer::useFrontend()->getItemContent($PersonId, $ItemId, $IsOpen);
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     *
     * @return IFormInterface $Form
     */
    public function formDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '', $DebtorSelectionId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', self::getEndpoint(), new Save());
        if('' !== $DebtorSelectionId){
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditDebtorSelection($Identifier, $PersonId, $ItemId,
                $DebtorSelectionId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddDebtorSelection($Identifier, $PersonId, $ItemId));
        }

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

        //get First Variant to Select
        $PostVariantId = '-1';
        $ItemName = '';
        if(($tblItem = Item::useService()->getItemById($ItemId))){

            $ItemName = $tblItem->getName();
            // edit POST
            if($DebtorSelectionId && ($tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId))){
                if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())){
                    $PostVariantId = $tblItemVariant->getId();
                } else {
                    $PostVariantId = '-1';
                }
            } /* new POST */ elseif(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))) {
                $PostVariantId = $tblItemVariantList[0]->getId();
            }
        }
        if(!isset($_POST['DebtorSelection']['Variant'])){
            $_POST['DebtorSelection']['Variant'] = $PostVariantId;
        }

        $RadioBoxListVariant = array();
        if(($tblItem = Item::useService()->getItemById($ItemId))){
            if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))){
                foreach($tblItemVariantList as $tblItemVariant) {
                    $PriceString = new DangerText('Nicht verfügbar');
                    if(($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))){
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


        $PersonDebtorList = array();
        $SelectBoxDebtorList = array();
        $SelectBoxDebtorList[] = new Person();

        $PersonTitle = '';
        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $PersonTitle = ' für '.new Bold($tblPerson->getFirstName().' '.$tblPerson->getLastName());
            $ObjectList = self::getSelectBoxDebtor($tblPerson);
            if(isset($ObjectList['SelectBoxDebtorList']) && $ObjectList['SelectBoxDebtorList']){
                $SelectBoxDebtorList = $ObjectList['SelectBoxDebtorList'];
            }
            if(isset($ObjectList['PersonDebtorList']) && $ObjectList['PersonDebtorList']){
                $PersonDebtorList = $ObjectList['PersonDebtorList'];
            }
        }

        // no BankAccount available
        if(!isset($_POST['DebtorSelection']['BankAccount'])){
            $_POST['DebtorSelection']['BankAccount'] = '-1';
        }
        $RadioBoxListBankAccount = self::getBankAccountRadioBoxList($PersonDebtorList);

        $tblBankReferenceList = Debtor::useService()->getBankReferenceByPerson($tblPerson);
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
                        (new SelectBox('DebtorSelection[Debtor]', 'Bezahler',
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
                    new FormColumn(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(''))))
                        , 6
                    ),
                    new FormColumn(
                        new SelectBox('DebtorSelection[BankReference]', 'Mandatsreferenznummer',
                            array('{{ReferenceNumber}} - (ab: {{ReferenceDate}})' => $tblBankReferenceList))
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
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private static function getDebtorNumberByPerson(TblPerson $tblPerson)
    {

        $IsDebtorNumberNeed = false;
        if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED)){
            if($tblSetting->getValue() == 1){
                $IsDebtorNumberNeed = true;
            }
        }
        $DeborNumber = '';
        if($IsDebtorNumberNeed){
            $DeborNumber = '(keine Debitoren-Nr.)';
        }
        // change warning if necessary to "not in PaymentGroup"
        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR))){
            if(!Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                $DeborNumber = '(kein Bezahler)';
            }
        }
        if(($tblDebtorNumberList = Debtor::useService()->getDebtorNumberByPerson($tblPerson))){
            $DebtorNumberList = array();
            foreach($tblDebtorNumberList as $tblDebtorNumber) {
                $DebtorNumberList[] = $tblDebtorNumber->getDebtorNumber();
            }
            $DeborNumber = implode(', ', $DebtorNumberList);
            $DeborNumber = '('.$DeborNumber.')';
        }
        return $DeborNumber;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array array('SelectBoxDebtorList' => SelectBoxContent[]; 'PersonDebtorList' => TblPerson[])
     */
    public static function getSelectBoxDebtor(TblPerson $tblPerson)
    {

        if(($tblRelationshipType = Relationship::useService()->getTypeByName('Beitragszahler'))){
            if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                $tblRelationshipType))){
                foreach($tblRelationshipList as $tblRelationship) {
                    if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPerson->getId()){
                        $DeborNumber = self::getDebtorNumberByPerson($tblPersonRel);
                        $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName().' '.$DeborNumber;
                        $PersonDebtorList[] = $tblPersonRel;
                    }
                }
            }
        }
        if(($tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt'))){
            if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                $tblRelationshipType))){
                foreach($tblRelationshipList as $tblRelationship) {
                    if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPerson->getId()){
                        $DeborNumber = self::getDebtorNumberByPerson($tblPersonRel);
                        $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName().' '.$DeborNumber;
                        $PersonDebtorList[] = $tblPersonRel;
                    }
                }
            }
        }
        if(($tblRelationshipType = Relationship::useService()->getTypeByName('Bevollmächtigt'))){
            if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                $tblRelationshipType))){
                foreach($tblRelationshipList as $tblRelationship) {
                    if(($tblPersonRel = $tblRelationship->getServiceTblPersonFrom()) && $tblPersonRel->getId() !== $tblPerson->getId()){
                        $DeborNumber = self::getDebtorNumberByPerson($tblPersonRel);
                        $SelectBoxDebtorList[$tblPersonRel->getId()] = $tblPersonRel->getLastFirstName().' '.$DeborNumber;
                        $PersonDebtorList[] = $tblPersonRel;
                    }
                }
            }
        }
        // Beitragsverursacher steht immer am Schluss

        $DeborNumber = self::getDebtorNumberByPerson($tblPerson);
            // $SelectBoxDebtorList => Personen, die zur Auswahl als Debitor stehen
        $SelectBoxDebtorList[$tblPerson->getId()] = $tblPerson->getLastFirstName().' '.$DeborNumber;
            // $PersonDebtorList = Personen von denen Kontoinformationen geholt werden
        $PersonDebtorList[] = $tblPerson;

        return array('SelectBoxDebtorList' => $SelectBoxDebtorList,
                     'PersonDebtorList' => (isset($PersonDebtorList) ? $PersonDebtorList : false));
    }

    /**
     * @param bool|TblPerson[] $PersonDebtorList
     *
     * @return RadioBox[]
     */
    public static function getBankAccountRadioBoxList($PersonDebtorList)
    {

        $PostBankAccountId = false;
        $RadioBoxListBankAccount['-1'] = new RadioBox('DebtorSelection[BankAccount]'
            , 'keine Bankverbindung', -1);
        if(!empty($PersonDebtorList)){
            /** @var TblPerson $PersonDebtor */
            foreach($PersonDebtorList as $PersonDebtor) {
                if(($tblBankAccountList = Debtor::useService()->getBankAccountAllByPerson($PersonDebtor))){
                    foreach($tblBankAccountList as $tblBankAccount) {
                        if(!$PostBankAccountId){
                            $PostBankAccountId = $tblBankAccount->getId();
                            if(isset($_POST['DebtorSelection']['PaymentType'])
                                && !isset($_POST['DebtorSelection']['BankAccount'])
                                && ($tblPaymentType = Balance::useService()->getPaymentTypeById($_POST['DebtorSelection']['PaymentType']))
                                && $tblPaymentType->getName() == 'SEPA-Lastschrift'){
                                // override Post with first found BankAccount
                                $_POST['DebtorSelection']['BankAccount'] = $PostBankAccountId;
                            }
                        }
                        $RadioBoxListBankAccount[$tblBankAccount->getId()] = new RadioBox('DebtorSelection[BankAccount]'
                            , $tblBankAccount->getOwner().'<br/>'.$tblBankAccount->getBankName().'<br/>'
                            .$tblBankAccount->getIBANFrontend()
                            , $tblBankAccount->getId());
                    }
                }
            }
        }
        return $RadioBoxListBankAccount;
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     * @param string $DebtorSelectionId
     * @param array  $DebtorSelection
     *
     * @return false|string|Form
     */
    private function checkInputDebtorSelection(
        $Identifier = '',
        $PersonId = '',
        $ItemId = '',
        $DebtorSelectionId = '',
        $DebtorSelection = array()
    ){

        $Error = false;
        $Warning = '';
        $form = $this->formDebtorSelection($Identifier, $PersonId, $ItemId, $DebtorSelectionId);
        if(isset($DebtorSelection['PaymentType']) && empty($DebtorSelection['PaymentType'])){
            $form->setError('DebtorSelection[PaymentType]', 'Bitte geben Sie eine Zahlungsart an');
            $Error = true;
        }
        if(isset($DebtorSelection['Variant']) && empty($DebtorSelection['Variant'])){
            $Warning .= new Warning('Bitte geben Sie eine Bezahlvariante an, steht keine zur Auswahl, stellen Sie bitte eine bei den Beitragsarten ein.');
            $form->setError('DebtorSelection[Variant]', 'Bitte geben Sie eine Bezahlvariante an');
            $Error = true;
        } elseif(isset($DebtorSelection['Variant']) && $DebtorSelection['Variant'] == '-1') {
            // is price empty (is requiered vor no Variant)
            if(isset($DebtorSelection['Price']) && empty($DebtorSelection['Price']) && $DebtorSelection['Price'] != '0'){
                $Warning .= new Warning('Bitte geben Sie einen individuellen Preis an');
//                $form->setError('DebtorSelection[Price]', 'Bitte geben Sie einen Individuellen Preis an');
                $Error = true;
            } elseif(isset($DebtorSelection['Price']) && !is_numeric(str_replace(',', '.',
                    $DebtorSelection['Price']))) {
                $Warning .= new Warning('Bitte geben Sie eine '.new Bold('Zahl').' als individuellen Preis an');
//                $form->setError('DebtorSelection[Price]', 'Bitte geben Sie einen Individuellen Preis an');
                $Error = true;
            } elseif(isset($DebtorSelection['Price']) && preg_match('!-!', $DebtorSelection['Price'])){
                $Warning .= new Danger('Bitte geben Sie eine '.new Bold('Positive Zahl').' als individuellen Preis an.');
                $Error = true;
            }
        }
        if(isset($DebtorSelection['Debtor']) && empty($DebtorSelection['Debtor'])){
            $form->setError('DebtorSelection[Debtor]', 'Bitte geben Sie einen Bezahler an');
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
                        $Warning .= new Warning('Bitte geben sie eine Bankverbindung an. (Eine Bankverbindung wird benötigt,
                         um ein SEPA-Lastschriftverfahren zu hinterlegen) Wahlweise andere Bezahlart auswählen.');
                        $form->setError('DebtorSelection[BankAccount]', 'Bitte geben Sie eine Bankverbindung an');
                        $Error = true;
                    } elseif(isset($DebtorSelection['BankAccount']) && $DebtorSelection['BankAccount'] == '-1') {
                        $Warning .= new Warning('Bitte geben sie eine Bankverbindung an. (Eine Bankverbindung wird benötigt,
                         um ein SEPA-Lastschriftverfahren zu hinterlegen) Wahlweise andere Bezahlart auswählen.');
                        $form->setError('DebtorSelection[BankAccount]', 'Bitte geben Sie eine Bankverbindung an');
                        $Error = true;
                    }
                    if (isset($DebtorSelection['BankReference']) && empty($DebtorSelection['BankReference'])) {
                        $form->setError('DebtorSelection[BankReference]', 'Bitte geben Sie eine Mandatsreferenznummer an');
                        $Error = true;
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
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     *
     * @return string
     */
    public function showAddDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '')
    {

        return new Well($this->formDebtorSelection($Identifier,
            $PersonId, $ItemId));
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     * @param array  $DebtorSelection
     *
     * @return string
     */
    public function saveAddDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '', $DebtorSelection = array())
    {

        // Handle error's
        if($form = $this->checkInputDebtorSelection($Identifier, $PersonId, $ItemId, '', $DebtorSelection)){

            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['DebtorSelection']['PaymentType'] = $DebtorSelection['PaymentType'];
            $Global->POST['DebtorSelection']['Variant'] = $DebtorSelection['Variant'];
            $Global->POST['DebtorSelection']['Price'] = $DebtorSelection['Price'];
            $Global->POST['DebtorSelection']['Debtor'] = $DebtorSelection['Debtor'];
            $Global->POST['DebtorSelection']['BankAccount'] = $DebtorSelection['BankAccount'];
            $Global->POST['DebtorSelection']['BankReference'] = $DebtorSelection['BankReference'];
            $Global->savePost();
            return $form;
        }

        $tblPersonCauser = Person::useService()->getPersonById($PersonId);
        $tblPerson = Person::useService()->getPersonById($DebtorSelection['Debtor']);
        $tblPaymentType = Balance::useService()->getPaymentTypeById($DebtorSelection['PaymentType']);
        $tblItem = Item::useService()->getItemById($ItemId);
        $tblItemVariant = Item::useService()->getItemVariantById($DebtorSelection['Variant']);
        $ItemPrice = '';
        // ItemPrice only if Variant is "-1"
        if($DebtorSelection['Variant'] == '-1'){
            $ItemPrice = $DebtorSelection['Price'];
        }
        if($tblPaymentType && $tblPaymentType->getName() != 'SEPA-Lastschrift'){
            $tblBankAccount = false;
            $tblBankReference = false;
        } else {
            $tblBankAccount = Debtor::useService()->getBankAccountById($DebtorSelection['BankAccount']);
            $tblBankReference = Debtor::useService()->getBankReferenceById($DebtorSelection['BankReference']);
        }

        if($tblPersonCauser && $tblPerson && $tblPaymentType && $tblItem){

            // Add Person to PaymentGroup
            if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR))){
                Group::useService()->addGroupPerson($tblGroup, $tblPerson);
            }
            $tblDebtorSelection = Debtor::useService()->createDebtorSelection($tblPersonCauser, $tblPerson,
                $tblPaymentType, $tblItem,
                ($tblItemVariant ? $tblItemVariant : null),
                $ItemPrice,
                ($tblBankAccount ? $tblBankAccount : null),
                ($tblBankReference ? $tblBankReference : null));
            if($tblDebtorSelection){
                return new Success('Die Zuordnung des Beitragszahlers erfolgreich angelegt').self::pipelineCloseModal($Identifier,
                        $PersonId, $ItemId);
            } else {
                return new Danger('Die Zuordnung des Beitragszahlers konnte nicht gengelegt werden');
            }
        } else {
            return new Danger('Die Zuordnung des Beitragszahlers konnte nicht gengelegt werden (Person/Typ/Item)');
        }
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     * @param array      $DebtorSelection
     *
     * @return string
     */
    public function saveEditDebtorSelection(
        $Identifier = '',
        $PersonId = '',
        $ItemId = '',
        $DebtorSelectionId = '',
        $DebtorSelection = array()
    ){

        // Handle error's
        if($form = $this->checkInputDebtorSelection($Identifier, $PersonId, $ItemId, $DebtorSelectionId,
            $DebtorSelection)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['DebtorSelection']['PaymentType'] = $DebtorSelection['PaymentType'];
            $Global->POST['DebtorSelection']['Variant'] = $DebtorSelection['Variant'];
            $Global->POST['DebtorSelection']['Price'] = $DebtorSelection['Price'];
            $Global->POST['DebtorSelection']['Debtor'] = $DebtorSelection['Debtor'];
            $Global->POST['DebtorSelection']['BankAccount'] = $DebtorSelection['BankAccount'];
            $Global->POST['DebtorSelection']['BankReference'] = $DebtorSelection['BankReference'];
            $Global->savePost();
            return $form;
        }

        $tblPerson = Person::useService()->getPersonById($DebtorSelection['Debtor']);
        $tblPaymentType = Balance::useService()->getPaymentTypeById($DebtorSelection['PaymentType']);
        $tblItemVariant = Item::useService()->getItemVariantById($DebtorSelection['Variant']);
        $ItemPrice = '';
        // ItemPrice only if Variant is "-1"
        if($DebtorSelection['Variant'] == '-1'){
            $ItemPrice = $DebtorSelection['Price'];
        }

        if(!isset($DebtorSelection['BankAccount']) || $DebtorSelection['BankAccount'] == '-1'){
            $tblBankAccount = false;
            $tblBankReference = false;
        } else {
            $tblBankAccount = Debtor::useService()->getBankAccountById($DebtorSelection['BankAccount']);
            $tblBankReference = Debtor::useService()->getBankReferenceById($DebtorSelection['BankReference']);
        }

        $IsChange = false;
        if(($tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId))
            && $tblPerson && $tblPaymentType){
            // Add Person to PaymentGroup
            if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR))){
                Group::useService()->addGroupPerson($tblGroup, $tblPerson);
            }
            $IsChange = Debtor::useService()->changeDebtorSelection($tblDebtorSelection, $tblPerson, $tblPaymentType,
                ($tblItemVariant ? $tblItemVariant : null),
                $ItemPrice,
                ($tblBankAccount ? $tblBankAccount : null),
                ($tblBankReference ? $tblBankReference : null)
            );
        }

        return ($IsChange
            ? new Success('Die Zuordnung des Beitragszahlers erfolgreich geändert').self::pipelineCloseModal($Identifier,
                $PersonId, $ItemId)
            : new Danger('Die Zuordnung des Beitragszahlers konnte nicht geändert werden'));
    }

    /**
     * @param string     $Identifier
     * @param string     $PersonId
     * @param string     $ItemId
     * @param int|string $DebtorSelectionId
     *
     * @return string
     */
    public function showEditDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '', $DebtorSelectionId = '')
    {

        if('' !== $DebtorSelectionId && ($tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId))){
            $Global = $this->getGlobal();
            $tblPaymentType = $tblDebtorSelection->getServiceTblPaymentType();
            ($tblPaymentType ? $Global->POST['DebtorSelection']['PaymentType'] = $tblPaymentType->getId() : '');
            $tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant();
            ($tblItemVariant ? $_POST['DebtorSelection']['Variant'] = $tblItemVariant->getId() : '');
            $Value = $tblDebtorSelection->getValue(true);
            ($Value !== '0,00' ? $Global->POST['DebtorSelection']['Price'] = $Value : '');
            $tblPerson = $tblDebtorSelection->getServiceTblPersonDebtor();
            ($tblPerson ? $Global->POST['DebtorSelection']['Debtor'] = $tblPerson->getId() : '');
            $tblBankAccount = $tblDebtorSelection->getTblBankAccount();
            ($tblBankAccount ? $Global->POST['DebtorSelection']['BankAccount'] = $tblBankAccount->getId()
                : $Global->POST['DebtorSelection']['BankAccount'] = '-1');
            $tblBankReference = $tblDebtorSelection->getTblBankReference();
            ($tblBankReference ? $Global->POST['DebtorSelection']['BankReference'] = $tblBankReference->getId() : '');
            $Global->savePost();
        }

        return new Well(self::formDebtorSelection($Identifier, $PersonId, $ItemId, $DebtorSelectionId));
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     * @param string $DebtorSelectionId
     *
     * @return string
     */
    public function showDeleteDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '', $DebtorSelectionId = '')
    {

        $tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId);


        if($tblDebtorSelection){
            $PersonString = 'Person nicht gefunden!';
            if(($tblPerson = $tblDebtorSelection->getServiceTblPersonDebtor())){
                $PersonString = $tblPerson->getFullName();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person: ', 2),
                new LayoutColumn(new Bold($PersonString), 10),
            ))));
            $ItemString = 'Beitragsart nicht gefunden!';
            if(($tblItem = $tblDebtorSelection->getServiceTblItem())){
                $ItemString = $tblItem->getName();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Beitragsart: ', 2),
                new LayoutColumn(new Bold($ItemString), 10),
            ))));
            $PaymentTypeString = 'Zahlungsart nicht gefunden!';
            if(($tblPaymentType = $tblDebtorSelection->getServiceTblPaymentType())){
                $PaymentTypeString = $tblPaymentType->getName();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Zahlungsart: ', 2),
                new LayoutColumn(new Bold($PaymentTypeString), 10),
            ))));
            $PriceString = 'Konditionen nicht gefunden';
            if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())){
                $PriceString = new DangerText('Kein aktueller Preis hinterlegt!');
                if(($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))){
                    $PriceString = $tblItemCalculation->getPriceString();
                }
                $PriceString = $tblItemVariant->getName().': '.$PriceString;
            } elseif(($Value = $tblDebtorSelection->getValue())) {
                $PriceString = $Value;
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Konditionen: ', 2),
                new LayoutColumn(new Bold($PriceString), 10),
            ))));
            $BankAccountString = 'Bankverbindung nicht gefunden!';
            $BankAccountLeftHeadString = '';
            if(($tblBankAccount = $tblDebtorSelection->getTblBankAccount())){
                $BankAccountLeftHeadString = 'Inhaber: <br/> Bankname: <br/>IBAN: <br/>BIC: ';
                $BankAccountString = $tblBankAccount->getOwner()
                    .'<br/>'.$tblBankAccount->getBankName()
                    .'<br/>'.$tblBankAccount->getIBANFrontend()
                    .'<br/>'.$tblBankAccount->getBICFrontend();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Bankverbindung: ', 2),
                new LayoutColumn($BankAccountLeftHeadString, 2),
                new LayoutColumn(new Bold($BankAccountString), 8),
            ))));
            $BankReferenceString = 'Mandatsreferenznummer nicht gefunden!';
            if(($tblBankReference = $tblDebtorSelection->getTblBankReference())){
                $BankReferenceString = $tblBankReference->getReferenceNumber();
            }
            $Content[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Mandatsreferenznummer: ', 2),
                new LayoutColumn(new Bold($BankReferenceString), 10),
            ))));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Zuordnung des Beitragszahlers wirklich entfernt werden?'
                                , $Content, Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteDebtorSelection($Identifier, $PersonId,
                                    $ItemId,
                                    $DebtorSelectionId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Die Zuordnung des Beitragszahlers wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $PersonId
     * @param string $ItemId
     * @param string $DebtorSelectionId
     *
     * @return string
     */
    public function deleteDebtorSelection($Identifier = '', $PersonId = '', $ItemId = '', $DebtorSelectionId = '')
    {

        if(($tblDebtorSelection = Debtor::useService()->getDebtorSelectionById($DebtorSelectionId))){
            Debtor::useService()->removeDebtorSelection($tblDebtorSelection);

            return new Success('Die Zuordnung des Beitragszahlers wurde erfolgreich entfernt').self::pipelineCloseModal($Identifier,
                    $PersonId, $ItemId);
        }
        return new Danger('Die Zuordnung des Beitragszahlers konnte nicht entfernt werden');
    }

}