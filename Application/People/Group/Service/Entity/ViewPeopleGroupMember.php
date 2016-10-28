<?php
namespace SPHERE\Application\People\Group\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleGroupMember")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleGroupMember extends AbstractView
{

    const TBL_GROUP_ID = 'TblGroup_Id';
    const TBL_GROUP_NAME = 'TblGroup_Name';
    const TBL_GROUP_DESCRIPTION = 'TblGroup_Description';
    const TBL_GROUP_REMARK = 'TblGroup_Remark';
    const TBL_GROUP_IS_LOCKED = 'TblGroup_IsLocked';
    const TBL_GROUP_META_TABLE = 'TblGroup_MetaTable';
    const TBL_MEMBER_ID = 'TblMember_Id';
    const TBL_MEMBER_TBL_GROUP = 'TblMember_tblGroup';
    const TBL_MEMBER_SERVICE_TBL_PERSON = 'TblMember_serviceTblPerson';

    /**
     * @Column(type="string")
     */
    protected $TblGroup_Id;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Name;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Description;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_IsLocked;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_MetaTable;

    /**
     * @Column(type="string")
     */
    protected $TblMember_Id;
    /**
     * @Column(type="string")
     */
    protected $TblMember_tblGroup;
    /**
     * @Column(type="string")
     */
    protected $TblMember_serviceTblPerson;

    /**
     * Use this method to set disabled Properties with "setDisabledProperty()"
     *
     * @return void
     */
    public function loadDisableDefinition()
    {
        $this->setDisableDefinition( self::TBL_GROUP_REMARK );
        $this->setDisableDefinition( self::TBL_GROUP_DESCRIPTION );
    }

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Personengruppe';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_GROUP_NAME, 'Personengruppe: Gruppe');
        $this->setNameDefinition(self::TBL_GROUP_DESCRIPTION, 'Personengruppe: Beschreibung');
        $this->setNameDefinition(self::TBL_GROUP_REMARK, 'Personengruppe: Bemerkungen');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);

        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewAddressToPerson(), ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewStudent(), ViewStudent::TBL_STUDENT_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewPeopleMetaClub(), ViewPeopleMetaClub::TBL_CLUB_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewPeopleMetaCommon(), ViewPeopleMetaCommon::TBL_COMMON_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewPeopleMetaCustody(), ViewPeopleMetaCustody::TBL_CUSTODY_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewPeopleMetaProspect(), ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_PERSON, new ViewPeopleMetaTeacher(), ViewPeopleMetaTeacher::TBL_TEACHER_SERVICE_TBL_PERSON);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Group::useService();
    }
}
