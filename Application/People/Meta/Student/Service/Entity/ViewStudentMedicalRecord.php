<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudentMedicalRecord")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentMedicalRecord extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';

    const TBL_STUDENT_MEDICAL_RECORD_ID = 'TblStudentMedicalRecord_Id';
    const TBL_STUDENT_MEDICAL_RECORD_DISEASE = 'TblStudentMedicalRecord_Disease';
    const TBL_STUDENT_MEDICAL_RECORD_MEDICATION = 'TblStudentMedicalRecord_Medication';
    const TBL_STUDENT_MEDICAL_RECORD_SERVICE_TBL_PERSON_ATTENDING_DOCTOR = 'TblStudentMedicalRecord_serviceTblPersonAttendingDoctor';
    const TBL_STUDENT_MEDICAL_RECORD_INSURANCE = 'TblStudentMedicalRecord_Insurance';

    /**
     * @Column(type="string")
     */
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_serviceTblPerson;

    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Id;
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
    protected $TblStudentMedicalRecord_serviceTblPersonAttendingDoctor;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Insurance;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Krankenakte';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_DISEASE, 'Krankenakte: Krankheiten / Allergien');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MEDICATION, 'Krankenakte: Mediakamente');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE, 'Krankenakte: Krankenkasse');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_STUDENT_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
