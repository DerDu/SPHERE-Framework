<?php

namespace SPHERE\Application\Education\ClassRegister\Absence\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewAbsence")
 * @Cache(usage="READ_ONLY")
 */
class ViewAbsence extends AbstractView
{

    const TBL_ABSENCE_ID = 'TblAbsence_Id';
    const TBL_ABSENCE_SERVICE_TBL_PERSON = 'TblAbsence_serviceTblPerson';
    const TBL_ABSENCE_SERVICE_TBL_DIVISION = 'TblAbsence_serviceTblDivision';
    const TBL_ABSENCE_FORM_DATE = 'TblAbsence_FromDate';
    const TBL_ABSENCE_TO_DATE = 'TblAbsence_ToDate';
    const TBL_ABSENCE_REMARK = 'TblAbsence_Remark';
//    const TBL_ABSENCE_STATUS = 'TblAbsence_Status'; // Boolen

    /**
     * @Column(type="string")
     */
    protected $TblAbsence_Id;
    /**
     * @Column(type="string")
     */
    protected $TblAbsence_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblAbsence_serviceTblDivision;
    /**
     * @Column(type="string")
     */
    protected $TblAbsence_FromDate;
    /**
     * @Column(type="string")
     */
    protected $TblAbsence_ToDate;
    /**
     * @Column(type="string")
     */
    protected $TblAbsence_Remark;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblAbsence_Status;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Fehltage';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_ABSENCE_FORM_DATE, 'Fehltage: Start Datum');
        $this->setNameDefinition(self::TBL_ABSENCE_TO_DATE, 'Fehltage: End Datum');
        $this->setNameDefinition(self::TBL_ABSENCE_REMARK, 'Fehltage: Bemerkung');
//        $this->setNameDefinition(self::TBL_ABSENCE_STATUS, 'Fehltage: Status');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_ABSENCE_SERVICE_TBL_DIVISION, new ViewDivision, ViewDivision::TBL_DIVISION_ID); // Schlechte verknüpfung
        $this->addForeignView(self::TBL_ABSENCE_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
//        $this->addForeignView(self::TBL_DIVISION_ID, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Absence::useService();
    }
}