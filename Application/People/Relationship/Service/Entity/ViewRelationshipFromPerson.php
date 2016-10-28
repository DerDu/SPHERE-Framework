<?php
namespace SPHERE\Application\People\Relationship\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewRelationshipFromPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewRelationshipFromPerson extends AbstractView
{

    const TBL_TO_PERSON_ID = 'TblToPerson_Id';
    const TBL_TO_PERSON_REMARK = 'TblToPerson_Remark';
    const TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM = 'TblToPerson_serviceTblPersonFrom';
    const TBL_TO_PERSON_SERVICE_TBL_PERSON_TO = 'TblToPerson_serviceTblPersonTo';
    const TBL_TO_PERSON_TBL_TYPE = 'TblToPerson_tblType';
    const TBL_TYPE_ID = 'TblType_Id';
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';
    const TBL_TYPE_IS_LOCKED = 'TblType_IsLocked';
    const TBL_TYPE_IS_BIDIRECTIONAL = 'TblType_IsBidirectional';
    const TBL_TYPE_TBL_GROUP = 'TblType_tblGroup';
    const TBL_GROUP_ID = 'TblGroup_Id';
    const TBL_GROUP_IDENTIFIER = 'TblGroup_Identifier';
    const TBL_GROUP_NAME = 'TblGroup_Name';
    const TBL_GROUP_DESCRIPTION = 'TblGroup_Description';

    /**
     * @Column(type="string")
     */
    protected $TblToPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_serviceTblPersonFrom;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_serviceTblPersonTo;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_tblType;

    /**
     * @Column(type="string")
     */
    protected $TblType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblType_Description;
    /**
     * @Column(type="string")
     */
    protected $TblType_IsLocked;
    /**
     * @Column(type="string")
     */
    protected $TblType_IsBidirectional;
    /**
     * @Column(type="string")
     */
    protected $TblType_tblGroup;

    /**
     * @Column(type="string")
     */
    protected $TblGroup_Id;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Name;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Description;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Personenbeziehungen zu dieser Person';
    }


    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_TO_PERSON_REMARK, 'Beziehung: Bemerkung');

        $this->setNameDefinition(self::TBL_TYPE_NAME, 'Beziehung: Typ');
//        $this->setNameDefinition(self::TBL_TYPE_DESCRIPTION, 'Beziehung: Typ-Bemerkung');

        $this->setNameDefinition(self::TBL_GROUP_NAME, 'Beziehung: Kategorie');
//        $this->setNameDefinition(self::TBL_GROUP_DESCRIPTION, 'Beziehung: Kategorie-Bemerkung');
    }

    public function loadDisableDefinition()
    {
        $this->setDisableDefinition(self::TBL_TYPE_IS_BIDIRECTIONAL);
        $this->setDisableDefinition(self::TBL_GROUP_IDENTIFIER);
        $this->setDisableDefinition(self::TBL_TYPE_DESCRIPTION);
        $this->setDisableDefinition(self::TBL_GROUP_DESCRIPTION);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
        $this->addForeignView(self::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM, new ViewAddressToPerson(), ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);

    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Relationship::useService();
    }


}
