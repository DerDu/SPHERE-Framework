<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.07.2016
 * Time: 16:29
 */

namespace SPHERE\Application\Education\Certificate\Approve;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Approve
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendSelectPrepare($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Zeugnisse freigeben', 'Übersicht');

        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && $tblYearList) {
            $tblYear = end($tblYearList);
        }

        if ($tblYearList) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $Stage->addButton(new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        '/Education/Certificate/Approve', new Edit(), array('YearId' => $tblYearItem->getId())));
                } else {
                    $Stage->addButton(new Standard($tblYearItem->getDisplayName(), '/Education/Certificate/Approve',
                        null, array('YearId' => $tblYearItem->getId())));
                }
            }

            if ($IsAllYears) {
                $Stage->addButton(new Standard(new Info(new Bold('Alle Schuljahre')),
                    '/Education/Certificate/Approve', new Edit(), array('IsAllYears' => true)));
            } else {
                $Stage->addButton(new Standard('Alle Schuljahre', '/Education/Certificate/Approve', null,
                    array('IsAllYears' => true)));
            }
        }

        $content = false;
        if ($tblYear) {
            $tblPrepareList = Prepare::useService()->getPrepareAllByYear($tblYear);
            $prepareList = array();
            if ($tblPrepareList) {
                foreach ($tblPrepareList as $tblPrepare) {
                    $countStudents = 0;
                    $countApproved = 0;
                    $tblDivision = $tblPrepare->getServiceTblDivision();
                    if ($tblDivision) {
                        if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                            $countStudents = count($tblPersonList);
                            foreach ($tblPersonList as $tblPerson) {
                                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                        $tblPerson))
                                    && $tblPrepareStudent->isApproved()
                                ) {
                                    $countApproved++;
                                }
                            }
                        }
                    }

                    $prepareList[] = array(
                        'Date' => $tblPrepare->getDate(),
                        'Name' => $tblPrepare->getName(),
                        'Division' => $tblDivision ? $tblDivision->getDisplayName() : '',
                        'Status' => $countApproved < $countStudents
                            ? new Warning(new Exclamation() . ' ' . $countApproved . ' von ' . $countStudents . ' Zeugnisse freigegeben.')
                            : new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                . ' ' . $countApproved . ' von ' . $countStudents . ' Zeugnissen freigegeben.'),
                        'Option' =>
                            (new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare',
                                new EyeOpen(),
                                array(
                                    'PrepareId' => $tblPrepare->getId()
                                ),
                                'Klassenansicht -> Zeugnisse einzeln freigeben'
                            ))
                            . (new External(
                                '',
                                '/Api/Education/Certificate/Generator/PreviewZip',
                                new Download(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'Name' => 'Zeugnismuster'
                                ), 'Alle Zeugnisse als Muster herunterladen'))
                            . (new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                                new Check(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'Route' => '/Education/Certificate/Approve'
                                ),
                                'Alle Zeugnisse dieser Klasse freigeben'
                            ))
                            . (new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                new Disable(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'Route' => '/Education/Certificate/Approve'
                                ),
                                'Alle Zeugnisfreigaben dieser Klasse entfernen'
                            ))
                    );
                }

                $content = new TableData($prepareList, null,
                    array(
                        'Date' => 'Zeugnisdatum',
                        'Division' => 'Klasse',
                        'Name' => 'Name',
                        'Status' => 'Freigaben',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc'),
                            array(1, 'asc'),
                            array(2, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 0),
                            array('type' => 'natural', 'targets' => 1),
                        )
                    )
                );
            }
        } else {
            $tblPrepareList = Prepare::useService()->getPrepareAll();
            $prepareList = array();
            if ($tblPrepareList) {
                foreach ($tblPrepareList as $tblPrepare) {
                    $tblDivision = $tblPrepare->getServiceTblDivision();

                    $prepareList[] = array(
                        'Year' => $tblDivision->getServiceTblYear()
                            ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                        'Date' => $tblPrepare->getDate(),
                        'Name' => $tblPrepare->getName(),
                        'Division' => $tblDivision ? $tblDivision->getDisplayName() : '',
                        'Option' =>
                            (new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare',
                                new EyeOpen(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'IsAllYears' => true
                                ),
                                'Klassenansicht -> Zeugnisse einzeln freigeben'
                            ))
                            . (new External(
                                '',
                                '/Api/Education/Certificate/Generator/PreviewZip',
                                new Download(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'Name' => 'Zeugnismuster'
                                ), 'Alle Zeugnisse als Muster herunterladen'))
                            . (new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                                new Check(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'Route' => '/Education/Certificate/Approve',
                                    'IsAllYears' => true
                                ),
                                'Alle Zeugnisse dieser Klasse freigeben'
                            ))
                            . (new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                new Disable(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'Route' => '/Education/Certificate/Approve',
                                    'IsAllYears' => true
                                ),
                                'Alle Zeugnisfreigaben dieser Klasse entfernen'
                            ))
                    );
                }

                $content = new TableData($prepareList, null,
                    array(
                        'Year' => 'Schuljahr',
                        'Date' => 'Zeugnisdatum',
                        'Division' => 'Klasse',
                        'Name' => 'Name',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc'),
                            array(1, 'desc'),
                            array(2, 'asc'),
                            array(3, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 1),
                            array('type' => 'natural', 'targets' => 2),
                        )
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Schuljahr',
                                $tblYear ? $tblYear->getDisplayName() : 'Alle Schuljahre',
                                Panel::PANEL_TYPE_INFO
                            ),
                        )),
                        new LayoutColumn(array(
                            // Massenfreigabe aktuell nicht performant realisierbar
//                            new Standard(
//                                'Alle Zeugnisse aller Klassen freigeben',
//                                '',
//                                new Check(),
//                                array(
//
//                                )
//                            ),
                            $content ? $content : null
                        )),
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PrepareId
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendDivision($PrepareId = null, $IsAllYears = false)
    {

        $Stage = new Stage('Zeugnisse freigeben', 'Klassenansicht');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $studentTable = array();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(),
                    $IsAllYears ?
                        array(
                            'IsAllYears' => $IsAllYears
                        )
                        : array(
                        'YearId' => ($tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getId() : null)
                    )
                ));

                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    foreach ($tblStudentList as $tblPerson) {
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

                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                        if ($tblPrepareStudent) {
                            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                            $isApproved = $tblPrepareStudent->isApproved();
                        } else {
                            $tblCertificate = false;
                            $isApproved = false;
                        }

                        $studentTable[] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName(),
                            'Course' => $course,
                            'ExcusedAbsence' => Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision),
                            'UnexcusedAbsence' => Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                $tblDivision),
                            'Template' => ($tblCertificate
                                ? new Success(new Enable() . ' ' . $tblCertificate->getName()
                                    . ($tblCertificate->getDescription() ? '<br>' . $tblCertificate->getDescription() : ''))
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' Keine Zeugnisvorlage ausgewählt')),
                            'Status' =>
                                $isApproved
                                    ? new Success(new Enable() . ' freigegeben')
                                    : new Warning(new Exclamation() . ' nicht freigegeben'),
                            'Option' =>
                                ($tblCertificate ? new External(
                                    'Zeugnis herunterladen',
                                    '/Api/Education/Certificate/Generator/Preview',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                    ), false) : '')
                                . (!$isApproved && $tblCertificate ? (new Standard(
                                    'Zeugnis freigeben', '/Education/Certificate/Approve/Prepare/SetApproved',
                                    new Check(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                        'IsAllYears' => $IsAllYears
                                    ),
                                    'Zeugnis freigeben')) : '')
                                . ($isApproved ? (new Standard(
                                    'Zeugnisfreigabe entfernen', '/Education/Certificate/Approve/Prepare/ResetApproved',
                                    new Disable(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                        'IsAllYears' => $IsAllYears
                                    ),
                                    'Zeugnisfreigabe entfernen')) : '')
                        );
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new External(
                                    'Alle Zeugnisse als Muster herunterladen',
                                    '/Api/Education/Certificate/Generator/PreviewZip',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Name' => 'Zeugnismuster'
                                    ), 'Alle Zeugnisse als Muster herunterladen'),
                                new Standard(
                                    'Alle Zeugnisse dieser Klasse freigeben',
                                    '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                                    new Check(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Route' => '/Education/Certificate/Approve/Prepare',
                                        'IsAllYears' => $IsAllYears
                                    )
                                ),
                                new Standard(
                                    'Alle Zeugnisfreigaben dieser Klasse entfernen',
                                    '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                    new Disable(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Route' => '/Education/Certificate/Approve/Prepare',
                                        'IsAllYears' => $IsAllYears
                                    )
                                ),
                            )),
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($studentTable, null, array(
                                    'Number' => '#',
                                    'Name' => 'Name',
                                    'Course' => 'Bildungs&shy;gang',
                                    'Template' => 'Zeugnis&shy;vorlage',
                                    'Status' => 'Freigabe',
                                    'Option' => ''
                                ), array(
                                    'order' => array(
                                        array('0', 'asc'),
                                    ),
                                    "paging" => false, // Deaktivieren Blättern
                                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                                ))
                            ))
                        ))
                    ), new Title('Übersicht'))
                ))
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendApprovePreparePerson($PrepareId = null, $PersonId = null, $IsAllYears = false)
    {
        $Stage = new Stage('Zeugnisse freigeben', 'Freigabe');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Approve/Prepare', new ChevronLeft(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                    'IsAllYears' => $IsAllYears
                )
            ));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson ? $tblPerson->getLastFirstName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 12),
                        )),
                    )),
                ))
            );

            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                Prepare::useService()->updatePrepareStudentSetApproved($tblPrepareStudent);

                return $Stage
                . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnis wurde freigegeben.',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Certificate/Approve/Prepare', Redirect::TIMEOUT_SUCCESS,
                    array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears));
            }

            return $Stage;

        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Approve', new ChevronLeft(), array('IsAllYears' => $IsAllYears)
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendResetApprovePreparePerson($PrepareId = null, $PersonId = null, $IsAllYears = false)
    {
        $Stage = new Stage('Zeugnis', 'Freigabe entfernen');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Approve/Prepare', new ChevronLeft(),
                array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears)
            ));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson ? $tblPerson->getLastFirstName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 12),
                        )),
                    )),
                ))
            );

            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                Prepare::useService()->updatePrepareStudentResetApproved($tblPrepareStudent);

                return $Stage
                . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisfreigabe wurde entfernt.',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Certificate/Approve/Prepare', Redirect::TIMEOUT_SUCCESS,
                    array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears));
            }

            return $Stage;

        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Approve', new ChevronLeft(), array('IsAllYears' => $IsAllYears)
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param string $Route
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendApprovePrepareDivision(
        $PrepareId = null,
        $Route = '/Education/Certificate/Approve/Prepare',
        $IsAllYears = false
    ) {
        $Stage = new Stage('Zeugnisse freigeben', 'Klasse freigeben');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                        )),
                    )),
                ))
            );

            Prepare::useService()->updatePrepareDivisionSetApproved($tblPrepare);

            return $Stage
            . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisse wurden freigegeben.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears));
        } else {
            $Stage->addButton(new Standard(
                'Zurück', $Route, new ChevronLeft(), array('IsAllYears' => $IsAllYears)
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param string $Route
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendResetApprovePrepareDivision(
        $PrepareId = null,
        $Route = '/Education/Certificate/Approve/Prepare',
        $IsAllYears = false
    ) {
        $Stage = new Stage('Zeugnisse freigeben', 'Klassen Freigabe entfernen');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                        )),
                    )),
                ))
            );

            Prepare::useService()->updatePrepareDivisionResetApproved($tblPrepare);

            return $Stage
            . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisfreigaben wurden entfernt.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears));
        } else {
            $Stage->addButton(new Standard(
                'Zurück', $Route, new ChevronLeft()
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }
}