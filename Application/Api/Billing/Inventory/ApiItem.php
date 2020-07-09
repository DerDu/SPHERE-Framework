<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
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

/**
 * Class ApiItem
 * @package SPHERE\Application\Api\Billing\Inventory
 *
 *  ApiItem -> ItemVariant -> ItemCalculation
 */
class ApiItem extends ItemVariant implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload Table
        $Dispatcher->registerMethod('getItemTable');
        // Item / Beitragsart
        $Dispatcher->registerMethod('showAddItem');
        $Dispatcher->registerMethod('saveAddItem');
        $Dispatcher->registerMethod('showEditItem');
        $Dispatcher->registerMethod('saveEditItem');
        $Dispatcher->registerMethod('showDeleteItem');
        $Dispatcher->registerMethod('deleteItem');
        // Variant / Beitragsvarianten
        $Dispatcher->registerMethod('showAddVariant');
        $Dispatcher->registerMethod('saveAddVariant');
        $Dispatcher->registerMethod('showEditVariant');
        $Dispatcher->registerMethod('saveEditVariant');
        $Dispatcher->registerMethod('showDeleteVariant');
        $Dispatcher->registerMethod('deleteVariant');
        // Calculation / Preis
        $Dispatcher->registerMethod('showAddCalculation');
        $Dispatcher->registerMethod('saveAddCalculation');
        $Dispatcher->registerMethod('showEditCalculation');
        $Dispatcher->registerMethod('saveEditCalculation');
        $Dispatcher->registerMethod('showDeleteCalculation');
        $Dispatcher->registerMethod('deleteCalculation');

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
    public static function receiverItemTable($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockTableContent');
    }

    /**
     * @param string $Identifier
     * @param array  $Item
     * @param array  $Group
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddItemModal($Identifier = '', $Item = array(), $Group = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showAddItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Item'       => $Item,
            'Group'      => $Group
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddItem($Identifier = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveAddItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param array      $Item
     * @param array      $Group
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditItemModal($Identifier = '', $ItemId = '', $Item = array(), $Group = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showEditItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId,
            'Item'       => $Item,
            'Group'      => $Group,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditItem($Identifier = '', $ItemId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveEditItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteItemModal($Identifier = '', $ItemId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showDeleteItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteItem($Identifier = '', $ItemId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'deleteItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId,
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
        $Emitter = new ServerEmitter(self::receiverItemTable(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemTable'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    public function getItemTable()
    {

        return Item::useFrontend()->getItemTable();
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Form
     */
    public function formItem($Identifier = '', $ItemId = '')
    {

        $CheckboxList = array();
        $tblGroupList = array();

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', ApiItem::getEndpoint(), new Save());
        if('' !== $ItemId){
            $SaveButton->ajaxPipelineOnClick(ApiItem::pipelineSaveEditItem($Identifier, $ItemId));
        } else {
            $SaveButton->ajaxPipelineOnClick(ApiItem::pipelineSaveAddItem($Identifier));
        }

        // get all possible Person Groups
        if(($tblSettingGroupPersonAll = Setting::useService()->getSettingGroupPersonAll())){
            foreach($tblSettingGroupPersonAll as $tblSettingGroupPerson) {
                if(($tblGroup = $tblSettingGroupPerson->getServiceTblGroupPerson())){
                    $tblGroupList[] = $tblGroup;
                }
            }
        }
        foreach($tblGroupList as &$tblGroup) {
            if($tblGroup->getMetaTable() === 'COMMON'){
                $tblGroup = false;
            }
        }
        $tblGroupList = array_filter($tblGroupList);

//        if(($tblGroupList = $this->getSorter($tblGroupList)->sortObjectBy('Name'))){

        /** @var TblGroup $tblGroupSingle */
        // strange effect if variable exact "$tblGroup"
        foreach($tblGroupList as $tblGroupSingle) {
            $CheckboxList[] = new CheckBox('Group['.$tblGroupSingle->getId().']', $tblGroupSingle->getName(),
                $tblGroupSingle->getId());
        }
//        }

        $InfoSepa = '';
        $InfoDatev = '';
        if(($tblItem = Item::useService()->getItemById($ItemId))){
            $InfoDatev = $InfoSepa = $tblItem->getName();
        }
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_SEPA_REMARK))){
            if($tblSetting->getValue()){
                $InfoSepa = $tblSetting->getValue();
            }
        }
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_DATEV_REMARK))){
            if($tblSetting->getValue()){
                $InfoDatev = $tblSetting->getValue();
            }
        }

        $FibuAccountValue = '';
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_ACCOUNT))){
            $FibuAccountValue = '(Standard) '.$tblSetting->getValue();
        }
        $FibuToAccountValue = '';
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_TO_ACCOUNT))){
            $FibuToAccountValue = '(Standard) '.$tblSetting->getValue();
        }

        $Kost1 = '';
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_KOST_1))){
            $Kost1 = '(Standard) '.$tblSetting->getValue();
        }
        $Kost2 = '';
        if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_KOST_2))){
            $Kost2 = '(Standard) '.$tblSetting->getValue();
        }


        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Item[Name]', 'Beitragsart', 'Beitragsart'))->setRequired()
                        , 6),
                    new FormColumn(
                        $CheckboxList
                        , 6)
                )),
                new FormRow(
                    new FormColumn(
                        new Ruler()
                    )
                ),
                new FormRow(array(
                    new FormColumn(new Panel('Buchungstext',
                        array(
                            new TextField('Item[SepaRemark]', '(Standard) '.$InfoSepa, 'SEPA Verwendungszweck &nbsp;'),
                            new TextField('Item[DatevRemark]', '(Standard) '.$InfoDatev, 'DATEV Buchungstext &nbsp;')
                        ), Panel::PANEL_TYPE_INFO)
                    , 8),
                    new FormColumn(
                        new Panel('Freifelder für Buchungstext', array(
                                '[GID] Gläubiger-ID',
                                '[SN] Mandantsreferenznummer',
                                '[BVN] Beitragsverursacher Name',
                                '[BVV] Beitragsverursacher Vorname',
                                '[BA] Beitragsart',
                                '[BAEP] Beitragsart mit Einzelpreis',
                                '[DEB] Debitoren-Nr.',
                                '[BAM] Abrechnungszeitraum (Jahr+Monat)',
                            )
                            , Panel::PANEL_TYPE_INFO)
                        , 4),

                )),
                new FormRow(
                    new FormColumn(
                        new Ruler()
                    )
                ),
                new FormRow(array(
                   new FormColumn(new TextField('Item[FibuAccount]', $FibuAccountValue, 'Fibu-Konto'), 6),
                   new FormColumn(new TextField('Item[FibuToAccount]', $FibuToAccountValue, 'Fibu-Gegenkonto'), 6),
                )),
                new FormRow(array(
                   new FormColumn(new NumberField('Item[Kost1]', $Kost1, 'Kostenstelle 1'), 6),
                   new FormColumn(new NumberField('Item[Kost2]', $Kost2, 'Kostenstelle 2'), 6),
                )),
                new FormRow(array(
                   new FormColumn(new NumberField('Item[BuKey]', $Kost1, 'BU-Schlüssel'), 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        $SaveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param string $ItemId
     * @param array  $Item
     * @param array  $Group
     *
     * @return false|string|Form
     */
    private function checkInputItem($Identifier = '', $ItemId = '', $Item = array(), $Group = array())
    {
        $Error = false;
        $form = $this->formItem($Identifier, $ItemId);
        $Warning = '';
        if(isset($Item['Name']) && empty($Item['Name'])){
            $form->setError('Item[Name]', 'Bitte geben Sie den Namen der Beitragsart an');
            $Error = true;
            // disable save for duplicated names
        } elseif(isset($Item['Name']) && ($tblItem = Item::useService()->getItemByName($Item['Name']))) {
            // ignore own Name
            if($tblItem->getId() != $ItemId){
                $form->setError('Item[Name]', 'Beitragsart exisitiert bereits, sie darf nicht doppelt angelegt werden');
                $Error = true;
            }
        }
        if(empty($Group)){
            $Warning = 'Bitte geben Sie mindestens eine Personengruppe an';
            $Error = true;
        }

        if($Error){
            if($Warning){
                return new Danger($Warning).new Well($form);
            } else {
                return new Well($form);
            }
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     *
     * @return string
     */
    public function showAddItem($Identifier = '')
    {

        return new Well($this->formItem($Identifier));
    }

    /**
     * @param string $Identifier
     * @param array  $Item
     * @param array  $Group
     *
     * @return string
     */
    public function saveAddItem($Identifier = '', $Item = array(), $Group = array())
    {

        // Handle error's
        if($form = $this->checkInputItem($Identifier, '', $Item, $Group)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Item']['Name'] = $Item['Name'];
            $Global->POST['Group'] = $Group;
            $Global->POST['Item']['SepaRemark'] = $Item['SepaRemark'];
            $Global->POST['Item']['DatevRemark'] = $Item['DatevRemark'];
            $Global->POST['Item']['FibuAccount'] = $Item['FibuAccount'];
            $Global->POST['Item']['FibuToAccount'] = $Item['FibuToAccount'];
            $Global->POST['Item']['Kost1'] = $Item['Kost1'];
            $Global->POST['Item']['Kost2'] = $Item['Kost2'];
            $Global->POST['Item']['BuKey'] = $Item['BuKey'];
            $Global->savePost();
            return $form;
        }

        if(($tblItem = Item::useService()->createItem($Item['Name'], '', $Item['SepaRemark'], $Item['DatevRemark'],
            $Item['FibuAccount'], $Item['FibuToAccount'], $Item['Kost1'], $Item['Kost2'], $Item['BuKey']))){
            foreach($Group as $GroupId) {
                if(($tblGroup = Group::useService()->getGroupById($GroupId))){
                    Item::useService()->createItemGroup($tblItem, $tblGroup);
                }
            }
        }

        return ($tblItem
            ? new Success('Beitragsart erfolgreich angelegt').self::pipelineCloseModal($Identifier)
            : new Danger('Beitragsart konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param array      $Item
     * @param array      $Group
     *
     * @return string
     */
    public function saveEditItem($Identifier = '', $ItemId = '', $Item = array(), $Group = array())
    {

        // Handle error's
        if($form = $this->checkInputItem($Identifier, $ItemId, $Item, $Group)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Item']['Name'] = $Item['Name'];
            $Global->POST['Group'] = $Group;
            $Global->POST['Item']['SepaRemark'] = $Item['SepaRemark'];
            $Global->POST['Item']['DatevRemark'] = $Item['DatevRemark'];
            $Global->POST['Item']['FibuAccount'] = $Item['FibuAccount'];
            $Global->POST['Item']['FibuToAccount'] = $Item['FibuToAccount'];
            $Global->POST['Item']['Kost1'] = $Item['Kost1'];
            $Global->POST['Item']['Kost2'] = $Item['Kost2'];
            $Global->POST['Item']['BuKey'] = $Item['BuKey'];
            $Global->savePost();
            return $form;
        }

        if(($tblItem = Item::useService()->getItemById($ItemId))){
            Item::useService()->changeItem($tblItem, $Item['Name'], '', $Item['SepaRemark'], $Item['DatevRemark'],
                $Item['FibuAccount'], $Item['FibuToAccount'], $Item['Kost1'], $Item['Kost2'], $Item['BuKey']);
            // entfernen überflüssiger Personengruppen-Verknüpfungen
            if(($tblItemGroupList = Item::useService()->getItemGroupByItem($tblItem))){
                foreach($tblItemGroupList as $tblItemGroup) {
                    $serviceTblGroup = $tblItemGroup->getServiceTblGroup();
                    // Personengruppen-Verknüpfungen, die weiterhin benutzt werden, müssen nicht gelöscht werden
                    // entfernte Personengruppen werden ebenfalls gelöscht
                    if($serviceTblGroup && !in_array($serviceTblGroup->getId(), $Group)
                    || !$serviceTblGroup){
                        Item::useService()->removeItemGroup($tblItemGroup);
                    }
                }
            }
            // Erstellen der neuen Personengruppen-Verknüpfungen
            foreach($Group as $GroupId) {
                if(($tblGroup = Group::useService()->getGroupById($GroupId))){
                    Item::useService()->createItemGroup($tblItem, $tblGroup);
                }
            }
        }

        return ($Item
            ? new Success('Beitragsart erfolgreich angelegt').self::pipelineCloseModal($Identifier)
            : new Danger('Beitragsart konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return string
     */
    public function showEditItem($Identifier = '', $ItemId = '')
    {

        if('' !== $ItemId && ($tblItem = Item::useService()->getItemById($ItemId))){
            $Global = $this->getGlobal();
            $Global->POST['Item']['Name'] = $tblItem->getName();
            $Global->POST['Item']['SepaRemark'] = $tblItem->getSepaRemark(true);
            $Global->POST['Item']['DatevRemark'] = $tblItem->getDatevRemark(true);
            $Global->POST['Item']['FibuAccount'] = $tblItem->getFibuAccount(true);
            $Global->POST['Item']['FibuToAccount'] = $tblItem->getFibuToAccount(true);
            $Global->POST['Item']['Kost1'] = $tblItem->getKost1(true);
            $Global->POST['Item']['Kost2'] = $tblItem->getKost2(true);
            if(($tblItemGroupList = Item::useService()->getItemGroupByItem($tblItem))){
                foreach($tblItemGroupList as $tblItemGroup) {
                    if(($tblGroup = $tblItemGroup->getServiceTblGroup())){
                        $Global->POST['Group'][$tblGroup->getId()] = $tblGroup->getId();
                    }
                }
            }
            $Global->savePost();
        }

        return new Well(self::formItem($Identifier, $ItemId));
    }

    /**
     * @param string $Identifier
     * @param string $ItemId
     *
     * @return string
     */
    public function showDeleteItem($Identifier = '', $ItemId = '')
    {

        $tblItem = Item::useService()->getItemById($ItemId);
        $GroupArray = array();
        if($tblItem){
            if(($tblGroupPersonList = Item::useService()->getItemGroupByItem($tblItem))){
                foreach($tblGroupPersonList as $tblGroupPerson) {
                    if(($tblGroup = $tblGroupPerson->getServiceTblGroup())){
                        $GroupArray[] = $tblGroup->getName();
                    }
                }
            }

            $Content[] = 'hinterlegte Personengruppen: '.new Bold(implode(', ', $GroupArray));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Beitragsart '.new Bold($tblItem->getName()).' wirklich entfernt werden?'
                                , new Listing($Content), Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteItem($Identifier, $ItemId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Beitragsart wurde nicht gefunden');
        }
    }

    /**
     * @param string $Identifier
     * @param string $ItemId
     *
     * @return string
     */
    public function deleteItem($Identifier = '', $ItemId = '')
    {

        if(($tblItem = Item::useService()->getItemById($ItemId))){
            Item::useService()->removeItem($tblItem);

            return new Success('Beitragsart wurde erfolgreich entfernt').self::pipelineCloseModal($Identifier);
        }
        return new Danger('Beitragsart konnte nicht entfernt werden');
    }

}