<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewEducationStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewEducationStudent extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    const TBL_DIVISION_DISPLAY = 'TblDivision_Display';
    const TBL_LEVEL_ID = 'TblLevel_Id';
    const TBL_LEVEL_NAME = 'TblLevel_Name';
    const TBL_LEVEL_DESCRIPTION = 'TblLevel_Description';
    const TBL_LEVEL_IS_CHECKED = 'TblLevel_IsChecked';

    const TBL_DIVISION_NAME = 'TblDivision_Name';
    const TBL_DIVISION_DESCRIPTION = 'TblDivision_Description';

    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';

    const TBL_YEAR_YEAR = 'TblYear_Year';
    const TBL_YEAR_DESCRIPTION = 'TblYear_Description';

    /**
     * @return array
     */
    public static function getConstants()
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
    protected $TblDivision_Display;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_Id;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_Name;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_Description;
    /**
     * @Column(type="string")
     */
    protected $TblLevel_IsChecked;
    /**
     * @Column(type="string")
     */
    protected $TblDivision_Name;
    /**
     * @Column(type="string")
     */
    protected $TblDivision_Description;
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
    protected $TblYear_Year;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Description;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_DIVISION_DISPLAY, 'Bildung: Klasse');
        $this->setNameDefinition(self::TBL_LEVEL_ID, 'Bildung: Klassenstufe');
//        $this->setNameDefinition(self::TBL_LEVEL_NAME, 'Bildung: Klassenstufe');
        $this->setNameDefinition(self::TBL_LEVEL_DESCRIPTION, 'Bildung: Stufen Beschreibung');
        $this->setNameDefinition(self::TBL_LEVEL_IS_CHECKED, 'Bildung: Klasse ist Stufenübergreifend');
        $this->setNameDefinition(self::TBL_DIVISION_NAME, 'Bildung: Klassengruppe');
        $this->setNameDefinition(self::TBL_DIVISION_DESCRIPTION, 'Bildung: Klassen Beschreibung');
        $this->setNameDefinition(self::TBL_TYPE_NAME, 'Bildung: Schulart');
        $this->setNameDefinition(self::TBL_YEAR_YEAR, 'Bildung: Schuljahr');
        $this->setNameDefinition(self::TBL_YEAR_DESCRIPTION, 'Bildung: Schuljahr Beschreibung');

        //GroupDefinition
        $this->setGroupDefinition('Klasse', array(
            self::TBL_DIVISION_DISPLAY,
            self::TBL_LEVEL_ID,
            self::TBL_DIVISION_NAME,
            self::TBL_DIVISION_DESCRIPTION,
            self::TBL_TYPE_NAME,
        ));

        $this->setGroupDefinition('Zeitraum', array(
            self::TBL_YEAR_YEAR,
            self::TBL_YEAR_DESCRIPTION,
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
     * @return AbstractField
     */
    public function getFormField( $PropertyName, $Placeholder = null, $Label = null, IIconInterface $Icon = null, $doResetCount = false )
    {

        switch ($PropertyName) {
            case self::TBL_LEVEL_ID:
                // Test Address By Student
                $Data = array();
                $tblLevelList = Division::useService()->getLevelAll();
                if($tblLevelList){
                    foreach($tblLevelList as $tblLevel){
                        // nur richtige Klassenstufen anzeigen
                        if(!$tblLevel->getIsChecked()){
                            $Type = '';
                            // Schulart der Klassenstufe zusätzlich anzeigen
                            if(($tblType = $tblLevel->getServiceTblType())){
                                $Type = $tblType->getName();
                            }
                            $Data[$tblLevel->getId()] = $tblLevel->getName().' '.$Type;
                        }
                    }
                }
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount, false);
                break;
            case self::TBL_TYPE_NAME:
                $Data = Type::useService()->getPropertyList(new TblType(), TblType::ATTR_NAME);
                $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount, true);
                break;
            case self::TBL_YEAR_YEAR:
                $Data = Term::useService()->getPropertyList( new TblYear(), TblYear::ATTR_YEAR );
                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Placeholder, $Label, $Icon,
                    $doResetCount);
                break;
//            case self::TBL_SUBJECT_NAME:
//                $Data = Subject::useService()->getPropertyList(new TblSubject(), TblSubject::ATTR_NAME);
//                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Placeholder, $Label, $Icon,
//                    $doResetCount);
//                break;
//            case self::TBL_SUBJECT_ACRONYM:
//                $Data = Subject::useService()->getPropertyList(new TblSubject(), TblSubject::ATTR_ACRONYM);
//                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
//                break;
            case self::TBL_LEVEL_IS_CHECKED:
                $Data = array( 0 => 'Nein', 1 => 'Ja' );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount, false );
                break;

            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
        }
        return $Field;
    }
}
