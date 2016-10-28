<?php
namespace SPHERE\Application\Education\Lesson\Subject\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Lesson\Subject\Service
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
        $tblGroup = $this->setTableGroup($Schema);
        $tblCategory = $this->setTableCategory($Schema);
        $this->setTableGroupCategory($Schema, $tblGroup, $tblCategory);
        $tblSubject = $this->setTableSubject($Schema);
        $this->setTableCategorySubject($Schema, $tblCategory, $tblSubject);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewSubject') )
                ->addLink(new TblSubject(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableGroup(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGroup');
        if (!$this->getConnection()->hasColumn('tblGroup', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGroup', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblGroup', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGroup', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCategory(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCategory');
        if (!$this->getConnection()->hasColumn('tblCategory', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCategory', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCategory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblGroup
     * @param Table  $tblCategory
     *
     * @return Table
     */
    private function setTableGroupCategory(Schema &$Schema, Table $tblGroup, Table $tblCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGroupCategory');
        $this->getConnection()->addForeignKey($Table, $tblGroup);
        $this->getConnection()->addForeignKey($Table, $tblCategory);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSubject(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSubject');
        if (!$this->getConnection()->hasColumn('tblSubject', 'Acronym')) {
            $Table->addColumn('Acronym', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Acronym'));
        if (!$this->getConnection()->hasIndex($Table, array('Acronym', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Acronym', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblSubject', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblSubject', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblCategory
     * @param Table  $tblSubject
     *
     * @return Table
     */
    private function setTableCategorySubject(Schema &$Schema, Table $tblCategory, Table $tblSubject)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCategorySubject');
        $this->getConnection()->addForeignKey($Table, $tblCategory);
        $this->getConnection()->addForeignKey($Table, $tblSubject);
        return $Table;
    }
}
