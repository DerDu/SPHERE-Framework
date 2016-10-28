<?php
namespace SPHERE\Application\Contact\Phone\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipFromPerson;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewPhoneToPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewPhoneToPerson extends AbstractView
{

    const TBL_TO_PERSON_ID = 'TblToPerson_Id';
    const TBL_TO_PERSON_SERVICE_TBL_PERSON = 'TblToPerson_serviceTblPerson';
    const TBL_TO_PERSON_REMARK = 'TblToPerson_Remark';
    const TBL_TO_PERSON_TBL_PHONE = 'TblToPerson_tblPhone';
    const TBL_TO_PERSON_TBL_TYPE = 'TblToPerson_tblType';

    const TBL_PHONE_ID = 'TblPhone_Id';
    const TBL_PHONE_NUMBER = 'TblPhone_Number';

    const TBL_TYPE_ID = 'TblType_Id';
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';

    /**
     * @Column(type="string")
     */
    protected $TblToPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_tblPhone;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_tblType;

    /**
     * @Column(type="string")
     */
    protected $TblPhone_Id;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number;

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

    /** (position for order)
     * @Column(type="string")
     */
    protected $TblToPerson_Remark;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Kontakt Telefon (Person)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_PHONE_NUMBER, 'Telefon: Nummer');
        $this->setNameDefinition(self::TBL_TYPE_NAME, 'Telefon: Typ');
        $this->setNameDefinition(self::TBL_TYPE_DESCRIPTION, 'Telefon: Beschreibung');
        $this->setNameDefinition(self::TBL_TO_PERSON_REMARK, 'Telefon: Bemerkung');
    }

    public function loadDisableDefinition()
    {

//        $this->setDisableDefinition(self::TBL_TO_PERSON_REMARK);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_TO_PERSON_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
        $this->addForeignView(self::TBL_TO_PERSON_SERVICE_TBL_PERSON, new ViewRelationshipToPerson(),
            ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM
        );
        $this->addForeignView(self::TBL_TO_PERSON_SERVICE_TBL_PERSON, new ViewRelationshipFromPerson(),
            ViewRelationshipFromPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_TO
        );
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {

        return Phone::useService();
    }
}
