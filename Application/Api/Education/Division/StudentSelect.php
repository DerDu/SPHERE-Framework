<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 30.05.2017
 * Time: 09:12
 */

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division as DivisionApplication;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\System\Extension\Extension;

/**
 * Class StudentSelect
 *
 * @package SPHERE\Application\Api\Education\Division
 */
class StudentSelect extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method Callable Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('tablePerson');
        $Dispatcher->registerMethod('serviceAddPerson');
        $Dispatcher->registerMethod('serviceRemovePerson');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverUsed($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('UsedReceiver');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverAvailable($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('AvailableReceiver');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('ServiceReceiver');
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public static function getTableContentUsed(TblDivision $tblDivision)
    {

        $tblDivisionStudentList = DivisionApplication::useService()->getDivisionStudentAllByDivision($tblDivision, true);

        $usedList = array();
        if ($tblDivisionStudentList) {
            array_walk($tblDivisionStudentList, function (TblDivisionStudent $tblDivisionStudent) use ($tblDivision, &$usedList) {
                $isInActive = $tblDivisionStudent->isInActive();
                if (($tblPerson = $tblDivisionStudent->getServiceTblPerson())) {
                    $address = ($tblAddress = $tblPerson->fetchMainAddress())
                        ? $tblAddress->getGuiString()
                        : new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
                    $course = ($tblCourse = Student::useService()->getCourseByPerson($tblPerson)) ? $tblCourse->getName() : '';

                    $Item['Id'] = $tblPerson->getId();
                    $Item['DivisionId'] = $tblDivision->getId();
                    $Item['Name'] = $isInActive ? new Strikethrough($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName();
                    $Item['Address'] = $isInActive ? new Strikethrough($address) : $address;
                    $Item['Course'] = $isInActive ? new Strikethrough($course) : $course;

                    array_push($usedList, $Item);
                }
            });
        }

        return $usedList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public static function getTableContentAvailable(TblDivision $tblDivision)
    {

        $tblPersonUsedList = DivisionApplication::useService()->getStudentAllByDivision($tblDivision, true);
        $tblStudentList = false;
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && ($tblStudentList = Group::useService()->getPersonAllByGroup($tblGroup))  // Alle Schüler
            && ($tblLevel = $tblDivision->getTblLevel())
            && ($tblYear = $tblDivision->getServiceTblYear())
        ) {
            // Schüler darf nur in einer festen Klasse sein (keine Jahrgangsübergreifende Klasse
            if (!$tblLevel->getIsChecked()
                && ($tblDivisionList = DivisionApplication::useService()->getDivisionByYear($tblDivision->getServiceTblYear()))
            ) {
                foreach ($tblDivisionList as $tblSingleDivision) {
                    if (($tblSingleLevel = $tblSingleDivision->getTblLevel())
                        && !$tblSingleLevel->getIsChecked()
                        && ($tblDivisionStudentList = DivisionApplication::useService()->getStudentAllByDivision($tblSingleDivision))
                    ) {
                        $tblStudentList = array_udiff($tblStudentList, $tblDivisionStudentList,
                            function (TblPerson $tblPersonA, TblPerson $tblPersonB) {
                                return $tblPersonA->getId() - $tblPersonB->getId();
                            });
                    }
                }
            }
        }

        // get available Persons in compare of used Persons
        if (is_array($tblPersonUsedList) && $tblStudentList) {
            $tblPersonAvailable = array_diff($tblStudentList, $tblPersonUsedList);
        } else {
            $tblPersonAvailable = $tblStudentList;
        }

        $availableList = array();
        if ($tblPersonAvailable) {
            array_walk($tblPersonAvailable, function (TblPerson $tblPerson) use ($tblDivision, &$availableList) {
                $Item['Id'] = $tblPerson->getId();
                $Item['DivisionId'] = $tblDivision->getId();
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Address'] = ($tblAddress = $tblPerson->fetchMainAddress())
                    ? $tblAddress->getGuiString()
                    : new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
                $Item['Course'] = ($tblCourse = Student::useService()->getCourseByPerson($tblPerson)) ? $tblCourse->getName() : '';
                array_push($availableList, $Item);
            });
        }
        return $availableList;
    }

    /**
     * @param null $DivisionId
     *
     * @return Layout
     */
    public static function tablePerson($DivisionId = null)
    {

        // get Content
        $tblDivision = DivisionApplication::useService()->getDivisionById($DivisionId);
        $ContentList = false;
        $ContentListAvailable = false;
        if ($tblDivision) {
            $ContentList = self::getTableContentUsed($tblDivision);
            $ContentListAvailable = self::getTableContentAvailable($tblDivision);
        }

        // Select
        $Table = array();
        if (is_array($ContentList)) {
            if (!empty($ContentList)) {
                $count = 1;
                foreach ($ContentList as $Person) {
                    $Table[] = array(
                        'Number'  => $count++,
                        'Name' => $Person['Name'],
                        'Address' => $Person['Address'],
                        'Course' => $Person['Course'],
                        'Option' => (new Standard('', self::getEndpoint(), new MinusSign(),
                            array('Id' => $Person['Id'], 'DivisionId' => $Person['DivisionId']), 'Entfernen'))
                            ->ajaxPipelineOnClick(self::pipelineMinus($Person['Id'], $Person['DivisionId']))
                    );
                }
                // Anzeige
                $left = (new TableData($Table, new Title('Ausgewählte', 'Schüler'), array(
                    'Number'  => '#',
                    'Name' => 'Name',
                    'Address' => 'Adresse',
                    'Course' => 'Bildungsgang',
                    'Option' => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => array(0,-1)),
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                        ),
                    )
                ))->setHash(__NAMESPACE__ . 'StudentSelect' . 'Selected');
            } else {
                $left = new Info('Keine Schüler ausgewählt');
            }
        } else {
            $left = new Warning('Klasse nicht gefunden');
        }

        // Select
        $TableAvailable = array();
        if (is_array($ContentListAvailable)) {
            if (!empty($ContentListAvailable)) {
                foreach ($ContentListAvailable as $Person) {
                    $TableAvailable[] = array(
                        'Name' => $Person['Name'],
                        'Address' => $Person['Address'],
                        'Course' => $Person['Course'],
                        'Option' => (new Standard('', self::getEndpoint(), new PlusSign(),
                            array('Id' => $Person['Id'], 'DivisionId' => $Person['DivisionId']), 'Hinzufügen'))
                            ->ajaxPipelineOnClick(self::pipelinePlus($Person['Id'], $Person['DivisionId']))
                    );
                }
                // Anzeige
                $right = (new TableData($TableAvailable, new Title('Verfügbare', 'Schüler'), array(
                    'Name' => 'Name',
                    'Address' => 'Adresse',
                    'Course' => 'Bildungsgang',
                    'Option' => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                        ),
                    )
                ))->setHash(__NAMESPACE__ . 'StudentSelect' . 'Available');
            } else {
                $right = new Info('Keine weiteren Schüler verfügbar');
            }
        } else {
            $right = new Warning('Klasse nicht gefunden');
        }

        return
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $left
                    , 6),
                new LayoutColumn(
                    $right
                    , 6)
            ))));

    }

    /**
     * @param null $Id
     * @param null $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineMinus($Id = null, $DivisionId = null)
    {

        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceRemovePerson',
            'Id' => $Id,
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tablePerson',
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     */
    public function serviceRemovePerson($Id = null, $DivisionId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($Id))
            && ($tblDivision = DivisionApplication::useService()->getDivisionById($DivisionId))
        ) {

            DivisionApplication::useService()->removeStudentToDivision($tblDivision, $tblPerson);
        }
    }


    /**
     * @param null $Id
     * @param null $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelinePlus($Id = null, $DivisionId = null)
    {

        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceAddPerson',
            'Id' => $Id,
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tablePerson',
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     */
    public function serviceAddPerson($Id = null, $DivisionId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($Id))
            && ($tblDivision = DivisionApplication::useService()->getDivisionById($DivisionId))
        ) {

            DivisionApplication::useService()->addStudentToDivision($tblDivision, $tblPerson);
        }
    }
}