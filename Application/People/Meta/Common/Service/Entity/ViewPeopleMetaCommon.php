<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\ViewPhoneToPerson;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionTeacher;
use SPHERE\Application\People\Meta\Club\Service\Entity\ViewPeopleMetaClub;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Service\Entity\ViewPeopleMetaCustody;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\ViewPeopleMetaProspect;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\ViewPeopleMetaTeacher;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaCommon")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaCommon extends AbstractView
{

    const TBL_COMMON_ID = 'TblCommon_Id';
    const TBL_COMMON_SERVICE_TBL_PERSON = 'TblCommon_serviceTblPerson';
    const TBL_COMMON_REMARK = 'TblCommon_Remark';
    const TBL_COMMON_TBL_COMMON_BIRTH_DATES = 'TblCommon_tblCommonBirthDates';
    const TBL_COMMON_TBL_COMMON_INFORMATION = 'TblCommon_tblCommonInformation';

    const TBL_COMMON_BIRTH_DATES_ID = 'TblCommonBirthDates_Id';
    const TBL_COMMON_BIRTH_DATES_BIRTHDAY = 'TblCommonBirthDates_Birthday';
    const TBL_COMMON_BIRTH_DATES_BIRTHPLACE = 'TblCommonBirthDates_Birthplace';
    const TBL_COMMON_BIRTH_DATES_GENDER = 'TblCommonBirthDates_Gender';

    const TBL_COMMON_BIRTH_DATES_TBL_COMMON_GENDER = 'TblCommonBirthDates_tblCommonGender';
    const TBL_COMMON_GENDER_NAME = 'TblCommonGender_Name';

    const TBL_COMMON_INFORMATION_NATIONALITY = 'TblCommonInformation_Nationality';
    const TBL_COMMON_INFORMATION_DENOMINATION = 'TblCommonInformation_Denomination';
    const TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY = 'TblCommonInformation_AssistanceActivity';
    const TBL_COMMON_INFORMATION_IS_ASSISTANCE = 'TblCommonInformation_IsAssistance';

    /**
     * @Column(type="string")
     */
    protected $TblCommon_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_tblCommonBirthDates;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_tblCommonInformation;

    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthday;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthplace;

    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_tblCommonGender;
    /**
     * @Column(type="string")
     */
    protected $TblCommonGender_Name;

    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Nationality;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Denomination;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_AssistanceActivity;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_IsAssistance;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Personendaten (Erweitert)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_COMMON_REMARK, 'Personendaten: Bemerkungen');

        $this->setNameDefinition(self::TBL_COMMON_GENDER_NAME, 'Personendaten: Geschlecht');
        $this->setNameDefinition(self::TBL_COMMON_BIRTH_DATES_BIRTHPLACE, 'Personendaten: Geburtsort');
        $this->setNameDefinition(self::TBL_COMMON_BIRTH_DATES_BIRTHDAY, 'Personendaten: Geburtsdatum');

        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_NATIONALITY, 'Personendaten: Nationalität');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_DENOMINATION, 'Personendaten: Konfession');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY, 'Personendaten: Aktivitäten');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_IS_ASSISTANCE, 'Personendaten: Mitarbeitsbereitschaft');
    }

    public function loadDisableDefinition()
    {
        $this->setDisableDefinition(self::TBL_COMMON_BIRTH_DATES_GENDER);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);

        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewStudent(), ViewStudent::TBL_STUDENT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewPeopleMetaClub(), ViewPeopleMetaClub::TBL_CLUB_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewPeopleMetaCommon(), ViewPeopleMetaCommon::TBL_COMMON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewPeopleMetaCustody(), ViewPeopleMetaCustody::TBL_CUSTODY_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewPeopleMetaProspect(), ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewPeopleMetaTeacher(), ViewPeopleMetaTeacher::TBL_TEACHER_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewDivisionTeacher(), ViewDivisionTeacher::TBL_DIVISION_TEACHER_SERVICE_TBL_PERSON);

        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewAddressToPerson(),
            ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewMailToPerson(),
            ViewMailToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewPhoneToPerson(),
            ViewPhoneToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Common::useService();
    }

    /**
     * @return mixed
     */
    public function getTblCommonBirthDates_Birthday()
    {
        if( $this->TblCommonBirthDates_Birthday ) {
            return (new \DateTime($this->TblCommonBirthDates_Birthday))->format('d.m.Y');
        }
        return $this->TblCommonBirthDates_Birthday;
    }

    /**
     * @return mixed
     */
    public function getTblCommonInformation_IsAssistance()
    {
        if( null === $this->TblCommonInformation_IsAssistance ) {
            return '';
        }
        return $this->TblCommonInformation_IsAssistance ? 'Ja' : 'Nein';
    }

    /** @return string */
    public function getBirthplace() { return $this->TblCommonBirthDates_Birthplace; }
}
