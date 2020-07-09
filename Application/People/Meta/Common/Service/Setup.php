<?php
namespace SPHERE\Application\People\Meta\Common\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Common\Service
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
        $tblCommonGender = $this->setTableCommonGender($Schema);
        $tblCommonBirthDates = $this->setTableCommonBirthDates($Schema, $tblCommonGender);
        $tblCommonInformation = $this->setTableCommonInformation($Schema);
        $this->setTableCommon($Schema, $tblCommonBirthDates, $tblCommonInformation);
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
            (new View($this->getConnection(), 'viewPeopleMetaCommon'))
                ->addLink(new TblCommon(), 'tblCommonBirthDates', new TblCommonBirthDates(), 'Id', View::JOIN_LEFT)
                ->addLink(new TblCommonBirthDates(), 'tblCommonGender', new TblCommonGender(), 'Id', View::JOIN_LEFT)
                ->addLink(new TblCommon(), 'tblCommonInformation', new TblCommonInformation(), 'Id', View::JOIN_LEFT)
        );

        /**
         * Upgrade Column Gender
         */
        if( !$Simulate ) {
            if (
                $this->hasColumn($tblCommonBirthDates, 'Gender')
                && $this->hasColumn($tblCommonBirthDates, 'tblCommonGender')
            ) {
                Common::useService()->createCommonGender( 'Männlich' );
                Common::useService()->createCommonGender( 'Weiblich' );
                $tblCommonBirthDatesAll = Common::useService()->getCommonBirthDatesAll();
                if ($tblCommonBirthDatesAll) {
                    foreach ($tblCommonBirthDatesAll as $tblCommonBirthDates) {
                        if( $tblCommonBirthDates->isGenderInSync() ) {
                            continue;
                        }
                        Common::useService()->updateCommonBirthDates(
                            $tblCommonBirthDates,
                            $tblCommonBirthDates->getBirthday(),
                            $tblCommonBirthDates->getBirthplace(),
                            $tblCommonBirthDates->getGender()
                        );
                    }
                }
            }
        }

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     * @return Table
     */
    private function setTableCommonGender(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblCommonGender');

        $this->createColumn( $Table, 'Name', self::FIELD_TYPE_STRING );

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblCommonGender
     *
     * @return Table
     */
    private function setTableCommonBirthDates(Schema &$Schema, Table $tblCommonGender)
    {

        $Table = $this->createTable($Schema, 'tblCommonBirthDates');
        $this->createColumn( $Table, 'Birthday', self::FIELD_TYPE_DATETIME, true );
        $this->createColumn( $Table, 'Birthplace', self::FIELD_TYPE_STRING );

        if (!$this->getConnection()->hasColumn('tblCommonBirthDates', 'Gender')) {  // ToDO can't remove now (View problem)
            $Table->addColumn('Gender', 'smallint');
        }

        if( $this->hasColumn( $Table, 'Gender' ) && !$this->hasColumn( $Table, $tblCommonGender->getName() ) ) {
            $this->createColumn($Table, 'Gender', self::FIELD_TYPE_INTEGER, true);
//        } else {
//            if ($this->hasColumn($Table, 'Gender') && $this->hasColumn($Table, $tblCommonGender->getName())) {
//                $this->getConnection()->deadProtocol('tblCommonBirthDates.Gender');
//            }
        }
        $this->createForeignKey( $Table, $tblCommonGender, true );

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCommonInformation(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommonInformation');
        if (!$this->getConnection()->hasColumn('tblCommonInformation', 'Nationality')) {
            $Table->addColumn('Nationality', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCommonInformation', 'Denomination')) {
            $Table->addColumn('Denomination', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCommonInformation', 'AssistanceActivity')) {
            $Table->addColumn('AssistanceActivity', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblCommonInformation', 'IsAssistance')) {
            $Table->addColumn('IsAssistance', 'smallint');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblCommonBirthDates
     * @param Table  $tblCommonInformation
     *
     * @return Table
     */
    private function setTableCommon(Schema &$Schema, Table $tblCommonBirthDates, Table $tblCommonInformation)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommon');
        if (!$this->getConnection()->hasColumn('tblCommon', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblCommon', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->getConnection()->addForeignKey($Table, $tblCommonBirthDates);
        $this->getConnection()->addForeignKey($Table, $tblCommonInformation);
        return $Table;
    }
}
