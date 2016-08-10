<?php
namespace SPHERE\Application\Education\Certificate\Setting;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title as FormTitle;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Frontend\Icon\Repository\Star;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int   $Certificate
     * @param array $Grade
     * @param array $Subject
     *
     * @return Stage
     */
    public function frontendCertificateSetting($Certificate = 0, $Grade = array(), $Subject = array())
    {

        $Stage = new Stage('Zeugnisvorlage', 'Einstellungen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Setting', new ChevronLeft()));

        if (( $tblCertificate = Generator::useService()->getCertificateById($Certificate) )) {

//            Debugger::screenDump( Generator::useService()->getCertificateGradeAll( $tblCertificate ) );
//            Debugger::screenDump( Generator::useService()->getCertificateSubjectAll( $tblCertificate ) );

            // Kopf-Noten-Definition
            $tblTestTypeBehavior = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
            $tblGradeTypeBehavior = Gradebook::useService()->getGradeTypeAllByTestType($tblTestTypeBehavior);

            // Fach-Noten-Definition
            $tblSubjectAll = Subject::useService()->getSubjectAll();

            $LaneLength = floor(( count($tblSubjectAll) + 1 ) / 3);
            $SubjectLaneLeft = array();
            $SubjectLaneRight = array();
            for ($Run = 1; $Run < $LaneLength; $Run++) {
                array_push($SubjectLaneLeft,
                    $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run,
                        ( $Run == 1 ? 'Linke Zeugnis-Spalte' : '' ))
                );
                array_push($SubjectLaneRight,
                    $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run,
                        ( $Run == 1 ? 'Rechte Zeugnis-Spalte' : '' ))
                );
            }

            $Stage->setContent(
                Generator::useService()->createCertificateSetting(
                    new Form(array(
                        new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(array(
                                    $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 1, 1, 'Betragen',
                                        'Linke Zeugnis-Spalte'),
                                    $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 1, 2, 'Fleiß')
                                ), 6),
                                new FormColumn(array(
                                    $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 2, 1, 'Mitarbeit',
                                        'Rechte Zeugnis-Spalte'),
                                    $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 2, 2, 'Ordnung')
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

        } else {
            // TODO Error
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
     *
     * @return array
     */
    private function getSubject(
        TblCertificate $tblCertificate,
        $tblSubjectAll,
        $LaneIndex,
        $LaneRanking,
        $LaneTitle = '',
        $FieldName = 'Subject'
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
                $Global->POST[$FieldName][$LaneIndex][$LaneRanking]['Liberation'] =
                    ( $tblCertificateSubject->getServiceTblStudentLiberationCategory()
                        ? $tblCertificateSubject->getServiceTblStudentLiberationCategory()->getId()
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

        $tblStudentLiberationCategoryAll = Student::useService()->getStudentLiberationCategoryAll();

        return new Panel($LaneTitle, array(
            new SelectBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][Subject]', 'Fach',
                array('{{ Acronym }} - {{ Name }}' => $tblSubjectAll)
            ),
            new CheckBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][IsEssential]',
                'Muss immer ausgewiesen werden', 1),
            new SelectBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][Liberation]', 'Befreihung',
                array('{{ Name }}' => $tblStudentLiberationCategoryAll)
            ),
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
     * @return array
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

        $Stage = new Stage('Zeugnisvorlage', 'Einstellungen');
        $Stage->addButton(new Backward());

        // Find Certificate-Templates
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getAcronym() == 'DEMO'){
            $tblCertificateAll = Generator::useService()->getCertificateAll();
        } else {
            $tblCertificateAll = Generator::useService()->getCertificateAllByConsumer();
        }
        if ($tblConsumer) {
            $tblCertificateConsumer = Generator::useService()->getCertificateAllByConsumer($tblConsumer);
            if ($tblCertificateConsumer) {
                $tblCertificateAll = array_merge($tblCertificateConsumer, $tblCertificateAll);
            }

            $TemplateTable = array();
            array_walk($tblCertificateAll,
                function (TblCertificate $tblCertificate) use (&$TemplateTable) {

                    $TemplateTable[] = array_merge($tblCertificate->__toArray(), array(
                            'Typ'    => '<div class="text-center">'.( $tblCertificate->getServiceTblConsumer()
                                    ? new Small(new Muted($tblCertificate->getServiceTblConsumer()->getAcronym())).'<br/>'.new Star()
                                    : new Document().'<br/>'.new Small(new Muted('Standard'))
                                ).'</div>',
                            'Option' => new Standard(
                                'Weiter', '/Education/Certificate/Setting/Configuration', new ChevronRight(),
                                array(
                                    'Certificate' => $tblCertificate->getId()
                                ), 'Auswählen')
                        )
                    );
                });

            $Content = new TableData($TemplateTable, null, array(
                'Typ'         => 'Typ',
                'Name'        => 'Name',
                'Description' => 'Beschreibung',
                'Option'      => 'Option'
            ), array(
                'order'      => array(array(0, 'asc'), array(1, 'asc'), array(2, 'asc')),
                'columnDefs' => array(
                    array('width' => '1%', 'targets' => 0),
                    array('width' => '1%', 'targets' => 3),
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
}
