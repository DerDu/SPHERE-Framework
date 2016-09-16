<?php

namespace SPHERE\Application\Education\School\Type\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\School\Type\Service
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
        $this->setTableType($Schema);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);

        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewSchoolType') )
                ->addLink(new TblType(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }


    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblType');
        if (!$this->getConnection()->hasColumn('tblType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }
}
