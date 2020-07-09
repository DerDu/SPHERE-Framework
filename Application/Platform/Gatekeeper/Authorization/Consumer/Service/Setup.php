<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service
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
        $tblConsumer = $this->setTableConsumer($Schema);
        $this->setTableConsumerLogin($Schema, $tblConsumer);
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
    private function setTableConsumer(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblConsumer');
        if (!$this->getConnection()->hasColumn('tblConsumer', 'Acronym')) {
            $Table->addColumn('Acronym', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Acronym'));
        if (!$this->getConnection()->hasIndex($Table, array('Acronym', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Acronym', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblConsumer', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        $this->createColumn($Table, 'Alias', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblConsumer
     *
     * @return Table
     */
    private function setTableConsumerLogin(Schema &$Schema, Table $tblConsumer)
    {

        $Table = $this->createTable($Schema, 'tblConsumerLogin');
        $this->createColumn($Table, 'SystemName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IsSchoolSeparated', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->getConnection()->addForeignKey($Table, $tblConsumer);

        return $Table;
    }
}
