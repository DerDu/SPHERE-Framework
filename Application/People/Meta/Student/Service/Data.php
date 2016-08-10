<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use SPHERE\Application\People\Meta\Student\Service\Data\Integration;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Student\Service
 */
class Data extends Integration
{

    public function setupDatabaseContent()
    {

        $tblStudentAgreementCategory = $this->createStudentAgreementCategory(
            'Foto des Schülers',
            'Sowohl Einzelaufnahmen als auch in Gruppen (z.B. zufällig)'
        );
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'in Schulschriften');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'in Veröffentlichungen');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'auf Internetpräsenz');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'auf Facebookseite');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'für Druckpresse');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'durch Ton/Video/Film');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'für Werbung in eigener Sache');

        $tblStudentAgreementCategory = $this->createStudentAgreementCategory(
            'Namentliche Erwähnung des Schülers',
            ''
        );
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'in Schulschriften');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'in Veröffentlichungen');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'auf Internetpräsenz');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'auf Facebookseite');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'für Druckpresse');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'durch Ton/Video/Film');
        $this->createStudentAgreementType($tblStudentAgreementCategory, 'für Werbung in eigener Sache');

        $this->createStudentSubjectType('ORIENTATION', 'Orientation');
        $this->createStudentSubjectType('ADVANCED', 'Advanced');
        $this->createStudentSubjectType('PROFILE', 'Profile');
        $this->createStudentSubjectType('RELIGION', 'Religion');

        $this->createStudentSubjectType('FOREIGN_LANGUAGE', 'Foreign Language');
        $this->createStudentSubjectType('ELECTIVE', 'Elective');
        $this->createStudentSubjectType('TEAM', 'Team');
        $this->createStudentSubjectType('TRACK_INTENSIVE', 'Track Intensive');
        $this->createStudentSubjectType('TRACK_BASIC', 'Track Basic');

        $this->createStudentSubjectRanking('1', '1.');
        $this->createStudentSubjectRanking('2', '2.');
        $this->createStudentSubjectRanking('3', '3.');
        $this->createStudentSubjectRanking('4', '4.');
        $this->createStudentSubjectRanking('5', '5.');
        $this->createStudentSubjectRanking('6', '6.');
        $this->createStudentSubjectRanking('7', '7.');
        $this->createStudentSubjectRanking('8', '8.');
        $this->createStudentSubjectRanking('9', '9.');

        $this->createStudentFocusType('Sprache');
        $this->createStudentFocusType('Körperlich-motorische Entwicklung');
        $this->createStudentFocusType('Sozial-emotionale Entwicklung');
        $this->createStudentFocusType('Hören');
        $this->createStudentFocusType('Sehen');
        $this->createStudentFocusType('Geistige Entwicklung');
        $this->createStudentFocusType('Lernen');

        $this->createStudentDisorderType('LRS');
        $this->createStudentDisorderType('Gehörschwierigkeiten');
        $this->createStudentDisorderType('Augenleiden');
        $this->createStudentDisorderType('Sprachfehler');
        $this->createStudentDisorderType('Dyskalkulie');
        $this->createStudentDisorderType('Autismus');
        $this->createStudentDisorderType('ADS / ADHS');
        $this->createStudentDisorderType('Rechenschwäche');
        $this->createStudentDisorderType('Hochbegabung');
        $this->createStudentDisorderType('Konzentrationsstörung');
        $this->createStudentDisorderType('Körperliche Beeinträchtigung');

        $this->createStudentTransferType('ENROLLMENT', 'Einschulung');
        $this->createStudentTransferType('ARRIVE', 'Aufnahme');
        $this->createStudentTransferType('LEAVE', 'Abgabe');
        $this->createStudentTransferType('PROCESS', 'Process');
        
        $tblStudentLiberationCategory = $this->createStudentLiberationCategory('Sportbefreihung');
        $this->createStudentLiberationType( $tblStudentLiberationCategory, 'Nicht befreit');
        $this->createStudentLiberationType( $tblStudentLiberationCategory, 'Teilbefreit');
        $this->createStudentLiberationType( $tblStudentLiberationCategory, 'Vollbefreit');
    }

    /**
     * @param string         $Disease
     * @param string         $Medication
     * @param null|TblPerson $tblPersonAttendingDoctor
     * @param int            $InsuranceState
     * @param string         $Insurance
     *
     * @return TblStudentMedicalRecord
     */
    public function createStudentMedicalRecord(
        $Disease,
        $Medication,
        TblPerson $tblPersonAttendingDoctor = null,
        $InsuranceState,
        $Insurance
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentMedicalRecord();
        $Entity->setDisease($Disease);
        $Entity->setMedication($Medication);
        $Entity->setServiceTblPersonAttendingDoctor($tblPersonAttendingDoctor);
        $Entity->setInsuranceState($InsuranceState);
        $Entity->setInsurance($Insurance);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentMedicalRecord $tblStudentMedicalRecord
     * @param string                  $Disease
     * @param string                  $Medication
     * @param null|TblPerson          $tblPersonAttendingDoctor
     * @param int                     $InsuranceState
     * @param string                  $Insurance
     *
     * @return TblStudentMedicalRecord
     */
    public function updateStudentMedicalRecord(
        TblStudentMedicalRecord $tblStudentMedicalRecord,
        $Disease,
        $Medication,
        TblPerson $tblPersonAttendingDoctor = null,
        $InsuranceState,
        $Insurance
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentMedicalRecord $Entity */
        $Entity = $Manager->getEntityById('TblStudentMedicalRecord', $tblStudentMedicalRecord->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setDisease($Disease);
            $Entity->setMedication($Medication);
            $Entity->setServiceTblPersonAttendingDoctor($tblPersonAttendingDoctor);
            $Entity->setInsuranceState($InsuranceState);
            $Entity->setInsurance($Insurance);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentMedicalRecordById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentMedicalRecord', $Id
        );
    }

    /**
     * @param string $BaptismDate
     * @param string $Location
     *
     * @return TblStudentBaptism
     */
    public function createStudentBaptism(
        $BaptismDate,
        $Location
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentBaptism();
        $Entity->setBaptismDate(( $BaptismDate ? new \DateTime($BaptismDate) : null ));
        $Entity->setLocation($Location);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentBaptism $tblStudentBaptism
     * @param string            $BaptismDate
     * @param string            $Location
     *
     * @return TblStudentBaptism
     */
    public function updateStudentBaptism(
        TblStudentBaptism $tblStudentBaptism,
        $BaptismDate,
        $Location
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentBaptism $Entity */
        $Entity = $Manager->getEntityById('TblStudentBaptism', $tblStudentBaptism->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setBaptismDate(( $BaptismDate ? new \DateTime($BaptismDate) : null ));
            $Entity->setLocation($Location);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBaptism
     */
    public function getStudentBaptismById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentBaptism', $Id
        );
    }

    /**
     * @param null|TblSiblingRank $tblSiblingRank
     *
     * @return TblStudentBilling
     */
    public function createStudentBilling(
        TblSiblingRank $tblSiblingRank = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentBilling();
        $Entity->setServiceTblSiblingRank($tblSiblingRank);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentBilling   $tblStudentBilling
     * @param null|TblSiblingRank $tblSiblingRank
     *
     * @return TblStudentBilling
     */
    public function updateStudentBilling(
        TblStudentBilling $tblStudentBilling,
        TblSiblingRank $tblSiblingRank = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentBilling $Entity */
        $Entity = $Manager->getEntityById('TblStudentBilling', $tblStudentBilling->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblSiblingRank($tblSiblingRank);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBilling
     */
    public function getStudentBillingById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentBilling', $Id
        );
    }

    /**
     * @param string $LockerNumber
     * @param string $LockerLocation
     * @param string $KeyNumber
     *
     * @return TblStudentLocker
     */
    public function createStudentLocker(
        $LockerNumber,
        $LockerLocation,
        $KeyNumber
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentLocker();
        $Entity->setLockerNumber($LockerNumber);
        $Entity->setLockerLocation($LockerLocation);
        $Entity->setKeyNumber($KeyNumber);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentLocker $tblStudentLocker
     * @param string           $LockerNumber
     * @param string           $LockerLocation
     * @param string           $KeyNumber
     *
     * @return TblStudentLocker
     */
    public function updateStudentLocker(
        TblStudentLocker $tblStudentLocker,
        $LockerNumber,
        $LockerLocation,
        $KeyNumber
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentLocker $Entity */
        $Entity = $Manager->getEntityById('TblStudentLocker', $tblStudentLocker->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setLockerNumber($LockerNumber);
            $Entity->setLockerLocation($LockerLocation);
            $Entity->setKeyNumber($KeyNumber);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLocker
     */
    public function getStudentLockerById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLocker', $Id
        );
    }

    /**
     * @param string $Route
     * @param string $StationEntrance
     * @param string $StationExit
     * @param string $Remark
     *
     * @return TblStudentTransport
     */
    public function createStudentTransport(
        $Route,
        $StationEntrance,
        $StationExit,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentTransport();
        $Entity->setRoute($Route);
        $Entity->setStationEntrance($StationEntrance);
        $Entity->setStationExit($StationExit);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentTransport $tblStudentTransport
     * @param string              $Route
     * @param string              $StationEntrance
     * @param string              $StationExit
     * @param string              $Remark
     *
     * @return TblStudentTransport
     */
    public function updateStudentTransport(
        TblStudentTransport $tblStudentTransport,
        $Route,
        $StationEntrance,
        $StationExit,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentTransport $Entity */
        $Entity = $Manager->getEntityById('TblStudentTransport', $tblStudentTransport->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setRoute($Route);
            $Entity->setStationEntrance($StationEntrance);
            $Entity->setStationExit($StationExit);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransport
     */
    public function getStudentTransportById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentTransport', $Id
        );
    }
}
