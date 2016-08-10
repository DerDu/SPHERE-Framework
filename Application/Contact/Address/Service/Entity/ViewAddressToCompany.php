<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewAddressToCompany")
 * @Cache(usage="READ_ONLY")
 */
class ViewAddressToCompany extends AbstractView
{

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
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {
        // TODO: Implement loadNameDefinition() method.
    }
}
