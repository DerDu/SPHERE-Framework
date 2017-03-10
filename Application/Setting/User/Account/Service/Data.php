<?php
namespace SPHERE\Application\Setting\User\Account\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblToPersonAddress;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson as TblToPersonMail;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\User\Account\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblUserAccount
     */
    public function getUserAccountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUserAccount', $Id);
    }

    /**
     * @param $IsSend
     * @param $IsExport
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByIsSendAndIsExport($IsSend = false, $IsExport = false)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUserAccount',
            array(
                TblUserAccount::ATTR_IS_SEND   => $IsSend,
                TblUserAccount::ATTR_IS_EXPORT => $IsExport
            ));
    }

    /**
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUserAccount');
    }

    /**
     * @param TblPerson               $tblPerson
     * @param TblToPersonAddress|null $tblToPersonAddress
     * @param TblToPersonMail|null    $tblToPersonMail
     *
     * @return bool|TblUserAccount
     */
    public function createUserAccount(
        TblPerson $tblPerson,
        TblToPersonAddress $tblToPersonAddress = null,
        TblToPersonMail $tblToPersonMail = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblUserAccount')
            ->findOneBy(array(
                TblUserAccount::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblUserAccount();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblToPersonAddress($tblToPersonAddress);
            $Entity->setServiceTblToPersonMail($tblToPersonMail);
            $Entity->setUserName('');
            $Entity->setUserPass('');
            $Entity->setIsSend(false);
            $Entity->setIsExport(false);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblUserAccount $tblUserAccount
     *
     * @return bool
     */
    public function removeUserAccount(TblUserAccount $tblUserAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblUserAccount $Entity */
        $Entity = $Manager->getEntityById('TblUserAccount', $tblUserAccount->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
