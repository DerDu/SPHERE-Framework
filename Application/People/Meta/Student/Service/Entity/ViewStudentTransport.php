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
 * @Table(name="viewStudentTransport")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentTransport extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';

    const TBL_STUDENT_TRANSPORT_ID = 'TblStudentTransport_Id';
    const TBL_STUDENT_TRANSPORT_ROUTE = 'TblStudentTransport_Route';
    const TBL_STUDENT_TRANSPORT_STATION_ENTRANCE = 'TblStudentTransport_StationEntrance';
    const TBL_STUDENT_TRANSPORT_STATION_EXIT = 'TblStudentTransport_StationExit';
    const TBL_STUDENT_TRANSPORT_REMARK = 'TblStudentTransport_Remark';

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
    protected $TblStudentTransport_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_Route;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_StationEntrance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_StationExit;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_Remark;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Transport';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_ROUTE, 'Transport: Buslinie');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_STATION_ENTRANCE, 'Transport: Einstiegshaltestelle');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_STATION_EXIT, 'Transport: Ausstiegshaltestelle');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_REMARK, 'Transport: Bemerkung');
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
