<?php
namespace SPHERE\Application\Education\Lesson\Division\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Lesson\Division\Service
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

        $Schema = clone $this->getConnection()->getSchema();
        $tblLevel = $this->setTableLevel($Schema);
        $tblDivision = $this->setTableDivision($Schema, $tblLevel);
        $tblDivisionSubject = $this->setTableDivisionSubject($Schema, $tblDivision);
        $this->setTableDivisionStudent($Schema, $tblDivision);
        $this->setTableDivisionTeacher($Schema, $tblDivision);
        $this->setTableDivisionCustody($Schema, $tblDivision);
        $this->setTableSubjectGroup($Schema);
        $this->setTableSubjectStudent($Schema, $tblDivisionSubject);
        $this->setTableSubjectTeacher($Schema, $tblDivisionSubject);
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
    private function setTableLevel(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblLevel');
        if (!$this->getConnection()->hasColumn('tblLevel', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblLevel', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblLevel', 'IsChecked')) {
            $Table->addColumn('IsChecked', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblLevel', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblLevel
     *
     * @return Table
     */
    private function setTableDivision(Schema &$Schema, Table $tblLevel)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDivision');
        if (!$this->getConnection()->hasColumn('tblDivision', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDivision', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDivision', 'serviceTblYear')) {
            $Table->addColumn('serviceTblYear', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblLevel);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDivision
     *
     * @return Table
     */
    private function setTableDivisionSubject(Schema &$Schema, Table $tblDivision)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDivisionSubject');
        if (!$this->getConnection()->hasColumn('tblDivisionSubject', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblDivisionSubject', 'tblSubjectGroup')) {
            $Table->addColumn('tblSubjectGroup', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblDivision);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDivision
     *
     * @return Table
     */
    private function setTableDivisionStudent(Schema &$Schema, Table $tblDivision)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDivisionStudent');
        if (!$this->getConnection()->hasColumn('tblDivisionStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblDivision);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDivision
     *
     * @return Table
     */
    private function setTableDivisionTeacher(Schema &$Schema, Table $tblDivision)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDivisionTeacher');
        if (!$this->getConnection()->hasColumn('tblDivisionTeacher', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblDivisionTeacher', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblDivision);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDivision
     *
     * @return Table
     */
    private function setTableDivisionCustody(Schema &$Schema, Table $tblDivision)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDivisionCustody');
        if (!$this->getConnection()->hasColumn('tblDivisionCustody', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblDivisionCustody', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblDivision);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSubjectGroup(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSubjectGroup');
        if (!$this->getConnection()->hasColumn('tblSubjectGroup', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblSubjectGroup', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDivisionSubject
     *
     * @return Table
     */
    private function setTableSubjectStudent(Schema &$Schema, Table $tblDivisionSubject)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSubjectStudent');
        if (!$this->getConnection()->hasColumn('tblSubjectStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblDivisionSubject);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDivisionSubject
     *
     * @return Table
     */
    private function setTableSubjectTeacher(Schema &$Schema, Table $tblDivisionSubject)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSubjectTeacher');
        if (!$this->getConnection()->hasColumn('tblSubjectTeacher', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblDivisionSubject);
        return $Table;
    }
}
