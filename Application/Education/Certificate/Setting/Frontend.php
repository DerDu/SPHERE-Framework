<?php
namespace SPHERE\Application\Education\Certificate\Setting;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title as FormTitle;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Star;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success as SuccessLink;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $Certificate
     * @param array $Grade
     * @param array $Subject
     * @param array $Data
     *
     * @return Stage|string
     */
    public function frontendCertificateSetting($Certificate = 0, $Grade = array(), $Subject = array(), $Data = null)
    {

        $Stage = new Stage('Einstellungen', 'Vorlage bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Setting/Template', new ChevronLeft()));

        if (( $tblCertificate = Generator::useService()->getCertificateById($Certificate) )) {
            // Spezial Fall Abiturzeugnis
            if ($tblCertificate->getCertificate() == 'GymAbitur') {
                if (($tblCertificateReferenceForLanguagesList = Generator::useService()->getCertificateReferenceForLanguagesAllByCertificate($tblCertificate))) {
                    $global = $this->getGlobal();
                    foreach ($tblCertificateReferenceForLanguagesList as $tblCertificateReferenceForLanguages) {
                        $global->POST['Data'][$tblCertificateReferenceForLanguages->getLanguageRanking()]['ToLevel10'] = $tblCertificateReferenceForLanguages->getToLevel10();
                        $global->POST['Data'][$tblCertificateReferenceForLanguages->getLanguageRanking()]['AfterBasicCourse'] = $tblCertificateReferenceForLanguages->getAfterBasicCourse();
                        $global->POST['Data'][$tblCertificateReferenceForLanguages->getLanguageRanking()]['AfterAdvancedCourse'] = $tblCertificateReferenceForLanguages->getAfterAdvancedCourse();
                    }
                    $global->savePost();
                }

                $formRows = array();
                for ($i = 1; $i < 4; $i++) {
                    $formRows[] = new FormRow(new FormColumn(
                        new Panel(
                            $i . '. Fremdsprache',
                            array(
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(
                                        new TextField('Data[' . $i . '][ToLevel10]', '', 'Bis Klasse 10')
                                    , 4),
                                    new LayoutColumn(
                                        new TextField('Data[' . $i . '][AfterBasicCourse]', '', 'Nach Grundkurs')
                                    , 4),
                                    new LayoutColumn(
                                        new TextField('Data[' . $i . '][AfterAdvancedCourse]', '', 'Nach Leistungskurs')
                                    , 4),
                                ))))
                            ),
                            Panel::PANEL_TYPE_INFO
                        )
                    ));
                }

                $form = new Form(
                    new FormGroup($formRows, new FormTitle('Gemeinsamer Europäischer Referenzrahmen für Sprachen')),
                    new Primary('Speichern')
                );

                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()),
                        Panel::PANEL_TYPE_INFO)
                    . new Well(Generator::useService()->updateCertificateReferenceForLanguages($form, $tblCertificate, $Data))
                );

            } elseif(preg_match('!Berufsfachschule!', $tblCertificate->getName())) {

                // Fach-Noten-Definition
                $tblSubjectAll = Subject::useService()->getSubjectAll();
                // Erstmal bis 20
                $haveToAcrossSubject = 4; // (4 * 2) = 8 Fächer (3 Zusatzplatzhalter füü z.B. Religion auf der rechten Seite)
                $haveToBaseSubject = 12; // (8 * 2) = 16 LF (14 Ist Standard, 15 passen auf das Zeugnis)
                $chosenSubject = 14; // (2 * 2) = 4 Wahlfächer (3 Wahlfächer passen auf das Zeugnis)
                $praktSubject = 15; // (2 * 2) = 4 Berufspraktische Ausbildung (1 Fach)

                // Berufsübergreifende Pflichtfächer
                $SubjectLaneAcrossLeft = array();
                $SubjectLaneAcrossRight = array();
                for ($Run = 1; $Run <= $haveToAcrossSubject; $Run++) {
                    array_push($SubjectLaneAcrossLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run,
                            ($Run == 1 ? 'Linke Zeugnis-Spalte' : ''))
                    );
                    array_push($SubjectLaneAcrossRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run,
                            ($Run == 1 ? 'Rechte Zeugnis-Spalte' : ''))
                    );
                }
                // Berufsbezogene Pflichtfächer
                $SubjectLaneBaseLeft = array();
                $SubjectLaneBaseRight = array();
                $countLF = 1;
                for ($Run = ($haveToAcrossSubject + 1); $Run <= $haveToBaseSubject; $Run++) {
                    array_push($SubjectLaneBaseLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', 'LF'.$countLF++)
                    );
                    array_push($SubjectLaneBaseRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', 'LF'.$countLF++)
                    );
                }
                // Wahlfächer
                $SubjectLaneChosenLeft = array();
                $SubjectLaneChosenRight = array();
                for ($Run = ($haveToBaseSubject + 1); $Run <= $chosenSubject; $Run++) {
                    array_push($SubjectLaneChosenLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run)
                    );
                    array_push($SubjectLaneChosenRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run)
                    );
                }
                // Wahlfächer
                $SubjectPrakt = array();
                for ($Run = ($chosenSubject + 1); $Run <= $praktSubject; $Run++) {
                    array_push($SubjectPrakt,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run)
                    );
                }


                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()),
                        Panel::PANEL_TYPE_INFO)
                    . Generator::useService()->createCertificateSetting(
                        new Form(array(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneAcrossLeft, 6),
                                    new FormColumn($SubjectLaneAcrossRight, 6),
                                )),
                            ), new FormTitle('Pflicht Berufsübergreifender Bereich')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneBaseLeft, 6),
                                    new FormColumn($SubjectLaneBaseRight, 6),
                                )),
                            ), new FormTitle('Pflicht Berufsbezogener Bereich (LF Sortiert auf dem Zeugnis untereinander)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneChosenLeft, 6),
                                    new FormColumn($SubjectLaneChosenRight, 6),
                                )),
                            ), new FormTitle('Wahlpflichtbereich (Reihenfolge Links -> Rechts)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectPrakt, 6)
                                )),
                            ), new FormTitle('Berufspraktische Ausbildung')),
                        ), new Primary('Speichern')), $tblCertificate, $Grade, $Subject)
                );
            } else {

                // Kopf-Noten-Definition
                $tblTestTypeBehavior = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
                $tblGradeTypeBehavior = Gradebook::useService()->getGradeTypeAllByTestType($tblTestTypeBehavior);

                // Fach-Noten-Definition
                $tblSubjectAll = Subject::useService()->getSubjectAll();

                if ($tblCertificate->isGradeInformation()) {
                    // bei Noteninformationen stehen alle Fächer auf der linken Seite
                    $LaneLength = 25;
                } else {
                    if ($tblSubjectAll) {
                        $LaneLength = ceil(count($tblSubjectAll) / 2);
                    } else {
                        $LaneLength = 2;
                    }
                }

                $SubjectLaneLeft = array();
                $SubjectLaneRight = array();
                for ($Run = 1; $Run <= $LaneLength; $Run++) {
                    array_push($SubjectLaneLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run,
                            ($Run == 1 ? 'Linke Zeugnis-Spalte' : ''))
                    );
                    array_push($SubjectLaneRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run,
                            ($Run == 1 ? 'Rechte Zeugnis-Spalte' : ''))
                    );
                }

                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()),
                        Panel::PANEL_TYPE_INFO)
                    . Generator::useService()->createCertificateSetting(
                        new Form(array(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn(array(
                                        $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 1, 1, 'Kopfnote',
                                            'Linke Zeugnis-Spalte'),
                                        $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 1, 2, 'Kopfnote')
                                    ), 6),
                                    new FormColumn(array(
                                        $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 2, 1, 'Kopfnote',
                                            'Rechte Zeugnis-Spalte'),
                                        $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 2, 2, 'Kopfnote')
                                    ), 6),
                                ))
                            ), new FormTitle('Kopfnoten')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneLeft, 6),
                                    new FormColumn($SubjectLaneRight, 6),
                                )),
                            ), new FormTitle('Fachnoten')),
                        ), new Primary('Speichern')), $tblCertificate, $Grade, $Subject)
                );
            }

        } else {
            return $Stage
                . new Danger('Die Zeugnisvorlage wurde nicht gefunden', new Exclamation())
                . new Redirect('/Education/Certificate/Setting/Template', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject[]   $tblSubjectAll
     * @param int            $LaneIndex   [1..n]
     * @param int            $LaneRanking [1..n]
     * @param string         $LaneTitle
     * @param string         $FieldName
     * @param string         $PreFach
     *
     * @return Panel
     */
    private function getSubject(
        TblCertificate $tblCertificate,
        $tblSubjectAll,
        $LaneIndex,
        $LaneRanking,
        $LaneTitle = '',
        $FieldName = 'Subject',
        $PreFach = ''
    ) {

        $Global = $this->getGlobal();
        if (!isset( $Global->POST[$FieldName][$LaneIndex][$LaneRanking] )) {
            if (( $tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                $tblCertificate, $LaneIndex, $LaneRanking
            ) )
            ) {
                $Global->POST[$FieldName][$LaneIndex][$LaneRanking]['Subject'] =
                    ( $tblCertificateSubject->getServiceTblSubject()
                        ? $tblCertificateSubject->getServiceTblSubject()->getId()
                        : 0
                    );
                $Global->POST[$FieldName][$LaneIndex][$LaneRanking]['IsEssential'] =
                    ( $tblCertificateSubject->isEssential()
                        ? 1
                        : 0
                    );
            };
            $Global->savePost();
        }

        return new Panel($LaneTitle, array(
            new SelectBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][Subject]', ($PreFach? $PreFach.' ' : '').'Fach',
                array('{{ Acronym }} - {{ Name }}' => $tblSubjectAll)
            ),
            new CheckBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][IsEssential]',
                'Muss immer ausgewiesen werden', 1),
        ));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblGradeType[] $tblGradeTypeAll
     * @param int            $LaneIndex   [1..n]
     * @param int            $LaneRanking [1..n]
     * @param string         $LabelName
     * @param string         $LaneTitle
     * @param string         $FieldName
     *
     * @return Panel
     */
    private function getGrade(
        TblCertificate $tblCertificate,
        $tblGradeTypeAll,
        $LaneIndex,
        $LaneRanking,
        $LabelName = 'Betragen',
        $LaneTitle = '',
        $FieldName = 'Grade'
    ) {

        $Global = $this->getGlobal();
        if (!isset( $Global->POST[$FieldName][$LaneIndex][$LaneRanking] )) {
            if (( $tblCertificateGrade = Generator::useService()->getCertificateGradeByIndex(
                $tblCertificate, $LaneIndex, $LaneRanking
            ) )
            ) {
                $Global->POST[$FieldName][$LaneIndex][$LaneRanking]['GradeType'] = $tblCertificateGrade->getServiceTblGradeType()->getId();
            };
            $Global->savePost();
        }

        return new Panel($LaneTitle, array(
            new SelectBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][GradeType]', $LabelName,
                array('{{ Code }} - {{ Name }}' => $tblGradeTypeAll)
            )
        ));
    }

    /**
     * @return Stage
     */
    public function frontendSelectCertificate()
    {

        $Stage = new Stage('Einstellungen', 'Zeugnisvorlage auswählen');
        $Stage = self::setSettingMenue($Stage, 'Template');

        // Find Certificate-Templates
        $tblConsumer = Consumer::useService()->getConsumerBySession();
//        if ($tblConsumer && $tblConsumer->getAcronym() == 'DEMO') {
//            $tblTemplateAll = Generator::useService()->getTemplateAll();
//        } else {
        // holt alle ohne Mandant
        $tblTemplateAll = Generator::useService()->getTemplateAllByConsumer(null);
//        }
        if ($tblConsumer) {
            if (!$tblTemplateAll){
                $tblTemplateAll = array();
            }

            $tblTemplateConsumer = Generator::useService()->getTemplateAllByConsumer($tblConsumer);
            if ($tblTemplateConsumer) {
                $tblTemplateAll = array_merge($tblTemplateConsumer, $tblTemplateAll);
            }

            $TemplateTable = array();
            array_walk($tblTemplateAll,
                function (TblCertificate $tblCertificate) use (&$TemplateTable) {

                    $TemplateTable[] = array_merge($tblCertificate->__toArray(), array(
                            'Typ'    => '<div class="text-center">'.( $tblCertificate->getServiceTblConsumer()
                                    ? new Small(new Muted($tblCertificate->getServiceTblConsumer()->getAcronym())).'<br/>'.new Star()
                                    : new Document().'<br/>'.new Small(new Muted('Standard'))
                                ).'</div>',
                            'Category' => $tblCertificate->getDisplayCategory(),
                            'Option' => new Standard(
                                '', '/Education/Certificate/Setting/Configuration', new Select(),
                                array(
                                    'Certificate' => $tblCertificate->getId()
                                ), 'Zeugnisvorlage auswählen')
                        )
                    );
                });

            $Content = new TableData($TemplateTable, null, array(
                'Typ'         => 'Typ',
                'Category'         => 'Kategorie',
                'Name'        => 'Name',
                'Description' => 'Beschreibung',
                'Option'      => ''
            ), array(
                'order'      => array(array(0, 'asc'), array(1, 'asc'), array(2, 'asc'), array(3, 'asc')),
                'columnDefs' => array(
                    array('width' => '1%', 'targets' => 0),
                    array('width' => '1%', 'targets' => 4),
                )
            ));

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn($Content)
                        ), new Title('Verfügbare Vorlagen')
                    )
                )
            );

        } else {
            // TODO Error
        }

        return $Stage;
    }

    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendApproval($Data = null)
    {

        $Stage = new Stage('Einsellungen', 'Automatische Freigabe');
        $Stage = self::setSettingMenue($Stage, 'Approval');

        $certificateTypeList = array();
        if (($tblCertificateTypeAll = Generator::useService()->getCertificateTypeAll())) {
            $Global = $this->getGlobal();
            foreach ($tblCertificateTypeAll as $tblCertificateType) {
                $Global->POST['Data'][$tblCertificateType->getId()] = $tblCertificateType->isAutomaticallyApproved() ? 1 : 0;
            }
            $Global->savePost();

            foreach ($tblCertificateTypeAll as $tblCertificateType) {
                if ($tblCertificateType->getIdentifier() !== 'GRADE_INFORMATION') {
                    $certificateTypeList[] = new CheckBox('Data[' . $tblCertificateType->getId() . ']',
                        $tblCertificateType->getName(), 1);
                }
            }
        }

        $form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Zeugnistypen automatisch freigeben',
                            $certificateTypeList,
                            Panel::PANEL_TYPE_PRIMARY
                        )
                    ),
                    new FormColumn(new HiddenField('Data[IsSubmit]'))
                ))
            )
        );
        $form->appendFormButton(new Primary('Speichern', new Save()));

        $Stage->setContent(new Well(Generator::useService()->updateCertificateType($form, $Data)));

        return $Stage;
    }

    public function frontendImplement()
    {

        $Stage = new Stage('Einstellungen', 'Zeugnisvorlagen installieren');
        $Stage = self::setSettingMenue($Stage, '');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(new Title('Auswahl'))
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $this->getCertificateInstallButton(TblCertificate::CERTIFICATE_TYPE_PRIMARY, 'Zeugnisse Grundschule', 'GsJa')
                            .$this->getCertificateInstallButton(TblCertificate::CERTIFICATE_TYPE_SECONDARY, 'Zeugnisse Oberschule', 'MsJ')
                            .$this->getCertificateInstallButton(TblCertificate::CERTIFICATE_TYPE_GYM, 'Zeugnisse Gymnasium', 'GymJ')
                            .$this->getCertificateInstallButton(TblCertificate::CERTIFICATE_TYPE_BERUFSFACHSCHULE, 'Zeugnisse Berufsfachschule', 'BfsHj')
                        )
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param string $Type
     * @param string $Name
     * @param string $CertificateClass
     *
     * @return Standard|SuccessLink
     */
    private function getCertificateInstallButton($Type, $Name, $CertificateClass)
    {

        $Button = new Standard($Name, '/Education/Certificate/Setting/ImplementCertificate',
            new Save(), array('Type' => $Type));
        // Installation schon vorhanden? Test an einem Zeugnis
        if(Generator::useService()->getCertificateByCertificateClassName($CertificateClass)){
            $Button = new SuccessLink($Name,'/Education/Certificate/Setting/ImplementCertificate',
                new Ok(), array('Type' => $Type),
                'Erneut Installieren (eventuell fehlende/neue ergänzen)');
        }
        return $Button;
    }

    public function frontendImplementCertificate($Type = '')
    {

        return Generator::useService()->insertCertificate($Type);
    }

    public function frontendDashboard()
    {

        $Stage = new Stage('Einstellungen', 'Auswählen');
        $Stage = self::setSettingMenue($Stage, '');

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param string $Route
     *
     * @return Stage
     */
    private static function setSettingMenue(Stage $Stage, $Route = 'Template')
    {

        $text = 'Zeungisvorlagen';
        $Stage->addButton(new Standard($Route == 'Template' ? new Edit() . ' ' . $text : $text,
            '/Education/Certificate/Setting/Template', null, null,
            'Den Zeugnisvorlagen Fächer zuordnen'));

        $text = 'Automatische Freigabe';
        $Stage->addButton(new Standard($Route == 'Approval' ? new Edit() . ' ' . $text : $text,
            '/Education/Certificate/Setting/Approval', null, null,
            'Automatische Freigaben setzen'));

        $text = 'Zeugnisvorlagen installieren';
        $Stage->addButton(new Standard($Route == 'Implement' ? new Edit() . ' ' . $text : $text,
            '/Education/Certificate/Setting/Implement', null, null,
            'Standardzeugnisse hinzufügen'));

        return $Stage;
    }
}
