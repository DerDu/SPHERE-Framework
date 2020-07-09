<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Heart;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Medicine;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\Stethoscope;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

/**
 * Class FrontendStudentMedicalRecord
 * 
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentMedicalRecord extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Krankenakte';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentMedicalRecordContent($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
            ) {
                $disease = $tblStudentMedicalRecord->getDisease();
                $medication = $tblStudentMedicalRecord->getMedication();
                $attendingDoctor = $tblStudentMedicalRecord->getAttendingDoctor();
                $insuranceState = $tblStudentMedicalRecord->getInsuranceState();
                $insurance = $tblStudentMedicalRecord->getInsurance();
                $masernDate = $tblStudentMedicalRecord->getMasernDate();
                $masernDocumentType = $tblStudentMedicalRecord->getMasernDocumentType();
                $masernCreatorType = $tblStudentMedicalRecord->getMasernCreatorType();
            } else {
                $disease = '';
                $medication = '';
                $attendingDoctor = '';
                $insuranceState = '';
                $insurance = '';
                $masernDate = '';
                $masernDocumentType = '';
                $masernCreatorType = '';
            }

            if($masernDocumentType){
                $masernDocumentType = new ToolTip($masernDocumentType->getTextShort().' '.new Info() , $masernDocumentType->getTextLong());
            }
            if($masernCreatorType){
                $masernCreatorType = new ToolTip($masernCreatorType->getTextShort().' '.new Info() , $masernCreatorType->getTextLong());
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            '',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Krankheiten / Allergien', 6),
                                    self::getLayoutColumnValue($disease, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Medikamente', 6),
                                    self::getLayoutColumnValue($medication, 6),
                                ))
                            )))
                        ),

                    ), 4),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            '',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Versicherungsstatus', 6),
                                    self::getLayoutColumnValue($insuranceState, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Krankenkasse', 6),
                                    self::getLayoutColumnValue($insurance, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Behandelnder Arzt', 6),
                                    self::getLayoutColumnValue($attendingDoctor, 6),
                                )),
                            )))
                        ),
                    ), 4),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Masern-Impfpflicht',
                            array(
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Datum (vorgelegt am)', 6),
                                    self::getLayoutColumnValue($masernDate, 6),
                                )),
                            ))),
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Art der Bescheinigung', 6),
                                    self::getLayoutColumnValue($masernDocumentType, 6),
                                )),
                            ))),
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Bescheinigung durch', 6),
                                    self::getLayoutColumnValue($masernCreatorType, 6),
                                )),
                            ))))
                        ),
                    ), 4),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentMedicalRecordContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Hospital()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentMedicalRecordContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
            ) {

                $Global->POST['Meta']['MedicalRecord']['Disease'] = $tblStudentMedicalRecord->getDisease();
                $Global->POST['Meta']['MedicalRecord']['Medication'] = $tblStudentMedicalRecord->getMedication();
                $Global->POST['Meta']['MedicalRecord']['AttendingDoctor'] = $tblStudentMedicalRecord->getAttendingDoctor();
                $Global->POST['Meta']['MedicalRecord']['Insurance']['State'] = $tblStudentMedicalRecord->getInsuranceStateId();
                $Global->POST['Meta']['MedicalRecord']['Insurance']['Company'] = $tblStudentMedicalRecord->getInsurance();
                $Global->POST['Meta']['MedicalRecord']['Masern']['Date'] = $tblStudentMedicalRecord->getMasernDate();
                if(($tblStudentMasernInfoDocument = $tblStudentMedicalRecord->getMasernDocumentType())){
                    $Global->POST['Meta']['MedicalRecord']['DocumentType'][$tblStudentMasernInfoDocument->getMeta()] = $tblStudentMasernInfoDocument->getId();
                }
                if(($tblStudentMasernInfoCreator = $tblStudentMedicalRecord->getMasernCreatorType())){
                    $Global->POST['Meta']['MedicalRecord']['CreatorType'][$tblStudentMasernInfoCreator->getMeta()] = $tblStudentMasernInfoCreator->getId();
                }
                $Global->savePost();
            }
        }

        return $this->getEditStudentMedicalRecordTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentMedicalRecordForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentMedicalRecordTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Hospital() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentMedicalRecordForm(TblPerson $tblPerson = null)
    {

//        $tblPerson
        $tblStudentInsuranceState = Student::useService()->getStudentInsuranceStateAll();

        $PanelContentArray = array();
        $PanelContentArray[] = new DatePicker('Meta[MedicalRecord][Masern][Date]', null, 'Datum (vorgelegt am)');

        // Document
        $tblStudentMasernInfoDocumentList = Student::useService()->getStudentMasernInfoByType(TblStudentMasernInfo::TYPE_DOCUMENT);
        $DisableDocumentList = array(
            TblStudentMasernInfo::DOCUMENT_IDENTIFICATION => array(
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_VACCINATION_PROTECTION.']',
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_IMMUNITY.']',
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_CANT_VACCINATION.']'
            ),
            TblStudentMasernInfo::DOCUMENT_VACCINATION_PROTECTION => array(
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_IDENTIFICATION.']',
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_IMMUNITY.']',
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_CANT_VACCINATION.']'
            ),
            TblStudentMasernInfo::DOCUMENT_IMMUNITY => array(
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_IDENTIFICATION.']',
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_VACCINATION_PROTECTION.']',
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_CANT_VACCINATION.']'
            ),
            TblStudentMasernInfo::DOCUMENT_CANT_VACCINATION => array(
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_IDENTIFICATION.']',
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_VACCINATION_PROTECTION.']',
                'Meta[MedicalRecord][DocumentType]['.TblStudentMasernInfo::DOCUMENT_IMMUNITY.']',
            )
        );

        $DocumentType = new Bold('Art der Bescheinigung');
        if($tblStudentMasernInfoDocumentList){
            foreach($tblStudentMasernInfoDocumentList as $tblStudentMasernInfoDocument){
                $DocumentType .= new CheckBox('Meta[MedicalRecord][DocumentType]['.$tblStudentMasernInfoDocument->getMeta().']',
                    $tblStudentMasernInfoDocument->getTextLong(), $tblStudentMasernInfoDocument->getId(), $DisableDocumentList[$tblStudentMasernInfoDocument->getMeta()]);
            }
        }
        $PanelContentArray[] = $DocumentType;
        // Creator
        $tblStudentMasernInfoProofList = Student::useService()->getStudentMasernInfoByType(TblStudentMasernInfo::TYPE_CREATOR);
        $DisableCreatorList = array(
            TblStudentMasernInfo::CREATOR_STATE => array(
                'Meta[MedicalRecord][CreatorType]['.TblStudentMasernInfo::CREATOR_COMMUNITY.']'
            ),
            TblStudentMasernInfo::CREATOR_COMMUNITY => array(
                'Meta[MedicalRecord][CreatorType]['.TblStudentMasernInfo::CREATOR_STATE.']'
            ),
        );
        $CreatorType = new Bold('Bescheinigung, dass der Nachweis bereits vorgelegt wurde, durch:');
        if($tblStudentMasernInfoProofList){
            foreach($tblStudentMasernInfoProofList as $tblStudentMasernInfoProof){
                $CreatorType .= new CheckBox('Meta[MedicalRecord][CreatorType]['.$tblStudentMasernInfoProof->getMeta().']',
                    $tblStudentMasernInfoProof->getTextLong(), $tblStudentMasernInfoProof->getId(), $DisableCreatorList[$tblStudentMasernInfoProof->getMeta()]);
            }
        }
        $PanelContentArray[] = $CreatorType;

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('&nbsp;', array(
                        new TextArea('Meta[MedicalRecord][Disease]', 'Krankheiten / Allergien', 'Krankheiten / Allergien', new Heart()),
                        new TextArea('Meta[MedicalRecord][Medication]', 'Medikamente', 'Medikamente', new Medicine())
                        ), Panel::PANEL_TYPE_INFO)
                    , 4),
                    new FormColumn(
                        new Panel('&nbsp;', array(
                            new SelectBox('Meta[MedicalRecord][Insurance][State]', 'Versicherungsstatus', array( '{{ Name }}' => $tblStudentInsuranceState), new Lock()),
                            new AutoCompleter('Meta[MedicalRecord][Insurance][Company]', 'Krankenkasse', 'Krankenkasse', array(), new Shield()),
                            new TextField('Meta[MedicalRecord][AttendingDoctor]', 'Name', 'Behandelnder Arzt',
                                new Stethoscope())
                        ), Panel::PANEL_TYPE_INFO)
                    , 4),
                    new FormColumn(
                        new Panel('Masern-Impfpflicht',
                            $PanelContentArray
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentMedicalRecordContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentMedicalRecordContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}