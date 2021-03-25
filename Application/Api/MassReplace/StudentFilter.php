<?php

namespace SPHERE\Application\Api\MassReplace;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

class StudentFilter extends Extension
{
    const STUDENT_FILTER = 'StudentFilter';

    /**
     * @param string $modalField
     *
     * @return Form|string
     */
    public function formStudentFilter($modalField)
    {

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        $tblLevelShowList = array();

        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as &$tblLevel) {
                if (!$tblLevel->getName()) {
                    $tblLevelClone = clone $tblLevel;
                    $tblLevelClone->setName('Stufenübergreifende Klassen');
                    $tblLevelShowList[] = $tblLevelClone;
                } else {
                    $tblLevelShowList[] = $tblLevel;
                }
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Bildung: Schuljahr '.new DangerText('*'),
                            array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1)))
                    ), 3),
                    new FormColumn(array(
                        new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Bildung: Schulart',
                            array('Name' => Type::useService()->getTypeAll()))
                    ), 3),
                    new FormColumn(array(
                        new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Klasse: Stufe',
                            array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList))
                    ), 3),
                    new FormColumn(array(
                        new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Klasse: Gruppe',
                            'Klasse: Gruppe',
                            array('Name' => Division::useService()->getDivisionAll()))
                    ), 3),
                )),
                new FormRow(
                    new FormColumn(
                        (new Primary('Filter',
                            ApiMassReplace::getEndpoint(),
                            null,
                            $this->getGlobal()->POST))->ajaxPipelineOnClick(ApiMassReplace::pipelineOpen($Field))
                    )
                ),
                new FormRow(
                    new FormColumn(
                        new DangerText('*'.new Small('Pflichtfeld'))
                    )
                )
            ))
//                , new Primary('Filtern'), '',
//                $this->getGlobal()->POST))->ajaxPipelineOnSubmit(ApiMassReplace::pipelineOpen($Field))
        ))->disableSubmitAction();
    }

    /**
     * @param string      $modalField
     * @param null|string $Year
     * @param null|string $Division
     * @param null|string $Node
     *
     * @return Layout
     * // Content for OpenModal -> ApiMassReplace
     */
    public function getFrontendStudentFilter($modalField, $Year = null, $Division = null, $Node = null)
    {
        /** @var SelectBox|TextField $Field */
        $Field = unserialize(base64_decode($modalField));
        $CloneField = (new ApiMassReplace())->cloneField($Field, 'CloneField', 'Auswahl/Eingabe '
            .new SuccessText($Node).' - '.$Field->getLabel());

        $TableContent = $this->getStudentFilterResult($Year, $Division, $Field);

        $Table = (new TableData($TableContent, null,
            array(
                'Check'         => 'Auswahl',
                'Name'          => 'Name',
                'StudentNumber' => 'Schülernummer',
                'Level'         => 'Stufe',
                'Division'      => 'Klasse',
                'Edit'          => $Field->getLabel(),
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 3),
                ),
                'order'      => array(array(1, 'asc')),
                'pageLength' => -1,
                'paging'     => false,
                'info'       => false,
                'searching'  => false,
                'responsive' => false
            )))->setHash('MassReplaceStudent' . $Node . $Field->getLabel());

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
//                    new LayoutColumn(
//                        new Panel($Node, '', Panel::PANEL_TYPE_PRIMARY)
//                    ),
                    new LayoutColumn(
                        new Danger('Achtung: Die Massenänderung kann nicht automatisch rückgängig gemacht werden!')
                    ),
                    new LayoutColumn(new Well(
                        ApiMassReplace::receiverFilter('Filter', $this->formStudentFilter($modalField))
                    )),
                    new LayoutColumn(new Well(
                        (new Form(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn(
                                        new Panel('Weitere Personen ('.new Bold(count($TableContent)).' nach Filterung):',
                                            (!empty($TableContent)
                                                ? new ToggleCheckbox('Alle wählen/abwählen', $Table).$Table
                                                : new Warning('Keine Personen gefunden '.
                                                    new ToolTip(new Info(), 'Das Schuljahr ist ein Pflichtfeld'))),
                                            Panel::PANEL_TYPE_INFO
                                        )
                                    ),
                                    new FormColumn(
                                        $CloneField
                                    )
                                )),
                                new FormRow(
                                    new FormColumn(
                                        (new Primary('Speichern', ApiMassReplace::getEndpoint(), new Save(),
                                            $this->getGlobal()->POST))->ajaxPipelineOnClick(ApiMassReplace::pipelineSave($Field))
                                    )
                                )
                            ))
                        ))->disableSubmitAction()
                    ))
                ))
            )
        );
    }

    /**
     * @param null          $Year
     * @param null          $Division
     * @param AbstractField $Field
     *
     * @return array $SearchResult
     *
     */
    private function getStudentFilterResult($Year = null, $Division = null, AbstractField $Field)
    {
        /** @var SelectBox|TextField $Field */
        $Label = $Field->getLabel();

        $tblStudentTransferType = false;
        if (preg_match('!([Meta]*)(\[[Transfer]*\])\[([\d]*)\](\[[\w]*\])!is', $Field->getName(), $matches)) {
//            return new Code(print_r($Matches, true));
            if (isset($matches[3])) {
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeById($matches[3]);
            }
        }


        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = '';

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            // Preparation Filter
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Year = array_filter($Year);
//            // Preparation FilterPerson
//            $Filter['Person'] = array();

            // Preparation $FilterType
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $Division = array_filter($Division);
            } else {
                $Division = array();
            }

            $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $Result = $Pile->searchPile(array(
                0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                1 => array(),   // empty Person search
                2 => $Division,
                3 => $Year
            ));
        }

        $SearchResult = array();
        if ($Result != '') {
            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Check'] = '';
                $DataPerson['Edit'] = ''; // get content by Field->getLabel()

                if ($tblPerson) {
                    $DataPerson['Check'] = (new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ',
                        $tblPerson->getId()
                        , array($tblPerson->getId())))->setChecked();
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
//                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        // Grunddaten
                        if($Label == 'Prefix'){
                            $DataPerson['Edit'] = $tblStudent->getPrefix();
                        }
                        if($Label == 'Beginnt am'){
                            $DataPerson['Edit'] = $tblStudent->getSchoolAttendanceStartDate();
                        }
                        // Transfer
                        if ($tblStudentTransferType) {
//                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblStudentTransferType);
                            if ($tblStudentTransfer) {
                                // Ersteinschulung
                                if (($tblCompany = $tblStudentTransfer->getServiceTblCompany()) && $Label == 'Schule'
                                    && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                ) {
                                    $DataPerson['Edit'] = $tblCompany->getName();
                                }
                                if (($tblType = $tblStudentTransfer->getServiceTblType()) && $Label == 'Schulart'
                                    && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                ) {
                                    $DataPerson['Edit'] = $tblType->getName();
                                }
                                if (($tblStudentSchoolEnrollmentType = $tblStudentTransfer->getTblStudentSchoolEnrollmentType())
                                    && $Label == 'Einschulungsart'
                                    && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                ) {
                                    $DataPerson['Edit'] = $tblStudentSchoolEnrollmentType->getName();
                                }
                                if (($tblCourse = $tblStudentTransfer->getServiceTblCourse()) && $Label == 'Bildungsgang'
                                    && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                ) {
                                    $DataPerson['Edit'] = $tblCourse->getName();
                                }
                                if (($transferDate = $tblStudentTransfer->getTransferDate()) && $Label == 'Datum'
                                    && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                ) {
                                    $DataPerson['Edit'] = $transferDate;
                                }

                                // Schüler - Aufnahme
                                if (($tblCompany = $tblStudentTransfer->getServiceTblCompany()) && $Label == 'Abgebende Schule / Kita'
                                    && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                ) {
                                    $DataPerson['Edit'] = $tblCompany->getName();
                                }
                                if (($tblStateCompany = $tblStudentTransfer->getServiceTblStateCompany()) && $Label == 'Staatliche Stammschule'
                                    && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                ) {
                                    $DataPerson['Edit'] = $tblStateCompany->getName();
                                }
                                if (($tblType = $tblStudentTransfer->getServiceTblType()) && $Label == 'Letzte Schulart'
                                    && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                ) {
                                    $DataPerson['Edit'] = $tblType->getName();
                                }
                                if (($tblCourse = $tblStudentTransfer->getServiceTblCourse()) && $Label == 'Letzter Bildungsgang'
                                    && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                ) {
                                    $DataPerson['Edit'] = $tblCourse->getName();
                                }
                                if (($transferDate = $tblStudentTransfer->getTransferDate()) && $Label == 'Datum'
                                    && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                ) {
                                    $DataPerson['Edit'] = $transferDate;
                                }

                                // Schüler - Abgabe
                                if (($tblCompany = $tblStudentTransfer->getServiceTblCompany()) && $Label == 'Aufnehmende Schule'
                                    && $tblStudentTransferType->getIdentifier() == 'LEAVE'
                                ) {
                                    $DataPerson['Edit'] = $tblCompany->getName();
                                }
                                if (($tblType = $tblStudentTransfer->getServiceTblType()) && $Label == 'Letzte Schulart'
                                    && $tblStudentTransferType->getIdentifier() == 'LEAVE'
                                ) {
                                    $DataPerson['Edit'] = $tblType->getName();
                                }
                                if (($tblCourse = $tblStudentTransfer->getServiceTblCourse()) && $Label == 'Letzter Bildungsgang'
                                    && $tblStudentTransferType->getIdentifier() == 'LEAVE'
                                ) {
                                    $DataPerson['Edit'] = $tblCourse->getName();
                                }
                                if (($transferDate = $tblStudentTransfer->getTransferDate()) && $Label == 'Datum'
                                    && $tblStudentTransferType->getIdentifier() == 'LEAVE'
                                ) {
                                    $DataPerson['Edit'] = $transferDate;
                                }

                                // Schulverlauf
                                if (($tblCompany = $tblStudentTransfer->getServiceTblCompany()) && $Label == 'Aktuelle Schule'
                                    && $tblStudentTransferType->getIdentifier() == 'PROCESS'
                                ) {
                                    $DataPerson['Edit'] = $tblCompany->getName();
                                }
                                if (($tblCourse = $tblStudentTransfer->getServiceTblCourse()) && $Label == 'Aktueller Bildungsgang'
                                    && $tblStudentTransferType->getIdentifier() == 'PROCESS'
                                ) {
                                    $DataPerson['Edit'] = $tblCourse->getName();
                                }
//                                if(( $tblType = $tblStudentTransfer->getServiceTblType()) && $Label == 'Aktuelle Schulart'){
//                                $DataPerson['Edit'] = $tblType->getName();
//                                }
                            }
                        }
                        // Subject
                        if ($Label == 'Religion') {
                            $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION');
                            $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                            $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                $tblStudentSubjectType, $tblStudentSubjectRanking);
                            if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                $DataPerson['Edit'] = new Muted('('.$tblSubject->getAcronym().') ').$tblSubject->getName();
                            }
                        }
                        if ($Label == 'Profil') {
                            $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE');
                            $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                            $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                $tblStudentSubjectType, $tblStudentSubjectRanking);
                            if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                $DataPerson['Edit'] = new Muted('('.$tblSubject->getAcronym().') ').$tblSubject->getName();
                            }
                        }
                        if ($Label == 'Neigungskurs') {
                            $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION');
                            $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                            $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                $tblStudentSubjectType, $tblStudentSubjectRanking);
                            if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                $DataPerson['Edit'] = new Muted('('.$tblSubject->getAcronym().') ').$tblSubject->getName();
                            }
                        }
                        for ($i = 1; $i < 6; $i++){
                            if ($Label == $i . '. Fremdsprache'){
                                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                                $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $DataPerson['Edit'] = new Muted('('.$tblSubject->getAcronym().') ').$tblSubject->getName();
                                }
                            }
                            if ($Label == $i . '. Wahlfach'){
                                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ELECTIVE');
                                $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $DataPerson['Edit'] = new Muted('('.$tblSubject->getAcronym().') ').$tblSubject->getName();
                                }
                            }
                            if ($Label == $i . '. Arbeitsgemeinschaft'){
                                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM');
                                $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $DataPerson['Edit'] = new Muted('('.$tblSubject->getAcronym().') ').$tblSubject->getName();
                                }
                            }
                            if ($Label == new Muted(new Small($i . '. Fremdsprache von Klasse'))){
                                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                                $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $DataPerson['Edit'] = ($tblStudentSubject->getServiceTblLevelFrom() ? $tblStudentSubject->getServiceTblLevelFrom()->getName() . ' ' : '')
                                        . new Muted('('.$tblSubject->getAcronym().') ');
                                }
                            }
                            if ($Label == new Muted(new Small($i . '. Fremdsprache bis Klasse'))){
                                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                                $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $DataPerson['Edit'] = ($tblStudentSubject->getServiceTblLevelTill() ? $tblStudentSubject->getServiceTblLevelTill()->getName() . ' ' : '')
                                        . new Muted('('.$tblSubject->getAcronym().') ');
                                }
                            }
                        }

                        // TechnicalSchool
                        if (($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())) {
                            if ($Label == 'Bildungsgang / Berufsbezeichnung / Ausbildung') {
                                if (($tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())) {
                                    $DataPerson['Edit'] = $tblTechnicalCourse->getName();
                                }
                            }
                            if ($Label == 'Fachrichtung') {
                                if (($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())) {
                                    $DataPerson['Edit'] = $tblTechnicalSubjectArea->getName();
                                }
                            }
                        }

                    }
                }
                $DataPerson['Division'] = '';
                $DataPerson['Level'] = '';

                $tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']);
                if ($tblDivision) {
                    $DataPerson['Division'] = $tblDivision->getName();
                    $tblLevel = $tblDivision->getTblLevel();
                    if ($tblLevel) {
                        $DataPerson['Level'] = $tblLevel->getName();
                    }
                }
//                /** @noinspection PhpUndefinedFieldInspection */
//                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
//                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
//                    /** @noinspection PhpUndefinedFieldInspection */
//                    $DataPerson['Address'] = $tblAddress->getGuiString();
//                }
                $DataPerson['StudentNumber'] = '';
                if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                    $DataPerson['StudentNumber'] = $tblStudent->getIdentifierComplete();
                }

                if (!isset($DataPerson['ProspectYear'])) {
                    $DataPerson['ProspectYear'] = new Small(new Muted('-NA-'));
                }
                if (!isset($DataPerson['ProspectDivision'])) {
                    $DataPerson['ProspectDivision'] = new Small(new Muted('-NA-'));
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                        $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }

        return $SearchResult;
    }
}