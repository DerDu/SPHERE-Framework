<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Prospect\Service\Data;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\ViewPeopleMetaProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Prospect
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewPeopleMetaProspect[]
     */
    public function viewPeopleMetaProspect()
    {

        return ( new Data($this->getBinding()) )->viewPeopleMetaProspect();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson $tblPerson
     * @param array $Meta
     * @param null $Group
     *
     * @return IFormInterface|string
     */
    public function createMeta(IFormInterface $Form = null, TblPerson $tblPerson, $Meta, $Group = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        $tblProspect = $this->getProspectByPerson($tblPerson);
        if ($tblProspect) {
            (new Data($this->getBinding()))->updateProspectAppointment(
                $tblProspect->getTblProspectAppointment(),
                $Meta['Appointment']['ReservationDate'],
                $Meta['Appointment']['InterviewDate'],
                $Meta['Appointment']['TrialDate']
            );
            $OptionA = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionA']);
            $OptionB = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionB']);
            (new Data($this->getBinding()))->updateProspectReservation(
                $tblProspect->getTblProspectReservation(),
                $Meta['Reservation']['Year'],
                $Meta['Reservation']['Division'],
                ($OptionA ? $OptionA : null),
                ($OptionB ? $OptionB : null)
            );
            (new Data($this->getBinding()))->updateProspect(
                $tblProspect,
                $Meta['Remark']
            );
        } else {
            $tblProspectAppointment = (new Data($this->getBinding()))->createProspectAppointment(
                $Meta['Appointment']['ReservationDate'],
                $Meta['Appointment']['InterviewDate'],
                $Meta['Appointment']['TrialDate']
            );
            $OptionA = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionA']);
            $OptionB = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionB']);
            $tblProspectReservation = (new Data($this->getBinding()))->createProspectReservation(
                $Meta['Reservation']['Year'],
                $Meta['Reservation']['Division'],
                ($OptionA ? $OptionA : null),
                ($OptionB ? $OptionB : null)
            );
            (new Data($this->getBinding()))->createProspect(
                $tblPerson,
                $tblProspectAppointment,
                $tblProspectReservation,
                $Meta['Remark']
            );
        }
        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Daten wurde erfolgreich gespeichert')
        .new Redirect(null, Redirect::TIMEOUT_SUCCESS);
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblProspect
     */
    public function getProspectByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getProspectByPerson($tblPerson);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspect
     */
    public function getProspectById($Id)
    {

        return (new Data($this->getBinding()))->getProspectById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectAppointment
     */
    public function getProspectAppointmentById($Id)
    {

        return (new Data($this->getBinding()))->getProspectAppointmentById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectReservation
     */
    public function getProspectReservationById($Id)
    {

        return (new Data($this->getBinding()))->getProspectReservationById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $ReservationDate
     * @param string $InterviewDate
     * @param string $TrialDate
     * @param              $ReservationYear
     * @param              $ReservationDivision
     * @param TblType|null $tblTypeOptionA
     * @param TblType|null $tblTypeOptionB
     * @param              $Remark
     *
     * @return TblProspect
     */
    public function insertMeta(
        TblPerson $tblPerson,
        $ReservationDate,
        $InterviewDate,
        $TrialDate,
        $ReservationYear,
        $ReservationDivision,
        TblType $tblTypeOptionA = null,
        TblType $tblTypeOptionB = null,
        $Remark
    ) {

        $tblProspectAppointment = (new Data($this->getBinding()))->createProspectAppointment(
            $ReservationDate,
            $InterviewDate,
            $TrialDate
        );
        $tblProspectReservation = (new Data($this->getBinding()))->createProspectReservation(
            $ReservationYear,
            $ReservationDivision,
            $tblTypeOptionA,
            $tblTypeOptionB
        );
        return (new Data($this->getBinding()))->createProspect(
            $tblPerson,
            $tblProspectAppointment,
            $tblProspectReservation,
            $Remark
        );
    }

    /**
     * @return false|TblProspectReservation[]
     */
    public function getProspectReservationAll()
    {

        return (new Data($this->getBinding()))->getProspectReservationAll();
    }

    /**
     * @param TblProspect $tblProspect
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyProspect(TblProspect $tblProspect, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyProspect($tblProspect, $IsSoftRemove);
    }
}
