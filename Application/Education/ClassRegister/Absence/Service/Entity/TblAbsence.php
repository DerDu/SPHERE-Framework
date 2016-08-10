<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.07.2016
 * Time: 08:59
 */

namespace SPHERE\Application\Education\ClassRegister\Absence\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblAbsence")
 * @Cache(usage="READ_ONLY")
 */
class TblAbsence extends Element
{

    const VALUE_STATUS_NULL = 0;
    const VALUE_STATUS_EXCUSED = 1;
    const VALUE_STATUS_UNEXCUSED = 2;

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="datetime")
     */
    protected $FromDate;

    /**
     * @Column(type="datetime")
     */
    protected $ToDate;

    /**
     * @Column(type="string")
     */
    protected $Remark;

    /**
     * @Column(type="smallint")
     */
    protected $Status;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {

        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {

        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
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
     * @return mixed
     */
    public function getRemark()
    {
        return $this->Remark;
    }

    /**
     * @param mixed $Remark
     */
    public function setRemark($Remark)
    {
        $this->Remark = $Remark;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param mixed $Status
     */
    public function setStatus($Status)
    {
        $this->Status = $Status;
    }

    /**
     * @param \DateTime $tillDate
     *
     * @return int
     */
    public function getDays(\DateTime $tillDate = null)
    {

        $countDays = 0;
        $fromDate = new \DateTime($this->getFromDate());

        if ($tillDate === null) {
            if ($this->getToDate()) {
                $toDate = new \DateTime($this->getToDate());
                if ($toDate >= $fromDate) {
                    $date = $fromDate;
                    while ($date <= $toDate) {

                        $countDays = $this->countThisDay($date, $countDays);

                        $date = $date->modify('+1 day');
                    }
                }
            } else {

                $countDays = $this->countThisDay($fromDate, $countDays);
            }
        } else {
            if ($tillDate >= $fromDate){
                if ($this->getToDate()) {
                    $toDate = new \DateTime($this->getToDate());
                    if ($toDate >= $fromDate) {
                        $date = $fromDate;
                        while ($date <= $toDate && $date <= $tillDate) {

                            $countDays = $this->countThisDay($date, $countDays);

                            $date = $date->modify('+1 day');
                        }
                    }
                } else {

                    $countDays = $this->countThisDay($fromDate, $countDays);
                }
            }
        }

        return $countDays;
    }

    /**
     * @param $date
     * @param $countDays
     * @return mixed
     */
    private function countThisDay(\DateTime $date, $countDays)
    {

        if ($date->format('w') != 0 && $date->format('w') != 6) {
            if ($this->getServiceTblDivision()
                && ($tblYear = $this->getServiceTblDivision()->getServiceTblYear())
                && !Term::useService()->getHolidayByDay($tblYear, $date)
            ) {
                $countDays++;
            }
        }

        return $countDays;
    }

}