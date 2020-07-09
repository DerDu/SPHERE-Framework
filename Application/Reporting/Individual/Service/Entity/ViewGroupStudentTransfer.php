<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSchoolEnrollmentType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupStudentTransfer")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupStudentTransfer extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';
    // Aktuelle Schule
    const TBL_COMPANY_PROCESS_NAME = 'TblCompanyProcess_Name';
    const TBL_STUDENT_TRANSFER_PROCESS_COURSE = 'TblStudentTransferProcess_Course';
    const TBL_STUDENT_TRANSFER_PROCESS_REMARK = 'TblStudentTransferProcess_Remark';
    // Ersteinschulung
    const TBL_COMPANY_ENROLLMENT_NAME = 'TblCompanyEnrollment_Name';
    const TBL_STUDENT_TRANSFER_ENROLLMENT_TYPE = 'TblStudentTransferEnrollment_Type';
    const TBL_STUDENT_SCHOOL_ENROLLMENT_TYPE_NAME = 'TblStudentSchoolEnrollmentType_Name';
    const TBL_STUDENT_TRANSFER_ENROLLMENT_COURSE = 'TblStudentTransferEnrollment_Course';
    const TBL_STUDENT_TRANSFER_ENROLLMENT_TRANSFER_DATE = 'TblStudentTransferEnrollment_TransferDate';
    const TBL_STUDENT_TRANSFER_ENROLLMENT_REMARK = 'TblStudentTransferEnrollment_Remark';
    // Schüler Aufnahme
    const TBL_COMPANY_ARRIVE_NAME = 'TblCompanyArrive_Name';
    const TBL_STATE_COMPANY_ARRIVE_NAME = 'TblStateCompanyArrive_Name';
    const TBL_STUDENT_TRANSFER_ARRIVE_TYPE = 'TblStudentTransferArrive_Type';
    const TBL_STUDENT_TRANSFER_ARRIVE_COURSE = 'TblStudentTransferArrive_Course';
    const TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE = 'TblStudentTransferArrive_TransferDate';
    const TBL_STUDENT_TRANSFER_ARRIVE_REMARK = 'TblStudentTransferArrive_Remark';
    // Schüler Abgabe
    const TBL_COMPANY_LEAVE_NAME = 'TblCompanyLeave_Name';
    const TBL_STUDENT_TRANSFER_LEAVE_TYPE = 'TblStudentTransferLeave_Type';
    const TBL_STUDENT_TRANSFER_LEAVE_COURSE = 'TblStudentTransferLeave_Course';
    const TBL_STUDENT_TRANSFER_LEAVE_TRANSFER_DATE = 'TblStudentTransferLeave_TransferDate';
    const TBL_STUDENT_TRANSFER_LEAVE_REMARK = 'TblStudentTransferLeave_Remark';

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
    protected $TblCompanyEnrollment_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferEnrollment_Type;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSchoolEnrollmentType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferEnrollment_Course;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferEnrollment_TransferDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferEnrollment_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyArrive_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStateCompanyArrive_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferArrive_Type;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferArrive_Course;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferArrive_TransferDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferArrive_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyLeave_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferLeave_Type;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferLeave_Course;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferLeave_TransferDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferLeave_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyProcess_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferProcess_Course;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferProcess_Remark;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_COMPANY_PROCESS_NAME, 'Aktuell: Schule');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_PROCESS_COURSE, 'Aktuell: Bildungsgang');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_PROCESS_REMARK, 'Aktuell: Bemerkungen');

        $this->setNameDefinition(self::TBL_COMPANY_ENROLLMENT_NAME, 'Einschulung: Schule');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ENROLLMENT_TYPE, 'Einschulung: Schulart');
        $this->setNameDefinition(self::TBL_STUDENT_SCHOOL_ENROLLMENT_TYPE_NAME, 'Einschulung: Einschulungsart');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ENROLLMENT_COURSE, 'Einschulung: Bildungsgang');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ENROLLMENT_TRANSFER_DATE, 'Einschulung: Datum');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ENROLLMENT_REMARK, 'Einschulung: Bemerkungen');

        $this->setNameDefinition(self::TBL_COMPANY_ARRIVE_NAME, 'Aufnahme: Abgebende Schule / Kita');
        $this->setNameDefinition(self::TBL_STATE_COMPANY_ARRIVE_NAME, 'Aufnahme: Staatliche Stammschule');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_TYPE, 'Aufnahme: Letzte Schulart');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_COURSE, 'Aufnahme: Letzter Bildungsgang');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE, 'Aufnahme: Datum');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_REMARK, 'Aufnahme: Bemerkungen');

        $this->setNameDefinition(self::TBL_COMPANY_LEAVE_NAME, 'Abgabe: Aufnehmende Schule');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_LEAVE_TYPE, 'Abgabe: Letzte Schulart');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_LEAVE_COURSE, 'Abgabe: Letzter Bildungsgang');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_LEAVE_TRANSFER_DATE, 'Abgabe: Datum');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_LEAVE_REMARK, 'Abgabe: Bemerkungen');

        $this->setGroupDefinition('Schulverlauf', array(
            self::TBL_COMPANY_PROCESS_NAME,
            self::TBL_STUDENT_TRANSFER_PROCESS_COURSE,
            self::TBL_STUDENT_TRANSFER_PROCESS_REMARK
        ));

        $this->setGroupDefinition('Einschulung', array(
            self::TBL_COMPANY_ENROLLMENT_NAME,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_TYPE,
            self::TBL_STUDENT_SCHOOL_ENROLLMENT_TYPE_NAME,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_COURSE,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_TRANSFER_DATE,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_REMARK
        ));

        $this->setGroupDefinition('Schüler – Aufnahme', array(

            self::TBL_COMPANY_ARRIVE_NAME,
            self::TBL_STATE_COMPANY_ARRIVE_NAME,
            self::TBL_STUDENT_TRANSFER_ARRIVE_TYPE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_COURSE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_REMARK
        ));

        $this->setGroupDefinition('Schüler – Abgabe', array(
            self::TBL_COMPANY_LEAVE_NAME,
            self::TBL_STUDENT_TRANSFER_LEAVE_TYPE,
            self::TBL_STUDENT_TRANSFER_LEAVE_COURSE,
            self::TBL_STUDENT_TRANSFER_LEAVE_TRANSFER_DATE,
            self::TBL_STUDENT_TRANSFER_LEAVE_REMARK
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
            case self::TBL_STUDENT_SCHOOL_ENROLLMENT_TYPE_NAME:
                $Data = Student::useService()->getPropertyList( new TblStudentSchoolEnrollmentType(), TblStudentSchoolEnrollmentType::ATTR_NAME );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
