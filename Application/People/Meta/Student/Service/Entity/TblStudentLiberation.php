<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentLiberation")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentLiberation extends Element
{

    const ATTR_TBL_STUDENT = 'tblStudent';
    const ATTR_TBL_STUDENT_LIBERATION_TYPE = 'tblStudentLiberationType';

    /**
     * @Column(type="bigint")
     */
    protected $tblStudent;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentLiberationType;

    /**
     * @return bool|TblStudent
     */
    public function getTblStudent()
    {

        if (null === $this->tblStudent) {
            return false;
        } else {
            return Student::useService()->getStudentById($this->tblStudent);
        }
    }

    /**
     * @param null|TblStudent $tblStudent
     */
    public function setTblStudent(TblStudent $tblStudent = null)
    {

        $this->tblStudent = ( null === $tblStudent ? null : $tblStudent->getId() );
    }

    /**
     * @return bool|TblStudentLiberationType
     */
    public function getTblStudentLiberationType()
    {

        if (null === $this->tblStudentLiberationType) {
            return false;
        } else {
            return Student::useService()->getStudentLiberationTypeById($this->tblStudentLiberationType);
        }
    }

    /**
     * @param TblStudentLiberationType|null $tblStudentLiberationType
     */
    public function setTblStudentLiberationType(TblStudentLiberationType $tblStudentLiberationType = null)
    {

        $this->tblStudentLiberationType = ( null === $tblStudentLiberationType ? null : $tblStudentLiberationType->getId() );
    }
}
