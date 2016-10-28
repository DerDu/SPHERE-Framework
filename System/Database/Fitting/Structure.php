<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager as DBALSchemaManager;
use Doctrine\DBAL\Schema\Schema as DBALSchema;
use Doctrine\DBAL\Schema\Table as DBALTable;
use SPHERE\System\Database\Database;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Structure
 *
 * @package SPHERE\Application
 */
class Structure
{

    /** @var null|Database $Database */
    private $Database = null;

    /**
     * @param Identifier $Identifier
     */
    public function __construct(Identifier $Identifier)
    {

        $this->Database = new Database($Identifier);
    }

    /**
     * @return AbstractPlatform
     */
    public function getPlatform()
    {

        return $this->Database->getPlatform();
    }

    /**
     * @param View $View
     */
    public function createView(View $View)
    {

        if (!$this->Database->hasView($View->getName())) {
            $this->getSchemaManager()->createView($View->getView());
        } else {
            $this->getSchemaManager()->dropView($View->getName());
            $this->getSchemaManager()->createView($View->getView());
        }
    }

    /**
     * @return DBALSchemaManager
     */
    public function getSchemaManager()
    {

        return $this->Database->getSchemaManager();
    }

    /**
     * @param DBALSchema $Schema
     * @param string     $Name
     *
     * @return DBALTable
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function createTable(DBALSchema &$Schema, $Name)
    {

        if (!$this->Database->hasTable($Name)) {
            $Table = $Schema->createTable($Name);
            $Column = $Table->addColumn('Id', 'bigint');
            $Column->setAutoincrement(true);
            $Table->setPrimaryKey(array('Id'));
        }
        $Table = $Schema->getTable($Name);
        if (!$this->Database->hasColumn($Name, 'EntityCreate')) {
            $Table->addColumn('EntityCreate', 'datetime', array('notnull' => false));
        }
        if (!$this->Database->hasColumn($Name, 'EntityUpdate')) {
            $Table->addColumn('EntityUpdate', 'datetime', array('notnull' => false));
        }
        if (!$this->Database->hasColumn($Name, 'EntityRemove')) {
            $Table->addColumn('EntityRemove', 'datetime', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param DBALTable $KeyTarget Foreign Key (Column: KeySource Name)
     * @param DBALTable $KeySource Foreign Data (Column: Id)
     * @param bool      $AllowNull
     */
    public function addForeignKey(DBALTable &$KeyTarget, DBALTable $KeySource, $AllowNull = false)
    {

        if (!$this->Database->hasColumn($KeyTarget->getName(), $KeySource->getName())) {
            if ($AllowNull) {
                $KeyTarget->addColumn($KeySource->getName(), 'bigint', array(
                    'notnull' => false
                ));
            } else {
                $KeyTarget->addColumn($KeySource->getName(), 'bigint');
            }
            if ($this->Database->getPlatform()->supportsForeignKeyConstraints()) {
                if ($AllowNull) {
                    $KeyTarget->addForeignKeyConstraint($KeySource, array($KeySource->getName()), array('Id'), array(
                        'notnull' => false
                    ));
                } else {
                    $KeyTarget->addForeignKeyConstraint($KeySource, array($KeySource->getName()), array('Id'));
                }
            }
        }
    }

    /**
     * @param DBALSchema $Schema
     * @param bool       $Simulate
     */
    public function setMigration(DBALSchema &$Schema, $Simulate = true)
    {

        $Statement = $this->Database->getSchema()->getMigrateToSql($Schema,
            $this->Database->getPlatform()
        );

        if( $this->Database->getPlatform()->getName() == "mysql" ) {

            $DatabaseName = $this->Database->getDatabase();
            $TableStatus = $this->Database->getStatement("show table status from ".$DatabaseName.";");

            foreach( $TableStatus as $Status ) {

                if( $Status['Collation'] != 'utf8_german2_ci'
                    && (
                        $Schema->hasTable( $Status['Name'] )
                        || $this->Database->getSchema()->hasTable( $Status['Name'] )
                    )
                ) {
                    array_push( $Statement, "alter table ".$DatabaseName.".".$Status['Name']." character set utf8 collate utf8_german2_ci;" );
                    array_push( $Statement, "alter table ".$DatabaseName.".".$Status['Name']." convert to character set utf8 collate utf8_german2_ci;" );
                }
            }

        }

        if (!empty( $Statement )) {
            foreach ((array)$Statement as $Query) {
                $this->Database->addProtocol($Query);
                if (!$Simulate) {
                    $this->Database->setStatement($Query);
                }
            }
        }
    }

    /**
     * @param string $ViewName
     *
     * @return bool
     */
    public function hasView($ViewName)
    {

        return $this->Database->hasView($ViewName);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getQueryBuilder()
    {

        return $this->Database->getConnection()->getQueryBuilder();
    }

    /**
     * @return DBALSchema
     */
    public function getSchema()
    {

        return $this->Database->getSchema();
    }

    /**
     * @param string $TableName
     * @param string $ColumnName
     *
     * @return bool
     */
    public function hasColumn($TableName, $ColumnName)
    {

        return $this->Database->hasColumn($TableName, $ColumnName);
    }

    /**
     * @param DBALTable $Table
     * @param array     $ColumnList
     *
     * @return bool
     */
    public function hasIndex(DBALTable $Table, $ColumnList)
    {

        return $this->Database->hasIndex($Table, $ColumnList);
    }

    /**
     * @param DBALTable $Table
     * @param array     $ColumnList
     *
     * @return void
     */
    public function removeIndex(DBALTable $Table, $ColumnList)
    {

        $this->Database->removeIndex($Table, $ColumnList);
    }

    /**
     * @param string $TableName
     *
     * @return bool
     */
    public function hasTable($TableName)
    {

        return $this->Database->hasTable($TableName);
    }

    /**
     * @param string $Item
     */
    public function addProtocol($Item)
    {

        $this->Database->addProtocol($Item);
    }

    /**
     * @param string $Item
     */
    public function deadProtocol($Item)
    {

        $this->Database->deadProtocol($Item);
    }

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function getProtocol($Simulate = false)
    {

        return $this->Database->getProtocol($Simulate);
    }
}
