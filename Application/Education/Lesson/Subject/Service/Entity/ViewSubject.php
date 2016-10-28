<?php
namespace SPHERE\Application\Education\Lesson\Subject\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewSubject")
 * @Cache(usage="READ_ONLY")
 */
class ViewSubject extends AbstractView
{

    const TBL_SUBJECT_ID = 'TblSubject_Id';
    const TBL_SUBJECT_ACRONYM = 'TblSubject_Acronym';
    const TBL_SUBJECT_NAME = 'TblSubject_Name';
    const TBL_SUBJECT_DESCRIPTION = 'TblSubject_Description';

    /**
     * @Column(type="string")
     */
    protected $TblSubject_Id;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Acronym;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Description;


    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Fach';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_SUBJECT_NAME, 'Fach: Name');
        $this->setNameDefinition(self::TBL_SUBJECT_ACRONYM, 'Fach: Kürzel');
        $this->setNameDefinition(self::TBL_SUBJECT_DESCRIPTION, 'Fach: Beschreibung');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_SUBJECT_ID, new ViewSubjectTeacher(), ViewSubjectTeacher::TBL_DIVISION_SUBJECT_SERVICE_TBL_SUBJECT);
        $this->addForeignView(self::TBL_SUBJECT_ID, new ViewDivisionSubject(), ViewDivisionSubject::TBL_DIVISION_SUBJECT_SERVICE_TBL_SUBJECT);
//        $this->addForeignView(self::TBL_SUBJECT_ID, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_SUBJECT_SERVICE_TBL_SUBJECT);
//        $this->addForeignView(self::TBL_DIVISION_TEACHER_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Subject::useService();
    }

    /**
     * TODO: Abstract
     *
     * Use this method to set disabled Properties with "setDisabledProperty()"
     *
     * @return void
     */
    public function loadDisableDefinition()
    {
        $this->setDisableDefinition( self::TBL_SUBJECT_DESCRIPTION );
    }


}