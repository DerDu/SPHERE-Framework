<?php
namespace SPHERE\Application\Reporting\Dynamic\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Reporting\Dynamic\Service
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

        $Schema = $this->loadSchema();

        $tblDynamicFilter = $this->setTableDynamicFilter($Schema);
        $tblDynamicFilterMask = $this->setTableDynamicFilterMask($Schema, $tblDynamicFilter);
        $tblDynamicFilterOption = $this->setTableDynamicFilterOption($Schema, $tblDynamicFilterMask);
        $this->setTableDynamicFilterSearch($Schema, $tblDynamicFilterMask, $tblDynamicFilterOption);

        return $this->saveSchema($Schema, $Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableDynamicFilter(Schema $Schema)
    {

        $Table = $this->createTable($Schema, 'tblDynamicFilter');
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'FilterName');
        $this->createIndex($Table, array('serviceTblAccount','FilterName'));
        $this->createColumn($Table, 'IsPublic', self::FIELD_TYPE_BOOLEAN);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDynamicFilter
     *
     * @return Table
     */
    private function setTableDynamicFilterMask(Schema $Schema, Table $tblDynamicFilter)
    {

        $Table = $this->createTable($Schema, 'tblDynamicFilterMask');
        $this->createForeignKey($Table, $tblDynamicFilter);
        $this->createColumn($Table, 'FilterPileOrder', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'FilterClassName');

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDynamicMask
     *
     * @return Table
     */
    private function setTableDynamicFilterOption(Schema $Schema, Table $tblDynamicMask)
    {

        $Table = $this->createTable($Schema, 'tblDynamicFilterOption');
        $this->createForeignKey($Table, $tblDynamicMask);
        $this->createColumn($Table, 'FilterFieldName');
        $this->createColumn($Table, 'IsMandatory', self::FIELD_TYPE_BOOLEAN);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDynamicFilterMask
     * @param Table  $tblDynamicFilterOption
     *
     * @return Table
     */
    private function setTableDynamicFilterSearch(Schema $Schema, Table $tblDynamicFilterMask, Table $tblDynamicFilterOption)
    {

        $Table = $this->createTable($Schema, 'tblDynamicFilterSearch');
        $this->createForeignKey($Table, $tblDynamicFilterMask);
        $this->createForeignKey($Table, $tblDynamicFilterOption);
        $this->createColumn($Table, 'FilterFieldValue');

        return $Table;
    }
}
