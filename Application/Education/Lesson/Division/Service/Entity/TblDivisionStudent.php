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
 * e.g. 6 Alpha - Student
 *
 * @Entity
 * @Table(name="tblDivisionStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionStudent extends Element
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
     * @Column(type="integer")
     */
    protected $SortOrder;

    /**
     * @return bool|TblDivision
     */
    public function getTblDivision()
    {

        if (null === $this->tblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->tblDivision);
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

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return integer|null
     */
    public function getSortOrder()
    {
        return $this->SortOrder;
    }

    /**
     * @param integer|null $SortOrder
     */
    public function setSortOrder($SortOrder)
    {
        $this->SortOrder = $SortOrder;
    }
}
