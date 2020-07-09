<?php
namespace SPHERE\Application\People\Meta\Student;

use DateTime;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentInsuranceState;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentIntegration;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentAgreement;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentIntegration;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransport;
use SPHERE\Application\People\Meta\Student\Service\Service\Support;
use SPHERE\Application\People\Meta\Student\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Service extends Support
{

    /**
     * @return false|ViewStudent[]
     */
    public function viewPerson()
    {

        return ( new Data($this->getBinding()) )->viewStudent();
    }

    /**
     * @return false|ViewStudentAgreement[]
     */
    public function viewStudentAgreement()
    {

        return ( new Data($this->getBinding()) )->viewStudentAgreement();
    }

    /**
     * @return false|ViewStudentBaptism[]
     */
    public function viewStudentBaptism()
    {

        return ( new Data($this->getBinding()) )->viewStudentBaptism();
    }

    /**
     * @return false|ViewStudentDisorder[]
     */
    public function viewStudentDisorder()
    {

        return ( new Data($this->getBinding()) )->viewStudentDisorder();
    }

    /**
     * @return false|ViewStudentFocus[]
     */
    public function viewStudentFocus()
    {

        return ( new Data($this->getBinding()) )->viewStudentFocus();
    }

    /**
     * @return false|ViewStudentIntegration[]
     */
    public function viewStudentIntegration()
    {

        return ( new Data($this->getBinding()) )->viewStudentIntegration();
    }

    /**
     * @return false|ViewStudentIntegration[]
     */
    public function viewStudentLiberation()
    {

        return ( new Data($this->getBinding()) )->viewStudentLiberation();
    }

    /**
     * @return false|ViewStudentLocker[]
     */
    public function viewStudentLocker()
    {

        return ( new Data($this->getBinding()) )->viewStudentLocker();
    }

    /**
     * @return false|ViewStudentMedicalRecord[]
     */
    public function viewStudentMedicalRecord()
    {

        return ( new Data($this->getBinding()) )->viewStudentMedicalRecord();
    }

    /**
     * @return false|ViewStudentTransfer[]
     */
    public function viewStudentTransfer()
    {

        return ( new Data($this->getBinding()) )->viewStudentTransfer();
    }

    /**
     * @return false|ViewStudentTransport[]
     */
    public function viewStudentTransport()
    {

        return ( new Data($this->getBinding()) )->viewStudentTransport();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $LockerNumber
     * @param $LockerLocation
     * @param $KeyNumber
     *
     * @return TblStudentLocker
     */
    public function insertStudentLocker(
        $LockerNumber,
        $LockerLocation,
        $KeyNumber
    ) {

        return (new Data($this->getBinding()))->createStudentLocker(
            $LockerNumber,
            $LockerLocation,
            $KeyNumber
        );
    }

    /**
     * @param TblSiblingRank $tblSiblingRank
     *
     * @return TblStudentBilling
     */
    public function insertStudentBilling(TblSiblingRank $tblSiblingRank)
    {

        return (new Data($this->getBinding()))->createStudentBilling(
            $tblSiblingRank
        );
    }

    /**
     * @param string                    $Disease
     * @param string                    $Medication
     * @param string                    $Insurance
     * @param int                       $InsuranceState
     * @param string                    $AttendingDoctor
     * @param DateTime|null             $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return TblStudentMedicalRecord
     */
    public function insertStudentMedicalRecord(
        $Disease,
        $Medication,
        $Insurance,
        $InsuranceState = 0,
        $AttendingDoctor = '',
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {

        return (new Data($this->getBinding()))->createStudentMedicalRecord(
            $Disease,
            $Medication,
            $AttendingDoctor,
            $InsuranceState,
            $Insurance,
            $MasernDate,
            $MasernDocumentType,
            $MasernCreatorType
        );
    }

    /**
     * @param TblStudent              $tblStudent
     * @param TblStudentAgreementType $tblStudentAgreementType
     *
     * @return Service\Entity\TblStudentAgreement
     */
    public function insertStudentAgreement(
        TblStudent $tblStudent,
        TblStudentAgreementType $tblStudentAgreementType
    ) {

        return (new Data($this->getBinding()))->addStudentAgreement($tblStudent, $tblStudentAgreementType);
    }

    /**
     * @param        $Route
     * @param        $StationEntrance
     * @param        $StationExit
     * @param string $Remark
     *
     * @return TblStudentTransport
     */
    public function insertStudentTransport(
        $Route,
        $StationEntrance,
        $StationExit,
        $Remark = ''
    ) {

        return (new Data($this->getBinding()))->createStudentTransport(
            $Route,
            $StationEntrance,
            $StationExit,
            $Remark
        );
    }

    /**
     * @param TblPerson|null  $IntegrationPerson
     * @param TblCompany|null $IntegrationCompany
     * @param                 $CoachingRequestDate
     * @param                 $CoachingCounselDate
     * @param                 $CoachingDecisionDate
     * @param                 $CoachingRequired
     * @param                 $CoachingTime
     * @param string          $CoachingRemark
     *
     * @return Service\Entity\TblStudentIntegration
     */
    public function insertStudentIntegration(
        TblPerson $IntegrationPerson = null,
        TblCompany $IntegrationCompany = null,
        $CoachingRequestDate,
        $CoachingCounselDate,
        $CoachingDecisionDate,
        $CoachingRequired,
        $CoachingTime = '',
        $CoachingRemark = ''
    ) {

        return (new Data($this->getBinding()))->createStudentIntegration(
            $IntegrationPerson ? $IntegrationPerson : null,
            $IntegrationCompany ? $IntegrationCompany : null,
            $CoachingRequestDate,
            $CoachingCounselDate,
            $CoachingDecisionDate,
            $CoachingRequired,
            $CoachingTime,
            $CoachingRemark
        );
    }

    /**
     * @param TblPerson                    $tblPerson
     * @param string                       $Prefix
     * @param string                       $Identifier
     * @param TblStudentMedicalRecord|null $tblStudentMedicalRecord
     * @param TblStudentTransport|null     $tblStudentTransport
     * @param TblStudentBilling|null       $tblStudentBilling
     * @param TblStudentLocker|null        $tblStudentLocker
     * @param TblStudentBaptism|null       $tblStudentBaptism
     * @param TblStudentIntegration|null   $tblStudentIntegration
     * @param string                       $SchoolAttendanceStartDate
     *
     * @return TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        $Prefix = '',
        $Identifier = '',
        TblStudentMedicalRecord $tblStudentMedicalRecord = null,
        TblStudentTransport $tblStudentTransport = null,
        TblStudentBilling $tblStudentBilling = null,
        TblStudentLocker $tblStudentLocker = null,
        TblStudentBaptism $tblStudentBaptism = null,
        TblStudentIntegration $tblStudentIntegration = null,
        $SchoolAttendanceStartDate = ''
    ) {

        return (new Data($this->getBinding()))->createStudent($tblPerson,
            $Prefix,
            $Identifier,
            $tblStudentMedicalRecord,
            $tblStudentTransport,
            $tblStudentBilling,
            $tblStudentLocker,
            $tblStudentBaptism,
            $tblStudentIntegration,
            $SchoolAttendanceStartDate);
    }

    /**
     * @param TblStudent $tblStudent
     * @param $Prefix
     * @return bool|TblStudent
     */
    public function updateStudentPrefix(TblStudent $tblStudent, $Prefix)
    {

        return (new Data($this->getBinding()))->updateStudentPrefix($tblStudent,$Prefix);
    }

    /**
     * @param TblStudent $tblStudent
     * @param $Identifier
     * @return bool|TblStudent
     */
    public function updateStudentIdentifier(TblStudent $tblStudent, $Identifier)
    {

        return (new Data($this->getBinding()))->updateStudentIdentifier($tblStudent,$Identifier);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentBasic(TblPerson $tblPerson, $Meta)
    {

        $tblStudent = $tblPerson->getStudent(true);

        $Prefix = $Meta['Student']['Prefix'];
        $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');

        if($tblSetting && $tblSetting->getValue() && !$tblStudent->getIdentifier()){
            // höchste Schülernummer setzen, wenn noch nicht vorhanden
            $biggestIdentifier = Student::useService()->getStudentMaxIdentifier();
            $Meta['Student']['Identifier'] = $biggestIdentifier + 1;
        } elseif($tblSetting && $tblSetting->getValue() && $tblStudent->getIdentifier()){
            // vorhandene Schülernummer übergeben
            $Meta['Student']['Identifier'] = $tblStudent->getIdentifier();
        } elseif(!isset($Meta['Student']['Identifier'])){
            // sollte nie vorkommen, wenn wird die vorhandene Einstellung übergeben (keine Änderung)
            $Meta['Student']['Identifier'] = $tblStudent->getIdentifier();
        }

        if ($tblStudent) {
            return (new Data($this->getBinding()))->updateStudentBasic(
                $tblStudent,
                $Prefix,
                $Meta['Student']['Identifier'],
                $Meta['Student']['SchoolAttendanceStartDate'],
                isset($Meta['Student']['HasMigrationBackground']),
                isset($Meta['Student']['IsInPreparationDivisionForMigrants'])
            );
        } else {
            return (new Data($this->getBinding()))->createStudentBasic(
                $tblPerson,
                $Prefix,
                $Meta['Student']['Identifier'],
                $Meta['Student']['SchoolAttendanceStartDate'],
                isset($Meta['Student']['HasMigrationBackground']),
                isset($Meta['Student']['IsInPreparationDivisionForMigrants'])
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblStudent
     */
    private function createStudentWithOnlyAutoIdentifier(TblPerson $tblPerson)
    {
        $identifier = '';
        $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');
        if($tblSetting && $tblSetting->getValue()){
            $biggestIdentifier = Student::useService()->getStudentMaxIdentifier();
            $identifier = $biggestIdentifier + 1;
        }

        return (new Data($this->getBinding()))->createStudent($tblPerson, '', $identifier);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentTransfer(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $TransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
            $tblStudentTransferByTypeEnrollment = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeEnrollment
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['School']);
            $tblStateCompany = false;
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['Course']);
            $tblStudentSchoolEnrollmentType = $this->getStudentSchoolEnrollmentTypeById(
                $Meta['Transfer'][$TransferTypeEnrollment->getId()]['StudentSchoolEnrollmentType']
            );
            if ($tblStudentTransferByTypeEnrollment) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeEnrollment,
                    $tblStudent,
                    $TransferTypeEnrollment,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Remark'],
                    $tblStudentSchoolEnrollmentType ? $tblStudentSchoolEnrollmentType : null
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeEnrollment,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Remark'],
                    $tblStudentSchoolEnrollmentType ? $tblStudentSchoolEnrollmentType : null
                );
            }

            $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
            $tblStudentTransferByTypeArrive = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeArrive
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeArrive->getId()]['School']);
            if (isset($Meta['Transfer'][$TransferTypeArrive->getId()]['StateSchool'])) {
                $tblStateCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeArrive->getId()]['StateSchool']);
            } else {
                $tblStateCompany = false;
            }
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeArrive->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeArrive->getId()]['Course']);
            if ($tblStudentTransferByTypeArrive) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeArrive,
                    $tblStudent,
                    $TransferTypeArrive,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeArrive,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Remark']
                );
            }

            $TransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
            $tblStudentTransferByTypeLeave = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeLeave
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeLeave->getId()]['School']);
            $tblStateCompany = false;
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeLeave->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeLeave->getId()]['Course']);
            if ($tblStudentTransferByTypeLeave) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeLeave,
                    $tblStudent,
                    $TransferTypeLeave,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeLeave,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Remark']
                );
            }

//            $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
//            $tblStudentTransferByTypeProcess = Student::useService()->getStudentTransferByType(
//                $tblStudent,
//                $TransferTypeProcess
//            );
//            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeProcess->getId()]['School']);
//            $tblStateCompany = false;
//            // removed "Aktuelle Schulart"
////            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeProcess->getId()]['Type']);
//            $tblType = false;
//            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeProcess->getId()]['Course']);
//            if ($tblStudentTransferByTypeProcess) {
//                (new Data($this->getBinding()))->updateStudentTransfer(
//                    $tblStudentTransferByTypeProcess,
//                    $tblStudent,
//                    $TransferTypeProcess,
//                    $tblCompany ? $tblCompany : null,
//                    $tblStateCompany ? $tblStateCompany : null,
//                    $tblType ? $tblType : null,
//                    $tblCourse ? $tblCourse : null,
//                    '',
//                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
//                );
//            } else {
//                (new Data($this->getBinding()))->createStudentTransfer(
//                    $tblStudent,
//                    $TransferTypeProcess,
//                    $tblCompany ? $tblCompany : null,
//                    $tblStateCompany ? $tblStateCompany : null,
//                    $tblType ? $tblType : null,
//                    $tblCourse ? $tblCourse : null,
//                    '',
//                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
//                );
//            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentProcess(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
            $tblStudentTransferByTypeProcess = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeProcess
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeProcess->getId()]['School']);
            $tblStateCompany = false;
            // removed "Aktuelle Schulart"
//            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeProcess->getId()]['Type']);
            $tblType = false;
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeProcess->getId()]['Course']);
            if ($tblStudentTransferByTypeProcess) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeProcess,
                    $tblStudent,
                    $TransferTypeProcess,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    '',
                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeProcess,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    '',
                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentMedicalRecord(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }
        $DocumentType = null;
        if(isset($Meta['MedicalRecord']['DocumentType'][TblStudentMasernInfo::DOCUMENT_IDENTIFICATION])){
            $DocumentType = $this->getStudentMasernInfoById($Meta['MedicalRecord']['DocumentType'][TblStudentMasernInfo::DOCUMENT_IDENTIFICATION]);
        } elseif(isset($Meta['MedicalRecord']['DocumentType'][TblStudentMasernInfo::DOCUMENT_VACCINATION_PROTECTION])) {
            $DocumentType = $this->getStudentMasernInfoById($Meta['MedicalRecord']['DocumentType'][TblStudentMasernInfo::DOCUMENT_VACCINATION_PROTECTION]);
        } elseif(isset($Meta['MedicalRecord']['DocumentType'][TblStudentMasernInfo::DOCUMENT_IMMUNITY])) {
            $DocumentType = $this->getStudentMasernInfoById($Meta['MedicalRecord']['DocumentType'][TblStudentMasernInfo::DOCUMENT_IMMUNITY]);
        } elseif(isset($Meta['MedicalRecord']['DocumentType'][TblStudentMasernInfo::DOCUMENT_CANT_VACCINATION])) {
            $DocumentType = $this->getStudentMasernInfoById($Meta['MedicalRecord']['DocumentType'][TblStudentMasernInfo::DOCUMENT_CANT_VACCINATION]);
        }
        $CreatorType = null;
        if(isset($Meta['MedicalRecord']['CreatorType'][TblStudentMasernInfo::CREATOR_STATE])){
            $CreatorType = $this->getStudentMasernInfoById($Meta['MedicalRecord']['CreatorType'][TblStudentMasernInfo::CREATOR_STATE]);
        } elseif(isset($Meta['MedicalRecord']['CreatorType'][TblStudentMasernInfo::CREATOR_COMMUNITY])) {
            $CreatorType = $this->getStudentMasernInfoById($Meta['MedicalRecord']['CreatorType'][TblStudentMasernInfo::CREATOR_COMMUNITY]);
        }
        $MasernDate = null;
        if(isset($Meta['MedicalRecord']['Masern']['Date']) && $Meta['MedicalRecord']['Masern']['Date']){
            $MasernDate = new DateTime($Meta['MedicalRecord']['Masern']['Date']);
        }

        if ($tblStudent) {
            if (($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                (new Data($this->getBinding()))->updateStudentMedicalRecord(
                    $tblStudent->getTblStudentMedicalRecord(),
                    $Meta['MedicalRecord']['Disease'],
                    $Meta['MedicalRecord']['Medication'],
                    $Meta['MedicalRecord']['AttendingDoctor'],
                    $Meta['MedicalRecord']['Insurance']['State'],
                    $Meta['MedicalRecord']['Insurance']['Company'],
                    $MasernDate,
                    $DocumentType,
                    $CreatorType
                );
            } else {
                $tblStudentMedicalRecord = (new Data($this->getBinding()))->createStudentMedicalRecord(
                    $Meta['MedicalRecord']['Disease'],
                    $Meta['MedicalRecord']['Medication'],
                    $Meta['MedicalRecord']['AttendingDoctor'],
                    $Meta['MedicalRecord']['Insurance']['State'],
                    $Meta['MedicalRecord']['Insurance']['Company'],
                    $MasernDate,
                    $DocumentType,
                    $CreatorType
                );

                if ($tblStudentMedicalRecord) {
                    (new Data($this->getBinding()))->updateStudentField(
                        $tblStudent,
                        $tblStudentMedicalRecord,
                        $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                        $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                        $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                        $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                        $tblStudent->getTblStudentIntegration() ? $tblStudent->getTblStudentIntegration() : null
                    );
                } else {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentGeneral(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            if (($tblStudentLocker = $tblStudent->getTblStudentLocker())) {
                (new Data($this->getBinding()))->updateStudentLocker(
                    $tblStudent->getTblStudentLocker(),
                    $Meta['Additional']['Locker']['Number'],
                    $Meta['Additional']['Locker']['Location'],
                    $Meta['Additional']['Locker']['Key']
                );
            } else {
                $tblStudentLocker = (new Data($this->getBinding()))->createStudentLocker(
                    $Meta['Additional']['Locker']['Number'],
                    $Meta['Additional']['Locker']['Location'],
                    $Meta['Additional']['Locker']['Key']
                );
            }

            if (($tblStudentBaptism = $tblStudent->getTblStudentBaptism())) {
                (new Data($this->getBinding()))->updateStudentBaptism(
                    $tblStudent->getTblStudentBaptism(),
                    $Meta['Additional']['Baptism']['Date'],
                    $Meta['Additional']['Baptism']['Location']
                );
            } else {
                $tblStudentBaptism = (new Data($this->getBinding()))->createStudentBaptism(
                    $Meta['Additional']['Baptism']['Date'],
                    $Meta['Additional']['Baptism']['Location']
                );
            }

            if (($tblStudentTransport = $tblStudent->getTblStudentTransport())) {
                (new Data($this->getBinding()))->updateStudentTransport(
                    $tblStudent->getTblStudentTransport(),
                    $Meta['Transport']['Route'],
                    $Meta['Transport']['Station']['Entrance'],
                    $Meta['Transport']['Station']['Exit'],
                    $Meta['Transport']['Remark']
                );
            } else {
                $tblStudentTransport = (new Data($this->getBinding()))->createStudentTransport(
                    $Meta['Transport']['Route'],
                    $Meta['Transport']['Station']['Entrance'],
                    $Meta['Transport']['Station']['Exit'],
                    $Meta['Transport']['Remark']
                );
            }

            $SiblingRank = Relationship::useService()->getSiblingRankById($Meta['Billing']);
            if ($tblStudentBilling = $tblStudent->getTblStudentBilling()) {
                (new Data($this->getBinding()))->updateStudentBilling(
                    $tblStudentBilling,
                    $SiblingRank ? $SiblingRank : null
                );
            } else {
                $tblStudentBilling = (new Data($this->getBinding()))->createStudentBilling(
                    $SiblingRank ? $SiblingRank : null
                );
            }

            (new Data($this->getBinding()))->updateStudentField(
                $tblStudent,
                $tblStudent->getTblStudentMedicalRecord() ? $tblStudent->getTblStudentMedicalRecord() : null,
                $tblStudentTransport ? $tblStudentTransport : null,
                $tblStudentBilling ? $tblStudentBilling : null,
                $tblStudentLocker ? $tblStudentLocker : null,
                $tblStudentBaptism ? $tblStudentBaptism : null,
                $tblStudent->getTblStudentIntegration() ? $tblStudent->getTblStudentIntegration() : null
            );

            /*
             * Liberation
             */
            $tblStudentLiberationAllByStudent = $this->getStudentLiberationAllByStudent($tblStudent);
            if ($tblStudentLiberationAllByStudent) {
                foreach ($tblStudentLiberationAllByStudent as $tblStudentLiberation) {
                    (new Data($this->getBinding()))->removeStudentLiberation($tblStudentLiberation);
                }
            }
            if (isset( $Meta['Liberation'] )) {
                foreach ($Meta['Liberation'] as $Category => $Type) {
                    $tblStudentLiberationCategory = $this->getStudentLiberationTypeById($Category);
                    if ($tblStudentLiberationCategory) {
                        $tblStudentLiberationType = $this->getStudentLiberationTypeById($Type);
                        if ($tblStudentLiberationType) {
                            (new Data($this->getBinding()))->addStudentLiberation($tblStudent,
                                $tblStudentLiberationType);
                        }
                    }
                }
            }

            /*
             * Agreement
             */
            $tblStudentAgreementAllByStudent = $this->getStudentAgreementAllByStudent($tblStudent);
            if ($tblStudentAgreementAllByStudent) {
                foreach ($tblStudentAgreementAllByStudent as $tblStudentAgreement) {
                    if (!isset(
                        $Meta['Agreement']
                        [$tblStudentAgreement->getTblStudentAgreementType()->getTblStudentAgreementCategory()->getId()]
                        [$tblStudentAgreement->getTblStudentAgreementType()->getId()]
                    )
                    ) {
                        (new Data($this->getBinding()))->removeStudentAgreement($tblStudentAgreement);
                    }
                }
            }
            if (isset( $Meta['Agreement'] )) {
                foreach ($Meta['Agreement'] as $Category => $Items) {
                    $tblStudentAgreementCategory = $this->getStudentAgreementTypeById($Category);
                    if ($tblStudentAgreementCategory) {
                        foreach ($Items as $Type => $Value) {
                            $tblStudentAgreementType = $this->getStudentAgreementTypeById($Type);
                            if ($tblStudentAgreementType) {
                                (new Data($this->getBinding()))->addStudentAgreement($tblStudent,
                                    $tblStudentAgreementType);
                            }
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentSubject(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $tblStudentSubjectAll = $this->getStudentSubjectAllByStudent($tblStudent);
            if ($tblStudentSubjectAll) {
                foreach ($tblStudentSubjectAll as $tblStudentSubject) {
                    if (!Subject::useService()->getSubjectById(
                        $Meta['Subject'][$tblStudentSubject->getTblStudentSubjectType()->getId()]
                        [$tblStudentSubject->getTblStudentSubjectRanking()->getId()])
                    ) {
                        (new Data($this->getBinding()))->removeStudentSubject($tblStudentSubject);
                    }
                }
            }
            if (isset( $Meta['Subject'] )) {
                foreach ($Meta['Subject'] as $Category => $Items) {
                    $tblStudentSubjectType = $this->getStudentSubjectTypeById($Category);
                    if ($tblStudentSubjectType) {
                        foreach ($Items as $Ranking => $Type) {
                            $tblStudentSubjectRanking = $this->getStudentSubjectRankingById($Ranking);
                            $tblSubject = Subject::useService()->getSubjectById($Type);
                            if ($tblSubject) {
                                // From & Till
                                $tblLevelFrom = null;
                                $tblLevelTill = null;
                                if (isset( $Meta['SubjectLevelFrom'] ) && isset( $Meta['SubjectLevelFrom'][$Category][$Ranking] )) {
                                    if ($Meta['SubjectLevelFrom'][$Category][$Ranking]) {
                                        $tblLevelFrom = Division::useService()->getLevelById($Meta['SubjectLevelFrom'][$Category][$Ranking]);
                                    }
                                }
                                if (isset( $Meta['SubjectLevelTill'] ) && isset( $Meta['SubjectLevelTill'][$Category][$Ranking] )) {
                                    if ($Meta['SubjectLevelTill'][$Category][$Ranking]) {
                                        $tblLevelTill = Division::useService()->getLevelById($Meta['SubjectLevelTill'][$Category][$Ranking]);
                                    }
                                }

                                $this->addStudentSubject(
                                    $tblStudent,
                                    $tblStudentSubjectType,
                                    $tblStudentSubjectRanking ? $tblStudentSubjectRanking : null,
                                    $tblSubject,
                                    $tblLevelFrom, $tblLevelTill
                                );
                            }
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getStudentMaxIdentifier()
    {

        $tblStudentList = (new Data($this->getBinding()))->getStudentAll();
        $result = 0;
        if($tblStudentList) {
            foreach($tblStudentList as $tblStudent){
                if(is_numeric($tblStudent->getIdentifier()) && $tblStudent->getIdentifier() > $result){
                    $result = $tblStudent->getIdentifier();
                }
            }
        }
        return $result;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentMedicalRecordById($Id)
    {

        return (new Data($this->getBinding()))->getStudentMedicalRecordById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentInsuranceState
     */
    public function getStudentInsuranceStateById($Id)
    {

        return (new Data($this->getBinding()))->getStudentInsuranceStateById($Id);
    }

    /**
     * @param $Name
     *
     * @return false|TblStudentInsuranceState
     */
    public function getStudentInsuranceStateByName($Name)
    {
        return (new Data($this->getBinding()))->getStudentInsuranceStateByName($Name);
    }

    /**
     * @param int $Id
     *
     * @return false|TblStudentMasernInfo
     */
    public function getStudentMasernInfoById($Id)
    {
        return (new Data($this->getBinding()))->getStudentMasernInfoById($Id);
    }

    /**
     * @param string $Type
     * TblStudentMasernInfo::TYPE_DOCUMENT || TblStudentMasernInfo::TYPE_CREATOR
     *
     * @return false|TblStudentMasernInfo[]
     */
    public function getStudentMasernInfoByType($Type = TblStudentMasernInfo::TYPE_DOCUMENT)
    {
        return (new Data($this->getBinding()))->getStudentMasernInfoByType($Type);
    }

    /**
     * @return bool|TblStudentInsuranceState[]
     */
    public function getStudentInsuranceStateAll()
    {

        return (new Data($this->getBinding()))->getStudentInsuranceStateAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBaptism
     */
    public function getStudentBaptismById($Id)
    {

        return (new Data($this->getBinding()))->getStudentBaptismById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBilling
     */
    public function getStudentBillingById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getStudentBillingById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLocker
     */
    public function getStudentLockerById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getStudentLockerById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransport
     */
    public function getStudentTransportById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getStudentTransportById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDivision[]
     */
    public function getCurrentDivisionListByPerson(TblPerson $tblPerson)
    {

        $tblDivisionList = array();
        if (Group::useService()->existsGroupPerson(Group::useService()->getGroupByMetaTable('STUDENT'),
            $tblPerson)
        ) {
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                if ($tblDivisionStudentList) {
                    foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                        foreach ($tblYearList as $tblYear) {
                            if ($tblDivisionStudent->getTblDivision()) {
                                $divisionYear = $tblDivisionStudent->getTblDivision()->getServiceTblYear();
                                if ($divisionYear && $divisionYear->getId() == $tblYear->getId()) {
                                    $tblDivisionList[] = $tblDivisionStudent->getTblDivision();
                                }
                            }
                        }
                    }
                }
            }
        }

        return empty($tblDivisionList) ? false : $tblDivisionList;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDivision
     */
    public function getCurrentMainDivisionByPerson(TblPerson $tblPerson)
    {

        if (Group::useService()->existsGroupPerson(Group::useService()->getGroupByMetaTable('STUDENT'),
            $tblPerson)
        ) {
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                if ($tblDivisionStudentList) {
                    foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                        foreach ($tblYearList as $tblYear) {
                            if ($tblDivisionStudent->getTblDivision()) {
                                $divisionYear = $tblDivisionStudent->getTblDivision()->getServiceTblYear();
                                if ($divisionYear && $divisionYear->getId() == $tblYear->getId()) {
                                    if(($tblDivision = $tblDivisionStudent->getTblDivision())){
                                        if (($tblLevel = $tblDivision->getTblLevel())
                                            && !$tblLevel->getIsChecked()
                                        ) {
                                            return $tblDivision;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblDivision
     */
    public function getMainDivisionByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {

        $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
        if ($tblDivisionStudentList) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if ($tblDivisionStudent->getLeaveDateTime() == null
                    && $tblDivisionStudent->getTblDivision()
                ) {
                    $divisionYear = $tblDivisionStudent->getTblDivision()->getServiceTblYear();
                    if ($divisionYear && $divisionYear->getId() == $tblYear->getId()) {
                        if (($tblDivision = $tblDivisionStudent->getTblDivision())) {
                            if (($tblLevel = $tblDivision->getTblLevel())
                                && !$tblLevel->getIsChecked()
                            ) {
                                return $tblDivision;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $Prefix
     *
     * @return string
     */
    public function getDisplayCurrentDivisionListByPerson(TblPerson $tblPerson, $Prefix = 'Klasse' )
    {

        $tblDivisionList = $this->getCurrentDivisionListByPerson($tblPerson);
        $list = array();
        if ($tblDivisionList){
            foreach ($tblDivisionList as $tblDivision){
                $list[] = trim($Prefix . ' ' . $tblDivision->getDisplayName());
            }

            return implode(', ', $list);
        } else {

            return '';
        }
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblDivision|bool
     */
    public function getCurrentDivisionByPerson(TblPerson $tblPerson)
    {

        $tblDivisionList = $this->getCurrentDivisionListByPerson($tblPerson);
        if ($tblDivisionList) {
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblLevel = $tblDivision->getTblLevel()) && !$tblLevel->getIsChecked()) {
                    return $tblDivision;
                }
            }
        }
        return false;
    }

    /**
     * @param TblStudentSubject $tblStudentSubject
     */
    public function removeStudentSubject(TblStudentSubject $tblStudentSubject)
    {
        ( new Data($this->getBinding()) )->removeStudentSubject($tblStudentSubject);
    }

    /**
     * @param TblStudent               $tblStudent
     * @param TblStudentSubjectType    $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     * @param TblSubject               $tblSubject
     * @param TblLevel                 $tblLevelFrom
     * @param TblLevel                 $tblLevelTill
     *
     * @return TblStudentSubject
     */
    public function addStudentSubject(
        TblStudent $tblStudent,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking,
        TblSubject $tblSubject,
        TblLevel $tblLevelFrom = null,
        TblLevel $tblLevelTill = null
    ) {

        return ( new Data($this->getBinding()) )->addStudentSubject(
            $tblStudent,
            $tblStudentSubjectType,
            $tblStudentSubjectRanking,
            $tblSubject,
            $tblLevelFrom,
            $tblLevelTill);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblDivision[]
     */
    public function getDivisionListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {

        $tblDivisionList = array();

        $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
        if ($tblDivisionStudentList) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if ($tblDivisionStudent->getTblDivision()) {
                    $divisionYear = $tblDivisionStudent->getTblDivision()->getServiceTblYear();
                    if ($divisionYear && $divisionYear->getId() == $tblYear->getId()) {
                        $tblDivisionList[] = $tblDivisionStudent->getTblDivision();
                    }
                }
            }
        }

        return empty($tblDivisionList) ? false : $tblDivisionList;
    }

    /**
     * @param array $EntityList
     * @param array $ProtocolList
     *
     * @return bool
     */
    public function bulkSaveEntityList($EntityList = array(), $ProtocolList = array())
    {

        if (!empty($EntityList)) {
            return (new Data($this->getBinding()))->bulkSaveEntityList($EntityList, $ProtocolList);
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision|null $tblDivision
     *
     * @return bool|TblCompany
     */
    public function getCurrentSchoolByPerson(TblPerson $tblPerson, TblDivision $tblDivision = null)
    {
        if ($tblDivision && $tblDivision->getServiceTblCompany()) {
            return $tblDivision->getServiceTblCompany();
        } elseif (($tblCurrentDivision = $this->getCurrentDivisionByPerson($tblPerson))
            && $tblCurrentDivision->getServiceTblCompany()
        ) {
            return $tblCurrentDivision->getServiceTblCompany();
        } else {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                && ($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferType))
            ) {
                return $tblTransfer->getServiceTblCompany();
            }
        }

        return false;
    }
}
