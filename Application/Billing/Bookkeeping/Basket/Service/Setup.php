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
        $this->setTableBasketType($Schema);
        $tblBasket = $this->setTableBasket($Schema);
        $this->setTableBasketItem($Schema, $tblBasket);
        $this->setTblBasketVerification($Schema, $tblBasket);

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
    private function setTableBasketType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblBasketType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBasket(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblBasket');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Year', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Month', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'TargetTime', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'BillTime', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'IsDone', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'IsArchive', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($Table, 'SepaDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'SepaUser', self::FIELD_TYPE_STRING, true);
        $this->createColumn($Table, 'DatevDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'DatevUser', self::FIELD_TYPE_STRING, true);
        $this->createColumn($Table, 'serviceTblCreditor', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblType', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblDebtorPeriodType', self::FIELD_TYPE_BIGINT, true, 1);
        $this->createColumn($Table, 'tblBasketType', self::FIELD_TYPE_BIGINT, false, 1);
        $this->createColumn($Table, 'FibuAccount', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'FibuToAccount', self::FIELD_TYPE_STRING);
        // Update vorhandener Daten deswegen ist kein Fremdschlüssel mehr möglich
//        $this->getConnection()->addForeignKey($Table, $tblBasketType);

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

        $Table = $this->createTable($Schema, 'tblBasketItem');
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }

    private function setTblBasketVerification(Schema &$Schema, Table $tblBasket)
    {

        $Table = $this->createTable($Schema, 'tblBasketVerification');
        if(!$this->getConnection()->hasColumn('tblBasketVerification', 'Value')){
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'serviceTblItemVariant', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Quantity', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'serviceTblPersonCauser', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPersonDebtor', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblBankAccount', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblBankReference', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPaymentType', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblDebtorSelection', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }
}
