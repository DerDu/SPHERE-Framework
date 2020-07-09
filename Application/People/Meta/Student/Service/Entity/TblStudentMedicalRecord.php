<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentMedicalRecord")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentMedicalRecord extends Element
{

    const ATTR_ATTENDING_DOCTOR = 'AttendingDoctor';
    const ATTR_INSURANCE_STATE = 'InsuranceState';

    /**
     * @Column(type="text")
     */
    protected $Disease;
    /**
     * @Column(type="text")
     */
    protected $Medication;
    /**
     * @Column(type="string")
     */
    protected $AttendingDoctor;
    /**
     * @Column(type="bigint")
     */
    protected $InsuranceState;
    /**
     * @Column(type="string")
     */
    protected $Insurance;
    /**
     * @Column(type="datetime")
     */
    protected $MasernDate;
    /**
     * @Column(type="string")
     */
    protected $MasernDocumentType;
    /**
     * @Column(type="string")
     */
    protected $MasernCreatorType;

    /**
     * @return string
     */
    public function getDisease()
    {

        return $this->Disease;
    }

    /**
     * @param string $Disease
     */
    public function setDisease($Disease)
    {

        $this->Disease = $Disease;
    }

    /**
     * @return string
     */
    public function getMedication()
    {

        return $this->Medication;
    }

    /**
     * @param string $Medication
     */
    public function setMedication($Medication)
    {

        $this->Medication = $Medication;
    }

    /**
     * @return string
     */
    public function getAttendingDoctor()
    {

        return $this->AttendingDoctor;
    }

    /**
     * @param string $AttendingDoctor
     */
    public function setAttendingDoctor($AttendingDoctor = '')
    {

        $this->AttendingDoctor = $AttendingDoctor;
    }

    /**
     * @return string
     */
    public function getInsuranceState()
    {

        $value = '';
        if(($tblStudentInsuranceState = Student::useService()->getStudentInsuranceStateById($this->InsuranceState))){
            $value = $tblStudentInsuranceState->getName();
        }
        return $value;
    }

    /**
     * @return string
     */
    public function getInsuranceStateId()
    {

        return $this->InsuranceState;
    }

    /**
     * @param int $tblStudentInsuranceStateId
     */
    public function setInsuranceState($tblStudentInsuranceStateId = 0)
    {

        $this->InsuranceState = $tblStudentInsuranceStateId;
    }

    /**
     * @return string
     */
    public function getInsurance()
    {

        return $this->Insurance;
    }

    /**
     * @param string $Insurance
     */
    public function setInsurance($Insurance)
    {

        $this->Insurance = $Insurance;
    }

    /**
     * @return false|string
     */
    public function getMasernDate()
    {

        if (null === $this->MasernDate) {
            return false;
        }
        /** @var DateTime MasernDate */
        $MasernDate = $this->MasernDate;
        if ($MasernDate instanceof DateTime) {
            return $MasernDate->format('d.m.Y');
        } else {
            return (string)$MasernDate;
        }
    }

    /**
     * @param null|DateTime $MasernDate
     */
    public function setMasernDate(DateTime $MasernDate = null)
    {

        $this->MasernDate = $MasernDate;
    }

    /**
     * @return TblStudentMasernInfo|false
     */
    public function getMasernDocumentType()
    {

        if (null === $this->MasernDocumentType) {
            return false;
        } else {
            return Student::useService()->getStudentMasernInfoById($this->MasernDocumentType);
        }
    }

    /**
     * @param TblStudentMasernInfo|null $MasernDocumentType
     */
    public function setMasernDocumentType(TblStudentMasernInfo $MasernDocumentType = null)
    {

        $this->MasernDocumentType = ( null === $MasernDocumentType ? null : $MasernDocumentType->getId() );
    }

    /**
     * @return TblStudentMasernInfo|false
     */
    public function getMasernCreatorType()
    {

        if (null === $this->MasernCreatorType) {
            return false;
        } else {
            return Student::useService()->getStudentMasernInfoById($this->MasernCreatorType);
        }
    }

    /**
     * @param TblStudentMasernInfo|null $MasernCreatorType
     */
    public function setMasernCreatorType(TblStudentMasernInfo $MasernCreatorType = null)
    {

        $this->MasernCreatorType = ( null === $MasernCreatorType ? null : $MasernCreatorType->getId() );
    }

}
