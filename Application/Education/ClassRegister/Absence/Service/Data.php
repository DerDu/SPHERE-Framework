<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.07.2016
 * Time: 08:57
 */

namespace SPHERE\Application\Education\ClassRegister\Absence\Service;

use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision|null $tblDivision
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, TblDivision $tblDivision = null)
    {

        if ($tblDivision) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence',
                array(
                    TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblAbsence::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
                )
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence',
                array(
                    TblAbsence::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                )
            );
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblAbsence
     */
    public function getAbsenceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAbsence', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param $FromDate
     * @param $ToDate
     * @param $Status
     * @param string $Remark
     *
     * @return TblAbsence
     */
    public function createAbsence(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        $FromDate,
        $ToDate,
        $Status,
        $Remark = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblAbsence();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
        $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);
        $Entity->setStatus($Status);
        $Entity->setRemark($Remark);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param $FromDate
     * @param $ToDate
     * @param $Remark
     * @param $Status
     *
     * @return bool
     */
    public function updateAbsence(
        TblAbsence $tblAbsence,
        $FromDate,
        $ToDate,
        $Status,
        $Remark = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAbsence $Entity */
        $Entity = $Manager->getEntityById('TblAbsence', $tblAbsence->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setFromDate($FromDate ? new \DateTime($FromDate) : null);
            $Entity->setToDate($ToDate ? new \DateTime($ToDate) : null);
            $Entity->setRemark($Remark);
            $Entity->setStatus($Status);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return bool
     */
    public function destroyAbsence(
        TblAbsence $tblAbsence
    ){

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAbsence $Entity */
        $Entity = $Manager->getEntityById('TblAbsence', $tblAbsence->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

}