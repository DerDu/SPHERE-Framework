<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
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
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblStudent
     */
    public function getStudentByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getStudentByPerson($tblPerson);
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
        $SchoolAttendanceStartDate = ''
    ) {

        $tblStudent = $this->getStudentByPerson($tblPerson);

        if ($tblStudent) {
            (new Data($this->getBinding()))->updateStudent(
                $tblStudent,
                $Identifier,
                $tblStudentMedicalRecord,
                $tblStudentTransport,
                $tblStudentBilling,
                $tblStudentLocker,
                $tblStudentBaptism,
                $tblStudentIntegration,
                $SchoolAttendanceStartDate
            );
        } else {
            $tblStudent = (new Data($this->getBinding()))->createStudent(
                $tblPerson,
                $Identifier,
                $tblStudentMedicalRecord,
                $tblStudentTransport,
                $tblStudentBilling,
                $tblStudentLocker,
                $tblStudentBaptism,
                $tblStudentIntegration,
                $SchoolAttendanceStartDate
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
}
