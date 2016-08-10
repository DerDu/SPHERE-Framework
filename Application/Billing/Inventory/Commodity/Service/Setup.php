<?php
namespace SPHERE\Application\Billing\Inventory\Commodity\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Inventory\Commodity\Service
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
        $this->setTableCommodity($Schema);
        $this->setTableCommodityItem($Schema);

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
    private function setTableCommodity(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommodity');

        if (!$this->getConnection()->hasColumn('tblCommodity', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCommodity', 'Description')) {
            $Table->addColumn('Description', 'text');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCommodityItem(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommodityItem');

        if (!$this->getConnection()->hasColumn('tblCommodityItem', 'Quantity')) {
            $Table->addColumn('Quantity', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblCommodityItem', 'tblItem')) {
            $Table->addColumn('tblItem', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblCommodityItem', 'tblCommodity')) {
            $Table->addColumn('tblCommodity', 'bigint');
        }

        return $Table;
    }
}
