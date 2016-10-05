<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2016
 * Time: 13:03
 */

namespace SPHERE\Application\Education\Certificate\GradeInformation;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
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
use SPHERE\Common\Frontend\Icon\Repository\Download;
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
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\GradeInformation
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendSelectDivision()
    {

        $Stage = new Stage('Noteninformation', 'Klasse auswählen');

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
                        '', '/Education/Certificate/GradeInformation/Create', new Select(),
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
    public function frontendGradeInformation($DivisionId = null, $Data = null)
    {

        $Stage = new Stage('Noteninformation', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation', new ChevronLeft()
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {

            $tableData = array();
            $tblPrepareCertificateAllByDivision = Prepare::useService()->getPrepareAllByDivision($tblDivision, true);
            if ($tblPrepareCertificateAllByDivision) {
                foreach ($tblPrepareCertificateAllByDivision as $tblPrepareCertificate) {

                    $tableData[] = array(
                        'Date' => $tblPrepareCertificate->getDate(),
                        'Name' => $tblPrepareCertificate->getName(),
                        'Status' => $tblPrepareCertificate->isAppointedDateTaskUpdated()
                            ? new Warning(new Exclamation() . ' Stichtagsnotenauftrag wurde aktualisiert')
                            : new Success(new Enable() . ' Keine Fachnoten-Änderungen'),
                        'Option' =>
                            (new Standard(
                                '', '/Education/Certificate/GradeInformation/Edit', new Edit(),
                                array(
                                    'PrepareId' => $tblPrepareCertificate->getId(),
                                )
                                , 'Bearbeiten'
                            ))
                            . (new Standard(
                                '', '/Education/Certificate/GradeInformation/Setting', new Setup(),
                                array(
                                    'PrepareId' => $tblPrepareCertificate->getId(),
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
                                        'Date' => 'Datum',
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
                                    new Well(GradeInformation::useService()->createGradeInformation($Form, $tblDivision,
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
                    new DatePicker('Data[Date]', '', 'Datum', new Calendar()), 3
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
    public function frontendEditGradeInformation($PrepareId = null, $Data = null)
    {

        $Stage = new Stage('Noteninformation', 'Bearbeiten');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/GradeInformation/Create', new ChevronLeft(),
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
                                        'Noteninformation',
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
                                    new Well(GradeInformation::useService()->updateGradeInformation($Form, $tblPrepare,
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
                'Zurück', '/Education/Certificate/GradeInformation', new ChevronLeft()
            ));

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendSetting($PrepareId = null)
    {

        $Stage = new Stage('Noteninformation', 'Klassenübersicht');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $studentTable = array();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/GradeInformation/Create', new ChevronLeft(),
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
                    /** @var TblPerson $tblPerson */
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

                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                        if ($tblPrepareStudent) {
                            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                        } else {
                            $tblCertificate = false;
                        }

                        $studentTable[] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => $tblAddress ? $tblAddress->getGuiTwoRowString() : '',
                            'Birthday' => $birthday,
                            'Course' => $course,
                            'SubjectGrades' => ($countSubjectGrades < $countSubjects || !$tblPrepare->getServiceTblAppointedDateTask()
                                ? new WarningText(new Exclamation() . ' ' . $subjectGradesText)
                                : new Success(new Enable() . ' ' . $subjectGradesText)),
                            'BehaviorGrades' => ($countBehaviorGrades < $countBehavior || !$tblPrepare->getServiceTblBehaviorTask()
                                ? new WarningText(new Exclamation() . ' ' . $behaviorGradesText)
                                : new Success(new Enable() . ' ' . $behaviorGradesText)),
                            'Template' => ($tblCertificate
                                ? new Success(new Enable() . ' ' . $tblCertificate->getName()
                                    . ($tblCertificate->getDescription() ? '<br>' . $tblCertificate->getDescription() : ''))
                                : new WarningText(new Exclamation() . ' Keine Vorlage ausgewählt')),
                            'Option' =>
                                (new Standard(
                                    '', '/Education/Certificate/GradeInformation/Setting/Template', new Edit(),
                                    array('PrepareId' => $tblPrepare->getId(), 'PersonId' => $tblPerson->getId()),
                                    'Vorlage auswählen und zusätzliche Informationen bearbeiten'))
                                . ($tblCertificate
                                    ? (new Standard(
                                        '', '/Education/Certificate/GradeInformation/Setting/Template/Show',
                                        new EyeOpen(),
                                        array('PrepareId' => $tblPrepare->getId(), 'PersonId' => $tblPerson->getId()),
                                        'Vorschau anzeigen und Herunterladen'))
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
                '/Education/Certificate/GradeInformation/Setting/AppointedDateTask',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId()
                ),
                'Stichtagsnotenauftrag auswählen und Fachnoten übernehmen'
            );
            $buttonAppointedDateTaskShowGrades = new Standard(
                'Fachnoten ansehen',
                '/Education/Certificate/GradeInformation/Setting/SubjectGrades',
                new EyeOpen(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Fachnoten ansehen'
            );
            $buttonBehaviorTask = new Standard(
                'Kopfnotenauftrag wählen',
                '/Education/Certificate/GradeInformation/Setting/BehaviorTask',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId()
                ),
                'Kopfnotenauftrag auswählen'
            );
            $buttonBehaviorTaskShowGrades = new Standard(
                'Kopfnoten ansehen und Kopfnoten festlegen',
                '/Education/Certificate/GradeInformation/Setting/BehaviorGrades',
                new EyeOpen(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Kopfnoten ansehen und Kopfnoten festlegen'
            );
            $buttonUpdateAppointedDateTask = new Standard(
                'Aktualisieren',
                '/Education/Certificate/GradeInformation/Setting/AppointedDateTask/Update',
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
                                    'Noteninformation',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                    ),
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
                                        $buttonAppointedDateTask
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
                                        $buttonBehaviorTask .
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
                                    'SubjectGrades' => 'Fachnoten',
                                    'BehaviorGrades' => 'Kopfnoten',
                                    'Template' => 'Vorlage',
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
                'Zurück', '/Education/Certificate/GradeInformation', new ChevronLeft()
            ));

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendAppointedDateTask($PrepareId = null)
    {

        $Stage = new Stage('Noteninformation', 'Stichtagsnotenauftrag auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $Stage->setMessage(new WarningText(new Bold(new Exclamation() . ' Hinweis:')
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
                            ? ''
                            : new Standard(
                                '',
                                '/Education/Certificate/GradeInformation/Setting/AppointedDateTask/Select',
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
                                    'Noteninformation',
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

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
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

        $Stage = new Stage('Noteninformation', 'Stichtagsnotenauftrag auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $Stage->setMessage(new WarningText(new Bold(new Exclamation() . ' Hinweis:')
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
                                    'Noteninformation',
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
                                GradeInformation::useService()->updatePrepareSetAppointedDateTask(
                                    $tblPrepare, $tblTask
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Noteninformation oder Notenauftrag nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendUpdateAppointedDateTask($PrepareId = null)
    {

        $Stage = new Stage('Noteninformation', 'Stichtagsnotenauftrag aktualisieren');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare && $tblPrepare->getServiceTblAppointedDateTask()) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Noteninformation',
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
                                GradeInformation::useService()->updatePrepareUpdateAppointedDateTask($tblPrepare)
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

        $Stage = new Stage('Noteninformation', 'Fachnotenübersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
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
                                    : new WarningText('f');
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
                                    'Noteninformation',
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

            return $Stage . new Danger('Noteninformation oder Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendBehaviorTask($PrepareId = null)
    {

        $Stage = new Stage('Noteninformation', 'Kopfnotenauftrag auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $Stage->setMessage(new WarningText(new Bold(new Exclamation() . ' Hinweis:')
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
                        $isChosen = ($tblTask->getId() == $tblPrepare->getServiceTblBehaviorTask()->getId());
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
                                '/Education/Certificate/GradeInformation/Setting/BehaviorTask/Select',
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
                                    'Noteninformation',
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

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
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

        $Stage = new Stage('Noteninformation', 'Kopfnotenauftrag auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $Stage->setMessage(new WarningText(new Bold(new Exclamation() . ' Hinweis:')
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
                                    'Noteninformation',
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
                                GradeInformation::useService()->updatePrepareSetBehaviorTask(
                                    $tblPrepare, $tblTask
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Noteninformation oder Notenauftrag nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendBehaviorGrades($PrepareId = null)
    {

        $Stage = new Stage('Noteninformation', 'Kopfnotenübersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
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
                                '/Education/Certificate/GradeInformation/Setting/BehaviorGrades/Edit',
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
                                    'Noteninformation',
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

            return $Stage . new Danger('Noteninformation oder Person nicht gefunden.', new Ban());
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

        $Stage = new Stage('Noteninformation', 'Kopfnoten festlegen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting/BehaviorGrades', new ChevronLeft(), array(
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
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            /** @var TblSubject $tblSubject */
                            $tblSubject = $tblDivisionSubject->getServiceTblSubject();
                            if ($tblSubject) {
                                /** @var TblGradeType $tblGradeType */
                                foreach ($tblGradeTypeList as $tblGradeType) {
                                    if (!isset($data[$tblGradeType->getId()][$tblSubject->getId()])) {
                                        $text = new WarningText('f');
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
                                    'Noteninformation',
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
                                GradeInformation::useService()->updatePrepareGradeForBehaviorTask($form, $tblPrepare,
                                    $tblPerson,
                                    $tblScoreType ? $tblScoreType : null, $Data)
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;

        } else {

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
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
    public function frontendTemplate(
        $PrepareId = null,
        $PersonId = null,
        $Content = null,
        $IsChange = false
    ) {

        $Stage = new Stage('Noteninformation', 'Vorlage Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
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
                        );
                        $FormLabel = array(
                            'Content.Input.Remark' => 'Bemerkungen',
                        );

                        if ($Content === null) {
                            $Global = $this->getGlobal();
                            $tblPrepareInformationAll = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare,
                                $tblPerson);
                            if ($tblPrepareInformationAll) {
                                /** @var TblPrepareInformation $tblPrepareInformation */
                                foreach ($tblPrepareInformationAll as $tblPrepareInformation) {
                                    $Global->POST['Content']['Input'][$tblPrepareInformation->getField()]
                                        = $tblPrepareInformation->getValue();
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
                    $contentLayout = new Warning('Es sind keine zusätzlichen Informationen in der Vorlage vorhanden.',
                        new Exclamation());
                } else {
                    $form->appendFormButton(new Primary('Speichern', new Save()))
                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                    $contentLayout = new Well(GradeInformation::useService()->updatePrepareInformationList($form, $tblPrepare,
                        $tblPerson, $Content, $Certificate));
                }
            } else {
                $TemplateList = array();
                $tblConsumer = Consumer::useService()->getConsumerBySession();
                if ($tblConsumer && $tblConsumer->getAcronym() == 'DEMO') {
                    $tblTemplateAll = Generator::useService()->getGradeInformationTemplateAll();
                } else {
                    $tblTemplateAll = Generator::useService()->getGradeInformationTemplateAllByConsumer();
                }
                if ($tblConsumer) {
                    if (!$tblTemplateAll){
                        $tblTemplateAll = array();
                    }

                    $tblTemplateConsumer = Generator::useService()->getGradeInformationTemplateAllByConsumer($tblConsumer);
                    if ($tblTemplateConsumer) {
                        $tblTemplateAll = array_merge($tblTemplateConsumer, $tblTemplateAll);
                    }

                    $TemplateList = array();
                    /** @var TblCertificate $item */
                    foreach ($tblTemplateAll as $item) {

                        $TemplateList[] = array_merge($item->__toArray(), array(
                                'Typ' => '<div class="text-center">' . ($item->getServiceTblConsumer()
                                        ? new Small(new Muted($item->getServiceTblConsumer()->getAcronym())) . '<br/>' . new Star()
                                        : new Document() . '<br/>' . new Small(new Muted('Standard'))
                                    ) . '</div>',
                                'Option' => new Standard(
                                    '', '/Education/Certificate/GradeInformation/Setting/Template/Select', new Select(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                        'TemplateId' => $item->getId()
                                    ), 'Auswählen')
                            )
                        );
                    }
                }

                $contentLayout = empty($TemplateList)
                    ? new Warning('Keine Vorlagen verfügbar.')
                    : new TableData($TemplateList, null, array(
                        'Typ' => 'Typ',
                        'Name' => 'Name',
                        'Description' => 'Beschreibung',
                        'Option' => 'Auswählen'
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
                                    'Noteninformation',
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
                                        : new WarningText(new Exclamation()
                                        . ' Keine Schulart hinterlegt'),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Bildungsgang',
                                    $tblCourse
                                        ? $tblCourse->getName()
                                        : new WarningText(new Exclamation()
                                        . ' Kein Bildungsgang hinterlegt'),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Vorlage',
                                    $tblCertificate
                                        ? array(
                                        ($tblCertificate->getName()
                                            . ($tblCertificate->getDescription() ? ' - ' . $tblCertificate->getDescription() : '')),
                                        new Standard(
                                            'Ändern',
                                            '/Education/Certificate/GradeInformation/Setting/Template',
                                            new Edit(),
                                            array(
                                                'PrepareId' => $tblPrepare->getId(),
                                                'PersonId' => $tblPerson->getId(),
                                                'IsChange' => true
                                            )
                                        )
                                    )
                                        : new WarningText(new Exclamation()
                                        . ' Keine Vorlage hinterlegt'),
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

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $TemplateId
     *
     * @return Stage|string
     */
    public function frontendSelectTemplate($PrepareId = null, $PersonId = null, $TemplateId = null)
    {

        $Stage = new Stage('Noteninformation', 'Vorlage auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblCertificate = Generator::useService()->getCertificateById($TemplateId);
        $tblPerson = Person::useService()->getPersonById($PersonId);

        if ($tblPrepare && $tblCertificate && $tblPerson) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Noteninformation',
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
                                GradeInformation::useService()->updatePrepareStudentSetTemplate(
                                    $tblPrepare, $tblPerson, $tblCertificate
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Noteninformation oder Vorlage nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendShowTemplate($PrepareId = null, $PersonId = null)
    {

        $Stage = new Stage('Noteninformation', 'Vorschau und Herunterladen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
                'PrepareId' => $PrepareId
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            $Stage->addButton(new External(
                'Noteninformation herunterladen',
                '/Api/Education/Certificate/Generator/Preview',
                new Download(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                    'PersonId' => $tblPerson->getId(),
                    'Name' => 'Noteninformation'
                ), false)
            );

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
                                    'Noteninformation',
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
                                    'Vorlage',
                                    $tblCertificate
                                        ? ($tblCertificate->getName()
                                        . ($tblCertificate->getDescription() ? ' - ' . $tblCertificate->getDescription() : ''))
                                        : new WarningText(new Exclamation()
                                        . ' Keine Vorlage hinterlegt'),
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

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
        }
    }
}