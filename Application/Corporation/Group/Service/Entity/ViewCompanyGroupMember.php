<?php
namespace SPHERE\Application\Corporation\Group\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewCompanyGroupMember")
 * @Cache(usage="READ_ONLY")
 */
class ViewCompanyGroupMember extends AbstractView
{

    const TBL_GROUP_ID = 'TblGroup_Id';
    const TBL_GROUP_NAME = 'TblGroup_Name';
    const TBL_GROUP_DESCRIPTION = 'TblGroup_Description';
    const TBL_GROUP_REMARK = 'TblGroup_Remark';
    const TBL_GROUP_IS_LOCKED = 'TblGroup_IsLocked';
    const TBL_GROUP_META_TABLE = 'TblGroup_MetaTable';
    const TBL_MEMBER_ID = 'TblMember_Id';
    const TBL_MEMBER_TBL_GROUP = 'TblMember_tblGroup';
    const TBL_MEMBER_SERVICE_TBL_COMPANY = 'TblMember_serviceTblCompany';

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
    protected $TblMember_serviceTblCompany;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Firmengruppe';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_GROUP_NAME, 'Firmengruppe: Gruppe');
//        $this->setNameDefinition(self::TBL_GROUP_DESCRIPTION, 'Firmengruppe: Beschreibung');
//        $this->setNameDefinition(self::TBL_GROUP_REMARK, 'Firmengruppe: Bemerkungen');
    }

    public function loadDisableDefinition()
    {
        $this->setDisableDefinition(self::TBL_GROUP_DESCRIPTION);
        $this->setDisableDefinition(self::TBL_GROUP_REMARK);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_COMPANY, new ViewCompany(), ViewCompany::TBL_COMPANY_ID);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_COMPANY, new ViewAddressToCompany(), ViewAddressToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY);
//        $this->addForeignView(self::TBL_MEMBER_SERVICE_TBL_COMPANY, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Group::useService();
    }
}
