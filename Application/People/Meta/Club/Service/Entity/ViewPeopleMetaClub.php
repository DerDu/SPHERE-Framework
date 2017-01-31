<?php
namespace SPHERE\Application\People\Meta\Club\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\ViewPhoneToPerson;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Service\Entity\ViewPeopleMetaCommon;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\ViewPeopleMetaTeacher;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaClub")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaClub extends AbstractView
{

    const TBL_CLUB_ID = 'TblClub_Id';
    const TBL_CLUB_SERVICE_TBL_PERSON = 'TblClub_serviceTblPerson';
    const TBL_CLUB_REMARK = 'TblClub_Remark';
    const TBL_CLUB_IDENTIFIER = 'TblClub_Identifier';
    const TBL_CLUB_ENTRY_DATE = 'TblClub_EntryDate';
    const TBL_CLUB_EXIT_DATE = 'TblClub_ExitDate';

    /**
     * @Column(type="string")
     */
    protected $TblClub_Id;
    /**
     * @Column(type="string")
     */
    protected $TblClub_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblClub_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblClub_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblClub_EntryDate;
    /**
     * @Column(type="string")
     */
    protected $TblClub_ExitDate;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Vereinsmitglieder';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_CLUB_IDENTIFIER, 'Verein: Mitgliedsnummer');
        $this->setNameDefinition(self::TBL_CLUB_ENTRY_DATE, 'Verein: Eintrittsdatum');
        $this->setNameDefinition(self::TBL_CLUB_EXIT_DATE, 'Verein: Austrittsdatum');
        $this->setNameDefinition(self::TBL_CLUB_REMARK, 'Verein: Bemerkungen');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);

        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM);
//        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewStudent(), ViewStudent::TBL_STUDENT_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewPeopleMetaClub(), ViewPeopleMetaClub::TBL_CLUB_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewPeopleMetaCommon(), ViewPeopleMetaCommon::TBL_COMMON_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewPeopleMetaCustody(), ViewPeopleMetaCustody::TBL_CUSTODY_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewPeopleMetaProspect(), ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewPeopleMetaTeacher(), ViewPeopleMetaTeacher::TBL_TEACHER_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewDivisionTeacher(), ViewDivisionTeacher::TBL_DIVISION_TEACHER_SERVICE_TBL_PERSON);

        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewAddressToPerson(),
            ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewMailToPerson(),
            ViewMailToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewPhoneToPerson(),
            ViewPhoneToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Club::useService();
    }
}
