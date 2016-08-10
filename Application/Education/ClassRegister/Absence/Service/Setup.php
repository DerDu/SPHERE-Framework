<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.07.2016
 * Time: 08:58
 */

namespace SPHERE\Application\Education\ClassRegister\Absence\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * 
 * @package SPHERE\Application\Education\ClassRegister\Absence\Service
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
        $this->setTableAbsence($Schema);

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
    private function setTableAbsence(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAbsence');
        if (!$this->getConnection()->hasColumn('tblAbsence', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'FromDate')) {
            $Table->addColumn('FromDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'ToDate')) {
            $Table->addColumn('ToDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'Remark')) {
            $Table->addColumn('Remark', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAbsence', 'Status')) {
            $Table->addColumn('Status', 'smallint');
        }

        return $Table;
    }
}
