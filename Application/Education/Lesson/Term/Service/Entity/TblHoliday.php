<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.07.2016
 * Time: 08:14
 */

namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblHoliday")
 * @Cache(usage="READ_ONLY")
 */
class TblHoliday extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_FROM_DATE = 'FromDate';
    const ATTR_TO_DATE = 'ToDate';
    const ATTR_TBL_HOLIDAY_TYPE = 'tblHolidayType';

    /**
    * @Column(type="string")
    */
    protected $Name;

    /**
     * @Column(type="datetime")
     */
    protected $FromDate;

    /**
     * @Column(type="datetime")
     */
    protected $ToDate;

    /**
     * @Column(type="bigint")
     */
    protected $tblHolidayType;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getFromDate()
    {

        if (null === $this->FromDate) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->FromDate;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setFromDate(\DateTime $Date = null)
    {

        $this->FromDate = $Date;
    }

    /**
     * @return string
     */
    public function getToDate()
    {

        if (null === $this->ToDate) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->ToDate;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setToDate(\DateTime $Date = null)
    {

        $this->ToDate = $Date;
    }

    /**
     * @return bool|TblHolidayType
     */
    public function getTblHolidayType()
    {

        if (null === $this->tblHolidayType) {
            return false;
        } else {
            return Term::useService()->getHolidayTypeById($this->tblHolidayType);
        }
    }

    /**
     * @param TblHolidayType|null $tblHolidayType
     */
    public function setTblHolidayType(TblHolidayType $tblHolidayType = null)
    {

        $this->tblHolidayType = (null === $tblHolidayType ? null : $tblHolidayType->getId());
    }

}