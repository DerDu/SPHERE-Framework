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
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Select;
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
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendSelectPrepare($YearId = null)
    {

        $Stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');

        $tblYearDisplayList = array();
        $tblYearList = Term::useService()->getYearAllSinceYears(2);
        if ($tblYearList) {
            foreach ($tblYearList as $item) {
                if (Prepare::useService()->getPrepareAllByYear($item)) {
                    $tblYearDisplayList[$item->getId()] = $item;
                }
            }
        }

        $tblYear = Term::useService()->getYearById($YearId);

        if (!empty($tblYearDisplayList)) {
            $tblYearDisplayList = $this->getSorter($tblYearDisplayList)->sortObjectBy('DisplayName');

            if (count($tblYearDisplayList) > 0) {
                /** @var TblYear $year */
                foreach ($tblYearDisplayList as $year) {
                    $Stage->addButton(new Standard(
                        $year->getDisplayName(),
                        '/Education/Certificate/Approve',
                        null,
                        array('YearId' => $year->getId())
                    ));
                }
            } else {
                $tblYear = current($tblYearDisplayList);
            }
        }

        $content = false;
        if ($tblYear) {
            $tblPrepareList = Prepare::useService()->getPrepareAllByYear($tblYear);
            $prepareList = array();
            if ($tblPrepareList) {
                foreach ($tblPrepareList as $tblPrepare) {
                    $tblDivision = $tblPrepare->getServiceTblDivision();
                    if ($tblDivision) {
                        $countStudent = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                    } else {
                        $countStudent = 0;
                    }

                    $prepareList[] = array(
                        'Date' => $tblPrepare->getDate(),
                        'Name' => $tblPrepare->getName(),
                        'Division' => $tblDivision ? $tblDivision->getDisplayName() : '',
                        'CountCertificate' => $countStudent,
                        'Option' =>
                            $tblPrepare->getServiceTblAppointedDateTask()
                            && $tblPrepare->getServiceTblBehaviorTask()
                                ? new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare',
                                new Select(),
                                array(
                                    'PrepareId' => $tblPrepare->getId()
                                ),
                                'Zeugnisvorbereitung auswählen und Zeugnisse freigeben'
                            )
                                : ''
                    );
                }

                $content = new TableData($prepareList, null,
                    array(
                        'Date' => 'Zeugnisdatum',
                        'Division' => 'Klasse',
                        'Name' => 'Name',
                        'CountCertificate' => 'Zeugnisse',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc'),
                            array(1, 'asc'),
                            array(2, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 0)
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
                                $tblYear ? $tblYear->getDisplayName() : new Warning(new Exclamation()
                                    . ' Bitte wählen Sie ein Schuljahr aus'),
                                Panel::PANEL_TYPE_INFO
                            ),
                        )),
                        new LayoutColumn(array(
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
     *
     * @return Stage
     */
    public function frontendDivision($PrepareId = null)
    {

        $Stage = new Stage('Zeugnis', 'Übersicht');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $studentTable = array();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(),
                    array(
                        'YearId' => ($tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getId() : null)
                    )
                ));

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
                            'Address' => $tblAddress ? $tblAddress->getGuiTwoRowString() : '',
                            'Birthday' => $birthday,
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
                                        'PersonId' => $tblPerson->getId()
                                    ),
                                    'Zeugnis freigeben')) : '')
                                . ($isApproved ? (new Standard(
                                    'Zeugnisfreigabe entfernen', '/Education/Certificate/Approve/Prepare/ResetApproved',
                                    new Disable(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId()
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
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                '<br>',
                                new TableData($studentTable, null, array(
                                    'Number' => '#',
                                    'Name' => 'Name',
                                    'Address' => 'Adresse',
                                    'Birthday' => 'Geburts&shy;datum',
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
     *
     * @return Stage|string
     */
    public function frontendApprovePrepare($PrepareId = null, $PersonId = null)
    {
        $Stage = new Stage('Zeugnis', 'Freigeben');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Approve/Prepare', new ChevronLeft(),
                array('PrepareId' => $tblPrepare->getId())
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
                    array('PrepareId' => $tblPrepare->getId()));
            }

            return $Stage;

        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Approve', new ChevronLeft()
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendResetApprovePrepare($PrepareId = null, $PersonId = null)
    {
        $Stage = new Stage('Zeugnis', 'Freigabe entfernen');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Approve/Prepare', new ChevronLeft(),
                array('PrepareId' => $tblPrepare->getId())
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
                    array('PrepareId' => $tblPrepare->getId()));
            }

            return $Stage;

        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Approve', new ChevronLeft()
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }
}