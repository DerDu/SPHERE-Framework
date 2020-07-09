<?php

namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\Api\People\Meta\Support\ApiSupport;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblHandyCap;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecial;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecialDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupport;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocusType;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\SuperGlobal;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     *
     * @return Stage
     */
    public function frontendIntegration(TblPerson $tblPerson = null)
    {

        $Stage = new Stage();

        $SupportContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'SupportTable');
        $SpecialContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'SpecialTable');
        $HandyCapContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'HandyCapTable');

        $Accordion = new Accordion('');
        $Accordion->addItem('Förderantrag/Förderbescheid '.ApiSupport::receiverInline('', 'SupportCount'), $SupportContent, true);
        $Accordion->addItem('Entwicklungsbesonderheiten '.ApiSupport::receiverInline('', 'SpecialCount'), $SpecialContent, false);
        $Accordion->addItem('Nachteilsausgleich '.ApiSupport::receiverInline('', 'HandyCapCount'), $HandyCapContent, false);

        $Stage->setContent(
            ApiSupport::pipelineLoadTable($tblPerson->getId())
            .new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Für Lehrer sind nur die aktuellsten Einträge sichtbar')
                        , 6)
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                                ApiSupport::receiverModal(),
                                (new Standard('Förderantrag/Förderbescheid hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateSupportModal($tblPerson->getId())),
                                (new Standard('Entwicklungsbesonderheiten hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateSpecialModal($tblPerson->getId())),
                                (new Standard('Nachteilsausgleich hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateHandyCapModal($tblPerson->getId())),
                                new Ruler()
                            )
                        )
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            $Accordion,
                        ))
                    )
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param int      $PersonId
     * @param null|int $SupportId
     *
     * @return Form
     */
    public function formSupport($PersonId, $SupportId = null)
    {

        $Global = $this->getGlobal();
        if($SupportId != null && !isset($Global->POST['Data']['Date'])){
            if(($tblSupport = Student::useService()->getSupportById($SupportId))){
                $Global = $this->fillGlobalSupport($tblSupport, $Global);
                $Global->savePost();
            }
        } elseif(!isset($Global->POST['Data']['Date']) && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            // fill Field with newest Input (only for new Entity's)
            if(($tblSupport = Student::useService()->getSupportByPersonNewest($tblPerson))){
                $IsEdit = false;
                $Global = $this->fillGlobalSupport($tblSupport, $Global, $IsEdit);
                $Global->savePost();
            }
        }
        $tblSupportFocusList = Student::useService()->getSupportFocusTypeAll();
        $SupportTypeList = Student::useService()->getSupportTypeAll();

        $tblSupportFocusList = $this->getSorter($tblSupportFocusList)->sortObjectBy('Name');
        $CheckboxList = array();
        /** @var TblSupportFocusType $tblSupportFocus */
        foreach($tblSupportFocusList as $tblSupportFocus){
            $CheckboxList[] = new CheckBox('Data[CheckboxList]['.$tblSupportFocus->getName().']', $tblSupportFocus->getName(), $tblSupportFocus->getId());
        }

        if($SupportId === null){
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineCreateSupportSave($PersonId));
        } else {
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineUpdateSupportSave($PersonId, $SupportId));
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                        , 6),
                    new FormColumn(
                        new SelectBox('Data[PrimaryFocus]', 'Primär geförderter Schwerpunkt', array('{{ Name }}' => $tblSupportFocusList))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new SelectBox('Data[SupportType]', 'Vorgang', array('{{ Name }}' => $SupportTypeList), new Education()))->setRequired(),
                        new Warning('Nur "Förderbescheid" ist für Lehrer sichtbar')
                        ), 6),
                    new FormColumn(array(
                            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold('Förderschwerpunkte'))))),
                            new Listing($CheckboxList)
                        ), 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new TextField('Data[Company]', 'Förderschule', 'Förderschule', new Education()),
                        new Ruler(),
                        new TextField('Data[PersonSupport]', 'Schulbegleitung', 'Schulbegleitung', new PersonIcon()),
                        new Ruler(),
                        new TextField('Data[SupportTime]', 'Stundenbedarf pro Woche', 'Stundenbedarf pro Woche', new Clock()),
                    ), 6),
                    new FormColumn(
                        (new TextArea('Data[Remark]', 'Bemerkung', 'Bemerkung', new Edit()))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        $SaveButton
                    ))
                )),
            ))
        );
    }

    /**
     * @param TblSupport  $tblSupport
     * @param SuperGlobal $Global
     * @param bool        $IsEdit
     *
     * @return SuperGlobal
     */
    private function fillGlobalSupport(TblSupport $tblSupport, SuperGlobal $Global, $IsEdit = true)
    {
        // fill only update Form's
        if($IsEdit){
            $Global->POST['Data']['Date'] = $tblSupport->getDate();
            if(($tblSupportType = $tblSupport->getTblSupportType())){
                $Global->POST['Data']['SupportType'] = $tblSupportType->getId();
            }
            $Global->POST['Data']['Remark'] = $tblSupport->getRemark(false);
        }

        if(($tblSupportFocusPrimary = Student::useService()->getPrimaryFocusBySupport($tblSupport))){
            $Global->POST['Data']['PrimaryFocus'] = $tblSupportFocusPrimary->getId();
        }
        if(($tblSupportFocusTypeList = Student::useService()->getFocusListBySupport($tblSupport))){
            foreach($tblSupportFocusTypeList as $tblSupportFocusType){
                $Global->POST['Data']['CheckboxList'][$tblSupportFocusType->getName()] = $tblSupportFocusType->getId();
            }
        }


        $Global->POST['Data']['Company'] = $tblSupport->getCompany();
        $Global->POST['Data']['PersonSupport'] = $tblSupport->getPersonSupport();
        $Global->POST['Data']['SupportTime'] = $tblSupport->getSupportTime();
        return $Global;
    }

    /**
     * @param int $PersonId
     * @param null|int $SpecialId
     * @param bool $IsCanceled
     * @param bool $IsInit
     *
     * @return Form
     */
    public function formSpecial($PersonId, $SpecialId = null, $IsCanceled = false, $IsInit = false)
    {

        $Global = $this->getGlobal();
        if($SpecialId != null && !isset($Global->POST['Data']['Date'])) {
            if (($tblSpecial = Student::useService()->getSpecialById($SpecialId))) {
                if ($IsInit) {
                    if ($tblSpecial->isCanceled()) {
                       $Global->POST['Data']['IsCanceled'] = $tblSpecial->isCanceled() ? '1' : '0';
                    }
                }

                $Global = $this->fillGlobalSpecial($tblSpecial, $Global);

                $Global->savePost();
            } elseif (!isset($Global->POST['Data']['Date']) && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
                // fill Field with newest Input (only for new Entity's)
                if(($tblSpecial = Student::useService()->getSpecialByPersonNewest($tblPerson))) {
                    $IsEdit = false;
                    $Global = $this->fillGlobalSpecial($tblSpecial, $Global, $IsEdit);
                    $Global->savePost();
                }
            }
        }

        $tblSpecialDisorderList = Student::useService()->getSpecialDisorderTypeAll();
        $tblSpecialDisorderList = $this->getSorter($tblSpecialDisorderList)->sortObjectBy('Name');
        $CheckboxList = array();
        /** @var TblSpecialDisorderType $tblSpecialDisorder*/
        foreach($tblSpecialDisorderList as $tblSpecialDisorder){
            $CheckboxList[] = new CheckBox('Data[CheckboxList]['.$tblSpecialDisorder->getName().']', $tblSpecialDisorder->getName(), $tblSpecialDisorder->getId());
        }

        if($SpecialId === null){
            // create
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineCreateSpecialSave($PersonId));
            $cancelCheckbox = (new CheckBox('Data[IsCanceled]', new Bold('Aufhebung'), '1'))->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateSpecialModal($PersonId));
        } else {
            // edit
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineUpdateSpecialSave($PersonId, $SpecialId));
            $cancelCheckbox = (new CheckBox('Data[IsCanceled]', new Bold('Aufhebung'), '1'))->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditSpecialModal($PersonId, $SpecialId));
        }

        $arrayRight = array();

        $arrayLeft[] = (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired();
        $arrayLeft[] = $cancelCheckbox;
        if (!$IsCanceled) {
            $arrayLeft[] = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold('Entwicklungsbesonderheiten '.new DangerText('*'))))));
            $arrayLeft[] = new Listing($CheckboxList);

            $arrayRight[] = new TextArea('Data[Remark]', 'Bemerkung', 'Bemerkung', new Edit());
        }

        $arrayRight[] = new HiddenField('SpecialId');

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn($arrayLeft, 6),
                    new FormColumn($arrayRight, 6)
                )),
                new FormRow(array(
                    new FormColumn(
                        $SaveButton
                    )
                )),
            ))
        );
    }

    /**
     * @param TblSpecial  $tblSpecial
     * @param SuperGlobal $Global
     * @param bool        $IsEdit
     *
     * @return SuperGlobal
     */
    private function fillGlobalSpecial(TblSpecial $tblSpecial, SuperGlobal $Global, $IsEdit = true)
    {
        // fill only update Form's
        if($IsEdit){
            $Global->POST['Data']['Date'] = $tblSpecial->getDate();
            $Global->POST['Data']['Remark'] = $tblSpecial->getRemark(false);
        }

        if (($tblSpecialDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial))) {
            foreach ($tblSpecialDisorderTypeList as $tblSpecialDisorderType) {
                $Global->POST['Data']['CheckboxList'][$tblSpecialDisorderType->getName()] = $tblSpecialDisorderType->getId();
            }
        }

        return $Global;
    }

    /**
     * @param int      $PersonId
     * @param null|int $HandyCapId
     * @param bool $IsCanceled
     * @param bool $IsInit
     *
     * @return Form
     */
    public function formHandyCap($PersonId, $HandyCapId = null, $IsCanceled = false, $IsInit = false)
    {

        $Global = $this->getGlobal();
        if($HandyCapId != null && !isset($Global->POST['Data']['Date'])){
            if(($tblHandyCap = Student::useService()->getHandyCapById($HandyCapId))){
                if ($IsInit) {
                    if ($tblHandyCap->isCanceled()) {
                        $Global->POST['Data']['IsCanceled'] = $tblHandyCap->isCanceled() ? '1' : '0';
                    }
                }

                $Global->POST['Data']['Date'] = $tblHandyCap->getDate();
                $Global->POST['Data']['LegalBasis'] = $tblHandyCap->getLegalBasis();
                $Global->POST['Data']['LearnTarget'] = $tblHandyCap->getLearnTarget();
                $Global->POST['Data']['RemarkLesson'] = $tblHandyCap->getRemarkLesson(false);
                $Global->POST['Data']['RemarkRating'] = $tblHandyCap->getRemarkRating(false);
                $Global->savePost();
            }
        }// don't need pre fill for create new Entity's

        if($HandyCapId === null){
            // create
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineCreateHandyCapSave($PersonId));

            $cancelCheckbox = (new CheckBox('Data[IsCanceled]', new Bold('Aufhebung'), '1'))->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateHandyCapModal($PersonId));
        } else {
            // edit
            $SaveButton = (new PrimaryLink('Speichern', ApiSupport::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSupport::pipelineUpdateHandyCapSave($PersonId, $HandyCapId));

            $cancelCheckbox = (new CheckBox('Data[IsCanceled]', new Bold('Aufhebung'), '1'))->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditHandyCapModal($PersonId, $HandyCapId));
        }

        $array[] = (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired();
        $array[] = $cancelCheckbox;

        if ($IsCanceled) {
            return new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn($array, 6),
                    )),
                    new FormRow(array(
                        new FormColumn(
                            $SaveButton
                        )
                    )),
                ))
            );
        } else {

            return new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn($array, 6),
                    )),
                    new FormRow(new FormColumn(new Ruler())),
                    new FormRow(array(
                        new FormColumn(array(
                            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold('Rechtliche Grundlage'))))),
                        ), 6),
                        new FormColumn(array(
                            new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Bold('Lernziel'))))),
                        ), 6)
                    )),
                    new FormRow(array(
                        new FormColumn(
                            new RadioBox('Data[LegalBasis]', 'Schulaufsicht', TblHandyCap::LEGAL_BASES_SCHOOL_VIEW)
                            , 3),
                        new FormColumn(
                            new RadioBox('Data[LegalBasis]', 'Schulintern', TblHandyCap::LEGAL_BASES_INTERN)
                            , 3),
                        new FormColumn(
                            new RadioBox('Data[LearnTarget]', 'lernzielgleich', TblHandyCap::LEARN_TARGET_EQUAL)
                            , 3),
                        new FormColumn(
                            new RadioBox('Data[LearnTarget]', 'lernzieldifferenziert',
                                TblHandyCap::LEARN_TARGET_DIFFERENT)
                            , 3)
                    )),
                    new FormRow(new FormColumn(new Ruler())),
                    new FormRow(
                        new FormColumn(
                            new TextArea('Data[RemarkLesson]', 'Bemerkung', 'Besonderheiten im Unterricht', new Edit())
                            , 12)
                    ),
                    new FormRow(new FormColumn(new Ruler())),
                    new FormRow(
                        new FormColumn(
                            new TextArea('Data[RemarkRating]', 'Bemerkung', 'Besonderheiten bei Leistungsbewertungen',
                                new Edit())
                            , 12)
                    ),
                    new FormRow(array(
                        new FormColumn(
                            $SaveButton
                        )
                    )),
                ))
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $hasEdit
     *
     * @return TableData|Warning
     */
    public function getSupportTable(TblPerson $tblPerson, $hasEdit = true)
    {

        $tblSupportList = Student::useService()->getSupportByPerson($tblPerson);
        $TableContent = array();
        if($tblSupportList){
            array_walk($tblSupportList, function(TblSupport $tblSupport) use (&$TableContent, $tblPerson, $hasEdit){
                $Item['RequestDate'] = $tblSupport->getDate();
                $Item['CoachingRequest'] = ($tblSupport->getTblSupportType() ? $tblSupport->getTblSupportType()->getName() : '');
                $Item['Focus'] = '';
                $Item['IntegrationCompany'] = $tblSupport->getCompany();
                $Item['PersonSupport'] = $tblSupport->getPersonSupport();
                $Item['IntegrationTime'] = $tblSupport->getSupportTime();
                $Item['IntegrationRemark'] = $tblSupport->getRemark();
                $Item['Editor'] = $tblSupport->getPersonEditor();
                if ($hasEdit) {
                    $Item['Option'] = (new Standard('', '#', new Edit()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditSupportModal($tblPerson->getId(),
                                $tblSupport->getId()))
                        . (new Standard('', '#', new Remove()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineOpenDeleteSupport($tblPerson->getId(),
                                $tblSupport->getId()));
                }

                $FocusList = array();
                $PrimaryFocus = Student::useService()->getPrimaryFocusBySupport($tblSupport);
                $tblFocusList = Student::useService()->getFocusListBySupport($tblSupport);
                if($tblFocusList){
                    foreach($tblFocusList as $tblFocus){
                        if($PrimaryFocus && $PrimaryFocus->getId() == $tblFocus->getId()){
                            $FocusList[] = new Bold($tblFocus->getName().' *');
                        } else {
                            $FocusList[] = $tblFocus->getName();
                        }
                    }
                }
                if(!empty($FocusList)) {
                    $Item['Focus'] = new Listing($FocusList);
                }

                array_push($TableContent, $Item);
            });
        }

        if(empty($TableContent)){
            return new Warning('Es sind keine Daten vorhanden.');
        }

        $columns = array('RequestDate' => 'Datum',
            'CoachingRequest' => 'Vorgang',
            'Focus' => 'Förderschwerpunkte '.new Muted(new Small('Primary *')),
            'IntegrationCompany' => 'Förderschule',
            'PersonSupport' => 'Schulbegleitung',
            'IntegrationTime' => 'Stundenbedarf',
            'IntegrationRemark' => 'Bemerkung',
            'Editor' => 'Bearbeiter',
        );
        if ($hasEdit) {
            $columns['Option'] = '';
        }

        return new TableData($TableContent, null, $columns,
            array(
                'order' => array(
                    array(0, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
//                'searching' => false,
                'responsive' => false
            ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $hasEdit
     *
     * @return TableData|Warning
     */
    public function getSpecialTable(TblPerson $tblPerson, $hasEdit = true)
    {

        $tblSpecialList = Student::useService()->getSpecialByPerson($tblPerson);
        $TableContent = array();
        if($tblSpecialList){
            array_walk($tblSpecialList, function(TblSpecial $tblSpecial) use (&$TableContent, $tblPerson, $hasEdit){
                $Item['RequestDate'] = $tblSpecial->getDate();
                $Item['Disorder'] = '';
                $Item['Remark'] = $tblSpecial->getRemark();
                $Item['Editor'] = $tblSpecial->getPersonEditor();
                if ($hasEdit) {
                    $Item['Option'] = (new Standard('', '#', new Edit()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditSpecialModal($tblPerson->getId(),
                                $tblSpecial->getId(), true))
                        . (new Standard('', '#', new Remove()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineOpenDeleteSpecial($tblPerson->getId(),
                                $tblSpecial->getId()));
                }

                $DisorderList = array();
                $tblStudentDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial);
                if($tblStudentDisorderTypeList){
                    foreach($tblStudentDisorderTypeList as $tblStudentDisorderType){
                        $DisorderList[] = $tblStudentDisorderType->getName();
                    }
                }
                if(!empty($DisorderList)) {
                    $Item['Disorder'] = new Listing($DisorderList);
                }

                array_push($TableContent, $Item);
            });
        }

        if(empty($TableContent)){
            return new Warning('Es sind keine Daten vorhanden.');
        }

        $columns = array('RequestDate' => 'Datum',
            'Disorder' => 'Entwicklungsbesonderheiten',
            'Remark' => 'Bemerkung',
            'Editor' => 'Bearbeiter',
        );
        if ($hasEdit) {
            $columns['Option'] = '';
        }

        return new TableData($TableContent, null, $columns,
            array(
                'order' => array(
                    array(0, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
//                'searching' => false,
                'responsive' => false
            ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $hasEdit
     *
     * @return TableData|Warning
     */
    public function getHandyCapTable(TblPerson $tblPerson, $hasEdit = true)
    {

        $tblSpecialList = Student::useService()->getHandyCapByPerson($tblPerson);
        $TableContent = array();
        if($tblSpecialList){
            array_walk($tblSpecialList, function(TblHandyCap $tblHandyCap) use (&$TableContent, $tblPerson, $hasEdit){
                $Item['RequestDate'] = $tblHandyCap->getDate();
                $Item['LegalBasis'] = $tblHandyCap->getLegalBasis();
                $Item['LearnTarget'] = $tblHandyCap->getLearnTarget();
                $Item['RemarkLesson'] = $tblHandyCap->getRemarkLesson();
                $Item['RemarkRating'] = $tblHandyCap->getRemarkRating();
                $Item['Editor'] = $tblHandyCap->getPersonEditor();
                if ($hasEdit) {
                    $Item['Option'] = (new Standard('', '#', new Edit()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineOpenEditHandyCapModal($tblPerson->getId(),
                                $tblHandyCap->getId(), true))
                        . (new Standard('', '#', new Remove()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineOpenDeleteHandyCap($tblPerson->getId(),
                                $tblHandyCap->getId()));
                }

                array_push($TableContent, $Item);
            });
        }

        if(empty($TableContent)){
            return new Warning('Es sind keine Daten vorhanden.');
        }

        $columns = array('RequestDate' => 'Datum',
            'LegalBasis' => 'Rechtliche Grundlage',
            'LearnTarget' => 'Lernziel',
            'RemarkLesson' => 'Besonderheiten im Unterricht',
            'RemarkRating' => 'Besonderheiten bei Leistungsbewertungen',
            'Editor' => 'Bearbeiter'
        );
        if ($hasEdit) {
            $columns['Option'] = '';
        }

        return new TableData($TableContent, null, $columns,
            array(
                'order' => array(
                    array(0, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
//                'searching' => false,
                'responsive' => false
            ));
    }
}
