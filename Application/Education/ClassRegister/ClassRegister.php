<?php
namespace SPHERE\Application\Education\ClassRegister;

use SPHERE\Application\Api\People\Meta\Agreement\ApiAgreementReadOnly;
use SPHERE\Application\Api\People\Meta\MedicalRecord\MedicalRecordReadOnly;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Commodity;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

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
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Integration', __CLASS__ . '::frontendIntegration')
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
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Sort\Gender', __NAMESPACE__.'\Sort\Frontend::frontendSortDivisionGender')
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
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendDivisionTeacher($IsAllYears = false, $YearId = null)
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

        $buttonList = Evaluation::useFrontend()->setYearButtonList('/Education/ClassRegister/Teacher',
            $IsAllYears, $YearId, $tblYear, false);

        if ($tblPerson) {
            $tblDivisionList = Division::useService()->getDivisionAllByTeacher($tblPerson);
        } else {
            $tblDivisionList = false;
        }

        return $this->getDivisionSelectStage($Stage, $tblDivisionList, '/Education/ClassRegister/Teacher', $tblYear, $buttonList);
    }

    /**
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendDivisionAll($IsAllYears = false, $YearId = null)
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

        $buttonList = Evaluation::useFrontend()->setYearButtonList('/Education/ClassRegister/All',
            $IsAllYears, $YearId, $tblYear);

        $tblDivisionList = Division::useService()->getDivisionAll();
        return $this->getDivisionSelectStage($Stage, $tblDivisionList, '/Education/ClassRegister/All', $tblYear, $buttonList);
    }

    /**
     * @param Stage $Stage
     * @param array $tblDivisionList
     * @param string $BasicRoute
     * @param bool|TblYear $tblYear
     * @param array $buttonList
     *
     * @return Stage
     */
    public function getDivisionSelectStage(
        Stage $Stage,
        $tblDivisionList,
        $BasicRoute = '/Education/ClassRegister/Teacher',
        $tblYear = false,
        $buttonList = array()
    ) {
        $divisionTable = array();
        if ($tblDivisionList) {
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                /** @var TblYear $tblYear */
                if ($tblYear && $tblDivision  && $tblDivision->getServiceTblYear()
                    && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                ) {
                    continue;
                }

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
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            new TableData($divisionTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Option' => ''
                            ), array(
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2),
                                    array("orderable" => false, "targets"   => 3),
                                ),
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
            $IsSortable = Access::useService()->hasAuthorization('/Api/Education/ClassRegister/Reorder');
            $studentTable = array();
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblStudentList) {
                foreach ($tblStudentList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $birthday = '';
                    $Gender = '';
                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                        if ($tblCommon->getTblCommonBirthDates()) {
                            $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                            $tblGender = $tblCommon->getTblCommonBirthDates()->getTblCommonGender();
                            if ($tblGender) {
                                $Gender = $tblGender->getName();
                                switch ($Gender) {
                                    case 'Männlich':
                                        $Gender = 'M';
                                        break;
                                    case 'Weiblich':
                                        $Gender = 'W';
                                        break;
                                }
                            }
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


                    if(Student::useService()->getIsSupportByPerson($tblPerson)) {
                        $IntegrationButton = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                            ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                    } else {
                        $IntegrationButton = '';
                    }

                    if ($tblStudent
                        && ($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
                        && ($tblMedicalRecord->getDisease()
                            || $tblMedicalRecord->getMedication()
                            || $tblMedicalRecord->getAttendingDoctor())) {
                        $MedicalRecord = (new Standard('', MedicalRecordReadOnly::getEndpoint(), new EyeOpen()))
                            ->ajaxPipelineOnClick(MedicalRecordReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                    } else {
                        $MedicalRecord = '';
                    }
                    $Agreement = (new Standard('', ApiAgreementReadOnly::getEndpoint(), new EyeOpen()))
                        ->ajaxPipelineOnClick(ApiAgreementReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));

                    $studentTable[] = array(
                        'Number'        => (count($studentTable) + 1),
                        'Name'          => (($isTeacher || !$IsSortable)
                                            ? $tblPerson->getLastFirstName()
                                            : new PullClear(
                                                new PullLeft(new ResizeVertical().' '.$tblPerson->getLastFirstName())
                                            )),
                        'Integration'   => $IntegrationButton,
                        'MedicalRecord' => $MedicalRecord,
                        'Agreement'     => $Agreement,
                        'Gender'        => $Gender,
                        'Address'       => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Birthday'      => $birthday,
                        'Course'        => $course,
                        'Absence'       => $absence,
                        'Option'        => new Standard(
                            '', '/Education/ClassRegister/Absence', new Time(),
                            array(
                                'DivisionId' => $tblDivision->getId(),
                                'PersonId'   => $tblPerson->getId(),
                                'BasicRoute' => $isTeacher
                                    ? '/Education/ClassRegister/Teacher' : '/Education/ClassRegister/All'
                            ),
                            'Fehlzeiten des Schülers verwalten'
                        ).new Standard(
                            '', '/Education/ClassRegister/Integration', new Commodity(),
                            array(
                                'DivisionId' => $tblDivision->getId(),
                                'PersonId'   => $tblPerson->getId(),
                                'BasicRoute' => $isTeacher
                                    ? '/Education/ClassRegister/Teacher/Selected' : '/Education/ClassRegister/All/Selected'
                            ),
                            'Integration des Schülers verwalten'
                        )
                    );
                }
            }

            if (!$isTeacher) {
                $buttonList[] = new Standard(
                    'Sortierung alphabetisch', '/Education/ClassRegister/Sort', new ResizeVertical(),
                    array(
                        'DivisionId' => $tblDivision->getId()
                    )
                );
                $buttonList[] = new Standard(
                    'Sortierung Geschlecht (alphabetisch)', '/Education/ClassRegister/Sort/Gender',
                    new ResizeVertical(),
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
            $buttonList[] = new Standard('Download Klassenliste Krankenakte'
                , '/Api/Reporting/Standard/Person/MedicalRecordClassList/Download', new Download(), array(
                    'DivisionId' => $tblDivision->getId()
                )
            );
            $buttonList[] = new Standard('Download Klassenliste Einverständniserklärung'
                , '/Api/Reporting/Standard/Person/AgreementClassList/Download', new Download(), array(
                    'DivisionId' => $tblDivision->getId()
                )
            );

            $YearString = new Muted('-NA-');
            if (( $tblYear = $tblDivision->getServiceTblYear() )) {
                $YearString = $tblYear->getName();
            }

            $Stage->setContent(
                ApiSupportReadOnly::receiverOverViewModal()
                .MedicalRecordReadOnly::receiverOverViewModal()
                .ApiAgreementReadOnly::receiverOverViewModal()
                .new Layout(array(
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
                                    'Number'        => '#',
                                    'Name'          => 'Name',
                                    'Integration'   => 'Integration',
                                    'MedicalRecord' => 'Krankenakte',
                                    'Agreement'     => 'Einverständnis',
                                    'Gender'        => 'Geschlecht',
                                    'Address'       => 'Addresse',
                                    'Birthday'      => 'Geburtsdatum',
                                    'Course'        => 'Bildungsgang',
                                    'Absence'       => 'Fehlzeiten (E, U)',
                                    'Option'        => ''
                                ),
                                    ($isTeacher || !$IsSortable)
                                        ? array(
                                            'paging' => false,
                                            'columnDefs' => array(
                                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                                array('width' => '1%', 'targets' => 2),
                                            ),
                                        )
                                        : array(
                                        'ExtensionRowReorder' => array(
                                            'Enabled' => true,
                                            'Url'     => '/Api/Education/ClassRegister/Reorder',
                                            'Data'    => array('DivisionId' => $tblDivision->getId()
                                            )
                                        ),
                                        'paging' => false,
                                        'columnDefs' => array(
                                            array('type'  => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('width' => '1%', 'targets' => 2),
                                            array('width' => '60px', 'targets' => -1),
                                        ),
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

    /**
     * @param int    $DivisionId
     * @param int    $PersonId
     * @param string $BasicRoute
     *
     * @return Stage
     */
    public function frontendIntegration($DivisionId, $PersonId, $BasicRoute)
    {

        $Stage = new Stage('Integration', 'Verwalten');

        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft(),
            array( 'DivisionId' => $DivisionId,
                   'BasicRoute' => $BasicRoute,
            )));

        $PersonPanel = '';
        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $PersonPanel = new Panel('Person', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO);
        }
        $DivisionPanel = '';
        if(($tblDivision = Division::useService()->getDivisionById($DivisionId))){
            $DivisionPanel = new Panel('Klasse, Schulart', $tblDivision->getDisplayName().', '.$tblDivision->getTypeName(), Panel::PANEL_TYPE_INFO);
        }


        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $Content = (new Well(Student::useFrontend()->frontendIntegration($tblPerson)));
        } else {
            $Content = (new Warning('Person wurde nicht gefunden.'));
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $PersonPanel
                        , 6),
                        new LayoutColumn(
                            $DivisionPanel
                        , 6),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            $Content
                        )
                    )
                ))
            )
        );

        return $Stage;
    }
}
