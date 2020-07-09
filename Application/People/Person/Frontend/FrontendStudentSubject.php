<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.12.2018
 * Time: 11:37
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\MassReplace\StudentFilter;
use SPHERE\Application\Api\People\Meta\Subject\MassReplaceSubject;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendStudentSubject
 * 
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentSubject  extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Unterrichtsfächer';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentSubjectContent($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $subjects = array();
            if (($tblStudent = $tblPerson->getStudent())
                && ($tblStudentSubjectAll = Student::useService()->getStudentSubjectAllByStudent($tblStudent))
            ) {
                array_walk($tblStudentSubjectAll, function (TblStudentSubject $tblStudentSubject) use (&$subjects) {
                    $Type = $tblStudentSubject->getTblStudentSubjectType()->getIdentifier();
                    $Ranking = $tblStudentSubject->getTblStudentSubjectRanking()->getId();
                    $text = '&ndash;';
                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                        $text = $tblSubject->getDisplayName();
                        $fromLevel = $tblStudentSubject->getServiceTblLevelFrom();
                        $tillLevel = $tblStudentSubject->getServiceTblLevelTill();
                        if ($fromLevel || $tillLevel) {
                            $text .= new Container(
                                '(ab '
                                . ($fromLevel ? $fromLevel->getName() : '&ndash;')
                                . '. bis '
                                . ($tillLevel ? $tillLevel->getName() : '&ndash;')
                                . '.)'
                            );
                        }
                    }

                    $subjects[$Type][$Ranking] = $text;
                });
            }

            /**
             * Wahlfächer
             */
            $electiveRows = array();
            for ($i = 1; $i < 5; $i++)
            {
                $electiveRows[] =
                    new LayoutRow(array(
                        self::getLayoutColumnLabel($i . '. Wahlfach', 6),
                        self::getLayoutColumnValue(isset($subjects['ELECTIVE'][$i]) ? $subjects['ELECTIVE'][$i] : '&ndash;', 6),
                    ));
            }
            $electiveContent = new Layout(new LayoutGroup($electiveRows));

            /**
             * Arbeitsgemeinschaften
             */
            $teamRows = array();
            for ($i = 1; $i < 6; $i++)
            {
                $teamRows[] =
                    new LayoutRow(array(
                        self::getLayoutColumnLabel($i . '. AG', 6),
                        self::getLayoutColumnValue(isset($subjects['TEAM'][$i]) ? $subjects['TEAM'][$i] : '&ndash;', 6),
                    ));
            }
            $teamContent = new Layout(new LayoutGroup($teamRows));

            /**
             * Fremdsprachen
             */
            $foreignLanguageContent = array();
            for ($i = 1; $i < 5; $i++)
            {
                $foreignLanguageContent[] = new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        self::getLayoutColumnLabel($i . '. FS', 4),
                        self::getLayoutColumnValue(isset($subjects['FOREIGN_LANGUAGE'][$i]) ? $subjects['FOREIGN_LANGUAGE'][$i] : '&ndash;', 8),
                    )),
                )));
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Fremdsprachen',
                            $foreignLanguageContent
                        ),
                    ), 3),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Religion',
                            isset($subjects['RELIGION'][1]) ? $subjects['RELIGION'][1] : '&ndash;'
                        ),
                        FrontendReadOnly::getSubContent(
                            'Profil',
                            isset($subjects['PROFILE'][1]) ? $subjects['PROFILE'][1] : '&ndash;'
                        ),
                        FrontendReadOnly::getSubContent(
                            'Neigungskurs',
                            isset($subjects['ORIENTATION'][1]) ? $subjects['ORIENTATION'][1] : '&ndash;'
                        ),
                    ), 3),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Wahlfächer',
                            $electiveContent
                        ),
                    ), 3),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Arbeitsgemeinschaften',
                            $teamContent
                        ),
                    ), 3),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentSubjectContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Education()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentSubjectContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                if (($tblStudentSubjectAll = Student::useService()->getStudentSubjectAllByStudent($tblStudent))) {
                    array_walk($tblStudentSubjectAll, function (TblStudentSubject $tblStudentSubject) use (&$Global) {

                        $Type = $tblStudentSubject->getTblStudentSubjectType()->getId();
                        $Ranking = $tblStudentSubject->getTblStudentSubjectRanking()->getId();
                        $Subject = $tblStudentSubject->getServiceTblSubject() ? $tblStudentSubject->getServiceTblSubject()->getId() : 0;
                        $Global->POST['Meta']['Subject'][$Type][$Ranking] = $Subject;
                    });

                    $Global->savePost();
                }
            }
        }

        return $this->getEditStudentSubjectTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentSubjectForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentSubjectTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Education() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentSubjectForm(TblPerson $tblPerson = null)
    {

        // Orientation
        $tblSubjectOrientation = Subject::useService()->getSubjectOrientationAll();
        if ($tblSubjectOrientation) {
            array_push($tblSubjectOrientation, new TblSubject());
        } else {
            $tblSubjectOrientation = array(new TblSubject());
        }

        // Elective
        $tblSubjectElective = Subject::useService()->getSubjectElectiveAll();
        if ($tblSubjectElective) {
            array_push($tblSubjectElective, new TblSubject());
        } else {
            $tblSubjectElective = array(new TblSubject());
        }

        // Profile
        $tblSubjectProfile = Subject::useService()->getSubjectProfileAll();
        if ($tblSubjectProfile) {
            array_push($tblSubjectProfile, new TblSubject());
        } else {
            $tblSubjectProfile = array(new TblSubject());
        }

        // Religion
        $tblSubjectReligion = Subject::useService()->getSubjectReligionAll();
        if ($tblSubjectReligion) {
            array_push($tblSubjectReligion, new TblSubject());
        } else {
            $tblSubjectReligion = array(new TblSubject());
        }

        // ForeignLanguage
        $tblSubjectForeignLanguage = Subject::useService()->getSubjectForeignLanguageAll();
        if ($tblSubjectForeignLanguage) {
            array_push($tblSubjectForeignLanguage, new TblSubject());
        } else {
            $tblSubjectForeignLanguage = array(new TblSubject());
        }

        // All
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        if ($tblSubjectAll) {
            array_push($tblSubjectAll, new TblSubject());
        } else {
            $tblSubjectAll = array(new TblSubject());
        }

        FrontendStudent::setYearAndDivisionForMassReplace($tblPerson, $Year, $Division);
        $tblStudent = $tblPerson->getStudent();

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        $this->panelSubjectList('FOREIGN_LANGUAGE', 'Fremdsprachen', 'Fremdsprache',
                            $tblSubjectForeignLanguage, 4, ($tblStudent ? $tblStudent : null), $Year, $Division, $tblPerson),
                    ), 3),
                    new FormColumn(array(
                        $this->panelSubjectList('RELIGION', 'Religion', 'Religion', $tblSubjectReligion, 1,
                            ($tblStudent ? $tblStudent : null), $Year,
                            $Division, $tblPerson),
                        $this->panelSubjectList('PROFILE', 'Profile', 'Profil', $tblSubjectProfile, 1,
                            ($tblStudent ? $tblStudent : null), $Year,
                            $Division, $tblPerson),
                        $this->panelSubjectList('ORIENTATION', 'Neigungskurse', 'Neigungskurs', $tblSubjectOrientation, 1,
                            ($tblStudent ? $tblStudent : null), $Year, $Division, $tblPerson),
                    ), 3),
                    new FormColumn(array(
                        $this->panelSubjectList('ELECTIVE', 'Wahlfächer', 'Wahlfach', $tblSubjectElective, 4,
                            ($tblStudent ? $tblStudent : null), $Year,
                            $Division, $tblPerson),
                    ), 3),
                    new FormColumn(array(
                        $this->panelSubjectList('TEAM', 'Arbeitsgemeinschaften', 'Arbeitsgemeinschaft', $tblSubjectAll, 5,
                            ($tblStudent ? $tblStudent : null), $Year, $Division, $tblPerson),
                    ), 3),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentSubjectContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentSubjectContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param string $Title
     * @param string $Label
     * @param TblSubject[] $SubjectList
     * @param int $Count
     * @param TblStudent $tblStudent
     * @param array $Year
     * @param array $Division
     * @param TblPerson|null $tblPerson
     *
     * @return Panel
     */
    private function panelSubjectList(
        $Identifier,
        $Title,
        $Label,
        $SubjectList,
        $Count = 1,
        TblStudent $tblStudent = null,
        $Year = array(),
        $Division = array(),
        TblPerson $tblPerson = null
    ) {

        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(strtoupper($Identifier));
        $Panel = array();
        for ($Rank = 1; $Rank <= $Count; $Rank++) {
            $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($Rank);
            $PersonId = false;
            if($tblPerson) {
                $PersonId = $tblPerson->getId();
            }

            $useSubjectList = $SubjectList;
            $tblStudentSubject = false;
            // Vorhandene Werte ergänzen (wenn sie in der SelectBox nicht mehr existieren)
            if ($tblStudent && $tblStudentSubjectType && $tblStudentSubjectRanking) {
                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                    if (!array_key_exists($tblSubject->getId(), $SubjectList)) {
                        $tblSubjectList = array($tblSubject->getId() => $tblSubject);
                        $useSubjectList = array_merge($SubjectList, $tblSubjectList);
                    }
                }
            }

            $Node = 'Unterrichtsfächer';
            // activate MassReplace
            if ($Identifier == 'PROFILE'
                || $Identifier == 'RELIGION'
                || $Identifier == 'ORIENTATION'
                || $Identifier == 'FOREIGN_LANGUAGE'
                || $Identifier == 'ELECTIVE'
                || $Identifier == 'TEAM'
            ) {
                array_push($Panel,
                    ApiMassReplace::receiverField((
                    $Field = new SelectBox('Meta[Subject]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        ($Count > 1 ? $tblStudentSubjectRanking->getName().' ' : '') . $Label
                        , array('{{ Acronym }} - {{ Name }} {{ Description }}' => $useSubjectList), new Education())
                    ))
                    .ApiMassReplace::receiverModal($Field, $Node)
                    .new PullRight((new Link('Massen-Änderung',
                        ApiMassReplace::getEndpoint(), null, array(
                            ApiMassReplace::SERVICE_CLASS                                   => MassReplaceSubject::CLASS_MASS_REPLACE_SUBJECT,
                            ApiMassReplace::SERVICE_METHOD                                  => MassReplaceSubject::METHOD_REPLACE_SUBJECT,
                            ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                            MassReplaceSubject::ATTR_TYPE                                   => $tblStudentSubjectType->getId(),
                            MassReplaceSubject::ATTR_RANKING                                => $tblStudentSubjectRanking->getId(),
                            'Id'                                                            => $PersonId,
                            'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                            'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                            'Node'                                                          => $Node,
                        )))->ajaxPipelineOnClick(
                        ApiMassReplace::pipelineOpen($Field, $Node)
                    ))
                );
            } else {
                array_push($Panel,
                    new SelectBox(
                        'Meta[Subject]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        ($Count > 1 ? $tblStudentSubjectRanking->getName().' ' : '').$Label,
                        array('{{ Acronym }} - {{ Name }} {{ Description }}' => $useSubjectList),
                        new Education()
                    ));
            }
            // Student FOREIGN_LANGUAGE: LevelFrom, LevelTill
            if ($tblStudentSubjectType->getIdentifier() == 'FOREIGN_LANGUAGE') {
                $tblLevelAll = Division::useService()->getLevelAll();

                // Gespeicherte Daten ergänzen wenn nicht bereits vorhanden
                $useLevelFromList = $tblLevelAll;
                $useLevelTillList = $tblLevelAll;
                if ($tblStudentSubject && ($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())) {
                    if (!array_key_exists($tblLevelFrom->getId(), $tblLevelAll)) {
                        $tblLevelFromList = array($tblLevelFrom->getId() => $tblLevelFrom);
                        $useLevelFromList = array_merge($tblLevelAll, $tblLevelFromList);
                    }
                }
                if ($tblStudentSubject && ($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill())) {
                    if (!array_key_exists($tblLevelTill->getId(), $tblLevelAll)) {
                        $tblLevelTillList = array($tblLevelTill->getId() => $tblLevelTill);
                        $useLevelTillList = array_merge($tblLevelAll, $tblLevelTillList);
                    }
                }


                // Read StudentSubject Levels from DB
                if ($tblStudent) {
                    $tblStudentSubjectAll = Student::useService()
                        ->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
                    if ($tblStudentSubjectAll) {
                        foreach ($tblStudentSubjectAll as $tblStudentSubject) {
                            // TblStudentSubject Rank == Panel Rank
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getId() == $Rank) {
                                $Global = $this->getGlobal();
                                if ($tblStudentSubject->getServiceTblLevelFrom()) {
                                    $Global->POST['Meta']['SubjectLevelFrom'][$tblStudentSubjectType->getId()][$tblStudentSubjectRanking->getId()]
                                        = $tblStudentSubject->getServiceTblLevelFrom()->getId();
                                }
                                if ($tblStudentSubject->getServiceTblLevelTill()) {
                                    $Global->POST['Meta']['SubjectLevelTill'][$tblStudentSubjectType->getId()][$tblStudentSubjectRanking->getId()]
                                        = $tblStudentSubject->getServiceTblLevelTill()->getId();
                                }
                                $Global->savePost();
                            }
                        }
                    }
                }
                array_push($Panel,
                    ApiMassReplace::receiverField((
                    $Field = new SelectBox(
                        'Meta[SubjectLevelFrom]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        new Muted(new Small($tblStudentSubjectRanking->getName() . ' Fremdsprache von Klasse')),
                        array('{{ Name }} {{ ServiceTblType.Name }}' => $useLevelFromList),
                        new Time())))
                    .ApiMassReplace::receiverModal($Field, $Node)
                    .new PullRight((new Link('Massen-Änderung',
                        ApiMassReplace::getEndpoint(), null, array(
                            ApiMassReplace::SERVICE_CLASS                                   => MassReplaceSubject::CLASS_MASS_REPLACE_SUBJECT,
                            ApiMassReplace::SERVICE_METHOD                                  => MassReplaceSubject::METHOD_REPLACE_LEVEL_FROM,
                            ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                            MassReplaceSubject::ATTR_TYPE                                   => $tblStudentSubjectType->getId(),
                            MassReplaceSubject::ATTR_RANKING                                => $tblStudentSubjectRanking->getId(),
                            'Id'                                                            => $PersonId,
                            'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                            'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                            'Node'                                                          => $Node,
                        )))->ajaxPipelineOnClick(
                        ApiMassReplace::pipelineOpen($Field, $Node)
                    ))
                );
                array_push($Panel,
                    ApiMassReplace::receiverField((
                    $Field = new SelectBox(
                        'Meta[SubjectLevelTill]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        new Muted(new Small($tblStudentSubjectRanking->getName() . ' Fremdsprache bis Klasse')),
                        array('{{ Name }} {{ ServiceTblType.Name }}' => $useLevelTillList),
                        new Time())))
                    .ApiMassReplace::receiverModal($Field, $Node)
                    .new PullRight((new Link('Massen-Änderung',
                        ApiMassReplace::getEndpoint(), null, array(
                            ApiMassReplace::SERVICE_CLASS                                   => MassReplaceSubject::CLASS_MASS_REPLACE_SUBJECT,
                            ApiMassReplace::SERVICE_METHOD                                  => MassReplaceSubject::METHOD_REPLACE_LEVEL_TILL,
                            ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                            MassReplaceSubject::ATTR_TYPE                                   => $tblStudentSubjectType->getId(),
                            MassReplaceSubject::ATTR_RANKING                                => $tblStudentSubjectRanking->getId(),
                            'Id'                                                            => $PersonId,
                            'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                            'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                            'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                            'Node'                                                          => $Node,
                        )))->ajaxPipelineOnClick(
                        ApiMassReplace::pipelineOpen($Field, $Node)
                    ))
                );
            }
        }
        return new Panel($Title, $Panel, Panel::PANEL_TYPE_INFO);
    }
}