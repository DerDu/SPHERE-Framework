<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Bookkeeping\Balance\Service
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

        $tblPaymentType = $this->setTablePaymentType($Schema);
        $tblPayment = $this->setTablePayment($Schema, $tblPaymentType);
        $this->setTableInvoicePayment($Schema, $tblPayment);


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
    private function setTablePaymentType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPaymentType');

        if (!$this->getConnection()->hasColumn('tblPaymentType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPaymentType
     *
     * @return Table
     */
    private function setTablePayment(Schema &$Schema, Table $tblPaymentType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPayment');

        if (!$this->getConnection()->hasColumn('tblPayment', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblPayment', 'Purpose')) {
            $Table->addColumn('Purpose', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblPaymentType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPayment
     *
     * @return Table
     */
    private function setTableInvoicePayment(Schema &$Schema, Table $tblPayment)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoicePayment');
        if (!$this->getConnection()->hasColumn('tblInvoicePayment', 'serviceTblInvoice')) {
            $Table->addColumn('serviceTblInvoice', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblPayment);

        return $Table;
    }
}
