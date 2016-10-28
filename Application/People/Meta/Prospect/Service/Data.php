<?php
namespace SPHERE\Application\People\Meta\Prospect\Service;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\ViewPeopleMetaProspect;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Prospect\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewPeopleMetaProspect[]
     */
    public function viewPeopleMetaProspect()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewPeopleMetaProspect'
        );
    }

    public function setupDatabaseContent()
    {

    }


    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblProspect
     */
    public function getProspectByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblProspect', array(
            TblProspect::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param string $ReservationDate
     * @param string $InterviewDate
     * @param string $TrialDate
     *
     * @return TblProspectAppointment
     */
    public function createProspectAppointment(
        $ReservationDate,
        $InterviewDate,
        $TrialDate
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblProspectAppointment();
        $Entity->setReservationDate(( $ReservationDate ? new \DateTime($ReservationDate) : null ));
        $Entity->setInterviewDate(( $InterviewDate ? new \DateTime($InterviewDate) : null ));
        $Entity->setTrialDate(( $TrialDate ? new \DateTime($TrialDate) : null ));
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param string       $ReservationYear
     * @param string       $ReservationDivision
     * @param null|TblType $tblTypeOptionA
     * @param null|TblType $tblTypeOptionB
     *
     * @return TblProspectReservation
     */
    public function createProspectReservation(
        $ReservationYear,
        $ReservationDivision,
        TblType $tblTypeOptionA = null,
        TblType $tblTypeOptionB = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblProspectReservation();
        $Entity->setReservationYear($ReservationYear);
        $Entity->setReservationDivision($ReservationDivision);
        $Entity->setServiceTblTypeOptionA($tblTypeOptionA);
        $Entity->setServiceTblTypeOptionB($tblTypeOptionB);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblPerson              $tblPerson
     * @param TblProspectAppointment $tblProspectAppointment
     * @param TblProspectReservation $tblProspectReservation
     * @param string                 $Remark
     *
     * @return TblProspect
     */
    public function createProspect(
        TblPerson $tblPerson,
        TblProspectAppointment $tblProspectAppointment,
        TblProspectReservation $tblProspectReservation,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblProspect();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblProspectAppointment($tblProspectAppointment);
        $Entity->setTblProspectReservation($tblProspectReservation);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspect
     */
    public function getProspectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblProspect', $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectAppointment
     */
    public function getProspectAppointmentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblProspectAppointment',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectReservation
     */
    public function getProspectReservationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblProspectReservation',
            $Id);
    }

    /**
     * @param TblProspectAppointment $tblProspectAppointment
     * @param string                 $ReservationDate
     * @param string                 $InterviewDate
     * @param string                 $TrialDate
     *
     * @return bool
     */
    public function updateProspectAppointment(
        TblProspectAppointment $tblProspectAppointment,
        $ReservationDate,
        $InterviewDate,
        $TrialDate
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblProspectAppointment $Entity */
        $Entity = $Manager->getEntityById('TblProspectAppointment', $tblProspectAppointment->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setReservationDate(( $ReservationDate ? new \DateTime($ReservationDate) : null ));
            $Entity->setInterviewDate(( $InterviewDate ? new \DateTime($InterviewDate) : null ));
            $Entity->setTrialDate(( $TrialDate ? new \DateTime($TrialDate) : null ));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblProspectReservation $tblProspectReservation
     * @param string                 $ReservationYear
     * @param string                 $ReservationDivision
     * @param null|TblType           $tblTypeOptionA
     * @param null|TblType           $tblTypeOptionB
     *
     * @return bool
     */
    public function updateProspectReservation(
        TblProspectReservation $tblProspectReservation,
        $ReservationYear,
        $ReservationDivision,
        TblType $tblTypeOptionA = null,
        TblType $tblTypeOptionB = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblProspectReservation $Entity */
        $Entity = $Manager->getEntityById('TblProspectReservation', $tblProspectReservation->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setReservationYear($ReservationYear);
            $Entity->setReservationDivision($ReservationDivision);
            $Entity->setServiceTblTypeOptionA($tblTypeOptionA);
            $Entity->setServiceTblTypeOptionB($tblTypeOptionB);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblProspect $tblProspect
     * @param string      $Remark
     *
     * @return bool
     */
    public function updateProspect(
        TblProspect $tblProspect,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblProspect $Entity */
        $Entity = $Manager->getEntityById('TblProspect', $tblProspect->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return false|TblProspectReservation[]
     */
    public function getProspectReservationAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblProspectReservation');
    }

    /**
     * @param TblProspect $tblProspect
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyProspect(TblProspect $tblProspect, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblProspect $Entity */
        $Entity = $Manager->getEntityById('TblProspect', $tblProspect->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }
}
