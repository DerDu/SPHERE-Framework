<?php
namespace SPHERE\Application\Education\ClassRegister\Diary\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblDiaryDivision")
 * @Cache(usage="READ_ONLY")
 */
class TblDiaryDivision extends Element
{
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_PREDECESSOR_DIVISION = 'serviceTblPredecessorDivision';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPredecessorDivision;

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
     * @return bool|TblDivision
     */
    public function getServiceTblPredecessorDivision()
    {
        if (null === $this->serviceTblPredecessorDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblPredecessorDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblPredecessorDivision(TblDivision $tblDivision = null)
    {
        $this->serviceTblPredecessorDivision = (null === $tblDivision ? null : $tblDivision->getId());
    }
}