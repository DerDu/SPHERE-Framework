<?php
namespace SPHERE\Application\Document\Explorer\Storage\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * @deprecated
 * Class Setup
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @deprecated
     *
*@param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        $Schema = clone $this->getConnection()->getSchema();
        $tblDirectory = $this->setTableDirectory($Schema);
        $this->setTableDirectoryRecursive($tblDirectory, $tblDirectory);
        $this->setTableFile($Schema, $tblDirectory);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @deprecated
     *
*@param Schema $Schema
     *
     * @return Table
     */
    private function setTableDirectory(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDirectory');
        if (!$this->getConnection()->hasColumn('tblDirectory', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDirectory', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblDirectory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDirectory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @deprecated
     *
*@param Table $tblDirectoryParent
     * @param Table $tblDirectoryChild
     *
     * @return Table
     */
    private function setTableDirectoryRecursive(Table $tblDirectoryParent, Table $tblDirectoryChild)
    {

        $this->getConnection()->addForeignKey($tblDirectoryParent, $tblDirectoryChild, true);
    }

    /**
     * @deprecated
     * @param Schema $Schema
     * @param Table  $tblDirectory
     *
     * @return Table
     */
    private function setTableFile(Schema &$Schema, Table $tblDirectory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblFile');
        $this->getConnection()->addForeignKey($Table, $tblDirectory, true);
        if (!$this->getConnection()->hasColumn('tblFile', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileName')) {
            $Table->addColumn('FileName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileExtension')) {
            $Table->addColumn('FileExtension', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileContent')) {
            $Table->addColumn('FileContent', 'blob');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileType')) {
            $Table->addColumn('FileType', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileSize')) {
            $Table->addColumn('FileSize', 'integer');
        }

        return $Table;
    }
}
