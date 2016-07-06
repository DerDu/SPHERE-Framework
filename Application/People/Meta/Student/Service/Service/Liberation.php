<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberation;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationType;

/**
 * Class Liberation
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Liberation extends Student
{

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentLiberation[]
     */
    public function getStudentLiberationAllByStudent(TblStudent $tblStudent)
    {

        return (new Data($this->getBinding()))->getStudentLiberationAllByStudent($tblStudent);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLiberation
     */
    public function getStudentLiberationById($Id)
    {

        return (new Data($this->getBinding()))->getStudentLiberationById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLiberationType
     */
    public function getStudentLiberationTypeById($Id)
    {

        return (new Data($this->getBinding()))->getStudentLiberationTypeById($Id);
    }

    /**
     * @return bool|TblStudentLiberationType[]
     */
    public function getStudentLiberationTypeAll()
    {

        return (new Data($this->getBinding()))->getStudentLiberationTypeAll();
    }

    /**
     * @param TblStudentLiberationCategory $tblStudentLiberationCategory
     *
     * @return bool|TblStudentLiberationType[]
     */
    public function getStudentLiberationTypeAllByCategory(TblStudentLiberationCategory $tblStudentLiberationCategory)
    {

        return (new Data($this->getBinding()))->getStudentLiberationTypeAllByCategory($tblStudentLiberationCategory);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLiberationCategory
     */
    public function getStudentLiberationCategoryById($Id)
    {

        return (new Data($this->getBinding()))->getStudentLiberationCategoryById($Id);
    }

    /**
     * @return bool|TblStudentLiberationCategory[]
     */
    public function getStudentLiberationCategoryAll()
    {

        return (new Data($this->getBinding()))->getStudentLiberationCategoryAll();
    }
}
