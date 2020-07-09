<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudent extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';

    const SIBLINGS_COUNT = 'Sibling_Count';
    // Krankenakte
    const TBL_STUDENT_MEDICAL_RECORD_ATTENDING_DOCTOR = 'TblStudentMedicalRecord_AttendingDoctor';
    const TBL_STUDENT_MEDICAL_RECORD_DISEASE = 'TblStudentMedicalRecord_Disease';
    const TBL_STUDENT_MEDICAL_RECORD_MEDICATION = 'TblStudentMedicalRecord_Medication';
    const TBL_STUDENT_INSURANCE_STATE_NAME = 'TblStudentInsuranceState_Name';
    const TBL_STUDENT_MEDICAL_RECORD_INSURANCE = 'TblStudentMedicalRecord_Insurance';
    const TBL_STUDENT_MEDICAL_RECORD_MASERN_DATE = 'TblStudentMedicalRecord_MasernDate';
    const TBL_STUDENT_MEDICAL_RECORD_MASERN_DOCUMENT_TYPE = 'TblStudentMedicalRecord_MasernDocumentType';
    const TBL_STUDENT_MEDICAL_RECORD_MASERN_CREATOR_TYPE = 'TblStudentMedicalRecord_MasernCreatorType';
    // Taufe
    const TBL_STUDENT_BAPTISM_LOCATION = 'TblStudentBaptism_Location';
    const TBL_STUDENT_BAPTISM_DATE = 'TblStudentBaptism_BaptismDate';
    // Schulbeförderung
    const TBL_STUDENT_TRANSPORT_ROUTE = 'TblStudentTransport_Route';
    const TBL_STUDENT_TRANSPORT_STATION_ENTRANCE = 'TblStudentTransport_StationEntrance';
    const TBL_STUDENT_TRANSPORT_STATION_EXIT = 'TblStudentTransport_StationExit';
    const TBL_STUDENT_TRANSPORT_REMARK = 'TblStudentTransport_Remark';
    // Unterrichtsbefreiung
    // entfernt #SSW-2277
    // Sportbefreiung hinzugefügt #SSW-207
    const TBL_STUDENT_LIBERATION_SPORT_TYPE_NAME = 'TblStudentLiberationSportType_Name';

    const SPORT_LIBERATION = 'SportLiberation';
    // Schließfach
    const TBL_STUDENT_LOCKER_LOCKER_NUMBER = 'TblStudentLocker_LockerNumber';
    const TBL_STUDENT_LOCKER_LOCKER_LOCATION = 'TblStudentLocker_LockerLocation';
    const TBL_STUDENT_LOCKER_KEY_NUMBER = 'TblStudentLocker_KeyNumber';
    // Einverständnis
    const TBL_STUDENT_STUDENT_NAME_AGREEMENT = 'TblStudent_NameAgreement';
    const TBL_STUDENT_STUDENT_PICTURE_AGREEMENT = 'TblStudent_PictureAgreement';

    /**
     * @return array
     */
    static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * @Column(type="string")
     */
    protected $TblPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentBaptism_Location;
    /**
     * @Column(type="string")
     */
    protected $TblStudentBaptism_BaptismDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationSportType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_LockerNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_LockerLocation;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_KeyNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_AttendingDoctor;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Disease;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Medication;
    /**
     * @Column(type="string")
     */
    protected $TblStudentInsuranceState_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Insurance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_MasernDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_MasernDocumentType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_MasernCreatorType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_Route;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_StationEntrance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_StationExit;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_Remark;
    /**
     * @Column(type="string")
     */
    protected $Sibling_Count;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_NameAgreement;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_PictureAgreement;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_STUDENT_BAPTISM_LOCATION, 'Allgemeines: Taufort');
        $this->setNameDefinition(self::TBL_STUDENT_BAPTISM_DATE, 'Allgemeines: Taufedatum');

        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_LOCKER_NUMBER, 'Allgemeines: Schließfachnummer');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_LOCKER_LOCATION, 'Allgemeines: Schließfach Standort');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_KEY_NUMBER, 'Allgemeines: Schließfach Schlüssel Nummer');

        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_ATTENDING_DOCTOR, 'Allgemeines: Behandelnder Arzt');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_DISEASE, 'Allgemeines: Krankheiten / Allergien');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MEDICATION, 'Allgemeines: Medikamente');
        $this->setNameDefinition(self::TBL_STUDENT_INSURANCE_STATE_NAME, 'Allgemeines: Versicherungsstatus');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE, 'Allgemeines: Krankenkasse');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MASERN_DATE, 'Masern: Vorlagedatum Bescheid');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MASERN_DOCUMENT_TYPE, 'Masern: Art der Bescheinigung');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MASERN_CREATOR_TYPE, 'Masern: Bescheinigung durch');

        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_ROUTE, 'Allgemeines: Buslinie');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_STATION_ENTRANCE, 'Allgemeines: Einstiegshaltestelle');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_STATION_EXIT, 'Allgemeines: Ausstiegshaltestelle');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_REMARK, 'Allgemeines: Schulbeförderung Bemerkung');

        $this->setNameDefinition(self::TBL_STUDENT_LIBERATION_SPORT_TYPE_NAME, 'Allgemeines: Sportbefreiung');

        $this->setNameDefinition(self::SIBLINGS_COUNT, 'Allgemeines: Anzahl Geschwister');

        $this->setNameDefinition(self::TBL_STUDENT_STUDENT_NAME_AGREEMENT, 'Allgemeines: Erlaubnis Schülername');
        $this->setNameDefinition(self::TBL_STUDENT_STUDENT_PICTURE_AGREEMENT, 'Allgemeines: Erlaubnis Schülerbild');

//        //GroupDefinition
        $this->setGroupDefinition('&nbsp;', array(
            self::SIBLINGS_COUNT,
            self::TBL_STUDENT_LOCKER_LOCKER_NUMBER,
            self::TBL_STUDENT_LOCKER_LOCKER_LOCATION,
            self::TBL_STUDENT_LOCKER_KEY_NUMBER,
            self::TBL_STUDENT_BAPTISM_LOCATION,
            self::TBL_STUDENT_BAPTISM_DATE,
            self::TBL_STUDENT_MEDICAL_RECORD_ATTENDING_DOCTOR,
            self::TBL_STUDENT_MEDICAL_RECORD_DISEASE,
            self::TBL_STUDENT_MEDICAL_RECORD_MEDICATION,
            self::TBL_STUDENT_INSURANCE_STATE_NAME,
            self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE,
            self::TBL_STUDENT_MEDICAL_RECORD_MASERN_DATE,
            self::TBL_STUDENT_MEDICAL_RECORD_MASERN_DOCUMENT_TYPE,
            self::TBL_STUDENT_MEDICAL_RECORD_MASERN_CREATOR_TYPE,
            self::TBL_STUDENT_TRANSPORT_ROUTE,
            self::TBL_STUDENT_TRANSPORT_STATION_ENTRANCE,
            self::TBL_STUDENT_TRANSPORT_STATION_EXIT,
            self::TBL_STUDENT_TRANSPORT_REMARK,
            self::TBL_STUDENT_LIBERATION_SPORT_TYPE_NAME,
            self::TBL_STUDENT_STUDENT_NAME_AGREEMENT,
            self::TBL_STUDENT_STUDENT_PICTURE_AGREEMENT,
        ));
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {
        // TODO: Implement loadViewGraph() method.
    }

    /**
     * @return void|AbstractService
     */
    public function getViewService()
    {
        // TODO: Implement getViewService() method.
    }

    /**
     * Define Property Field-Type and additional Data
     *
     * @param string $PropertyName __CLASS__::{CONSTANT_}
     * @param null|string $Placeholder
     * @param null|string $Label
     * @param IIconInterface|null $Icon
     * @param bool $doResetCount Reset ALL FieldName calculations e.g. FieldName[23] -> FieldName[1]
     * @return AbstractField
     */
    public function getFormField( $PropertyName, $Placeholder = null, $Label = null, IIconInterface $Icon = null, $doResetCount = false )
    {

        switch ($PropertyName) {
            case self::SIBLINGS_COUNT:
                $PropertyCount = $this->calculateFormFieldCount( $PropertyName, $doResetCount );
                $Field = new NumberField( $PropertyName.'['.$PropertyCount.']',
                    $Placeholder, $Label, $Icon
                );
                break;
            case self::TBL_STUDENT_MEDICAL_RECORD_MASERN_DOCUMENT_TYPE:
                $Data = Student::useService()->getPropertyList( new TblStudentMasernInfo(), TblStudentMasernInfo::ATTR_TEXT_SHORT, array(
                    TblStudentMasernInfo::ATTR_TYPE => TblStudentMasernInfo::TYPE_DOCUMENT
                ));
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_STUDENT_MEDICAL_RECORD_MASERN_CREATOR_TYPE:
                $Data = Student::useService()->getPropertyList( new TblStudentMasernInfo(), TblStudentMasernInfo::ATTR_TEXT_SHORT, array(
                    TblStudentMasernInfo::ATTR_TYPE => TblStudentMasernInfo::TYPE_CREATOR
                ));
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
