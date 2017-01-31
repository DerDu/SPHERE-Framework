<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\ViewAbsence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewDivisionStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewDivisionStudent extends AbstractView
{

    const TBL_DIVISION_STUDENT_ID = 'TblDivisionStudent_Id';
    const TBL_DIVISION_STUDENT_TBL_DIVISION = 'TblDivisionStudent_tblDivision';
    const TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON = 'TblDivisionStudent_serviceTblPerson';
    const TBL_DIVISION_STUDENT_SORT_ORDER = 'TblDivisionStudent_SortOrder';

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

    /**
     * @Column(type="string")
     */
    protected $TblDivisionStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblDivisionStudent_tblDivision;
    /**
     * @Column(type="string")
     */
    protected $TblDivisionStudent_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblDivisionStudent_SortOrder;

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

        return 'Klassenstufen - Schüler';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_LEVEL_NAME, 'Klasse: Stufe');
//        $this->setNameDefinition(self::TBL_LEVEL_DESCRIPTION, 'Klasse: Beschreibung');
        $this->setNameDefinition(self::TBL_DIVISION_NAME, 'Klasse: Gruppenname');
//        $this->setNameDefinition(self::TBL_DIVISION_DESCRIPTION, 'Klasse: Beschreibung');
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
        parent::setDisableDefinition(self::TBL_DIVISION_STUDENT_SORT_ORDER);
    }


    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
        $this->addForeignView(self::TBL_DIVISION_TBL_YEAR, new ViewYear(), ViewYear::TBL_YEAR_ID);
        $this->addForeignView(self::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, new ViewAbsence(), ViewAbsence::TBL_ABSENCE_SERVICE_TBL_PERSON);
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

    /**
     * @return bool|TblDivisionStudent
     */
    public function getTblDivisionStudent()
    {

        if (null === $this->TblDivisionStudent_Id) {
            return false;
        } else {
            return Division::useService()->getDivisionStudentById($this->TblDivisionStudent_Id);
        }
    }
}