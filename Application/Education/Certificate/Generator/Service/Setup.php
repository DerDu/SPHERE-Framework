<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Certificate\Generator\Service
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
        $tblCertificateType =  $this->setTableCertificateType($Schema);
        $tblCertificate = $this->setTableCertificate($Schema, $tblCertificateType);
        $this->setTableCertificateSubject($Schema, $tblCertificate);
        $this->setTableCertificateGrade($Schema, $tblCertificate);
        $this->setTableCertificateLevel($Schema, $tblCertificate);
        $this->setTableCertificateField($Schema, $tblCertificate);
        $this->setTableCertificateReferenceForLanguages($Schema, $tblCertificate);
        $this->setTableCertificateInformation($Schema, $tblCertificate);

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
     * @param Table $tblCertificateType
     *
     * @return Table
     */
    private function setTableCertificate(Schema &$Schema, Table $tblCertificateType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificate');
        if (!$this->getConnection()->hasColumn('tblCertificate', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCertificate', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCertificate', 'Certificate')) {
            $Table->addColumn('Certificate', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCertificate', 'serviceTblConsumer')) {
            $Table->addColumn('serviceTblConsumer', 'bigint', array('notnull' => false));
        }
        if (!$Table->hasColumn('IsGradeInformation')){
            $Table->addColumn('IsGradeInformation', 'boolean');
        }
        $this->createColumn($Table, 'IsInformation', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'IsChosenDefault', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'IsIgnoredForAutoSelect', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($Table, 'IsGradeVerbal', self::FIELD_TYPE_BOOLEAN, false, false);

        $this->createColumn($Table, 'serviceTblCourse', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT, true);

        $this->getConnection()->addForeignKey($Table, $tblCertificateType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblCertificate
     *
     * @return Table
     */
    private function setTableCertificateSubject(Schema &$Schema, Table $tblCertificate)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificateSubject');
        if (!$this->getConnection()->hasColumn('tblCertificateSubject', 'Lane')) {
            $Table->addColumn('Lane', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateSubject', 'Ranking')) {
            $Table->addColumn('Ranking', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateSubject', 'IsEssential')) {
            $Table->addColumn('IsEssential', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateSubject', 'serviceTblStudentLiberationCategory')) {
            $Table->addColumn('serviceTblStudentLiberationCategory', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCertificateSubject', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblCertificate);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblCertificate
     *
     * @return Table
     */
    private function setTableCertificateGrade(Schema &$Schema, Table $tblCertificate)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificateGrade');
        if (!$this->getConnection()->hasColumn('tblCertificateGrade', 'Lane')) {
            $Table->addColumn('Lane', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateGrade', 'Ranking')) {
            $Table->addColumn('Ranking', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateGrade', 'IsEssential')) {
            $Table->addColumn('IsEssential', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateGrade', 'serviceTblStudentLiberationCategory')) {
            $Table->addColumn('serviceTblStudentLiberationCategory', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCertificateGrade', 'serviceTblGradeType')) {
            $Table->addColumn('serviceTblGradeType', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblCertificate);

        $this->createIndex($Table, array('serviceTblGradeType'), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCertificateType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblCertificateType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Identifier', self::FIELD_TYPE_STRING);
        $this->createIndex($Table, array('Identifier'));
        $this->createColumn($Table, 'IsAutomaticallyApproved', self::FIELD_TYPE_BOOLEAN, false, false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblCertificate
     *
     * @return Table
     */
    private function setTableCertificateLevel(Schema &$Schema, Table $tblCertificate)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificateLevel');
        $this->createColumn($Table, 'serviceTblLevel', self::FIELD_TYPE_BIGINT, true);

        $this->getConnection()->addForeignKey($Table, $tblCertificate, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblCertificate
     *
     * @return Table
     */
    private function setTableCertificateField(Schema &$Schema, Table $tblCertificate)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificateField');
        $this->createColumn($Table, 'FieldName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'CharCount', self::FIELD_TYPE_INTEGER);

        $this->createForeignKey($Table, $tblCertificate, true);
        $this->createIndex($Table, array('FieldName', 'tblCertificate'));

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblCertificate
     *
     * @return Table
     */
    private function setTableCertificateReferenceForLanguages(Schema &$Schema, Table $tblCertificate)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificateReferenceForLanguages');
        $this->createColumn($Table, 'LanguageRanking', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'ToLevel10', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'AfterBasicCourse', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'AfterAdvancedCourse', self::FIELD_TYPE_STRING);

        $this->createForeignKey($Table, $tblCertificate, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblCertificate
     *
     * @return Table
     */
    private function setTableCertificateInformation(Schema &$Schema, Table $tblCertificate)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificateInformation');
        $this->createColumn($Table, 'FieldName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Page', self::FIELD_TYPE_INTEGER);

        $this->createForeignKey($Table, $tblCertificate, true);
        $this->createIndex($Table, array('FieldName', 'tblCertificate'));

        return $Table;
    }
}
