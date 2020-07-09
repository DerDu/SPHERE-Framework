<?php
namespace SPHERE\Application\Corporation\Company\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Corporation\Company\Service
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
        $this->setTableCompany($Schema);
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
            ( new View($this->getConnection(), 'viewCompany') )
                ->addLink(new TblCompany(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCompany(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCompany');
        if (!$this->getConnection()->hasColumn('tblCompany', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCompany', 'ExtendedName')) {
            $Table->addColumn('ExtendedName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCompany', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->createColumn($Table, 'ImportId', self::FIELD_TYPE_STRING, false, '');

        return $Table;
    }
}
