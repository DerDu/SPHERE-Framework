<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreement;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;

/**
 * Class Agreement
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Agreement extends Liberation
{

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentAgreement[]
     */
    public function getStudentAgreementAllByStudent(TblStudent $tblStudent)
    {

        return (new Data($this->getBinding()))->getStudentAgreementAllByStudent($tblStudent);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreement
     */
    public function getStudentAgreementById($Id)
    {

        return (new Data($this->getBinding()))->getStudentAgreementById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreementType
     */
    public function getStudentAgreementTypeById($Id)
    {

        return (new Data($this->getBinding()))->getStudentAgreementTypeById($Id);
    }

    /**
     * @return bool|TblStudentAgreementType[]
     */
    public function getStudentAgreementTypeAll()
    {

        return (new Data($this->getBinding()))->getStudentAgreementTypeAll();
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     *
     * @return bool|TblStudentAgreementType[]
     */
    public function getStudentAgreementTypeAllByCategory(TblStudentAgreementCategory $tblStudentAgreementCategory)
    {

        return (new Data($this->getBinding()))->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreementCategory
     */
    public function getStudentAgreementCategoryById($Id)
    {

        return (new Data($this->getBinding()))->getStudentAgreementCategoryById($Id);
    }

    /**
     * @return bool|TblStudentAgreementCategory[]
     */
    public function getStudentAgreementCategoryAll()
    {

        return (new Data($this->getBinding()))->getStudentAgreementCategoryAll();
    }
}
