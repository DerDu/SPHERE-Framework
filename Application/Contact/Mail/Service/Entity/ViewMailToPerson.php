<?php
namespace SPHERE\Application\Contact\Mail\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipFromPerson;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewMailToPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewMailToPerson extends AbstractView
{

    const TBL_TO_PERSON_ID = 'TblToPerson_Id';
    const TBL_TO_PERSON_SERVICE_TBL_PERSON = 'TblToPerson_serviceTblPerson';
    const TBL_TO_PERSON_REMARK = 'TblToPerson_Remark';
    const TBL_TO_PERSON_TBL_MAIL = 'TblToPerson_tblMail';
    const TBL_TO_PERSON_TBL_TYPE = 'TblToPerson_tblType';

    const TBL_MAIL_ID = 'TblMail_Id';
    const TBL_MAIL_ADDRESS = 'TblMail_Address';

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
    protected $TblToPerson_tblMail;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_tblType;

    /**
     * @Column(type="string")
     */
    protected $TblMail_Id;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address;

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

        return 'Kontakt E-Mail (Person)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_MAIL_ADDRESS, 'E-Mail: Adresse');
        $this->setNameDefinition(self::TBL_TYPE_NAME, 'E-Mail: Typ');
        $this->setNameDefinition(self::TBL_TO_PERSON_REMARK, 'E-Mail: Bemerkung');
    }

    public function loadDisableDefinition()
    {

        $this->setDisableDefinition(self::TBL_TYPE_DESCRIPTION);
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

        return Mail::useService();
    }
}
