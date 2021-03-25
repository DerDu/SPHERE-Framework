<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\MassReplace\StudentFilter;
use SPHERE\Application\Api\People\Meta\TechnicalSchool\MassReplaceTechnicalSchool;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Service\Entity\TblCategory;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;

/**
 * Class FrontendStudentTechnicalSchool
 * 
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentTechnicalSchool extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Berufsbildende Schulen';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentTechnicalSchoolContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
            ) {
                $technicalCourse = ($tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())
                    ? $tblTechnicalCourse->getDisplayName(($tblCommonGender = $tblPerson->getGender()) ? $tblCommonGender : null) : '';
                $schoolDiploma = ($tblSchoolDiploma = $tblStudentTechnicalSchool->getServiceTblSchoolDiploma())
                    ? $tblSchoolDiploma->getName() : '';
                $schoolType = ($tblSchoolType = $tblStudentTechnicalSchool->getServiceTblSchoolType())
                    ? $tblSchoolType->getName() : '';
                $technicalDiploma = ($tblTechnicalDiploma = $tblStudentTechnicalSchool->getServiceTblTechnicalDiploma())
                    ? $tblTechnicalDiploma->getName() : '';
                $technicalType = ($tblTechnicalType = $tblStudentTechnicalSchool->getServiceTblTechnicalType())
                    ? $tblTechnicalType->getName() : '';

                $praxisLessons = $tblStudentTechnicalSchool->getPraxisLessons();
                $durationOfTraining = $tblStudentTechnicalSchool->getDurationOfTraining();
                $remark = $tblStudentTechnicalSchool->getRemark();
                $studentTenseOfLessons = ($tblStudentTenseOfLessons = $tblStudentTechnicalSchool->getTblStudentTenseOfLesson())
                    ? $tblStudentTenseOfLessons->getName() : '';
                $studentTrainingStatus = ($tblStudentTrainingStatus = $tblStudentTechnicalSchool->getTblStudentTrainingStatus())
                    ? $tblStudentTrainingStatus->getName() : '';
                $yearOfSchoolDiploma = $tblStudentTechnicalSchool->getYearOfSchoolDiploma();
                $yearOfTechnicalDiploma = $tblStudentTechnicalSchool->getYearOfTechnicalDiploma();
                $technicalSubjectArea = ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                    ? $tblTechnicalSubjectArea->getAcronym() . ' - ' . $tblTechnicalSubjectArea->getName() : '';
                $hasFinancialAid = $tblStudentTechnicalSchool->getHasFinancialAid() ? 'Ja' : 'Nein';
                $financialAidApplicationYear = $tblStudentTechnicalSchool->getFinancialAidApplicationYear();
                $financialAidBureau = $tblStudentTechnicalSchool->getFinancialAidBureau();
            } else {
                $technicalCourse = '';
                $schoolDiploma = '';
                $schoolType = '';
                $technicalDiploma = '';
                $technicalType = '';

                $praxisLessons = '';
                $durationOfTraining = '';
                $remark = '';
                $studentTenseOfLessons = '';
                $studentTrainingStatus = '';
                $yearOfSchoolDiploma = '';
                $yearOfTechnicalDiploma = '';
                $technicalSubjectArea = '';
                $hasFinancialAid = '';
                $financialAidApplicationYear = '';
                $financialAidBureau = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Schüler - Aufname (für Kamenz-Statistik)',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Allgemeinbildender Abschluss', 3),
                                    self::getLayoutColumnValue($schoolDiploma, 3),
                                    self::getLayoutColumnLabel('An der allgemeinbildenden Schulart', 3),
                                    self::getLayoutColumnValue($schoolType, 3),
                                    self::getLayoutColumnLabel('Abschlussjahr (allgemeinbildend)', 3),
                                    self::getLayoutColumnValue($yearOfSchoolDiploma, 3),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Berufsbildender Abschluss', 3),
                                    self::getLayoutColumnValue($technicalDiploma, 3),
                                    self::getLayoutColumnLabel('An der berufsbildenden Schulart', 3),
                                    self::getLayoutColumnValue($technicalType, 3),
                                    self::getLayoutColumnLabel('Abschlussjahr (berufsbildenden)', 3),
                                    self::getLayoutColumnValue($yearOfTechnicalDiploma, 3),
                                )),
                            )))
                        ),
                    )),
                )),
                new LayoutRow(array(
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Allgemeines',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Bildungsgang / Berufsbezeichnung / Ausbildung', 5),
                                    self::getLayoutColumnValue($technicalCourse, 7),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Fachrichtung', 5),
                                    self::getLayoutColumnValue($technicalSubjectArea, 7),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Zeitform des Unterrichts', 3),
                                    self::getLayoutColumnValue($studentTenseOfLessons, 3),
                                    self::getLayoutColumnLabel('Ausbildungsstatus', 3),
                                    self::getLayoutColumnValue($studentTrainingStatus, 3),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Planmäßige Ausbildungsdauer', 3),
                                    self::getLayoutColumnValue($durationOfTraining, 3),
                                    self::getLayoutColumnLabel('Geleistete Praxisstunden', 3),
                                    self::getLayoutColumnValue($praxisLessons, 3),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Bemerkungen', 3),
                                    self::getLayoutColumnValue($remark, 9),
                                )),
                            )))
                        ),
                    )),
                )),
                new LayoutRow(array(
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Bafög',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Bafög'),
                                    self::getLayoutColumnValue($hasFinancialAid),
                                    self::getLayoutColumnLabel('Beantragungsjahr'),
                                    self::getLayoutColumnValue($financialAidApplicationYear),
                                    self::getLayoutColumnLabel('Amt'),
                                    self::getLayoutColumnValue($financialAidBureau),
                                )),
                            )))
                        ),
                    )),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentTechnicalSchoolContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new ClipBoard()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentTechnicalSchoolContent($PersonId = null)
    {
        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
            ) {

                $Global->POST['Meta']['TechnicalSchool']['serviceTblTechnicalCourse'] = ($tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())
                    ? $tblTechnicalCourse->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['serviceTblSchoolDiploma'] = ($tblSchoolDiploma = $tblStudentTechnicalSchool->getServiceTblSchoolDiploma())
                    ? $tblSchoolDiploma->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['serviceTblSchoolType'] = ($tblSchoolType = $tblStudentTechnicalSchool->getServiceTblSchoolType())
                    ? $tblSchoolType->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['serviceTblTechnicalDiploma'] = ($tblTechnicalDiploma = $tblStudentTechnicalSchool->getServiceTblTechnicalDiploma())
                    ? $tblTechnicalDiploma->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['serviceTblTechnicalType'] = ($tblTechnicalType = $tblStudentTechnicalSchool->getServiceTblTechnicalType())
                    ? $tblTechnicalType->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['PraxisLessons'] = $tblStudentTechnicalSchool->getPraxisLessons();
                $Global->POST['Meta']['TechnicalSchool']['DurationOfTraining'] = $tblStudentTechnicalSchool->getDurationOfTraining();
                $Global->POST['Meta']['TechnicalSchool']['Remark'] = $tblStudentTechnicalSchool->getRemark();
                $Global->POST['Meta']['TechnicalSchool']['tblStudentTenseOfLesson'] = ($tblStudentTenseOfLessons = $tblStudentTechnicalSchool->getTblStudentTenseOfLesson())
                    ? $tblStudentTenseOfLessons->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['tblStudentTrainingStatus'] = ($tblStudentTrainingStatus = $tblStudentTechnicalSchool->getTblStudentTrainingStatus())
                    ? $tblStudentTrainingStatus->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['YearOfSchoolDiploma'] = $tblStudentTechnicalSchool->getYearOfSchoolDiploma();
                $Global->POST['Meta']['TechnicalSchool']['YearOfTechnicalDiploma'] = $tblStudentTechnicalSchool->getYearOfTechnicalDiploma();
                $Global->POST['Meta']['TechnicalSchool']['serviceTblTechnicalSubjectArea'] = ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                    ? $tblTechnicalSubjectArea->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['HasFinancialAid'] = $tblStudentTechnicalSchool->getHasFinancialAid() ? 1 : 0;
                $Global->POST['Meta']['TechnicalSchool']['FinancialAidApplicationYear'] = $tblStudentTechnicalSchool->getFinancialAidApplicationYear();
                $Global->POST['Meta']['TechnicalSchool']['FinancialAidBureau'] = $tblStudentTechnicalSchool->getFinancialAidBureau();

                $Global->savePost();
            }
        }

        return $this->getEditStudentTechnicalSchoolTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentTechnicalSchoolForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentTechnicalSchoolTitle(TblPerson $tblPerson = null)
    {
        return new Title(new ClipBoard() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentTechnicalSchoolForm(TblPerson $tblPerson = null)
    {
        FrontendStudent::setYearAndDivisionForMassReplace($tblPerson, $Year, $Division);

        $tblTechnicalCourseAll = Course::useService()->getTechnicalCourseAll();
        $tblTechnicalSubjectAreaAll = Course::useService()->getTechnicalSubjectAreaAll();
        $tblSchoolDiplomaAll = Course::useService()->getSchoolDiplomaAll();
        $tblTechnicalDiplomaAll = Course::useService()->getTechnicalDiplomaAll();
        $tblStudentTenseOfLessonAll = Student::useService()->getStudentTenseOfLessonAll();
        $tblStudentTrainingStatusAll = Student::useService()->getStudentTrainingStatusAll();

        $tblTypeTechnicalAll = Type::useService()->getTypeAllByCategory(Type::useService()->getCategoryByIdentifier(TblCategory::TECHNICAL));
        $tblTypeCommonAll = Type::useService()->getTypeAllByCategory(Type::useService()->getCategoryByIdentifier(TblCategory::COMMON));
        $tblTypeSecondCourseAll = Type::useService()->getTypeAllByCategory(Type::useService()->getCategoryByIdentifier(TblCategory::SECOND_COURSE));
        if ($tblTypeCommonAll && $tblTypeSecondCourseAll) {
            $tblTypeCommonAll = array_merge($tblTypeCommonAll, $tblTypeSecondCourseAll);
        }

        $yearAll = array();
        if (($tblYearAll = Term::useService()->getYearAll())) {
            foreach ($tblYearAll as $tblYear) {
                $yearAll[] = $tblYear->getName();
            }
        }

        $panelArrive = new Panel(
            'Schüler - Aufname',
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        (new SelectBox('Meta[TechnicalSchool][serviceTblSchoolDiploma]', 'Höchster Abschluss an einer allgemeinbildenden Schule',
                            array('{{ Name }}' => $tblSchoolDiplomaAll)))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        , 4),
                    new LayoutColumn(
                        (new SelectBox('Meta[TechnicalSchool][serviceTblSchoolType]', 'An der allgemeinbildenden Schulart (bzw. 2. Bildungsweg)',
                            array('{{ Name }}' => $tblTypeCommonAll)))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        , 4),
                    new LayoutColumn(
                        new TextField('Meta[TechnicalSchool][YearOfSchoolDiploma]', '', 'Abschlussjahr der allgemeinbildenden Schule')
                        , 4),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        (new SelectBox('Meta[TechnicalSchool][serviceTblTechnicalDiploma]', 'Höchster Abschluss an einer berufsbildenden Schule',
                            array('{{ Name }}' => $tblTechnicalDiplomaAll)))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        , 4),
                    new LayoutColumn(
                        (new SelectBox('Meta[TechnicalSchool][serviceTblTechnicalType]', 'An der berufsbildenden Schulart',
                            array('{{ Name }}' => $tblTypeTechnicalAll)))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        , 4),
                    new LayoutColumn(
                        new TextField('Meta[TechnicalSchool][YearOfTechnicalDiploma]', '', 'Abschlussjahr der berufsbildenden Schule')
                        , 4),
                ))
            ))),
            Panel::PANEL_TYPE_INFO
        );

        $Node = 'Berufsbildende Schulen - Allgemeines';

        $panelTechnical = new Panel(
            'Allgemeines',
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiMassReplace::receiverField((
                        $Field = (new SelectBox('Meta[TechnicalSchool][serviceTblTechnicalCourse]', 'Bildungsgang / Berufsbezeichnung / Ausbildung',
                            array('{{ Name }}' => $tblTechnicalCourseAll)))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        ))
                        . ApiMassReplace::receiverModal($Field, $Node)

                        . new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTechnicalSchool::CLASS_MASS_REPLACE_TECHNICAL_SCHOOL,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTechnicalSchool::METHOD_REPLACE_COURSE,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                            => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $Node,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $Node)
                        ))
                    ),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiMassReplace::receiverField((
                        $Field = (new SelectBox('Meta[TechnicalSchool][serviceTblTechnicalSubjectArea]', 'Fachrichtung',
                            array('{{ Name }}' => $tblTechnicalSubjectAreaAll)))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        ))
                        . ApiMassReplace::receiverModal($Field, $Node)

                        . new PullRight((new Link('Massen-Änderung',
                            ApiMassReplace::getEndpoint(), null, array(
                                ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTechnicalSchool::CLASS_MASS_REPLACE_TECHNICAL_SCHOOL,
                                ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTechnicalSchool::METHOD_REPLACE_SUBJECT_AREA,
                                ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                'Id'                                                            => $tblPerson->getId(),
                                'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                'Node'                                                          => $Node,
                            )))->ajaxPipelineOnClick(
                            ApiMassReplace::pipelineOpen($Field, $Node)
                        ))
                    ),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        (new SelectBox('Meta[TechnicalSchool][tblStudentTenseOfLesson]', 'Zeitform des Unterrichts',
                            array('{{ Name }}' => $tblStudentTenseOfLessonAll)))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        , 6),
                    new LayoutColumn(
                        (new SelectBox('Meta[TechnicalSchool][tblStudentTrainingStatus]', 'Ausbildungsstatus',
                            array('{{ Name }}' => $tblStudentTrainingStatusAll)))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        , 6),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new TextField('Meta[TechnicalSchool][DurationOfTraining]', '36', 'Planmäßige Ausbildungsdauer in Monaten')
                        , 6),
                    new LayoutColumn(
                        new TextField('Meta[TechnicalSchool][PraxisLessons]', '100', 'Geleistete Praxisstunden')
                        , 6),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new TextArea('Meta[TechnicalSchool][Remark]', '', 'Bemerkungen')
                    ),
                ))
            ))),
            Panel::PANEL_TYPE_INFO
        );

        $panelFinancialAid = new Panel(
            'Allgemeines',
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new CheckBox('Meta[TechnicalSchool][HasFinancialAid]', 'Bafög', 1)
                        , 4),
                    new LayoutColumn(
                        new AutoCompleter('Meta[TechnicalSchool][FinancialAidApplicationYear]', 'Beantragungsjahr', '',
                            $yearAll)
                        , 4),
                    new LayoutColumn(
                        new TextField('Meta[TechnicalSchool][FinancialAidBureau]', '', 'Amt')
                        , 4),
                )),
            ))),
            Panel::PANEL_TYPE_INFO
        );

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn($panelArrive),
                )),
                new FormRow(array(
                    new FormColumn($panelTechnical),
                )),
                new FormRow(array(
                    new FormColumn($panelFinancialAid),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentTechnicalSchoolContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentTechnicalSchoolContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}