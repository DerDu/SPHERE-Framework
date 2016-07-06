<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblStudent extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="string")
     */
    protected $Identifier;

    /**
     * @Column(type="datetime")
     */
    protected $SchoolAttendanceStartDate;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentMedicalRecord;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransport;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentBilling;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentLocker;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentBaptism;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentIntegration;

    /**
     * @return bool|TblStudentMedicalRecord
     */
    public function getTblStudentMedicalRecord()
    {

        if (null === $this->tblStudentMedicalRecord) {
            return false;
        } else {
            return Student::useService()->getStudentMedicalRecordById($this->tblStudentMedicalRecord);
        }
    }

    /**
     * @param null|TblStudentMedicalRecord $tblStudentMedicalRecord
     */
    public function setTblStudentMedicalRecord(TblStudentMedicalRecord $tblStudentMedicalRecord = null)
    {

        $this->tblStudentMedicalRecord = ( null === $tblStudentMedicalRecord ? null : $tblStudentMedicalRecord->getId() );
    }

    /**
     * @return bool|TblStudentTransport
     */
    public function getTblStudentTransport()
    {

        if (null === $this->tblStudentTransport) {
            return false;
        } else {
            return Student::useService()->getStudentTransportById($this->tblStudentTransport);
        }
    }

    /**
     * @param null|TblStudentTransport $tblStudentTransport
     */
    public function setTblStudentTransport(TblStudentTransport $tblStudentTransport = null)
    {

        $this->tblStudentTransport = ( null === $tblStudentTransport ? null : $tblStudentTransport->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblStudentBilling
     */
    public function getTblStudentBilling()
    {

        if (null === $this->tblStudentBilling) {
            return false;
        } else {
            return Student::useService()->getStudentBillingById($this->tblStudentBilling);
        }
    }

    /**
     * @param null|TblStudentBilling $tblStudentBilling
     */
    public function setTblStudentBilling(TblStudentBilling $tblStudentBilling = null)
    {

        $this->tblStudentBilling = ( null === $tblStudentBilling ? null : $tblStudentBilling->getId() );
    }

    /**
     * @return bool|TblStudentBaptism
     */
    public function getTblStudentBaptism()
    {

        if (null === $this->tblStudentBaptism) {
            return false;
        } else {
            return Student::useService()->getStudentBaptismById($this->tblStudentBaptism);
        }
    }

    /**
     * @param null|TblStudentBaptism $tblStudentBaptism
     */
    public function setTblStudentBaptism(TblStudentBaptism $tblStudentBaptism = null)
    {

        $this->tblStudentBaptism = ( null === $tblStudentBaptism ? null : $tblStudentBaptism->getId() );
    }

    /**
     * @return bool|TblStudentLocker
     */
    public function getTblStudentLocker()
    {

        if (null === $this->tblStudentLocker) {
            return false;
        } else {
            return Student::useService()->getStudentLockerById($this->tblStudentLocker);
        }
    }

    /**
     * @param null|TblStudentLocker $tblStudentLocker
     */
    public function setTblStudentLocker(TblStudentLocker $tblStudentLocker = null)
    {

        $this->tblStudentLocker = ( null === $tblStudentLocker ? null : $tblStudentLocker->getId() );
    }

    /**
     * @return bool|TblStudentIntegration
     */
    public function getTblStudentIntegration()
    {

        if (null === $this->tblStudentIntegration) {
            return false;
        } else {
            return Student::useService()->getStudentIntegrationById($this->tblStudentIntegration);
        }
    }

    /**
     * @param null|TblStudentIntegration $tblStudentIntegration
     */
    public function setTblStudentIntegration(TblStudentIntegration $tblStudentIntegration = null)
    {

        $this->tblStudentIntegration = ( null === $tblStudentIntegration ? null : $tblStudentIntegration->getId() );
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getSchoolAttendanceStartDate()
    {

        if (null === $this->SchoolAttendanceStartDate) {
            return false;
        }
        /** @var \DateTime $SchoolAttendanceStartDate */
        $SchoolAttendanceStartDate = $this->SchoolAttendanceStartDate;
        if ($SchoolAttendanceStartDate instanceof \DateTime) {
            return $SchoolAttendanceStartDate->format('d.m.Y');
        } else {
            return (string)$SchoolAttendanceStartDate;
        }
    }

    /**
     * @param null|\DateTime $SchoolAttendanceStartDate
     */
    public function setSchoolAttendanceStartDate(\DateTime $SchoolAttendanceStartDate = null)
    {

        $this->SchoolAttendanceStartDate = $SchoolAttendanceStartDate;
    }

}
