<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewAddressToCompany")
 * @Cache(usage="READ_ONLY")
 */
class ViewAddressToCompany extends AbstractView
{

    const TBL_TO_COMPANY_ID = 'TblToCompany_Id';
    const TBL_TO_COMPANY_SERVICE_TBL_COMPANY = 'TblToCompany_serviceTblCompany';
    const TBL_TO_COMPANY_REMARK = 'TblToCompany_Remark';
    const TBL_TYPE_ID = 'TblType_Id';
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';
    const TBL_ADDRESS_ID = 'TblAddress_Id';
    const TBL_ADDRESS_STREET_NAME = 'TblAddress_StreetName';
    const TBL_ADDRESS_STREET_NUMBER = 'TblAddress_StreetNumber';
    const TBL_ADDRESS_POST_OFFICE_BOX = 'TblAddress_PostOfficeBox';
    const TBL_ADDRESS_COUNTY = 'TblAddress_County';
    const TBL_ADDRESS_NATION = 'TblAddress_Nation';
    const TBL_CITY_ID = 'TblCity_Id';
    const TBL_CITY_CODE = 'TblCity_Code';
    const TBL_CITY_NAME = 'TblCity_Name';
    const TBL_CITY_DISTRICT = 'TblCity_District';
    const TBL_STATE_ID = 'TblState_Id';
    const TBL_STATE_NAME = 'TblState_Name';

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
    protected $TblToCompany_Remark;

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
    protected $TblAddress_Id;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_PostOfficeBox;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_County;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation;

    /**
     * @Column(type="string")
     */
    protected $TblCity_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District;

    /**
     * @Column(type="string")
     */
    protected $TblState_Id;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Adressdaten (Firma)';
    }


    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_TO_COMPANY_REMARK, 'Adresse: Bemerkungen');
        $this->setNameDefinition(self::TBL_TYPE_NAME, 'Adresse: Adresstyp');
        $this->setNameDefinition(self::TBL_TYPE_DESCRIPTION, 'Adresse: Adresstyp-Bemerkung');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME, 'Adresse: Strasse');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER, 'Adresse: Hausnummer');
        $this->setNameDefinition(self::TBL_ADDRESS_POST_OFFICE_BOX, 'Adresse: Postfach');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY, 'Adresse: Kreis');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION, 'Adresse: Land');
        $this->setNameDefinition(self::TBL_CITY_CODE, 'Adresse: PLZ');
        $this->setNameDefinition(self::TBL_CITY_NAME, 'Adresse: Stadt');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT, 'Adresse: Ortsteil');
        $this->setNameDefinition(self::TBL_STATE_NAME, 'Adresse: Bundesland');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_TO_COMPANY_SERVICE_TBL_COMPANY, new ViewCompany(), ViewCompany::TBL_COMPANY_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        // TODO: Implement getViewService() method.
    }
}
