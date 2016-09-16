<?php
namespace SPHERE\Application\Education\Lesson\Term\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearPeriod;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Lesson\Term\Service
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
        $tblYear = $this->setTableYear($Schema);
        $tblPeriod = $this->setTablePeriod($Schema);
        $this->setTableYearPeriod($Schema, $tblYear, $tblPeriod);
        $tblHolidayType = $this->setTableHolidayType($Schema);
        $tblHoliday = $this->setTableHoliday($Schema, $tblHolidayType);
        $this->setTableYearHoliday($Schema, $tblYear, $tblHoliday);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);

        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewYearPeriod') )
                ->addLink(new TblYear(), 'Id', new TblYearPeriod(), 'tblYear')
                ->addLink(new TblYearPeriod(), 'tblPeriod', new TblPeriod(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableYear(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblYear');
        if (!$this->getConnection()->hasColumn('tblYear', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblYear', 'Year')) {
            $Table->addColumn('Year', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblYear', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePeriod(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPeriod');
        if (!$this->getConnection()->hasColumn('tblPeriod', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPeriod', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPeriod', 'FromDate')) {
            $Table->addColumn('FromDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPeriod', 'ToDate')) {
            $Table->addColumn('ToDate', 'datetime', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblYear
     * @param Table $tblPeriod
     *
     * @return Table
     */
    private function setTableYearPeriod(Schema &$Schema, Table $tblYear, Table $tblPeriod)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblYearPeriod');
        $this->getConnection()->addForeignKey($Table, $tblYear);
        $this->getConnection()->addForeignKey($Table, $tblPeriod);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableHolidayType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblHolidayType');
        if (!$this->getConnection()->hasColumn('tblHolidayType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblHolidayType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblHolidayType
     * 
     * @return Table
     */
    private function setTableHoliday(Schema &$Schema, Table $tblHolidayType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblHoliday');
        if (!$this->getConnection()->hasColumn('tblHoliday', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblHoliday', 'FromDate')) {
            $Table->addColumn('FromDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblHoliday', 'ToDate')) {
            $Table->addColumn('ToDate', 'datetime', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblHolidayType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblYear
     * @param Table $tblHoliday
     *
     * @return Table
     */
    private function setTableYearHoliday(Schema &$Schema, Table $tblYear, Table $tblHoliday)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblYearHoliday');
        if (!$this->getConnection()->hasColumn('tblYearHoliday', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblYear, true);
        $this->getConnection()->addForeignKey($Table, $tblHoliday, true);

        return $Table;
    }

}
