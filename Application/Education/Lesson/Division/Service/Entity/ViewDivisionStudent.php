<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewDivisionStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewDivisionStudent extends AbstractView
{

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
    protected $TblDivisionStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblDivision_Name;
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
    protected $TblDivision_Id;
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
    protected $TblLevel_IsChecked;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_serviceTblType;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_Id;

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
        return Division::useService();
    }
}