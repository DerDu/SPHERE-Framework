<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.11.2016
 * Time: 08:45
 */

namespace SPHERE\Application\Education\Certificate\Generate;

use SPHERE\Application\Api\Education\Certificate\Generate\ApiGenerate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Education\Certificate\Generate
 */
class Frontend extends Extension
{

    /**
     * @param bool $IsAllYears
     * @param null $YearId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendGenerate($IsAllYears = false, $YearId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnis generieren', 'Übersicht');

        $buttonList = Evaluation::useFrontend()->setYearButtonList('/Education/Certificate/Generate',
            $IsAllYears, $YearId, $tblYear);

        $tableData = array();
        if (($tblGenerateCertificateAll = Generate::useService()->getGenerateCertificateAll())) {
            foreach ($tblGenerateCertificateAll as $tblGenerateCertificate) {
                // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                /** @var TblYear $tblYear */
                if ($tblYear
                    && ($tblYearCertificate = $tblGenerateCertificate->getServiceTblYear())
                    && $tblYearCertificate->getId() != $tblYear->getId()
                ) {
                    continue;
                }

                // Zusatz Option für Abschlusszeugnisse
                $hasDiplomaCertificate = false;
                if (($tblGenerateCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                    && $tblGenerateCertificateType->getIdentifier() == 'DIPLOMA'
                ) {
                    $hasDiplomaCertificate = true;
                }

                $tableData[] = array(
                    'Date' => $tblGenerateCertificate->getDate(),
                    'Type' => $tblGenerateCertificate->getServiceTblCertificateType()
                        ? $tblGenerateCertificate->getServiceTblCertificateType()->getName() : '',
                    'Name' => $tblGenerateCertificate->getName(),
                    'Option' =>
                        (new Standard(
                            '', '/Education/Certificate/Generate/Edit', new Edit(),
                            array(
                                'Id' => $tblGenerateCertificate->getId(),
                            )
                            , 'Bearbeiten'
                        ))
                        . (new Standard(
                            '', '/Education/Certificate/Generate/Division/Select', new Listing(),
                            array(
                                'GenerateCertificateId' => $tblGenerateCertificate->getId(),
                            )
                            , 'Klassen zuordnen'
                        ))
                        . (new Standard(
                            '', '/Education/Certificate/Generate/Division', new Equalizer(),
                            array(
                                'GenerateCertificateId' => $tblGenerateCertificate->getId(),
                            )
                            , 'Zeugnisvorlagen zuordnen'
                        ))
                        . ($hasDiplomaCertificate
                            ? (new Standard(
                                '', '/Education/Certificate/Generate/Setting', new Cog(),
                                array(
                                    'GenerateCertificateId' => $tblGenerateCertificate->getId(),
                                )
                                , 'Zusätzliche Einstellungen: Prüfungsausschuss'
                            ))
                            : ''
                        )
                        . ($tblGenerateCertificate->isLocked()
                            ? ''
                            : (new Standard(
                                '', '/Education/Certificate/Generate/Destroy', new Remove(),
                                array(
                                    'Id' => $tblGenerateCertificate->getId(),
                                )
                                , 'Löschen'
                            )))
                );
            }
        }

        if ($tblYear) {
            $Form = $this->formGenerate($tblYear ? $tblYear : null)
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        } else {
            $Form = null;
        }

        $Stage->setContent(
            new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            empty($buttonList)
                                ? null
                                : new LayoutColumn($buttonList)
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
                    $IsAllYears
                        ? null
                        : new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Well(Generate::useService()->createGenerateCertificate($Form, $Data))
                            ))
                        ))

                    ), new Title(new PlusSign() . ' Hinzufügen'))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return Form
     */
    private function formGenerate(TblYear $tblYear = null)
    {

        $certificateTypeList = array();
        $tblCertificateTypeAll = Generator::useService()->getCertificateTypeAll();
        if ($tblCertificateTypeAll) {
            foreach ($tblCertificateTypeAll as $tblCertificateType) {
                if ($tblCertificateType->getIdentifier() !== 'LEAVE') {
                    $certificateTypeList[] = $tblCertificateType;
                }
            }
        }

        $Global = $this->getGlobal();
        if (!$Global->POST) {
            if ($tblYear) {
                $Global->POST['Data']['Year'] = $tblYear ? $tblYear->getId() : 0;

                // Halbjahr oder Jahreszeugnis vorauswählen an Hand des aktuellen Datums
                if (($tblPeriodList = $tblYear->getTblPeriodAll(false))
                    && count($tblPeriodList) == 2
                ) {
                    $tblCurrentPeriod = false;
                    foreach ($tblPeriodList as $tblPeriod) {
                        if ($tblPeriod->getFromDate() && $tblPeriod->getToDate()) {
                            $fromDate = (new \DateTime($tblPeriod->getFromDate()))->format("Y-m-d");
                            $toDate = (new \DateTime($tblPeriod->getToDate()))->format("Y-m-d");
                            $now = (new \DateTime('now'))->format("Y-m-d");
                            if ($fromDate <= $now && $now <= $toDate) {
                                $tblCurrentPeriod = $tblPeriod;
                                break;
                            }
                        }
                    }

                    if ($tblCurrentPeriod) {
                        if ($tblPeriodList[0]->getFromDate() && $tblPeriodList[1]->getFromDate()
                            && (new \DateTime($tblPeriodList[0]->getFromDate()))->format("Y-m-d")
                            < (new \DateTime($tblPeriodList[1]->getFromDate()))->format("Y-m-d")
                        ) {
                            $tblFirstPeriod = $tblPeriodList[0];
                            $tblSecondPeriod = $tblPeriodList[1];
                        } else {
                            $tblFirstPeriod = $tblPeriodList[1];
                            $tblSecondPeriod = $tblPeriodList[0];
                        }

                        if ($tblFirstPeriod->getId() == $tblCurrentPeriod->getId()) {
                            $Global->POST['Data']['Type'] = Generator::useService()->getCertificateTypeByIdentifier('HALF_YEAR');
                        } elseif ($tblSecondPeriod->getId() == $tblCurrentPeriod->getId()) {
                            $Global->POST['Data']['Type'] = Generator::useService()->getCertificateTypeByIdentifier('YEAR');
                        }
                    }
                }
            }

            $Global->savePost();
        }

        $tblYearList = Term::useService()->getYearAll();

        $paramArray = array(
            'Year' => $tblYear ? $tblYear->getId() : null
        );
        $receiverAppointedDateTask = ApiGenerate::receiverFormSelect(
            (new ApiGenerate())->reloadAppointedDateTaskSelect($paramArray)
        );
        $receiverBehaviorTask = ApiGenerate::receiverFormSelect(
            (new ApiGenerate())->reloadBehaviorTaskSelect($paramArray)
        );

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(
                        'Zeugnis',
                        array(
                            (new SelectBox('Data[Year]',
                                'Schuljahr',
                                array('{{ Name }} {{ Description }}' => $tblYearList)
                            ))->ajaxPipelineOnChange(
                                array(
                                    ApiGenerate::pipelineCreateAppointedDateTaskSelect($receiverAppointedDateTask),
                                    ApiGenerate::pipelineCreateBehaviorTaskSelect($receiverBehaviorTask)
                                )
                            )->setRequired(),
                            (new TextField('Data[Name]', '', 'Name des Zeugnisauftrags'))->setRequired(),
                            (new DatePicker('Data[Date]', '', 'Zeugnisdatum', new Calendar()))->setRequired(),
                            (new SelectBox('Data[Type]', 'Typ', array('Name' => $certificateTypeList)))->setRequired()
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Notenaufträge',
                        array(
                            $receiverAppointedDateTask,
                            $receiverBehaviorTask
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Unterzeichner',
                        array(
                            (new CheckBox('Data[IsTeacherAvailable]',
                                'Name des Klassenlehrers und Name des/der Schulleiters/in (falls vorhanden) auf dem Zeugnis anzeigen',
                                1
                            )),
                            new TextField('Data[HeadmasterName]', '', 'Name des/der Schulleiters/in'),
                            new Panel(
                                new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                array(
                                    (new RadioBox('Data[GenderHeadmaster]', 'Männlich',
                                        ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                                            ? $tblCommonGender->getId() : 0)),
                                    (new RadioBox('Data[GenderHeadmaster]', 'Weiblich',
                                        ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                                            ? $tblCommonGender->getId() : 0))
                                ),
                                Panel::PANEL_TYPE_DEFAULT
                            )
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
            )),
        )));
    }

    /**
     * @param null $GenerateCertificateId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSelectDivision($GenerateCertificateId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnis generieren', 'Klassen zuordnen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));

        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($GenerateCertificateId))) {

            $divisionExistsList = array();
            $hasPreSelectedDivisions = false;
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                    foreach ($tblPrepareList as $tblPrepareCertificate) {
                        if (($tblDivision = $tblPrepareCertificate->getServiceTblDivision())) {
                            $Global->POST['Data']['Division'][$tblDivision->getId()] = 1;
                            $divisionExistsList[$tblDivision->getId()] = $tblDivision;
                        }
                    }
                } else {
                    // vorselektieren anhand der Notenaufträge
                    if (($tblGenerateCertificate->getServiceTblAppointedDateTask())) {
                        $tblTestAllByTest = Evaluation::useService()->getTestAllByTask(
                            $tblGenerateCertificate->getServiceTblAppointedDateTask()
                        );
                        if ($tblTestAllByTest) {
                            $hasPreSelectedDivisions = true;
                            foreach ($tblTestAllByTest as $tblTest) {
                                if (($tblDivision = $tblTest->getServiceTblDivision())) {
                                    $Global->POST['Data']['Division'][$tblDivision->getId()] = 1;
                                }
                            }
                        }
                    }
                    if (($tblGenerateCertificate->getServiceTblBehaviorTask())) {
                        $tblTestAllByTest = Evaluation::useService()->getTestAllByTask(
                            $tblGenerateCertificate->getServiceTblBehaviorTask()
                        );
                        if ($tblTestAllByTest) {
                            $hasPreSelectedDivisions = true;
                            foreach ($tblTestAllByTest as $tblTest) {
                                if (($tblDivision = $tblTest->getServiceTblDivision())) {
                                    $Global->POST['Data']['Division'][$tblDivision->getId()] = 1;
                                }
                            }
                        }
                    }
                }
            }
            $Global->savePost();

            $schoolTypeList = array();
            if ($tblGenerateCertificate->getServiceTblYear() && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())) {
                $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblGenerateCertificate->getServiceTblYear());
                if ($tblDivisionAllByYear) {
                    foreach ($tblDivisionAllByYear as $tblDivision) {
                        // keine Klassenstufen Übergreifende anzeigen
                        // auch Klassen ohne Fächer anzeigen, z.B. für 1. Klasse
                        if (!$tblDivision->getTblLevel()->getIsChecked()) {
                            $type = $tblDivision->getTblLevel()->getServiceTblType();

                            // Klassen und Schulart Auswahl nach Typ
                            if ($type) { // && $tblDivisionSubjectList) {
                                if ($tblCertificateType->getIdentifier() == 'DIPLOMA') {
                                    if ($type->getName() == 'Gymnasium'
                                        && (($tblLevel = $tblDivision->getTblLevel()))
                                        && $tblLevel->getName() == '12'
                                    ) {
                                        $schoolTypeList[$type->getId()][$tblDivision->getId()] = $tblDivision->getDisplayName();
                                    } elseif ($type->getName() == 'Mittelschule / Oberschule'
                                        && (($tblLevel = $tblDivision->getTblLevel()))
                                        && ($tblLevel->getName() == '9' || $tblLevel->getName() == '10')
                                    ) {
                                        $schoolTypeList[$type->getId()][$tblDivision->getId()] = $tblDivision->getDisplayName();
                                    }
                                } elseif ($tblCertificateType->getIdentifier() == 'MID_TERM_COURSE') {
                                    // nur Gymnasium Klasse 11 und 12
                                    if ($type->getName() == 'Gymnasium'
                                        && (($tblLevel = $tblDivision->getTblLevel()))
                                        && ($tblLevel->getName() == '11' || $tblLevel->getName() == '12')
                                    ) {
                                        $schoolTypeList[$type->getId()][$tblDivision->getId()] = $tblDivision->getDisplayName();
                                    }
                                } else {
                                    if ($tblCertificateType->getIdentifier() != 'GRADE_INFORMATION'
                                        && $type->getName() == 'Gymnasium'
                                        && (($tblLevel = $tblDivision->getTblLevel()))
                                        && ($tblLevel->getName() == '11' || $tblLevel->getName() == '12')
                                    ) {
                                        continue;
                                    }

                                    $schoolTypeList[$type->getId()][$tblDivision->getId()] = $tblDivision->getDisplayName();
                                }
                            }
                        }
                    }
                }
            }

            $columnList = array();
            if (!empty($schoolTypeList)) {
                foreach ($schoolTypeList as $typeId => $divisionList) {
                    $type = Type::useService()->getTypeById($typeId);
                    if ($type && is_array($divisionList)) {

                        asort($divisionList, SORT_NATURAL);

                        $checkBoxList = array();
                        foreach ($divisionList as $key => $value) {
                            if (isset($divisionExistsList[$key])) {
                                $checkBoxList[] = (new CheckBox('Data[Division][' . $key . ']', $value,
                                    1))->setDisabled();
                            } else {
                                $checkBoxList[] = new CheckBox('Data[Division][' . $key . ']', $value, 1);
                            }
                        }

                        $panel = new Panel($type->getName(), $checkBoxList, Panel::PANEL_TYPE_DEFAULT);
                        $columnList[] = new FormColumn($panel, 3);
                    }
                }
            }
            $columnList[] = new FormColumn(new HiddenField('Data[IsSubmit]'));

            $form = new Form(array(
                new FormGroup(
                    new FormRow(
                        $columnList
                    )
                    , new \SPHERE\Common\Frontend\Form\Repository\Title('Klassen'))
            ));
            $form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblGenerateCertificate->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ',
                                    $tblGenerateCertificate->getServiceTblCertificateType()
                                        ? $tblGenerateCertificate->getServiceTblCertificateType()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Stichtagsnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblAppointedDateTask()
                                        ? $tblGenerateCertificate->getServiceTblAppointedDateTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Kopfnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblBehaviorTask()
                                        ? $tblGenerateCertificate->getServiceTblBehaviorTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                        ))
                    )),
                    $hasPreSelectedDivisions
                        ? new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new \SPHERE\Common\Frontend\Message\Repository\Warning(
                                    'Die vorselektierten Klassen aus den Notenaufträgen wurden noch nicht gespeichert.',
                                    new Exclamation())
                            )),
                        ))
                    )) : null,
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Well(Generate::useService()->createPrepareCertificates($form,
                                    $tblGenerateCertificate, $Data))
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Zeugniserstellung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param null $GenerateCertificateId
     *
     * @return Stage|string
     */
    public function frontendDivision($GenerateCertificateId = null)
    {

        $Stage = new Stage('Zeugnis generieren', 'Klassenübersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));

        $Stage->setMessage(
            new Warning(new Bold('Hinweis: ')
                . new Container('Für die automatischen Zuordnungen der Zeugnisvorlagen zu den Schülern werden
                    die folgenden Daten herangezogen:')
                . new Container('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&ndash; Die Schulart wird über Klasse ermittelt 
                    (Bildung -> Unterricht).')
                . new Container('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&ndash; Bei Zeugnisvorlagen der Mittelschule ab 
                    Klasse 7 ist der Bildungsgang erforderlich (Schülerakte -> Schulverlauf -> Aktueller Bildungsgang).')
                . new Container('Bei staatlichen Zeugnisvorlagen ist zusätzlich die aktuelle Schule erforderlich 
                    (Schülerakte -> Schulverlauf -> Aktuelle Schule).')
            )
        );

        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($GenerateCertificateId))) {
            $tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType();
            $tableData = array();

            if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                foreach ($tblPrepareList as $tblPrepare) {
                    if (($tblDivision = $tblPrepare->getServiceTblDivision())
                        && ($tblLevel = $tblDivision->getTblLevel())
                        && ($tblType = $tblLevel->getServiceTblType())
                    ) {
                        $certificateNameList = array();
                        $schoolNameList = array();
                        $countTemplates = $countStudents = 0;
                        // für Noteninformation
                        if ($tblGenerateCertificate->getServiceTblCertificateType()
                            && $tblGenerateCertificate->getServiceTblCertificateType()->getIdentifier() == 'GRADE_INFORMATION'
                        ) {
                            if (($tblDivision = $tblPrepare->getServiceTblDivision())
                                && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
                            ) {
                                $countTemplates = $countStudents = count($tblPersonList);
                            }
                        } else {
                            $countTemplates = Generate::useService()->setCertificateTemplates($tblPrepare,
                                $countStudents, $certificateNameList, $schoolNameList);
                        }

                        $text = '';
                        if (!empty($certificateNameList)) {
                            $text = implode(', ', $certificateNameList);
                        }
                        $schoolText = '';
                        if (!empty($schoolNameList)) {
                            foreach ($schoolNameList as $schoolName) {
                                $schoolText .= new Container($schoolName);
                            }
                        }

                        // bei Abschlusszeugnisse Klasse 9 nur Hauptschüler zählen
                        if ($tblCertificateType
                            && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                            && Prepare::useService()->isCourseMainDiploma($tblPrepare)
                        ) {
                            $countStudents = 0;
                            if (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                                foreach ($tblStudentList as $tblPerson) {
                                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                                        && ($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                                        && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                            $tblTransferType))
                                        && ($tblCourse = $tblStudentTransfer->getServiceTblCourse())
                                        && $tblCourse->getName() == 'Hauptschule'
                                    ) {
                                        $countStudents++;
                                    }
                                }
                            }
                        }
                        $hasMissingForeignLanguage = false;
                        // check missing subjects on certificates
                        if (($missingSubjects = Prepare::useService()->checkCertificateSubjectsForDivision($tblPrepare, $certificateNameList, $hasMissingForeignLanguage))) {
                            ksort($missingSubjects);
                        }
                        if ($missingSubjects) {
                            $missingSubjectsString = new Warning(new Ban() .  ' ' . implode(', ',
                                $missingSubjects) . (count($missingSubjects) > 1 ? ' fehlen' : ' fehlt')
                                . ' auf Zeugnisvorlage(n)'
                                .($hasMissingForeignLanguage
                                    ? ' ' . new ToolTip(new \SPHERE\Common\Frontend\Icon\Repository\Info(),
                                        'Bei Fremdsprachen kann die Warnung unter Umständen ignoriert werden,
                                         bitte prüfen Sie die Detailansicht unter Bearbeiten.') : ''));
                        } else {
                            $missingSubjectsString = new Success(
                                new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Alle Fächer sind zugeordnet.'
                            );
                        }

                        // Abitur Fächerprüfung ignorieren
                        if ($tblCertificateType
                            && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                            && $tblLevel->getName() == '12'
                        ) {
                            $missingSubjectsString = new Success(
                                new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Keine Fächerzuordnung erforderlich.'
                            );
                        }

                        $tableData[] = array(
                            'SchoolType' => $tblType->getName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'School' => $schoolText,
                            'Status' => ($countTemplates < $countStudents
                                ? new Warning(new Exclamation() . ' ' . $countTemplates . ' von '
                                    . $countStudents . ' Zeugnisvorlagen zugeordnet.')
                                : new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' ' . $countTemplates . ' von ' . $countStudents . ' Zeugnisvorlagen zugeordnet.')),
                            'Templates' => $text,
                            'CheckSubjects' => $missingSubjectsString,
                            'Option' =>
                                new Standard('', '/Education/Certificate/Generate/Division/SelectTemplate',
                                    new Edit(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'DivisionId' => $tblDivision->getId(),
                                    ),
                                    'Bearbeiten'
                                )
                        );
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblGenerateCertificate->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ', $tblCertificateType ? $tblCertificateType->getName() : '',
                                    Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Name', $tblGenerateCertificate ? $tblGenerateCertificate->getName() : '',
                                    Panel::PANEL_TYPE_INFO)
                            ), 6),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData(
                                    $tableData, null, array(
                                    'SchoolType' => 'Schulart',
                                    'Division' => 'Klasse',
                                    'School' => 'Aktuelle Schulen',
                                    'Status' => 'Zeugnisvorlagen Zuordnung',
                                    'Templates' => 'Zeugnisvorlagen',
                                    'CheckSubjects' => 'Prüfung Fächer/Zeugnis',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array('0', 'asc'),
                                            array('1', 'asc'),
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => 1)
                                        ),
                                        "paging" => false,
                                        "iDisplayLength" => -1
                                    )
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisgenerierung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $DivisionId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSelectTemplate($PrepareId = null, $DivisionId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnis generieren', 'Zeugnisvorlagen auswählen');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = Division::useService()->getDivisionById($DivisionId))
        ) {
            $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate/Division', new ChevronLeft(),
                    array('GenerateCertificateId' => $tblPrepare->getServiceTblGenerateCertificate()->getId()))
            );

            $isCourseMainDiploma = Prepare::useService()->isCourseMainDiploma($tblPrepare);
            $isDiploma = false;
            $tblCertificateType = false;
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                && $tblCertificateType->getIdentifier() == 'DIPLOMA'
            ) {
                $isDiploma = true;
            }

            $tableData = array();
            $checkSubjectList = Prepare::useService()->checkCertificateSubjectsForStudents($tblPrepare);
            if (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                $count = 0;
                $Global = $this->getGlobal();
                foreach ($tblStudentList as $tblPerson) {
                    $isMuted = $isCourseMainDiploma && $isDiploma;

                    $tblCourse = false;
                    $tblCompany = false;
                    if (($tblStudent = $tblPerson->getStudent())
                        && ($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                    ) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            $tblCompany = Student::useService()->getCurrentSchoolByPerson($tblPerson, $tblDivision);
                            if ($tblCourse && $tblCourse->getName() == 'Hauptschule') {
                                $isMuted = false;
                            }
                        }
                    }

                    if (isset($checkSubjectList[$tblPerson->getId()])) {
                        $checkSubjectsString = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban() . ' '
                            . implode(', ', $checkSubjectList[$tblPerson->getId()])
                            . (count($checkSubjectList[$tblPerson->getId()]) > 1 ? ' fehlen' : ' fehlt') . ' auf Zeugnisvorlage');
                    } elseif(($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                        && $tblPrepareStudent->getServiceTblCertificate()) {
                        $checkSubjectsString = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .
                            ' alles ok');
                    } else {
                        $checkSubjectsString = '';
                    }

                    $courseName = $tblCourse ? $tblCourse->getName() : '';

                    // Primärer Förderschwerpunkt -> zur Hilfe für Auswahl des Zeugnisses
                    $primaryFocus = '';
                    if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
                        && ($tblPrimaryFocus = Student::useService()->getPrimaryFocusBySupport($tblSupport))
                    ) {
                        $primaryFocus = $tblPrimaryFocus->getName();
                    }

                    $count++;
                    $tableData[$tblPerson->getId()] = array(
                        'Number' => $isMuted ? new Muted($count) : $count,
                        'Student' => $isMuted ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName(),
                        'Course' => $isMuted ? new Muted($courseName) : $courseName,
                        'School' => $isMuted ? '' : ($tblCompany ? $tblCompany->getName() : new Warning(
                            new Exclamation() . ' Keine aktuelle Schule in der Schülerakte gepflegt oder bei der Klasse hinterlegt.'
                        )),
                        'PrimaryFocus' => $isMuted ? new Muted($primaryFocus) : $primaryFocus,
                        'CheckSubjects' => $checkSubjectsString,
                    );

                    if ($isMuted) {
                        $tableData[$tblPerson->getId()]['Template'] = '';
                    } else {
                        $tblCertificate = false;
                        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                        ) {

                        } else {
                            // Noteninformation
                            if ($tblPrepare->isGradeInformation()) {
                                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GradeInformation');
                            }
                        }
                        if ($Global && $tblCertificate) {
                            $Global->POST['Data'][$tblPerson->getId()] = $tblCertificate->getId();
                            $Global->savePost();
                        }

                        // Alle Zeungisse nach Zeugnistyp zur Auswahl
                        $tblCertificateType = false;
                        if ($tblPrepare->getServiceTblGenerateCertificate()) {
                            $tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType();
                        }

                        $tblCertificateAllByType = array();
                        if ($tblCertificateType) {
                            $tblConsumer = Consumer::useService()->getConsumerBySession(null);
                            $tblCertificateAllStandard = Generator::useService()->getCertificateAllByConsumerAndCertificateType(null, $tblCertificateType);
                            $tblCertificateAllConsumer = Generator::useService()->getCertificateAllByConsumerAndCertificateType($tblConsumer, $tblCertificateType);
                            if ($tblCertificateAllConsumer) {
                                $tblCertificateAllByType = array_merge($tblCertificateAllByType, $tblCertificateAllConsumer);
                            }
                            if ($tblCertificateAllStandard) {
                                $tblCertificateAllByType = array_merge($tblCertificateAllByType, $tblCertificateAllStandard);
                            }
                        }
                        $tableData[$tblPerson->getId()]['Template'] = new SelectBox('Data[' . $tblPerson->getId() . ']',
                            '',
                            array(
                                '{{ serviceTblConsumer.Acronym }} {{ Name }} {{Description}}' => $tblCertificateAllByType
                            )
                        );
                    }
                }
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new TableData(
                                $tableData, null, array(
                                'Number' => 'Nr.',
                                'Student' => 'Schüler',
                                'Course' => 'Bildungsgang',
                                'School' => 'Aktuelle Schule',
                                'PrimaryFocus' => 'primärer FS',
                                'Template' => 'Zeugnisvorlage',
                                'CheckSubjects' => 'Prüfung Fächer/Zeugnis'
                            ), null
                            )
                        )
                    )
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblPrepare->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ', $tblCertificateType ? $tblCertificateType->getName() : '',
                                    Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Name', $tblGenerateCertificate ? $tblGenerateCertificate->getName() : '',
                                    Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Klasse', $tblDivision->getDisplayName(), Panel::PANEL_TYPE_INFO)
                            ), 3)
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                Generate::useService()->editCertificateTemplates($form, $tblPrepare, $Data)
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return new Danger('Klasse nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditGenerate($Id = null, $Data = null)
    {

        $Stage = new Stage('Zeugnis generieren', 'Bearbeiten');
        $Stage->setMessage('Die Notenaufträge können nur geändert werden bis ein Zeugnis freigeben wurde.');

        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($Id))) {

            $Stage->addButton(
                new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft())
            );

            $Global = $this->getGlobal();
            if (!$Global->POST) {

                $Global->POST['Data']['Date'] = $tblGenerateCertificate->getDate();
                $Global->POST['Data']['Name'] = $tblGenerateCertificate->getName();
                $Global->POST['Data']['IsTeacherAvailable'] = $tblGenerateCertificate->isDivisionTeacherAvailable();
                $Global->POST['Data']['HeadmasterName'] = $tblGenerateCertificate->getHeadmasterName();
                $Global->POST['Data']['GenderHeadmaster'] = $tblGenerateCertificate->getServiceTblCommonGenderHeadmaster()
                    ? $tblGenerateCertificate->getServiceTblCommonGenderHeadmaster()->getId() : 0;
                $Global->POST['Data']['AppointedDateTask'] = $tblGenerateCertificate->getServiceTblAppointedDateTask()
                    ? $tblGenerateCertificate->getServiceTblAppointedDateTask()->getId() : 0;
                $Global->POST['Data']['BehaviorTask'] = $tblGenerateCertificate->getServiceTblBehaviorTask()
                    ? $tblGenerateCertificate->getServiceTblBehaviorTask()->getId() : 0;

                $Global->savePost();
            }

            $message = '';
            if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
                $countBehaviorGrades = 0;
                foreach ($tblPrepareList as $tblPrepare) {
                    if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                        if (($tblGradeList = Prepare::useService()->getPrepareGradesByPrepare($tblPrepare, $tblTestType))) {
                            $countBehaviorGrades += count($tblGradeList);
                        }
                    }
                }

                if ($countBehaviorGrades > 0) {
                    $message = new \SPHERE\Common\Frontend\Message\Repository\Warning(
                        'Es wurden bereits ' . $countBehaviorGrades . ' Kopfnoten festgelegt',
                        new Exclamation()
                    );
                }
            }

            $tblYear = $tblGenerateCertificate->getServiceTblYear();
            $Form = $this->formEditGenerate($tblYear ? $tblYear : null, $tblGenerateCertificate->isLocked())
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Zeugnisgenerierung',
                                    array(
                                        $tblGenerateCertificate->getDate(),
                                        $tblGenerateCertificate->getName()
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                ($message !== '' ? $message : '')
                                . new Well(Generate::useService()->updateGenerateCertificate($Form,
                                    $tblGenerateCertificate, $Data))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Zeugnisgenerierung nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblYear|null $tblYear
     * @param boolean $IsLocked
     * @return Form
     */
    private function formEditGenerate(TblYear $tblYear = null, $IsLocked)
    {


        $tblAppointedDateTaskListByYear = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'),
            $tblYear ? $tblYear : null
        );
        $tblBehaviorTaskListByYear = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'),
            $tblYear ? $tblYear : null
        );

        $selectBoxAppointedDateTask = new SelectBox('Data[AppointedDateTask]', 'Stichtagsnotenauftrag',
            array('{{ Date }} {{ Name }}' => $tblAppointedDateTaskListByYear));
        $selectBoxBehaviorTask = new SelectBox('Data[BehaviorTask]', 'Kopfnotenauftrag',
            array('{{ Date }} {{ Name }}' => $tblBehaviorTaskListByYear));
        if ($IsLocked) {
            $selectBoxAppointedDateTask->setDisabled();
            $selectBoxBehaviorTask->setDisabled();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(
                        'Zeugnis',
                        array(
                            (new TextField('Data[Name]', '', 'Name des Zeugnisauftrags'))->setRequired(),
                            (new DatePicker('Data[Date]', '', 'Zeugnisdatum', new Calendar()))->setRequired(),
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Notenaufträge',
                        array(
                            $selectBoxAppointedDateTask,
                            $selectBoxBehaviorTask
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Unterzeichner',
                        array(
                            new CheckBox('Data[IsTeacherAvailable]',
                                'Name des Klassenlehrers und Name des/der Schulleiters/in (falls vorhanden) auf dem Zeugnis anzeigen',
                                1
                            ),
                            new TextField('Data[HeadmasterName]', '', 'Name des/der Schulleiters/in'),
                            new Panel(
                                new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                array(
                                    (new RadioBox('Data[GenderHeadmaster]', 'Männlich',
                                        ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                                            ? $tblCommonGender->getId() : 0)),
                                    (new RadioBox('Data[GenderHeadmaster]', 'Weiblich',
                                        ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                                            ? $tblCommonGender->getId() : 0))
                                ),
                                Panel::PANEL_TYPE_DEFAULT
                            )
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
            ))
        )));
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyGenerate($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Zeugnisgenerierung', 'Löschen');

        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($Id))) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Generate', new ChevronLeft()
            ));

            if (!$Confirm) {
                $divisionList = array();
                $divisionList[0] = 'Zeungisdatum: ' . $tblGenerateCertificate->getDate();
                $divisionList[1] = 'Typ: ' . (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                        ? $tblCertificateType->getName() : '');
                $divisionList[2] = 'Name: ' . $tblGenerateCertificate->getName();

                if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                    $divisionList[3] = '&nbsp;';
                    $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
                    foreach ($tblPrepareList as $tblPrepare) {
                        if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                            $hasBehaviorGrades = false;
                            $countBehaviorGrades = 0;
                            $hasPrepareInformation = false;
                            $countPrepareInformation = 0;
                            if (($tblGradeList = Prepare::useService()->getPrepareGradesByPrepare($tblPrepare, $tblTestType))) {
                                $hasBehaviorGrades = true;
                                $countBehaviorGrades = count($tblGradeList);
                            }
                            if (($tblPrepareInformationList = Prepare::useService()->getPrepareInformationAllByPrepare($tblPrepare))) {
                                $hasPrepareInformation = true;
                                $countPrepareInformation = count($tblPrepareInformationList);
                            }
                            if ($hasBehaviorGrades && $hasPrepareInformation) {
                                $message = 'Es wurden bereits ' . $countBehaviorGrades . ' Kopfnoten festgelegt und '
                                    . $countPrepareInformation . ' Sonstige Informationen gespeichert ' . new Exclamation();
                            } elseif ($hasBehaviorGrades) {
                                $message = 'Es wurden bereits ' . $countBehaviorGrades . ' Kopfnoten festgelegt '
                                    . new Exclamation();
                            } elseif ($hasPrepareInformation) {
                                $message = 'Es wurden bereits ' . $countPrepareInformation . ' Sonstige Informationen gespeichert ' . new Exclamation();
                            } else {
                                $message = '';
                            }

                            $divisionList[$tblDivision->getDisplayName()] = 'Klasse: ' . $tblDivision->getDisplayName()
                                . ($message !== '' ? new \SPHERE\Common\Frontend\Text\Repository\Danger('&nbsp;&nbsp;&nbsp;' . $message) : '');
                        }
                    }
                }

                ksort($divisionList);

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            new Question() . ' Diese Zeugnisgenerierung wirklich löschen?',
                            $divisionList,
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/Certificate/Generate/Destroy', new Ok(),
                                array(
                                    'Id' => $Id,
                                    'Confirm' => true
                                )
                            )
                            . new Standard(
                                'Nein', '/Education/Certificate/Generate', new Disable()
                            )
                        ),
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Generate::useService()->destroyGenerateCertificate($tblGenerateCertificate)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(
                                    new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zeugnisgenerierung wurde gelöscht')
                                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS)
                                : new Danger(new Ban() . ' Die Zeugnisgenerierung konnte nicht gelöscht werden')
                                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Zeugnisgenerierung konnte nicht gefunden werden'),
                        new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $GenerateCertificateId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendGenerateSetting($GenerateCertificateId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnis generieren', 'Zusätzliche Einstellungen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));

        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($GenerateCertificateId))) {

            $tblPersonList = false;
            if (($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))) {
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            }

            if (($tblGenerateCertificateSettingList = Generate::useService()->getGenerateCertificateSettingAllByGenerateCertificate($tblGenerateCertificate))) {
                $global = $this->getGlobal();
                foreach ($tblGenerateCertificateSettingList as $tblGenerateCertificateSetting) {
                    $global->POST['Data'][$tblGenerateCertificateSetting->getField()]
                        = $tblGenerateCertificateSetting->getValue() ? $tblGenerateCertificateSetting->getValue() : 0;
                }
                $global->savePost();
            }

            $form = new Form(array(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(array(
                            new SelectBox('Data[Leader]', 'Vorsitzende(r)', array('{{ LastFirstName }}' => $tblPersonList))
                        ), 4),
                        new FormColumn(array(
                            new SelectBox('Data[FirstMember]', 'Mitglied', array('{{ LastFirstName }}' => $tblPersonList))
                        ), 4),
                        new FormColumn(array(
                            new SelectBox('Data[SecondMember]', 'Mitglied', array('{{ LastFirstName }}' => $tblPersonList))
                        ), 4),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Title('Prüfungsausschuss')
                )
            ));
            $form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblGenerateCertificate->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ',
                                    $tblGenerateCertificate->getServiceTblCertificateType()
                                        ? $tblGenerateCertificate->getServiceTblCertificateType()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Stichtagsnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblAppointedDateTask()
                                        ? $tblGenerateCertificate->getServiceTblAppointedDateTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Kopfnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblBehaviorTask()
                                        ? $tblGenerateCertificate->getServiceTblBehaviorTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Well(Generate::useService()->updateAbiturSettings($form, $tblGenerateCertificate, $Data))
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Zeugniserstellung nicht gefunden', new Exclamation());
        }
    }
}