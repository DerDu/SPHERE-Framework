<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        //deactivate DEMO (now REF)
//        $this->createConsumer('DEMO', 'Mandant');
        $this->createConsumer('REF', 'Referenz-Mandant');

//        // cleanup after installation
//        $tblConsumerActive = $this->getConsumerBySession();
//        if($tblConsumerActive->getAcronym() === 'REF'){
//            $AccountList = array(
//                'EVSC',
//                'EVSR',
//                'EVAMTL',
//                'EGE',
//            );
//
//            if($AccountList){
//                foreach ($AccountList as $Acronym) {
//                    $tblConsumer = $this->getConsumerByAcronym($Acronym);
//                    if ($tblConsumer){
//                        $this->createConsumerLogin($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS);
//                    }
//                }
//            }
//        }

    }

    /**
     * @param string $Name
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByName($Name)
    {

        /** @var TblConsumer $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblConsumer')
            ->findOneBy(array(TblConsumer::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByAcronym($Acronym)
    {

        /** @var TblConsumer $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblConsumer')
            ->findOneBy(array(TblConsumer::ATTR_ACRONYM => $Acronym));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblConsumer
     */
    public function getConsumerById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblConsumer', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblConsumer
     */
    public function getConsumerLoginById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblConsumerLogin', $Id);
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblConsumerLogin
     */
    public function getConsumerLoginByConsumer(TblConsumer $tblConsumer)
    {

        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblConsumerLogin',
            array(TblConsumerLogin::ATTR_TBL_CONSUMER => $tblConsumer->getId())
        );
    }

    /**
     * @param string $SystemName
     *
     * @return bool|TblConsumerLogin[]
     */
    public function getConsumerLoginBySystemName($SystemName = '')
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblConsumerLogin',
            array(TblConsumerLogin::ATTR_SYSTEM_NAME => $SystemName)
        );
    }

    /**
     * @return bool|TblConsumerLogin[]
     */
    public function getConsumerLoginAll()
    {

        return $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblConsumerLogin');
    }

    /**
     * @return TblConsumer[]|bool
     */
    public function getConsumerAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblConsumer');
    }

    /**
     * @param null|string $Session
     *
     * @return bool|TblConsumer
     */
    public function getConsumerBySession($Session = null)
    {

        // 1. Level Cache
        $Memory = $this->getCache(new MemoryHandler());
        if (null === ( $Entity = $Memory->getValue($Session, __METHOD__) )) {

            if (false !== ( $tblAccount = Account::useService()->getAccountBySession($Session) )) {
                $Entity = $tblAccount->getServiceTblConsumer();
            } else {
                $Entity = false;
            }
            $Memory->setValue($Session, $Entity, 0, __METHOD__);
        }
        return $Entity;
    }

    /**
     * @param string $Acronym
     * @param string $Name
     * @param string $Alias
     *
     * @return TblConsumer
     */
    public function createConsumer($Acronym, $Name, $Alias = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblConsumer')
            ->findOneBy(array(TblConsumer::ATTR_ACRONYM => $Acronym));
        if (null === $Entity) {
            $Entity = new TblConsumer($Acronym);
            $Entity->setName($Name);
            $Entity->setAlias($Alias);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblConsumer $tblConsumer
     * @param string      $SystemName
     *
     * @return TblConsumerLogin
     */
    public function createConsumerLogin(TblConsumer $tblConsumer, $SystemName = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblConsumerLogin')->findOneBy(array(
            TblConsumerLogin::ATTR_SYSTEM_NAME => $SystemName,
            TblConsumerLogin::ATTR_TBL_CONSUMER => $tblConsumer->getId(),
        ));

        if(null === $Entity){
            $Entity = new TblConsumerLogin();
            $Entity->setSystemName($SystemName);
            $Entity->setTblConsumer($tblConsumer);
            $Entity->setIsSchoolSeparated(false);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }
}
