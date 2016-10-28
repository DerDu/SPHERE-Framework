<?php
namespace SPHERE\Application\People\Meta\Prospect\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Prospect\Service
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
        $tblProspectAppointment = $this->setTableProspectAppointment($Schema);
        $tblProspectReservation = $this->setTableProspectReservation($Schema);
        $this->setTableProspect($Schema, $tblProspectAppointment, $tblProspectReservation);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewPeopleMetaProspect') )
                ->addLink(new TblProspect(), 'tblProspectAppointment', new TblProspectAppointment(), 'Id')
                ->addLink(new TblProspect(), 'tblProspectReservation', new TblProspectReservation(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableProspectAppointment(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblProspectAppointment');
        if (!$this->getConnection()->hasColumn('tblProspectAppointment', 'ReservationDate')) {
            $Table->addColumn('ReservationDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProspectAppointment', 'InterviewDate')) {
            $Table->addColumn('InterviewDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProspectAppointment', 'TrialDate')) {
            $Table->addColumn('TrialDate', 'datetime', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableProspectReservation(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblProspectReservation');
        if (!$this->getConnection()->hasColumn('tblProspectReservation', 'ReservationYear')) {
            $Table->addColumn('ReservationYear', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblProspectReservation', 'ReservationDivision')) {
            $Table->addColumn('ReservationDivision', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblProspectReservation', 'serviceTblTypeOptionA')) {
            $Table->addColumn('serviceTblTypeOptionA', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProspectReservation', 'serviceTblTypeOptionB')) {
            $Table->addColumn('serviceTblTypeOptionB', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblProspectAppointment
     * @param Table  $tblProspectReservation
     *
     * @return Table
     */
    private function setTableProspect(Schema &$Schema, Table $tblProspectAppointment, Table $tblProspectReservation)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblProspect');
        if (!$this->getConnection()->hasColumn('tblProspect', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProspect', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->getConnection()->addForeignKey($Table, $tblProspectAppointment);
        $this->getConnection()->addForeignKey($Table, $tblProspectReservation);
        return $Table;
    }
}
