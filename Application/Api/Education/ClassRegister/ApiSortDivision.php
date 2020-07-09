<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiSortDivision
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ApiSortDivision extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('sortContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('SortModalReceiver');
    }

    /**
     * @param int|null $DivisionId
     * @param string   $sortType
     *
     * @return Pipeline
     */
    public static function pipelineOpenSortModal($DivisionId = null, $sortType = '')
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'sortContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'sortType' => $sortType,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null   $DivisionId
     * @param string $sortType
     *
     * @return Layout
     */
    public static function sortContent($DivisionId = null, $sortType = '')
    {

        $button = new ToolTip((new Standard('Ja', '#'))->setDisabled(), 'Sortierung wurde nicht gefunden');
        if($sortType == 'Sortierung alphabetisch'){
            $button = new Standard(
                'Ja', '/Education/ClassRegister/Sort', new Ok(), array('DivisionId' => $DivisionId)
            );
        } elseif($sortType == 'Sortierung Geschlecht (alphabetisch)'){
            $button = new Standard('Ja', '/Education/ClassRegister/Sort/Gender', new Ok(),
                array('DivisionId' => $DivisionId));
        }

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Panel('"'.new Bold($sortType).'" Sollen alle Schüler der Klasse neu sortiert werden?',
                $button.new Close('Nein'), Panel::PANEL_TYPE_WARNING)
            )
        ))));

    }
}