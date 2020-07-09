<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPersonContact")
 * @Cache(usage="READ_ONLY")
 */
class ViewPersonContact extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    const TBL_ADDRESS_STREET_NAME = 'TblAddress_StreetName';
    const TBL_ADDRESS_STREET_NUMBER = 'TblAddress_StreetNumber';
    const TBL_CITY_CODE = 'TblCity_Code';
    const TBL_CITY_NAME = 'TblCity_Name';
    const TBL_CITY_DISTRICT = 'TblCity_District';
    const TBL_ADDRESS_COUNTY = 'TblAddress_County';
    const TBL_ADDRESS_STATE = 'TblState_Name';
    const TBL_ADDRESS_NATION = 'TblAddress_Nation';

    const TBL_ADDRESS_STREET_NAME_2 = 'TblAddress_StreetName2';
    const TBL_ADDRESS_STREET_NUMBER_2 = 'TblAddress_StreetNumber2';
    const TBL_CITY_CODE_2 = 'TblCity_Code2';
    const TBL_CITY_NAME_2 = 'TblCity_Name2';
    const TBL_CITY_DISTRICT_2 = 'TblCity_District2';
    const TBL_ADDRESS_COUNTY_2 = 'TblAddress_County2';
    const TBL_ADDRESS_STATE_2 = 'TblState_Name2';
    const TBL_ADDRESS_NATION_2 = 'TblAddress_Nation2';

    const TBL_PHONE_NUMBER = 'TblPhone_Number';
    const TBL_MAIL_ADDRESS = 'TblMail_Address';
    const TBL_MAIL_ADDRESS_PRIVATE = 'TblMail_AddressPrivate';
    const TBL_MAIL_ADDRESS_COMPANY = 'TblMail_AddressCompany';

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
    protected $TblAddress_StreetName;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber;
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
    protected $TblAddress_County;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name;

    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Name2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_County2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation2;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name2;

    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressPrivate;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressCompany;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME, 'Hauptadresse: Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER, 'Hauptadresse: Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE, 'Hauptadresse: Postleitzahl');
        $this->setNameDefinition(self::TBL_CITY_NAME, 'Hauptadresse: Ort');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT, 'Hauptadresse: Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY, 'Hauptadresse: Landkreis');
        $this->setNameDefinition(self::TBL_ADDRESS_STATE, 'Hauptadresse: Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION, 'Hauptadresse: Land');

        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_2, 'Nebenadresse: Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_2, 'Nebenadresse: Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE_2, 'Nebenadresse: Postleitzahl');
        $this->setNameDefinition(self::TBL_CITY_NAME_2, 'Nebenadresse: Ort');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_2, 'Nebenadresse: Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY_2, 'Nebenadresse: Landkreis');
        $this->setNameDefinition(self::TBL_ADDRESS_STATE_2, 'Nebenadresse: Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION_2, 'Nebenadresse: Land');

        $this->setNameDefinition(self::TBL_PHONE_NUMBER, 'Person: Telefon');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS, 'Person: E-Mail');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_PRIVATE, 'Person: E-Mail Privat');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_COMPANY, 'Person: E-Mail Geschäftlich');

        $this->setGroupDefinition('Hauptadresse', array(
            self::TBL_ADDRESS_STREET_NAME,
            self::TBL_ADDRESS_STREET_NUMBER,
            self::TBL_CITY_CODE,
            self::TBL_CITY_NAME,
            self::TBL_CITY_DISTRICT,
            self::TBL_ADDRESS_COUNTY,
            self::TBL_ADDRESS_STATE,
            self::TBL_ADDRESS_NATION,
        ));

        $this->setGroupDefinition('Zweit-/Nebenadresse', array(
            self::TBL_ADDRESS_STREET_NAME_2,
            self::TBL_ADDRESS_STREET_NUMBER_2,
            self::TBL_CITY_CODE_2,
            self::TBL_CITY_NAME_2,
            self::TBL_CITY_DISTRICT_2,
            self::TBL_ADDRESS_COUNTY_2,
            self::TBL_ADDRESS_STATE_2,
            self::TBL_ADDRESS_NATION_2,
        ));

        $this->setGroupDefinition('Kontaktdaten', array(
            self::TBL_PHONE_NUMBER,
            self::TBL_MAIL_ADDRESS,
            self::TBL_MAIL_ADDRESS_PRIVATE,
            self::TBL_MAIL_ADDRESS_COMPANY,
        ));

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
//        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S1);
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

    /**
     * Define Property Field-Type and additional Data
     *
     * @param string $PropertyName __CLASS__::{CONSTANT_}
     * @param null|string $Placeholder
     * @param null|string $Label
     * @param IIconInterface|null $Icon
     * @param bool $doResetCount Reset ALL FieldName calculations e.g. FieldName[23] -> FieldName[1]
     * @param string $ViewType
     * @return AbstractField
     */
    public function getFormField( $PropertyName, $Placeholder = null, $Label = null, IIconInterface $Icon = null,
        $doResetCount = false, $ViewType = TblWorkSpace::VIEW_TYPE_ALL  )
    {

        switch ($PropertyName) {
            case self::TBL_CITY_NAME:
                // Test Address By Student
                $Data = array();
                $tblGroup = false;
                switch ($ViewType){
                    case TblWorkSpace::VIEW_TYPE_STUDENT:
                        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
                        break;
                    case TblWorkSpace::VIEW_TYPE_PROSPECT:
                        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
                        break;
                    case TblWorkSpace::VIEW_TYPE_CUSTODY:
                        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
                        break;
                    case TblWorkSpace::VIEW_TYPE_TEACHER:
                        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
                        break;
                    default:
                        // Alle Personen --// problem 3 sec. oder mehr zum laden
//                        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);

//                        // old version: Alle Namen aller Städte  --// problem: Gelöschte Einträge werden auch angezeigt
//                        $Data = Address::useService()->getPropertyList( new TblCity(), TblCity::ATTR_NAME );
                        // über View's
                            // holen über View's
//                        $viewContactAddressList = Individual::useService()->getViewContactAddressAll();
//                        $Data = array('TblCity_Name' => $viewContactAddressList);
                            // spiezielle Abfrage aus der View
                        $cityNameList = Individual::useService()->getCityNameGroupByCityName();
                        $Data = $cityNameList;
                        break;
                }
                if($tblGroup){
                    $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
                    if ($tblPersonList) {
                        foreach ($tblPersonList as $tblPerson) {
                            $tblAddress = $tblPerson->fetchMainAddress();
                            if ($tblAddress) {
                                $tblCity = $tblAddress->getTblCity();
                                if ($tblCity) {
                                    if (!isset($Data[$tblCity->getId()])) {
                                        $Data[$tblCity->getId()] = $tblCity->getName();
                                    }
                                }
                            }
                        }
                    }
                }

                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_CITY_NAME_2:
                // old version: all name from City
                $Data = Address::useService()->getPropertyList( new TblCity(), TblCity::ATTR_NAME );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_ADDRESS_STREET_NAME:
                // old version: all name from City
                $Data = Address::useService()->getPropertyList( new TblAddress(), TblAddress::ATTR_STREET_NAME );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_ADDRESS_STREET_NAME_2:
                // old version: all name from City
                $Data = Address::useService()->getPropertyList( new TblAddress(), TblAddress::ATTR_STREET_NAME );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_CITY_CODE:
                // old version: all name from City
                $Data = Address::useService()->getPropertyList( new TblCity(), TblCity::ATTR_CODE );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_CITY_CODE_2:
                // old version: all name from City
                $Data = Address::useService()->getPropertyList( new TblCity(), TblCity::ATTR_CODE );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_CITY_DISTRICT:
                // old version: all name from City
                $Data = Address::useService()->getPropertyList( new TblCity(), TblCity::ATTR_CODE );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_CITY_DISTRICT_2:
                // old version: all name from City
                $Data = Address::useService()->getPropertyList( new TblCity(), TblCity::ATTR_CODE );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_MAIL_ADDRESS:
                // old version: all name from City
                $Data = Mail::useService()->getPropertyList( new TblMail(), TblMail::ATTR_ADDRESS );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_PHONE_NUMBER:
                // old version: all name from City
                $Data = Phone::useService()->getPropertyList( new TblPhone(), TblPhone::ATTR_NUMBER );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
