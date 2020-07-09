<?php
namespace SPHERE\Application\Setting\User\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Setup
 * @package SPHERE\Application\Setting\User\Account\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableUserAccount($Schema);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableUserAccount(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblUserAccount');
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        $this->createColumn($Table, 'Type', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'UserPassword', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'AccountPassword', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'ExportDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'LastDownloadAccount', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'GroupByTime', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'GroupByCount', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($Table, 'AccountCreator', self::FIELD_TYPE_STRING, false, '');
        $this->createColumn($Table, 'AccountUpdater', self::FIELD_TYPE_STRING, false, '');
        $this->createColumn($Table, 'UpdateDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'UpdateType', self::FIELD_TYPE_INTEGER, true);

        return $Table;
    }
}
