<?php
namespace SPHERE\Application\Education\ClassRegister;

use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\Application\IApplicationInterface;

/**
 * Class ClassRegister
 *
 * @package SPHERE\Application\Education\ClassRegister
 */
class ClassRegister implements IApplicationInterface
{

    public static function registerApplication()
    {

        Absence::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Klassenbuch'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Selected', __CLASS__ . '::frontendDivisionSelected')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Sort', __CLASS__ . '::frontendSortDivision')
        );
    }

    /**
     * @return Stage
     */
    public function frontendDivision()
    {

        $Stage = new Stage('Klassenbuch', 'Auswählen');

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        if ($tblPerson) {
            $tblDivisionList = Division::useService()->getDivisionAllByTeacher($tblPerson);
        } else {
            $tblDivisionList = false;
        }

        $divisionTable = array();
        if ($tblDivisionList) {
            foreach ($tblDivisionList as $tblDivision) {
                $divisionTable[] = array(
                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Type' => $tblDivision->getTypeName(),
                    'Division' => $tblDivision->getDisplayName(),
                    'Option' => new Standard(
                        '', '/Education/ClassRegister/Selected', new Select(),
                        array(
                            'DivisionId' => $tblDivision->getId()
                        ),
                        'Auswählen'
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                )
                            ))
                        ))
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendDivisionSelected($DivisionId = null)
    {

        $Stage = new Stage('Klassenbuch', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/ClassRegister', new ChevronLeft()
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $studentTable = array();
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblStudentList) {
                foreach ($tblStudentList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $birthday = '';
                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                        if ($tblCommon->getTblCommonBirthDates()) {
                            $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                        }
                    }
                    $course = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        if ($tblTransferType) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                if ($tblCourse) {
                                    $course = $tblCourse->getName();
                                }
                            }
                        }
                    }
                    $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision);
                    $unExcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblDivision);
                    $absence = ($excusedDays + $unExcusedDays) . ' (' . new Success($excusedDays) . ', '
                        . new \SPHERE\Common\Frontend\Text\Repository\Danger($unExcusedDays) . ')';
                    $studentTable[] = array(
                        'Number' => (count($studentTable) + 1),
                        'Name' => new PullClear(
                            new PullLeft(new ResizeVertical() . ' ' . $tblPerson->getLastFirstName())
                        ),
                        'Address' => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Birthday' => $birthday,
                        'Course' => $course,
                        'Absence' => $absence,
                        'Option' => new Standard(
                            '', '/Education/ClassRegister/Absence', new Time(),
                            array(
                                'DivisionId' => $tblDivision->getId(),
                                'PersonId' => $tblPerson->getId()
                            ),
                            'Fehlzeiten des Schülers verwalten'
                        )
                    );
                }
            }

            $buttonList[] = new Standard(
                'Klasse nach Nachname->Vorname sortieren', '/Education/ClassRegister/Sort', new ResizeVertical(), array(
                    'DivisionId' => $tblDivision->getId()
                )
            );
            $buttonList[] = new Standard(
                'Fehlzeiten (Monatsansicht)', '/Education/ClassRegister/Absence/Month', new Calendar(), array(
                    'DivisionId' => $tblDivision->getId()
                )
            );

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            )),
                            new LayoutColumn($buttonList),
                            new LayoutColumn(array(
                                new TableData($studentTable, null, array(
                                    'Number' => '#',
                                    'Name' => 'Name',
                                    'Address' => 'Addresse',
                                    'Birthday' => 'Geburtsdatum',
                                    'Course' => 'Bildungsgang',
                                    'Absence' => 'Fehlzeiten (E, U)',
                                    'Option' => ''
                                ), array(
                                    'order' => array(
                                        array('0', 'asc'),
                                    ),
                                    "paging" => false, // Deaktivieren Blättern
                                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    'ExtensionRowReorder' => array(
                                        'Enabled' => true,
                                        'Url' => '/Api/Education/ClassRegister/Reorder',
//                                        'Event' => array(
//                                            'Success' => 'console.log(1,this);',
//                                            'Error' => 'window.location.reload();',
//                                        ),
                                        'Data' => array(
                                            'DivisionId' => $tblDivision->getId()
                                        )
                                    )
                                ))
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Klassenbuch nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $DivisionId
     *
     * @return string
     */
    public function frontendSortDivision($DivisionId = null)
    {

        $Stage = new Stage('Klassenbuch', 'Schüler sortieren');

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            if (Division::useService()->sortDivisionStudentByProperty($tblDivision)) {
                return $Stage . new \SPHERE\Common\Frontend\Message\Repository\Success(
                    'Die Schüler der Klasse wurden erfolgreich sortiert.',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success()
                )
                . new Redirect('/Education/ClassRegister/Selected', Redirect::TIMEOUT_SUCCESS,
                    array(
                        'DivisionId' => $tblDivision->getId()
                    )
                );
            } else {
                return $Stage . new Danger(
                    'Die Schüler der Klasse konnten nicht sortiert werden.',
                    new Exclamation()
                )
                . new Redirect('/Education/ClassRegister/Selected', Redirect::TIMEOUT_ERROR,
                    array(
                        'DivisionId' => $tblDivision->getId()
                    )
                );
            }
        } else {

            return $Stage
            . new Danger('Klassen nicht vorhanden.', new Ban())
            . new Redirect('/Education/ClassRegister', Redirect::TIMEOUT_ERROR);
        }

    }
}
