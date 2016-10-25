<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDivisionCustody")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionCustody extends Element
{

    const ATTR_TBL_DIVISION = 'tblDivision';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $tblDivision;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @param bool $IsForce
     *
     * @return bool|TblDivision
     */
    public function getTblDivision($IsForce = false)
    {

        if (null === $this->tblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->tblDivision, $IsForce);
        }
    }

    /**
     * @param null|TblDivision $tblDivision
     */
    public function setTblDivision(TblDivision $tblDivision = null)
    {

        $this->tblDivision = ( null === $tblDivision ? null : $tblDivision->getId() );
    }

    /**
     * @param bool $IsForce
     *
     * @return bool|TblPerson
     */
    public function getServiceTblPerson($IsForce = false)
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson, $IsForce);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
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
}
