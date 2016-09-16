<?php
namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
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

    /**
     * @Column(type="string")
     */
    protected $TblYear_Name;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Year;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Description;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Id;
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
    protected $TblYearPeriod_Id;
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
     * @Column(type="string")
     */
    protected $TblPeriod_Id;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {
        // TODO: Implement loadNameDefinition() method.
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {
        // TODO: Implement loadViewGraph() method.
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Term::useService();
    }

}