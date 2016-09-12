<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Meta\Student\Service\Service\Integration;
use SPHERE\Application\People\Meta\Student\Service\Setup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Service extends Integration
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
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
     * @param                $Disease
     * @param                $Medication
     * @param                $Insurance
     * @param int|null       $InsuranceState
     * @param TblPerson|null $tblPersonAttendingDoctor
     *
     * @return TblStudentMedicalRecord
     */
    public function insertStudentMedicalRecord(
        $Disease,
        $Medication,
        $Insurance,
        $InsuranceState = 0,
        TblPerson $tblPersonAttendingDoctor = null
    ) {

        return (new Data($this->getBinding()))->createStudentMedicalRecord(
            $Disease,
            $Medication,
            $tblPersonAttendingDoctor,
            $InsuranceState,
            $Insurance
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
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param array          $Meta
     * @param                $Group
     *
     * @return IFormInterface|Redirect
     */
    public function createMeta(IFormInterface $Form = null, TblPerson $tblPerson, $Meta, $Group)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        $tblStudent = $this->getStudentByPerson($tblPerson);

        $AttendingDoctor = Person::useService()->getPersonById($Meta['MedicalRecord']['AttendingDoctor']);
        $IntegrationPerson = Person::useService()->getPersonById($Meta['Integration']['School']['Person']);
        $IntegrationCompany = Company::useService()->getCompanyById($Meta['Integration']['School']['Company']);
        $SiblingRank = Relationship::useService()->getSiblingRankById($Meta['Billing']);

        if ($tblStudent) {

            $tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord();
            if ($tblStudentMedicalRecord) {
                (new Data($this->getBinding()))->updateStudentMedicalRecord(
                    $tblStudent->getTblStudentMedicalRecord(),
                    $Meta['MedicalRecord']['Disease'],
                    $Meta['MedicalRecord']['Medication'],
                    $AttendingDoctor ? $AttendingDoctor : null,
                    $Meta['MedicalRecord']['Insurance']['State'],
                    $Meta['MedicalRecord']['Insurance']['Company']
                );
            } else {
                $tblStudentMedicalRecord = (new Data($this->getBinding()))->createStudentMedicalRecord(
                    $Meta['MedicalRecord']['Disease'],
                    $Meta['MedicalRecord']['Medication'],
                    $AttendingDoctor ? $AttendingDoctor : null,
                    $Meta['MedicalRecord']['Insurance']['State'],
                    $Meta['MedicalRecord']['Insurance']['Company']
                );
            }

            $tblStudentLocker = $tblStudent->getTblStudentLocker();
            if ($tblStudentLocker) {
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

            $tblStudentBaptism = $tblStudent->getTblStudentBaptism();
            if ($tblStudentBaptism) {
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

            $tblStudentTransport = $tblStudent->getTblStudentTransport();
            if ($tblStudentTransport) {
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

            $tblStudentIntegration = $tblStudent->getTblStudentIntegration();
            if ($tblStudentIntegration) {
                (new Data($this->getBinding()))->updateStudentIntegration(
                    $tblStudent->getTblStudentIntegration(),
                    $IntegrationPerson ? $IntegrationPerson : null,
                    $IntegrationCompany ? $IntegrationCompany : null,
                    $Meta['Integration']['Coaching']['RequestDate'],
                    $Meta['Integration']['Coaching']['CounselDate'],
                    $Meta['Integration']['Coaching']['DecisionDate'],
                    isset( $Meta['Integration']['Coaching']['Required'] ),
                    $Meta['Integration']['School']['Time'],
                    $Meta['Integration']['School']['Remark']
                );
            } else {
                $tblStudentIntegration = (new Data($this->getBinding()))->createStudentIntegration(
                    $IntegrationPerson ? $IntegrationPerson : null,
                    $IntegrationCompany ? $IntegrationCompany : null,
                    $Meta['Integration']['Coaching']['RequestDate'],
                    $Meta['Integration']['Coaching']['CounselDate'],
                    $Meta['Integration']['Coaching']['DecisionDate'],
                    isset( $Meta['Integration']['Coaching']['Required'] ),
                    $Meta['Integration']['School']['Time'],
                    $Meta['Integration']['School']['Remark']
                );
            }

            $tblStudentBilling = $tblStudent->getTblStudentBilling();
            if ($tblStudentBilling) {
                (new Data($this->getBinding()))->updateStudentBilling(
                    $tblStudentBilling,
                    $SiblingRank ? $SiblingRank : null
                );
            } else {
                $tblStudentBilling = (new Data($this->getBinding()))->createStudentBilling(
                    $SiblingRank ? $SiblingRank : null
                );
            }

            (new Data($this->getBinding()))->updateStudent(
                $tblStudent,
                $Meta['Student']['Identifier'],
                $tblStudentMedicalRecord,
                $tblStudentTransport,
                $tblStudentBilling,
                $tblStudentLocker,
                $tblStudentBaptism,
                $tblStudentIntegration,
                $Meta['Student']['SchoolAttendanceStartDate']
            );

        } else {

            $tblStudentLocker = (new Data($this->getBinding()))->createStudentLocker(
                $Meta['Additional']['Locker']['Number'],
                $Meta['Additional']['Locker']['Location'],
                $Meta['Additional']['Locker']['Key']
            );

            $tblStudentMedicalRecord = (new Data($this->getBinding()))->createStudentMedicalRecord(
                $Meta['MedicalRecord']['Disease'],
                $Meta['MedicalRecord']['Medication'],
                $AttendingDoctor ? $AttendingDoctor : null,
                $Meta['MedicalRecord']['Insurance']['State'],
                $Meta['MedicalRecord']['Insurance']['Company']
            );

            $tblStudentBaptism = (new Data($this->getBinding()))->createStudentBaptism(
                $Meta['Additional']['Baptism']['Date'],
                $Meta['Additional']['Baptism']['Location']
            );

            $tblStudentTransport = (new Data($this->getBinding()))->createStudentTransport(
                $Meta['Transport']['Route'],
                $Meta['Transport']['Station']['Entrance'],
                $Meta['Transport']['Station']['Exit'],
                $Meta['Transport']['Remark']
            );

            $tblStudentIntegration = (new Data($this->getBinding()))->createStudentIntegration(
                $IntegrationPerson ? $IntegrationPerson : null,
                $IntegrationCompany ? $IntegrationCompany : null,
                $Meta['Integration']['Coaching']['RequestDate'],
                $Meta['Integration']['Coaching']['CounselDate'],
                $Meta['Integration']['Coaching']['DecisionDate'],
                isset( $Meta['Integration']['Coaching']['Required'] ),
                $Meta['Integration']['School']['Time'],
                $Meta['Integration']['School']['Remark']
            );

            $tblStudentBilling = (new Data($this->getBinding()))->createStudentBilling(
                $SiblingRank ? $SiblingRank : null
            );

            $tblStudent = (new Data($this->getBinding()))->createStudent(
                $tblPerson,
                $Meta['Student']['Identifier'],
                $tblStudentMedicalRecord,
                $tblStudentTransport,
                $tblStudentBilling,
                $tblStudentLocker,
                $tblStudentBaptism,
                $tblStudentIntegration,
                $Meta['Student']['SchoolAttendanceStartDate']
            );
        }

        if ($tblStudent) {
            $tblStudentDisorderAll = $this->getStudentDisorderAllByStudent($tblStudent);
            if ($tblStudentDisorderAll) {
                foreach ($tblStudentDisorderAll as $tblStudentDisorder) {
                    if (!isset( $Meta['Integration']['Disorder'][$tblStudentDisorder->getTblStudentDisorderType()->getId()] )) {
                        (new Data($this->getBinding()))->removeStudentDisorder($tblStudentDisorder);
                    }
                }
            }
            if (isset( $Meta['Integration']['Disorder'] )) {
                foreach ($Meta['Integration']['Disorder'] as $Category => $Type) {
                    $tblStudentDisorderType = $this->getStudentDisorderTypeById($Category);
                    if ($tblStudentDisorderType) {
                        (new Data($this->getBinding()))->addStudentDisorder($tblStudent, $tblStudentDisorderType);
                    }
                }
            }

            $tblStudentFocusAll = $this->getStudentFocusAllByStudent($tblStudent);
            if ($tblStudentFocusAll) {
                foreach ($tblStudentFocusAll as $tblStudentFocus) {
                    if (!isset( $Meta['Integration']['Focus'][$tblStudentFocus->getTblStudentFocusType()->getId()] )) {
                        (new Data($this->getBinding()))->removeStudentFocus($tblStudentFocus);
                    }
                }
            }
            if (isset( $Meta['Integration']['Focus'] )) {
                foreach ($Meta['Integration']['Focus'] as $Category => $Type) {
                    $tblStudentFocusType = $this->getStudentFocusTypeById($Category);
                    if ($tblStudentFocusType) {
                        (new Data($this->getBinding()))->addStudentFocus($tblStudent, $tblStudentFocusType);
                    }
                }
            }

            $TransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
            $tblStudentTransferByTypeEnrollment = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeEnrollment
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['School']);
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['Course']);
            if ($tblStudentTransferByTypeEnrollment) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeEnrollment,
                    $tblStudent,
                    $TransferTypeEnrollment,
                    $tblCompany ? $tblCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeEnrollment,
                    $tblCompany ? $tblCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Remark']
                );
            }

            $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
            $tblStudentTransferByTypeArrive = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeArrive
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeArrive->getId()]['School']);
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeArrive->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeArrive->getId()]['Course']);
            if ($tblStudentTransferByTypeArrive) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeArrive,
                    $tblStudent,
                    $TransferTypeArrive,
                    $tblCompany ? $tblCompany : null,
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
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeLeave->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeLeave->getId()]['Course']);
            if ($tblStudentTransferByTypeLeave) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeLeave,
                    $tblStudent,
                    $TransferTypeLeave,
                    $tblCompany ? $tblCompany : null,
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
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Remark']
                );
            }

            $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
            $tblStudentTransferByTypeProcess = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeProcess
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeProcess->getId()]['School']);
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeProcess->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeProcess->getId()]['Course']);
            if ($tblStudentTransferByTypeProcess) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeProcess,
                    $tblStudent,
                    $TransferTypeProcess,
                    $tblCompany ? $tblCompany : null,
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
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    '',
                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
                );
            }

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
                        // TODO: Save only if Typ exists in Category
                        $tblStudentLiberationType = $this->getStudentLiberationTypeById($Type);
                        if ($tblStudentLiberationType) {
                            (new Data($this->getBinding()))->addStudentLiberation($tblStudent,
                                $tblStudentLiberationType);
                        }
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Daten wurde erfolgreich gespeichert')
        .new Redirect(null, Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentMedicalRecordById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getStudentMedicalRecordById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBaptism
     */
    public function getStudentBaptismById(
        $Id
    ) {

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
     * @return string
     */
    public function getDisplayCurrentDivisionListByPerson(TblPerson $tblPerson)
    {

        $tblDivisionList = $this->getCurrentDivisionListByPerson($tblPerson);
        $list = array();
        if ($tblDivisionList){
            foreach ($tblDivisionList as $tblDivision){
                $list[] = 'Klasse ' . $tblDivision->getDisplayName();
            }

            return implode(', ', $list);
        } else {

            return '';
        }
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

        return ( new Data($this->getBinding()) )->addStudentSubject($tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking, $tblSubject, null, null);
    }
}
