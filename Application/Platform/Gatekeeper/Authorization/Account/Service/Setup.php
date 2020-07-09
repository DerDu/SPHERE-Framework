<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
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
        $tblAccount = $this->setTableAccount($Schema);
        $this->setTableAccountInitial($Schema, $tblAccount);
        $tblIdentification = $this->setTableIdentification($Schema);
        $this->setTableSession($Schema, $tblAccount);
        $this->setTableAuthorization($Schema, $tblAccount);
        $this->setTableAuthentication($Schema, $tblAccount, $tblIdentification);
        $this->setTableUser($Schema, $tblAccount);
        $this->setTableSetting($Schema, $tblAccount);
//        $tblGroup = $this->setTableGroup($Schema);
//        $this->setTableGroupRole($Schema, $tblGroup);
//        $this->setTableGroupAccount($Schema, $tblGroup, $tblAccount);
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
    private function setTableAccount(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAccount');
        if (!$this->getConnection()->hasColumn('tblAccount', 'Username')) {
            $Table->addColumn('Username', 'string');
        }
        $this->removeIndex($Table, array('Username'));
        $this->removeIndex($Table, array('Username', Element::ENTITY_REMOVE));
        $this->createIndex($Table, array('Username'), true);
        if (!$this->getConnection()->hasColumn('tblAccount', 'Password')) {
            $Table->addColumn('Password', 'string');
        }
        $this->createIndex($Table, array('Username', 'Password'));
        if (!$this->getConnection()->hasColumn('tblAccount', 'serviceTblToken')) {
            $Table->addColumn('serviceTblToken', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'serviceTblConsumer')) {
            $Table->addColumn('serviceTblConsumer', 'bigint', array('notnull' => false));
        }

        $this->createColumn($Table, 'UserAlias', self::FIELD_TYPE_STRING, true);
        $this->createIndex($Table, array('UserAlias'), true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableGroup(Schema $Schema)
    {
        $Table = $this->createTable( $Schema, 'tblGroup' );
        $this->createColumn( $Table, 'Name', self::FIELD_TYPE_STRING );
        $this->createColumn( $Table, 'Description', self::FIELD_TYPE_TEXT );
        $this->createColumn( $Table, 'serviceTblConsumer', self::FIELD_TYPE_BIGINT, true );
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGroup
     *
     * @return Table
     */
    private function setTableGroupRole(Schema &$Schema, Table $tblGroup)
    {

        $Table = $this->createTable($Schema, 'tblGroupRole');
        $this->getConnection()->addForeignKey($Table, $tblGroup);
        $this->createColumn($Table, 'serviceTblRole', self::FIELD_TYPE_BIGINT, true);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGroup
     * @param Table $tblAccount
     *
     * @return Table
     */
    private function setTableGroupAccount(Schema &$Schema, Table $tblGroup, Table $tblAccount)
    {

        $Table = $this->createTable($Schema, 'tblGroupAccount');
        $this->getConnection()->addForeignKey($Table, $tblGroup);
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableIdentification(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblIdentification');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->removeIndex($Table, array('Name'));
        $this->removeIndex($Table, array('Name', Element::ENTITY_REMOVE));
        $this->createIndex($Table, array('Name'), true);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_STRING);
        if (!$this->getConnection()->hasColumn('tblIdentification', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean', array('default' => 1));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccount
     *
     * @return Table
     */
    private function setTableAccountInitial(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAccountInitial');
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        $this->createColumn($Table, 'Password', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblAccount
     *
     * @return Table
     */
    private function setTableSession(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->createTable($Schema, 'tblSession');
        $this->createColumn($Table, 'Session', self::FIELD_TYPE_STRING);
        if (!$this->getConnection()->hasIndex($Table, array('Session'))) {
            $Table->addIndex(array('Session'));
        }
        if (!$this->getConnection()->hasColumn('tblSession', 'Timeout')) {
            $Table->addColumn('Timeout', 'integer');
        }
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @param Table $tblAccount
     *
     * @return Table
     */
    private function setTableAuthorization(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAuthorization');
        if (!$this->getConnection()->hasColumn('tblAuthorization', 'serviceTblRole')) {
            $Table->addColumn('serviceTblRole', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @param Table $tblAccount
     * @param Table $tblIdentification
     *
     * @return Table
     */
    private function setTableAuthentication(Schema &$Schema, Table $tblAccount, Table $tblIdentification)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAuthentication');
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        $this->getConnection()->addForeignKey($Table, $tblIdentification);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblAccount
     *
     * @return Table
     */
    private function setTableUser(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->createTable($Schema, 'tblUser');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true );
        $this->createForeignKey( $Table, $tblAccount );
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblAccount
     *
     * @return Table
     */
    private function setTableSetting(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSetting');
        if (!$this->getConnection()->hasColumn('tblSetting', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblSetting', 'Value')) {
            $Table->addColumn('Value', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        return $Table;
    }
}
