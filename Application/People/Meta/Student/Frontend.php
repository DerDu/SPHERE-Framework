<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Common\Frontend\Form\Repository\Aspect;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Bus;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Heart;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Medicine;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\Stethoscope;
use SPHERE\Common\Frontend\Icon\Repository\StopSign;
use SPHERE\Common\Frontend\Icon\Repository\TempleChurch;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array     $Meta
     *
     * @param null      $Group
     *
     * @return Stage
     */
    public function frontendMeta(TblPerson $tblPerson = null, $Meta = array(), $Group = null)
    {

        $Stage = new Stage();

        $Stage->setDescription(
            new Danger(
                new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

        $Stage->setContent(
            Student::useService()->createMeta(
                (new Form(array(
                    new FormGroup(
                        new FormRow(array(
                            new FormColumn(
                                new Panel('Identifikation', array(
                                    new TextField('Meta[Student][Identifier]', 'Schülernummer',
                                        'Schülernummer')
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new FormColumn(
                                new Panel('Schulpflicht', array(
                                    new DatePicker('Meta[Student][SchoolAttendanceStartDate]', '', 'Beginnt am', new Calendar())
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                        ))
                    ),
                    $this->formGroupTransfer($tblPerson),
                    $this->formGroupGeneral($tblPerson),
                    $this->formGroupSubject($tblPerson),
                    $this->formGroupIntegration($tblPerson),
                ), new Primary('Speichern', new Save()))
                )->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.')
                , $tblPerson, $Meta, $Group
            )
        );

        return $Stage;
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return FormGroup
     */
    private function formGroupTransfer(TblPerson $tblPerson = null)
    {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Meta']['Transfer'] )) {
                /** @var TblStudent $tblStudent */
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $TransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
                    /** @var TblStudentTransfer $tblStudentTransferEnrollment */
                    $tblStudentTransferEnrollment = Student::useService()->getStudentTransferByType(
                        $tblStudent, $TransferTypeEnrollment
                    );
                    if ($tblStudentTransferEnrollment) {
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['School'] = (
                        $tblStudentTransferEnrollment->getServiceTblCompany()
                            ? $tblStudentTransferEnrollment->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Type'] = (
                        $tblStudentTransferEnrollment->getServiceTblType()
                            ? $tblStudentTransferEnrollment->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Course'] = (
                        $tblStudentTransferEnrollment->getServiceTblCourse()
                            ? $tblStudentTransferEnrollment->getServiceTblCourse()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Date'] = $tblStudentTransferEnrollment->getTransferDate();
                        $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Remark'] = $tblStudentTransferEnrollment->getRemark();
                    }

                    $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
                    /** @var TblStudentTransfer $tblStudentTransferArrive */
                    $tblStudentTransferArrive = Student::useService()->getStudentTransferByType(
                        $tblStudent, $TransferTypeArrive
                    );
                    if ($tblStudentTransferArrive) {
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['School'] = (
                        $tblStudentTransferArrive->getServiceTblCompany()
                            ? $tblStudentTransferArrive->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Type'] = (
                        $tblStudentTransferArrive->getServiceTblType()
                            ? $tblStudentTransferArrive->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Course'] = (
                        $tblStudentTransferArrive->getServiceTblCourse()
                            ? $tblStudentTransferArrive->getServiceTblCourse()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Date'] = $tblStudentTransferArrive->getTransferDate();
                        $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Remark'] = $tblStudentTransferArrive->getRemark();
                    }

                    $TransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
                    /** @var TblStudentTransfer $tblStudentTransferLeave */
                    $tblStudentTransferLeave = Student::useService()->getStudentTransferByType(
                        $tblStudent, $TransferTypeLeave
                    );
                    if ($tblStudentTransferLeave) {
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['School'] = (
                        $tblStudentTransferLeave->getServiceTblCompany()
                            ? $tblStudentTransferLeave->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Type'] = (
                        $tblStudentTransferLeave->getServiceTblType()
                            ? $tblStudentTransferLeave->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Course'] = (
                        $tblStudentTransferLeave->getServiceTblCourse()
                            ? $tblStudentTransferLeave->getServiceTblCourse()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Date'] = $tblStudentTransferLeave->getTransferDate();
                        $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Remark'] = $tblStudentTransferLeave->getRemark();
                    }

                    $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
                    /** @var TblStudentTransfer $tblStudentTransferProcess */
                    $tblStudentTransferProcess = Student::useService()->getStudentTransferByType(
                        $tblStudent, $TransferTypeProcess
                    );
                    if ($tblStudentTransferProcess) {
                        $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['School'] = (
                        $tblStudentTransferProcess->getServiceTblCompany()
                            ? $tblStudentTransferProcess->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Type'] = (
                        $tblStudentTransferProcess->getServiceTblType()
                            ? $tblStudentTransferProcess->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Course'] = (
                        $tblStudentTransferProcess->getServiceTblCourse()
                            ? $tblStudentTransferProcess->getServiceTblCourse()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Remark'] = $tblStudentTransferProcess->getRemark();
                    }

                    $Global->savePost();
                }
            }
        }

        $VisitedDivisions = array();
        $RepeatedLevels = array();
        if ($tblPerson !== null) {
            $tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
            if ($tblDivisionStudentAllByPerson) {
                foreach ($tblDivisionStudentAllByPerson as &$tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        $tblLevel = $tblDivision->getTblLevel();
                        $tblYear = $tblDivision->getServiceTblYear();
                        if ($tblLevel && $tblYear) {
                            $VisitedDivisions[] = $tblYear->getDisplayName() . ' Klasse ' . $tblDivision->getDisplayName();;
                            foreach ($tblDivisionStudentAllByPerson as &$tblDivisionStudentTemp) {
                                if ($tblDivisionStudent->getId() !== $tblDivisionStudentTemp->getId()
                                    && $tblDivisionStudentTemp->getTblDivision()
                                    && (
                                        $tblDivisionStudentTemp->getTblDivision()->getTblLevel()
                                        && $tblDivisionStudent->getTblDivision()->getTblLevel()->getId()
                                        === $tblDivisionStudentTemp->getTblDivision()->getTblLevel()->getId()
                                    )
                                ) {
                                    $RepeatedLevels[] = $tblYear->getDisplayName().' Klasse '.$tblLevel->getName();
                                }
                            }
                        }
                    }
                }

                if (!empty( $VisitedDivisions )) {
                    rsort($VisitedDivisions);
                }
            }
        }

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );

        $tblCompanyAllSchoolNursery = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('NURSERY')
        );
        if ($tblCompanyAllSchoolNursery) {
            $tblCompanyAllSchoolNursery = array_merge($tblCompanyAllSchool, $tblCompanyAllSchoolNursery);
        } else {
            $tblCompanyAllSchoolNursery = $tblCompanyAllSchool;
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();

        $tblSchoolCourseAll = Course::useService()->getCourseAll();
        if ($tblSchoolCourseAll) {
            array_push($tblSchoolCourseAll, new TblCourse());
        } else {
            $tblSchoolCourseAll = array(new TblCourse());
        }

        $tblStudentTransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
        $tblStudentTransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
        $tblStudentTransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
        $tblStudentTransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Ersteinschulung', array(
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][School]',
                            'Schule', array(
                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
                            ), new Education()),
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Type]',
                            'Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education()),
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Course]',
                            'Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education()),
                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Date]',
                            'Datum', 'Datum', new Calendar()),
                        new TextArea('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schüler - Aufnahme', array(
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][School]',
                            'Abgebende Schule / Kita', array(
                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchoolNursery
                            ), new Education()),
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Type]',
                            'Letzte Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education()),
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Course]',
                            'Letzter Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education()),
                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Date]',
                            'Datum',
                            'Datum', new Calendar()),
                        new TextArea('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schüler - Abgabe', array(
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][School]',
                            'Aufnehmende Schule', array(
                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
                            ), new Education()),
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Type]',
                            'Letzte Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education()),
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Course]',
                            'Letzter Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education()),
                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Date]',
                            'Datum',
                            'Datum', new Calendar()),
                        new TextArea('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Schulverlauf', array(
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][School]',
                            'Aktuelle Schule', array(
                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
                            ), new Education()),
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Type]',
                            'Aktuelle Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll,
                            ), new Education()),
                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Course]',
                            'Aktueller Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll,
                            ), new Education()),
                        new TextArea('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 6),
                new FormColumn(array(
                    new Panel('Besuchte Schulklassen',
                        $VisitedDivisions,
                        Panel::PANEL_TYPE_DEFAULT,
                        new Warning(
                            'Vom System erkannte Besuche. Wird bei Klassen&shy;zuordnung in Schuljahren erzeugt'
                        )
                    ),
                ), 3),
                new FormColumn(array(
                    new Panel('Aktuelle Schuljahrwiederholungen',
                        $RepeatedLevels,
                        Panel::PANEL_TYPE_DEFAULT,
                        new Warning(
                            'Vom System erkannte Schuljahr&shy;wiederholungen.'
                            .'Wird bei wiederholter Klassen&shy;zuordnung in verschiedenen Schuljahren erzeugt'
                        )
                    ),
                ), 3),
            )),
        ), new Title(new TileSmall().' Schülertransfer ', new Bold(new Success($tblPerson->getFullName()))));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return FormGroup
     */
    private function formGroupGeneral(
        TblPerson $tblPerson = null
    ) {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Meta']['MedicalRecord'] )) {
                /** @var TblStudent $tblStudent */
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {

                    $Global->POST['Meta']['Student']['Identifier'] = $tblStudent->getIdentifier();
                    $Global->POST['Meta']['Student']['SchoolAttendanceStartDate'] = $tblStudent->getSchoolAttendanceStartDate();

                    /** @var TblStudentMedicalRecord $tblStudentMedicalRecord */
                    $tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord();
                    if ($tblStudentMedicalRecord) {
                        $Global->POST['Meta']['MedicalRecord']['Disease'] = $tblStudentMedicalRecord->getDisease();
                        $Global->POST['Meta']['MedicalRecord']['Medication'] = $tblStudentMedicalRecord->getMedication();
                        $Global->POST['Meta']['MedicalRecord']['AttendingDoctor'] = (
                        $tblStudentMedicalRecord->getServiceTblPersonAttendingDoctor()
                            ? $tblStudentMedicalRecord->getServiceTblPersonAttendingDoctor()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['MedicalRecord']['Insurance']['State'] = $tblStudentMedicalRecord->getInsuranceState();
                        $Global->POST['Meta']['MedicalRecord']['Insurance']['Company'] = $tblStudentMedicalRecord->getInsurance();
                    }

                    $tblStudentLocker = $tblStudent->getTblStudentLocker();
                    if ($tblStudentLocker) {
                        $Global->POST['Meta']['Additional']['Locker']['Number'] = $tblStudentLocker->getLockerNumber();
                        $Global->POST['Meta']['Additional']['Locker']['Location'] = $tblStudentLocker->getLockerLocation();
                        $Global->POST['Meta']['Additional']['Locker']['Key'] = $tblStudentLocker->getKeyNumber();
                    }

                    $tblStudentBaptism = $tblStudent->getTblStudentBaptism();
                    if ($tblStudentBaptism) {
                        $Global->POST['Meta']['Additional']['Baptism']['Date'] = $tblStudentBaptism->getBaptismDate();
                        $Global->POST['Meta']['Additional']['Baptism']['Location'] = $tblStudentBaptism->getLocation();
                    }

                    $tblStudentTransport = $tblStudent->getTblStudentTransport();
                    if ($tblStudentTransport) {
                        $Global->POST['Meta']['Transport']['Route'] = $tblStudentTransport->getRoute();
                        $Global->POST['Meta']['Transport']['Station']['Entrance'] = $tblStudentTransport->getStationEntrance();
                        $Global->POST['Meta']['Transport']['Station']['Exit'] = $tblStudentTransport->getStationExit();
                        $Global->POST['Meta']['Transport']['Remark'] = $tblStudentTransport->getRemark();
                    }

                    $tblStudentBilling = $tblStudent->getTblStudentBilling();
                    if ($tblStudentBilling) {
                        if ($tblStudentBilling->getServiceTblSiblingRank()) {
                            $Global->POST['Meta']['Billing'] = $tblStudentBilling->getServiceTblSiblingRank()->getId();
                        }
                    }

                    $tblStudentAgreementAll = Student::useService()->getStudentAgreementAllByStudent($tblStudent);
                    if ($tblStudentAgreementAll) {
                        foreach ($tblStudentAgreementAll as $tblStudentAgreement) {
                            $Global->POST['Meta']['Agreement']
                            [$tblStudentAgreement->getTblStudentAgreementType()->getTblStudentAgreementCategory()->getId()]
                            [$tblStudentAgreement->getTblStudentAgreementType()->getId()] = 1;
                        }
                    }

                    $tblStudentLiberationAll = Student::useService()->getStudentLiberationAllByStudent($tblStudent);
                    if ($tblStudentLiberationAll) {
                        foreach ($tblStudentLiberationAll as $tblStudentLiberation) {
                            $Global->POST['Meta']['Liberation']
                            [$tblStudentLiberation->getTblStudentLiberationType()->getTblStudentLiberationCategory()->getId()]
                                = $tblStudentLiberation->getTblStudentLiberationType()->getId();
                        }
                    }

                    $Global->savePost();
                }
            }
        }

        /**
         * Panel: Agreement
         */
        $tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll();
        $AgreementPanel = array();
        array_walk($tblAgreementCategoryAll,
            function (TblStudentAgreementCategory $tblStudentAgreementCategory) use (&$AgreementPanel) {

                array_push($AgreementPanel, new Aspect(new Bold($tblStudentAgreementCategory->getName())));
                $tblAgreementTypeAll = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
                array_walk($tblAgreementTypeAll,
                    function (TblStudentAgreementType $tblStudentAgreementType) use (
                        &$AgreementPanel,
                        $tblStudentAgreementCategory
                    ) {

                        array_push($AgreementPanel,
                            new CheckBox('Meta[Agreement]['.$tblStudentAgreementCategory->getId().']['.$tblStudentAgreementType->getId().']',
                                $tblStudentAgreementType->getName(), 1)
                        );
                    }
                );
            }
        );
        $AgreementPanel = new Panel('Einverständniserklärung zur Datennutzung', $AgreementPanel,
            Panel::PANEL_TYPE_INFO);

        /**
         * Panel: Liberation
         */
        $tblLiberationCategoryAll = Student::useService()->getStudentLiberationCategoryAll();
        $LiberationPanel = array();
        array_walk($tblLiberationCategoryAll,
            function (TblStudentLiberationCategory $tblStudentLiberationCategory) use (&$LiberationPanel) {

                $tblLiberationTypeAll = Student::useService()->getStudentLiberationTypeAllByCategory($tblStudentLiberationCategory);
                array_push($LiberationPanel,
                    new SelectBox('Meta[Liberation]['.$tblStudentLiberationCategory->getId().']',
                        $tblStudentLiberationCategory->getName(), array(
                            '{{ Name }}' => $tblLiberationTypeAll
                        ))
                );
            }
        );
        $LiberationPanel = new Panel('Unterichtsbefreihung', $LiberationPanel, Panel::PANEL_TYPE_INFO);

        $tblSiblingRankAll = Relationship::useService()->getSiblingRankAll();
        $tblSiblingRankAll[] = new TblSiblingRank();

        /**
         * Form
         */
        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(new Hospital().' Krankenakte', array(
                        new TextArea('Meta[MedicalRecord][Disease]', 'Krankheiten / Allergien',
                            'Krankheiten / Allergien', new Heart()),
                        new TextArea('Meta[MedicalRecord][Medication]', 'Mediakamente', 'Mediakamente',
                            new Medicine()),
                        new SelectBox('Meta[MedicalRecord][AttendingDoctor]', 'Behandelnder Arzt', array(),
                            new Stethoscope()),
                        // ToDo -> extra Tabelle für Statustypen
                        new SelectBox('Meta[MedicalRecord][Insurance][State]', 'Versicherungsstatus', array(
                            0 => '',
                            1 => 'Pflicht',
                            2 => 'Freiwillig',
                            3 => 'Privat',
                            4 => 'Familie Vater',
                            5 => 'Familie Mutter',
                        ), new Lock()),
                        new AutoCompleter('Meta[MedicalRecord][Insurance][Company]', 'Krankenkasse', 'Krankenkasse',
                            array(), new Shield()),
                    ), Panel::PANEL_TYPE_DANGER), 3),
                new FormColumn(array(
                    new Panel('Fakturierung', array(
                        new SelectBox('Meta[Billing]', 'Geschwisterkind', array('{{Name}}' => $tblSiblingRankAll),
                            new Child()),
                    ), Panel::PANEL_TYPE_INFO),
                    new Panel('Schließfach', array(
                        new TextField('Meta[Additional][Locker][Number]', 'Schließfachnummer', 'Schließfachnummer',
                            new Lock()),
                        new TextField('Meta[Additional][Locker][Location]', 'Schließfach Standort',
                            'Schließfach Standort', new MapMarker()),
                        new TextField('Meta[Additional][Locker][Key]', 'Schlüssel Nummer', 'Schlüssel Nummer',
                            new Key()),
                    ), Panel::PANEL_TYPE_INFO),
                    new Panel('Taufe', array(
                        new DatePicker('Meta[Additional][Baptism][Date]', 'Taufdatum', 'Taufdatum',
                            new TempleChurch()
                        ),
                        new TextField('Meta[Additional][Baptism][Location]', 'Taufort', 'Taufort', new MapMarker()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 3),
                new FormColumn(array(
                    new Panel('Schulbeförderung', array(
                        new TextField('Meta[Transport][Route]', 'Buslinie', 'Buslinie', new Bus()),
                        new TextField('Meta[Transport][Station][Entrance]', 'Einstiegshaltestelle',
                            'Einstiegshaltestelle', new StopSign()),
                        new TextField('Meta[Transport][Station][Exit]', 'Ausstiegshaltestelle',
                            'Ausstiegshaltestelle', new StopSign()),
                        new TextArea('Meta[Transport][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                    $LiberationPanel
                ), 3),
                new FormColumn($AgreementPanel, 3),
            )),
        ), new Title(new TileSmall().' Allgemeines', new Bold(new Success($tblPerson->getFullName()))));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return FormGroup
     */
    private    function formGroupSubject(
        TblPerson $tblPerson = null
    ) {

        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        $Global = $this->getGlobal();

        if ($tblStudent && !isset( $Global->POST['Meta']['Subject'] )) {

            $tblStudentSubjectAll = Student::useService()->getStudentSubjectAllByStudent($tblStudent);
            if ($tblStudentSubjectAll) {

                array_walk($tblStudentSubjectAll, function (TblStudentSubject $tblStudentSubject) use (&$Global) {

                    $Type = $tblStudentSubject->getTblStudentSubjectType()->getId();
                    $Ranking = $tblStudentSubject->getTblStudentSubjectRanking()->getId();
                    $Subject = $tblStudentSubject->getServiceTblSubject() ? $tblStudentSubject->getServiceTblSubject()->getId() : 0;
                    $Global->POST['Meta']['Subject'][$Type][$Ranking] = $Subject;
                });
                $Global->savePost();
            }
        }

        // Orientation
        $tblSubjectOrientation = Subject::useService()->getSubjectOrientationAll();
        if ($tblSubjectOrientation) {
            array_push($tblSubjectOrientation, new TblSubject());
        } else {
            $tblSubjectOrientation = array(new TblSubject());
        }

        // Advanced
        $tblSubjectAdvanced = Subject::useService()->getSubjectAdvancedAll();
        if ($tblSubjectAdvanced) {
            array_push($tblSubjectAdvanced, new TblSubject());
        } else {
            $tblSubjectAdvanced = array(new TblSubject());
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

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    $this->panelSubjectList('FOREIGN_LANGUAGE', 'Fremdsprachen', 'Fremdsprache',
                        $tblSubjectForeignLanguage, 4, ( $tblStudent ? $tblStudent : null )),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('RELIGION', 'Religion', 'Religion', $tblSubjectReligion, 1),
                    $this->panelSubjectList('PROFILE', 'Profile', 'Profil', $tblSubjectProfile, 1),
                    $this->panelSubjectList('ORIENTATION', 'Neigungskurse', 'Neigungskurs', $tblSubjectOrientation,
                        1),
                    $this->panelSubjectList('ADVANCED', 'Vertiefungskurse', 'Vertiefungskurs', $tblSubjectAdvanced,
                        1),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('ELECTIVE', 'Wahlfächer', 'Wahlfach', $tblSubjectElective, 2),
                    $this->panelSubjectList('TEAM', 'Arbeitsgemeinschaften', 'Arbeitsgemeinschaft', $tblSubjectAll,
                        3),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('TRACK_INTENSIVE', 'Leistungskurse', 'Leistungskurs', $tblSubjectAll,
                        2),
                    $this->panelSubjectList('TRACK_BASIC', 'Grundkurse', 'Grundkurs', $tblSubjectAll, 8),
                ), 3),
            )),
        ), new Title(new TileSmall().' Unterrichtsfächer', new Bold(new Success($tblPerson->getFullName()))));
    }

    /**
     * @param string       $Identifier
     * @param string       $Title
     * @param string       $Label
     * @param TblSubject[] $SubjectList
     * @param int          $Count
     * @param TblStudent   $tblStudent
     *
     * @return Panel
     */
    private function panelSubjectList(
        $Identifier,
        $Title,
        $Label,
        $SubjectList,
        $Count = 1,
        TblStudent $tblStudent = null
    ) {

        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(strtoupper($Identifier));
        $Panel = array();
        for ($Rank = 1; $Rank <= $Count; $Rank++) {
            $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($Rank);
            array_push($Panel,
                new SelectBox(
                    'Meta[Subject]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                    ( $Count > 1 ? $tblStudentSubjectRanking->getName().' ' : '' ).$Label,
                    array('{{ Acronym }} - {{ Name }} {{ Description }}' => $SubjectList),
                    new Education()
                )
            );
            // Student FOREIGN_LANGUAGE: LevelFrom, LevelTill
            if ($tblStudentSubjectType->getIdentifier() == 'FOREIGN_LANGUAGE') {
                $tblLevelAll = Division::useService()->getLevelAll();
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
                    new SelectBox(
                        'Meta[SubjectLevelFrom]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        new Muted(new Small('von Klasse')),
                        array('{{ Name }} {{ ServiceTblType.Name }}' => $tblLevelAll),
                        new Time()
                    )
                );
                array_push($Panel,
                    new SelectBox(
                        'Meta[SubjectLevelTill]['.$tblStudentSubjectType->getId().']['.$tblStudentSubjectRanking->getId().']',
                        new Muted(new Small('bis Klasse')),
                        array('{{ Name }} {{ ServiceTblType.Name }}' => $tblLevelAll),
                        new Time()
                    )
                );
            }
        }
        return new Panel($Title, $Panel, Panel::PANEL_TYPE_INFO);
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return FormGroup
     */
    private function formGroupIntegration(
        TblPerson $tblPerson = null
    ) {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Meta']['Integration'] )) {

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);

                if ($tblStudent) {

                    $tblStudentIntegration = $tblStudent->getTblStudentIntegration();
                    if ($tblStudentIntegration) {

                        $Global->POST['Meta']['Integration']['Coaching']['Required'] = $tblStudentIntegration->getCoachingRequired() ? 1 : 0;
                        $Global->POST['Meta']['Integration']['Coaching']['CounselDate'] = $tblStudentIntegration->getCoachingCounselDate();
                        $Global->POST['Meta']['Integration']['Coaching']['RequestDate'] = $tblStudentIntegration->getCoachingRequestDate();
                        $Global->POST['Meta']['Integration']['Coaching']['DecisionDate'] = $tblStudentIntegration->getCoachingDecisionDate();

                        $Global->POST['Meta']['Integration']['School']['Company'] =
                            $tblStudentIntegration->getServiceTblCompany() ? $tblStudentIntegration->getServiceTblCompany()->getId() : 0;
                        $Global->POST['Meta']['Integration']['School']['Person'] =
                            $tblStudentIntegration->getServiceTblPerson() ? $tblStudentIntegration->getServiceTblPerson()->getId() : 0;
                        $Global->POST['Meta']['Integration']['School']['Time'] = $tblStudentIntegration->getCoachingTime();
                        $Global->POST['Meta']['Integration']['School']['Remark'] = $tblStudentIntegration->getCoachingRemark();
                    }

                    $tblStudentDisorderAll = Student::useService()->getStudentDisorderAllByStudent($tblStudent);
                    if ($tblStudentDisorderAll) {
                        foreach ($tblStudentDisorderAll as $tblStudentDisorder) {
                            $Global->POST['Meta']['Integration']['Disorder'][$tblStudentDisorder->getTblStudentDisorderType()->getId()] = 1;
                        }
                    }

                    $tblStudentFocusAll = Student::useService()->getStudentFocusAllByStudent($tblStudent);
                    if ($tblStudentFocusAll) {
                        foreach ($tblStudentFocusAll as $tblStudentFocus) {
                            $Global->POST['Meta']['Integration']['Focus'][$tblStudentFocus->getTblStudentFocusType()->getId()] = 1;
                        }
                    }
                }
            }
            $Global->savePost();
        }

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        if ($tblCompanyAllSchool) {
            array_push($tblCompanyAllSchool, new TblCompany());
        } else {
            $tblCompanyAllSchool = array();
            $tblCompanyAllSchool[] = new TblCompany();
        }

        $PanelDisorder = array();
        $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeAll();
        $tblStudentDisorderType = $this->getSorter($tblStudentDisorderType)->sortObjectBy('Name',
            new StringNaturalOrderSorter());
        array_walk($tblStudentDisorderType,
            function (TblStudentDisorderType $tblStudentDisorderType) use (&$PanelDisorder) {

                array_push($PanelDisorder,
                    new CheckBox('Meta[Integration][Disorder]['.$tblStudentDisorderType->getId().']',
                        $tblStudentDisorderType->getName(), 1)
                );
            });
        $PanelDisorder = new Panel('Förderbedarf: Teilleistungsstörungen', $PanelDisorder, Panel::PANEL_TYPE_INFO);

        $PanelFocus = array();
        $tblStudentFocusType = Student::useService()->getStudentFocusTypeAll();
        $tblStudentFocusType = $this->getSorter($tblStudentFocusType)->sortObjectBy('Name',
            new StringNaturalOrderSorter());
        array_walk($tblStudentFocusType,
            function (TblStudentFocusType $tblStudentFocusType) use (&$PanelFocus) {

                array_push($PanelFocus,
                    new CheckBox('Meta[Integration][Focus]['.$tblStudentFocusType->getId().']',
                        $tblStudentFocusType->getName(), 1)
                );
            });
        $PanelFocus = new Panel('Förderbedarf: Schwerpunkte', $PanelFocus, Panel::PANEL_TYPE_INFO);

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel('Förderantrag / Förderbescheid', array(
                        new CheckBox('Meta[Integration][Coaching][Required]', 'Förderbedarf', 1),
                        new DatePicker('Meta[Integration][Coaching][CounselDate]', 'Förderantrag Beratung',
                            'Förderantrag Beratung',
                            new Calendar()
                        ),
                        new DatePicker('Meta[Integration][Coaching][RequestDate]', 'Förderantrag',
                            'Förderantrag',
                            new Calendar()
                        ),
                        new DatePicker('Meta[Integration][Coaching][DecisionDate]', 'Förderbescheid SBA',
                            'Förderbescheid SBA',
                            new Calendar()
                        )
                    ), Panel::PANEL_TYPE_INFO), 3),
                new FormColumn(
                    new Panel('Förderschule', array(
                        new SelectBox('Meta[Integration][School][Company]', 'Förderschule',
                            array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                            new Education()),
                        new SelectBox('Meta[Integration][School][Person]',
                            'Schulbegleitung '.new Small(new Muted('Integrationsbeauftragter')), array(),
                            new Person()),
                        new NumberField('Meta[Integration][School][Time]', 'Stundenbedarf pro Woche',
                            'Stundenbedarf pro Woche', new Clock()),
                        new TextArea('Meta[Integration][School][Remark]', 'Bemerkungen', 'Bemerkungen',
                            new Pencil()),

                    ), Panel::PANEL_TYPE_INFO), 3),
                new FormColumn($PanelFocus, 3),
                new FormColumn($PanelDisorder, 3),
            )),
        ), new Title(new TileSmall().' Integration', new Bold(new Success($tblPerson->getFullName()))));
    }
}
