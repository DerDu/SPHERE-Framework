<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2016
 * Time: 13:03
 */

namespace SPHERE\Application\Education\Certificate\GradeInformation;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
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
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
        } else {
            return $this->frontendTeacherSelectDivision();
        }
    }

    /**
     * @return Stage
     */
    public function frontendTeacherSelectDivision()
    {

        $Stage = new Stage('Noteninformation', 'Klasse auswählen');

        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Teacher');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                '/Education/Certificate/GradeInformation/Teacher', new Edit()));
            $Stage->addButton(new Standard('Ansicht: Leitung', '/Education/Certificate/GradeInformation/Headmaster'));
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
                            'DivisionId' => $tblDivision->getId(),
                            'Route' => 'Teacher'
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
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision()
    {
        $Stage = new Stage('Noteninformation', 'Klasse auswählen');

        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Teacher');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Certificate/GradeInformation/Teacher'));
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                '/Education/Certificate/GradeInformation/Headmaster', new Edit()));
        }


        $divisionTable = array();
        if (($tblDivisionList = Division::useService()->getDivisionAll())) {
            foreach ($tblDivisionList as $tblDivision) {
                $divisionTable[] = array(
                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Type' => $tblDivision->getTypeName(),
                    'Division' => $tblDivision->getDisplayName(),
                    'Option' => new Standard(
                        '', '/Education/Certificate/GradeInformation/Create', new Select(),
                        array(
                            'DivisionId' => $tblDivision->getId(),
                            'Route' => 'Headmaster'
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
     * @param string $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendGradeInformation($DivisionId = null, $Route = 'Teacher', $Data = null)
    {

        $Stage = new Stage('Noteninformation', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/' . $Route, new ChevronLeft()
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
                        'Option' =>
                            (new Standard(
                                '', '/Education/Certificate/GradeInformation/Edit', new Edit(),
                                array(
                                    'PrepareId' => $tblPrepareCertificate->getId(),
                                    'Route' => $Route
                                )
                                , 'Bearbeiten'
                            ))
                            . (new Standard(
                                '', '/Education/Certificate/GradeInformation/Setting', new Setup(),
                                array(
                                    'PrepareId' => $tblPrepareCertificate->getId(),
                                    'Route' => $Route
                                )
                                , 'Einstellungen'
                            )
                            )
                            . (new Standard(
                                '', '/Education/Certificate/GradeInformation/Setting/Preview', new EyeOpen(),
                                array(
                                    'PrepareId' => $tblPrepareCertificate->getId(),
                                    'Route' => $Route
                                )
                                , 'Vorschau und Herunterladen der Noteninformation'
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
                                    new Well(GradeInformation::useService()->createGradeInformation(
                                        $Form, $tblDivision, $Route, $Data)
                                    )
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
     * @param string $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditGradeInformation($PrepareId = null, $Route = 'Teacher', $Data = null)
    {

        $Stage = new Stage('Noteninformation', 'Bearbeiten');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/GradeInformation/Create', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
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
                                    new Well(GradeInformation::useService()->updateGradeInformation(
                                        $Form, $tblPrepare, $Route, $Data)
                                    )
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
     * @param string $Route
     * @param null $Grades
     * @param null $Remarks
     *
     * @return Stage|string
     */
    public function frontendSetting($PrepareId = null, $Route = 'Teacher', $Grades = null, $Remarks = null)
    {

        $Stage = new Stage('Noteninformation', 'Klassenübersicht');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $columnTable = array();
//        $tblScoreType = false;
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $studentTable = array();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/GradeInformation/Create', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

                $tblGradeTypeList = array();
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                );
                if ($tblPrepare->getServiceTblBehaviorTask()) {
                    if (($tblTestList = Evaluation::useService()->getTestAllByTask(
                        $tblPrepare->getServiceTblBehaviorTask(),
                        $tblDivision
                    ))
                    ) {
                        foreach ($tblTestList as $tblTest) {
                            if (($tblGradeType = $tblTest->getServiceTblGradeType())) {
                                if (!isset($tblGradeTypeList[$tblGradeType->getId()])) {
                                    $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                                    $columnTable['GradeType' . $tblGradeType->getId()] = $tblGradeType->getName();
                                }
                            }
                        }
                    }

//                    $tblScoreType = $tblPrepare->getServiceTblBehaviorTask()->getServiceTblScoreType();
                }
                $columnTable['Remark'] = 'Bemerkungen zum Schüler';

                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    /** @var TblPerson $tblPerson */
                    foreach ($tblStudentList as $tblPerson) {
                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName(),
                        );

                        // Post setzen
                        if ($Grades === null
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
                                    $Global->POST['Grades'][$tblPerson->getId()][$tblGradeType->getId()] = $tblPrepareGrade->getGrade();
                                }
                            }

                            $tblPrepareInformationAll = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare,
                                $tblPerson);
                            if ($tblPrepareInformationAll) {
                                /** @var TblPrepareInformation $tblPrepareInformation */
                                foreach ($tblPrepareInformationAll as $tblPrepareInformation) {
                                    if ($tblPrepareInformation->getField() == 'Remark') {
                                        $Global->POST['Remarks'][$tblPerson->getId()] = $tblPrepareInformation->getValue();
                                    }
                                }
                            }
                            $Global->savePost();
                        }

                        foreach ($tblGradeTypeList as $tblGradeType) {
                            $studentTable[$tblPerson->getId()]['GradeType' . $tblGradeType->getId()] =
                                new TextField('Grades[' . $tblPerson->getId() . '][' . $tblGradeType->getId() . ']');
                        }

                        $studentTable[$tblPerson->getId()]['Remark'] =
                            new TextArea('Remarks[' . $tblPerson->getId() . ']');

                    }
                }
            }

            $tableData = new TableData($studentTable, null, $columnTable,
                array(
                    "columnDefs" => array(
                        array(
                            "width" => "7px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "200px",
                            "targets" => 1
                        ),
                        array(
                            "width" => "50px",
                            "targets" => array(2, 3, 4, 5)
                        ),
                    ),
                    'order' => array(
                        array('0', 'asc'),
                    ),
                    "paging" => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                    "searching" => false, // Deaktivieren Suchen
                    "info" => false,  // Deaktivieren Such-Info
                    "sort" => false,
                    "responsive" => false
                ));

            $form = new Form(
                new FormGroup(array(
                    new FormRow(
                        new FormColumn(
                            $tableData
                        )
                    ),
                ))
                , new Primary('Speichern', new Save())
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
                                GradeInformation::useService()->updatePrepareBehaviorGradesAndRemark(
                                    $form, $tblPrepare, $Route, $Grades, $Remarks
                                )
                            ))
                        ))
                    ))
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
     * @param null $PersonId
     * @param string $Route
     *
     * @return Stage|string
     */
    public function frontendShowTemplate($PrepareId = null, $PersonId = null, $Route = 'Teacher')
    {

        $Stage = new Stage('Noteninformation', 'Vorschau und Herunterladen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'Route' => $Route
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
            if (!($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                && ($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GradeInformation'))
            ) {
                $tblPrepareStudent = Prepare::useService()->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson,
                    $tblCertificate);
            } else {
                $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
            }

            if ($tblPrepareStudent) {
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

    /**
     * @param null $PrepareId
     * @param string $Route
     *
     * @return Stage|string
     */
    public function frontendPreview($PrepareId = null, $Route = 'Teacher')
    {

        $Stage = new Stage('Noteninformation', 'Klassenübersicht');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $columnTable = array();
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();
            $studentTable = array();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/GradeInformation/Create', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                );
                $columnTable['Option'] = '';

                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    /** @var TblPerson $tblPerson */
                    foreach ($tblStudentList as $tblPerson) {
                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName(),
                        );

                        $studentTable[$tblPerson->getId()]['Option'] =
                            (new Standard(
                                '', '/Education/Certificate/GradeInformation/Setting/Template/Show',
                                new EyeOpen(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'PersonId' => $tblPerson->getId(),
                                    'Route' => $Route
                                ),
                                'Vorschau anzeigen'
                            ))
                            . (new External(
                                '',
                                '/Api/Education/Certificate/Generator/Preview',
                                new Download(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'PersonId' => $tblPerson->getId(),
                                    'Name' => 'Noteninformation'
                                ), 'Noteninformation herunterladen'));
                    }
                }
            }

//            $buttonAppointedDateTask = new Standard(
//                'Stichtagsnotenauftrag wählen',
//                '/Education/Certificate/GradeInformation/Setting/AppointedDateTask',
//                new Select(),
//                array(
//                    'PrepareId' => $tblPrepare->getId()
//                ),
//                'Stichtagsnotenauftrag auswählen und Fachnoten übernehmen'
//            );
//            $buttonBehaviorTask = new Standard(
//                'Kopfnotenauftrag wählen',
//                '/Education/Certificate/GradeInformation/Setting/BehaviorTask',
//                new Select(),
//                array(
//                    'PrepareId' => $tblPrepare->getId()
//                ),
//                'Kopfnotenauftrag auswählen'
//            );

            $tableData = new TableData($studentTable, null, $columnTable,
                array(
                    "columnDefs" => array(
                        array(
                            "width" => "7px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "60px",
                            "targets" => 2
                        ),
                    ),
                    'order' => array(
                        array('0', 'asc'),
                    ),
                    "paging" => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                    "searching" => false, // Deaktivieren Suchen
                    "info" => false,  // Deaktivieren Such-Info
                    "sort" => false
                ));

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
//                    new LayoutGroup(array(
//                        new LayoutRow(array(
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Stichtagsnotenauftrag',
//                                    array(
//                                        $tblPrepare->getServiceTblAppointedDateTask()
//                                            ? $tblPrepare->getServiceTblAppointedDateTask()->getName()
//                                            . ' ' . $tblPrepare->getServiceTblAppointedDateTask()->getDate()
//                                            : new Exclamation() . ' Kein Stichtagsnotenauftrag ausgewählt',
//                                        $buttonAppointedDateTask
//                                    ),
//                                    $tblPrepare->getServiceTblAppointedDateTask()
//                                        ? Panel::PANEL_TYPE_SUCCESS
//                                        : Panel::PANEL_TYPE_WARNING
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Kopfnotenauftrag',
//                                    array(
//                                        $tblPrepare->getServiceTblBehaviorTask()
//                                            ? $tblPrepare->getServiceTblBehaviorTask()->getName()
//                                            . ' ' . $tblPrepare->getServiceTblBehaviorTask()->getDate()
//                                            : 'Kein Kopfnotenauftrag ausgewählt',
//                                        $buttonBehaviorTask
//                                    ),
//                                    $tblPrepare->getServiceTblBehaviorTask()
//                                        ? Panel::PANEL_TYPE_SUCCESS
//                                        : Panel::PANEL_TYPE_WARNING
//                                ),
//                            ), 6),
//                        )),
//                    ), new Title('Notenaufträge')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new External(
                                    'Alle Noteninformationen herunterladen',
                                    '/Api/Education/Certificate/Generator/PreviewZip',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Name' => 'Noteninformation'
                                    ), 'Alle Noteninformationen herunterladen'),
                                $tableData
                            ))
                        ))
                    ))
//                    ), new Title('Vorschau und Herunterladen'))
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

//    /**
//     * @param null $PrepareId
//     *
//     * @return Stage|string
//     */
//    public function frontendAppointedDateTask($PrepareId = null)
//    {
//
//        $Stage = new Stage('Noteninformation', 'Stichtagsnotenauftrag auswählen');
//        $Stage->addButton(new Standard(
//            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
//                'PrepareId' => $PrepareId
//            )
//        ));
//
//        $Stage->setMessage(new WarningText(new Bold(new Exclamation() . ' Hinweis:')
//            . ' Bei der Auswahl des Stichtagsnotenauftrags werden alle Zensuren dieses Auftrags übernommen. Dieser Vorgang kann
//             einen Augenblick dauern.'));
//
//        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
//        if ($tblPrepare) {
//            $tblDivision = $tblPrepare->getServiceTblDivision();
//
//            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
//            $tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType);
//            $tableContent = array();
//            if ($tblTaskList) {
//                foreach ($tblTaskList as $tblTask) {
//                    if ($tblPrepare->getServiceTblAppointedDateTask()) {
//                        $isChosen = $tblTask->getId() == $tblPrepare->getServiceTblAppointedDateTask()->getId();
//                    } else {
//                        $isChosen = false;
//                    }
//                    $tableContent[] = array(
//                        'Date' => $isChosen ? new Bold($tblTask->getDate()) : $tblTask->getDate(),
//                        'Name' => $isChosen ? new Bold($tblTask->getName()) : $tblTask->getName(),
//                        'Period' => $tblTask->getServiceTblPeriod()
//                            ? $tblTask->getServiceTblPeriod()->getDisplayName()
//                            : 'Gesamtes Schuljahr',
//                        'Option' => $isChosen
//                            ? ''
//                            : new Standard(
//                                '',
//                                '/Education/Certificate/GradeInformation/Setting/AppointedDateTask/Select',
//                                new Select(),
//                                array(
//                                    'PrepareId' => $tblPrepare->getId(),
//                                    'TaskId' => $tblTask->getId()
//                                ),
//                                'Auswählen'
//                            )
//                    );
//                }
//            }
//
//            $Stage->setContent(
//                new Layout(array(
//                    new LayoutGroup(array(
//                        new LayoutRow(array(
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Noteninformation',
//                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Klasse',
//                                    $tblDivision->getDisplayName(),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Stichtagsnotenauftrag',
//                                    array(
//                                        $tblPrepare->getServiceTblAppointedDateTask()
//                                            ? $tblPrepare->getServiceTblAppointedDateTask()->getName()
//                                            . ' ' . $tblPrepare->getServiceTblAppointedDateTask()->getDate()
//                                            : new Exclamation() . ' Kein Stichtagsnotenauftrag ausgewählt',
//                                    ),
//                                    $tblPrepare->getServiceTblAppointedDateTask()
//                                        ? Panel::PANEL_TYPE_SUCCESS
//                                        : Panel::PANEL_TYPE_WARNING
//                                ),
//                            ), 12),
//                            new LayoutColumn(array(
//                                $tblTaskList
//                                    ? new TableData($tableContent, null, array(
//                                    'Date' => 'Stichtag',
//                                    'Name' => 'Name',
//                                    'Period' => 'Zeitraum',
//                                    'Option' => ''
//                                ),
//                                    array(
//                                        'order' => array(
//                                            array(0, 'desc')
//                                        ),
//                                        'columnDefs' => array(
//                                            array('type' => 'de_date', 'targets' => 0)
//                                        ),
//                                        "paging" => false, // Deaktivieren Blättern
//                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
//                                    )
//                                )
//                                    : new Warning('Für diese Klasse sind keine Notenaufträge vorhanden.',
//                                    new Exclamation())
//                            )),
//                        ))
//                    ))
//                ))
//            );
//
//            return $Stage;
//        } else {
//
//            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
//        }
//    }
//
//    /**
//     * @param null $PrepareId
//     * @param null $TaskId
//     *
//     * @return Stage|string
//     */
//    public function frontendSelectAppointedDateTask($PrepareId = null, $TaskId = null)
//    {
//
//        $Stage = new Stage('Noteninformation', 'Stichtagsnotenauftrag auswählen');
//        $Stage->addButton(new Standard(
//            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
//                'PrepareId' => $PrepareId
//            )
//        ));
//
//        $Stage->setMessage(new WarningText(new Bold(new Exclamation() . ' Hinweis:')
//            . ' Bei der Auswahl des Stichtagsnotenauftrags werden alle Zensuren dieses Auftrags übernommen. Dieser Vorgang kann
//             einen Augenblick dauern.'));
//
//        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
//        $tblTask = Evaluation::useService()->getTaskById($TaskId);
//
//        if ($tblPrepare && $tblTask) {
//            $tblDivision = $tblPrepare->getServiceTblDivision();
//
//            $Stage->setContent(
//                new Layout(array(
//                    new LayoutGroup(array(
//                        new LayoutRow(array(
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Noteninformation',
//                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Klasse',
//                                    $tblDivision->getDisplayName(),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                GradeInformation::useService()->updatePrepareSetAppointedDateTask(
//                                    $tblPrepare, $tblTask
//                                )
//                            )),
//                        ))
//                    ))
//                ))
//            );
//
//            return $Stage;
//        } else {
//
//            return $Stage . new Danger('Noteninformation oder Notenauftrag nicht gefunden.', new Ban());
//        }
//    }
//
//    /**
//     * @param null $PrepareId
//     *
//     * @return Stage|string
//     */
//    public function frontendBehaviorTask($PrepareId = null)
//    {
//
//        $Stage = new Stage('Noteninformation', 'Kopfnotenauftrag auswählen');
//        $Stage->addButton(new Standard(
//            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
//                'PrepareId' => $PrepareId
//            )
//        ));
//
//        $Stage->setMessage(new WarningText(new Bold(new Exclamation() . ' Hinweis:')
//            . ' Bei der Auswahl eines anderen Kopfnotenauftrages werden alle bereits festgelegten Kopfnoten entfernt.'));
//
//        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
//        if ($tblPrepare) {
//            $tblDivision = $tblPrepare->getServiceTblDivision();
//
//            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
//            $tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType);
//
//            $tableContent = array();
//            if ($tblTaskList) {
//                foreach ($tblTaskList as $tblTask) {
//                    if ($tblPrepare->getServiceTblBehaviorTask()) {
//                        $isChosen = ($tblTask->getId() == $tblPrepare->getServiceTblBehaviorTask()->getId());
//                    } else {
//                        $isChosen = false;
//                    }
//                    $tableContent[] = array(
//                        'Date' => $isChosen ? new Bold($tblTask->getDate()) : $tblTask->getDate(),
//                        'Name' => $isChosen ? new Bold($tblTask->getName()) : $tblTask->getName(),
//                        'Period' => $tblTask->getServiceTblPeriod()
//                            ? $tblTask->getServiceTblPeriod()->getDisplayName()
//                            : 'Gesamtes Schuljahr',
//                        'Option' => $isChosen
//                            ? ''
//                            : new Standard(
//                                '',
//                                '/Education/Certificate/GradeInformation/Setting/BehaviorTask/Select',
//                                new Select(),
//                                array(
//                                    'PrepareId' => $tblPrepare->getId(),
//                                    'TaskId' => $tblTask->getId()
//                                ),
//                                'Auswählen'
//                            )
//                    );
//                }
//            }
//
//            $Stage->setContent(
//                new Layout(array(
//                    new LayoutGroup(array(
//                        new LayoutRow(array(
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Noteninformation',
//                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Klasse',
//                                    $tblDivision->getDisplayName(),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Kopfnotenauftrag',
//                                    array(
//                                        $tblPrepare->getServiceTblBehaviorTask()
//                                            ? $tblPrepare->getServiceTblBehaviorTask()->getName()
//                                            . ' ' . $tblPrepare->getServiceTblBehaviorTask()->getDate()
//                                            : new Exclamation() . ' Kein Kopfnotenauftrag ausgewählt',
//                                    ),
//                                    $tblPrepare->getServiceTblBehaviorTask()
//                                        ? Panel::PANEL_TYPE_SUCCESS
//                                        : Panel::PANEL_TYPE_WARNING
//                                ),
//                            ), 12),
//                            new LayoutColumn(array(
//                                $tblTaskList
//                                    ? new TableData($tableContent, null, array(
//                                    'Date' => 'Stichtag',
//                                    'Name' => 'Name',
//                                    'Period' => 'Zeitraum',
//                                    'Option' => ''
//                                ),
//                                    array(
//                                        'order' => array(
//                                            array(0, 'desc')
//                                        ),
//                                        'columnDefs' => array(
//                                            array('type' => 'de_date', 'targets' => 0)
//                                        ),
//                                        "paging" => false, // Deaktivieren Blättern
//                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
//                                    )
//                                )
//                                    : new Warning('Für diese Klasse sind keine Kopfnotenaufträge vorhanden.',
//                                    new Exclamation())
//                            )),
//                        ))
//                    ))
//                ))
//            );
//
//            return $Stage;
//        } else {
//
//            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
//        }
//    }
//
//    /**
//     * @param null $PrepareId
//     * @param null $TaskId
//     *
//     * @return Stage|string
//     */
//    public function frontendSelectBehaviorTask($PrepareId = null, $TaskId = null)
//    {
//
//        $Stage = new Stage('Noteninformation', 'Kopfnotenauftrag auswählen');
//        $Stage->addButton(new Standard(
//            'Zurück', '/Education/Certificate/GradeInformation/Setting', new ChevronLeft(), array(
//                'PrepareId' => $PrepareId
//            )
//        ));
//
//        $Stage->setMessage(new WarningText(new Bold(new Exclamation() . ' Hinweis:')
//            . ' Bei der Auswahl eines anderen Kopfnotenauftrages werden alle bereits festgelegten Kopfnoten entfernt.'));
//
//        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
//        $tblTask = Evaluation::useService()->getTaskById($TaskId);
//
//        if ($tblPrepare && $tblTask) {
//            $tblDivision = $tblPrepare->getServiceTblDivision();
//
//            $Stage->setContent(
//                new Layout(array(
//                    new LayoutGroup(array(
//                        new LayoutRow(array(
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Noteninformation',
//                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                new Panel(
//                                    'Klasse',
//                                    $tblDivision->getDisplayName(),
//                                    Panel::PANEL_TYPE_INFO
//                                ),
//                            ), 6),
//                            new LayoutColumn(array(
//                                GradeInformation::useService()->updatePrepareSetBehaviorTask(
//                                    $tblPrepare, $tblTask
//                                )
//                            )),
//                        ))
//                    ))
//                ))
//            );
//
//            return $Stage;
//        } else {
//
//            return $Stage . new Danger('Noteninformation oder Notenauftrag nicht gefunden.', new Ban());
//        }
//    }
}