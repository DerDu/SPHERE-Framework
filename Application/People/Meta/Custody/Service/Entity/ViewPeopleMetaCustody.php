<?php
namespace SPHERE\Application\People\Meta\Custody\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionTeacher;
use SPHERE\Application\People\Meta\Club\Service\Entity\ViewPeopleMetaClub;
use SPHERE\Application\People\Meta\Common\Service\Entity\ViewPeopleMetaCommon;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\ViewPeopleMetaTeacher;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaCustody")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaCustody extends AbstractView
{

    const TBL_CUSTODY_ID = 'TblCustody_Id';
    const TBL_CUSTODY_SERVICE_TBL_PERSON = 'TblCustody_serviceTblPerson';
    const TBL_CUSTODY_OCCUPATION = 'TblCustody_Occupation';
    const TBL_CUSTODY_EMPLOYMENT = 'TblCustody_Employment';
    const TBL_CUSTODY_REMARK = 'TblCustody_Remark';

    /**
     * @Column(type="string")
     */
    protected $TblCustody_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCustody_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblCustody_Occupation;
    /**
     * @Column(type="string")
     */
    protected $TblCustody_Employment;
    /**
     * @Column(type="string")
     */
    protected $TblCustody_Remark;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Sorgeberechtigte';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_CUSTODY_OCCUPATION, 'Sorgeberechtigte: Beruf');
        $this->setNameDefinition(self::TBL_CUSTODY_EMPLOYMENT, 'Sorgeberechtigte: Arbeitsstelle');
        $this->setNameDefinition(self::TBL_CUSTODY_REMARK, 'Sorgeberechtigte: Bemerkungen');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);

        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM);
        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewAddressToPerson(), ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewStudent(), ViewStudent::TBL_STUDENT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewPeopleMetaClub(), ViewPeopleMetaClub::TBL_CLUB_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewPeopleMetaCommon(), ViewPeopleMetaCommon::TBL_COMMON_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewPeopleMetaCustody(), ViewPeopleMetaCustody::TBL_CUSTODY_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewPeopleMetaProspect(), ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewPeopleMetaTeacher(), ViewPeopleMetaTeacher::TBL_TEACHER_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewDivisionTeacher(), ViewDivisionTeacher::TBL_DIVISION_TEACHER_SERVICE_TBL_PERSON);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Custody::useService();
    }
}
