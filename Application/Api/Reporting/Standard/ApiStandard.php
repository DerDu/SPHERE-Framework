<?php

namespace SPHERE\Application\Api\Reporting\Standard;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiStandard
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generate
 */
class ApiStandard extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('reloadAbsenceContent');

        return $Dispatcher->callMethod($Method);
    }

    public static function receiverFormSelect($Content = '')
    {

        return new BlockReceiver($Content);
    }

    /**
     * @param AbstractReceiver $Receiver
     *
     * @return Pipeline
     */
    public static function pipelineCreateAbsenceContent(AbstractReceiver $Receiver)
    {
        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter($Receiver, ApiStandard::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            ApiStandard::API_TARGET => 'reloadAbsenceContent'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Fehlzeiten werden aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param null $Data
     *
     * @return Layout|string
     */
    public function reloadAbsenceContent($Data = null)
    {
        if ($Data['Date'] == null) {
            $date = (new \DateTime('now'))->format('d.m.Y');
        } else {
            $date = $Data['Date'];
        }
        $dateTime = new \DateTime($date);

        if ($Data['Type'] != null) {
            $tblType = Type::useService()->getTypeById($Data['Type']);
        } else {
            $tblType = false;
        }

        $divisionName = $Data['DivisionName'];
        $groupName = $Data['GroupName'];
        $isGroup = false;
        if ($divisionName != '') {
            $divisionList = Division::useService()->getDivisionAllByName($divisionName);
            if (empty($divisionList)) {
                return new Warning('Klasse nicht gefunden', new Exclamation());
            }

            $absenceList = Absence::useService()->getAbsenceAllByDay($dateTime, $tblType ? $tblType : null, $divisionList);
        } elseif ($groupName != '') {
            $isGroup = true;
            $groupList = Group::useService()->getGroupListLike($groupName);
            if (empty($groupList)) {
                return new Warning('Gruppe nicht gefunden', new Exclamation());
            }

            $absenceList = Absence::useService()->getAbsenceAllByDay($dateTime, $tblType ? $tblType : null, array(), $groupList);
        } else {
            $absenceList = Absence::useService()->getAbsenceAllByDay($dateTime, $tblType ? $tblType : null);
        }

        $title = new Title(
            'Fehlzeiten für den ' . $dateTime->format('d.m.Y')
            . ($tblType ? ', Schulart: ' . $tblType->getName() : '')
        );

        if (!empty($absenceList)) {
            if ($isGroup) {
                $columns = array(
                    'Type' => 'Schulart',
                    'Group' => 'Gruppe',
                    'Person' => 'Schüler',
                    'DateSpan' => 'Zeitraum',
                    'Status' => 'Status',
                    'Remark' => 'Bemerkung'
                );
            } else {
                $columns = array(
                    'Type' => 'Schulart',
                    'Division' => 'Klasse',
                    'Person' => 'Schüler',
                    'DateSpan' => 'Zeitraum',
                    'Status' => 'Status',
                    'Remark' => 'Bemerkung'
                );
            }

            return new Layout(new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Primary(
                            'Herunterladen', '/Api/Reporting/Standard/Person/AbsenceList/Download',
                            new Download(),
                            array(
                                'Date' => $Data['Date'],
                                'Type' => $Data['Type'],
                                'DivisionName' => $Data['DivisionName'],
                                'GroupName' => $Data['GroupName']
                            )
                        )
                    )
                ),
                new LayoutRow(
                    new LayoutColumn(
                        $title
                        . new TableData(
                            $absenceList,
                            null,
                            $columns,
                            array(
                                'order' => array(
                                    array('0', 'asc'),
                                    array('1', 'asc'),
                                    array('2', 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 1),
                                    //  geht aktuell nicht zusammen mit order beide Spalten
//                                  array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                ),
                            )
                        )
                    )
                )
            )));
        } else {
            return
                $title
                . new Warning('Für diesen Tag liegen keine Fehlzeiten vor.', new Ban());
        }
    }
}