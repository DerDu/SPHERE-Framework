<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.05.2016
 * Time: 08:25
 */

namespace SPHERE\Application\People\Meta\Club\Service;

use SPHERE\Application\People\Meta\Club\Service\Entity\TblClub;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\People\Meta\Club\Service
 */
class Data  extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblClub
     */
    public function getClubByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblClub', array(
            TblClub::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Identifier
     * @param $EntryDate
     * @param $ExitDate
     * @param $Remark
     *
     * @return TblClub
     */
    public function createClub(
        TblPerson $tblPerson,
        $Identifier,
        $EntryDate,
        $ExitDate,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblClub();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setIdentifier($Identifier);
        $Entity->setEntryDate(( $EntryDate ? new \DateTime($EntryDate) : null ));
        $Entity->setExitDate(( $ExitDate ? new \DateTime($ExitDate) : null ));
        $Entity->setRemark($Remark);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblClub $tblClub
     * @param $Identifier
     * @param $EntryDate
     * @param $ExitDate
     * @param $Remark
     *
     * @return bool
     */
    public function updateClub(
        TblClub $tblClub,
        $Identifier,
        $EntryDate,
        $ExitDate,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblClub $Entity */
        $Entity = $Manager->getEntityById('TblClub', $tblClub->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setIdentifier($Identifier);
            $Entity->setEntryDate(( $EntryDate ? new \DateTime($EntryDate) : null ));
            $Entity->setExitDate(( $ExitDate ? new \DateTime($ExitDate) : null ));
            $Entity->setRemark($Remark);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblClub $tblClub
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyClub(TblClub $tblClub, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblClub $Entity */
        $Entity = $Manager->getEntityById('TblClub', $tblClub->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }
}