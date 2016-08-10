<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Bookkeeping\Basket\Service
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
        $tblBasket = $this->setTableBasket($Schema);
        $this->setTableBasketPerson($Schema, $tblBasket);
        $this->setTableBasketItem($Schema, $tblBasket);
        $this->setTblBasketVerification($Schema, $tblBasket);

//        $tblBasketCommodity = $this->setTableBasketCommodity($Schema, $tblBasket);
//        $this->setTableBasketCommodityDebtor($Schema, $tblBasketCommodity);
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
    private function setTableBasket(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBasket');

        if (!$this->getConnection()->hasColumn('tblBasket', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBasket', 'Description')) {
            $Table->addColumn('Description', 'text');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblBasket
     *
     * @return Table
     */
    private function setTableBasketPerson(Schema &$Schema, Table $tblBasket)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBasketPerson');

        if (!$this->getConnection()->hasColumn('tblBasketPerson', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblBasket
     *
     * @return Table
     */
    private function setTableBasketItem(Schema &$Schema, Table $tblBasket)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBasketItem');

        if (!$this->getConnection()->hasColumn('tblBasketItem', 'serviceTblItem')) {
            $Table->addColumn('serviceTblItem', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }

    private function setTblBasketVerification(Schema &$Schema, Table $tblBasket)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBasketVerification');

        if (!$this->getConnection()->hasColumn('tblBasketVerification', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblBasketVerification', 'Quantity')) {
            $Table->addColumn('Quantity', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblBasketVerification', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblBasketVerification', 'serviceTblItem')) {
            $Table->addColumn('serviceTblItem', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }
}
