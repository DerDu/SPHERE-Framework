<?php

namespace SPHERE\Application\Setting\Univention\Service;

use DateTime;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Application\Setting\Univention\UniventionToken;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Setting\Univention\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param string $Type
     *
     * @return false|TblUnivention
     */
    public function getUniventionByType($Type = '')
    {

        if($Type !== TblUnivention::TYPE_VALUE_TOKEN){
            return $this->getCachedEntityBy(
                __METHOD__,
                $this->getConnection()->getEntityManager(),
                'TblUnivention',
                array(
                    TblUnivention::ATTR_TYPE => $Type,
                )
            );
        }
        // using force for Token
        /** @var TblUnivention $tblUnivention */
        $tblUnivention = $this->getForceEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblUnivention',
            array(
                TblUnivention::ATTR_TYPE => $Type,
            )
        );

        // calculate time for active token
        $past = new DateTime('-55 minute');

        if($tblUnivention
            && $tblUnivention->getEntityUpdate() == null
            && $tblUnivention->getEntityCreate() < $past){
            // Eintrag erneuern (create timestamp)
            if(($token = $this->getNewToken())){
                if($this->updateUnivention($tblUnivention, $token)){
                    return $tblUnivention;
                }
            }
            return false;
        } elseif($tblUnivention
            && $tblUnivention->getEntityUpdate() != null
            && $tblUnivention->getEntityUpdate() < $past){
            // Eintrag erneuern (update timestamp)
            if(($token = $this->getNewToken())){
                if($this->updateUnivention($tblUnivention, $token)){
                    return $tblUnivention;
                }
            }
        } elseif($tblUnivention){
            // gültiger Eintrag
            return $tblUnivention;
        } else {
            // kein Eintrag vorhanden
            if(($token = $this->getNewToken())){
                return $this->createUnivention(TblUnivention::TYPE_VALUE_TOKEN, $token);
            }
        }
        return false;
    }

    /**
     * @return bool|string
     */
    private function getNewToken()
    {

        $UniventionToken = new UniventionToken();
        return $UniventionToken->getVerify();
    }

    /**
     * @param $Id
     *
     * @return false|TblUnivention
     */
    public function getUniventionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblUnivention', $Id);
    }

    /**
     * @return false|TblUnivention[]
     */
    public function getUniventionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblUnivention');
    }

    /**
     * @param string $Type
     * @param string $Value
     *
     * @return TblUnivention
     */
    public function createUnivention($Type, $Value)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblUnivention')->findOneBy(array(
            TblUnivention::ATTR_TYPE => $Type
        ));
        if ($Entity === null) {
            $Entity = new TblUnivention();
            $Entity->setType($Type);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblUnivention $tblSetting
     * @param string $value
     *
     * @return bool
     */
    public function updateUnivention(TblUnivention $tblUnivention, $value)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblUnivention $Entity */
        $Entity = $Manager->getEntityById('TblUnivention', $tblUnivention->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblStudentCustody $tblStudentCustody
     *
     * @return bool
     */
    public function removeUnivention(TblUnivention $tblStudentCustody)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentCustody')->findOneBy(array('Id' => $tblStudentCustody->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}