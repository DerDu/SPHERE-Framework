<?php
namespace SPHERE\Application\Education\ClassRegister;

use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
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
            __NAMESPACE__ . '\Teacher', __CLASS__ . '::frontendDivisionTeacher'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\All', __CLASS__ . '::frontendDivisionAll'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Teacher\Selected', __CLASS__ . '::frontendDivisionTeacherSelected')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\All\Selected', __CLASS__ . '::frontendDivisionAllSelected')
        );

        /*
         * ReportingClassList
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\DivisionList', __NAMESPACE__ . '\ReportingClassList\Frontend::frontendDivisionList')
        );

        /*
         * Sort
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Sort', __NAMESPACE__ . '\Sort\Frontend::frontendSortDivision')
        );

    }

    /**
     * @return Stage
     */
    public function frontendDivision()
    {
        $hasAllRight = Access::useService()->hasAuthorization('/Education/ClassRegister/All');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/ClassRegister/Teacher');

        if ($hasAllRight) {
            if ($hasTeacherRight) {
                return $this->frontendDivisionTeacher();
            } else {
                return $this->frontendDivisionAll();
            }
        } else {
            return $this->frontendDivisionTeacher();
        }
    }

    /**
     * @return Stage
     */
    public function frontendDivisionTeacher()
    {

        $Stage = new Stage('Klassenbuch', 'Auswählen');

        $hasAllRight = Access::useService()->hasAuthorization('/Education/ClassRegister/All');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/ClassRegister/Teacher');
        if ($hasAllRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                '/Education/ClassRegister/Teacher', new Edit()));
            $Stage->addButton(new Standard('Ansicht: Alle Klassenbücher', '/Education/ClassRegister/All'));
        }

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

        return $this->getDivisionSelectStage($Stage, $tblDivisionList);
    }

    /**
     * @return Stage
     */
    public function frontendDivisionAll()
    {

        $Stage = new Stage('Klassenbuch', 'Auswählen');

        $hasAllRight = Access::useService()->hasAuthorization('/Education/ClassRegister/All');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/ClassRegister/Teacher');
        if ($hasAllRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer',
                '/Education/ClassRegister/Teacher'));
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Alle Klassenbücher')),
                '/Education/ClassRegister/All', new Edit()));
        }


        $tblDivisionList = Division::useService()->getDivisionAll();
        return $this->getDivisionSelectStage($Stage, $tblDivisionList, '/Education/ClassRegister/All');
    }

    /**
     * @param Stage $Stage
     * @param array $tblDivisionList
     * @param string $BasicRoute
     *
     * @return Stage
     */
    public function getDivisionSelectStage(
        Stage $Stage,
        $tblDivisionList,
        $BasicRoute = '/Education/ClassRegister/Teacher'
    ) {
        $divisionTable = array();
        if ($tblDivisionList) {
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                $divisionTable[] = array(
                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Type' => $tblDivision->getTypeName(),
                    'Division' => $tblDivision->getDisplayName(),
                    'Option' => new Standard(
                        '', $BasicRoute . '/Selected', new Select(),
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
     * @return Stage|string
     */
    public function frontendDivisionTeacherSelected($DivisionId = null)
    {

        $Stage = new Stage('Klassenbuch', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/ClassRegister/Teacher', new ChevronLeft()
        ));

        return $this->getDivisionSelectedStage($Stage, $DivisionId, true);
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage|string
     */
    public function frontendDivisionAllSelected($DivisionId = null)
    {

        $Stage = new Stage('Klassenbuch', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/ClassRegister/All', new ChevronLeft()
        ));

        return $this->getDivisionSelectedStage($Stage, $DivisionId);
    }

    /**
     * @param Stage $Stage
     * @param $DivisionId
     * @param bool $isTeacher
     *
     * @return Stage|string
     */
    public function getDivisionSelectedStage(Stage $Stage, $DivisionId, $isTeacher = false)
    {

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
                        'Name' => $isTeacher
                            ? $tblPerson->getLastFirstName()
                            : new PullClear(
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
                                'PersonId' => $tblPerson->getId(),
                                'BasicRoute' => $isTeacher
                                    ? '/Education/ClassRegister/Teacher' : '/Education/ClassRegister/All'
                            ),
                            'Fehlzeiten des Schülers verwalten'
                        )
                    );
                }
            }

            if (!$isTeacher) {
                $buttonList[] = new Standard(
                    'Klasse nach Nachname->Vorname sortieren', '/Education/ClassRegister/Sort', new ResizeVertical(),
                    array(
                        'DivisionId' => $tblDivision->getId()
                    )
                );
            }
            $buttonList[] = new Standard(
                'Fehlzeiten (Monatsansicht)', '/Education/ClassRegister/Absence/Month', new Calendar(), array(
                    'DivisionId' => $tblDivision->getId(),
                    'BasicRoute' => $isTeacher ? '/Education/ClassRegister/Teacher' : '/Education/ClassRegister/All'
                )
            );
            $buttonList[] = new Standard(
                'Klassenliste (Auswertung)', '/Education/ClassRegister/DivisionList', new EyeOpen(), array(
                    'DivisionId' => $tblDivision->getId(),
                    'BasicRoute' => $isTeacher ? '/Education/ClassRegister/Teacher' : '/Education/ClassRegister/All'
                )
            );

            $YearString = new Muted('-NA-');
            if (( $tblYear = $tblDivision->getServiceTblYear() )) {
                $YearString = $tblYear->getName();
            }

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
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schuljahr',
                                    $YearString,
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
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
                                ),
                                    $isTeacher
                                        ? array(
                                            'paging' => false
                                        )
                                        : array(
                                        'ExtensionRowReorder' => array(
                                            'Enabled' => true,
                                            'Url' => '/Api/Education/ClassRegister/Reorder',
                                            'Data' => array(
                                                'DivisionId' => $tblDivision->getId()
                                            )
                                        ),
                                        'paging' => false
                                    )
                                )
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
}
