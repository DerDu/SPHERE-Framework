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
 * @Table(name="viewStudentIntegration")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentIntegration extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';

    const TBL_STUDENT_INTEGRATION_ID = 'TblStudentIntegration_Id';
    const TBL_STUDENT_INTEGRATION_SERVICE_TBL_PERSON = 'TblStudentIntegration_serviceTblPerson';
    const TBL_STUDENT_INTEGRATION_SERVICE_TBL_COMPANY = 'TblStudentIntegration_serviceTblCompany';
    const TBL_STUDENT_INTEGRATION_COACHING_REQUEST_DATE = 'TblStudentIntegration_CoachingRequestDate';
    const TBL_STUDENT_INTEGRATION_COACHING_COUNSEL_DATE = 'TblStudentIntegration_CoachingCounselDate';
    const TBL_STUDENT_INTEGRATION_COACHING_DECISION_DATE = 'TblStudentIntegration_CoachingDecisionDate';
    const TBL_STUDENT_INTEGRATION_COACHING_TIME = 'TblStudentIntegration_CoachingTime';
    const TBL_STUDENT_INTEGRATION_COACHING_REMARK = 'TblStudentIntegration_CoachingRemark';

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
    protected $TblStudentIntegration_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_serviceTblCompany;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingRequestDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingCounselDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingDecisionDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingTime;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingRemark;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Förderbedarf: Antrag';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_REQUEST_DATE, 'Förderantrag: Beratgung');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_COUNSEL_DATE, 'Förderantrag vom');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_DECISION_DATE, 'Förderantrag: Bescheid SBA');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_TIME, 'Förderantrag: Wochenstunden');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_REMARK, 'Förderantrag: Bemerkung');

    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_STUDENT_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentDisorder(), ViewStudentDisorder::TBL_STUDENT_DISORDER_TBL_STUDENT);
        $this->addForeignView(self::TBL_STUDENT_ID, new ViewStudentFocus(), ViewStudentFocus::TBL_STUDENT_FOCUS_TBL_STUDENT);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Student::useService();
    }
}
