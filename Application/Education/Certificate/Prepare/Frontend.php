<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESZC\CheBeGs;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quote;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\Star;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendSelectDivision()
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Klasse auswählen');

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        if ($tblPerson) {
            $tblDivisionList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
        } else {
            $tblDivisionList = false;
        }

        $divisionTable = array();
        if ($tblDivisionList) {
            foreach ($tblDivisionList as $tblDivisionTeacher) {
                $tblDivision = $tblDivisionTeacher->getTblDivision();
                $divisionTable[] = array(
                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Type' => $tblDivision->getTypeName(),
                    'Division' => $tblDivision->getDisplayName(),
                    'Option' => new Standard(
                        '', '/Education/Certificate/Prepare/Prepare', new Select(),
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
                                    array('1', 'asc'),
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
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPrepare($DivisionId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {

            $tableData = array();
            $tblPrepareAllByDivision = Prepare::useService()->getPrepareAllByDivision($tblDivision);
            if ($tblPrepareAllByDivision) {
                foreach ($tblPrepareAllByDivision as $tblPrepare) {

                    $tableData[] = array(
                        'Date' => $tblPrepare->getDate(),
                        'Name' => $tblPrepare->getName(),
                        'Status' => $tblPrepare->isAppointedDateTaskUpdated()
                            ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' Stichtagsnotenauftrag wurde aktualisiert')
                            : new Success(new Enable() . ' Keine Fachnoten-Änderungen'),
                        'Option' =>
                            (new Standard(
                                '', '/Education/Certificate/Prepare/Prepare/Edit', new Edit(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                )
                                , 'Bearbeiten'
                            ))
                            . (new Standard(
                                '', '/Education/Certificate/Prepare/Division', new Setup(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                )
                                , 'Einstellungen'
                            ))
                    );
                }
            }

            $Form = $this->formPrepare()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Klasse',
                                        $tblDivision->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tableData, null, array(
                                        'Date' => 'Zeugnisdatum',
                                        'Name' => 'Name',
                                        'Status' => 'Status',
                                        'Option' => ''
                                    ),
                                        array(
                                            'order' => array(
                                                array(0, 'desc')
                                            ),
                                            'columnDefs' => array(
                                                array('type' => 'de_date', 'targets' => 0)
                                            )
                                        )
                                    )
                                ))
                            ))
                        ), new Title(new ListingTable() . ' Übersicht')),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well(Prepare::useService()->createPrepare($Form, $tblDivision,
                                        $Data))
                                )
                            ))
                        ), new Title(new PlusSign() . ' Hinzufügen'))
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Klasse nicht gefunden.', new Ban());
        }
    }

    /**
     * @return Form
     */
    private function formPrepare()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Data[Date]', '', 'Zeugnisdatum', new Calendar()), 3
                ),
                new FormColumn(
                    new TextField('Data[Name]', 'Name', 'Name'), 9
                ),
            )),
        )));
    }

    /**
     * @param null $PrepareId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditPrepare($PrepareId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Bearbeiten');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array('DivisionId' => $tblDivision->getId())
                ));
            }

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Date'] = $tblPrepare->getDate();
                $Global->POST['Data']['Name'] = $tblPrepare->getName();
                $Global->savePost();
            }

            $Form = $this->formPrepare()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
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
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well(Prepare::useService()->updatePrepare($Form, $tblPrepare,
                                        $Data))
                                )
                            ))
                        ), new Title(new PlusSign() . ' Hinzufügen'))
                    )
                )
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
     *
     * @return Stage|string
     */
    public function frontendDivision($PrepareId = null)
    {

        $Stage = new Stage('Klassen', 'Übersicht');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $isOneApproved = Prepare::useService()->existsPrepareStudentWhereIsApproved($tblPrepare);
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $studentTable = array();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array('DivisionId' => $tblDivision->getId())
                ));

                $tblGradeTypeList = array();
                if ($tblPrepare->getServiceTblBehaviorTask()) {
                    $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask());
                    if ($tblTestAllByTask) {
                        foreach ($tblTestAllByTask as $tblTest) {
                            if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                                if (!isset($tblGradeTypeList[$tblGradeType->getId()])) {
                                    $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                                }
                            }
                        }
                    }
                }
                $countBehavior = count($tblGradeTypeList);

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

                        // Fächer zählen
                        if ($tblDivision->getServiceTblYear()) {
                            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                                $tblPerson, $tblDivision->getServiceTblYear()
                            );
                        } else {
                            $tblDivisionSubjectList = false;
                        }
                        if ($tblDivisionSubjectList) {
                            $countSubjects = count($tblDivisionSubjectList);
                        } else {
                            $countSubjects = 0;
                        }

                        // Zensuren zählen
                        if ($tblPrepare->getServiceTblAppointedDateTask()) {
                            $tblPrepareGradeSubjectList = Prepare::useService()->getPrepareGradeAllByPerson(
                                $tblPrepare, $tblPerson, $tblPrepare->getServiceTblAppointedDateTask()->getTblTestType()
                            );
                        } else {
                            $tblPrepareGradeSubjectList = false;
                        }
                        if ($tblPrepareGradeSubjectList) {
                            $countSubjectGrades = count($tblPrepareGradeSubjectList);
                        } else {
                            $countSubjectGrades = 0;
                        }

                        if ($tblPrepare->getServiceTblBehaviorTask()) {
                            $tblPrepareGradeBehaviorList = Prepare::useService()->getPrepareGradeAllByPerson(
                                $tblPrepare, $tblPerson, $tblPrepare->getServiceTblBehaviorTask()->getTblTestType()
                            );
                        } else {
                            $tblPrepareGradeBehaviorList = false;
                        }
                        if ($tblPrepareGradeBehaviorList) {
                            $countBehaviorGrades = count($tblPrepareGradeBehaviorList);
                        } else {
                            $countBehaviorGrades = 0;
                        }

                        if ($tblPrepare->getServiceTblAppointedDateTask()) {
                            $subjectGradesText = $countSubjectGrades . ' von ' . $countSubjects; // . ' Zensuren&nbsp;';
                        } else {
                            $subjectGradesText = 'Kein Stichtagsnotenauftrag ausgewählt';
                        }

                        if ($tblPrepare->getServiceTblBehaviorTask()) {
                            $behaviorGradesText = $countBehaviorGrades . ' von ' . $countBehavior; // . ' Zensuren&nbsp;';
                        } else {
                            $behaviorGradesText = 'Kein Kopfnoten ausgewählt';
                        }

                        $excusedDays = null;
                        $unexcusedDays = null;
                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                        if ($tblPrepareStudent) {
                            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                            $isApproved = $tblPrepareStudent->isApproved();
                            $excusedDays = $tblPrepareStudent->getExcusedDays();
                            $unexcusedDays = $tblPrepareStudent->getUnexcusedDays();
                        } else {
                            $tblCertificate = false;
                            $isApproved = false;
                        }

                        if ($excusedDays === null) {
                            $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision,
                                new \DateTime($tblPrepare->getDate()));
                        }
                        if ($unexcusedDays === null) {
                            $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblDivision,
                                new \DateTime($tblPrepare->getDate()));
                        }

                        $studentTable[] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => $tblAddress ? $tblAddress->getGuiTwoRowString() : '',
                            'Birthday' => $birthday,
                            'Course' => $course,
                            'ExcusedAbsence' => $excusedDays,
                            'UnexcusedAbsence' => $unexcusedDays,
                            'SubjectGrades' => ($countSubjectGrades < $countSubjects || !$tblPrepare->getServiceTblAppointedDateTask()
                                ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $subjectGradesText)
                                : new Success(new Enable() . ' ' . $subjectGradesText)),
                            'BehaviorGrades' => ($countBehaviorGrades < $countBehavior || !$tblPrepare->getServiceTblBehaviorTask()
                                ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $behaviorGradesText)
                                : new Success(new Enable() . ' ' . $behaviorGradesText)),
                            'Template' => ($tblCertificate
                                ? new Success(new Enable() . ' ' . $tblCertificate->getName()
                                    . ($tblCertificate->getDescription() ? '<br>' . $tblCertificate->getDescription() : ''))
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' Keine Zeugnisvorlage ausgewählt')),
                            'Option' =>
                                (!$isApproved ? (new Standard(
                                    '', '/Education/Certificate/Prepare/Certificate', new Edit(),
                                    array('PrepareId' => $tblPrepare->getId(), 'PersonId' => $tblPerson->getId()),
                                    'Zeugnisvorlage auswählen und zusätzliche Informationen bearbeiten')) : '')
                                . (!$isApproved ? (new Standard(
                                    '', '/Education/Certificate/Prepare/Absence/Edit', new Time(),
                                    array('PrepareId' => $tblPrepare->getId(), 'PersonId' => $tblPerson->getId()),
                                    'Manuelle Eingabe der Fehlzeiten')) : '')
                                . ($tblCertificate
                                    ? (new Standard(
                                        '', '/Education/Certificate/Prepare/Certificate/Show', new EyeOpen(),
                                        array('PrepareId' => $tblPrepare->getId(), 'PersonId' => $tblPerson->getId()),
                                        'Zeugnisvorschau anzeigen'))
                                    : '')
                        );
                    }
                }
            }

            /*
             * Buttons
             */
            $buttonAppointedDateTask = new Standard(
                'Stichtagsnotenauftrag wählen und Fachnoten übernehmen',
                '/Education/Certificate/Prepare/AppointedDateTask',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId()
                ),
                'Stichtagsnotenauftrag auswählen und Fachnoten übernehmen'
            );
            $buttonAppointedDateTaskShowGrades = new Standard(
                'Fachnoten ansehen',
                '/Education/Certificate/Prepare/SubjectGrades',
                new EyeOpen(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Fachnoten ansehen'
            );
            $buttonBehaviorTask = new Standard(
                'Kopfnotenauftrag wählen',
                '/Education/Certificate/Prepare/BehaviorTask',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId()
                ),
                'Kopfnotenauftrag auswählen'
            );
            $buttonBehaviorTaskShowGrades = new Standard(
                'Kopfnoten ansehen und Kopfnoten festlegen',
                '/Education/Certificate/Prepare/BehaviorGrades',
                new EyeOpen(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Kopfnoten ansehen und Kopfnoten festlegen'
            );
            $buttonSigner = new Standard(
                'Unterzeichner auswählen',
                '/Education/Certificate/Prepare/Signer',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Unterzeichner auswählen'
            );
            $buttonUpdateAppointedDateTask = new Standard(
                'Aktualisieren',
                '/Education/Certificate/Prepare/AppointedDateTask/Update',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Fachnoten aus dem Stichtagsnotenauftrag aktualisieren'
            );

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        'Klasse ' . $tblDivision->getDisplayName()
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Klasse',
//                                    $tblDivision->getDisplayName(),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 3),
                            new LayoutColumn(array(
                                new Panel(
                                    'Unterzeichner',
                                    array(
                                        $tblPrepare->getServiceTblPersonSigner()
                                            ? $tblPrepare->getServiceTblPersonSigner()->getFullName()
                                            : new Exclamation() . ' Kein Unterzeichner ausgewählt',
                                        ($isOneApproved ? null : $buttonSigner)
                                    ),
                                    $tblPrepare->getServiceTblPersonSigner()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 6),
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                $tblPrepare->isAppointedDateTaskUpdated()
                                    ? new Warning('Die Fachnoten im Stichtagsnotenauftrag wurden aktualisiert.',
                                    new Exclamation()) : null,
                            )),
                            new LayoutColumn(array(
                                new Panel(
                                    'Stichtagsnotenauftrag',
                                    array(
                                        $tblPrepare->getServiceTblAppointedDateTask()
                                            ? $tblPrepare->getServiceTblAppointedDateTask()->getName()
                                            . ' ' . $tblPrepare->getServiceTblAppointedDateTask()->getDate()
                                            : new Exclamation() . ' Kein Stichtagsnotenauftrag ausgewählt',
                                        ($isOneApproved ? ' ' : $buttonAppointedDateTask)
                                        . ($tblPrepare->isAppointedDateTaskUpdated() ? $buttonUpdateAppointedDateTask : '')
                                        . ($tblPrepare->getServiceTblAppointedDateTask()
                                            ? $buttonAppointedDateTaskShowGrades
                                            : '')
                                    ),
                                    $tblPrepare->getServiceTblAppointedDateTask()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Kopfnotenauftrag',
                                    array(
                                        $tblPrepare->getServiceTblBehaviorTask()
                                            ? $tblPrepare->getServiceTblBehaviorTask()->getName()
                                            . ' ' . $tblPrepare->getServiceTblBehaviorTask()->getDate()
                                            : 'Kein Kopfnotenauftrag ausgewählt',
                                        ($isOneApproved ? ' ' : $buttonBehaviorTask) .
                                        ($tblPrepare->getServiceTblBehaviorTask()
                                            ? $buttonBehaviorTaskShowGrades
                                            : '')
                                    ),
                                    $tblPrepare->getServiceTblBehaviorTask()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 6),
                        )),
                    ), new Title('Notenaufträge')),
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
                                    'ExcusedAbsence' => 'E-FZ', //'ent&shy;schuld&shy;igte FZ',
                                    'UnexcusedAbsence' => 'U-FZ', // 'unent&shy;schuld&shy;igte FZ',
                                    'SubjectGrades' => 'Fachnoten',
                                    'BehaviorGrades' => 'Kopfnoten',
                                    'Template' => 'Zeugnis&shy;vorlage',
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
     *
     * @return Stage|string
     */
    public function frontendAppointedDateTask($PrepareId = null)
    {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $Stage->setMessage(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Bold(new Exclamation() . ' Hinweis:')
            . ' Bei der Auswahl des Stichtagsnotenauftrags werden alle Zensuren dieses Auftrags übernommen. Dieser Vorgang kann
             einen Augenblick dauern.'));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
            $tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType);
            $tableContent = array();
            if ($tblTaskList) {
                foreach ($tblTaskList as $tblTask) {
                    if ($tblPrepare->getServiceTblAppointedDateTask()) {
                        $isChosen = $tblTask->getId() == $tblPrepare->getServiceTblAppointedDateTask()->getId();
                    } else {
                        $isChosen = false;
                    }
                    $tableContent[] = array(
                        'Date' => $isChosen ? new Bold($tblTask->getDate()) : $tblTask->getDate(),
                        'Name' => $isChosen ? new Bold($tblTask->getName()) : $tblTask->getName(),
                        'Period' => $tblTask->getServiceTblPeriod()
                            ? $tblTask->getServiceTblPeriod()->getDisplayName()
                            : 'Gesamtes Schuljahr',
                        'Option' => $isChosen
                            ? new Success( new Enable().' Ausgewählt')
                            : new Standard(
                                '',
                                '/Education/Certificate/Prepare/AppointedDateTask/Select',
                                new Select(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'TaskId' => $tblTask->getId()
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
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Stichtagsnotenauftrag',
                                    array(
                                        $tblPrepare->getServiceTblAppointedDateTask()
                                            ? $tblPrepare->getServiceTblAppointedDateTask()->getName()
                                            . ' ' . $tblPrepare->getServiceTblAppointedDateTask()->getDate()
                                            : new Exclamation() . ' Kein Stichtagsnotenauftrag ausgewählt',
                                    ),
                                    $tblPrepare->getServiceTblAppointedDateTask()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 12),
                            new LayoutColumn(array(
                                $tblTaskList
                                    ? new TableData($tableContent, null, array(
                                    'Date' => 'Stichtag',
                                    'Name' => 'Name',
                                    'Period' => 'Zeitraum',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 0)
                                        ),
                                        "paging" => false, // Deaktivieren Blättern
                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    )
                                )
                                    : new Warning('Für diese Klasse sind keine Notenaufträge vorhanden.',
                                    new Exclamation())
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $TaskId
     *
     * @return Stage|string
     */
    public function frontendSelectAppointedDateTask($PrepareId = null, $TaskId = null)
    {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $Stage->setMessage(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Bold(new Exclamation() . ' Hinweis:')
            . ' Bei der Auswahl des Stichtagsnotenauftrags werden alle Zensuren dieses Auftrags übernommen. Dieser Vorgang kann
             einen Augenblick dauern.'));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblTask = Evaluation::useService()->getTaskById($TaskId);

        if ($tblPrepare && $tblTask) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                Prepare::useService()->updatePrepareSetAppointedDateTask(
                                    $tblPrepare, $tblTask
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung oder Notenauftrag nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendUpdateAppointedDateTask($PrepareId = null)
    {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Aktualisieren');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare && $tblPrepare->getServiceTblAppointedDateTask()) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                Prepare::useService()->updatePrepareUpdateAppointedDateTask($tblPrepare)
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendSubjectGrades($PrepareId = null)
    {

        $Stage = new Stage('Fachnoten', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');

            $tableData = array();
            $tableHeader['Number'] = '#';
            $tableHeader['Student'] = 'Schüler';
            if (($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblYear = $tblDivision->getServiceTblYear())
            ) {
                $tblStudentAllByDivision = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentAllByDivision) {
                    foreach ($tblStudentAllByDivision as $tblPerson) {
                        $tableData[$tblPerson->getId()]['Number'] = count($tableData) + 1;
                        $tableData[$tblPerson->getId()]['Student'] = $tblPerson->getLastFirstName();
                        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear(
                            $tblPerson, $tblYear
                        );
                        if ($tblDivisionSubjectList) {
                            /** @var TblDivisionSubject $tblDivisionSubject */
                            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                $tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject(
                                    $tblPrepare,
                                    $tblPerson,
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblTestType
                                );
                                if (!isset($tableHeader['Subject' . $tblDivisionSubject->getServiceTblSubject()])) {
                                    $tableHeader['Subject' . $tblDivisionSubject->getServiceTblSubject()]
                                        = $tblDivisionSubject->getServiceTblSubject()->getAcronym()
                                        . ($tblDivision->getId() == $tblDivisionSubject->getTblDivision()->getId()
                                            ? '' : ' (' . $tblDivisionSubject->getTblDivision()->getDisplayName() . ')');
                                }
                                $tableData[$tblPerson->getId()]['Subject' . $tblDivisionSubject->getServiceTblSubject()]
                                    = $tblPrepareGrade ? $tblPrepareGrade->getGrade()
                                    : new \SPHERE\Common\Frontend\Text\Repository\Warning('f');
                            }
                        }
                    }

                    // leere Zellen setzen
                    foreach ($tableHeader as $key => $value) {
                        /** @var TblPerson $tblPerson */
                        foreach ($tblStudentAllByDivision as $tblPerson) {
                            if (!isset($tableData[$tblPerson->getId()][$key])) {
                                $tableData[$tblPerson->getId()][$key] = '';
                            }
                        }
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
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new TableData(
                                    $tableData,
                                    null,
                                    $tableHeader,
                                    null
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung oder Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendBehaviorTask($PrepareId = null)
    {

        $Stage = new Stage('Kopfnotenauftrag', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $Stage->setMessage(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Bold(new Exclamation() . ' Hinweis:')
            . ' Bei der Auswahl eines anderen Kopfnotenauftrages werden alle bereits festgelegten Kopfnoten entfernt.'));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
            $tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType);


            $tableContent = array();
            if ($tblTaskList) {
                foreach ($tblTaskList as $tblTask) {
                    if ($tblPrepare->getServiceTblBehaviorTask()) {
                        $isChosen = $tblTask->getId() == $tblPrepare->getServiceTblBehaviorTask()->getId();
                    } else {
                        $isChosen = false;
                    }
                    $tableContent[] = array(
                        'Date' => $isChosen ? new Bold($tblTask->getDate()) : $tblTask->getDate(),
                        'Name' => $isChosen ? new Bold($tblTask->getName()) : $tblTask->getName(),
                        'Period' => $tblTask->getServiceTblPeriod()
                            ? $tblTask->getServiceTblPeriod()->getDisplayName()
                            : 'Gesamtes Schuljahr',
                        'Option' => $isChosen
                            ? ''
                            : new Standard(
                                '',
                                '/Education/Certificate/Prepare/BehaviorTask/Select',
                                new Select(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'TaskId' => $tblTask->getId()
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
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Kopfnotenauftrag',
                                    array(
                                        $tblPrepare->getServiceTblBehaviorTask()
                                            ? $tblPrepare->getServiceTblBehaviorTask()->getName()
                                            . ' ' . $tblPrepare->getServiceTblBehaviorTask()->getDate()
                                            : new Exclamation() . ' Kein Kopfnotenauftrag ausgewählt',
                                    ),
                                    $tblPrepare->getServiceTblBehaviorTask()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 12),
                            new LayoutColumn(array(
                                $tblTaskList
                                    ? new TableData($tableContent, null, array(
                                    'Date' => 'Stichtag',
                                    'Name' => 'Name',
                                    'Period' => 'Zeitraum',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 0)
                                        ),
                                        "paging" => false, // Deaktivieren Blättern
                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    )
                                )
                                    : new Warning('Für diese Klasse sind keine Kopfnotenaufträge vorhanden.',
                                    new Exclamation())
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $TaskId
     *
     * @return Stage|string
     */
    public function frontendSelectBehaviorTask($PrepareId = null, $TaskId = null)
    {

        $Stage = new Stage('Kopfnotenauftrag', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $Stage->setMessage(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Bold(new Exclamation() . ' Hinweis:')
            . ' Bei der Auswahl eines anderen Kopfnotenauftrages werden alle bereits festgelegten Kopfnoten entfernt.'));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblTask = Evaluation::useService()->getTaskById($TaskId);

        if ($tblPrepare && $tblTask) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                Prepare::useService()->updatePrepareSetBehaviorTask(
                                    $tblPrepare, $tblTask
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung oder Notenauftrag nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendBehaviorGrades($PrepareId = null)
    {

        $Stage = new Stage('Fachnoten', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare && ($tblDivision = $tblPrepare->getServiceTblDivision())) {
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');

            $dataTable = array();
            $headerTable['Number'] = '#';
            $headerTable['Student'] = 'Name';

            $tblGradeTypeList = array();
            $tblStudentAllByDivision = Division::useService()->getStudentAllByDivision($tblDivision);
            $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask());
            if ($tblTestAllByTask) {
                foreach ($tblTestAllByTask as $tblTest) {
                    if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                        if (!isset($tblGradeTypeList[$tblGradeType->getId()])) {
                            $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                            $headerTable['GradeType' . $tblGradeType->getId()] = $tblGradeType->getCode() . ' ('
                                . $tblGradeType->getName() . ')';
                        }
                    }
                }
            }

            $headerTable['Option'] = '';

            if ($tblStudentAllByDivision) {
                /** @var TblPerson $tblPerson */
                foreach ($tblStudentAllByDivision as $tblPerson) {
                    if ($tblDivision->getServiceTblYear()) {

                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                        if ($tblPrepareStudent) {
                            $isApproved = $tblPrepareStudent->isApproved();
                        } else {
                            $isApproved = false;
                        }

                        $dataTable[$tblPerson->getId()]['Number'] = count($dataTable) + 1;
                        $dataTable[$tblPerson->getId()]['Student'] = $tblPerson->getLastFirstName();
                        $dataTable[$tblPerson->getId()]['Option'] =
                            $isApproved
                                ? ''
                                : new Standard(
                                '',
                                '/Education/Certificate/Prepare/BehaviorGrades/Edit',
                                new Edit(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'PersonId' => $tblPerson->getId()
                                ),
                                'Kopfnoten bearbeiten'
                            );

                        /** @var TblGradeType $tblGradeType */
                        foreach ($tblGradeTypeList as $tblGradeType) {
                            $tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType(
                                $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblGradeType
                            );
                            $dataTable[$tblPerson->getId()]['GradeType' . $tblGradeType->getId()] = $tblPrepareGrade
                                ? $tblPrepareGrade->getGrade()
                                : '';
                        }
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
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new TableData(
                                    $dataTable,
                                    null,
                                    $headerTable,
                                    null
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung oder Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Data
     * @return Stage|string
     */
    public function frontendEditBehaviorGrades($PrepareId = null, $PersonId = null, $Data = null)
    {

        $Stage = new Stage('Kopfnoten', 'Festlegen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/BehaviorGrades', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblScoreType = false;
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $tblPerson = Person::useService()->getPersonById($PersonId);
            $tempTable = array();
            $dataTable = array();
            $headerTable = array(
                'GradeType' => 'Zensuren-Typ',
                'Grades' => 'Zensuren',
                'Average' => '&#216;',
                'Grade' => 'Zensur',
            );
            $gradeList = array();
            if ($tblDivision && $tblPerson) {

                $data = array();
                $tblGradeTypeList = array();
                $tblScoreType = $tblPrepare->getServiceTblBehaviorTask()->getServiceTblScoreType();
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask());
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                            if (!isset($tblGradeTypeList[$tblGradeType->getId()])) {
                                $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                                $dataTable[$tblGradeType->getId()]['GradeType'] = $tblGradeType->getCode() . ' ('
                                    . $tblGradeType->getName() . ')';
                            }
                            if (($tblSubject = $tblTest->getServiceTblSubject())
                                && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                    $tblPerson))
                            ) {
                                $data[$tblGradeType->getId()][$tblSubject->getId()] = $tblGrade;
                                if (!$tblScoreType && $tblGrade->getServiceTblDivision() && $tblGrade->getServiceTblSubject()) {
                                    Gradebook::useService()->getScoreTypeByDivisionAndSubject($tblGrade->getServiceTblDivision(),
                                        $tblGrade->getServiceTblSubject());
                                }
                            }
                        }
                    }
                }

                // Zusammensetzen (für Anzeige) der vergebenen Kopfnoten
                foreach ($data as $gradeTypeId => $subjects) {
                    $tblGradeType = Gradebook::useService()->getGradeTypeById($gradeTypeId);
                    if ($tblGradeType && is_array($subjects)) {
                        /** @var TblGrade $grade */
                        foreach ($subjects as $subjectId => $grade) {
                            $tblSubject = Subject::useService()->getSubjectById($subjectId);
                            if ($tblSubject) {
                                if ($grade->getGrade() && is_numeric($grade->getGrade())) {
                                    $gradeList[$tblGradeType->getId()][] = floatval($grade->getGrade());
                                }
                                if (isset($tempTable[$tblGradeType->getId()])) {
                                    $tempTable[$tblGradeType->getId()] .= ' | '
                                        . $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                } else {
                                    $tempTable[$tblGradeType->getId()] =
                                        $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                }
                            }
                        }
                    }
                }

                // calc average
                foreach ($gradeList as $gradeTypeId => $valueArray) {
                    $count = count($valueArray);
                    $dataTable[$gradeTypeId]['Average'] = $count > 0 ? round(array_sum($valueArray) / $count, 2) : '';
                }

                // Post setzen
                if ($Data === null
                    && ($tblTask = $tblPrepare->getServiceTblBehaviorTask())
                    && ($tblTestType = $tblTask->getTblTestType())
                ) {
                    $Global = $this->getGlobal();
                    /** @var TblGradeType $tblGradeType */
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        $tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType(
                            $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblGradeType
                        );
                        if ($tblPrepareGrade) {
                            $Global->POST['Data'][$tblGradeType->getId()] = $tblPrepareGrade->getGrade();
                        }
                    }
                    $Global->savePost();
                }

                /** @var TblGradeType $tblGradeType */
                foreach ($tblGradeTypeList as $tblGradeType) {
                    if (!isset($dataTable[$tblGradeType->getId()]['Average'])) {
                        $dataTable[$tblGradeType->getId()]['Average'] = '';
                    }

                    // Zensuren-Eingaben-Spalte
                    if ($tblScoreType && $tblScoreType->getIdentifier() == 'VERBAL') {
                        $dataTable[$tblGradeType->getId()]['Grade']
                            = new TextField('Data[' . $tblGradeType->getId() . ']', '', '', new Quote());
                    } else {
                        $dataTable[$tblGradeType->getId()]['Grade']
                            = new NumberField('Data[' . $tblGradeType->getId() . ']', '', '');
                    }
                }

                // fehlende Kopfnoten anzeigen
                if ($tblDivision->getServiceTblYear()) {
                    $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson,
                        $tblDivision->getServiceTblYear()
                    );
                    if ($tblDivisionSubjectList) {
                        /** @var TblDivisionSubject $tblDivisionSubject */
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            $tblSubject = $tblDivisionSubject->getServiceTblSubject();
                            if ($tblSubject) {
                                /** @var TblGradeType $tblGradeType */
                                foreach ($tblGradeTypeList as $tblGradeType) {
                                    if (!isset($data[$tblGradeType->getId()][$tblSubject->getId()])) {
                                        $text = new \SPHERE\Common\Frontend\Text\Repository\Warning('f');
                                        if (isset($tempTable[$tblGradeType->getId()])) {
                                            $tempTable[$tblGradeType->getId()] .= ' | '
                                                . $tblSubject->getAcronym() . ':' . $text;
                                        } else {
                                            $tempTable[$tblGradeType->getId()] =
                                                $tblSubject->getAcronym() . ':' . $text;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (is_array($tempTable)) {
                    foreach ($tempTable as $gradeTypeId => $value) {
                        $dataTable[$gradeTypeId]['Grades'] = $value;
                    }
                }
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new TableData(
                                $dataTable,
                                null,
                                $headerTable,
                                null
                            )
                        )
                    )
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson ? $tblPerson->getLastFirstName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                Prepare::useService()->updatePrepareGradeForBehaviorTask($form, $tblPrepare, $tblPerson,
                                    $tblScoreType ? $tblScoreType : null, $Data)
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSigner($PrepareId = null, $Data = null)
    {

        $Stage = new Stage('Unterzeichner', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare && ($tblDivision = $tblPrepare->getServiceTblDivision())) {

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data'] = $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : 0;
                $Global->savePost();
            }

            $tblPersonList = Division::useService()->getTeacherAllByDivision($tblDivision);

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new SelectBox(
                                'Data',
                                'Unterzeichner (Klassenlehrer)',
                                array('{{ FullName }}' => $tblPersonList)
                            )
                        )
                    )
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                $tblPersonList
                                    ? new Well(Prepare::useService()->updatePrepareSetSigner($form,
                                    $tblPrepare, $Data))
                                    : new Warning('Für diese Klasse sind keine Klassenlehrer vorhanden.')
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Content
     * @param bool $IsChange
     *
     * @return Stage|string
     */
    public function frontendCertificate(
        $PrepareId = null,
        $PersonId = null,
        $Content = null,
        $IsChange = false
    ) {

        $Stage = new Stage('Zeugnisvorlage', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            $tblCourse = false;
            $tblSchoolType = false;
            if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
            ) {
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblTransferType);
                if ($tblStudentTransfer) {
                    $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                    $tblSchoolType = $tblStudentTransfer->getServiceTblType();
                }
            }
            if (!$tblSchoolType) {
                $tblSchoolType = $tblDivision->getTblLevel() ? $tblDivision->getTblLevel()->getServiceTblType() : false;
            }

            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
            if ($tblPrepareStudent) {
                $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
            } else {
                $tblCertificate = false;
            }

            if ($tblCertificate && !$IsChange) {

                $form = null;
                $Certificate = null;
                if ($tblCertificate) {
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                        $Certificate = new $CertificateClass($tblPerson, $tblDivision);

                        $FormField = array(
                            'Content.Input.Remark' => 'TextArea',
                            'Content.Input.Rating' => 'TextArea',
                            'Content.Input.Survey' => 'TextArea',
//                            'Content.Input.Team' => 'TextArea',
                            'Content.Input.Deepening' => 'TextField',
                            'Content.Input.Choose' => 'TextField',
                            'Content.Input.SchoolType' => 'SelectBox',
                            'Content.Input.Type' => 'SelectBox',
//                            'Content.Input.Date' => 'DatePicker',
                            'Content.Input.DateCertifcate' => 'DatePicker',
                            'Content.Input.DateConference' => 'DatePicker',
                            'Content.Input.Transfer' => 'TextField',
//                        'Content.Input.LevelTwo' => 'TextField',
//                        'Content.Input.LevelThree' => 'TextField',
                        );
                        $FormLabel = array(
                            'Content.Input.Remark' => 'Bemerkungen',
                            'Content.Input.Rating' => 'Einschätzung',
                            'Content.Input.Survey' => 'Gutachten',
//                            'Content.Input.Team' => 'Arbeitsgemeinschaften',
                            'Content.Input.Deepening' => 'Vertiefungsrichtung',
                            'Content.Input.Choose' => 'Wahlpflichtbereich',
                            'Content.Input.SchoolType' => 'Ausbildung fortsetzen',
                            'Content.Input.Type' => 'Bezieht sich auf',
//                            'Content.Input.Date' => 'Datum',
                            'Content.Input.DateCertifcate' => 'Datum des Zeugnisses',
                            'Content.Input.DateConference' => 'Datum der Konferenz',
                            'Content.Input.Transfer' => 'Versetzungsvermerk',
//                        'Content.Input.LevelTwo' => '2. Fremdsprache ab Klassenstufe',
//                        'Content.Input.LevelThree' => '3. Fremdsprache ab Klassenstufe',
                        );

                        if ($Content === null) {
                            $Global = $this->getGlobal();
                            $tblPrepareInformationAll = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare,
                                $tblPerson);
                            if ($tblPrepareInformationAll) {
                                foreach ($tblPrepareInformationAll as $tblPrepareInformation) {
                                    if ($tblPrepareInformation->getField() == 'SchoolType'
                                        && method_exists($Certificate, 'selectValuesSchoolType')
                                    ) {
                                        $Global->POST['Content']['Input'][$tblPrepareInformation->getField()] =
                                            array_search($tblPrepareInformation->getValue(),
                                                $Certificate->selectValuesSchoolType());
                                    } elseif ($tblPrepareInformation->getField() == 'Type'
                                        && method_exists($Certificate, 'selectValuesType')
                                    ) {
                                        $Global->POST['Content']['Input'][$tblPrepareInformation->getField()] =
                                            /** @var CheBeGs $Certificate */
                                            array_search($tblPrepareInformation->getValue(),
                                                $Certificate->selectValuesType());
                                    } else {
                                        $Global->POST['Content']['Input'][$tblPrepareInformation->getField()]
                                            = $tblPrepareInformation->getValue();
                                    }
                                }
                            }
                            $Global->savePost();
                        }

                        // Create Form, Additional Information from Template
                        $PlaceholderList = $Certificate->getCertificate()->getPlaceholder();
                        $FormPanelList = array();
                        if ($PlaceholderList) {
                            array_walk($PlaceholderList,
                                function ($Placeholder) use ($Certificate, $FormField, $FormLabel, &$FormPanelList) {

                                    $PlaceholderList = explode('.', $Placeholder);
                                    $Identifier = array_slice($PlaceholderList, 1);

                                    $FieldName = $PlaceholderList[0] . '[' . implode('][', $Identifier) . ']';

                                    $Type = array_shift($Identifier);
                                    if (!method_exists($Certificate, 'get' . $Type)) {
                                        if (isset($FormField[$Placeholder])) {
                                            if (isset($FormLabel[$Placeholder])) {
                                                $Label = $FormLabel[$Placeholder];
                                            } else {
                                                $Label = $Placeholder;
                                            }
                                            if (isset($FormField[$Placeholder])) {
                                                $Field = '\SPHERE\Common\Frontend\Form\Repository\Field\\' . $FormField[$Placeholder];
                                                if ($Field == '\SPHERE\Common\Frontend\Form\Repository\Field\SelectBox') {
                                                    $selectBoxData = array();
                                                    if ($Placeholder == 'Content.Input.SchoolType'
                                                        && method_exists($Certificate, 'selectValuesSchoolType')
                                                    ) {
                                                        $selectBoxData = $Certificate->selectValuesSchoolType();
                                                    } elseif ($Placeholder == 'Content.Input.Type'
                                                        && method_exists($Certificate, 'selectValuesType')
                                                    ) {
                                                        $selectBoxData = $Certificate->selectValuesType();
                                                    }
                                                    $Placeholder = (new SelectBox($FieldName, $Label, $selectBoxData));
                                                } else {
                                                    $Placeholder = (new $Field($FieldName, $Label, $Label));
                                                }
                                            } else {
                                                $Placeholder = (new TextField($FieldName, $Label, $Label));
                                            }

                                            $FormPanelList['Additional'][] = $Placeholder;
                                        }
                                    }
                                });
                        }

                        foreach ($FormPanelList as $Type => $Payload) {
                            switch ($Type) {
                                case 'Additional':
                                    $Title = 'Zusätzliche Informationen';
                                    break;
                                default:
                                    $Title = 'Informationen';
                            }
                            $FormPanelList[] = new FormColumn(new Panel($Title, $Payload, Panel::PANEL_TYPE_INFO));
                        }

                        if (!empty($FormPanelList)) {
                            $form = new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        $FormPanelList
                                    ),
                                ))
                            );
                        }
                    }
                }

                if ($form === null) {
                    $contentLayout = new Warning('Es sind keine zusätzlichen Informationen in der Zeugnisvorlage vorhanden.',
                        new Exclamation());
                } else {
                    $form->appendFormButton(new Primary('Speichern', new Save()))
                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                    $contentLayout = new Well(Prepare::useService()->updatePrepareInformationList($form, $tblPrepare,
                        $tblPerson, $Content, $Certificate));
                }
            } else {
                $CertificateList = array();
                $tblConsumer = Consumer::useService()->getConsumerBySession();
                if ($tblConsumer && $tblConsumer->getAcronym() == 'DEMO') {
                    $tblCertificateAll = Generator::useService()->getCertificateAll();
                } else {
                    $tblCertificateAll = Generator::useService()->getCertificateAllByConsumer();
                }
                if ($tblConsumer) {
                    $tblCertificateConsumer = Generator::useService()->getCertificateAllByConsumer($tblConsumer);
                    if ($tblCertificateConsumer) {
                        $tblCertificateAll = array_merge($tblCertificateConsumer, $tblCertificateAll);
                    }

                    $CertificateList = array();
                    /** @var TblCertificate $item */
                    foreach ($tblCertificateAll as $item) {

                        $CertificateList[] = array_merge($item->__toArray(), array(
                                'Typ' => '<div class="text-center">' . ($item->getServiceTblConsumer()
                                        ? new Small(new Muted($item->getServiceTblConsumer()->getAcronym())) . '<br/>' . new Star()
                                        : new Document() . '<br/>' . new Small(new Muted('Standard'))
                                    ) . '</div>',
                                'Option' => new Standard(
                                    '', '/Education/Certificate/Prepare/Certificate/Select', new Select(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                        'CertificateId' => $item->getId()
                                    ), 'Auswählen')
                            )
                        );
                    }
                }

                $contentLayout = empty($CertificateList)
                    ? new Warning('Keine Zeugnisvorlagen verfügbar.')
                    : new TableData($CertificateList, null, array(
                        'Typ' => 'Typ',
                        'Name' => 'Name',
                        'Description' => 'Beschreibung',
                        'Option' => 'Option'
                    ), array(
                        'order' => array(array(0, 'asc'), array(1, 'asc'), array(2, 'asc')),
                        'columnDefs' => array(
                            array('width' => '1%', 'targets' => 0),
                            array('width' => '1%', 'targets' => 3),
                        )
                    ));
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schulart',
                                    $tblSchoolType
                                        ? $tblSchoolType->getName()
                                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                        . ' Keine Schulart hinterlegt'),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Bildungsgang',
                                    $tblCourse
                                        ? $tblCourse->getName()
                                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                        . ' Kein Bildungsgang hinterlegt'),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorlage',
                                    $tblCertificate
                                        ? array(
                                        ($tblCertificate->getName()
                                            . ($tblCertificate->getDescription() ? ' - ' . $tblCertificate->getDescription() : '')),
                                        new Standard(
                                            'Ändern',
                                            '/Education/Certificate/Prepare/Certificate',
                                            new Edit(),
                                            array(
                                                'PrepareId' => $tblPrepare->getId(),
                                                'PersonId' => $tblPerson->getId(),
                                                'IsChange' => true
                                            )
                                        )
                                    )
                                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                        . ' Keine Zeugnisvorlage hinterlegt'),
                                    $tblCertificate
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                $contentLayout
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $CertificateId
     *
     * @return Stage|string
     */
    public function frontendSelectCertificate($PrepareId = null, $PersonId = null, $CertificateId = null)
    {

        $Stage = new Stage('Zeugnisvorlage', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblCertificate = Generator::useService()->getCertificateById($CertificateId);
        $tblPerson = Person::useService()->getPersonById($PersonId);

        if ($tblPrepare && $tblCertificate && $tblPerson) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                Prepare::useService()->updatePrepareStudentSetCertificate(
                                    $tblPrepare, $tblPerson, $tblCertificate
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung oder Zeugnisvorlage nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendShowCertificate($PrepareId = null, $PersonId = null)
    {
        $Stage = new Stage('Zeugnisvorlage', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            $ContentLayout = array();

            $tblCertificate = false;
            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Template */
                        $Template = new $CertificateClass();

                        // get Content
                        $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);

                        $ContentLayout = $Template->createCertificate($Content)->getContent();
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
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorlage',
                                    $tblCertificate
                                        ? ($tblCertificate->getName()
                                        . ($tblCertificate->getDescription() ? ' - ' . $tblCertificate->getDescription() : ''))
                                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                        . ' Keine Zeugnisvorlage hinterlegt'),
                                    $tblCertificate
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 12),
                            new LayoutColumn(array(
                                $ContentLayout
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditAbsence($PrepareId = null, $PersonId = null, $Data = null)
    {
        $Stage = new Stage('Fehlzeiten', 'Manuell festlegen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Division', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $tblPerson = Person::useService()->getPersonById($PersonId);
            if (!$tblPerson) {
                return $Stage . new Danger('Person nicht gefunden', new Ban());
            }

            $Stage->setMessage('Hier können die Fehlzeiten manuell eingegeben werden, 
                falls die Fehlzeiten nicht aus dem Klassenbuch verwendet werden sollen.');

            if ($Data === null && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                    $tblPerson))
            ) {
                $Global = $this->getGlobal();
                if ($Global) {
                    $Global->POST['Data']['ExcusedDays'] = $tblPrepareStudent->getExcusedDays();
                    $Global->POST['Data']['UnexcusedDays'] = $tblPrepareStudent->getUnexcusedDays();
                }
                $Global->savePost();
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(
                            new NumberField('Data[ExcusedDays]', '', 'Entschuldigte Fehltage'), 6
                        ),
                        new FormColumn(
                            new NumberField('Data[UnexcusedDays]', '', 'Unentschuldigte Fehltage'), 6
                        )
                    ))
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klasse',
                                    $tblDivision ? $tblDivision->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson ? $tblPerson->getLastFirstName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Klassenbuch',
                                    array(
                                        'Entschuldigte Fehltage: ' . Absence::useService()->getExcusedDaysByPerson($tblPerson,
                                            $tblDivision,
                                            new \DateTime($tblPrepare->getDate())),
                                        'Unentschuldigte Fehltage: ' . Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                            $tblDivision,
                                            new \DateTime($tblPrepare->getDate()))
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            )),
                            new LayoutColumn(array(
                                new Well(Prepare::useService()->setAbsenceDays($form, $tblPrepare, $tblPerson, $Data))
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }
}
