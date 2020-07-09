<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewPerson extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    const TBL_SALUTATION_SALUTATION = 'TblSalutation_Salutation';
    const TBL_PERSON_TITLE = 'TblPerson_Title';
    const TBL_PERSON_FIRST_NAME = 'TblPerson_FirstName';
    const TBL_PERSON_SECOND_NAME = 'TblPerson_SecondName';
    const TBL_PERSON_CALL_NAME = 'TblPerson_CallName';
    const TBL_PERSON_LAST_NAME = 'TblPerson_LastName';
    const TBL_PERSON_BIRTH_NAME = 'TblPerson_BirthName';
    // Special Column #SSW-704
    const TBL_PERSON_FIRST_LAST_NAME = 'TblPerson_FirstLastName';
    const TBL_PERSON_LAST_FIRST_NAME = 'TblPerson_LastFirstName';

    const TBL_GROUP_GROUP_LIST = 'TblGroup_GroupList';

    const TBL_COMMON_BIRTHDATES_BIRTHDAY = 'TblCommonBirthDates_Birthday';
    const TBL_COMMON_BIRTHDATES_BIRTHPLACE = 'TblCommonBirthDates_Birthplace';
    const TBL_COMMON_GENDER_NAME = 'TblCommonGender_Name';
    const TBL_COMMON_INFORMATION_NATIONALITY = 'TblCommonInformation_Nationality';
    const TBL_COMMON_INFORMATION_DENOMINATION = 'TblCommonInformation_Denomination';
    const TBL_COMMON_INFORMATION_IS_ASSISTANCE = 'TblCommonInformation_IsAssistance';
    const TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY = 'TblCommonInformation_AssistanceActivity';
    const TBL_COMMON_REMARK = 'TblCommon_Remark';

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
    protected $TblCommonGender_Name;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_CallName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_BirthName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstLastName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastFirstName;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_GroupList;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Denomination;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Nationality;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_IsAssistance;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_AssistanceActivity;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthday;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthplace;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION, 'Person: Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE, 'Person: Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME, 'Person: Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME, 'Person: Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_CALL_NAME, 'Person: Rufname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME, 'Person: Nachname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_NAME, 'Person: Geburtsname');

        $this->setNameDefinition(self::TBL_PERSON_FIRST_LAST_NAME, 'Person: Vorname Nachname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_FIRST_NAME, 'Person: Nachname Vorname');

        $this->setNameDefinition(self::TBL_GROUP_GROUP_LIST, 'Person: Gruppenliste');

        $this->setNameDefinition(self::TBL_COMMON_BIRTHDATES_BIRTHDAY, 'Person: Geburtstag');
        $this->setNameDefinition(self::TBL_COMMON_BIRTHDATES_BIRTHPLACE, 'Person: Geburtsort');
        $this->setNameDefinition(self::TBL_COMMON_GENDER_NAME, 'Person: Geschlecht');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_NATIONALITY, 'Person: Staatsangehörigkeit');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_DENOMINATION, 'Person: Konfession');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_IS_ASSISTANCE, 'Person: Mitarbeitsbereitschaft');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY, 'Person: Mitarbeit Tätigkeit');
        $this->setNameDefinition(self::TBL_COMMON_REMARK, 'Person: Bemerkung zur Person');

        //GroupDefinition
        $this->setGroupDefinition('Grunddaten', array(
            self::TBL_SALUTATION_SALUTATION,
            self::TBL_PERSON_TITLE,
            self::TBL_PERSON_FIRST_NAME,
            self::TBL_PERSON_SECOND_NAME,
            self::TBL_PERSON_CALL_NAME,
            self::TBL_PERSON_LAST_NAME,
            self::TBL_PERSON_FIRST_LAST_NAME,
            self::TBL_PERSON_LAST_FIRST_NAME,
//            self::TBL_PERSON_BIRTH_NAME,
        ));
        $this->setGroupDefinition('Personendaten', array(
            self::TBL_GROUP_GROUP_LIST,
            self::TBL_COMMON_BIRTHDATES_BIRTHDAY,
            self::TBL_COMMON_BIRTHDATES_BIRTHPLACE,
            self::TBL_COMMON_GENDER_NAME,
            self::TBL_COMMON_INFORMATION_NATIONALITY,
            self::TBL_COMMON_INFORMATION_DENOMINATION,
            self::TBL_COMMON_INFORMATION_IS_ASSISTANCE,
            self::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY,
            self::TBL_COMMON_REMARK,
        ));

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
        $this->setDisableDefinition(self::TBL_PERSON_FIRST_LAST_NAME);
        $this->setDisableDefinition(self::TBL_PERSON_LAST_FIRST_NAME);
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
//            case self::TBL_PERSON_FIRST_NAME:
//                // old version: all name from City
//                $Data = Person::useService()->getPropertyList( new TblPerson(), TblPerson::ATTR_FIRST_NAME );
//                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
//                break;
//            case self::TBL_PERSON_LAST_NAME:
//                // old version: all name from City
//                $Data = Person::useService()->getPropertyList( new TblPerson(), TblPerson::ATTR_LAST_NAME );
//                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
//                break;
            case self::TBL_PERSON_BIRTH_NAME:
                // old version: all name from City
                $Data = Person::useService()->getPropertyList( new TblPerson(), TblPerson::ATTR_LAST_NAME );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_COMMON_INFORMATION_NATIONALITY:
                // old version: all name from City
                $Data = Common::useService()->getPropertyList( new TblCommonInformation(), TblCommonInformation::ATTR_NATIONALITY );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_COMMON_INFORMATION_DENOMINATION:
                // old version: all name from City
                $Data = Common::useService()->getPropertyList( new TblCommonInformation(), TblCommonInformation::ATTR_DENOMINATION );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_COMMON_INFORMATION_IS_ASSISTANCE:
                $Data[1] = 'Ja';
                $Data[2] = 'Nein';
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_COMMON_BIRTHDATES_BIRTHPLACE:
                $Data = Common::useService()->getPropertyList( new TblCommonBirthDates(), TblCommonBirthDates::ATTR_BIRTHPLACE );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_COMMON_GENDER_NAME:
                $Data = Common::useService()->getPropertyList( new TblCommonGender(), TblCommonGender::ATTR_NAME );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_SALUTATION_SALUTATION:
                $Data = Person::useService()->getPropertyList( new TblSalutation(''), TblSalutation::ATTR_SALUTATION );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_GROUP_GROUP_LIST:
                $Data = Group::useService()->getPropertyList( new TblGroup(''), TblGroup::ATTR_NAME );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }


    // only necessary if need in twig (SelectBox etc.)
//    /** @return string */
//    public function getTblPerson_Id(){return $this->TblPerson_Id;}
//    public function getTblCommonGender_Name(){return $this->TblCommonGender_Name;}
//    public function getTblSalutation_Salutation(){return $this->TblSalutation_Salutation;}
//    public function getTblPerson_Title(){return $this->TblPerson_Title;}
//    public function TblPerson_FirstName(){return $this->TblPerson_FirstName;}
//    public function TblPerson_SecondName(){return $this->TblPerson_SecondName;}
//    public function TblPerson_CallName(){return $this->TblPerson_CallName;}
//    public function getTblPerson_LastName(){return $this->TblPerson_LastName;}
//    public function getTblPerson_BirthName(){return $this->TblPerson_BirthName;}
//    public function getTblPerson_FirstLastName(){return $this->TblPerson_FirstLastName;}
//    public function getTblPerson_LastFirstName(){return $this->TblPerson_LastFirstName;}
//    public function getTblGroup_GroupList(){return $this->TblGroup_GroupList;}
//    public function getTblCommonInformation_Denomination(){return $this->TblCommonInformation_Denomination;}
//    public function getTblCommonInformation_Nationality(){return $this->TblCommonInformation_Nationality;}
//    public function getTblCommonInformation_IsAssistance(){return $this->TblCommonInformation_IsAssistance;}
//    public function getTblCommonInformation_AssistanceActivity(){return $this->TblCommonInformation_AssistanceActivity;}
//    public function getTblCommon_Remark(){return $this->TblCommon_Remark;}
//    public function getTblCommonBirthDates_Birthday(){return $this->TblCommonBirthDates_Birthday;}
//    public function getTblCommonBirthDates_Birthplace(){return $this->TblCommonBirthDates_Birthplace;}
}
