<?php
namespace SPHERE\Application\Billing\Accounting\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Accounting\Account\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $tblAccountKeyType = $this->setTableAccountKeyType($Schema);
        $tblAccountType = $this->setTableAccountType($Schema);
        $tblAccountKey = $this->setTableAccountKey($Schema, $tblAccountKeyType);
        $this->setTableAccount($Schema, $tblAccountType, $tblAccountKey);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table $tblAccountKeyType
     *
     * @return Table
     */
    private function setTableAccountKeyType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAccountKeyType');
        if (!$this->getConnection()->hasColumn('tblAccountKeyType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccountKeyType', 'Description')) {
            $Table->addColumn('Description', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableAccountType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAccountType');
        if (!$this->getConnection()->hasColumn('tblAccountType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccountType', 'Description')) {
            $Table->addColumn('Description', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccountKeyType
     *
     * @return Table
     */
    private function setTableAccountKey(Schema &$Schema, Table $tblAccountKeyType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAccountKey');
        if (!$this->getConnection()->hasColumn('tblAccountKey', 'ValidFrom')) {
            $Table->addColumn('ValidFrom', 'date');
        }
        if (!$this->getConnection()->hasColumn('tblAccountKey', 'Value')) {
            $Table->addColumn('Value', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccountKey', 'ValidTo')) {
            $Table->addColumn('ValidTo', 'date');
        }
        if (!$this->getConnection()->hasColumn('tblAccountKey', 'Description')) {
            $Table->addColumn('Description', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblAccountKey', 'Code')) {
            $Table->addColumn('Code', 'integer');
        }
        $this->getConnection()->addForeignKey($Table, $tblAccountKeyType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccountType
     * @param Table  $tblAccountKey
     *
     * @return Table
     */
    private function setTableAccount(Schema &$Schema, Table $tblAccountType, Table $tblAccountKey)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAccount');
        if (!$this->getConnection()->hasColumn('tblAccount', 'Number')) {
            $Table->addColumn('Number', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'Description')) {
            $Table->addColumn('Description', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean');
        }
        $this->getConnection()->addForeignKey($Table, $tblAccountType);
        $this->getConnection()->addForeignKey($Table, $tblAccountKey);
        return $Table;
    }
}
