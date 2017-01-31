<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
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
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Teacher');

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

        $Stage = new Stage('Zeugnisvorbereitung', 'Klasse auswählen');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Teacher');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                '/Education/Certificate/Prepare/Teacher', new Edit()));
            $Stage->addButton(new Standard('Ansicht: Leitung',
                '/Education/Certificate/Prepare/Headmaster'));
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
                        '', '/Education/Certificate/Prepare/Prepare', new Select(),
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
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2)
                                ),
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

        $Stage = new Stage('Zeugnisvorbereitung', 'Klasse auswählen');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Teacher');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Certificate/Prepare/Teacher'));
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                '/Education/Certificate/Prepare/Headmaster', new Edit()));
        }

        $tblDivisionList = Division::useService()->getDivisionAll();

        $divisionTable = array();
        if ($tblDivisionList) {
            foreach ($tblDivisionList as $tblDivision) {
                $divisionTable[] = array(
                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Type' => $tblDivision->getTypeName(),
                    'Division' => $tblDivision->getDisplayName(),
                    'Option' => new Standard(
                        '', '/Education/Certificate/Prepare/Prepare', new Select(),
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
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2)
                                ),
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
     *
     * @return Stage|string
     */
    public function frontendPrepare($DivisionId = null, $Route = 'Teacher')
    {

        $Stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/' . $Route, new ChevronLeft()
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {

            $tableData = array();
            $tblPrepareAllByDivision = Prepare::useService()->getPrepareAllByDivision($tblDivision);
            if ($tblPrepareAllByDivision) {
                foreach ($tblPrepareAllByDivision as $tblPrepareCertificate) {

                    // Setzen der Zeugnisvorlagen
                    Prepare::useService()->setTemplatesAllByPrepareCertificate($tblPrepareCertificate);

                    $tableData[] = array(
                        'Date' => $tblPrepareCertificate->getDate(),
                        'Type' => $tblPrepareCertificate->getServiceTblGenerateCertificate()
                            ? $tblPrepareCertificate->getServiceTblGenerateCertificate()->getServiceTblCertificateType()->getName()
                            : '',
                        'Name' => $tblPrepareCertificate->getName(),
                        'Option' =>
                            (new Standard(
                                '', '/Education/Certificate/Prepare/Prepare/Setting', new Setup(),
                                array(
                                    'PrepareId' => $tblPrepareCertificate->getId(),
                                    'Route' => $Route
                                )
                                , 'Einstellungen'
                            ))
                            . (new Standard(
                                '', '/Education/Certificate/Prepare/Prepare/Preview', new EyeOpen(),
                                array(
                                    'PrepareId' => $tblPrepareCertificate->getId(),
                                    'Route' => $Route
                                )
                                , 'Vorschau der Zeugnisse'
                            ))
                    );
                }
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
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tableData, null, array(
                                        'Date' => 'Zeugnisdatum',
                                        'Type' => 'Typ',
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
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Klasse nicht gefunden.', new Ban());
        }
    }


    /**
     * @param null $PrepareId
     * @param string $Route
     * @param null $GradeTypeId
     * @param null $IsNotGradeType
     * @param null $Data
     * @param null $Trend
     * @param null $CertificateList
     *
     * @return Stage|string
     */
    public function frontendPrepareSetting(
        $PrepareId = null,
        $Route = 'Teacher',
        $GradeTypeId = null,
        $IsNotGradeType = null,
        $Data = null,
        $Trend = null,
        $CertificateList = null
    ) {

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $selectBoxContent = array(
                TblGrade::VALUE_TREND_NULL => '',
                TblGrade::VALUE_TREND_PLUS => 'Plus',
                TblGrade::VALUE_TREND_MINUS => 'Minus'
            );

            // Kopfnoten festlegen
            if (!$IsNotGradeType
                && $tblPrepare->getServiceTblBehaviorTask()
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                    $tblDivision))
            ) {
                $Stage = new Stage('Zeugnisvorbereitung', 'Kopfnoten festlegen');
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

                $hasPreviewGrades = false;
                $tblCurrentGradeType = false;
                $tblNextGradeType = false;
                $tblGradeTypeList = array();
                $hasInformation = false;
                foreach ($tblTestList as $tblTest) {
                    if (($tblGradeTypeItem = $tblTest->getServiceTblGradeType())) {
                        if (!isset($tblGradeTypeList[$tblGradeTypeItem->getId()])) {
                            $tblGradeTypeList[$tblGradeTypeItem->getId()] = $tblGradeTypeItem;
                            if ($tblCurrentGradeType && !$tblNextGradeType) {
                                $tblNextGradeType = $tblGradeTypeItem;
                            }
                            if ($GradeTypeId && $GradeTypeId == $tblGradeTypeItem->getId()) {
                                $tblCurrentGradeType = $tblGradeTypeItem;
                            }
                        }
                    }
                }
                if (!$tblCurrentGradeType && !empty($tblGradeTypeList)) {
                    $tblCurrentGradeType = current($tblGradeTypeList);
                    if (count($tblGradeTypeList) > 1) {
                        $tblNextGradeType = next($tblGradeTypeList);
                    }
                }

                $buttonList = array();
                /** @var TblGradeType $tblGradeType */
                foreach ($tblGradeTypeList as $tblGradeType) {
                    if ($tblCurrentGradeType->getId() == $tblGradeType->getId()) {
                        $name = new Info(new Bold($tblGradeType->getName()));
                        $icon = new Edit();
                    } else {
                        $name = $tblGradeType->getName();
                        $icon = null;
                    }

                    $buttonList[] = new Standard($name,
                        '/Education/Certificate/Prepare/Prepare/Setting', $icon, array(
                            'PrepareId' => $tblPrepare->getId(),
                            'Route' => $Route,
                            'GradeTypeId' => $tblGradeType->getId()
                        )
                    );
                }

                $buttonList[] = new Standard('Sonstige Informationen',
                    '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route,
                        'IsNotGradeType' => true
                    )
                );

                $studentTable = array();
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'Course' => 'Bildungsgang',
                    'Grades' => 'Einzelnoten in ' . ($tblCurrentGradeType ? $tblCurrentGradeType->getName() : ''),
                    'Average' => '&#216;',
                    'Data' => 'Zensur'
                );

                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    $tabIndex = 1;
                    /** @var TblPerson $tblPerson */
                    foreach ($tblStudentList as $tblPerson) {
                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName()
                        );

                        // Bildungsgang
                        $tblCourse = false;
                        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                        ) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            }
                        }
                        $studentTable[$tblPerson->getId()]['Course'] = $tblCourse ? $tblCourse->getName() : '';

                        if ($tblCurrentGradeType) {
                            $subjectGradeList = array();
                            $gradeList = array();
                            foreach ($tblTestList as $tblTest) {
                                if (($tblGradeType = $tblTest->getServiceTblGradeType())
                                    && $tblGradeType->getId() == $tblCurrentGradeType->getId()
                                ) {
                                    if (($tblSubject = $tblTest->getServiceTblSubject())
                                        && ($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                            $tblPerson))
                                    ) {
                                        $subjectGradeList[$tblSubject->getAcronym()] = $tblGrade;
                                    }
                                }
                            }

                            $gradeListString = '';
                            if (!empty($subjectGradeList)) {
                                ksort($subjectGradeList);
                            }

                            // Zusammensetzen (für Anzeige) der vergebenen Kopfnoten
                            /** @var TblGrade $grade */
                            foreach ($subjectGradeList as $subjectAcronym => $grade) {
                                $tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym);
                                if ($tblSubject) {
                                    if ($grade->getGrade() && is_numeric($grade->getGrade())) {
                                        $gradeList[] = floatval($grade->getGrade());
                                    }
                                    if (empty($gradeListString)) {
                                        $gradeListString =
                                            $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                    } else {
                                        $gradeListString .= ' | '
                                            . $tblSubject->getAcronym() . ':' . $grade->getDisplayGrade();
                                    }
                                }
                            }
                            $studentTable[$tblPerson->getId()]['Grades'] = $gradeListString;

                            // calc average
                            $average = '';
                            if (!empty($gradeList)) {
                                $count = count($gradeList);
                                $average = $count > 0 ? round(array_sum($gradeList) / $count, 2) : '';
                                $studentTable[$tblPerson->getId()]['Average'] = $average;
                            } else {
                                $studentTable[$tblPerson->getId()]['Average'] = '';
                            }

                            // Post setzen
                            if ($Data === null
                                && ($tblTask = $tblPrepare->getServiceTblBehaviorTask())
                                && ($tblTestType = $tblTask->getTblTestType())
                                && $tblCurrentGradeType
                            ) {
                                $Global = $this->getGlobal();
                                $tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType(
                                    $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblCurrentGradeType
                                );
                                if ($tblPrepareGrade) {
                                    $gradeValue = $tblPrepareGrade->getGrade();
                                    if (strpos($gradeValue, '+') !== false) {
//                                        $this->getDebugger()->screenDump($gradeValue, $tblPerson->getId(), $tblPerson->getLastFirstName());
                                        $Global->POST['Trend'][$tblPerson->getId()] = TblGrade::VALUE_TREND_PLUS;
                                        $gradeValue = str_replace('+', '', $gradeValue);
                                    } elseif (strpos($gradeValue, '-') !== false) {
                                        $Global->POST['Trend'][$tblPerson->getId()] = TblGrade::VALUE_TREND_MINUS;
                                        $gradeValue = str_replace('-', '', $gradeValue);
                                    }
                                    $Global->POST['Data'][$tblPerson->getId()] = $gradeValue;
                                } elseif ($average) {
                                    // Noten aus dem Notendurchschnitt als Vorschlag eintragen
                                    $hasPreviewGrades = true;
                                    $Global->POST['Data'][$tblPerson->getId()] =
                                        str_replace('.', ',', round($average, 0));
                                }

                                $Global->savePost();
                            }

                            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                    $tblPerson))
                                && $tblPrepareStudent->isApproved()
                            ) {
                                $studentTable[$tblPerson->getId()]['Data'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . ']'))->setDisabled();
                                if (($tblCertificate = $tblPrepareStudent->getTblPrepareCertificate())
                                    && $tblCertificate->isGradeInformation()
                                ) {
                                    $hasInformation = true;
                                    $studentTable[$tblPerson->getId()]['Trend'] =
                                        (new SelectBox('Trend[' . $tblPerson->getId() . ']', '', $selectBoxContent,
                                            new ResizeVertical()))->setDisabled();
                                }
                            } else {
                                $studentTable[$tblPerson->getId()]['Data'] =
                                    (new NumberField('Data[' . $tblPerson->getId() . ']'))->setTabIndex($tabIndex++);

                                if ($tblPrepareStudent
                                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                    && $tblCertificate->isInformation()
                                ) {
                                    $hasInformation = true;
                                    $studentTable[$tblPerson->getId()]['Trend'] =
                                        (new SelectBox('Trend[' . $tblPerson->getId() . ']', '', $selectBoxContent,
                                            new ResizeVertical()))->setTabIndex($tabIndex++);
                                }
                            }
                        }
                    }
                }

                if ($hasInformation) {
                    $columnTable['Trend'] = 'Tendenz';

                    $columnDef = array(
                        array(
                            "width" => "7px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "200px",
                            "targets" => 1
                        ),
                        array(
                            "width" => "80px",
                            "targets" => 2
                        ),
                        array(
                            "width" => "50px",
                            "targets" => array(4)
                        ),
                        array(
                            "width" => "80px",
                            "targets" => array(5)
                        ),
                        array(
                            "width" => "180px",
                            "targets" => array(6)
                        )
                    );
                } else {
                    $columnDef = array(
                        array(
                            "width" => "7px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "200px",
                            "targets" => 1
                        ),
                        array(
                            "width" => "80px",
                            "targets" => 2
                        ),
                        array(
                            "width" => "50px",
                            "targets" => array(4)
                        ),
                        array(
                            "width" => "80px",
                            "targets" => array(5)
                        ),
                    );
                }

                $tableData = new TableData($studentTable, null, $columnTable,
                    array(
                        "columnDefs" => $columnDef,
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        "paging" => false, // Deaktivieren Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                        "searching" => false, // Deaktivieren Suchen
                        "info" => false,  // Deaktivieren Such-Info
                        "sort" => false,
                        "responsive" => false
                    )
                );

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
                                        'Zeugnis',
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
                                new LayoutColumn($buttonList),
                                $hasPreviewGrades
                                    ? new LayoutColumn(new Warning(
                                    'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                                ))
                                    : null,
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    Prepare::useService()->updatePrepareBehaviorGrades(
                                        $form,
                                        $tblPrepare,
                                        $tblCurrentGradeType,
                                        $tblNextGradeType ? $tblNextGradeType : null,
                                        $Route,
                                        $Data,
                                        $Trend
                                    )
                                ))
                            ))
                        ))
                    ))
                );

                return $Stage;

                // Sonstige Informationen
            } elseif (($tblDivision = $tblPrepare->getServiceTblDivision())
                && (($IsNotGradeType
                        || (!$IsNotGradeType && !$tblPrepare->getServiceTblBehaviorTask()))
                    || (!$IsNotGradeType && $tblPrepare->getServiceTblBehaviorTask()
                        && !Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                            $tblDivision)))
            ) {
                $Stage = new Stage('Zeugnisvorbereitung', 'Sonstige Informationen festlegen');
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

                if ($tblPrepare->getServiceTblBehaviorTask()
                    && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                        $tblDivision))
                ) {
                    $tblCurrentGradeType = false;
                    $tblNextGradeType = false;
                    $tblGradeTypeList = array();
                    foreach ($tblTestList as $tblTest) {
                        if (($tblGradeTypeItem = $tblTest->getServiceTblGradeType())) {
                            if (!isset($tblGradeTypeList[$tblGradeTypeItem->getId()])) {
                                $tblGradeTypeList[$tblGradeTypeItem->getId()] = $tblGradeTypeItem;
                                if ($tblCurrentGradeType && !$tblNextGradeType) {
                                    $tblNextGradeType = $tblGradeTypeItem;
                                }
                                if ($GradeTypeId && $GradeTypeId == $tblGradeTypeItem->getId()) {
                                    $tblCurrentGradeType = $tblGradeTypeItem;
                                }
                            }
                        }
                    }

                    $buttonList = array();
                    /** @var TblGradeType $tblGradeType */
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        $buttonList[] = new Standard($tblGradeType->getName(),
                            '/Education/Certificate/Prepare/Prepare/Setting', null, array(
                                'PrepareId' => $tblPrepare->getId(),
                                'Route' => $Route,
                                'GradeTypeId' => $tblGradeType->getId()
                            )
                        );
                    }
                }

                $buttonList[] = new Standard(new Info(new Bold('Sonstige Informationen')),
                    '/Education/Certificate/Prepare/Prepare/Setting', new Edit(), array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route,
                        'IsNotGradeType' => true
                    )
                );

                $studentTable = array();
                $columnTable = array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'Course' => 'Bildungsgang',
                    'ExcusedDays' => 'E-FZ', //'ent&shy;schuld&shy;igte FZ',
                    'UnexcusedDays' => 'U-FZ' // 'unent&shy;schuld&shy;igte FZ'
                );

                $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblStudentList) {
                    /** @var TblPerson $tblPerson */
                    foreach ($tblStudentList as $tblPerson) {
                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName()
                        );

                        // Bildungsgang
                        $tblCourse = false;
                        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                        ) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            }
                        }
                        $studentTable[$tblPerson->getId()]['Course'] = $tblCourse ? $tblCourse->getName() : '';

                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);

                        /*
                         * Fehlzeiten
                         */
                        // Post setzen von Fehlzeiten und Fehlzeiten aus dem Klassenbuch voreintragen
                        if ($Data === null) {
                            $Global = $this->getGlobal();
                            if ($Global) {
                                $Global->POST['Data'][$tblPerson->getId()]['ExcusedDays'] =
                                    $tblPrepareStudent && $tblPrepareStudent->getExcusedDays() !== null
                                        ? $tblPrepareStudent->getExcusedDays()
                                        : Absence::useService()->getExcusedDaysByPerson($tblPerson,
                                        $tblDivision, new \DateTime($tblPrepare->getDate()));
                                $Global->POST['Data'][$tblPerson->getId()]['UnexcusedDays'] =
                                    $tblPrepareStudent && $tblPrepareStudent->getUnexcusedDays() !== null
                                        ? $tblPrepareStudent->getUnexcusedDays()
                                        : Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                        $tblDivision, new \DateTime($tblPrepare->getDate()));
                            }
                            $Global->savePost();
                        }

                        if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                            $studentTable[$tblPerson->getId()]['ExcusedDays'] =
                                (new NumberField('Data[' . $tblPerson->getId() . '][ExcusedDays]', '',
                                    ''))->setDisabled();
                            $studentTable[$tblPerson->getId()]['UnexcusedDays'] =
                                (new NumberField('Data[' . $tblPerson->getId() . '][UnexcusedDays]', '',
                                    ''))->setDisabled();
                        } else {
                            $studentTable[$tblPerson->getId()]['ExcusedDays'] =
                                new NumberField('Data[' . $tblPerson->getId() . '][ExcusedDays]', '', '');
                            $studentTable[$tblPerson->getId()]['UnexcusedDays'] =
                                new NumberField('Data[' . $tblPerson->getId() . '][UnexcusedDays]', '', '');
                        }
                        /*
                         * Sonstige Informationen der Zeugnisvorlage
                         */
                        $this->getTemplateInformation($tblPrepare, $tblPerson, $studentTable, $columnTable, $Data,
                            $CertificateList);
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
                                "width" => "80px",
                                "targets" => 2
                            ),
                            array(
                                "width" => "50px",
                                "targets" => array(3, 4)
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
                    ),
                    true
                );

//                $form = new Form(
//                    new FormGroup(
//                        new FormRow(array(
//                            new FormColumn(
//                                new NumberField('Data[ExcusedDays]', '', 'Entschuldigte Fehltage'), 6
//                            ),
//                            new FormColumn(
//                                new NumberField('Data[UnexcusedDays]', '', 'Unentschuldigte Fehltage'), 6
//                            )
//                        ))
//                    )
//                );

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
                                        'Zeugnis',
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
                                new LayoutColumn($buttonList),
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    Prepare::useService()->updatePrepareInformationList($form, $tblPrepare, $Route,
                                        $Data, $CertificateList)
                                ))
                            ))
                        ))
                    ))
                );

                return $Stage;
            }
        }

        $Stage = new Stage('Zeugnisvorbereitung', 'Einstellungen');

        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
        ));

        return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param array $studentTable
     * @param array $columnTable
     * @param array|null $Data
     * @param array|null $CertificateList
     */
    private function getTemplateInformation(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        &$studentTable,
        &$columnTable,
        &$Data,
        &$CertificateList
    ) {

        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);
        if ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
        } else {
            $tblCertificate = false;
        }

        if ($tblCertificate && ($tblDivision = $tblPrepareCertificate->getServiceTblDivision())) {
            $Certificate = null;
            if ($tblCertificate) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                if (class_exists($CertificateClass)) {

                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = new $CertificateClass($tblPerson, $tblDivision);

                    $CertificateList[$tblPerson->getId()] = $Certificate;

                    $FormField = Generator::useService()->getFormField();
                    $FormLabel = Generator::useService()->getFormLabel();

                    if ($Data === null) {
                        $Global = $this->getGlobal();
                        $tblPrepareInformationAll = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepareCertificate,
                            $tblPerson);
                        $hasTransfer = false;
                        $isTeamSet = false;
                        if ($tblPrepareInformationAll) {
                            foreach ($tblPrepareInformationAll as $tblPrepareInformation) {
                                if ($tblPrepareInformation->getField() == 'Team' || $tblPrepareInformation->getField() == 'TeamExtra') {
                                    $isTeamSet = true;
                                }

                                if ($tblPrepareInformation->getField() == 'SchoolType'
                                    && method_exists($Certificate, 'selectValuesSchoolType')
                                ) {
                                    $Global->POST['Data'][$tblPerson->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesSchoolType());
                                } elseif ($tblPrepareInformation->getField() == 'Type'
                                    && method_exists($Certificate, 'selectValuesType')
                                ) {
                                    $Global->POST['Data'][$tblPerson->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesType());
                                } elseif ($tblPrepareInformation->getField() == 'Transfer'
                                    && method_exists($Certificate, 'selectValuesTransfer')
                                ) {
                                    $Global->POST['Data'][$tblPerson->getId()][$tblPrepareInformation->getField()] =
                                        array_search($tblPrepareInformation->getValue(),
                                            $Certificate->selectValuesTransfer());
                                    $hasTransfer = true;
                                } else {
                                    $Global->POST['Data'][$tblPerson->getId()][$tblPrepareInformation->getField()]
                                        = $tblPrepareInformation->getValue();
                                }
                            }
                        }

                        // Arbeitsgemeinschaften aus der Schülerakte laden
                        if (!$isTeamSet) {
                            if (($tblStudent = $tblPerson->getStudent())
                                && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
                                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                                    $tblStudent, $tblSubjectType
                                ))
                            ) {
                                $tempList = array();
                                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                    if ($tblStudentSubject->getServiceTblSubject()) {
                                        $tempList[] = $tblStudentSubject->getServiceTblSubject()->getName();
                                    }
                                }
                                if (!empty($tempList)) {
                                    $Global->POST['Data'][$tblPerson->getId()]['Team'] = implode(', ', $tempList);
                                    $Global->POST['Data'][$tblPerson->getId()]['TeamExtra'] = implode(', ', $tempList);
                                }
                            }
                        }

                        // Vorsetzen auf Versetzungsvermerk: wird versetzt
                        if (!$hasTransfer) {
                            $Global->POST['Data'][$tblPerson->getId()]['Transfer'] = 1;
                        }

                        $Global->savePost();
                    }

                    // Create Form, Additional Information from Template
                    $PlaceholderList = $Certificate->getCertificate()->getPlaceholder();
                    // Arbeitsgemeinschaften stehen extra und nicht in den Bemerkungen
                    $hasTeamExtra = false;
                    if ($PlaceholderList) {
                        array_walk($PlaceholderList,
                            function ($Placeholder) use (
                                $Certificate,
                                $FormField,
                                $FormLabel,
                                &$columnTable,
                                &$studentTable,
                                $tblPerson,
                                $tblPrepareStudent,
                                $tblCertificate,
                                $hasTeamExtra
                            ) {

                                $PlaceholderList = explode('.', $Placeholder);
                                $Identifier = array_slice($PlaceholderList, 1);

                                $FieldName = $PlaceholderList[0] . '[' . implode('][', $Identifier) . ']';

                                $dataFieldName = str_replace('Content[Input]', 'Data[' . $tblPerson->getId() . ']',
                                    $FieldName);

                                $Type = array_shift($Identifier);
                                if (!method_exists($Certificate, 'get' . $Type)) {
                                    if (isset($FormField[$Placeholder])) {
                                        if (isset($FormLabel[$Placeholder])) {
                                            $Label = $FormLabel[$Placeholder];
                                        } else {
                                            $Label = $Placeholder;
                                        }

                                        $key = str_replace('Content.Input.', '', $Placeholder);

                                        if ($key == 'TeamExtra' || isset($columnTable['TeamExtra'])){
                                            $hasTeamExtra = true;
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
                                                } elseif ($Placeholder == 'Content.Input.Transfer'
                                                    && method_exists($Certificate, 'selectValuesTransfer')
                                                ) {
                                                    $selectBoxData = $Certificate->selectValuesTransfer();
                                                }
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                    $studentTable[$tblPerson->getId()][$key]
                                                        = (new SelectBox($dataFieldName, '',
                                                        $selectBoxData))->setDisabled();
                                                } else {
                                                    $studentTable[$tblPerson->getId()][$key]
                                                        = (new SelectBox($dataFieldName, '', $selectBoxData));
                                                }
                                            } else {
                                                if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                    /** @var TextArea $Field */
                                                    $studentTable[$tblPerson->getId()][$key]
                                                        = (new $Field($dataFieldName, '', ''))->setDisabled();
                                                } else {
                                                    // Arbeitsgemeinschaften beim Bemerkungsfeld
                                                    if (!$hasTeamExtra && $key == 'Remark') {
                                                        if (!isset($columnTable['Team'])) {
                                                            $columnTable['Team'] = 'Arbeitsgemeinschaften';
                                                        }
                                                        $studentTable[$tblPerson->getId()]['Team']
                                                            = (new TextField('Data[' . $tblPerson->getId() . '][Team]',
                                                            '', ''));
                                                    }

                                                    // TextArea Zeichen begrenzen
                                                    if ($FormField[$Placeholder] == 'TextArea'
                                                        && (($CharCount = Generator::useService()->getCharCountByCertificateAndField(
                                                            $tblCertificate, $key, !$hasTeamExtra
                                                        )))
                                                    ) {
                                                        /** @var TextArea $Field */
                                                        $studentTable[$tblPerson->getId()][$key]
                                                            = (new TextArea($dataFieldName, '', ''))->setMaxLengthValue(
                                                            $CharCount
                                                        );
                                                    } else {
                                                        $studentTable[$tblPerson->getId()][$key]
                                                            = (new $Field($dataFieldName, '', ''));
                                                    }
                                                }
                                            }
                                        } else {
                                            if ($tblPrepareStudent && $tblPrepareStudent->isApproved()) {
                                                $studentTable[$tblPerson->getId()][$key]
                                                    = (new TextField($FieldName, '', ''))->setDisabled();
                                            } else {
                                                $studentTable[$tblPerson->getId()][$key]
                                                    = (new TextField($FieldName, '', ''));
                                            }
                                        }

                                        if (!isset($columnTable[$key])) {
                                            $columnTable[$key] = $Label;
                                        }
                                    }
                                }
                            });
                    }
                }
            }
        }
    }


    /**
     * @param null $PrepareId
     * @param string $Route
     *
     * @return Stage|string
     */
    public function frontendPreparePreview(
        $PrepareId = null,
        $Route = 'Teacher'
    ) {

        $Stage = new Stage('Zeugnisvorbereitung', 'Vorschau');

        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        if ($tblPrepare) {
            $tblDivision = $tblPrepare->getServiceTblDivision();

            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungs&shy;gang',
                'ExcusedAbsence' => 'E-FZ', //'ent&shy;schuld&shy;igte FZ',
                'UnexcusedAbsence' => 'U-FZ', // 'unent&shy;schuld&shy;igte FZ',
                'SubjectGrades' => 'Fachnoten',
                'BehaviorGrades' => 'Kopfnoten',
//                                    'Template' => 'Zeugnis&shy;vorlage',
            );

            $studentTable = array();
            if ($tblDivision) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(), array(
                        'DivisionId' => $tblDivision->getId(),
                        'Route' => $Route
                    )
                ));

                $tblGradeTypeList = array();
                if ($tblPrepare->getServiceTblBehaviorTask()) {
                    $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblBehaviorTask(),
                        $tblDivision);
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

                        $countSubjectGrades = 0;
                        // Zensuren zählen
                        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                            && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivision))
                        ) {
                            foreach ($tblTestList as $tblTest) {
                                if (($tblGradeItem = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                        $tblPerson))
                                    && $tblTest->getServiceTblSubject() && $tblGradeItem->getGrade()
                                ) {
                                    $countSubjectGrades++;
                                }
                            }
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
                            $excusedDays = $tblPrepareStudent->getExcusedDays();
                            $unexcusedDays = $tblPrepareStudent->getUnexcusedDays();
                        } else {
                            $tblCertificate = false;
                        }

                        if ($excusedDays === null) {
                            $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision,
                                new \DateTime($tblPrepare->getDate()));
                        }
                        if ($unexcusedDays === null) {
                            $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblDivision,
                                new \DateTime($tblPrepare->getDate()));
                        }

                        $studentTable[$tblPerson->getId()] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName(),
                            'Course' => $course,
                            'ExcusedAbsence' => $excusedDays . ' ',
                            'UnexcusedAbsence' => $unexcusedDays . ' ',
                            'SubjectGrades' => ($countSubjectGrades < $countSubjects || !$tblPrepare->getServiceTblAppointedDateTask()
                                ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $subjectGradesText)
                                : new Success(new Enable() . ' ' . $subjectGradesText)),
                            'BehaviorGrades' => ($countBehaviorGrades < $countBehavior || !$tblPrepare->getServiceTblBehaviorTask()
                                ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' ' . $behaviorGradesText)
                                : new Success(new Enable() . ' ' . $behaviorGradesText)),
//                            'Template' => ($tblCertificate
//                                ? new Success(new Enable() . ' ' . $tblCertificate->getName()
//                                    . ($tblCertificate->getDescription() ? '<br>' . $tblCertificate->getDescription() : ''))
//                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' Keine Zeugnisvorlage ausgewählt')),
                            'Option' =>
                                ($tblCertificate
                                    ? (new Standard(
                                        '', '/Education/Certificate/Prepare/Certificate/Show', new EyeOpen(),
                                        array('PrepareId' => $tblPrepare->getId(), 'PersonId' => $tblPerson->getId()),
                                        'Zeugnisvorschau anzeigen'))
                                    . (new External(
                                        '',
                                        '/Api/Education/Certificate/Generator/Preview',
                                        new Download(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'PersonId' => $tblPerson->getId(),
                                            'Name' => 'Zeugnismuster'
                                        ),
                                        'Zeugnis als Muster herunterladen'))
                                    : '')
                        );

                        // Vorlagen informationen
                        $this->getTemplateInformationForPreview($tblPrepare, $tblPerson, $studentTable, $columnTable);
                    }
                }
            }

            $columnTable['Option'] = '';

            $buttonSigner = new Standard(
                'Unterzeichner auswählen',
                '/Education/Certificate/Prepare/Signer',
                new Select(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                ),
                'Unterzeichner auswählen'
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
                            $tblPrepare->getServiceTblGenerateCertificate()
                            && $tblPrepare->getServiceTblGenerateCertificate()->isDivisionTeacherAvailable()
                                ? new LayoutColumn(array(
                                new Panel(
                                    'Unterzeichner',
                                    array(
                                        $tblPrepare->getServiceTblPersonSigner()
                                            ? $tblPrepare->getServiceTblPersonSigner()->getFullName()
                                            : new Exclamation() . ' Kein Unterzeichner ausgewählt',
                                        $buttonSigner
                                    ),
                                    $tblPrepare->getServiceTblPersonSigner()
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 6)
                                : null,
                            new LayoutColumn(array(
                                $tblPrepare->getServiceTblAppointedDateTask()
                                    ? new Standard(
                                    'Fachnoten ansehen',
                                    '/Education/Certificate/Prepare/Prepare/Preview/SubjectGrades',
                                    null,
                                    array(
                                        'PrepareId' => $PrepareId
                                    )
                                ) : null,
                                new External(
                                    'Alle Zeugnisse als Muster herunterladen',
                                    '/Api/Education/Certificate/Generator/PreviewZip',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Name' => 'Musterzeugnis'
                                    ),
                                    false
                                )
                            ))
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($studentTable, null, $columnTable, array(
                                    'order' => array(
                                        array('0', 'asc'),
                                    ),
                                    "paging" => false, // Deaktivieren Blättern
                                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    "responsive" => false
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
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param $studentTable
     * @param $columnTable
     */
    private function getTemplateInformationForPreview(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        &$studentTable,
        &$columnTable
    ) {

        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);
        if ($tblPrepareStudent && $tblPrepareStudent->getServiceTblCertificate()) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
        } else {
            $tblCertificate = false;
        }

        if ($tblCertificate && ($tblDivision = $tblPrepareCertificate->getServiceTblDivision())) {
            $Certificate = null;
            if ($tblCertificate) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                if (class_exists($CertificateClass)) {

                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = new $CertificateClass($tblPerson, $tblDivision);

                    $CertificateList[$tblPerson->getId()] = $Certificate;

                    $FormField = Generator::useService()->getFormField();
                    $FormLabel = Generator::useService()->getFormLabel();

                    $PlaceholderList = $Certificate->getCertificate()->getPlaceholder();
                    if ($PlaceholderList) {
                        array_walk($PlaceholderList,
                            function ($Placeholder) use (
                                $Certificate,
                                $FormField,
                                $FormLabel,
                                &$columnTable,
                                &$studentTable,
                                $tblPerson,
                                $tblPrepareStudent
                            ) {

                                $PlaceholderList = explode('.', $Placeholder);
                                $Identifier = array_slice($PlaceholderList, 1);

                                $Type = array_shift($Identifier);
                                if (!method_exists($Certificate, 'get' . $Type)) {
                                    if (isset($FormField[$Placeholder])) {
                                        if (isset($FormLabel[$Placeholder])) {
                                            $Label = $FormLabel[$Placeholder];
                                        } else {
                                            $Label = $Placeholder;
                                        }

                                        $key = str_replace('Content.Input.', '', $Placeholder);
                                        if (!isset($columnTable[$key])) {
                                            $columnTable[$key] = $Label;
                                        }

                                        if (isset($FormField[$Placeholder]) && $FormField[$Placeholder] == 'TextArea') {
                                            if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                                                    $tblPrepareStudent->getTblPrepareCertificate(), $tblPerson, $key))
                                                && !empty(trim($tblPrepareInformation->getValue()))
                                            ) {
                                                $studentTable[$tblPerson->getId()][$key] =
                                                    new Success(new Enable() . ' ' . 'erledigt');
                                            } else {
                                                $studentTable[$tblPerson->getId()][$key] =
                                                    new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                                        new Exclamation() . ' ' . 'nicht erledigt');
                                            }
                                        } else {
                                            if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                                                $tblPrepareStudent->getTblPrepareCertificate(), $tblPerson, $key))
                                            ) {
                                                $studentTable[$tblPerson->getId()][$key] = $tblPrepareInformation->getValue();
                                            } else {
                                                $studentTable[$tblPerson->getId()][$key] = '';
                                            }
                                        }
                                    }
                                }
                            });
                    }
                }
            }
        }
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage|string
     */
    public function frontendPrepareShowSubjectGrades($PrepareId = null)
    {

        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten-Übersicht');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
        ) {

            $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Preview',
                    new ChevronLeft(),
                    array('PrepareId' => $PrepareId))
            );

            $studentList = array();
            $tableHeaderList = array();
            // Alle Klassen ermitteln in denen der Schüler im aktuellen Schuljahr Unterricht hat
            $divisionList = array();
            $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
            $divisionPersonList = array();
            if ($tblDivisionStudentAll) {
                foreach ($tblDivisionStudentAll as $tblPerson) {
                    if (($tblPersonDivisionList = Student::useService()->getCurrentDivisionListByPerson($tblPerson))) {
                        foreach ($tblPersonDivisionList as $tblDivisionItem) {
                            if (!isset($divisionList[$tblDivisionItem->getId()])) {
                                $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                            }
                        }
                    }
                    $divisionPersonList[$tblPerson->getId()] = 1;
                }
            }

            foreach ($divisionList as $tblDivisionItem) {
                if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivisionItem))) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblSubject = $tblTest->getServiceTblSubject();
                        if ($tblSubject && $tblTest->getServiceTblDivision()) {
                            $tableHeaderList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();

                            $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                $tblTest->getServiceTblDivision(),
                                $tblSubject,
                                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                            );

                            if ($tblDivisionSubject && $tblDivisionSubject->getTblSubjectGroup()) {
                                $tblSubjectStudentAllByDivisionSubject =
                                    Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                if ($tblSubjectStudentAllByDivisionSubject) {
                                    foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                        $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                        if ($tblPerson) {
                                            $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                $tblTest, $tblSubject, $tblPerson, $studentList,
                                                $tblDivisionSubject->getTblSubjectGroup()
                                                    ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                $tblPrepare
                                            );
                                        }
                                    }

                                    // nicht vorhandene Schüler in der Gruppe auf leer setzten
                                    if ($tblDivisionStudentAll) {
                                        foreach ($tblDivisionStudentAll as $tblPersonItem) {
                                            if (!isset($studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()])) {
                                                $studentList[$tblPersonItem->getId()][$tblSubject->getAcronym()] = '';
                                            }
                                        }
                                    }
                                }
                            } else {
                                if ($tblDivisionStudentAll) {
                                    $count = 1;
                                    foreach ($tblDivisionStudentAll as $tblPerson) {
                                        // nur Schüler der ausgewählten Klasse
                                        if (isset($divisionPersonList[$tblPerson->getId()])) {
                                            $studentList[$tblPerson->getId()]['Number'] = $count++;
                                            $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                $tblTest, $tblSubject, $tblPerson, $studentList, null, $tblPrepare);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($tableHeaderList)) {
                ksort($tableHeaderList);
                $prependTableHeaderList['Number'] = '#';
                $prependTableHeaderList['Name'] = 'Schüler';
                $tableHeaderList = $prependTableHeaderList + $tableHeaderList;
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
                                )),
                                new LayoutColumn(array(
                                    new TableData(
                                        $studentList, null, $tableHeaderList, null
                                    )
                                ))
                            ))
                        ))
                    ))
                );
            }

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblSubject $tblSubject
     * @param TblPerson $tblPerson
     * @param $studentList
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return  $studentList
     */
    private function setTableContentForAppointedDateTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        $studentList,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPrepareCertificate $tblPrepare = null
    ) {
        $studentList[$tblPerson->getId()]['Name'] =
            $tblPerson->getLastFirstName();
        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        $tblTask = $tblTest->getTblTask();

        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup
        );

        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson,
            $tblDivision,
            $tblSubject,
            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblScoreRule ? $tblScoreRule : null,
            $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null,
            null,
            false,
            $tblTask->getDate() ? $tblTask->getDate() : false
        );
        if (is_array($average)) {
            $average = ' ';
        } else {
            $posStart = strpos($average, '(');
            if ($posStart !== false) {
                $average = substr($average, 0, $posStart);
            }
        }

        if ($tblGrade) {
            // Zeugnistext
            if (($tblGradeText = $tblGrade->getTblGradeText())) {
                $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblGradeText->getName();

                return $studentList;
            }

            $gradeValue = $tblGrade->getGrade();

            $isGradeInRange = true;
            if ($average !== ' ' && $average && $gradeValue !== null) {
                if (is_numeric($gradeValue)) {
                    $gradeFloat = floatval($gradeValue);
                    if (($gradeFloat - 0.5) <= $average && ($gradeFloat + 0.5) >= $average) {
                        $isGradeInRange = true;
                    } else {
                        $isGradeInRange = false;
                    }
                }
            }

            $withTrend = true;
            if ($tblPrepare
                && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                    $tblGrade->getServiceTblPerson()))
                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                && !$tblCertificate->isInformation()
            ) {
                $withTrend = false;
            }
            $gradeValue = $tblGrade->getDisplayGrade($withTrend);

            if ($isGradeInRange) {
                $gradeValue = new Success($gradeValue);
            } else {
                $gradeValue = new \SPHERE\Common\Frontend\Text\Repository\Danger($gradeValue);
            }

            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] = ($tblGrade->getGrade() !== null
                    ? $gradeValue : '') .
                (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        } else {
            $studentList[$tblPerson->getId()][$tblSubject->getAcronym()] =
                new \SPHERE\Common\Frontend\Text\Repository\Warning('fehlt')
                . (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendShowCertificate(
        $PrepareId = null,
        $PersonId = null
    ) {
        $Stage = new Stage('Zeugnisvorlage', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
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
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\'
                        . $tblCertificate->getCertificate();
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
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSigner($PrepareId = null, $Data = null)
    {

        $Stage = new Stage('Unterzeichner', 'Auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
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
}
