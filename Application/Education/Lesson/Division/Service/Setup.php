<?php
namespace SPHERE\Application\Education\Lesson\Division\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Lesson\Division\Service
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

        $Schema = clone $this->getConnection()->getSchema();
        $tblLevel = $this->setTableLevel($Schema);
        $tblDivision = $this->setTableDivision($Schema, $tblLevel);
        $tblDivisionSubject = $this->setTableDivisionSubject($Schema, $tblDivision);
        $this->setTableDivisionStudent($Schema, $tblDivision);
        $this->setTableDivisionTeacher($Schema, $tblDivision);
        $this->setTableDivisionCustody($Schema, $tblDivision);
        $tblSubjectGroup = $this->setTableSubjectGroup($Schema);
        $this->setTableSubjectStudent($Schema, $tblDivisionSubject);
        $this->setTableSubjectTeacher($Schema, $tblDivisionSubject);
        $this->setTableSubjectGroupFilter($Schema, $tblSubjectGroup);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
        $this->getConnection()->createView(
            (new View($this->getConnection(), 'viewDivisionStudent'))
                ->addLink(new TblDivisionStudent(), 'tblDivision', new TblDivision(), 'Id')
                ->addLink(new TblDivision(), 'tblLevel', new TblLevel(), 'Id')
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewDivisionTeacher') )
                ->addLink(new TblDivisionTeacher(), 'tblDivision', new TblDivision(), 'Id')
                ->addLink(new TblDivision(), 'tblLevel', new TblLevel(), 'Id')
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewDivision') )
                ->addLink(new TblDivision(), 'tblLevel', new TblLevel(), 'Id')
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewSubjectTeacher') )
                ->addLink(new TblDivisionSubject(), 'tblDivision', new TblDivision(), 'Id')
                ->addLink(new TblDivisionSubject(), 'tblSubjectGroup', new TblSubjectGroup(), 'Id')
                ->addLink(new TblDivisionSubject(), 'Id', new TblSubjectTeacher(), 'tblDivisionSubject')
                ->addLink(new TblDivision(), 'tblLevel', new TblLevel(), 'Id')
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewDivisionSubject') )
                ->addLink(new TblDivisionSubject(), 'tblDivision', new TblDivision(), 'Id')
                ->addLink(new TblDivisionSubject(), 'tblSubjectGroup', new TblSubjectGroup(), 'Id')
                ->addLink(new TblDivision(), 'tblLevel', new TblLevel(), 'Id')
        );

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
        $this->createColumn($Table, 'serviceTblCompany', self::FIELD_TYPE_BIGINT, true);

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
        $this->createColumn($Table, 'HasGrading', self::FIELD_TYPE_BOOLEAN, false, true);
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
        if (!$Table->hasColumn('SortOrder')) {
            $Table->addColumn('SortOrder', 'integer', array('notnull' => false));
        }
        $this->createColumn($Table, 'LeaveDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'UseGradesInNewDivision', self::FIELD_TYPE_BOOLEAN, false, false);

        $this->getConnection()->addForeignKey($Table, $tblDivision);
        $this->createIndex( $Table, array( 'serviceTblPerson', 'tblDivision' ), false );
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
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
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
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
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
        $this->createColumn($Table, 'IsAdvancedCourse', self::FIELD_TYPE_BOOLEAN, true, null);
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
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
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
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        $this->getConnection()->addForeignKey($Table, $tblDivisionSubject);
        return $Table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblSubjectGroup
     *
     * @return Table
     */
    private function setTableSubjectGroupFilter(Schema &$schema, Table $tblSubjectGroup)
    {

        $table = $this->createTable($schema, 'tblSubjectGroupFilter');
        $this->createColumn($table, 'Field', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Value', self::FIELD_TYPE_STRING);

        $this->getConnection()->addForeignKey($table, $tblSubjectGroup);
        $this->createIndex($table, array('Field', 'tblSubjectGroup'));

        return $table;
    }
}
