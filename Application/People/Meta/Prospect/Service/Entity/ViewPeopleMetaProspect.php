<?php
namespace SPHERE\Application\People\Meta\Prospect\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\ViewPhoneToPerson;
use SPHERE\Application\People\Meta\Common\Service\Entity\ViewPeopleMetaCommon;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaProspect")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaProspect extends AbstractView
{

    const TBL_PROSPECT_ID = 'TblProspect_Id';
    const TBL_PROSPECT_SERVICE_TBL_PERSON = 'TblProspect_serviceTblPerson';
    const TBL_PROSPECT_REMARK = 'TblProspect_Remark';
    const TBL_PROSPECT_TBL_PROSPECT_APPOINTMENT = 'TblProspect_tblProspectAppointment';
    const TBL_PROSPECT_TBL_PROSPECT_RESERVATION = 'TblProspect_tblProspectReservation';

    const TBL_PROSPECT_APPOINTMENT_ID = 'TblProspectAppointment_Id';
    const TBL_PROSPECT_APPOINTMENT_RESERVATION_DATE = 'TblProspectAppointment_ReservationDate';
    const TBL_PROSPECT_APPOINTMENT_INTERVIEW_DATE = 'TblProspectAppointment_InterviewDate';
    const TBL_PROSPECT_APPOINTMENT_TRIAL_DATE = 'TblProspectAppointment_TrialDate';

    const TBL_PROSPECT_RESERVATION_ID = 'TblProspectReservation_Id';
    const TBL_PROSPECT_RESERVATION_RESERVATION_YEAR = 'TblProspectReservation_ReservationYear';
    const TBL_PROSPECT_RESERVATION_RESERVATION_DIVISION = 'TblProspectReservation_ReservationDivision';
    const TBL_PROSPECT_RESERVATION_SERVICE_TBL_TYPE_OPTION_A = 'TblProspectReservation_serviceTblTypeOptionA';
    const TBL_PROSPECT_RESERVATION_SERVICE_TBL_TYPE_OPTION_B = 'TblProspectReservation_serviceTblTypeOptionB';


    /**
     * @Column(type="string")
     */
    protected $TblProspect_Id;
    /**
     * @Column(type="string")
     */
    protected $TblProspect_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblProspect_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblProspect_tblProspectAppointment;
    /**
     * @Column(type="string")
     */
    protected $TblProspect_tblProspectReservation;

    /**
     * @Column(type="string")
     */
    protected $TblProspectAppointment_Id;
    /**
     * @Column(type="string")
     */
    protected $TblProspectAppointment_ReservationDate;
    /**
     * @Column(type="string")
     */
    protected $TblProspectAppointment_InterviewDate;
    /**
     * @Column(type="string")
     */
    protected $TblProspectAppointment_TrialDate;

    /**
     * @Column(type="string")
     */
    protected $TblProspectReservation_Id;
    /**
     * @Column(type="string")
     */
    protected $TblProspectReservation_ReservationYear;
    /**
     * @Column(type="string")
     */
    protected $TblProspectReservation_ReservationDivision;
    /**
     * @Column(type="string")
     */
    protected $TblProspectReservation_serviceTblTypeOptionA;
    /**
     * @Column(type="string")
     */
    protected $TblProspectReservation_serviceTblTypeOptionB;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Interessenten';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_PROSPECT_APPOINTMENT_RESERVATION_DATE, 'Termin: Eingangsdatum');
        $this->setNameDefinition(self::TBL_PROSPECT_APPOINTMENT_INTERVIEW_DATE, 'Termin: Aufnahmegespräch');
        $this->setNameDefinition(self::TBL_PROSPECT_APPOINTMENT_TRIAL_DATE, 'Termin: Schnuppertag');

        $this->setNameDefinition(self::TBL_PROSPECT_RESERVATION_RESERVATION_YEAR, 'Voranmeldung: Schuljahr');
        $this->setNameDefinition(self::TBL_PROSPECT_RESERVATION_RESERVATION_DIVISION, 'Voranmeldung: Klassenstufe');

        $this->setNameDefinition(self::TBL_PROSPECT_REMARK, 'Interessent: Bemerkung');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);

        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM);
        // ToDO neue View? oder welche Richtung soll abgebildet werden?
//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_TO);
        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewAddressToPerson(), ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewStudent(), ViewStudent::TBL_STUDENT_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewPeopleMetaClub(), ViewPeopleMetaClub::TBL_CLUB_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewPeopleMetaCommon(), ViewPeopleMetaCommon::TBL_COMMON_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewPeopleMetaCustody(), ViewPeopleMetaCustody::TBL_CUSTODY_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewPeopleMetaProspect(), ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewPeopleMetaTeacher(), ViewPeopleMetaTeacher::TBL_TEACHER_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewDivisionTeacher(), ViewDivisionTeacher::TBL_DIVISION_TEACHER_SERVICE_TBL_PERSON);

        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewAddressToPerson(),
            ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewMailToPerson(),
            ViewMailToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PROSPECT_SERVICE_TBL_PERSON, new ViewPhoneToPerson(),
            ViewPhoneToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Prospect::useService();
    }
}
