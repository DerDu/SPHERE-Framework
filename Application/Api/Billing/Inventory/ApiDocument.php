<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.03.2019
 * Time: 11:06
 */

namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Inventory\Document\Document;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;

/**
 * Class ApiDocument
 *
 * @package SPHERE\Application\Api\Billing\Inventory
 */
class ApiDocument implements IApiInterface
{
    // registered method
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadDocumentOverviewContent');

        $Dispatcher->registerMethod('openCreateDocumentModal');
        $Dispatcher->registerMethod('saveCreateDocumentModal');

        $Dispatcher->registerMethod('openEditDocumentModal');
        $Dispatcher->registerMethod('saveEditDocumentModal');

        $Dispatcher->registerMethod('openDeleteDocumentModal');
        $Dispatcher->registerMethod('saveDeleteDocumentModal');

        $Dispatcher->registerMethod('changeFilter');
        $Dispatcher->registerMethod('loadDocumentContent');

        $Dispatcher->registerMethod('searchPerson');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReciever');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSearchPerson()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Balance
     *
     * @return string
     */
    public function searchPerson($Balance = null)
    {
//        Debugger::screenDump($Balance);exit;
        return Balance::useFrontend()->loadPersonSearch(isset($Balance['Search']) ? $Balance['Search'] : '');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadDocumentOverviewContent()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DocumentOverviewContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDocumentOverviewContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenCreateDocumentModal()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateDocumentModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineCreateDocumentSave()
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateDocumentModal'
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DocumentId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditDocumentModal($DocumentId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditDocumentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DocumentId' => $DocumentId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DocumentId
     *
     * @return Pipeline
     */
    public static function pipelineEditDocumentSave($DocumentId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDocumentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DocumentId' => $DocumentId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DocumentId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteDocumentModal($DocumentId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteDocumentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DocumentId' => $DocumentId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DocumentId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteDocumentSave($DocumentId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteDocumentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DocumentId' => $DocumentId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $IsMultiItem
     *
     * @return Pipeline
     */
    public static function pipelineChangeFilter($IsMultiItem = '0')
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'changeFilter'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeFilter',
        ));
        $ModalEmitter->setPostPayload(array(
            'IsMultiItem' => $IsMultiItem,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ItemId
     * @param $Location
     * @param $Date
     *
     * @return Pipeline
     */
    public static function pipelineLoadDocumentContent($ItemId, $Location, $Date)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'loadDocumentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDocumentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'ItemId' => $ItemId,
            'Location' => $Location,
            'Date' => $Date
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadDocumentOverviewContent()
    {
        return Document::useFrontend()->loadDocumentOverviewContent();
    }

    /**
     * @return string
     */
    public function openCreateDocumentModal()
    {
        return $this->getDocumentModal(Document::useFrontend()->formDocument());
    }

    /**
     * @param $form
     * @param null $DocumentId
     *
     * @return string
     */
    private function getDocumentModal($form, $DocumentId = null)
    {
        if ($DocumentId) {
            $title = new Title(new Edit() . ' Bescheinigung bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Bescheinigung hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function saveCreateDocumentModal($Data)
    {
        if (($form = Document::useService()->checkFormDocument($Data))) {
            // display Errors on form
            return $this->getDocumentModal($form);
        }

        if (Document::useService()->createDocument($Data)) {
            return new Success('Die Bescheinigung wurde erfolgreich gespeichert.')
                . self::pipelineLoadDocumentOverviewContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Bescheinigung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DocumentId
     *
     * @return string
     */
    public function openEditDocumentModal($DocumentId)
    {

        if (!($tblDocument = Document::useService()->getDocumentById($DocumentId))) {
            return new Danger('Die Bescheinigung wurde nicht gefunden', new Exclamation());
        }

        return $this->getDocumentModal(Document::useFrontend()->formDocument($DocumentId, true), $DocumentId);
    }

    /**
     * @param $DocumentId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditDocumentModal($DocumentId, $Data)
    {
        if (!($tblDocument = Document::useService()->getDocumentById($DocumentId))) {
            return new Danger('Die Bescheinigung wurde nicht gefunden', new Exclamation());
        }

        if (($form = Document::useService()->checkFormDocument($Data, $tblDocument))) {
            // display Errors on form
            return $this->getDocumentModal($form, $DocumentId);
        }

        if (Document::useService()->updateDocument($tblDocument, $Data)) {
            return new Success('Die Bescheinigung wurde erfolgreich gespeichert.')
                . self::pipelineLoadDocumentOverviewContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Bescheinigung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DocumentId
     * 
     * @return Danger|string
     */
    public function openDeleteDocumentModal($DocumentId)
    {
        if (!($tblDocument = Document::useService()->getDocumentById($DocumentId))) {
            return new Danger('Die Bescheinigung wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Bescheinigung löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Question() . ' Diese Bescheinigung wirklich löschen?', array(
                                $tblDocument->getName(),
                                $tblDocument->getDescription(),
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteDocumentSave($DocumentId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $DocumentId
     *
     * @return Danger|string
     */
    public function saveDeleteDocumentModal($DocumentId)
    {
        if (!($tblDocument = Document::useService()->getDocumentById($DocumentId))) {
            return new Danger('Die Bescheinigung wurde nicht gefunden', new Exclamation());
        }

        if (Document::useService()->removeDocument($tblDocument)) {
            return new Success('Die Bescheinigung wurde erfolgreich gelöscht.')
                . self::pipelineLoadDocumentOverviewContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Bescheinigung konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $Balance
     *
     * @return \SPHERE\Common\Frontend\Form\IFormInterface
     */
    public function changeFilter($Balance, $IsMultiItem = '0')
    {

        return Balance::useFrontend()->getFilterForm($Balance, null, $IsMultiItem);
    }

    /**
     * @param $ItemId
     * @param $Location
     * @param $Date
     * @param $Data
     *
     * @return Panel|string
     */
    public function loadDocumentContent($ItemId, $Location, $Date, $Data)
    {
        if (($tblItem = Item::useService()->getItemById($ItemId))) {
            return Balance::useFrontend()->getDocumentPanel($tblItem, $Data, $Location, $Date);
        } else {
            return '';
        }
    }
}