<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSchoolEnrollmentType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Student
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Student extends AbstractService
{

    /**
     * @param int $Id
     *
     * @return bool|TblStudent
     */
    public function getStudentById($Id)
    {

        return (new Data($this->getBinding()))->getStudentById($Id);
    }

    /**
     * @param string $Identifier
     * @param bool   $isWithRemoved -> true = get also EntityRemove
     *
     * @return bool|TblStudent
     */
    public function getStudentByIdentifier($Identifier, $isWithRemoved = false)
    {

        return (new Data($this->getBinding()))->getStudentByIdentifier($Identifier, $isWithRemoved);
    }

    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblStudent
     */
    public function getStudentByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getStudentByPerson($tblPerson, $isForced);
    }

    /**
     * @return bool|TblStudent[]
     */
    public function getStudentAll()
    {

        return (new Data($this->getBinding()))->getStudentAll();
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $Identifier
     * @param null $tblStudentMedicalRecord
     * @param null $tblStudentTransport
     * @param null $tblStudentBilling
     * @param null $tblStudentLocker
     * @param null $tblStudentBaptism
     * @param null $tblStudentIntegration
     * @param string $SchoolAttendanceStartDate
     * @param bool $HasMigrationBackground
     * @param bool $IsInPreparationDivisionForMigrants
     *
     * @return bool|TblStudent
     */
    public function insertStudent(
        TblPerson $tblPerson,
        $Identifier,
        $tblStudentMedicalRecord = null,
        $tblStudentTransport = null,
        $tblStudentBilling = null,
        $tblStudentLocker = null,
        $tblStudentBaptism = null,
        $tblStudentIntegration = null,
        $SchoolAttendanceStartDate = '',
        $HasMigrationBackground = false,
        $IsInPreparationDivisionForMigrants = false
    ) {

        $tblStudent = $this->getStudentByPerson($tblPerson);

        if ($tblStudent) {
            (new Data($this->getBinding()))->updateStudent(
                $tblStudent,
                '',
                $Identifier,
                $tblStudentMedicalRecord,
                $tblStudentTransport,
                $tblStudentBilling,
                $tblStudentLocker,
                $tblStudentBaptism,
                $tblStudentIntegration,
                $SchoolAttendanceStartDate,
                $HasMigrationBackground,
                $IsInPreparationDivisionForMigrants
            );
        } else {
            $tblStudent = (new Data($this->getBinding()))->createStudent(
                $tblPerson,
                '',
                $Identifier,
                $tblStudentMedicalRecord,
                $tblStudentTransport,
                $tblStudentBilling,
                $tblStudentLocker,
                $tblStudentBaptism,
                $tblStudentIntegration,
                $SchoolAttendanceStartDate,
                $HasMigrationBackground,
                $IsInPreparationDivisionForMigrants
            );
        }

        return $tblStudent;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|\SPHERE\Application\Education\School\Course\Service\Entity\TblCourse
     */
    public function getCourseByPerson(TblPerson $tblPerson)
    {

        $tblStudent = $this->getStudentByPerson($tblPerson);
        if ($tblStudent){
            return $this->getCourseByStudent($tblStudent);
        } else {
            return false;
        }
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|\SPHERE\Application\Education\School\Course\Service\Entity\TblCourse
     */
    public function getCourseByStudent(TblStudent $tblStudent)
    {

        $tblTransferType = \SPHERE\Application\People\Meta\Student\Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
        if ($tblTransferType) {
            $tblStudentTransfer = \SPHERE\Application\People\Meta\Student\Student::useService()->getStudentTransferByType($tblStudent,
                $tblTransferType);
            if ($tblStudentTransfer) {
                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                if ($tblCourse) {
                    return $tblCourse;
                }
            }
        }

        return false;
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool
     */
    public function destroyStudent(TblStudent $tblStudent)
    {

        return (new Data($this->getBinding()))->destroyStudent($tblStudent);
    }


    /**
     * @param $Id
     *
     * @return false|TblStudentSchoolEnrollmentType
     */
    public function getStudentSchoolEnrollmentTypeById($Id)
    {

        return (new Data($this->getBinding()))->getStudentSchoolEnrollmentTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblStudentSchoolEnrollmentType
     */
    public function getStudentSchoolEnrollmentTypeByIdentifier($Identifier)
    {
        return (new Data($this->getBinding()))->getStudentSchoolEnrollmentTypeByIdentifier($Identifier);
    }

    /**
     * @return false|TblStudentSchoolEnrollmentType[]
     */
    public function getStudentSchoolEnrollmentTypeAll()
    {

        return (new Data($this->getBinding()))->getStudentSchoolEnrollmentTypeAll();
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool
     */
    public function restoreStudent(TblStudent $tblStudent)
    {

        return (new Data($this->getBinding()))->restoreStudent($tblStudent);
    }
}
