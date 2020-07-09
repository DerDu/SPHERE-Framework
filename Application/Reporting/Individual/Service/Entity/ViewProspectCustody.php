<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewProspectCustody")
 * @Cache(usage="READ_ONLY")
 */
class ViewProspectCustody extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    // S1
    const TBL_SALUTATION_SALUTATION_S1 = 'TblSalutation_Salutation_S1';
    const TBL_PERSON_TITLE_S1 = 'TblPerson_Title_S1';
    const TBL_PERSON_FIRST_NAME_S1 = 'TblPerson_FirstName_S1';
    const TBL_PERSON_SECOND_NAME_S1 = 'TblPerson_SecondName_S1';
    const TBL_PERSON_CALL_NAME_S1 = 'TblPerson_CallName_S1';
    const TBL_PERSON_LAST_NAME_S1 = 'TblPerson_LastName_S1';
    const TBL_PERSON_BIRTH_NAME_S1 = 'TblPerson_BirthName_S1';
    const TBL_PERSON_BIRTH_DAY_S1 = 'TblPerson_Birthday_S1';
    const TBL_ADDRESS_STREET_NAME_S1 = 'TblAddress_StreetName_S1';
    const TBL_ADDRESS_STREET_NUMBER_S1 = 'TblAddress_StreetNumber_S1';
    const TBL_CITY_CODE_S1 = 'TblCity_Code_S1';
    const TBL_CITY_NAME_S1 = 'TblCity_Name_S1';
    const TBL_CITY_DISTRICT_S1 = 'TblCity_District_S1';
    const TBL_ADDRESS_COUNTY_S1 = 'TblAddress_County_S1';
    const TBL_ADDRESS_STATE_S1 = 'TblState_Name_S1';
    const TBL_ADDRESS_NATION_S1 = 'TblAddress_Nation_S1';
    const TBL_PHONE_NUMBER_S1 = 'TblPhone_Number_S1';
    const TBL_MAIL_ADDRESS_S1 = 'TblMail_Address_S1';
    const TBL_MAIL_ADDRESS_PRIVATE_S1 = 'TblMail_AddressPrivate_S1';
    const TBL_MAIL_ADDRESS_COMPANY_S1 = 'TblMail_AddressCompany_S1';
    const TBL_To_Person_Is_Single_Parent_S1 = 'TblToPerson_IsSingleParent_S1';

    // S2
    const TBL_SALUTATION_SALUTATION_S2 = 'TblSalutation_Salutation_S2';
    const TBL_PERSON_TITLE_S2 = 'TblPerson_Title_S2';
    const TBL_PERSON_FIRST_NAME_S2 = 'TblPerson_FirstName_S2';
    const TBL_PERSON_SECOND_NAME_S2 = 'TblPerson_SecondName_S2';
    const TBL_PERSON_CALL_NAME_S2 = 'TblPerson_CallName_S2';
    const TBL_PERSON_LAST_NAME_S2 = 'TblPerson_LastName_S2';
    const TBL_PERSON_BIRTH_NAME_S2 = 'TblPerson_BirthName_S2';
    const TBL_PERSON_BIRTH_DAY_S2 = 'TblPerson_Birthday_S2';
    const TBL_ADDRESS_STREET_NAME_S2 = 'TblAddress_StreetName_S2';
    const TBL_ADDRESS_STREET_NUMBER_S2 = 'TblAddress_StreetNumber_S2';
    const TBL_CITY_CODE_S2 = 'TblCity_Code_S2';
    const TBL_CITY_NAME_S2 = 'TblCity_Name_S2';
    const TBL_CITY_DISTRICT_S2 = 'TblCity_District_S2';
    const TBL_ADDRESS_COUNTY_S2 = 'TblAddress_County_S2';
    const TBL_ADDRESS_STATE_S2 = 'TblState_Name_S2';
    const TBL_ADDRESS_NATION_S2 = 'TblAddress_Nation_S2';
    const TBL_PHONE_NUMBER_S2 = 'TblPhone_Number_S2';
    const TBL_MAIL_ADDRESS_S2 = 'TblMail_Address_S2';
    const TBL_MAIL_ADDRESS_PRIVATE_S2 = 'TblMail_AddressPrivate_S2';
    const TBL_MAIL_ADDRESS_COMPANY_S2 = 'TblMail_AddressCompany_S2';
    const TBL_To_Person_Is_Single_Parent_S2 = 'TblToPerson_IsSingleParent_S2';

    // S3
    const TBL_SALUTATION_SALUTATION_S3 = 'TblSalutation_Salutation_S3';
    const TBL_PERSON_TITLE_S3 = 'TblPerson_Title_S3';
    const TBL_PERSON_FIRST_NAME_S3 = 'TblPerson_FirstName_S3';
    const TBL_PERSON_SECOND_NAME_S3 = 'TblPerson_SecondName_S3';
    const TBL_PERSON_CALL_NAME_S3 = 'TblPerson_CallName_S3';
    const TBL_PERSON_LAST_NAME_S3 = 'TblPerson_LastName_S3';
    const TBL_PERSON_BIRTH_NAME_S3 = 'TblPerson_BirthName_S3';
    const TBL_PERSON_BIRTH_DAY_S3 = 'TblPerson_Birthday_S3';
    const TBL_ADDRESS_STREET_NAME_S3 = 'TblAddress_StreetName_S3';
    const TBL_ADDRESS_STREET_NUMBER_S3 = 'TblAddress_StreetNumber_S3';
    const TBL_CITY_CODE_S3 = 'TblCity_Code_S3';
    const TBL_CITY_NAME_S3 = 'TblCity_Name_S3';
    const TBL_CITY_DISTRICT_S3 = 'TblCity_District_S3';
    const TBL_ADDRESS_COUNTY_S3 = 'TblAddress_County_S3';
    const TBL_ADDRESS_STATE_S3 = 'TblState_Name_S3';
    const TBL_ADDRESS_NATION_S3 = 'TblAddress_Nation_S3';
    const TBL_PHONE_NUMBER_S3 = 'TblPhone_Number_S3';
    const TBL_MAIL_ADDRESS_S3 = 'TblMail_Address_S3';
    const TBL_MAIL_ADDRESS_PRIVATE_S3 = 'TblMail_AddressPrivate_S3';
    const TBL_MAIL_ADDRESS_COMPANY_S3 = 'TblMail_AddressCompany_S3';
    const TBL_To_Person_Is_Single_Parent_S3 = 'TblToPerson_IsSingleParent_S3';

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
    protected $TblSalutation_Salutation_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_CallName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_BirthName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Birthday_S1;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Name_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S1;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_County_S1;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name_S1;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S1;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S1;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressPrivate_S1;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressCompany_S1;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_IsSingleParent_S1;

    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_CallName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_BirthName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Birthday_S2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Name_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_County_S2;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name_S2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S2;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S2;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressPrivate_S2;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressCompany_S2;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_IsSingleParent_S2;

    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_CallName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_BirthName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Birthday_S3;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Name_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S3;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_County_S3;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name_S3;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S3;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S3;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressPrivate_S3;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressCompany_S3;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_IsSingleParent_S3;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        // S1
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S1, 'S1: Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S1, 'S1: Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S1, 'S1: Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S1, 'S1: Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_CALL_NAME_S1, 'S1: Rufname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S1, 'S1: Nachname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_NAME_S1, 'S1: Geburtsname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_DAY_S1, 'S1: Geburtstag');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S1, 'S1: Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S1, 'S1: Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE_S1, 'S1: PLZ');
        $this->setNameDefinition(self::TBL_CITY_NAME_S1, 'S1: Ort');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S1, 'S1: Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY_S1, 'S1: Landkreis');
        $this->setNameDefinition(self::TBL_ADDRESS_STATE_S1, 'S1: Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION_S1, 'S1: Land');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S1, 'S1: Telefon');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S1, 'S1: E-Mail');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_PRIVATE_S1, 'S1: E-Mail Privat');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_COMPANY_S1, 'S1: E-Mail Geschäftlich');
        $this->setNameDefinition(self::TBL_To_Person_Is_Single_Parent_S1, 'S1: Alleinerziehend');
        // S2
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S2, 'S2: Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S2, 'S2: Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S2, 'S2: Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S2, 'S2: Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_CALL_NAME_S2, 'S2: Rufname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S2, 'S2: Nachname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_NAME_S2, 'S2: Geburtsname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_DAY_S2, 'S2: Geburtstag');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S2, 'S2: Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S2, 'S2: Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE_S2, 'S2: PLZ');
        $this->setNameDefinition(self::TBL_CITY_NAME_S2, 'S2: Ort');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S2, 'S2: Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY_S2, 'S2: Landkreis');
        $this->setNameDefinition(self::TBL_ADDRESS_STATE_S2, 'S2: Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION_S2, 'S2: Land');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S2, 'S2: Telefon');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S2, 'S2: E-Mail');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_PRIVATE_S2, 'S2: E-Mail Privat');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_COMPANY_S2, 'S2: E-Mail Geschäftlich');
        $this->setNameDefinition(self::TBL_To_Person_Is_Single_Parent_S2, 'S2: Alleinerziehend');
        // S3
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S3, 'S3: Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S3, 'S3: Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S3, 'S3: Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S3, 'S3: Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_CALL_NAME_S3, 'S3: Rufname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S3, 'S3: Nachname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_NAME_S3, 'S3: Geburtsname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_DAY_S3, 'S3: Geburtstag');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S3, 'S3: Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S3, 'S3: Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE_S3, 'S3: PLZ');
        $this->setNameDefinition(self::TBL_CITY_NAME_S3, 'S3: Ort');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S3, 'S3: Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY_S3, 'S3: Landkreis');
        $this->setNameDefinition(self::TBL_ADDRESS_STATE_S3, 'S3: Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION_S3, 'S3: Land');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S3, 'S3: Telefon');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S3, 'S3: E-Mail');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_PRIVATE_S3, 'S3: E-Mail Privat');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_COMPANY_S3, 'S3: E-Mail Geschäftlich');
        $this->setNameDefinition(self::TBL_To_Person_Is_Single_Parent_S3, 'S3: Alleinerziehend');

        //GroupDefinition

        $this->setGroupDefinition('Sorge. S1 (Zusatzinfo)', array(
            self::TBL_SALUTATION_SALUTATION_S1,
            self::TBL_PERSON_TITLE_S1,
            self::TBL_PERSON_FIRST_NAME_S1,
            self::TBL_PERSON_SECOND_NAME_S1,
            self::TBL_PERSON_CALL_NAME_S1,
            self::TBL_PERSON_LAST_NAME_S1,
            self::TBL_PERSON_BIRTH_NAME_S1,
            self::TBL_PERSON_BIRTH_DAY_S1,
            self::TBL_ADDRESS_STREET_NAME_S1,
            self::TBL_ADDRESS_STREET_NUMBER_S1,
            self::TBL_CITY_CODE_S1,
            self::TBL_CITY_NAME_S1,
            self::TBL_CITY_DISTRICT_S1,
            self::TBL_ADDRESS_COUNTY_S1,
            self::TBL_ADDRESS_STATE_S1,
            self::TBL_ADDRESS_NATION_S1,
            self::TBL_PHONE_NUMBER_S1,
            self::TBL_MAIL_ADDRESS_S1,
            self::TBL_MAIL_ADDRESS_PRIVATE_S1,
            self::TBL_MAIL_ADDRESS_COMPANY_S1,
            self::TBL_To_Person_Is_Single_Parent_S1
        ));
        $this->setGroupDefinition('Sorge. S2 (Zusatzinfo)', array(
            self::TBL_SALUTATION_SALUTATION_S2,
            self::TBL_PERSON_TITLE_S2,
            self::TBL_PERSON_FIRST_NAME_S2,
            self::TBL_PERSON_SECOND_NAME_S2,
            self::TBL_PERSON_CALL_NAME_S2,
            self::TBL_PERSON_LAST_NAME_S2,
            self::TBL_PERSON_BIRTH_NAME_S2,
            self::TBL_PERSON_BIRTH_DAY_S2,
            self::TBL_ADDRESS_STREET_NAME_S2,
            self::TBL_ADDRESS_STREET_NUMBER_S2,
            self::TBL_CITY_CODE_S2,
            self::TBL_CITY_NAME_S2,
            self::TBL_CITY_DISTRICT_S2,
            self::TBL_ADDRESS_COUNTY_S2,
            self::TBL_ADDRESS_STATE_S2,
            self::TBL_ADDRESS_NATION_S2,
            self::TBL_PHONE_NUMBER_S2,
            self::TBL_MAIL_ADDRESS_S2,
            self::TBL_MAIL_ADDRESS_PRIVATE_S2,
            self::TBL_MAIL_ADDRESS_COMPANY_S2,
            self::TBL_To_Person_Is_Single_Parent_S2
        ));
        $this->setGroupDefinition('Sorge. S3 (Zusatzinfo)', array(
            self::TBL_SALUTATION_SALUTATION_S3,
            self::TBL_PERSON_TITLE_S3,
            self::TBL_PERSON_FIRST_NAME_S3,
            self::TBL_PERSON_SECOND_NAME_S3,
            self::TBL_PERSON_CALL_NAME_S3,
            self::TBL_PERSON_LAST_NAME_S3,
            self::TBL_PERSON_BIRTH_NAME_S3,
            self::TBL_PERSON_BIRTH_DAY_S3,
            self::TBL_ADDRESS_STREET_NAME_S3,
            self::TBL_ADDRESS_STREET_NUMBER_S3,
            self::TBL_CITY_CODE_S3,
            self::TBL_CITY_NAME_S3,
            self::TBL_CITY_DISTRICT_S3,
            self::TBL_ADDRESS_COUNTY_S3,
            self::TBL_ADDRESS_STATE_S3,
            self::TBL_ADDRESS_NATION_S3,
            self::TBL_PHONE_NUMBER_S3,
            self::TBL_MAIL_ADDRESS_S3,
            self::TBL_MAIL_ADDRESS_PRIVATE_S3,
            self::TBL_MAIL_ADDRESS_COMPANY_S3,
            self::TBL_To_Person_Is_Single_Parent_S3
        ));

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S1);
        $this->setDisableDefinition(self::TBL_PERSON_TITLE_S1);
        $this->setDisableDefinition(self::TBL_PERSON_FIRST_NAME_S1);
        $this->setDisableDefinition(self::TBL_PERSON_SECOND_NAME_S1);
        $this->setDisableDefinition(self::TBL_PERSON_CALL_NAME_S1);
        $this->setDisableDefinition(self::TBL_PERSON_LAST_NAME_S1);
        $this->setDisableDefinition(self::TBL_PERSON_BIRTH_NAME_S1);
        $this->setDisableDefinition(self::TBL_PERSON_BIRTH_DAY_S1);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NAME_S1);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NUMBER_S1);
        $this->setDisableDefinition(self::TBL_CITY_CODE_S1);
        $this->setDisableDefinition(self::TBL_CITY_NAME_S1);
        $this->setDisableDefinition(self::TBL_CITY_DISTRICT_S1);
        $this->setDisableDefinition(self::TBL_ADDRESS_COUNTY_S1);
        $this->setDisableDefinition(self::TBL_ADDRESS_STATE_S1);
        $this->setDisableDefinition(self::TBL_ADDRESS_NATION_S1);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_S1);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_S1);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_PRIVATE_S1);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_COMPANY_S1);
        $this->setDisableDefinition(self::TBL_To_Person_Is_Single_Parent_S1);
        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S2);
        $this->setDisableDefinition(self::TBL_PERSON_TITLE_S2);
        $this->setDisableDefinition(self::TBL_PERSON_FIRST_NAME_S2);
        $this->setDisableDefinition(self::TBL_PERSON_SECOND_NAME_S2);
        $this->setDisableDefinition(self::TBL_PERSON_CALL_NAME_S2);
        $this->setDisableDefinition(self::TBL_PERSON_LAST_NAME_S2);
        $this->setDisableDefinition(self::TBL_PERSON_BIRTH_NAME_S2);
        $this->setDisableDefinition(self::TBL_PERSON_BIRTH_DAY_S2);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NAME_S2);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NUMBER_S2);
        $this->setDisableDefinition(self::TBL_CITY_CODE_S2);
        $this->setDisableDefinition(self::TBL_CITY_NAME_S2);
        $this->setDisableDefinition(self::TBL_CITY_DISTRICT_S2);
        $this->setDisableDefinition(self::TBL_ADDRESS_COUNTY_S2);
        $this->setDisableDefinition(self::TBL_ADDRESS_STATE_S2);
        $this->setDisableDefinition(self::TBL_ADDRESS_NATION_S2);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_S2);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_S2);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_PRIVATE_S2);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_COMPANY_S2);
        $this->setDisableDefinition(self::TBL_To_Person_Is_Single_Parent_S2);
        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S3);
        $this->setDisableDefinition(self::TBL_PERSON_TITLE_S3);
        $this->setDisableDefinition(self::TBL_PERSON_FIRST_NAME_S3);
        $this->setDisableDefinition(self::TBL_PERSON_SECOND_NAME_S3);
        $this->setDisableDefinition(self::TBL_PERSON_CALL_NAME_S3);
        $this->setDisableDefinition(self::TBL_PERSON_LAST_NAME_S3);
        $this->setDisableDefinition(self::TBL_PERSON_BIRTH_NAME_S3);
        $this->setDisableDefinition(self::TBL_PERSON_BIRTH_DAY_S3);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NAME_S3);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NUMBER_S3);
        $this->setDisableDefinition(self::TBL_CITY_CODE_S3);
        $this->setDisableDefinition(self::TBL_CITY_NAME_S3);
        $this->setDisableDefinition(self::TBL_CITY_DISTRICT_S3);
        $this->setDisableDefinition(self::TBL_ADDRESS_COUNTY_S3);
        $this->setDisableDefinition(self::TBL_ADDRESS_STATE_S3);
        $this->setDisableDefinition(self::TBL_ADDRESS_NATION_S3);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_S3);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_S3);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_PRIVATE_S3);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_COMPANY_S3);
        $this->setDisableDefinition(self::TBL_To_Person_Is_Single_Parent_S3);
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
     * @return AbstractField
     */
    public function getFormField( $PropertyName, $Placeholder = null, $Label = null, IIconInterface $Icon = null, $doResetCount = false )
    {

        switch ($PropertyName) {
//            case self::SIBLINGS_COUNT:
//                $PropertyCount = $this->calculateFormFieldCount( $PropertyName, $doResetCount );
//                $Field = new NumberField( $PropertyName.'['.$PropertyCount.']',
//                    $Placeholder, $Label, $Icon
//                );
//                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
