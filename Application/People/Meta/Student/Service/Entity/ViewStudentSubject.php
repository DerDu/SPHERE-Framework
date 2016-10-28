<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\ViewSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudentSubject")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentSubject extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';

    const TBL_STUDENT_SUBJECT_ID = 'TblStudentSubject_Id';
    const TBL_STUDENT_SUBJECT_TBL_STUDENT = 'TblStudentSubject_tblStudent';
    const TBL_STUDENT_SUBJECT_TBL_STUDENT_SUBJECT_TYPE = 'TblStudentSubject_tblStudentSubjectType';
    const TBL_STUDENT_SUBJECT_TBL_STUDENT_SUBJECT_RANKING = 'TblStudentSubject_tblStudentSubjectRanking';
    const TBL_STUDENT_SUBJECT_SERVICE_TBL_SUBJECT = 'TblStudentSubject_serviceTblSubject';
    const TBL_STUDENT_SUBJECT_SERVICE_TBL_LEVEL_FROM = 'TblStudentSubject_serviceTblLevelFrom';
    const TBL_STUDENT_SUBJECT_SERVICE_TBL_LEVEL_TILL = 'TblStudentSubject_serviceTblLevelTill';

    const TBL_STUDENT_SUBJECT_RANKING_ID = 'TblStudentSubjectRanking_Id';
    const TBL_STUDENT_SUBJECT_RANKING_NAME = 'TblStudentSubjectRanking_Name';

    const TBL_STUDENT_SUBJECT_TYPE_ID = 'TblStudentSubjectType_Id';
    const TBL_STUDENT_SUBJECT_TYPE_NAME = 'TblStudentSubjectType_Name';

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
    protected $TblStudentSubject_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_tblStudent;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_tblStudentSubjectType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_tblStudentSubjectRanking;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_serviceTblSubject;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_serviceTblLevelFrom;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubject_serviceTblLevelTill;

    /**
     * @Column(type="string")
     */
    protected $TblStudentSubjectRanking_Id;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblStudentSubjectRanking_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubjectRanking_Name;

    /**
     * @Column(type="string")
     */
    protected $TblStudentSubjectType_Id;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblStudentSubjectType_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSubjectType_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Kategorie der Fächer';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_SUBJECT_RANKING_NAME, 'Fach: Reihenfolge');
        $this->setNameDefinition(self::TBL_STUDENT_SUBJECT_TYPE_NAME, 'Fach: Kategorie');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_STUDENT_SUBJECT_SERVICE_TBL_SUBJECT, new ViewSubject(), ViewSubject::TBL_SUBJECT_ID);
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
