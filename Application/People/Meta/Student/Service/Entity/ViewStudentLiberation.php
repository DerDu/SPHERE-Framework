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
 * @Table(name="viewStudentLiberation")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentLiberation extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';

    const TBL_STUDENT_LIBERATION_ID = 'TblStudentLiberation_Id';
    const TBL_STUDENT_LIBERATION_TBL_STUDENT = 'TblStudentLiberation_tblStudent';
    const TBL_STUDENT_LIBERATION_TBL_STUDENT_LIBERATION_TYPE = 'TblStudentLiberation_tblStudentLiberationType';

    const TBL_STUDENT_LIBERATION_TYPE_ID = 'TblStudentLiberationType_Id';
    const TBL_STUDENT_LIBERATION_TYPE_NAME = 'TblStudentLiberationType_Name';
    const TBL_STUDENT_LIBERATION_TYPE_TBL_STUDENT_LIBERATION_CATEGORY = 'TblStudentLiberationType_tblStudentLiberationCategory';

    const TBL_STUDENT_LIBERATION_CATEGORY_ID = 'TblStudentLiberationCategory_Id';
    const TBL_STUDENT_LIBERATION_CATEGORY_NAME = 'TblStudentLiberationCategory_Name';

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
    protected $TblStudentLiberation_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberation_tblStudent;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberation_tblStudentLiberationType;

    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationType_tblStudentLiberationCategory;

    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationCategory_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationCategory_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Befreiung';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_LIBERATION_TYPE_NAME, 'Typ: Typ der Befreiung');
        $this->setNameDefinition(self::TBL_STUDENT_LIBERATION_CATEGORY_NAME, 'Kategorie: Art der Befreiung');

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
