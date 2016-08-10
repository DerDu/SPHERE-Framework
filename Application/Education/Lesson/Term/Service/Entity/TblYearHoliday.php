<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.07.2016
 * Time: 08:59
 */

namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblYearHoliday")
 * @Cache(usage="READ_ONLY")
 */
class TblYearHoliday extends Element
{

    const ATTR_TBL_HOLIDAY = 'tblHoliday';
    const ATTR_TBL_YEAR = 'tblYear';
    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';

    /**
     * @Column(type="bigint")
     */
    protected $tblHoliday;

    /**
     * @Column(type="bigint")
     */
    protected $tblYear;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;

    /**
     * @return bool|TblHoliday
     */
    public function getTblHoliday()
    {

        if (null === $this->tblHoliday) {
            return false;
        } else {
            return Term::useService()->getHolidayById($this->tblHoliday);
        }
    }

    /**
     * @param TblHoliday|null $tblHoliday
     */
    public function setTblHoliday(TblHoliday $tblHoliday = null)
    {

        $this->tblHoliday = (null === $tblHoliday ? null : $tblHoliday->getId());
    }

    /**
     * @return bool|TblYear
     */
    public function getTblYear()
    {

        if (null === $this->tblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->tblYear);
        }
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setTblYear(TblYear $tblYear = null)
    {

        $this->tblYear = (null === $tblYear ? null : $tblYear->getId());
    }

    /**
     * @return bool|TblCompany
     */
    public function getServiceTblCompany()
    {

        if (null === $this->serviceTblCompany) {
            return false;
        } else {
            return Company::useService()->getCompanyById($this->serviceTblCompany);
        }
    }

    /**
     * @param TblCompany|null $tblCompany
     */
    public function setServiceTblCompany(TblCompany $tblCompany = null)
    {

        $this->serviceTblCompany = ( null === $tblCompany ? null : $tblCompany->getId() );
    }

}