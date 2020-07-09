<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupStudentBasic")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupStudentBasic extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_IDENTIFIER = 'TblStudent_Identifier';
    const TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE = 'TblStudent_SchoolAttendanceStartDate';
    const TBL_STUDENT_HAS_MIGRATION_BACKGROUND = 'TblStudent_HasMigrationBackground';
    const TBL_STUDENT_IS_IN_PREPARATION_DIVISION_FOR_MIGRANTS = 'TblStudent_IsInPreparationDivisionForMigrants';

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
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_SchoolAttendanceStartDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_HasMigrationBackground;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_IsInPreparationDivisionForMigrants;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_STUDENT_IDENTIFIER, 'Grunddaten: Schülernummer');
        $this->setNameDefinition(self::TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE, 'Grunddaten: Schulpflichtbeginn');
        $this->setNameDefinition(self::TBL_STUDENT_HAS_MIGRATION_BACKGROUND, 'Grunddaten: Herkunftssprache ist nicht oder nicht ausschließlich Deutsch');
//        $this->setNameDefinition(self::TBL_STUDENT_IS_IN_PREPARATION_DIVISION_FOR_MIGRANTS, 'Grunddaten: Besucht Vorbereitungsklasse für Migranten');

//        //GroupDefinition
        $this->setGroupDefinition('&nbsp;', array(
            self::TBL_STUDENT_IDENTIFIER,
            self::TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE,
            self::TBL_STUDENT_HAS_MIGRATION_BACKGROUND,
//            self::TBL_STUDENT_IS_IN_PREPARATION_DIVISION_FOR_MIGRANTS
        ));
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
            case self::TBL_STUDENT_IDENTIFIER:
                $Data = Common::useService()->getPropertyList(new TblStudent(), TblStudent::ATTR_TBL_IDENTIFIER);
                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_STUDENT_HAS_MIGRATION_BACKGROUND:
                $Data[1] = 'Ja';
                $Data[2] = 'Nein';
                $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
//            case self::TBL_STUDENT_IS_IN_PREPARATION_DIVISION_FOR_MIGRANTS:
//                $Data[1] = 'Ja';
//                $Data[2] = 'Nein';
//                $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
//                break;
            default:
                $Field = parent::getFormField($PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
