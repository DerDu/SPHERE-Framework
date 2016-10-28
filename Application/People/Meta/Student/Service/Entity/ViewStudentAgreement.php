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
 * @Table(name="viewStudentAgreement")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentAgreement extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';

    const TBL_STUDENT_AGREEMENT_ID = 'TblStudentAgreement_Id';
    const TBL_STUDENT_AGREEMENT_TBL_STUDENT = 'TblStudentAgreement_tblStudent';
    const TBL_STUDENT_AGREEMENT_TBL_STUDENT_AGREEMENT_TYPE = 'TblStudentAgreement_tblStudentAgreementType';

    const TBL_STUDENT_AGREEMENT_TYPE_ID = 'TblStudentAgreementType_Id';
    const TBL_STUDENT_AGREEMENT_TYPE_NAME = 'TblStudentAgreementType_Name';
    const TBL_STUDENT_AGREEMENT_TYPE_TBL_STUDENT_AGREEMENT_CATEGORY = 'TblStudentAgreementType_tblStudentAgreementCategory';

    const TBL_STUDENT_AGREEMENT_CATEGORY_ID = 'TblStudentAgreementCategory_Id';
    const TBL_STUDENT_AGREEMENT_CATEGORY_NAME = 'TblStudentAgreementCategory_Name';


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
    protected $TblStudentAgreement_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentAgreement_tblStudent;
    /**
     * @Column(type="string")
     */
    protected $TblStudentAgreement_tblStudentAgreementType;

    /**
     * @Column(type="string")
     */
    protected $TblStudentAgreementType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentAgreementType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentAgreementType_tblStudentAgreementCategory;

    /**
     * @Column(type="string")
     */
    protected $TblStudentAgreementCategory_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentAgreementCategory_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Einverständnis';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_AGREEMENT_CATEGORY_NAME, 'Einverständnis: Kategorie');
        $this->setNameDefinition(self::TBL_STUDENT_AGREEMENT_TYPE_NAME, 'Einverständnis: Typ');
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
