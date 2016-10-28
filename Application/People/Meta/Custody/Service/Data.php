<?php
namespace SPHERE\Application\People\Meta\Custody\Service;

use SPHERE\Application\People\Meta\Custody\Service\Entity\TblCustody;
use SPHERE\Application\People\Meta\Custody\Service\Entity\ViewPeopleMetaCustody;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Custody\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewPeopleMetaCustody[]
     */
    public function viewPeopleMetaCustody()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewPeopleMetaCustody'
        );
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $Remark
     * @param string    $Occupation
     * @param string    $Employment
     *
     * @return TblCustody
     */
    public function createCustody(
        TblPerson $tblPerson,
        $Remark,
        $Occupation,
        $Employment
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblCustody();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setRemark($Remark);
        $Entity->setOccupation($Occupation);
        $Entity->setEmployment($Employment);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblCustody $tblCustody
     * @param string     $Remark
     * @param string     $Occupation
     * @param string     $Employment
     *
     * @return bool
     */
    public function updateCustody(
        TblCustody $tblCustody,
        $Remark,
        $Occupation,
        $Employment
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblCustody $Entity */
        $Entity = $Manager->getEntityById('TblCustody', $tblCustody->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setRemark($Remark);
            $Entity->setOccupation($Occupation);
            $Entity->setEmployment($Employment);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCustody
     */
    public function getCustodyByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCustody', array(
            TblCustody::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCustody
     */
    public function getCustodyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCustody', $Id);
    }

    /**
     * @param TblCustody $tblCustody
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyCustody(TblCustody $tblCustody, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCustody $Entity */
        $Entity = $Manager->getEntityById('TblCustody', $tblCustody->getId());
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
