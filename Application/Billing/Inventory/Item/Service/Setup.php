<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Inventory\Item\Service
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
        $tblItemType = $this->setTableItemType($Schema);
        $tblItem = $this->setTableItem($Schema, $tblItemType);
        $tblCalculation = $this->setTableCalculation($Schema);
        $this->setTableItemCalculation($Schema, $tblItem, $tblCalculation);
        $this->setTableItemAccount($Schema, $tblItem);

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
     * @return Table
     */
    private function setTableItemType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItemType');
        if (!$this->getConnection()->hasColumn('tblItemType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItemType
     *
     * @return Table
     */
    private function setTableItem(Schema &$Schema, Table $tblItemType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItem');
        if (!$this->getConnection()->hasColumn('tblItem', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Description')) {
            $Table->addColumn('Description', 'text');
        }

        $this->getConnection()->addForeignKey($Table, $tblItemType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCalculation(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCalculation');
        if (!$this->getConnection()->hasColumn('tblCalculation', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblCalculation', 'serviceTblSiblingRank')) {
            $Table->addColumn('serviceTblSiblingRank', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCalculation', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItem
     * @param Table  $tblCalculation
     */
    private function setTableItemCalculation(Schema &$Schema, Table $tblItem, Table $tblCalculation)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItemCalculation');
        $this->getConnection()->addForeignKey($Table, $tblItem);
        $this->getConnection()->addForeignKey($Table, $tblCalculation);
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItem
     *
     * @return Table
     */
    private function setTableItemAccount(Schema &$Schema, Table $tblItem)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItemAccount');

        if (!$this->getConnection()->hasColumn('tblItemAccount', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblItem);

        return $Table;
    }
}
