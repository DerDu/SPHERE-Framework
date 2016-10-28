<?php
namespace SPHERE\Application\People\Meta\Teacher\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 * @package SPHERE\Application\People\Meta\Teacher\Service
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
        $this->setTableTeacher($Schema);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewPeopleMetaTeacher') )
                ->addLink(new TblTeacher(), 'Id')
        );
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTeacher(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTeacher');
        if (!$this->getConnection()->hasColumn('tblTeacher', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTeacher', 'Acronym')) {
            $Table->addColumn('Acronym', 'string');
        }
        if (!$this->getConnection()->hasIndex($Table, array('Acronym', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Acronym', Element::ENTITY_REMOVE));
        }

        return $Table;
    }
}