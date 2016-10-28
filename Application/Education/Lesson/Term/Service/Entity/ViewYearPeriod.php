<?php
namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewYearPeriod")
 * @Cache(usage="READ_ONLY")
 */
class ViewYearPeriod extends AbstractView
{

    const TBL_YEAR_ID = 'TblYear_Id';
    const TBL_YEAR_YEAR = 'TblYear_Year';
    const TBL_YEAR_DESCRIPTION = 'TblYear_Description';
//    const TBL_YEAR_NAME = 'TblYear_Name';

    const TBL_YEAR_PERIOD_ID = 'TblYearPeriod_Id';
    const TBL_YEAR_PERIOD_TBL_YEAR = 'TblYearPeriod_tblYear';
    const TBL_YEAR_PERIOD_TBL_PERIOD = 'TblYearPeriod_tblPeriod';

    const TBL_PERIOD_ID = 'TblPeriod_Id';
    const TBL_PERIOD_NAME = 'TblPeriod_Name';
    const TBL_PERIOD_DESCRIPTION = 'TblPeriod_Description';
    const TBL_PERIOD_FROM_DATE = 'TblPeriod_FromDate';
    const TBL_PERIOD_TO_DATE = 'TblPeriod_ToDate';

    /**
     * @Column(type="string")
     */
    protected $TblYear_Id;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Year;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Description;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblYear_Name;

    /**
     * @Column(type="string")
     */
    protected $TblYearPeriod_Id;
    /**
     * @Column(type="string")
     */
    protected $TblYearPeriod_tblYear;
    /**
     * @Column(type="string")
     */
    protected $TblYearPeriod_tblPeriod;

    /**
     * @Column(type="string")
     */
    protected $TblPeriod_Id;
    /**
     * @Column(type="string")
     */
    protected $TblPeriod_Name;
    /**
     * @Column(type="string")
     */
    protected $TblPeriod_Description;
    /**
     * @Column(type="string")
     */
    protected $TblPeriod_FromDate;
    /**
     * @Column(type="string")
     */
    protected $TblPeriod_ToDate;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Jahrgang';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_YEAR_YEAR, 'Jahrgang: Schuljahr');
        $this->setNameDefinition(self::TBL_YEAR_DESCRIPTION, 'Jahrgang: Beschreibung');
        $this->setNameDefinition(self::TBL_PERIOD_NAME, 'Zeitraum: Name');
        $this->setNameDefinition(self::TBL_PERIOD_FROM_DATE, 'Zeitraum: Beginn');
        $this->setNameDefinition(self::TBL_PERIOD_TO_DATE, 'Zeitraum: Ende');
        $this->setNameDefinition(self::TBL_PERIOD_DESCRIPTION, 'Zeitraum: Beschreibung');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_YEAR_ID, new ViewDivision(), ViewDivision::TBL_DIVISION_TBL_YEAR);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Term::useService();
    }

}