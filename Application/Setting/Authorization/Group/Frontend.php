<?php
namespace SPHERE\Application\Setting\Authorization\Group;

use SPHERE\Application\Api\Platform\Gatekeeper\ApiUserGroup;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\Notify;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendUserGroup()
    {
        $Stage = new Stage('Benutzergruppen', 'Verwalten');
        $Stage->setMessage('');

        $receiverStageContent = new BlockReceiver();

        $receiverGroupList = new BlockReceiver();
        $receiverModalCreate = new ModalReceiver( new PlusSign() . ' Neue Benutzergruppe anlegen', new Close() );
        $receiverModalEdit = new ModalReceiver( 'Gruppe bearbeiten', new Close() );
        $receiverModalDestroy = new ModalReceiver( 'Sind Sie sicher?' );

        /**
         * Create New UserGroup
         */
        $pipelineModalCreate = new Pipeline();
        $emitterModalCreate = new ServerEmitter($receiverModalCreate, ApiUserGroup::getRoute());
        $emitterModalCreate->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'pieceCreateGroup',
            'receiverGroupList' => $receiverGroupList->getIdentifier(),
            'receiverModalCreate' => $receiverModalCreate->getIdentifier(),
            'receiverModalEdit' => $receiverModalEdit->getIdentifier(),
            'receiverModalDestroy' => $receiverModalDestroy->getIdentifier()
        ));
        $pipelineModalCreate->addEmitter($emitterModalCreate);

        $Stage->addButton(
            (new Standard('Neue Benutzergruppe anlegen','#', new PlusSign()))->ajaxPipelineOnClick( $pipelineModalCreate )
        );

        /**
         * Show Table UserGroup
         */
        $pipelineGroupList = new Pipeline();
        $emitterGroupList = new ServerEmitter($receiverGroupList, ApiUserGroup::getRoute());
        $emitterGroupList->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'pieceGroupList',
            'receiverGroupList' => $receiverGroupList->getIdentifier(),
            'receiverModalCreate' => $receiverModalCreate->getIdentifier(),
            'receiverModalEdit' => $receiverModalEdit->getIdentifier(),
            'receiverModalDestroy' => $receiverModalDestroy->getIdentifier()
        ));
        $pipelineGroupList->addEmitter($emitterGroupList);
        $receiverGroupList->initContent($pipelineGroupList);

        $receiverStageContent->initContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(array(
                            $receiverGroupList,
                            $receiverModalCreate,
                            $receiverModalEdit,
                            $receiverModalDestroy
                        ))
                    ),
                ), new Title(new PersonGroup() . ' Bestehende Benutzergruppen')),
            ))
        );

        $Stage->setContent( $receiverStageContent );

        return $Stage;
    }
}
