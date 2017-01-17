<?php
namespace SPHERE\Application\Api\Platform\Gatekeeper;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiUserGroup
 *
 * @package SPHERE\Application\Api\Platform\Gatekeeper
 */
class ApiUserGroup extends Extension implements IApiInterface
{
    const API_DISPATCHER = 'MethodName';

    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__ . '::ApiDispatcher'
        ));
    }

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function ApiDispatcher($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('pieceGroupList');

        $Dispatcher->registerMethod('pieceMemberList');
        $Dispatcher->registerMethod('pieceUserList');

        $Dispatcher->registerMethod('pieceAddUser');
        $Dispatcher->registerMethod('pieceRemoveUser');

        $Dispatcher->registerMethod('pieceCreateGroup');

        $Dispatcher->registerMethod('pieceEditGroup');

        $Dispatcher->registerMethod('pieceDestroyGroupConfirm');
        $Dispatcher->registerMethod('pieceDestroyGroup');

        return $Dispatcher->callMethod($MethodName);
    }

    /**
     * @param string $receiverGroupList
     * @param string $receiverModalCreate
     * @param string $receiverModalEdit
     * @param string $receiverModalDestroy
     * @return TableData
     */
    public function pieceGroupList($receiverGroupList, $receiverModalCreate, $receiverModalEdit, $receiverModalDestroy)
    {

        $tblGroupAll = Account::useService()->getGroupAll(
            Consumer::useService()->getConsumerBySession()
        );

        $TableList = array();
        if ($tblGroupAll) {
            array_walk($tblGroupAll,
                function (TblGroup $tblGroup) use (
                    &$TableList,
                    $receiverGroupList,
                    $receiverModalCreate,
                    $receiverModalEdit,
                    $receiverModalDestroy
                ) {

                    /**
                     * Edit Group
                     */
                    $pipelineEditGroup = new Pipeline();
                    $emitterEditGroup = new ServerEmitter((new ModalReceiver())->setIdentifier($receiverModalEdit), ApiUserGroup::getRoute());
                    $emitterEditGroup->setGetPayload(array(
                        ApiUserGroup::API_DISPATCHER => 'pieceEditGroup',
                        'Id' => $tblGroup->getId(),
                        'receiverGroupList' => $receiverGroupList,
                        'receiverModalCreate' => $receiverModalCreate,
                        'receiverModalEdit' => $receiverModalEdit,
                        'receiverModalDestroy' => $receiverModalDestroy
                    ));
                    $pipelineEditGroup->addEmitter($emitterEditGroup);

                    /**
                     * Destroy Group (Confirm)
                     */
                    $pipelineDestroyGroup = new Pipeline();
                    $emitterDestroyGroup = new ServerEmitter((new ModalReceiver())->setIdentifier($receiverModalDestroy), ApiUserGroup::getRoute());
                    $emitterDestroyGroup->setGetPayload(array(
                        ApiUserGroup::API_DISPATCHER => 'pieceDestroyGroupConfirm',
                        'Id' => $tblGroup->getId(),
                        'receiverGroupList' => $receiverGroupList,
                        'receiverModalCreate' => $receiverModalCreate,
                        'receiverModalEdit' => $receiverModalEdit,
                        'receiverModalDestroy' => $receiverModalDestroy
                    ));
                    $pipelineDestroyGroup->addEmitter($emitterDestroyGroup);

                    /**
                     * Data
                     */
                    $Group = $tblGroup->__toArray();
                    $Group['Option'] = ''
                        . (new Standard('', __CLASS__, new Edit()))->ajaxPipelineOnClick($pipelineEditGroup)
                        . (new Standard('', __CLASS__, new Remove()))->ajaxPipelineOnClick($pipelineDestroyGroup)
                        . (new Standard('', '#', new Setup()));
                    $TableList[] = $Group;
                });
        }

        return new TableData(
            $TableList
            , null, array(
            'Name' => 'Name',
            'Description' => 'Beschreibung',
            'Role' => 'Rechte',
            'Member' => 'Benutzer',
            'Option' => ''
        ), array(
            "columnDefs" => array(
                array("searchable" => false, "targets" => -1),
                array("type" => "natural", "targets" => '_all')
            )
        ));
    }

    /**
     * @return Route
     */
    public static function getRoute()
    {
        return new Route(__CLASS__);
    }

    public function pieceMemberList()
    {
        return new TableData(array());
    }

    public function pieceUserList()
    {
        return new TableData(array());
    }

    public function pieceAddUser($GroupId, $UserId)
    {
        $Pipeline = new Pipeline();

        return $Pipeline;
    }

    public function pieceRemoveUser($GroupId, $UserId)
    {
        $Pipeline = new Pipeline();

        return $Pipeline;
    }

    /**
     * @param null|array $Group
     * @param string $receiverGroupList
     * @param string $receiverModalCreate
     * @param string $receiverModalEdit
     * @param string $receiverModalDestroy
     * @return Well Form in Well
     */
    public function pieceCreateGroup($Group, $receiverGroupList, $receiverModalCreate, $receiverModalEdit, $receiverModalDestroy)
    {
        /**
         * On Submit
         */
        $pipelineCreateGroup = new Pipeline();
        $emitterCreateGroup = new ServerEmitter((new ModalReceiver())->setIdentifier($receiverModalCreate),
            ApiUserGroup::getRoute());
        $emitterCreateGroup->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'pieceCreateGroup',
            'receiverGroupList' => $receiverGroupList,
            'receiverModalCreate' => $receiverModalCreate,
            'receiverModalEdit' => $receiverModalEdit,
            'receiverModalDestroy' => $receiverModalDestroy
        ));
        $pipelineCreateGroup->addEmitter($emitterCreateGroup);

        /**
         * On Success
         */
        $pipelineSuccess = new Pipeline();
        (new ApiUserGroup())->loadGroupList($pipelineSuccess, (new BlockReceiver())->setIdentifier($receiverGroupList), $receiverModalEdit, $receiverModalDestroy);
        $pipelineSuccess->addEmitter( (new CloseModal($receiverModalCreate))->getEmitter() );

        /**
         * Create Group (Form)
         */
        $Form = $this->formUserGroup()->ajaxPipelineOnSubmit($pipelineCreateGroup);

        return new Well( Account::useService()->createGroup(
            $Form, $Group, $pipelineSuccess
        ) );
    }

    /**
     * @return Form
     */
    public function formUserGroup()
    {
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Group[Name]', 'Gruppenname', 'Gruppenname'))->setAutoFocus()
                    ),
                    new FormColumn(
                        (new TextArea('Group[Description]', 'Gruppenbeschreibung',
                            'Gruppenbeschreibung'))->setMaxLengthValue(200)
                    ),
                ))
            ), new Primary('Speichern', new Save())
        );
    }

    /**
     * @param Pipeline $Pipeline
     * @param AbstractReceiver $Receiver
     * @param string $receiverModalEdit
     * @param string $receiverModalDestroy
     */
    public function loadGroupList(Pipeline $Pipeline, AbstractReceiver $Receiver, $receiverModalEdit, $receiverModalDestroy)
    {

        $emitterGroupList = new ServerEmitter($Receiver, ApiUserGroup::getRoute());
        $emitterGroupList->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'pieceGroupList',
            'receiverGroupList' => $Receiver->getIdentifier(),
            'receiverModalEdit' => $receiverModalEdit,
            'receiverModalDestroy' => $receiverModalDestroy
        ));
        $Pipeline->addEmitter($emitterGroupList);
    }

    /**
     * @param int $Id
     * @param null|array $Group
     * @param string $receiverGroupList
     * @param string $receiverModalCreate
     * @param string $receiverModalEdit
     * @param string $receiverModalDestroy
     * @return Well Form in Well
     */
    public function pieceEditGroup($Id, $Group, $receiverGroupList, $receiverModalCreate, $receiverModalEdit, $receiverModalDestroy)
    {
        $tblGroup = Account::useService()->getGroupById( $Id );
        if( $tblGroup ) {

            /**
             * On Submit
             */
            $pipelineEditGroup = new Pipeline();
            $emitterEditGroup = new ServerEmitter((new ModalReceiver())->setIdentifier($receiverModalEdit),
                ApiUserGroup::getRoute());
            $emitterEditGroup->setGetPayload(array(
                ApiUserGroup::API_DISPATCHER => 'pieceEditGroup',
                'Id' => $tblGroup->getId(),
                'receiverGroupList' => $receiverGroupList,
                'receiverModalCreate' => $receiverModalCreate,
                'receiverModalEdit' => $receiverModalEdit,
                'receiverModalDestroy' => $receiverModalDestroy
            ));
            $pipelineEditGroup->addEmitter($emitterEditGroup);

            /**
             * On Success
             */
            $pipelineSuccess = new Pipeline();
            (new ApiUserGroup())->loadGroupList($pipelineSuccess, (new BlockReceiver())->setIdentifier($receiverGroupList), $receiverModalEdit, $receiverModalDestroy);
            $pipelineSuccess->addEmitter( (new CloseModal($receiverModalEdit))->getEmitter() );

            /**
             * Create Group (Form)
             */
            $Global = $this->getGlobal();
            $Global->POST['Group'] = $tblGroup->__toArray();
            $Global->savePost();
            $Form = $this->formUserGroup()->ajaxPipelineOnSubmit($pipelineEditGroup);

            return new Well(Account::useService()->editGroup(
                $Form, $tblGroup, $Group, $pipelineSuccess
            ));
        } else {
            // TODO: Error
            return new Well('?');
        }
    }

    public function pieceDestroyGroupConfirm($Id, $receiverGroupList, $receiverModalCreate, $receiverModalEdit, $receiverModalDestroy)
    {
        $tblGroup = Account::useService()->getGroupById($Id);

        if ($tblGroup) {

            /**
             * On Submit
             */
            $pipelineDestroyGroup = new Pipeline();
            $emitterDestroyGroup = new ServerEmitter((new ModalReceiver())->setIdentifier($receiverModalDestroy), ApiUserGroup::getRoute());
            $emitterDestroyGroup->setGetPayload(array(
                ApiUserGroup::API_DISPATCHER => 'pieceDestroyGroup',
                'Id' => $tblGroup->getId(),
                'receiverGroupList' => $receiverGroupList,
                'receiverModalCreate' => $receiverModalCreate,
                'receiverModalEdit' => $receiverModalEdit,
                'receiverModalDestroy' => $receiverModalDestroy
            ));
            $pipelineDestroyGroup->addEmitter($emitterDestroyGroup);

            return new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn('Wollen Sie die Gruppe ' . $tblGroup->getName() . ' wirklich löschen?')
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new PullRight(
                                (new Standard('Ja', __CLASS__, new Enable()))->ajaxPipelineOnClick($pipelineDestroyGroup)
                                . new Close('Nein', new Disable())
                            )
                        )
                    ),
                ))
            );

        } else {
            // TODO: Error
            return '?';
        }
    }

    public function pieceDestroyGroup($Id, $receiverGroupList, $receiverModalEdit, $receiverModalDestroy)
    {
        if (($tblGroup = Account::useService()->getGroupById($Id))) {
            Account::useService()->destroyGroup($tblGroup);

            $pipelineDestroyGroup = new Pipeline();
            /**
             * Reload Group List
             */
            $this->loadGroupList($pipelineDestroyGroup, (new BlockReceiver())->setIdentifier($receiverGroupList), $receiverModalEdit, $receiverModalDestroy);
            /**
             * Close Destroy Group (Confirm)
             */
            $pipelineDestroyGroup->addEmitter(
                (new CloseModal($receiverModalDestroy))->getEmitter()
            );

            return (string)$pipelineDestroyGroup;
        } else {
            // Todo: Error
            return '?';
        }
    }
}
