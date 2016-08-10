<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewAddressToPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewAddressToPerson extends AbstractView
{

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
    protected $TblToPerson_Remark;

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

        $this->setNameDefinition('TblToPerson_Remark', 'Person-Adresse Bemerkungen');
        $this->setNameDefinition('TblType_Name', 'Person-Adresse Adresstyp');
        $this->setNameDefinition('TblType_Description', 'Person-Adresse Adresstyp-Bemerkung');
        $this->setNameDefinition('TblAddress_StreetName', 'Person-Adresse Strasse');
        $this->setNameDefinition('TblAddress_StreetNumber', 'Person-Adresse Hausnummer');
        $this->setNameDefinition('TblAddress_PostOfficeBox', 'Person-Adresse Postfach');
        $this->setNameDefinition('TblAddress_County', 'Person-Adresse Kreis');
        $this->setNameDefinition('TblAddress_Nation', 'Person-Adresse Land');
        $this->setNameDefinition('TblCity_Code', 'Person-Adresse PLZ');
        $this->setNameDefinition('TblCity_Name', 'Person-Adresse Stadt');
        $this->setNameDefinition('TblCity_District', 'Person-Adresse Ortsteil');
        $this->setNameDefinition('TblState_Name', 'Person-Adresse Bundesland');
    }
}
