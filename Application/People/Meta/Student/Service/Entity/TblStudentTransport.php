<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentTransport")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTransport extends Element
{

    /**
     * @Column(type="string")
     */
    protected $Route;
    /**
     * @Column(type="string")
     */
    protected $StationEntrance;
    /**
     * @Column(type="string")
     */
    protected $StationExit;
    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="boolean")
     */
    protected $IsDriverStudent;

    /**
     * @return string
     */
    public function getRoute()
    {

        return $this->Route;
    }

    /**
     * @param string $Route
     */
    public function setRoute($Route)
    {

        $this->Route = $Route;
    }

    /**
     * @return string
     */
    public function getStationEntrance()
    {

        return $this->StationEntrance;
    }

    /**
     * @param string $StationEntrance
     */
    public function setStationEntrance($StationEntrance)
    {

        $this->StationEntrance = $StationEntrance;
    }

    /**
     * @return string
     */
    public function getStationExit()
    {

        return $this->StationExit;
    }

    /**
     * @param string $StationExit
     */
    public function setStationExit($StationExit)
    {

        $this->StationExit = $StationExit;
    }

    /**
     * @return string
     */
    public function getRemark()
    {

        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
    }

    /**
     * @return boolean
     */
    public function getIsDriverStudent()
    {
        return $this->IsDriverStudent;
    }

    /**
     * @param boolean $IsDriverStudent
     */
    public function setIsDriverStudent($IsDriverStudent)
    {
        $this->IsDriverStudent = (boolean) $IsDriverStudent;
    }
}
