<?php
namespace SPHERE\Application\Education\Graduation\Evaluation\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblTask")
 * @Cache(usage="READ_ONLY")
 */
class TblTask extends Element
{

    const ATTR_TBL_TEST_TYPE = 'tblTestType';
    const ATTR_SERVICE_TBL_PERIOD = 'serviceTblPeriod';
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

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
    protected $tblTestType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPeriod;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblScoreType;

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
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @return bool|TblTestType
     */
    public function getTblTestType()
    {

        if (null === $this->tblTestType) {
            return false;
        } else {
            return Evaluation::useService()->getTestTypeById($this->tblTestType);
        }
    }

    /**
     * @param TblTestType|null $tblTestType
     */
    public function setTblTestType($tblTestType)
    {

        $this->tblTestType = ( null === $tblTestType ? null : $tblTestType->getId() );
    }

    /**
     * @return bool|TblPeriod
     */
    public function getServiceTblPeriod()
    {

        if (null === $this->serviceTblPeriod) {
            return false;
        } else {
            return Term::useService()->getPeriodById($this->serviceTblPeriod);
        }
    }

    /**
     * @param TblPeriod|null $tblPeriod
     */
    public function setServiceTblPeriod(TblPeriod $tblPeriod = null)
    {

        $this->serviceTblPeriod = ( null === $tblPeriod ? null : $tblPeriod->getId() );
    }

    /**
     * @return bool
     */
    public function isInEditPeriod()
    {

        if ($this->getFromDate() && $this->getToDate()){
            $fromDate = $this->FromDate;
            if ($fromDate instanceof \DateTime) {
                $fromDate = $fromDate->format('Y-m-d');
            }

            $toDate = $this->ToDate;
            if ($toDate instanceof \DateTime) {
                $toDate = $toDate->format('Y-m-d');
            }

            if ($fromDate && $toDate){
                $now = (new \DateTime('now'))->format("Y-m-d");

                if ($fromDate <= $now && $now <= $toDate){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isBeforeEditPeriod()
    {

        if ($this->getFromDate()){
            $fromDate = $this->FromDate;
            if ($fromDate instanceof \DateTime) {
                $fromDate = $fromDate->format('Y-m-d');
            }

            if ($fromDate){
                $now = (new \DateTime('now'))->format("Y-m-d");

                if ($fromDate > $now){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isAfterEditPeriod()
    {

        if ($this->getFromDate()){
            $toDate = $this->ToDate;
            if ($toDate instanceof \DateTime) {
                $toDate = $toDate->format('Y-m-d');
            }

            if ($toDate){
                $now = (new \DateTime('now'))->format("Y-m-d");

                if ($toDate < $now){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getFromDate()
    {

        if (null === $this->Date) {
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

        if (null === $this->Date) {
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
     * @return bool|TblScoreType
     */
    public function getServiceTblScoreType()
    {

        if (null === $this->serviceTblScoreType) {
            return false;
        } else {
            return Gradebook::useService()->getScoreTypeById($this->serviceTblScoreType);
        }
    }

    /**
     * @param TblScoreType|null $tblScoreType
     */
    public function setServiceTblScoreType(TblScoreType $tblScoreType = null)
    {

        $this->serviceTblScoreType = ( null === $tblScoreType ? null : $tblScoreType->getId() );
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {

        if (null === $this->serviceTblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->serviceTblYear);
        }
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear = null)
    {

        $this->serviceTblYear = ( null === $tblYear ? null : $tblYear->getId() );
    }

    /**
     * Bestimmt ob zum Notenauftrag bereits Noten vergeben sind
     *
     * @return bool
     */
    public function isLocked()
    {

        $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($this);
        if ($tblTestAllByTask){
            foreach ($tblTestAllByTask as $tblTest){
                $tblGradeListByTest = Gradebook::useService()->getGradeAllByTest($tblTest);
                if ($tblGradeListByTest){
                    return true;
                }
            }
        }

        return false;
    }

}
