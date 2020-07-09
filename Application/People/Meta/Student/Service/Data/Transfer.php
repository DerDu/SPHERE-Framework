<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSchoolEnrollmentType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Transfer
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Transfer extends Agreement
{

    /**
     * @param string $Identifier
     * @param string $Name
     *
     * @return TblStudentTransferType
     */
    public function createStudentTransferType($Identifier, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentTransferType')->findOneBy(array(
            TblStudentTransferType::ATTR_IDENTIFIER => $Identifier
        ));
        if (null === $Entity) {
            $Entity = new TblStudentTransferType();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransfer
     */
    public function getStudentTransferById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentTransfer',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferType
     */
    public function getStudentTransferTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentTransferType',
            $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudent
     */
    public function getStudentTransferTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentTransferType', array(
                TblStudentTransferType::ATTR_IDENTIFIER => strtoupper($Identifier)
            ));
    }

    /**
     * @param TblStudent             $tblStudent
     * @param TblStudentTransferType $tblStudentTransferType
     *
     * @return bool|TblStudentTransfer
     */
    public function getStudentTransferByType(TblStudent $tblStudent, TblStudentTransferType $tblStudentTransferType)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentTransfer', array(
                TblStudentTransfer::ATTR_TBL_STUDENT       => $tblStudent->getId(),
                TblStudentTransfer::ATTR_TBL_TRANSFER_TYPE => $tblStudentTransferType->getId()
            ));
    }

    /**
     * @param TblStudent                          $tblStudent
     * @param TblStudentTransferType              $tblStudentTransferType
     * @param TblCompany|null                     $tblCompany
     * @param TblCompany|null                     $tblStateCompany
     * @param TblType|null                        $tblType
     * @param TblCourse|null                      $tblCourse
     * @param string                              $TransferDate
     * @param string                              $Remark
     * @param TblStudentSchoolEnrollmentType|null $tblStudentSchoolEnrollmentType
     *
     * @return TblStudentTransfer
     */
    public function createStudentTransfer(
        TblStudent $tblStudent,
        TblStudentTransferType $tblStudentTransferType,
        TblCompany $tblCompany = null,
        TblCompany $tblStateCompany = null,
        TblType $tblType = null,
        TblCourse $tblCourse = null,
        $TransferDate = '',
        $Remark = '',
        TblStudentSchoolEnrollmentType $tblStudentSchoolEnrollmentType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentTransfer')->findOneBy(array(
            TblStudentTransfer::ATTR_TBL_STUDENT       => $tblStudent->getId(),
            TblStudentTransfer::ATTR_TBL_TRANSFER_TYPE => $tblStudentTransferType->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblStudentTransfer();

            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentTransferType($tblStudentTransferType);
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setServiceTblStateCompany($tblStateCompany);
            $Entity->setServiceTblType($tblType);
            $Entity->setServiceTblCourse($tblCourse);
            $Entity->setTransferDate(( $TransferDate ? new \DateTime($TransferDate) : null ));
            $Entity->setRemark($Remark);
            $Entity->setTblStudentSchoolEnrollmentType($tblStudentSchoolEnrollmentType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblStudentTransfer $tblStudentTransfer
     * @param TblStudent $tblStudent
     * @param TblStudentTransferType $tblStudentTransferType
     * @param TblCompany|null $tblCompany
     * @param TblCompany|null $tblStateCompany
     * @param TblType|null $tblType
     * @param TblCourse|null $tblCourse
     * @param $TransferDate
     * @param $Remark
     * @param TblStudentSchoolEnrollmentType|null $tblStudentSchoolEnrollmentType
     *
     * @return bool
     */
    public function updateStudentTransfer(
        TblStudentTransfer $tblStudentTransfer,
        TblStudent $tblStudent,
        TblStudentTransferType $tblStudentTransferType,
        TblCompany $tblCompany = null,
        TblCompany $tblStateCompany = null,
        TblType $tblType = null,
        TblCourse $tblCourse = null,
        $TransferDate,
        $Remark,
        TblStudentSchoolEnrollmentType $tblStudentSchoolEnrollmentType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentTransfer $Entity */
        $Entity = $Manager->getEntityById('TblStudentTransfer', $tblStudentTransfer->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;

            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentTransferType($tblStudentTransferType);
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setServiceTblStateCompany($tblStateCompany);
            $Entity->setServiceTblType($tblType);
            $Entity->setServiceTblCourse($tblCourse);
            $Entity->setTransferDate(( $TransferDate ? new \DateTime($TransferDate) : null ));
            $Entity->setRemark($Remark);
            $Entity->setTblStudentSchoolEnrollmentType($tblStudentSchoolEnrollmentType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }
}
