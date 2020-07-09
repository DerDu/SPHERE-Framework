<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreement;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentIntegration;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberation;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Student\Service
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
        $this->setTableStudentInsuranceState($Schema);
        $tblStudentMedicalRecord = $this->setTableStudentMedicalRecord($Schema);
        $this->setTableStudentMasernInfo($Schema);
        $tblStudentTransport = $this->setTableStudentTransport($Schema);
        $tblStudentBilling = $this->setTableStudentBilling($Schema);
        $tblStudentLocker = $this->setTableStudentLocker($Schema);
        $tblStudentBaptism = $this->setTableStudentBaptism($Schema);
        $tblStudentIntegration = $this->setTableStudentIntegration($Schema);

        $tblStudent = $this->setTableStudent(
            $Schema, $tblStudentMedicalRecord, $tblStudentTransport,
            $tblStudentBilling, $tblStudentLocker, $tblStudentBaptism, $tblStudentIntegration
        );

        $tblStudentTransferType = $this->setTableStudentTransferType($Schema);
        $tblStudentSchoolEnrollmentType =  $this->setTableStudentSchoolEnrollmentType($Schema);
        $this->setTableStudentTransfer($Schema, $tblStudent, $tblStudentTransferType, $tblStudentSchoolEnrollmentType);

        $tblStudentAgreementCategory = $this->setTableStudentAgreementCategory($Schema);
        $tblStudentAgreementType = $this->setTableStudentAgreementType($Schema, $tblStudentAgreementCategory);
        $this->setTableStudentAgreement($Schema, $tblStudent, $tblStudentAgreementType);

        $tblStudentLiberationCategory = $this->setTableStudentLiberationCategory($Schema);
        $tblStudentLiberationType = $this->setTableStudentLiberationType($Schema, $tblStudentLiberationCategory);
        $this->setTableStudentLiberation($Schema, $tblStudent, $tblStudentLiberationType);

        $tblStudentSubjectType = $this->setTableStudentSubjectType($Schema);
        $tblStudentSubjectRanking = $this->setTableStudentSubjectRanking($Schema);
        $this->setTableStudentSubject($Schema, $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);

        $tblStudentDisorderType = $this->setTableStudentDisorderType($Schema);
        $this->setTableStudentDisorder($Schema, $tblStudent, $tblStudentDisorderType);

        $tblStudentFocusType = $this->setTableStudentFocusType($Schema);
        $this->setTableStudentFocus($Schema, $tblStudent, $tblStudentFocusType);

        $tblSupportType = $this->setTableSupportType($Schema);
        $tblSupport = $this->setTableSupport($Schema, $tblSupportType);
        $tblSupportFocusType = $this->setTableSupportFocusType($Schema);
        $this->setTableSupportFocus($Schema, $tblSupport, $tblSupportFocusType);

        $tblSpecial = $this->setTableSpecial($Schema);
        $tblSpecialDisorderType = $this->setTableSpecialDisorderType($Schema);
        $this->setTableSpecialDisorder($Schema, $tblSpecial, $tblSpecialDisorderType);

        $this->setTableHandyCap($Schema);

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
            ( new View($this->getConnection(), 'viewStudent') )
                ->addLink(new TblStudent(), 'Id')
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentAgreement') )
                ->addLink(new TblStudentAgreement(), 'tblStudent', new TblStudent(), 'Id', View::JOIN)
                ->addLink(new TblStudentAgreement(), 'tblStudentAgreementType', new TblStudentAgreementType(), 'Id', View::JOIN)
                ->addLink(new TblStudentAgreementType(), 'tblStudentAgreementCategory', new TblStudentAgreementCategory(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentBaptism') )
                ->addLink(new TblStudent(), 'tblStudentBaptism', new TblStudentBaptism(), 'Id', View::JOIN)
        );
//        $this->getConnection()->createView(
//            (new View($this->getConnection(), 'viewStudentBilling'))
//                ->addLink(new TblStudent(), 'tblStudentBilling', new TblStudentBilling(), 'Id')
//                ->addLink(new TblStudentBilling(), 'serviceTblSiblingRank', new TblSiblingRank(), 'Id')
//        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentDisorder') )
                ->addLink(new TblStudentDisorder(), 'tblStudent', new TblStudent(), 'Id', View::JOIN)
                ->addLink(new TblStudentDisorder(), 'tblStudentDisorderType', new TblStudentDisorderType(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentFocus') )
                ->addLink(new TblStudentFocus(), 'tblStudent', new TblStudent(), 'Id', View::JOIN)
                ->addLink(new TblStudentFocus(), 'tblStudentFocusType', new TblStudentFocusType(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentIntegration') )
                ->addLink(new TblStudent(), 'tblStudentIntegration', new TblStudentIntegration(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentLiberation') )
                ->addLink(new TblStudentLiberation(), 'tblStudent', new TblStudent(), 'Id', View::JOIN)
                ->addLink(new TblStudentLiberation(), 'tblStudentLiberationType', new TblStudentLiberationType(), 'Id', View::JOIN)
                ->addLink(new TblStudentLiberationType(), 'tblStudentLiberationCategory', new TblStudentLiberationCategory(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentLocker') )
                ->addLink(new TblStudent(), 'tblStudentLocker', new TblStudentLocker(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentMedicalRecord') )
                ->addLink(new TblStudent(), 'tblStudentMedicalRecord', new TblStudentMedicalRecord(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentSubject') )
                ->addLink(new TblStudentSubject(), 'tblStudent', new TblStudent(), 'Id', View::JOIN)
                ->addLink(new TblStudentSubject(), 'tblStudentSubjectRanking', new TblStudentSubjectRanking(), 'Id', View::JOIN)
                ->addLink(new TblStudentSubject(), 'tblStudentSubjectType', new TblStudentSubjectType(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentTransfer') )
                ->addLink(new TblStudentTransfer(), 'tblStudent', new TblStudent(), 'Id', View::JOIN)
                ->addLink(new TblStudentTransfer(), 'tblStudentTransferType', new TblStudentTransferType(), 'Id', View::JOIN)
        );
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewStudentTransport') )
                ->addLink(new TblStudent(), 'tblStudentTransport', new TblStudentTransport(), 'Id', View::JOIN)
        );

        return $this->getConnection()->getProtocol($Simulate);
    }


    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentInsuranceState(Schema &$Schema)
    {
        $Table = $this->createTable($Schema, 'tblStudentInsuranceState');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentMedicalRecord(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentMedicalRecord');
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Disease')) {
            $Table->addColumn('Disease', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Medication')) {
            $Table->addColumn('Medication', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'AttendingDoctor')) {
            $Table->addColumn('AttendingDoctor', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'InsuranceState')) {
            $Table->addColumn('InsuranceState', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Insurance')) {
            $Table->addColumn('Insurance', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'MasernDate')) {
            $Table->addColumn('MasernDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'MasernDocumentType')) {
            $Table->addColumn('MasernDocumentType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'MasernCreatorType')) {
            $Table->addColumn('MasernCreatorType', 'bigint', array('notnull' => false));
        }

//        // entfernen alter Rückstände
//        if ($this->getConnection()->hasColumn('tblStudentMedicalRecord', 'serviceTblPersonAttendingDoctor')) {
//            $Table->dropColumn('serviceTblPersonAttendingDoctor');
//        }

        return $Table;
    }

    private function setTableStudentMasernInfo(Schema $Schema)
    {

        $Table = $this->createTable($Schema, 'tblStudentMasernInfo');
        $this->createColumn($Table, 'Meta', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Type', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'TextShort', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'TextLong', self::FIELD_TYPE_TEXT);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransport(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransport');
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'Route')) {
            $Table->addColumn('Route', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'StationEntrance')) {
            $Table->addColumn('StationEntrance', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'StationExit')) {
            $Table->addColumn('StationExit', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentBilling(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentBilling');
        if (!$this->getConnection()->hasColumn('tblStudentBilling', 'serviceTblSiblingRank')) {
            $Table->addColumn('serviceTblSiblingRank', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentLocker(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentLocker');
        if (!$this->getConnection()->hasColumn('tblStudentLocker', 'LockerNumber')) {
            $Table->addColumn('LockerNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLocker', 'LockerLocation')) {
            $Table->addColumn('LockerLocation', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLocker', 'KeyNumber')) {
            $Table->addColumn('KeyNumber', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentBaptism(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentBaptism');
        if (!$this->getConnection()->hasColumn('tblStudentBaptism', 'BaptismDate')) {
            $Table->addColumn('BaptismDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentBaptism', 'Location')) {
            $Table->addColumn('Location', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentIntegration(
        Schema &$Schema
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentIntegration');
        if (!$this->getConnection()->hasColumn('tblStudentIntegration', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblStudentIntegration', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentIntegration', 'CoachingRequestDate')) {
            $Table->addColumn('CoachingRequestDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentIntegration', 'CoachingCounselDate')) {
            $Table->addColumn('CoachingCounselDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentIntegration', 'CoachingDecisionDate')) {
            $Table->addColumn('CoachingDecisionDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentIntegration', 'CoachingRequired')) {
            $Table->addColumn('CoachingRequired', 'boolean', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentIntegration', 'CoachingTime')) {
            $Table->addColumn('CoachingTime', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentIntegration', 'CoachingRemark')) {
            $Table->addColumn('CoachingRemark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblStudentMedicalRecord
     * @param Table $tblStudentTransport
     * @param Table $tblStudentBilling
     * @param Table $tblStudentLocker
     * @param Table $tblStudentBaptism
     * @param Table $tblStudentIntegration
     *
     * @return Table
     */
    private function setTableStudent(
        Schema &$Schema,
        Table $tblStudentMedicalRecord,
        Table $tblStudentTransport,
        Table $tblStudentBilling,
        Table $tblStudentLocker,
        Table $tblStudentBaptism,
        Table $tblStudentIntegration
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudent');
        if (!$this->getConnection()->hasColumn('tblStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblStudent', 'Prefix')) {
            $Table->addColumn('Prefix', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudent', 'Identifier')) {
            $Table->addColumn('Identifier', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudent', 'SchoolAttendanceStartDate')) {
            $Table->addColumn('SchoolAttendanceStartDate', 'datetime', array('notnull' => false));
        }
        $this->createColumn($Table, 'HasMigrationBackground', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($Table, 'IsInPreparationDivisionForMigrants', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->getConnection()->addForeignKey($Table, $tblStudentMedicalRecord, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransport, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentBilling, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentLocker, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentBaptism, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentIntegration, true);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferType');
        if (!$this->getConnection()->hasColumn('tblStudentTransferType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblStudent
     * @param Table $tblStudentTransferType
     * @param Table $tblStudentSchoolEnrollmentType
     *
     * @return Table
     */
    private function setTableStudentTransfer(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentTransferType,
        Table $tblStudentSchoolEnrollmentType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransfer');
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'serviceTblCourse')) {
            $Table->addColumn('serviceTblCourse', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'TransferDate')) {
            $Table->addColumn('TransferDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->createColumn($Table, 'serviceTblStateCompany', self::FIELD_TYPE_INTEGER, true);

        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferType);
        $this->getConnection()->addForeignKey($Table, $tblStudentSchoolEnrollmentType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentAgreementCategory(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentAgreementCategory');
        if (!$this->getConnection()->hasColumn('tblStudentAgreementCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentAgreementCategory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentAgreementCategory
     *
     * @return Table
     */
    private function setTableStudentAgreementType(Schema &$Schema, Table $tblStudentAgreementCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentAgreementType');
        if (!$this->getConnection()->hasColumn('tblStudentAgreementType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentAgreementType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblStudentAgreementCategory);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudent
     * @param Table  $tblStudentAgreementType
     *
     * @return Table
     */
    private function setTableStudentAgreement(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentAgreementType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentAgreement');
        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentAgreementType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentLiberationCategory(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentLiberationCategory');
        if (!$this->getConnection()->hasColumn('tblStudentLiberationCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLiberationCategory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentLiberationCategory
     *
     * @return Table
     */
    private function setTableStudentLiberationType(Schema &$Schema, Table $tblStudentLiberationCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentLiberationType');
        if (!$this->getConnection()->hasColumn('tblStudentLiberationType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLiberationType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblStudentLiberationCategory);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudent
     * @param Table  $tblStudentLiberationType
     *
     * @return Table
     */
    private function setTableStudentLiberation(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentLiberationType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentLiberation');
        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentLiberationType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectType');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectRanking(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectRanking');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectRanking', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectRanking', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudent
     * @param Table  $tblStudentSubjectType
     * @param Table  $tblStudentSubjectRanking
     *
     * @return Table
     */
    private function setTableStudentSubject(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentSubjectType,
        Table $tblStudentSubjectRanking
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubject');
        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectType);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectRanking);
        if (!$this->getConnection()->hasColumn('tblStudentSubject', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubject', 'serviceTblLevelFrom')) {
            $Table->addColumn('serviceTblLevelFrom', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubject', 'serviceTblLevelTill')) {
            $Table->addColumn('serviceTblLevelTill', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentDisorderType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentDisorderType');
        if (!$this->getConnection()->hasColumn('tblStudentDisorderType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentDisorderType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudent
     * @param Table  $tblStudentDisorderType
     *
     * @return Table
     */
    private function setTableStudentDisorder(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentDisorderType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentDisorder');
        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentDisorderType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentFocusType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentFocusType');
        if (!$this->getConnection()->hasColumn('tblStudentFocusType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentFocusType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudent
     * @param Table  $tblStudentFocusType
     *
     * @return Table
     */
    private function setTableStudentFocus(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentFocusType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentFocus');
        $this->createColumn($Table, 'IsPrimary', self::FIELD_TYPE_BOOLEAN, false, false);

        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentFocusType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSchoolEnrollmentType(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblStudentSchoolEnrollmentType');
        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSupportType(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblSupportType');
        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Description', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSupportType
     *
     * @return Table
     */
    private function setTableSupport(Schema &$Schema, Table $tblSupportType)
    {

        $table = $this->createTable($Schema, 'tblSupport');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createForeignKey($table, $tblSupportType);
        $this->createColumn($table, 'Company', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'PersonSupport', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'SupportTime', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'PersonEditor', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Remark', self::FIELD_TYPE_TEXT);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSupport
     * @param Table  $tblSupportFocusType
     *
     * @return Table
     */
    private function setTableSupportFocus(Schema &$Schema, Table $tblSupport, Table $tblSupportFocusType)
    {

        $table = $this->createTable($Schema, 'tblSupportFocus');
        $this->createForeignKey($table, $tblSupport);
        $this->createForeignKey($table, $tblSupportFocusType);
        $this->createColumn($table, 'IsPrimary', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSupportFocusType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblSupportFocusType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSpecial(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblSpecial');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($table, 'PersonEditor', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Remark', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'IsCanceled', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSpecial
     * @param Table  $tblSpecialDisorderType
     *
     * @return Table
     */
    private function setTableSpecialDisorder(Schema &$Schema, Table $tblSpecial, Table $tblSpecialDisorderType)
    {

        $table = $this->createTable($Schema, 'tblSpecialDisorder');
        $this->createForeignKey($table, $tblSpecial);
        $this->createForeignKey($table, $tblSpecialDisorderType);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSpecialDisorderType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblSpecialDisorderType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableHandyCap(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblHandyCap');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($table, 'PersonEditor', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'LegalBasis', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'LearnTarget', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'RemarkLesson', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'RemarkRating', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'IsCanceled', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

}
