<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewContactAddress")
 * @Cache(usage="READ_ONLY")
 */
class ViewContactAddress extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
//    const TBL_PERSON_ID = 'TblPerson_Id';
//    const TBL_TO_PERSON_REMARK = 'TblToPerson_Remark';
//
//    const TBL_TYPE_NAME = 'TblType_Name';
//    const TBL_TYPE_DESCRIPTION = 'TblType_Description';

    const TBL_ADDRESS_STREET_NAME = 'TblAddress_StreetName';
    const TBL_ADDRESS_STREET_NUMBER = 'TblAddress_StreetNumber';
//    const TBL_ADDRESS_POST_OFFICE_BOX = 'TblAddress_PostOfficeBox';
    const TBL_ADDRESS_COUNTY = 'TblAddress_County';
    const TBL_ADDRESS_NATION = 'TblAddress_Nation';

    const TBL_CITY_CODE = 'TblCity_Code';
    const TBL_CITY_NAME = 'TblCity_Name';
    const TBL_CITY_DISTRICT = 'TblCity_District';

    const TBL_ADDRESS_STATE = 'TblState_Name';

    /**
     * @return array
     */
    static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * @Column(type="string")
     */
    protected $TblPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_Remark;

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
    protected $TblAddress_StreetName;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblAddress_PostOfficeBox;
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
    protected $TblState_Name;

    public function loadNameDefinition()
    {
        // TODO: Implement loadNameDefinition() method.
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {
        // TODO: Implement loadViewGraph() method.
    }

    /**
     * @return void|AbstractService
     */
    public function getViewService()
    {
        // TODO: Implement getViewService() method.
    }

    //    protected $TblAddress_PostOfficeBox;
    /** @return string */
    public function getTblPerson_Id(){return $this->TblPerson_Id;}
    public function getTblToPerson_Remark(){return $this->TblToPerson_Remark;}
    public function getTblType_Name(){return $this->TblType_Name;}
    public function getTblType_Description(){return $this->TblType_Description;}
    public function getTblAddress_StreetName(){return $this->TblAddress_StreetName;}
    public function getTblAddress_StreetNumber(){return $this->TblAddress_StreetNumber;}
    public function getTblAddress_County(){return $this->TblAddress_County;}
    public function getTblAddress_Nation(){return $this->TblAddress_Nation;}
    public function getTblCity_Code(){return $this->TblCity_Code;}
    public function getTblCity_Name(){return $this->TblCity_Name;}
    public function getTblCity_District(){return $this->TblCity_District;}
    public function getTblState_Name(){return $this->TblState_Name;}

}
