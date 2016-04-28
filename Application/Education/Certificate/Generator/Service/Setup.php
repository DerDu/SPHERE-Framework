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
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableCertificate($Schema);
        $this->setTableCertificateSubject($Schema);
        $this->setTableCertificateGrade($Schema);

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
    private function setTableCertificate(Schema &$Schema)
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

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCertificateSubject(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificateSubject');
        if (!$this->getConnection()->hasColumn('tblCertificateSubject', 'Lane')) {
            $Table->addColumn('Lane', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateSubject', 'Ranking')) {
            $Table->addColumn('Ranking', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateSubject', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCertificateGrade(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificateGrade');
        if (!$this->getConnection()->hasColumn('tblCertificateGrade', 'Lane')) {
            $Table->addColumn('Lane', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateGrade', 'Ranking')) {
            $Table->addColumn('Ranking', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblCertificateGrade', 'serviceTblGradeType')) {
            $Table->addColumn('serviceTblGradeType', 'bigint', array('notnull' => false));
        }

        return $Table;
    }
}