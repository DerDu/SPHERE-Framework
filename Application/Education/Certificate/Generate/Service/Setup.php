<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:28
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service;

use SPHERE\System\Database\Binding\AbstractSetup;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

/**
 * Class Setup
 * @package SPHERE\Application\Education\Certificate\Generate\Service
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
        $tblGenerateCertificate = $this->setTableGenerateCertificate($Schema);
        $this->setTableGenerateCertificateSetting($Schema, $tblGenerateCertificate);

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
    private function setTableGenerateCertificate(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGenerateCertificate');
        $this->createColumn( $Table, 'Date', self::FIELD_TYPE_DATETIME );
        $this->createColumn( $Table, 'Name', self::FIELD_TYPE_STRING );
        $this->createColumn( $Table, 'serviceTblCertificateType', self::FIELD_TYPE_BIGINT );
        $this->createColumn( $Table, 'serviceTblAppointedDateTask', self::FIELD_TYPE_BIGINT, true );
        $this->createColumn( $Table, 'serviceTblBehaviorTask', self::FIELD_TYPE_BIGINT, true );
        $this->createColumn( $Table, 'HeadmasterName', self::FIELD_TYPE_STRING );
        $this->createColumn( $Table, 'IsDivisionTeacherAvailable', self::FIELD_TYPE_BOOLEAN );
        $this->createColumn( $Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT );
        $this->createColumn( $Table, 'serviceTblCommonGenderHeadmaster', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn( $Table, 'IsLocked', self::FIELD_TYPE_BOOLEAN, false, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGenerateCertificate
     *
     * @return Table
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function setTableGenerateCertificateSetting(Schema &$Schema, Table $tblGenerateCertificate)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGenerateCertificateSetting');
        $this->createColumn($Table, 'Field', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Value', self::FIELD_TYPE_TEXT);

        $this->getConnection()->addForeignKey($Table, $tblGenerateCertificate);
        $this->createIndex($Table, array('Field' , 'tblGenerateCertificate'));

        return $Table;
    }
}