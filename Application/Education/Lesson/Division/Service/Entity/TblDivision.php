<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\System\Database\Fitting\Element;

/**
 * e.g. 6 Alpha
 *
 * @Entity
 * @Table(name="tblDivision")
 * @Cache(usage="READ_ONLY")
 */
class TblDivision extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_LEVEL = 'tblLevel';
    const ATTR_YEAR = 'serviceTblYear';
    const SERVICE_TBL_COMPANY = 'serviceTblCompany';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="bigint")
     */
    protected $tblLevel;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;

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
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return bool|TblLevel
     */
    public function getTblLevel()
    {

        if (null === $this->tblLevel) {
            return false;
        } else {
            return Division::useService()->getLevelById($this->tblLevel);
        }
    }

    /**
     * @param null|TblLevel $tblLevel
     */
    public function setTblLevel(TblLevel $tblLevel = null)
    {

        $this->tblLevel = ( null === $tblLevel ? null : $tblLevel->getId() );
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

    /**
     * Level->Name + Division->Name
     *
     * @return string
     */
    public function getDisplayName()
    {

        if ($this->getTblLevel()) {
            if (strlen($this->getName()) >= 2) {
                // Gruppennamen mit dem Muster "x - xxxx" sollen an Klassen ohne Leerzeichen angefügt werden
                if(preg_match('!(^[a-z]{1,1} - [a-zA-Z]{2})!', $this->getName())) {
                    return $this->getTblLevel()->getName().$this->getName();
                } else {
                    // Gruppennamen die mehr als 1 Zeichen haben sollen durch ein Leerzeichen vom Level getrennt werden
                    // alpha etc.
                    return $this->getTblLevel()->getName().( is_numeric($this->getName()) ? '-' : ' ' ).$this->getName();
                }
            } else {
                // Gruppennamen sollen durch kein Leerzeichen vom Level getrennt werden
                return $this->getTblLevel()->getName().( is_numeric($this->getName()) ? '-' : '' ).$this->getName();
            }
        } else {
            return $this->getName();
        }
    }

    /**
     * @return string
     */
    public function getTypeName()
    {

        if ($this->getTblLevel()) {
            if ($this->getTblLevel()->getServiceTblType()) {
                return $this->getTblLevel()->getServiceTblType()->getName();
            } else {
                return new Warning('Schulart nicht vorhanden.');
            }
        } else {
            return '';
        }
    }

    /**
     * @return TblType|false
     */
    public function getType()
    {

        if(($tblLevel = $this->getTblLevel())){
            if(($tblType = $tblLevel->getServiceTblType())){
                return $tblType;
            }
        }
        return false;
    }
}
