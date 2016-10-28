<?php
namespace SPHERE\Application\People\Meta\Custody\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\People\Meta\Custody\Service\Entity\TblCustody;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Custody\Service
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
        $this->setTableCustody($Schema);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewPeopleMetaCustody') )
                ->addLink(new TblCustody(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCustody(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCustody');
        if (!$this->getConnection()->hasColumn('tblCustody', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCustody', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblCustody', 'Occupation')) {
            $Table->addColumn('Occupation', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCustody', 'Employment')) {
            $Table->addColumn('Employment', 'string');
        }
        return $Table;
    }
}
