<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\ViewSubject;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewSubjectTeacher")
 * @Cache(usage="READ_ONLY")
 */
class ViewSubjectTeacher extends AbstractView
{

    const TBL_SUBJECT_TEACHER_ID = 'TblSubjectTeacher_Id';
    const TBL_SUBJECT_TEACHER_SERVICE_TBL_PERSON = 'TblSubjectTeacher_serviceTblPerson';
    const TBL_SUBJECT_TEACHER_TBL_DIVISION_SUBJECT = 'TblSubjectTeacher_tblDivisionSubject';

    const TBL_DIVISION_SUBJECT_ID = 'TblDivisionSubject_Id';
    const TBL_DIVISION_SUBJECT_SERVICE_TBL_SUBJECT = 'TblDivisionSubject_serviceTblSubject';
    const TBL_DIVISION_SUBJECT_TBL_SUBJECT_GROUP = 'TblDivisionSubject_tblSubjectGroup';
    const TBL_DIVISION_SUBJECT_TBL_DIVISION = 'TblDivisionSubject_tblDivision';

    const TBL_LEVEL_ID = 'TblLevel_ID';
    const TBL_LEVEL_NAME = 'TblLevel_Name';
    const TBL_LEVEL_DESCRIPTION = 'TblLevel_Description';
    const TBL_LEVEL_IS_CHECKED = 'TblLevel_IsChecked';
    const TBL_LEVEL_SERVICE_TBL_TYPE = 'TblLevel_serviceTblType';

    const TBL_DIVISION_ID = 'TblDivision_Id';
    const TBL_DIVISION_NAME = 'TblDivision_Name';
    const TBL_DIVISION_DESCRIPTION = 'TblDivision_Description';
    const TBL_DIVISION_TBL_LEVEL = 'TblDivision_tblLevel';
    const TBL_DIVISION_TBL_YEAR = 'TblDivision_serviceTblYear';

    /**
     * @Column(type="string")
     */
    protected $TblSubjectTeacher_Id;
    /**
     * @Column(type="string")
     */
    protected $TblSubjectTeacher_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblSubjectTeacher_tblDivisionSubject;

    /**
     * @Column(type="string")
     */
    protected $TblDivisionSubject_Id;
    /**
     * @Column(type="string")
     */
    protected $TblDivisionSubject_serviceTblSubject;
    /**
     * @Column(type="string")
     */
    protected $TblDivisionSubject_tblSubjectGroup;
    /**
     * @Column(type="string")
     */
    protected $TblDivisionSubject_tblDivision;

    /**
     * @Column(type="string")
     */
    protected $TblLevel_Id;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_Name;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_Description;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_serviceTblType;

    /**
     * @Column(type="string")
     */
    protected $TblDivision_Id;
    /**
     * @Column(type="string")
     */
    protected $TblDivision_Name;

    /**
     * @Column(type="string")
     */
    protected $TblLevel_IsChecked;

    /**
     * @Column(type="string")
     */
    protected $TblDivision_Description;
    /**
     * @Column(type="string")
     */
    protected $TblDivision_tblLevel;
    /**
     * @Column(type="string")
     */
    protected $TblDivision_serviceTblYear;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Klassenstufen - Fachlehrer';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_LEVEL_NAME, 'Klasse: Stufe');
        $this->setNameDefinition(self::TBL_LEVEL_DESCRIPTION, 'Klasse: Beschreibung');
        $this->setNameDefinition(self::TBL_DIVISION_NAME, 'Klasse: Gruppenname');
        $this->setNameDefinition(self::TBL_DIVISION_DESCRIPTION, 'Klasse: Beschreibung');
        $this->setNameDefinition(self::TBL_LEVEL_IS_CHECKED, 'Klasse: Übergreifende Gruppe');
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
        parent::setDisableDefinition(self::TBL_LEVEL_DESCRIPTION);
        parent::setDisableDefinition(self::TBL_DIVISION_DESCRIPTION);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_SUBJECT_TEACHER_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
        $this->addForeignView(self::TBL_DIVISION_SUBJECT_SERVICE_TBL_SUBJECT, new ViewSubject(), ViewSubject::TBL_SUBJECT_ID);
//        $this->addForeignView(self::TBL_DIVISION_TBL_YEAR, new ViewYearPeriod(), ViewYearPeriod::TBL_YEAR_PERIOD_TBL_YEAR);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Division::useService();
    }
}