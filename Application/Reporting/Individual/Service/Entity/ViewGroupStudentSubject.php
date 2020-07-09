<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupStudentSubject")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupStudentSubject extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';
    // Subject
    const TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_1 = 'TblSubject_Name_ForeignLanguage1';
    const TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_1 = 'TblSubject_From_ForeignLanguage1';
    const TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_1 = 'TblSubject_Till_ForeignLanguage1';
    const TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_2 = 'TblSubject_Name_ForeignLanguage2';
    const TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_2 = 'TblSubject_From_ForeignLanguage2';
    const TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_2 = 'TblSubject_Till_ForeignLanguage2';
    const TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_3 = 'TblSubject_Name_ForeignLanguage3';
    const TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_3 = 'TblSubject_From_ForeignLanguage3';
    const TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_3 = 'TblSubject_Till_ForeignLanguage3';
    const TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_4 = 'TblSubject_Name_ForeignLanguage4';
    const TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_4 = 'TblSubject_From_ForeignLanguage4';
    const TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_4 = 'TblSubject_Till_ForeignLanguage4';
    const TBL_SUBJECT_NAME_RELIGION = 'TblSubject_Name_Religion';
    const TBL_SUBJECT_NAME_PROFILE = 'TblSubject_Name_Profile';
    const TBL_SUBJECT_NAME_ORIENTATION = 'TblSubject_Name_Orientation';
    const TBL_SUBJECT_NAME_ELECTIVE_1 = 'TblSubject_Name_Elective1';
    const TBL_SUBJECT_NAME_ELECTIVE_2 = 'TblSubject_Name_Elective2';
    const TBL_SUBJECT_NAME_ELECTIVE_3 = 'TblSubject_Name_Elective3';
    const TBL_SUBJECT_NAME_ELECTIVE_4 = 'TblSubject_Name_Elective4';
    const TBL_SUBJECT_NAME_TEAM_1 = 'TblSubject_Name_Team1';
    const TBL_SUBJECT_NAME_TEAM_2 = 'TblSubject_Name_Team2';
    const TBL_SUBJECT_NAME_TEAM_3 = 'TblSubject_Name_Team3';
    const TBL_SUBJECT_NAME_TEAM_4 = 'TblSubject_Name_Team4';
    const TBL_SUBJECT_NAME_TEAM_5 = 'TblSubject_Name_Team5';

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
    protected $TblSubject_Name_ForeignLanguage1;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_From_ForeignLanguage1;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Till_ForeignLanguage1;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_ForeignLanguage2;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_From_ForeignLanguage2;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Till_ForeignLanguage2;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_ForeignLanguage3;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_From_ForeignLanguage3;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Till_ForeignLanguage3;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_ForeignLanguage4;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_From_ForeignLanguage4;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Till_ForeignLanguage4;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Religion;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Profile;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Orientation;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Elective1;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Elective2;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Elective3;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Elective4;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Team1;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Team2;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Team3;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Team4;
    /**
     * @Column(type="string")
     */
    protected $TblSubject_Name_Team5;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_1, 'Fremdsprache 1: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_1, 'Fremdsprache 1: Von');
        $this->setNameDefinition(self::TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_1, 'Fremdsprache 1: Bis');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_2, 'Fremdsprache 2: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_2, 'Fremdsprache 2: Von');
        $this->setNameDefinition(self::TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_2, 'Fremdsprache 2: Bis');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_3, 'Fremdsprache 3: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_3, 'Fremdsprache 3: Von');
        $this->setNameDefinition(self::TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_3, 'Fremdsprache 3: Bis');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_4, 'Fremdsprache 4: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_4, 'Fremdsprache 4: Von');
        $this->setNameDefinition(self::TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_4, 'Fremdsprache 4: Bis');

        $this->setNameDefinition(self::TBL_SUBJECT_NAME_RELIGION, 'Religion: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_PROFILE, 'Profil: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_ORIENTATION, 'Neigungskurs: Fach');

        $this->setNameDefinition(self::TBL_SUBJECT_NAME_ELECTIVE_1, 'Wahlfach 1: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_ELECTIVE_2, 'Wahlfach 2: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_ELECTIVE_3, 'Wahlfach 3: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_ELECTIVE_4, 'Wahlfach 4: Fach');

        $this->setNameDefinition(self::TBL_SUBJECT_NAME_TEAM_1, 'Arbeitsgemeinschaft 1: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_TEAM_2, 'Arbeitsgemeinschaft 2: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_TEAM_3, 'Arbeitsgemeinschaft 3: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_TEAM_4, 'Arbeitsgemeinschaft 4: Fach');
        $this->setNameDefinition(self::TBL_SUBJECT_NAME_TEAM_5, 'Arbeitsgemeinschaft 5: Fach');

          //GroupDefinition
        $this->setGroupDefinition('Fremdsprachen', array(
            self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_1,
            self::TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_1,
            self::TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_1,
            self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_2,
            self::TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_2,
            self::TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_2,
            self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_3,
            self::TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_3,
            self::TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_3,
            self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_4,
            self::TBL_SUBJECT_FROM_FOREIGN_LANGUAGE_4,
            self::TBL_SUBJECT_TILL_FOREIGN_LANGUAGE_4
        ));
        $this->setGroupDefinition('Religion', array(
            self::TBL_SUBJECT_NAME_RELIGION
        ));
        $this->setGroupDefinition('Profil', array(
            self::TBL_SUBJECT_NAME_PROFILE
        ));
        $this->setGroupDefinition('Neigungskurs', array(
            self::TBL_SUBJECT_NAME_ORIENTATION
        ));
        $this->setGroupDefinition('Wahlfach', array(
            self::TBL_SUBJECT_NAME_ELECTIVE_1,
            self::TBL_SUBJECT_NAME_ELECTIVE_2,
            self::TBL_SUBJECT_NAME_ELECTIVE_3,
            self::TBL_SUBJECT_NAME_ELECTIVE_4
        ));
        $this->setGroupDefinition('Arbeitsgemeinschaft', array(
            self::TBL_SUBJECT_NAME_TEAM_1,
            self::TBL_SUBJECT_NAME_TEAM_2,
            self::TBL_SUBJECT_NAME_TEAM_3,
            self::TBL_SUBJECT_NAME_TEAM_4,
            self::TBL_SUBJECT_NAME_TEAM_5
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
            case self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_1:
            case self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_2:
            case self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_3:
            case self::TBL_SUBJECT_NAME_FOREIGN_LANGUAGE_4:
                $Data = array();
                if($tblSubjectCategory = Subject::useService()->getCategoryByIdentifier('FOREIGNLANGUAGE')){
                    if(($tblSubjectList = Subject::useService()->getSubjectAllByCategory($tblSubjectCategory))){
                        foreach($tblSubjectList as $tblSubject){
                            $Data[] = $tblSubject->getName();
                        }
                    }
                }
                $Field = parent::getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_SUBJECT_NAME_PROFILE:
                $Data = array();
                if($tblSubjectCategory = Subject::useService()->getCategoryByIdentifier('PROFILE')){
                    if(($tblSubjectList = Subject::useService()->getSubjectAllByCategory($tblSubjectCategory))){
                        foreach($tblSubjectList as $tblSubject){
                            $Data[] = $tblSubject->getName();
                        }
                    }
                }
                $Field = parent::getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_SUBJECT_NAME_RELIGION:
                $Data = array();
                if($tblSubjectCategory = Subject::useService()->getCategoryByIdentifier('RELIGION')){
                    if(($tblSubjectList = Subject::useService()->getSubjectAllByCategory($tblSubjectCategory))){
                        foreach($tblSubjectList as $tblSubject){
                            $Data[] = $tblSubject->getName();
                        }
                    }
                }
                $Field = parent::getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_SUBJECT_NAME_ORIENTATION:
                $Data = array();
                if($tblSubjectCategory = Subject::useService()->getCategoryByName('Neigungskurs')){
                    if(($tblSubjectList = Subject::useService()->getSubjectAllByCategory($tblSubjectCategory))){
                        foreach($tblSubjectList as $tblSubject){
                            $Data[] = $tblSubject->getName();
                        }
                    }
                }
                $Field = parent::getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_SUBJECT_NAME_ELECTIVE_1:
            case self::TBL_SUBJECT_NAME_ELECTIVE_2:
            case self::TBL_SUBJECT_NAME_ELECTIVE_3:
            case self::TBL_SUBJECT_NAME_ELECTIVE_4:
                $Data = array();
                if($tblSubjectCategory = Subject::useService()->getCategoryByName('Wahlfach')){
                    if(($tblSubjectList = Subject::useService()->getSubjectAllByCategory($tblSubjectCategory))){
                        foreach($tblSubjectList as $tblSubject){
                            $Data[] = $tblSubject->getName();
                        }
                    }
                }
                $Field = parent::getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
