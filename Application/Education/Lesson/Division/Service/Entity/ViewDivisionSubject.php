<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\ViewSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\School\Type\Service\Entity\ViewSchoolType;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewDivisionSubject")
 * @Cache(usage="READ_ONLY")
 */
class ViewDivisionSubject extends AbstractView
{

    const TBL_LEVEL_ID = 'TblLevel_Id';
    const TBL_LEVEL_NAME = 'TblLevel_Name';
    const TBL_LEVEL_DESCRIPTION = 'TblLevel_Description';
    const TBL_LEVEL_IS_CHECKED = 'TblLevel_IsChecked';
    const TBL_LEVEL_SERVICE_TBL_TYPE = 'TblLevel_serviceTblType';

    const TBL_DIVISION_ID = 'TblDivision_Id';
    const TBL_DIVISION_NAME = 'TblDivision_Name';
    const TBL_DIVISION_DESCRIPTION = 'TblDivision_Description';
    const TBL_DIVISION_TBL_LEVEL = 'TblDivision_tblLevel';
    const TBL_DIVISION_TBL_YEAR = 'TblDivision_serviceTblYear';

    const TBL_DIVISION_SUBJECT_ID = 'TblDivisionSubject_Id';
    const TBL_DIVISION_SUBJECT_SERVICE_TBL_SUBJECT = 'TblDivisionSubject_serviceTblSubject';
    const TBL_DIVISION_SUBJECT_TBL_SUBJECT_GROUP = 'TblDivisionSubject_tblSubjectGroup';
    const TBL_DIVISION_SUBJECT_TBL_DIVISION = 'TblDivisionSubject_tblDivision';

    const TBL_SUBJECT_GROUP_ID = 'TblSubjectGroup_Id';
    const TBL_SUBJECT_GROUP_NAME = 'TblSubjectGroup_Name';
    const TBL_SUBJECT_GROUP_DESCRIPTION = 'TblSubjectGroup_Description';

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
    protected $TblSubjectGroup_Id;
    /**
     * @Column(type="string")
     */
    protected $TblSubjectGroup_Name;
    /**
     * @Column(type="string")
     */
    protected $TblSubjectGroup_Description;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Klassenstufen - Fachzuordnung';
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
        $this->setNameDefinition(self::TBL_SUBJECT_GROUP_NAME, 'Fachgruppe: Gruppe');
        $this->setNameDefinition(self::TBL_SUBJECT_GROUP_DESCRIPTION, 'Fachgruppe: Beschreibung');
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

        $this->addForeignView(self::TBL_DIVISION_ID, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_ID);
        $this->addForeignView(self::TBL_DIVISION_ID, new ViewDivisionTeacher(), ViewDivisionTeacher::TBL_DIVISION_ID);
        $this->addForeignView(self::TBL_LEVEL_SERVICE_TBL_TYPE, new ViewSchoolType(), ViewSchoolType::TBL_TYPE_ID);
        $this->addForeignView(self::TBL_DIVISION_TBL_YEAR, new ViewYear(), ViewYear::TBL_YEAR_ID);
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

    /**
     * @return mixed
     */
    public function getTblLevel_IsChecked()
    {

        if (null !== $this->TblLevel_IsChecked) {
            return $this->TblLevel_IsChecked ? 'Ja' : 'Nein';
        }
        return '';
    }
}