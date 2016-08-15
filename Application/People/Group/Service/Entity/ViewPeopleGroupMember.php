<?php
namespace SPHERE\Application\People\Group\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleGroupMember")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleGroupMember extends AbstractView
{

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

        $this->setNameDefinition('TblGroup_Name', 'Gruppe: Name');
        $this->setNameDefinition('TblGroup_Description', 'Gruppe: Beschreibung');
        $this->setNameDefinition('TblGroup_Remark', 'Gruppe: Bemerkungen');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView('TblMember_serviceTblPerson', new ViewPerson(), 'TblPerson_Id');
        $this->addForeignView('TblMember_serviceTblPerson', new ViewAddressToPerson(), 'TblToPerson_serviceTblPerson');
        $this->addForeignView('TblMember_serviceTblPerson', new ViewRelationshipToPerson(), 'TblToPerson_serviceTblPersonFrom');
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Group::useService();
    }
}
