<?php
namespace SPHERE\Application\Contact\Address\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Address\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Contact\Address\Service
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
        $tblCity = $this->setTableCity($Schema);
        $tblState = $this->setTableState($Schema);
        $tblAddress = $this->setTableAddress($Schema, $tblCity, $tblState);
        $tblType = $this->setTableType($Schema);
        $this->setTableToPerson($Schema, $tblAddress, $tblType);
        $this->setTableToCompany($Schema, $tblAddress, $tblType);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);

        $this->getConnection()->createView(
            (new View($this->getConnection(), 'viewAddressToPerson'))
                ->addLink(new TblToPerson(), 'tblType', new TblType())
                ->addLink(new TblToPerson(), 'tblAddress', new TblAddress())
                ->addLink(new TblAddress(), 'tblCity', new TblCity())
                ->addLink(new TblAddress(), 'tblState', new TblState(''))
        );

        $this->getConnection()->createView(
            (new View($this->getConnection(), 'viewAddressToCompany'))
                ->addLink(new TblToCompany(), 'tblType', new TblType())
                ->addLink(new TblToCompany(), 'tblAddress', new TblAddress())
                ->addLink(new TblAddress(), 'tblCity', new TblCity())
                ->addLink(new TblAddress(), 'tblState', new TblState(''))
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCity(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCity');
        if (!$this->getConnection()->hasColumn('tblCity', 'Code')) {
            $Table->addColumn('Code', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCity', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCity', 'District')) {
            $Table->addColumn('District', 'string', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableState(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblState');
        if (!$this->getConnection()->hasColumn('tblState', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Name'));
        if (!$this->getConnection()->hasIndex($Table, array('Name', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Name', Element::ENTITY_REMOVE));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblCity
     * @param Table  $tblState
     *
     * @return Table
     */
    private function setTableAddress(Schema &$Schema, Table $tblCity, Table $tblState)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAddress');
        if (!$this->getConnection()->hasColumn('tblAddress', 'StreetName')) {
            $Table->addColumn('StreetName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAddress', 'StreetNumber')) {
            $Table->addColumn('StreetNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAddress', 'PostOfficeBox')) {
            $Table->addColumn('PostOfficeBox', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAddress', 'County')) {
            $Table->addColumn('County', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAddress', 'Nation')) {
            $Table->addColumn('Nation', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblCity);
        $this->getConnection()->addForeignKey($Table, $tblState, true);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblType');
        if (!$this->getConnection()->hasColumn('tblType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAddress
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToPerson(Schema &$Schema, Table $tblAddress, Table $tblType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblToPerson');
        if (!$this->getConnection()->hasColumn('tblToPerson', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblToPerson', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        $this->getConnection()->addForeignKey($Table, $tblAddress, null);
        $this->getConnection()->addForeignKey($Table, $tblType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAddress
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToCompany(Schema &$Schema, Table $tblAddress, Table $tblType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblToCompany');
        if (!$this->getConnection()->hasColumn('tblToCompany', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblToCompany', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblCompany'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblCompany', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblCompany', Element::ENTITY_REMOVE));
        }
        $this->getConnection()->addForeignKey($Table, $tblAddress, null);
        $this->getConnection()->addForeignKey($Table, $tblType);
        return $Table;
    }
}
