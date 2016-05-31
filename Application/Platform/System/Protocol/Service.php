<?php
namespace SPHERE\Application\Platform\System\Protocol;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Archive\Archive;
use SPHERE\Application\Platform\System\Archive\Service\Entity\TblArchive;
use SPHERE\Application\Platform\System\Protocol\Service\Data;
use SPHERE\Application\Platform\System\Protocol\Service\Entity\TblProtocol;
use SPHERE\Application\Platform\System\Protocol\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Platform\Protocol
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblProtocol[]
     */
    public function getProtocolAll()
    {

        return (new Data($this->getBinding()))->getProtocolAll();
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblProtocol[]
     */
    public function getProtocolLastActivity(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getProtocolLastActivity($tblAccount);
    }
    
    /**
     * @return TblProtocol[]|bool
     */
    public function getProtocolAllCreateSession()
    {

        return (new Data($this->getBinding()))->getProtocolAllCreateSession();
    }

    /**
     * @param string  $DatabaseName
     * @param Element $Entity
     *
     * @return false|TblProtocol
     */
    public function createInsertEntry(
        $DatabaseName,
        Element $Entity
    ) {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblConsumer = $tblAccount->getServiceTblConsumer();
        } else {
            $tblConsumer = null;
        }

        Archive::useService()->createArchiveEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            $Entity, TblArchive::ARCHIVE_TYPE_CREATE
        );

        return (new Data($this->getBinding()))->createProtocolEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            null,
            $Entity
        );
    }

    /**
     * @param string  $DatabaseName
     * @param Element $From
     * @param Element $To
     *
     * @return false|TblProtocol
     */
    public function createUpdateEntry(
        $DatabaseName,
        Element $From,
        Element $To
    ) {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblConsumer = $tblAccount->getServiceTblConsumer();
        } else {
            $tblConsumer = null;
        }

        if (( $Protocol = (new Data($this->getBinding()))->createProtocolEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            $From,
            $To
        ) )
        ) {
            Archive::useService()->createArchiveEntry(
                $DatabaseName,
                ( $tblAccount ? $tblAccount : null ),
                ( $tblConsumer ? $tblConsumer : null ),
                $To, TblArchive::ARCHIVE_TYPE_UPDATE
            );
        };
        return $Protocol;
    }

    /**
     * @param string  $DatabaseName
     * @param Element $Entity
     *
     * @return false|TblProtocol
     */
    public function createDeleteEntry(
        $DatabaseName,
        Element $Entity = null
    ) {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblConsumer = $tblAccount->getServiceTblConsumer();
        } else {
            $tblConsumer = null;
        }

        return (new Data($this->getBinding()))->createProtocolEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            $Entity,
            null
        );
    }
}
