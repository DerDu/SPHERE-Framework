<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice\Service
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
        $tblDebtor = $this->setTableDebtor($Schema);
        $tblItem = $this->setTableItem($Schema);
        $tblInvoice = $this->setTableInvoice($Schema);
        $this->setTableInvoiceItem($Schema, $tblInvoice, $tblItem, $tblDebtor);

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
    private function setTableDebtor(Schema $Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDebtor');
        if (!$this->getConnection()->hasColumn('tblDebtor', 'DebtorNumber')) {
            $Table->addColumn('DebtorNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'DebtorPerson')) {
            $Table->addColumn('DebtorPerson', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'BankReference')) {
            $Table->addColumn('BankReference', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'Owner')) {
            $Table->addColumn('Owner', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'BankName')) {
            $Table->addColumn('BankName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'IBAN')) {
            $Table->addColumn('IBAN', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'BIC')) {
            $Table->addColumn('BIC', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'serviceTblDebtor')) {
            $Table->addColumn('serviceTblDebtor', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'serviceTblBankReference')) {
            $Table->addColumn('serviceTblBankReference', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'serviceTblPaymentType')) {
            $Table->addColumn('serviceTblPaymentType', 'bigint', array('notnull' => false));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableItem(Schema $Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItem');
        if (!$this->getConnection()->hasColumn('tblItem', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Description')) {
            $Table->addColumn('Description', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Quantity')) {
            $Table->addColumn('Quantity', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'serviceTblItem')) {
            $Table->addColumn('serviceTblItem', 'bigint', array('notnull' => false));
        }

        return $Table;
    }


    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableInvoice(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoice');
        if (!$this->getConnection()->hasColumn('tblInvoice', 'InvoiceNumber')) {
            $Table->addColumn('InvoiceNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'TargetTime')) {
            $Table->addColumn('TargetTime', 'datetime');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'SchoolName')) {
            $Table->addColumn('SchoolName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'SchoolOwner')) {
            $Table->addColumn('SchoolOwner', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'SchoolBankName')) {
            $Table->addColumn('SchoolBankName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'SchoolIBAN')) {
            $Table->addColumn('SchoolIBAN', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'SchoolBIC')) {
            $Table->addColumn('SchoolBIC', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'IsPaid')) {
            $Table->addColumn('IsPaid', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'IsReversal')) {
            $Table->addColumn('IsReversal', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceTblAddress')) {
            $Table->addColumn('serviceTblAddress', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceTblMail')) {
            $Table->addColumn('serviceTblMail', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceTblPhone')) {
            $Table->addColumn('serviceTblPhone', 'bigint', array('notnull' => false));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     * @param Table  $tblItem
     * @param Table  $tblDebtor
     *
     * @return Table
     */
    private function setTableInvoiceItem(Schema $Schema, Table $tblInvoice, Table $tblItem, Table $tblDebtor)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoiceItem');
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblInvoice);
        $this->getConnection()->addForeignKey($Table, $tblItem);
        $this->getConnection()->addForeignKey($Table, $tblDebtor);

        return $Table;
    }
}
