<?php

namespace SPHERE\Application\Api\People\Meta\Support;


use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiSupport extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openCreateSupportModal');
        $Dispatcher->registerMethod('openCreateSpecialModal');
        $Dispatcher->registerMethod('openCreateHandyCapModal');
        $Dispatcher->registerMethod('openCreateDeleteSupportModal');
        $Dispatcher->registerMethod('saveCreateSupportModal');
        $Dispatcher->registerMethod('saveCreateSpecialModal');
        $Dispatcher->registerMethod('saveCreateHandyCapModal');
        $Dispatcher->registerMethod('deleteSupportModal');
        $Dispatcher->registerMethod('loadSupportTable');
        $Dispatcher->registerMethod('loadSpecialTable');
        $Dispatcher->registerMethod('loadHandyCapTable');
//        $Dispatcher->registerMethod('deleteSupportEntry');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver())->setIdentifier('ModalReciever');
    }

    public static function receiverTableBlock($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverInline($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    public static function pipelineLoadTable($PersonId)
    {

        $TablePipeline = new Pipeline(false);
        $TableEmitter = new ServerEmitter(ApiSupport::receiverTableBlock('', 'SupportTable'), ApiSupport::getEndpoint());
        $TableEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'loadSupportTable',
        ));
        $TableEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $TablePipeline->appendEmitter($TableEmitter);
        $TableEmitter = new ServerEmitter(ApiSupport::receiverTableBlock('', 'SpecialTable'), ApiSupport::getEndpoint());
        $TableEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'loadSpecialTable',
        ));
        $TableEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $TablePipeline->appendEmitter($TableEmitter);
        $TableEmitter = new ServerEmitter(ApiSupport::receiverTableBlock('', 'HandyCapTable'), ApiSupport::getEndpoint());
        $TableEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'loadHandyCapTable',
        ));
        $TableEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $TablePipeline->appendEmitter($TableEmitter);

        return $TablePipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateSupportModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openCreateSupportModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateSpecialModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openCreateSpecialModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateHandyCapModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openCreateHandyCapModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateSupportSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateSupportModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateSpecialSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateSpecialModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateHandyCapSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateHandyCapModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteSupport($PersonId, $SupportId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateDeleteSupportModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SupportId' => $SupportId
        ));
//        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteSupport($PersonId, $SupportId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'deleteSupportModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SupportId' => $SupportId
        ));
//        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateSupportModal($PersonId)
    {

        return new Title('Förderantrag/ Förderbescheid hinzufügen')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            Student::useFrontend()->formSupport($PersonId)
                        )
                    )
                )
            )
        );
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateSpecialModal($PersonId)
    {

        return new Title('Entwicklungsbesonderheiten hinzufügen')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            Student::useFrontend()->formSpecial($PersonId)
                        )
                    )
                )
            )
        );
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateHandyCapModal($PersonId)
    {

        return new Title('Nachteilsausgleich hinzufügen')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            Student::useFrontend()->formHandyCap($PersonId)
                        )
                    )
                )
            )
        );
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
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateSupportModal($PersonId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];
        if (($form = Student::useService()->checkInputSupport($PersonId, $Data))) {
            // display Errors on form
            return $form;
        }
        // do service

//        return 'Alles ok für\'s speichern';
        if (Student::useService()->createSupport($PersonId, $Data)
        ) {
             return new Success('Förderantrag wurde erfolgreich gespeichert.')
                 .self::pipelineLoadTable($PersonId)
                 .self::pipelineClose();
        } else {
            return new Danger('Förderantrag konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateSpecialModal($PersonId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];

        if (($form = Student::useService()->checkInputSpecial($PersonId, $Data))) {
            // display Errors on form
            return $form;
        }

        // do service
        if (Student::useService()->createSpecial($PersonId, $Data)
        ) {
            return new Success('Entwicklungsbesonderheiten wurde erfolgreich gespeichert.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Entwicklungsbesonderheiten konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateHandyCapModal($PersonId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];

        if (($form = Student::useService()->checkInputHandyCap($PersonId, $Data))) {
            // display Errors on form
            return $form;
        }

        // do service
        if (Student::useService()->createHandyCap($PersonId, $Data)
        ) {
            return new Success('Nachteilsausgleich wurde erfolgreich gespeichert.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Nachteilsausgleich konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     *
     * @return Warning|TableData
     */
    public function loadSupportTable($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson){
            return Student::useFrontend()->getSupportTable($tblPerson);
        }
        return new Warning('Person nicht gefunden');
    }

    /**
     * @param $PersonId
     *
     * @return Warning|TableData
     */
    public function loadSpecialTable($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson){
            return Student::useFrontend()->getSpecialTable($tblPerson);
        }
        return new Warning('Person nicht gefunden');
    }

    /**
     * @param $PersonId
     *
     * @return Warning|TableData
     */
    public function loadHandyCapTable($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson){
            return Student::useFrontend()->getHandyCapTable($tblPerson);
        }
        return new Warning('Person nicht gefunden');
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return Danger|string
     */
    public function openCreateDeleteSupportModal($PersonId, $SupportId)
    {
        $tblSupport = Student::useService()->getSupportById($SupportId);
        if(!$tblSupport){
            return new Danger('Eintrag nicht gefunden.');
        }

        $SupportType = '';
        if(($tblSupportType = $tblSupport->getTblSupportType())){
            $SupportType = $tblSupportType->getName() ;
        }
        $FocusList = array();
        $tblFocusType = Student::useService()->getPrimaryFocusBySupport($tblSupport);
        if($tblFocusType){
            $FocusList[] = new Bold($tblFocusType->getName());
        }
        $tblFocusTypeList = Student::useService()->getFocusListBySupport($tblSupport);
        if($tblFocusTypeList){
            foreach($tblFocusTypeList as $tblFocusTypeSingle){
                $FocusList[] = $tblFocusTypeSingle->getName();
            }
        }

        $Person = '';
        if(($tblPerson = $tblSupport->getServiceTblPerson())){
            $Person = $tblPerson->getLastFirstName();
        }

        $Focus = implode('<br/>', $FocusList);

        $Content = new Listing(array(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person:', 4),
                new LayoutColumn($Person, 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Datum:', 4),
                    new LayoutColumn($tblSupport->getDate(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Förderantrag/bescheid:', 4),
                    new LayoutColumn($SupportType, 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Schwerpunkte:', 4),
                    new LayoutColumn($Focus, 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Förderschule:', 4),
                    new LayoutColumn($tblSupport->getCompany(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Schulbegleitung:', 4),
                    new LayoutColumn($tblSupport->getPersonSupport(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Stundenbedarf:', 4),
                    new LayoutColumn($tblSupport->getSupportTime(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Bemerkung:', 4),
                    new LayoutColumn($tblSupport->getRemark(), 8),
            ))))
        ));

        return new Title('Förderantrag entfernen')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Soll der Eintrag wirklich gelöscht werden?',
                            $Content, Panel::PANEL_TYPE_DANGER
                        )
                        .(new DangerLink('Ja', '#', new Ok()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineDeleteSupport($PersonId, $SupportId))
                        .(new Standard('Nein', '#', new Remove()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineClose())
                    )
                )
            )
        );
    }

    /**
     * @param $PersonId
     * @param $SupportId
     *
     * @return Danger|string
     */
    public function deleteSupportModal($PersonId, $SupportId)
    {

        if(!($tblSupport = Student::useService()->getSupportById($SupportId))) {
            return new Danger('Der Förderantrag konnte nicht gefunden werden.');
        }
        if (Student::useService()->deleteSupport($tblSupport)
        ) {
            return new Success('Der Förderantrag wurde erfolgreich gelöscht.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Förderantrag konnte nicht gelöscht werden.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        }
    }
}