<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.12.2018
 * Time: 15:57
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\MassReplace\StudentFilter;
use SPHERE\Application\Api\People\Meta\Student\ApiStudent;
use SPHERE\Application\Api\People\Meta\Student\MassReplaceStudent;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeMinus;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendTeacher
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudent extends FrontendReadOnly
{
    const TITLE = 'Schülerakte';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentTitle($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
        ) {
            $showLink = (new Link(new EyeOpen() . ' Anzeigen', ApiPersonReadOnly::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadStudentContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                new Info('Die Schülerakte ist ausgeblendet. Bitte klicken Sie auf Anzeigen.'),
                array($showLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Tag(),
                true
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentContent($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $hasApiRight = Access::useService()->hasAuthorization('/Api/Document/Standard/StudentCard/Create');
            if ($hasApiRight && $tblPerson != null) {
                $listingContent[] = new External(
                        'Herunterladen der Schülerkartei', 'SPHERE\Application\Api\Document\Standard\StudentCard\Create',
                        new Download(), array('PersonId' => $tblPerson->getId()), 'Schülerkartei herunterladen')
                    .new External(
                        'Erstellen der Schulbescheinigung', '\Document\Standard\EnrollmentDocument\Fill',
                        new Download(), array('PersonId' => $tblPerson->getId()),
                        'Erstellen und Herunterladen einer Schulbescheinigung');
            }

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                 self::getStudentBasicContent($PersonId), 'StudentBasicContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentTransfer::getStudentTransferContent($PersonId), 'StudentTransferContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentProcess::getStudentProcessContent($PersonId), 'StudentProcessContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentMedicalRecord::getStudentMedicalRecordContent($PersonId), 'StudentMedicalRecordContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentGeneral::getStudentGeneralContent($PersonId), 'StudentGeneralContent'
            );

            $listingContent[] = ApiPersonReadOnly::receiverBlock(
                FrontendStudentSubject::getStudentSubjectContent($PersonId), 'StudentSubjectContent'
            );

            $content = new Listing($listingContent);

            $hideLink = (new Link(new EyeMinus() . ' Ausblenden', ApiPersonReadOnly::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadStudentTitle($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($hideLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Tag()
                , true
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentBasicContent($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                $identifier = $tblStudent->getIdentifierComplete();
                $schoolAttendanceStartDate = $tblStudent->getSchoolAttendanceStartDate();
                $hasMigrationBackground = $tblStudent->getHasMigrationBackground() ? 'Ja' : 'Nein';
                $isInPreparationDivisionForMigrants = $tblStudent->isInPreparationDivisionForMigrants() ? 'Ja' : 'Nein';
            } else {
                $identifier = '';
                $schoolAttendanceStartDate = '';
                $hasMigrationBackground = '';
                $isInPreparationDivisionForMigrants = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Identifikation'),
                    self::getLayoutColumnValue($identifier),
                    self::getLayoutColumnLabel('Schulpflichtbeginn'),
                    self::getLayoutColumnValue($schoolAttendanceStartDate),
                    self::getLayoutColumnLabel('Herkunftssprache ist nicht oder nicht ausschließlich Deutsch'),
                    self::getLayoutColumnValue($hasMigrationBackground)
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentBasicContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE . ' - Grunddaten',
                self::getSubContent('Grunddaten', $content),
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Nameplate()
            );
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Year
     * @param $Division
     */
    public static function setYearAndDivisionForMassReplace(TblPerson $tblPerson, &$Year, &$Division)
    {
        $Year[ViewYear::TBL_YEAR_ID] = '';
        $Division[ViewDivisionStudent::TBL_LEVEL_ID] = '';
        $Division[ViewDivisionStudent::TBL_DIVISION_NAME] = '';
        $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE] = '';
        // #SSW-1598 Fehlerbehebung Massen-Änderung

        // get information without tblStudent information
        $tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson);
        if ($tblPerson && $tblDivision) {
            $Division[ViewDivisionStudent::TBL_DIVISION_NAME] = $tblDivision->getName();
            if (($tblLevel = $tblDivision->getTblLevel())) {
                $Division[ViewDivisionStudent::TBL_LEVEL_ID] = $tblLevel->getId();
            }
            if (($tblType = $tblLevel->getServiceTblType())) {
                $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE] = $tblType->getId();
            }
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                $Year[ViewYear::TBL_YEAR_ID] = $tblYear->getId();
            }
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentBasicContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if ($tblStudent = Student::useService()->getStudentByPerson($tblPerson)) {

                $Global->POST['Meta']['Student']['Prefix'] = $tblStudent->getPrefix();
                $Global->POST['Meta']['Student']['Identifier'] = $tblStudent->getIdentifier();
                $Global->POST['Meta']['Student']['SchoolAttendanceStartDate'] = $tblStudent->getSchoolAttendanceStartDate();

                $Global->POST['Meta']['Student']['HasMigrationBackground'] = $tblStudent->getHasMigrationBackground();
                $Global->POST['Meta']['Student']['IsInPreparationDivisionForMigrants'] = $tblStudent->isInPreparationDivisionForMigrants();

                $Global->savePost();
            }
        }

        return $this->getEditStudentBasicTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentBasicForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentBasicTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Nameplate() . ' ' . self::TITLE . ' - Grunddaten', self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentBasicForm(TblPerson $tblPerson = null)
    {

        self::setYearAndDivisionForMassReplace($tblPerson, $Year, $Division);

        $isIdentifierAuto = false;
        $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');
        if($tblSetting && $tblSetting->getValue()){
            $isIdentifierAuto = true;
        }

        $NodePrefix = 'Grunddaten - Prefix der Schülernummer';
        $StartDatePrefix = 'Grunddaten - Schulpflicht';

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Identifikation', array(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            ApiMassReplace::receiverField((
                                            $Field = new TextField('Meta[Student][Prefix]',
                                                'Prefix', 'Prefix')
                                            ))
                                            .ApiMassReplace::receiverModal($Field, $NodePrefix)

                                            .new PullRight((new Link('Massen-Änderung',
                                                ApiMassReplace::getEndpoint(), null, array(
                                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceStudent::CLASS_MASS_REPLACE_STUDENT,
                                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceStudent::METHOD_REPLACE_PREFIX,
                                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                                    'Id'                                                            => $tblPerson->getId(),
                                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                                    'Node'                                                          => $NodePrefix,
                                                )))->ajaxPipelineOnClick(
                                                ApiMassReplace::pipelineOpen($Field, $NodePrefix)
                                            ))
                                            , 4)
                                    ,
                                        new LayoutColumn(
                                            ($isIdentifierAuto
                                                ?
                                                (new TextField('Meta[Student][Identifier]', 'Schülernummer',
                                                    'Schülernummer'))->setDisabled()
                                                    ->ajaxPipelineOnKeyUp(ApiStudent::pipelineCompareIdentifier($tblPerson->getId()))
                                                :
                                                (new TextField('Meta[Student][Identifier]', 'Schülernummer',
                                                    'Schülernummer'))
                                                    ->ajaxPipelineOnKeyUp(ApiStudent::pipelineCompareIdentifier($tblPerson->getId()))
                                            )
                                            , 8)
                                    ))
                                )
                            )
                        ,
                            ($isIdentifierAuto
                                ? ''
                                : ApiStudent::receiverControlIdentifier()
                            )

                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Schulpflicht', array(
                            ApiMassReplace::receiverField((
                            $Field = new DatePicker('Meta[Student][SchoolAttendanceStartDate]', '',
                                'Beginnt am', new Calendar())
                            ))
                            .ApiMassReplace::receiverModal($Field, $StartDatePrefix)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceStudent::CLASS_MASS_REPLACE_STUDENT,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceStudent::METHOD_REPLACE_START_DATE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                            => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodePrefix,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodePrefix)
                            ))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Kamenz-Statistik', array(
                            new CheckBox(
                                'Meta[Student][HasMigrationBackground]',
                                'Herkunftssprache ist nicht oder nicht ausschließlich Deutsch',
                                1
                            ),
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentBasicContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentBasicContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}