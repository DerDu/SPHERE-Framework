<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentTransferProcess")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTransferProcess extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCourse;
    /**
     * @Column(type="text")
     */
    protected $Remark;

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
     * @return bool|TblType
     */
    public function getServiceTblType()
    {

        if (null === $this->serviceTblType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblType);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceTblType(TblType $tblType = null)
    {

        $this->serviceTblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblCourse()
    {

        if (null === $this->serviceTblCourse) {
            return false;
        } else {
            return Course::useService()->getCourseById($this->serviceTblCourse);
        }
    }

    /**
     * @param TblCourse|null $tblCourse
     */
    public function setServiceTblCourse(TblCourse $tblCourse = null)
    {

        $this->serviceTblCourse = ( null === $tblCourse ? null : $tblCourse->getId() );
    }
}
