<?php
namespace SPHERE\Application\Contact\Phone\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewPhoneToCompany")
 * @Cache(usage="READ_ONLY")
 */
class ViewPhoneToCompany extends AbstractView
{

    const TBL_TO_COMPANY_ID = 'TblToCompany_Id';
    const TBL_TO_COMPANY_SERVICE_TBL_COMPANY = 'TblToCompany_serviceTblCompany';
    const TBL_TO_COMPANY_REMARK = 'TblToCompany_Remark';
    const TBL_TO_COMPANY_TBL_PHONE = 'TblToCompany_tblPhone';
    const TBL_TO_COMPANY_TBL_TYPE = 'TblToCompany_tblType';

    const TBL_PHONE_ID = 'TblPhone_Id';
    const TBL_PHONE_NUMBER = 'TblPhone_Number';

    const TBL_TYPE_ID = 'TblType_Id';
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';

    /**
     * @Column(type="string")
     */
    protected $TblToCompany_Id;
    /**
     * @Column(type="string")
     */
    protected $TblToCompany_serviceTblCompany;
    /**
     * @Column(type="string")
     */
    protected $TblToCompany_tblPhone;
    /**
     * @Column(type="string")
     */
    protected $TblToCompany_tblType;

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
    protected $TblToCompany_Remark;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Kontakt Telefon (Firma)';
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
        $this->setNameDefinition(self::TBL_TO_COMPANY_REMARK, 'Telefon: Bemerkung');
    }

    public function loadDisableDefinition()
    {

//        $this->setDisableDefinition(self::TBL_TO_COMPANY_REMARK);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_TO_COMPANY_SERVICE_TBL_COMPANY, new ViewCompany(), ViewCompany::TBL_COMPANY_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {

        return Phone::useService();
    }
}
