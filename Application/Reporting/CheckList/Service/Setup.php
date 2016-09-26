<?php

namespace SPHERE\Application\Reporting\CheckList\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Reporting\CheckList\Service
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
        $tblObjectType = $this->setTableObjectType($Schema);
        $tblElementType = $this->setTableElementType($Schema);
        $tblList = $this->setTableList($Schema);
        $this->setTableListObjectList($Schema, $tblList, $tblObjectType);
        $tblListElementList = $this->setTableListElementList($Schema, $tblList, $tblElementType);
        $this->setTableListObjectElementList($Schema, $tblList, $tblListElementList, $tblObjectType);

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
    private function setTableObjectType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblObjectType');
        if (!$this->getConnection()->hasColumn('tblObjectType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblObjectType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableElementType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblElementType');
        if (!$this->getConnection()->hasColumn('tblElementType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblElementType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableList(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblList');
        if (!$this->getConnection()->hasColumn('tblList', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblList', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblList
     * @param Table  $tblObjectType
     *
     * @return Table
     */
    private function setTableListObjectList(Schema &$Schema, Table $tblList, Table $tblObjectType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblListObjectList');
        if (!$this->getConnection()->hasColumn('tblListObjectList', 'serviceTblObject')) {
            $Table->addColumn('serviceTblObject', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblList, true);
        $this->getConnection()->addForeignKey($Table, $tblObjectType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblList
     * @param Table  $tblElementType
     *
     * @return Table
     */
    private function setTableListElementList(Schema &$Schema, Table $tblList, Table $tblElementType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblListElementList');
        if (!$this->getConnection()->hasColumn('tblListElementList', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$Table->hasColumn('SortOrder')) {
            $Table->addColumn('SortOrder', 'integer', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblList, true);
        $this->getConnection()->addForeignKey($Table, $tblElementType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblList
     * @param Table  $tblListElementList
     * @param Table  $tblObjectType
     *
     * @return Table
     */
    private function setTableListObjectElementList(
        Schema &$Schema,
        Table $tblList,
        Table $tblListElementList,
        Table $tblObjectType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblListObjectElementList');
        if (!$this->getConnection()->hasColumn('tblListObjectElementList', 'serviceTblObject')) {
            $Table->addColumn('serviceTblObject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblListObjectElementList', 'Value')) {
            $Table->addColumn('Value', 'string', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblList, true);
        $this->getConnection()->addForeignKey($Table, $tblListElementList, true);
        $this->getConnection()->addForeignKey($Table, $tblObjectType, true);

        return $Table;
    }
}
