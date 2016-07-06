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
 * @Table(name="tblStudentLiberationType")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentLiberationType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_TBL_STUDENT_LIBERATION_CATEGORY = 'tblStudentLiberationCategory';

    /**
     * @Column(type="bigint")
     */
    protected $tblStudentLiberationCategory;
    /**
     * @Column(type="string")
     */
    protected $Name;

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
     * @return bool|TblStudentLiberationCategory
     */
    public function getTblStudentLiberationCategory()
    {

        if (null === $this->tblStudentLiberationCategory) {
            return false;
        } else {
            return Student::useService()->getStudentLiberationCategoryById($this->tblStudentLiberationCategory);
        }
    }

    /**
     * @param TblStudentLiberationCategory|null $tblStudentLiberationCategory
     */
    public function setTblStudentLiberationCategory(
        TblStudentLiberationCategory $tblStudentLiberationCategory = null
    ) {

        $this->tblStudentLiberationCategory = ( null === $tblStudentLiberationCategory ? null : $tblStudentLiberationCategory->getId() );
    }
}
