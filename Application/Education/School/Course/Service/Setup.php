<?php

namespace SPHERE\Application\Education\School\Course\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\School\Course\Service
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
        $this->setTableCourse($Schema);
        $this->setTableSchoolDiploma($Schema);
        $this->setTableTechnicalDiploma($Schema);
        $this->setTableTechnicalCourse($Schema);
        $this->setTableTechnicalSubjectArea($Schema);

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
    private function setTableCourse(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCourse');
        if (!$this->getConnection()->hasColumn('tblCourse', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCourse', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTechnicalDiploma(Schema &$Schema)
    {
        $table = $this->createTable($Schema, 'tblTechnicalDiploma');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSchoolDiploma(Schema &$Schema)
    {
        $table = $this->createTable($Schema, 'tblSchoolDiploma');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTechnicalCourse(Schema &$Schema)
    {
        $table = $this->createTable($Schema, 'tblTechnicalCourse');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'GenderMaleName', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'GenderFemaleName', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTechnicalSubjectArea(Schema &$Schema)
    {
        $table = $this->createTable($Schema, 'tblTechnicalSubjectArea');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Acronym', self::FIELD_TYPE_STRING);

        return $table;
    }
}
