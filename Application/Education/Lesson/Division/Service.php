<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Filter\Filter;
use SPHERE\Application\Education\Lesson\Division\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionCustody;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroupFilter;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewSubjectTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Setup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewDivision[]
     */
    public function viewDivision()
    {

        return ( new Data($this->getBinding()) )->viewDivision();
    }

    /**
     * @return false|ViewDivisionStudent[]
     */
    public function viewDivisionStudent()
    {

        return ( new Data($this->getBinding()) )->viewDivisionStudent();
    }

    /**
     * @return false|ViewDivisionTeacher[]
     */
    public function viewDivisionTeacher()
    {

        return ( new Data($this->getBinding()) )->viewDivisionTeacher();
    }

    /**
     * @return false|ViewSubjectTeacher[]
     */
    public function viewSubjectTeacher()
    {

        return ( new Data($this->getBinding()) )->viewSubjectTeacher();
    }

    /**
     * @return false|ViewDivisionSubject[]
     */
    public function viewDivisionSubject()
    {

        return ( new Data($this->getBinding()) )->viewDivisionSubject();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        return (new Data($this->getBinding()))->getLevelAll();
    }

    /**
     * @return bool|TblDivision[]
     */
    public function getDivisionAll()
    {

        return (new Data($this->getBinding()))->getDivisionAll();
    }

    /**
     * @param IFormInterface $Form
     * @param                $Level
     * @param                $Division
     *
     * @return IFormInterface|string
     */
    public function createLevelDivision(
        IFormInterface $Form,
        $Level,
        $Division
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Level && null === $Division) {
            return $Form;
        }

        $Error = false;

        if (!(Type::useService()->getTypeById($Level['Type']))) {
            $Form->setError('Level[Type]', 'Schulart erforderlich! Bitte auswählen');
            $Error = true;
        }

        if (!($tblCompany = Company::useService()->getCompanyById($Division['Company']))) {
            $Form->setError('Division[Company]', 'Schule erforderlich! Bitte auswählen');
            $Error = true;
        }

        // Year
        if (!isset($Division['Year']) || empty($Division['Year'])) {
            $Form->setError('Division[Year]', 'Jahr erforderlich! Bitte zuerst einpflegen');
            $Error = true;
        }
        if ($Error) {
            return $Form;
        }
        $tblYear = Term::useService()->getYearById($Division['Year']);
        if (empty($tblYear)) {
            $Form->setError('Division[Year]', 'Schuljahr nicht gefunden');
            $Error = true;
        }

        // Level
        if (isset($Level['Name'])) {
            if (is_numeric($Level['Name'])) {
                $position = strpos($Level['Name'], '0');
                if ($position === 0) {
                    $Form->setError('Level[Name]', 'Bitte geben Sie eine Zahl ohne führende "0" ein');
                    $Error = true;
                }
            } else {
                $Form->setError('Level[Name]', 'Bitte geben Sie eine Zahl ein');
                $Error = true;
            }
        }

        // Create
        if (!$Error) {
            // Level
            $tblType = Type::useService()->getTypeById($Level['Type']);
            $tblLevel = (new Data($this->getBinding()))->createLevel($tblType, $Level['Name']);

            if ($this->checkDivisionExists($tblYear, $Division['Name'], $tblLevel)
            ) {
                $Form->setError('Division[Name]', 'Name wird in der Klassenstufe/Jahrgang bereits verwendet');
            } else {

                (new Data($this->getBinding()))->createDivision(
                    $tblYear, $tblLevel, $Division['Name'], $Division['Description'], $tblCompany ? $tblCompany : null
                );
                return new Success('Die Klassengruppe wurde erfolgreich hinzugefügt')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_SUCCESS);
            }
        }

        return $Form;
    }

    /**
     * @param TblYear $tblYear
     * @param string $Name
     * @param TblLevel|null $tblLevel
     *
     * @return bool
     */
    public function checkDivisionExists(TblYear $tblYear, $Name, TblLevel $tblLevel = null)
    {

        return (new Data($this->getBinding()))->checkDivisionExists($tblYear, $Name, $tblLevel);
    }

    /**
     * @param IFormInterface $Form
     * @param null|string $Year
     *
     * @return IFormInterface|Redirect
     */
    public function selectYear(IFormInterface $Form, $Year)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Year) {
            return $Form;
        }

        $Error = false;

        if (isset($Year) && empty($Year)) {
            $Form->setError('Year', 'Schuljahr benötigt!');
            $Error = true;
        }
        if (!$Error) {
            return new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_SUCCESS, array('Year' => $Year));
        }

        return $Form;
    }

    /**
     * @param TblType $tblType
     * @param string $Name
     *
     * @return bool|TblLevel
     */
    public function checkLevelExists(TblType $tblType, $Name)
    {

        return (new Data($this->getBinding()))->checkLevelExists($tblType, $Name);
    }

    /**
     * @param TblType $tblType
     * @param string $Name
     * @param string $Description
     * @param bool $Checked
     *
     * @return bool|TblLevel
     */
    public function insertLevel(TblType $tblType, $Name, $Description = '', $Checked = false)
    {

        return (new Data($this->getBinding()))->createLevel($tblType, $Name, $Description, $Checked);
    }

    /**
     * @param string        $Name
     * @param TblLevel|null $tblLevel
     * @param TblYear       $tblYear
     *
     * @return false|TblDivision[]
     */
    public function getDivisionByDivisionNameAndLevelAndYear($Name, TblLevel $tblLevel = null, TblYear $tblYear)
    {

        if ($tblYear && $tblLevel && $Name != '') {
            $tblDivisionList = array();
            if (( $tblDivision = ( new Data($this->getBinding()) )->getDivisionByDivisionNameAndLevelAndYear($Name, $tblLevel, $tblYear) )) {
                $tblDivisionList[] = $tblDivision;
                return $tblDivisionList;
            } else {
                return false;
            }

        } elseif ($tblYear && ( $tblLevel === null ) && $Name != '') {
            return ( new Data($this->getBinding()) )->getDivisionByDivisionNameAndYear($Name, $tblYear);
        } elseif ($tblYear && $tblLevel) {
            return ( new Data($this->getBinding()) )->getDivisionByLevelAndYear($tblLevel, $tblYear);
        } else {
            return false;
        }
    }

    /**
     * @param int $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById($Id)
    {

        return (new Data($this->getBinding()))->getLevelById($Id);
    }

    /**
     * @param string $Name
     * @param TblType|null $tblType
     *
     * @return bool|TblLevel[]
     */
    public function getLevelAllByName($Name, TblType $tblType = null)
    {

        return (new Data($this->getBinding()))->getLevelAllByName($Name, $tblType);
    }

    /**
     * @param TblLevel $tblLevel
     * used LevelName to find same Level range
     * @param TblYear  $tblYear
     *
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionAllByLevelNameAndYear(TblLevel $tblLevel, TblYear $tblYear)
    {

        $tblDivisionList = array();
        $tblLevelList = Division::useService()->getLevelAllByName($tblLevel->getName());
        if ($tblLevelList && $tblYear) {
            array_walk($tblLevelList, function ($tblLevel) use (&$tblDivisionList, $tblYear) {
                $DivisionArray = Division::useService()->getDivisionAllByLevelAndYear($tblLevel, $tblYear);
                if ($DivisionArray) {
                    /** @var TblDivision $tblDivision */
                    foreach ($DivisionArray as $tblDivision) {
                        $tblDivisionList[] = $tblDivision;
                    }
                }
            });
        }

        return ( !empty($tblDivisionList) ? $tblDivisionList : false );
    }

    /**
     * used LevelName to find same Level range
     *
     * @param int|string $tblLevelName
     * @param TblYear|null $tblYear
     * @param TblType|null $tblType
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionAllByLevelName($tblLevelName, TblYear $tblYear = null, TblType $tblType = null)
    {

        $tblDivisionList = array();
        $tblLevelList = Division::useService()->getLevelAllByName($tblLevelName, $tblType);
        if ($tblLevelList) {
            array_walk($tblLevelList, function ($tblLevel) use (&$tblDivisionList, $tblYear) {
                $tblDivisionArray = Division::useService()->getDivisionAllByLevel($tblLevel);
                if ($tblDivisionArray) {
                    /** @var TblDivision $tblDivision */
                    foreach ($tblDivisionArray as $tblDivision) {
                        if ($tblYear
                            && ($tblDivisionYear = $tblDivision->getServiceTblYear())
                            && $tblYear->getId() != $tblDivisionYear->getId()
                        ) {
                            continue;
                        }

                        $tblDivisionList[] = $tblDivision;
                    }
                }
            });
        }

        return (!empty($tblDivisionList) ? $tblDivisionList : false);
    }

    /**
     * @param TblLevel $tblLevel
     * @param TblYear  $tblYear
     *
     * @return false|TblDivision[]
     */
    public function getDivisionAllByLevelAndYear(TblLevel $tblLevel, TblYear $tblYear)
    {

        return ( new Data($this->getBinding()) )->getDivisionAllByLevelAndYear($tblLevel, $tblYear);
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return false|TblDivision[]
     */
    public function getDivisionAllByLevel(TblLevel $tblLevel)
    {

        return (new Data($this->getBinding()))->getDivisionAllByLevel($tblLevel);
    }

    /**
     * @param TblDivision[] $tblDivisionList
     *
     * @return array|bool
     */
    public function getPersonAllByDivisionList($tblDivisionList)
    {

        $tblPersonList = array();
        if (!empty($tblDivisionList)) {
            foreach ($tblDivisionList as $tblDivision) {
                $tblPersonDivisionList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonDivisionList) {
                    $tblPersonList = array_merge($tblPersonList, $tblPersonDivisionList);
                }
            }
        }
        return ( !empty($tblPersonList) ? $tblPersonList : false );
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionTeacher
     */
    public function getDivisionTeacherByDivisionAndTeacher(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionCustody
     */
    public function getDivisionCustodyByDivisionAndPerson(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionCustodyByDivisionAndPerson($tblDivision, $tblPerson);
    }

    /**
     * @param TblYear $tblYear
     * @param TblLevel $tblLevel
     * @param string $Name
     * @param string $Description
     * @param TblCompany|null $tblCompany
     *
     * @return null|TblDivision
     */
    public function insertDivision(TblYear $tblYear, TblLevel $tblLevel, $Name, $Description = '', TblCompany $tblCompany = null)
    {

        return (new Data($this->getBinding()))->createDivision($tblYear, $tblLevel, $Name, $Description, $tblCompany);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeStudentToDivision(TblDivision $tblDivision, TblPerson $tblPerson, $IsSoftRemove = false)
    {

        $tblStudentSubjectList = (new Data($this->getBinding()))->getSubjectStudentByPerson($tblPerson);
        if ($tblStudentSubjectList) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                // SSW-603 nur von dieser Klasse entfernen
                if (($tblDivisionSubject = $tblStudentSubject->getTblDivisionSubject())
                    && ($tblDivisionTemp = $tblDivisionSubject->getTblDivision())
                    && $tblDivisionTemp->getId() == $tblDivision->getId()
                ) {
                    (new Data($this->getBinding()))->removeSubjectStudent($tblStudentSubject, $IsSoftRemove);
                }
            }
        }

        return (new Data($this->getBinding()))->removeStudentToDivision($tblDivision, $tblPerson, $IsSoftRemove);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return TblDivisionTeacher
     */
    public function insertDivisionTeacher(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addDivisionTeacher($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return TblDivisionStudent
     */
    public function addStudentToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $orderMax = $this->getDivisionStudentSortOrderMax($tblDivision);
        if ($orderMax == 0) {
            $orderMax = $this->sortDivisionStudentByProperty($tblDivision, 'LastFirstName', new Sorter\StringGermanOrderSorter());
        }
        $SortOrder = $orderMax + 1;
        return (new Data($this->getBinding()))->addDivisionStudent($tblDivision, $tblPerson, $SortOrder);
    }

    /**
     * @param TblDivision $tblDivision
     * @param string $Property
     * @param null $Sorter
     * @param int $Order
     *
     * @return int
     */
    public function sortDivisionStudentByProperty(
        TblDivision $tblDivision,
        $Property = 'LastFirstName',
        $Sorter = null,
        $Order = Sorter::ORDER_ASC
    ) {
        $tblStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
        if ($tblStudentAll) {

            $tblStudentAll = $this->getSorter($tblStudentAll)->sortObjectBy($Property, $Sorter,
                $Order);
            $count = 1;
            foreach ($tblStudentAll as $tblPerson) {
                if (($tblDivisionStudent = $this->getDivisionStudentByDivisionAndPerson(
                    $tblDivision, $tblPerson))
                ) {
                    Division::useService()->updateDivisionStudentSortOrder($tblDivisionStudent, $count++);
                }
            }

            return $count;
        }

        return 0;
    }

    /**
     * @param TblDivision $tblDivision
     * @param string      $Property
     * @param null        $Sorter
     * @param int         $Order
     *
     * @return int
     */
    public function sortDivisionStudentWithGenderByProperty(
        TblDivision $tblDivision,
        $Property = 'LastFirstName',
        $Sorter = null,
        $Order = Sorter::ORDER_ASC
    ) {
        $tblStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
        if ($tblStudentAll) {
            $maleList = array();
            $femaleList = array();
            $otherList = array();
            foreach ($tblStudentAll as $tblStudent) {
                if (($tblCommon = $tblStudent->getCommon())) {
                    if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                        if (($tblGender = $tblCommonBirthDates->getTblCommonGender())) {
                            if ($tblGender->getName() == 'Männlich') {
                                $maleList[] = $tblStudent;
                                continue;
                            } elseif ($tblGender->getName() == 'Weiblich') {
                                $femaleList[] = $tblStudent;
                                continue;
                            }
                        }
                    }
                }
                $otherList[] = $tblStudent;
            }
            if (!empty($maleList)) {
                $maleList = $this->getSorter($maleList)->sortObjectBy($Property, $Sorter, $Order);
            }
            if (!empty($femaleList)) {
                $femaleList = $this->getSorter($femaleList)->sortObjectBy($Property, $Sorter, $Order);
            }
            if (!empty($otherList)) {
                $otherList = $this->getSorter($otherList)->sortObjectBy($Property, $Sorter, $Order);
            }

            $IsMaleSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Sort', 'SortMaleFirst');
            if ($IsMaleSetting) {
                if ($IsMaleSetting->getValue() == true) {
                    $tblStudentAll = array_merge($maleList, $femaleList, $otherList);
                } else {
                    $tblStudentAll = array_merge($femaleList, $maleList, $otherList);
                }
            } else {
                // sort order as default
                $tblStudentAll = array_merge($maleList, $femaleList, $otherList);
            }

            $count = 1;
            foreach ($tblStudentAll as $tblPerson) {
                if (($tblDivisionStudent = $this->getDivisionStudentByDivisionAndPerson(
                    $tblDivision, $tblPerson))
                ) {
                    Division::useService()->updateDivisionStudentSortOrder($tblDivisionStudent, $count++);
                }
            }

            return $count;
        }

        return 0;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public
    function removeTeacherToDivision(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        $IsSoftRemove = false
    ) {

        return (new Data($this->getBinding()))->removeTeacherToDivision($tblDivision, $tblPerson, $IsSoftRemove);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removePersonToDivision(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        $IsSoftRemove = false
    ) {

        return (new Data($this->getBinding()))->removePersonToDivision($tblDivision, $tblPerson, $IsSoftRemove);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return string
     */
    public
    function removeSubjectToDivision(
        TblDivision $tblDivision,
        TblSubject $tblSubject
    ) {

        $tblDivisionSubjectList = $this->getDivisionSubjectByDivision($tblDivision);
        if ($tblDivisionSubjectList) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                if ($tblDivisionSubject->getServiceTblSubject()) {
                    if ($tblDivisionSubject->getServiceTblSubject()->getId() === $tblSubject->getId()) {
                        (new Data($this->getBinding()))->removeSubjectStudentByDivisionSubject($tblDivisionSubject);
                        (new Data($this->getBinding()))->removeSubjectTeacherByDivisionSubject($tblDivisionSubject);
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            (new Data($this->getBinding()))->removeSubjectGroup($tblDivisionSubject->getTblSubjectGroup());
                        }
                    }
                }
            }
        }
        return (new Data($this->getBinding()))->removeSubjectToDivision($tblDivision, $tblSubject);
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool        $isListWithSubjectGroup
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectByDivision(TblDivision $tblDivision, $isListWithSubjectGroup = true)
    {

        if ($isListWithSubjectGroup) {
            return (new Data($this->getBinding()))->getDivisionSubjectByDivision($tblDivision);

        } else {
            $resultList = array();
            $tblDivisionSubjectList = (new Data($this->getBinding()))->getDivisionSubjectByDivision($tblDivision);
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                    if (!$tblDivisionSubject->getTblSubjectGroup()) {
                        $resultList[] = $tblDivisionSubject;
                    }
                }
            }
            return (!empty($resultList) ? $resultList : false);
        }
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        // Fachlehrer entfernen
       (new Data($this->getBinding()))->removeSubjectTeacherByDivisionSubject($tblDivisionSubject);
       // Schüler entfernen
       (new Data($this->getBinding()))->removeSubjectStudentByDivisionSubject($tblDivisionSubject);

        return (new Data($this->getBinding()))->removeDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeSubjectGroup(
        TblSubjectGroup $tblSubjectGroup,
        TblDivisionSubject $tblDivisionSubject
    ) {

        if ($tblDivisionSubject->getTblSubjectGroup()->getId() === $tblSubjectGroup->getId()) {
            (new Data($this->getBinding()))->removeSubjectStudentByDivisionSubject($tblDivisionSubject);
            (new Data($this->getBinding()))->removeSubjectTeacherByDivisionSubject($tblDivisionSubject);
            (new Data($this->getBinding()))->removeSubjectGroupFilterByDivisionSubject($tblDivisionSubject);
        }

        return (new Data($this->getBinding()))->removeSubjectGroup($tblSubjectGroup);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param             $Description
     *
     * @return null|object|TblDivisionTeacher
     */
    public
    function addDivisionTeacher(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        $Description
    ) {

        return (new Data($this->getBinding()))->addDivisionTeacher($tblDivision, $tblPerson, $Description);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param             $Description
     *
     * @return null|TblDivisionCustody
     */
    public function addDivisionCustody(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        $Description
    ) {

        return (new Data($this->getBinding()))->addDivisionCustody($tblDivision, $tblPerson, $Description);

    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return null|object|TblDivisionSubject
     */
    public function addSubjectToDivision(
        TblDivision $tblDivision,
        TblSubject $tblSubject
    ) {

        return (new Data($this->getBinding()))->addDivisionSubject($tblDivision, $tblSubject);
    }

    /**
     * @param IFormInterface $Form
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param $Group
     * @param $DivisionSubjectId
     * @param boolean $IsSekTwo
     *
     * @return IFormInterface|string
     */
    public function addSubjectToDivisionWithGroup(
        IFormInterface $Form,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        $Group,
        $DivisionSubjectId,
        $IsSekTwo
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }
        $Error = false;
        if (isset($Group['Name']) && empty($Group['Name'])) {
            $Form->setError('Group[Name]', 'Bitte geben Sie einen Namen für die Gruppe an');
            $Error = true;
        } else {
            if($this->getSubjectGroupByNameAndDivisionAndSubject($Group['Name'], $tblDivision, $tblSubject)){
                $Form->setError('Group[Name]', 'Dieser Gruppenname existiert bereits');
                $Error = true;
            }
        }

        if (!$Error) {
            $tblGroup = (new Data($this->getBinding()))->createSubjectGroup($Group['Name'], $Group['Description'],
                $IsSekTwo ? isset($Group['IsAdvancedCourse']) : null);
            if ($tblGroup) {
                // Prüfung ob das Fach bewertet wird
                if (($tblDivisionSubject = $this->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivision, $tblSubject, null))) {
                    $hasGrading = $tblDivisionSubject->getHasGrading();
                } else {
                    $hasGrading = false;
                }

                if ((new Data($this->getBinding()))->addDivisionSubject($tblDivision, $tblSubject, $tblGroup, $hasGrading)) {
                    return new Success('Die Gruppe ' . new Bold($Group['Name']) . ' wurde erfolgreich angelegt')
                    . new Redirect('/Education/Lesson/Division/SubjectGroup/Add', Redirect::TIMEOUT_SUCCESS, array(
                        'Id' => $tblDivision->getId(),
                        'DivisionSubjectId' => $DivisionSubjectId
                    ));
                } else {
                    return new Danger('Die Gruppe ' . new Bold($Group['Name']) . ' wurde nicht angelegt')
                    . new Redirect('/Education/Lesson/Division/SubjectGroup/Add', Redirect::TIMEOUT_ERROR, array(
                        'Id' => $tblDivision->getId(),
                        'DivisionSubjectId' => $DivisionSubjectId
                    ));
                }

            }
        }
        return $Form;
    }

    /**
     * @param              $Name
     * @param string       $Description
     * @param null|boolean $IsAdvancedCourse
     *
     * @return TblSubjectGroup
     */
    public function addSubjectGroup($Name, $Description = '', $IsAdvancedCourse = null)
    {

        return (new Data($this->getBinding()))->createSubjectGroup($Name, $Description, $IsAdvancedCourse);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject  $tblSubject
     * @param string      $SubjectGroup
     * @param bool        $IsIntensiveCourse
     *
     * @return bool|null|object|TblDivisionSubject
     */
    public function addSubjectToDivisionWithGroupImport(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        $SubjectGroup,
        $IsIntensiveCourse = false
    ) {

        $tblSubjectGroup = Division::useService()->getSubjectGroupByNameAndDivisionAndSubject($SubjectGroup,
            $tblDivision, $tblSubject);
        if (!$tblSubjectGroup) {
            $tblSubjectGroup = Division::useService()->addSubjectGroup($SubjectGroup, '', $IsIntensiveCourse);
        }

        if ($tblSubjectGroup) {
            return ( new Data($this->getBinding()) )->addDivisionSubject($tblDivision, $tblSubject, $tblSubjectGroup);
        }
        return false;
    }

    /**
     * @param int $Id
     * @param bool $IsForced
     *
     * @return bool|TblDivision
     */
    public function getDivisionById(
        $Id,
        $IsForced = false
    ) {

        return (new Data($this->getBinding()))->getDivisionById($Id, $IsForced);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivisionSubject
     */
    public
    function getDivisionSubjectById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getDivisionSubjectById($Id);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectStudent[]
     */
    public
    function getSubjectStudentByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        return (new Data($this->getBinding()))->getSubjectStudentByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param bool $withInActive
     *
     * @return bool|TblPerson[]
     */
    public function getStudentByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject,
        $withInActive = false
    ) {

        return (new Data($this->getBinding()))->getStudentByDivisionSubject($tblDivisionSubject, $withInActive);
    }

    /**
     * @param TblSubjectStudent $tblSubjectStudent
     * @param bool $IsSoftRemove
     *
     * @return string
     */
    public function removeSubjectStudent(
        TblSubjectStudent $tblSubjectStudent,
        $IsSoftRemove = false
    ) {

        return (new Data($this->getBinding()))->removeSubjectStudent($tblSubjectStudent, $IsSoftRemove);
    }

    /**
     * @param TblSubjectStudent[] $tblSubjectStudentList
     *
     * @return string
     */
    public function removeSubjectStudentBulk(
        $tblSubjectStudentList = array()
    ) {

        return (new Data($this->getBinding()))->removeSubjectStudentBulk($tblSubjectStudentList);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivisionStudent
     */
    public
    function getDivisionStudentById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getDivisionStudentById($Id);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     *
     * @return TblSubjectTeacher
     */
    public
    function addSubjectTeacher(
        TblDivisionSubject $tblDivisionSubject,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->addSubjectTeacher($tblDivisionSubject, $tblPerson);
    }

    /**
     * @param $SubjectTeacherList
     *
     * @return bool
     */
    public function addSubjectTeacherList($SubjectTeacherList)
    {

        return ( new Data($this->getBinding()) )->addSubjectTeacherList($SubjectTeacherList);
    }

    /**
     * @param array $SubjectStudentList [tblPerson => $tblPerson, tblDivisionSubject => $tblDivisionSubject]
     *
     * @return bool
     */
    public function addSubjectStudentList($SubjectStudentList)
    {

        return (new Data($this->getBinding()))->addSubjectStudentList($SubjectStudentList);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectTeacher[]
     */
    public
    function getSubjectTeacherByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        return (new Data($this->getBinding()))->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblSubjectTeacher $tblSubjectTeacher
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeSubjectTeacher(
        TblSubjectTeacher $tblSubjectTeacher,
        $IsSoftRemove = false
    ) {

        return (new Data($this->getBinding()))->removeSubjectTeacher($tblSubjectTeacher, $IsSoftRemove);
    }

    /**
     * @param TblSubjectTeacher[] $tblSubjectTeacherList
     *
     * @return bool
     */
    public function removeSubjectTeacherList($tblSubjectTeacherList)
    {

        return ( new Data($this->getBinding()) )->removeSubjectTeacherList($tblSubjectTeacherList);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param null|integer $SortOrder
     *
     * @return TblDivisionStudent
     */
    public
    function insertDivisionStudent(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        $SortOrder = null
    ) {

        return (new Data($this->getBinding()))->addDivisionStudent($tblDivision, $tblPerson, $SortOrder);
    }

    /**
     * @param IFormInterface $Form
     * @param null|array $Division
     * @param int $Id
     *
     * @return IFormInterface|string
     */
    public
    function changeDivision(
        IFormInterface $Form,
        $Division,
        $Id
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Division) {
            return $Form;
        }

        $Error = false;

        // School
        if (!($tblCompany = Company::useService()->getCompanyById($Division['Company']))) {
            $Form->setError('Division[Company]', 'Schule erforderlich! Bitte auswählen');
            $Error = true;
        }

        // auch leere Strings sollen gespeichert werden können
//        if (isset($Division['Name']) && empty($Division['Name'])
//        ) {
//            $Form->setError('Division[Name]', 'Bitte geben sie einen Namen an');
//            $Error = true;
//        }
//        else {
//            $tblDivisionTest =
//                Division::useService()->getDivisionByGroupAndLevelAndYear($Division['Name'], $Division['Level'], $Division['Year']);
//            if ($tblDivisionTest) {
//                $Form->setError('Division[Name]', 'Name schon vergeben');
//                $Error = true;
//            }
//        }

        if (!$Error) {
            $tblDivision = Division::useService()->getDivisionById($Id);
            if ($tblDivision) {
//                $tblYear = Term::useService()->getYearById($Division['Year']);
//                $tblLevel = $this->getLevelById($Division['Level']);
                if ((new Data($this->getBinding()))->updateDivision(
                    $tblDivision, trim($Division['Name']), $Division['Description'], $tblCompany ? $tblCompany : null
                )
                ) {
                    return new Success('Die Klasse wurde erfolgreich geändert')
                    . new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_SUCCESS);
                } else {
                    return new Danger('Die Klasse konnte nicht geändert werden')
                    . new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
                }
            } else {
                return new Danger('Die Klassen wurde nicht gefunden')
                . new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Form;
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectStudent
     */
    public
    function getSubjectStudentById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getSubjectStudentById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectTeacher
     */
    public
    function getSubjectTeacherById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getSubjectTeacherById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getSubjectStudentByPerson($tblPerson);
    }

    /**
     * @param TblPerson   $tblPerson
     *
     * @param TblDivision $tblDivision
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByPersonAndDivision(TblPerson $tblPerson, TblDivision $tblDivision)
    {

        $resultList = array();
        $tblSubjectStudentList = (new Data($this->getBinding()))->getSubjectStudentByPerson($tblPerson);
        /** @var TblSubjectStudent $tblSubjectStudent */
        if ($tblSubjectStudentList) {
            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                if ($tblDivisionSubject = $tblSubjectStudent->getTblDivisionSubject()) {
                    if (($tblDivisionCompare = $tblDivisionSubject->getTblDivision()) && $tblDivisionCompare->getId() == $tblDivision->getId()) {
                        $resultList[] = $tblSubjectStudent;
                    }
                }
            }
        }

        return (!empty($resultList) ? $resultList : false);
    }

    /**
     * @param IFormInterface $Form
     * @param $Group
     * @param $Id
     * @param $DivisionId
     * @param $DivisionSubjectId
     * @param boolean $IsSekTwo
     * @return IFormInterface|string
     */
    public function changeSubjectGroup(
        IFormInterface $Form,
        $Group,
        $Id,
        $DivisionId,
        $DivisionSubjectId,
        $IsSekTwo
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        $tblSubjectGroup = Division::useService()->getSubjectGroupById($Id);
        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);

        if (isset($Group['Name']) && empty($Group['Name'])) {
            $Form->setError('Group[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } else {
            if($tblDivisionSubject && ($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())){
                if(($tblSubjectGroupFind = $this->getSubjectGroupByNameAndDivisionAndSubject($Group['Name'], $tblDivision, $tblSubject))){
                    if($tblSubjectGroupFind->getId() != $tblSubjectGroup->getId()){
                        $Form->setError('Group[Name]', 'Dieser Gruppenname existiert bereits');
                        $Error = true;
                    }
                }
            }
        }

        if (!$Error) {

            if ($tblSubjectGroup) {
                if ((new Data($this->getBinding()))->updateSubjectGroup(
                    $tblSubjectGroup, $Group['Name'], $Group['Description'], $IsSekTwo ? isset($Group['IsAdvancedCourse']) : null
                )
                ) {
                    return new Success('Die Gruppe wurde erfolgreich geändert')
                    . new Redirect('/Education/Lesson/Division/SubjectGroup/Add', Redirect::TIMEOUT_SUCCESS, array(
                        'Id' => $DivisionId,
                        'DivisionSubjectId' => $DivisionSubjectId
                    ));
                } else {
                    return new Danger('Die Gruppe konnte nicht geändert werden')
                    . new Redirect('/Education/Lesson/Division/SubjectGroup/Add', Redirect::TIMEOUT_ERROR, array(
                        'Id' => $DivisionId,
                        'DivisionSubjectId' => $DivisionSubjectId
                    ));
                }
            } else {
                return new Danger('Die Gruppe wurde nicht gefunden')
                . new Redirect('/Education/Lesson/Division/SubjectGroup/Add', Redirect::TIMEOUT_ERROR, array(
                    'Id' => $DivisionId,
                    'DivisionSubjectId' => $DivisionSubjectId
                ));
            }
        }
        return $Form;
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectGroup
     */
    public
    function getSubjectGroupById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getSubjectGroupById($Id);
    }

    /**
     * @return false|TblSubjectGroup[]
     */
    public function getSubjectGroupAll()
    {

        return ( new Data($this->getBinding()) )->getSubjectGroupAll();
    }

    /**
     * @param             $Name
     * @param TblDivision $tblDivision
     * @param TblSubject  $tblSubject
     *
     * @return bool|TblSubjectGroup
     */
    public function getSubjectGroupByNameAndDivisionAndSubject($Name, TblDivision $tblDivision, TblSubject $tblSubject)
    {

        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject($tblDivision, $tblSubject);
        if ($tblDivisionSubjectList) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup();
                if ($tblSubjectGroup && $tblSubjectGroup->getName() == $Name) {
                    return $tblSubjectGroup;
                }
            }
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool
     */
    public
    function destroyDivision(
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->destroyDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool $withInActive
     *
     * @return bool|TblPerson[]
     */
    public function getStudentAllByDivision(
        TblDivision $tblDivision,
        $withInActive = false
    ) {

        return (new Data($this->getBinding()))->getStudentAllByDivision($tblDivision, $withInActive);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public
    function getTeacherAllByDivision(
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->getTeacherAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public
    function getCustodyAllByDivision(
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->getCustodyAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblSubject[]
     */
    public
    function getSubjectAllByDivision(
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->getSubjectAllByDivision($tblDivision);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblPerson[]
     */
    public
    function getTeacherAllByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        return (new Data($this->getBinding()))->getTeacherAllByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return string
     */
    public
    function destroyLevel(
        TblLevel $tblLevel
    ) {

        if (null === $tblLevel) {
            return '';
        }
        $Error = false;
        if ($this->getDivisionByLevel($tblLevel)) {
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->destroyLevel($tblLevel)) {
                return new Success('Die Klassenstufe wurde erfolgreich gelöscht')
                . new Redirect('/Education/Lesson/Division/Create/LevelDivision', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Die Klassenstufe konnte nicht gelöscht werden')
                . new Redirect('/Education/Lesson/Division/Create/LevelDivision', Redirect::TIMEOUT_ERROR);
            }
        }
        return new Danger('Die Klassenstufe enthält Klassengruppen!')
        . new Redirect('/Education/Lesson/Division/Create/LevelDivision', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return bool|TblDivision[]
     */
    public
    function getDivisionByLevel(
        TblLevel $tblLevel
    ) {

        return (new Data($this->getBinding()))->getDivisionByLevel($tblLevel);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblDivision[]
     */
    public
    function getDivisionByYear(
        TblYear $tblYear
    ) {

        return (new Data($this->getBinding()))->getDivisionByYear($tblYear);
    }

    /**
     * return Division without LevelName (Level->getName() != '')
     *
     * @param TblPerson $tblPerson
     * @param TblYear   $tblYear
     *
     * @return bool|TblDivision
     */
    public function getDivisionByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {

        $tblDivisionList = $this->getDivisionByYear($tblYear);
        if ($tblDivisionList) {
            foreach ($tblDivisionList as $tblDivision) {
                $tblLevel = $tblDivision->getTblLevel();
                if ($tblLevel->getName() != '') {
                    $DivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivision,
                        $tblPerson);
                    if ($DivisionStudent) {
                        return $tblDivision;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblDivisionStudent[]
     */
    public function getDivisionStudentAllByPerson(
        TblPerson $tblPerson, $isForced = false
    ) {

        return (new Data($this->getBinding()))->getDivisionStudentAllByPerson($tblPerson, $isForced);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionStudentAllByDivision(
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->countDivisionStudentAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public
    function countDivisionTeacherAllByDivision(
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->countDivisionTeacherAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public
    function countDivisionCustodyAllByDivision(
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->countDivisionCustodyAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public
    function countDivisionSubjectAllByDivision(
        TblDivision $tblDivision
    ) {

        $Sum = (new Data($this->getBinding()))->countDivisionSubjectAllByDivision($tblDivision);
        $Sub = (new Data($this->getBinding()))->countDivisionSubjectGroupByDivision($tblDivision);
        return ($Sum - $Sub);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public
    function countDivisionSubjectForSubjectTeacherByDivision(
        TblDivision $tblDivision
    ) {

        $DivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
        $SubjectUsedCount = 0;
        if ($DivisionSubjectList) {
            foreach ($DivisionSubjectList as $DivisionSubject) {

                if (!$DivisionSubject->getTblSubjectGroup()) {
                    if ($DivisionSubject->getServiceTblSubject()) {
                        if (Division::useService()->getSubjectTeacherByDivisionSubject($DivisionSubject)) {
                            // One Teacher for Subject without Groups (Ok)
                            // Teacher is able to teach all Groups of this Subject
                        } else {
                            $SubjectUsedCount++;
                            $tblDivisionSubjectActiveList = Division::useService()
                                ->getDivisionSubjectBySubjectAndDivision($DivisionSubject->getServiceTblSubject(),
                                    $tblDivision);
                            // Found more than 1 Subject? (Subject without Group + Subject with Group)
                            if ($tblDivisionSubjectActiveList && count($tblDivisionSubjectActiveList) > 1) {
                                /**@var TblDivisionSubject $tblDivisionSubjectActive */
                                $TeacherGroup = array();
                                foreach ($tblDivisionSubjectActiveList as $tblDivisionSubjectActive) {
                                    $SubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectActive);
                                    // Found Teacher in Subject with Group?
                                    if ($SubjectTeacherList) {
                                        $TeacherGroup[] = true;
                                    }
                                }
                                // Count Subject's - (Added Count + Subject without Group) - Found Teacher's in Group's
                                if ((count($tblDivisionSubjectActiveList) - 1) == count($TeacherGroup)) {
                                    $SubjectUsedCount--;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $SubjectUsedCount;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public
    function countDivisionSubjectGroupTeacherByDivision(
        TblDivision $tblDivision
    ) {

        $DivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
        $TeacherGroupCount = 0;
        if ($DivisionSubjectList) {
            foreach ($DivisionSubjectList as $DivisionSubject) {

                if ($DivisionSubject->getTblSubjectGroup()) {
                    $SubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($DivisionSubject);
                    if ($DivisionSubject->getServiceTblSubject()) {
                        $tblDivisionSubject = Division::useService()->getDivisionSubjectBySubjectAndDivisionWithoutGroup($DivisionSubject->getServiceTblSubject(),
                            $tblDivision);
                        if ($tblDivisionSubject) {
                            $tblSubjectTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubject);
                            if (!$SubjectTeacherList && !$tblSubjectTeacherList) {
                                $TeacherGroupCount++;
                            }
                        }
                    }
                }
            }
        }
        return $TeacherGroupCount;
    }

    /**
     * @param TblSubject $tblSubject
     * @param TblDivision $tblDivision
     *
     * @return bool|Service\Entity\TblDivisionSubject[]
     */
    public function getDivisionSubjectBySubjectAndDivision(
        TblSubject $tblSubject,
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision);
    }

    /**
     * @param TblSubject $tblSubject
     * @param TblDivision $tblDivision
     *
     * @return false|TblDivisionSubject
     */
    public
    function getDivisionSubjectBySubjectAndDivisionWithoutGroup(
        TblSubject $tblSubject,
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->getDivisionSubjectBySubjectAndDivisionWithoutGroup($tblSubject,
            $tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool|TblDivisionSubject[]
     */
    public
    function getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
        TblDivision $tblDivision,
        TblSubject $tblSubject
    ) {

        return (new Data($this->getBinding()))->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject($tblDivision,
            $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectTeacher[]
     */
    public
    function getSubjectTeacherAllByTeacher(
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getSubjectTeacherAllByTeacher($tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|TblDivisionSubject
     */
    public
    function getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivision,
            $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionTeacher[]
     */
    public
    function getDivisionTeacherAllByTeacher(
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getDivisionTeacherAllByTeacher($tblPerson);
    }

    /**
     * Alle Klassen wo die Person als Klassenlehrer oder Fachlehrer hinterlegt ist.
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivision[]
     */
    public
    function getDivisionAllByTeacher(
        TblPerson $tblPerson
    ) {

        $resultList = array();

        // DivisionTeacher
        $list = $this->getDivisionTeacherAllByTeacher($tblPerson);
        if ($list) {
            foreach ($list as $tblDivisionTeacher) {
                if ($tblDivisionTeacher->getServiceTblPerson() && $tblDivisionTeacher->getTblDivision()) {
                    $resultList[$tblDivisionTeacher->getTblDivision()->getId()] = $tblDivisionTeacher->getTblDivision();
                }
            }
        }

        // SubjectTeacher
        $list = $this->getSubjectTeacherAllByTeacher($tblPerson);
        if ($list) {
            foreach ($list as $tblSubjectTeacher) {
                if ($tblSubjectTeacher->getTblDivisionSubject()
                    && ($tblDivision = $tblSubjectTeacher->getTblDivisionSubject()->getTblDivision())
                ) {
                    $resultList[$tblDivision->getId()] = $tblDivision;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectStudent
     */
    public
    function getSubjectStudentByDivisionSubjectAndPerson(
        TblDivisionSubject $tblDivisionSubject,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubject,
            $tblPerson);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return int
     */
    public
    function countSubjectStudentByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        return (new Data($this->getBinding()))->countSubjectStudentByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param IFormInterface $Form
     * @param TblDivision    $tblDivision
     * @param                $Level
     * @param                $Division
     * @param bool           $CopyDiary
     *
     * @return IFormInterface|string
     */
    public function copyDivision(
        IFormInterface $Form,
        TblDivision $tblDivision,
        $Level,
        $Division,
        $CopyDiary = false
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Level && null === $Division) {
            return $Form;
        }

        $Error = false;

        // School
        if (!($tblCompany = Company::useService()->getCompanyById($Division['Company']))) {
            $Form->setError('Division[Company]', 'Schule erforderlich! Bitte auswählen');
            $Error = true;
        }

        // Year
        if (!isset($Division['Year']) || empty($Division['Year'])) {
            $Form->setError('Division[Year]', 'Jahr erforderlich! Bitte zuerst einpflegen');
            $Error = true;
        }
        if ($Error) {
            return $Form;
        }
        $tblYear = Term::useService()->getYearById($Division['Year']);
        if (empty($tblYear)) {
            $Form->setError('Division[Year]', 'Schuljahr nicht gefunden');
            $Error = true;
        }

        // Level
        if (isset($Level['Name'])) {
            if (is_numeric($Level['Name'])) {
                $position = strpos($Level['Name'], '0');
                if ($position === 0) {
                    $Form->setError('Level[Name]', 'Bitte geben Sie eine Zahl ohne führende "0" ein');
                    $Error = true;
                }
            } else {
                $Form->setError('Level[Name]', 'Bitte geben Sie eine Zahl ein');
                $Error = true;
            }
        } else {
            $Form->setError('Level[Name]', 'Bitte geben Sie eine Klassenstufe für die Schulart an');
            $Error = true;
        }

        // Level
        if (!$Error) {
            $tblType = Type::useService()->getTypeById($Level['Type']);
            $tblLevel = (new Data($this->getBinding()))->createLevel($tblType, $Level['Name']);
        }

        // Create
        if (!$Error) {

            if ($this->checkDivisionExists($tblYear, $Division['Name'], $tblLevel)
            ) {
                $Form->setError('Division[Name]', 'Name wird in der Klassenstufe/Jahrgang bereits verwendet');
            } else {

                $tblDivisionCopy = (new Data($this->getBinding()))->createDivision(
                    $tblYear, $tblLevel, $Division['Name'], $Division['Description'], $tblCompany ? $tblCompany : null
                );

                if ($tblDivisionCopy && $CopyDiary) {
                    Diary::useService()->addDiaryDivision($tblDivisionCopy, $tblDivision);
                }

                if ($tblDivision->getTblLevel()->getServiceTblType() && $tblLevel->getServiceTblType()
                    && $tblDivision->getTblLevel()->getServiceTblType()->getId() !== $tblLevel->getServiceTblType()->getId()
                ) {

                    $DivisionComparison = $this->getMinDivisionByLevelType($tblLevel->getServiceTblType());
                    if ($DivisionComparison) {
                        //Versuchr Fächer anderer Klasse
                        if ($this->addSubjectWithoutGroups($DivisionComparison, $tblDivisionCopy)) {
                        } else {
                            //Hinzufügen hat nicht funktioniert!
                            $this->addSubjectWithGroups($tblDivision, $tblDivisionCopy);
                        }
                    } else {
                        //Keine passenden Stufen mit Typ gefunden!
                        $this->addSubjectWithGroups($tblDivision, $tblDivisionCopy);
                    }
                } else {
                    //Typ ändert sich nicht!
                    $this->addSubjectWithGroups($tblDivision, $tblDivisionCopy);
                }

                $tblDivisionStudentList = $this->getDivisionStudentAllByDivision($tblDivision);
                if ($tblDivisionStudentList) {
                    foreach ($tblDivisionStudentList as $tblDivisionStudent){
                        $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
                        $tblPerson = $tblDivisionStudent->getServiceTblPerson();
                        $tblPersonGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                        if ($tblPersonGroupList && $StudentGroup) {
                            foreach ($tblPersonGroupList as $tblPersonGroup) {
                                if ($tblPersonGroup->getId() == $StudentGroup->getId()) {
                                    (new Data($this->getBinding()))->addDivisionStudent(
                                        $tblDivisionCopy,
                                        $tblDivisionStudent->getServiceTblPerson(),
                                        $tblDivisionStudent->getSortOrder()
                                    );
                                    break;
                                }
                            }
                        }

                    }
                }

                (new Data($this->getBinding()))->copyTeacherAllByDivision($tblDivision, $tblDivisionCopy);
                (new Data($this->getBinding()))->copyCustodyAllByDivision($tblDivision, $tblDivisionCopy);

                return new Success('Die Klassengruppe wurde erfolgreich hinzugefügt')
                . new Redirect('/Education/Lesson/Division/', Redirect::TIMEOUT_SUCCESS);
            }
        }

        return $Form;
    }

    /**
     * Take all Division from YearByNow
     *
     * @param TblType $tblType
     *
     * @return bool|TblDivision
     */
    public
    function getMinDivisionByLevelType(
        TblType $tblType
    ) {

        $DivisionList = array();
        $tblLevelList = Division::useService()->getLevelByServiceTblType($tblType);

        if ($tblLevelList) {
            foreach ($tblLevelList as $tblLevel) {
                if (!$tblLevel->getIsChecked()) {
                    $tblDivisionList = Division::useService()->getDivisionByLevel($tblLevel);
                    if ($tblDivisionList) {
                        foreach ($tblDivisionList as $tblDivision) {
                            $tblYearList = Term::useService()->getYearByNow();
                            if ($tblYearList) {
                                foreach ($tblYearList as $tblYear) {
                                    if ($tblDivision->getServiceTblYear()
                                        && $tblYear->getId() === $tblDivision->getServiceTblYear()->getId()
                                    ) {
                                        $DivisionList[] = $tblDivision;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!empty($DivisionList)) {
            $Compare = 20;
            $Result = new TblDivision();
            /** @var TblDivision $Division */
            foreach ($DivisionList as $Division) {
                if (is_numeric($Division->getTblLevel()->getName()) && $Division->getTblLevel()->getName() != '') {
                    if ((int)$Division->getTblLevel()->getName() < $Compare) {
                        $Result = $Division;
                        $Compare = (int)$Division->getTblLevel()->getName();
                    }
                }
            }
        }
        return (isset($Result)) ? $Result : false;
    }

    /**
     * @param TblType $serviceTblType
     *
     * @return bool|TblLevel[]
     */
    public
    function getLevelByServiceTblType(
        TblType $serviceTblType
    ) {

        return (new Data($this->getBinding()))->getLevelByServiceTblType($serviceTblType);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblDivision $tblDivisionCopy
     *
     * @return bool
     */
    public
    function addSubjectWithoutGroups(
        TblDivision $tblDivision,
        TblDivision $tblDivisionCopy
    ) {

        $tblSubjectList = $this->getSubjectAllByDivision($tblDivision);
        $done = false;
        if ($tblSubjectList) {
            foreach ($tblSubjectList as $tblSubject) {

                $tblDivisionSubjectList = $this->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision);
                /** @var TblDivisionSubject $tblDivisionSubject */
                if ($tblDivisionSubjectList) {
                    $done = true;
                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                        if ($tblDivisionSubject->getServiceTblSubject()) {
                            $tblDivisionSubjectCopy = (new Data($this->getBinding()))->addDivisionSubject($tblDivisionCopy,
                                $tblDivisionSubject->getServiceTblSubject(), null, $tblDivisionSubject->getHasGrading());

                            $tblSubjectTeacherList = false;
                            if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                $tblSubjectTeacherList = $this->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
                            }

                            if ($tblSubjectTeacherList) {
                                foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                    if ($tblSubjectTeacher->getServiceTblPerson()) {
                                        (new Data($this->getBinding()))->addSubjectTeacher($tblDivisionSubjectCopy,
                                            $tblSubjectTeacher->getServiceTblPerson());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $done;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblDivision $tblDivisionCopy
     */
    public function addSubjectWithGroups(
        TblDivision $tblDivision,
        TblDivision $tblDivisionCopy
    ) {

        $tblSubjectList = $this->getSubjectAllByDivision($tblDivision);
        if ($tblSubjectList) {
            foreach ($tblSubjectList as $tblSubject) {

                $tblDivisionSubjectList = $this->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision);
                /** @var TblDivisionSubject $tblDivisionSubject */
                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                        $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup();
                        $tblSubjectTeacherList = $this->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
                        $tblSubjectStudentList = $this->getSubjectStudentByDivisionSubject($tblDivisionSubject);

                        if ($tblSubjectGroup) {
                            $tblSubjectGroupCopy = (new Data($this->getBinding()))->createSubjectGroup($tblSubjectGroup->getName(),
                                $tblSubjectGroup->getDescription(), $tblSubjectGroup->isAdvancedCourse() !== null ? $tblSubjectGroup->isAdvancedCourse() : null);

                            // Filter (Schnittstelle) für die Fach-Gruppe kopieren
                            if ($tblSubjectGroupCopy
                                && ($tblSubjectGroupFilterList = $this->getSubjectGroupFilterAllBySubjectGroup($tblSubjectGroup))
                            ) {
                                foreach ($tblSubjectGroupFilterList as $tblSubjectGroupFilter) {
                                    $this->createSubjectGroupFilter(
                                        $tblSubjectGroupCopy,
                                        $tblSubjectGroupFilter->getField(),
                                        $tblSubjectGroupFilter->getValue()
                                    );
                                }
                            }
                        }

                        if ($tblDivisionSubject->getServiceTblSubject()) {
                            if (isset($tblSubjectGroupCopy)) {
                                $tblDivisionSubjectCopy = (new Data($this->getBinding()))->addDivisionSubject($tblDivisionCopy,
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblSubjectGroupCopy,
                                    $tblDivisionSubject->getHasGrading());

                            } else {
                                $tblDivisionSubjectCopy = (new Data($this->getBinding()))->addDivisionSubject($tblDivisionCopy,
                                    $tblDivisionSubject->getServiceTblSubject(), null, $tblDivisionSubject->getHasGrading());
                            }

                            if ($tblSubjectTeacherList) {
                                foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                    if ($tblSubjectTeacher->getServiceTblPerson()) {
                                        (new Data($this->getBinding()))->addSubjectTeacher($tblDivisionSubjectCopy,
                                            $tblSubjectTeacher->getServiceTblPerson());
                                    }
                                }
                            }
                            if ($tblSubjectStudentList) {
                                foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                    if ($tblSubjectStudent->getServiceTblPerson()) {
                                        (new Data($this->getBinding()))->addSubjectStudent($tblDivisionSubjectCopy,
                                            $tblSubjectStudent->getServiceTblPerson());
                                    }
                                }
                            }
                        }
                    } else {
                        if ($tblDivisionSubject->getServiceTblSubject()) {
                            $tblDivisionSubjectCopy = (new Data($this->getBinding()))->addDivisionSubject($tblDivisionCopy,
                                $tblDivisionSubject->getServiceTblSubject(), null, $tblDivisionSubject->getHasGrading());

                            $tblSubjectTeacherList = $this->getSubjectTeacherByDivisionSubject($tblDivisionSubject);

                            if ($tblSubjectTeacherList) {
                                foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                    if ($tblSubjectTeacher->getServiceTblPerson()) {
                                        (new Data($this->getBinding()))->addSubjectTeacher($tblDivisionSubjectCopy,
                                            $tblSubjectTeacher->getServiceTblPerson());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return string
     */
    public
    function getSubjectTeacherNameList(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        $nameList = array();
        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectBySubjectAndDivision(
            $tblSubject, $tblDivision
        );
        if ($tblDivisionSubjectList) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                $isAdd = false;
                if (!$tblDivisionSubject->getTblSubjectGroup()) {
                    $isAdd = true;
                } elseif ($tblSubjectGroup !== null
                    && $tblSubjectGroup->getId() == $tblDivisionSubject->getTblSubjectGroup()->getId()
                ) {
                    $isAdd = true;
                }

                if ($isAdd) {
                    $tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
                    if ($tblSubjectTeacherList) {
                        foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                            if ($tblSubjectTeacher->getServiceTblPerson()) {
                                $nameList[$tblSubjectTeacher->getServiceTblPerson()->getId()]
                                    = $tblSubjectTeacher->getServiceTblPerson()->getFullName();
                            }
                        }
                    }
                }
            }
        }

        return empty($nameList) ? '' : implode(', ', $nameList);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function existsDivisionStudent(
        TblDivision $tblDivision,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->existsDivisionStudent($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public
    function exitsSubjectStudent(
        TblDivisionSubject $tblDivisionSubject,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->exitsSubjectStudent($tblDivisionSubject, $tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblDivisionSubject[]
     */
    public
    function getDivisionSubjectAllByPersonAndYear(
        TblPerson $tblPerson,
        TblYear $tblYear
    ) {

        $resultList = array();
        $tblDivisionList = Division::useService()->getDivisionByYear($tblYear);
        if ($tblDivisionList) {
            foreach ($tblDivisionList as $tblDivision) {
                if ($this->existsDivisionStudent($tblDivision, $tblPerson)) {
                    $tblDivisionSubjectList = $this->getDivisionSubjectByDivision($tblDivision);
                    if ($tblDivisionSubjectList) {
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                $groups = $this->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblDivisionSubject->getServiceTblSubject()
                                );
                                if ($groups) {
                                    foreach ($groups as $item) {
                                        if ($this->exitsSubjectStudent($item, $tblPerson)) {
                                            $resultList[$item->getId()] = $item;
                                        }
                                    }
                                } else {
                                    $resultList[$tblDivisionSubject->getId()] = $tblDivisionSubject;
                                }
                            }
                        }
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionStudent $tblDivisionStudent
     * @param integer $SortOrder
     *
     * @return bool
     */
    public
    function updateDivisionStudentSortOrder(
        TblDivisionStudent $tblDivisionStudent,
        $SortOrder
    ) {

        return (new Data($this->getBinding()))->updateDivisionStudentSortOrder($tblDivisionStudent, $SortOrder);
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool $withInActive
     *
     * @return bool|TblDivisionStudent[]
     */
    public function getDivisionStudentAllByDivision(
        TblDivision $tblDivision,
        $withInActive = false
    ) {

        return (new Data($this->getBinding()))->getDivisionStudentAllByDivision($tblDivision, $withInActive);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int|null
     */
    public
    function getDivisionStudentSortOrderMax(
        TblDivision $tblDivision
    ) {

        return (new Data($this->getBinding()))->getDivisionStudentSortOrderMax($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return false|TblDivisionStudent
     */
    public
    function getDivisionStudentByDivisionAndPerson(
        TblDivision $tblDivision,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getDivisionStudentByDivisionAndPerson($tblDivision, $tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @return false|TblDivisionCustody[]
     */
    public function getDivisionCustodyAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionCustodyAllByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @return false|TblDivisionTeacher[]
     */
    public function getDivisionTeacherAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionTeacherAllByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @return false|TblSubjectStudent[]
     */
    public function getSubjectStudentAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getSubjectStudentAllByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @return false|TblSubjectTeacher[]
     */
    public function getSubjectTeacherAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getSubjectTeacherAllByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removePerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblDivisionCustodyAllByPerson = $this->getDivisionCustodyAllByPerson($tblPerson))){
            foreach($tblDivisionCustodyAllByPerson as $tblDivisionCustody){
                $this->removePersonToDivision(
                    $tblDivisionCustody->getTblDivision(true),
                    $tblDivisionCustody->getServiceTblPerson(true),
                    $IsSoftRemove
                );
            }
        }

        if (($tblDivisionStudentAllByPerson = $this->getDivisionStudentAllByPerson($tblPerson))){
            foreach($tblDivisionStudentAllByPerson as $tblDivisionStudent){
                $this->removeStudentToDivision(
                    $tblDivisionStudent->getTblDivision(true),
                    $tblDivisionStudent->getServiceTblPerson(true),
                    $IsSoftRemove
                );
            }
        }

        if (($tblDivisionTeacherAllByPerson = $this->getDivisionTeacherAllByPerson($tblPerson))){
            foreach($tblDivisionTeacherAllByPerson as $tblDivisionTeacher){
                $this->removeTeacherToDivision(
                    $tblDivisionTeacher->getTblDivision(true),
                    $tblDivisionTeacher->getServiceTblPerson(true),
                    $IsSoftRemove
                );
            }
        }

        if (($tblSubjectTeacherAllByPerson = $this->getSubjectTeacherAllByPerson($tblPerson))){
            foreach($tblSubjectTeacherAllByPerson as $tblSubjectTeacher){
                $this->removeSubjectTeacher(
                    $tblSubjectTeacher,
                    $IsSoftRemove
                );
            }
        }
    }

    /**
     * @param TblType $serviceTblType
     * @param $Name
     *
     * @return bool|TblLevel
     */
    public function getLevelBy(TblType $serviceTblType, $Name)
    {

        return (new Data($this->getBinding()))->getLevelBy($serviceTblType, $Name);
    }

    /**
     * Bei Gruppen, ohne Ohne-Gruppe
     *
     * @param TblDivision $tblDivision
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectListByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getDivisionSubjectListByDivision($tblDivision);
    }

    /**
     * Kann nur gelöscht werden wenn noch keine Tests und Noten existieren
     *
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function canRemoveSubjectGroup(TblDivisionSubject $tblDivisionSubject)
    {

        if (Evaluation::useService()->existsTestByDivisionSubject($tblDivisionSubject)) {
            return false;
        }

        return !Gradebook::useService()->existsGradeByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return null|object|TblSubjectStudent
     */
    public function addSubjectStudentData(TblDivisionSubject $tblDivisionSubject, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addSubjectStudent($tblDivisionSubject, $tblPerson);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblDivision[]
     */
    public function getDivisionAllByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getDivisionAllByYear($tblYear);
    }

    /**
     * @param TblYear $tblYear
     * @param TblType $tblType
     *
     * @return TblDivision[]|bool
     */
    public function getDivisionAllByYearAndType(TblYear $tblYear, TblType $tblType)
    {

        $result = array();

        if (($tblDivisionList = $this->getDivisionAllByYear($tblYear))) {
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblLevel = $tblDivision->getTblLevel())
                    && ($tblTypeDivision = $tblLevel->getServiceTblType())
                    && $tblType->getId() == $tblTypeDivision->getId()
                )  {
                    $result[] = $tblDivision;
                }
            }
        }

        return empty($result) ? false : $result;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return int
     */
    public function getStudentCountByYear(TblYear $tblYear)
    {

        $countStudentsByYear = 0;
        if (($tblDivisionList = $this->getDivisionAllByYear($tblYear))) {
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblLevel = $tblDivision->getTblLevel())
                    && !$tblLevel->getIsChecked()
                ) {
                    if (($tblStudentList = $this->getStudentAllByDivision($tblDivision))) {
                        $countStudentsByYear += count ($tblStudentList);
                    }
                }
            }
        }

        return $countStudentsByYear;
    }

    /**
     * @param IFormInterface $Form
     * @param TblDivision $tblDivision
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateDivisionSubject(
        IFormInterface $Form,
        TblDivision $tblDivision,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Form;
        }

        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                    && isset($Data[$tblSubject->getId()]) != $tblDivisionSubject->getHasGrading()
                ) {
                    (new Data($this->getBinding()))->updateDivisionSubject($tblDivisionSubject, isset($Data[$tblSubject->getId()]));
                }
            }
        }

        return new Success('Die Zuordnung, welche Fächer benotet werden sollen, wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Lesson/Division/Subject/Add', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblDivision->getId(), 'IsHasGradingView' => true));
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param $field
     *
     * @return false|TblSubjectGroupFilter
     */
    public function getSubjectGroupFilterBy(TblSubjectGroup $tblSubjectGroup, $field)
    {

        return (new Data($this->getBinding()))->getSubjectGroupFilterBy($tblSubjectGroup, $field);
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return false|TblSubjectGroupFilter[]
     */
    public function getSubjectGroupFilterAllBySubjectGroup(TblSubjectGroup $tblSubjectGroup)
    {

        return (new Data($this->getBinding()))->getSubjectGroupFilterAllBySubjectGroup($tblSubjectGroup);
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param $field
     * @param $value
     *
     * @return null|TblSubjectGroupFilter
     */
    public function createSubjectGroupFilter(TblSubjectGroup $tblSubjectGroup, $field, $value)
    {

        return (new Data($this->getBinding()))->createSubjectGroupFilter($tblSubjectGroup, $field, $value);
    }

    /**
     * @param TblSubjectGroupFilter $tblSubjectGroupFilter
     * @param $value
     *
     * @return bool
     */
    public function updateSubjectGroupFilter(TblSubjectGroupFilter $tblSubjectGroupFilter, $value)
    {

        return (new Data($this->getBinding()))->updateSubjectGroupFilter($tblSubjectGroupFilter, $value);
    }

    /**
     * @param TblSubjectGroupFilter $tblSubjectGroupFilter
     *
     * @return bool
     */
    public function destroySubjectGroupFilter(TblSubjectGroupFilter $tblSubjectGroupFilter)
    {

        return (new Data($this->getBinding()))->destroySubjectGroupFilter($tblSubjectGroupFilter);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function addAllAvailableStudentsToSubjectGroup(TblDivisionSubject $tblDivisionSubject)
    {

        $filter = new Filter($tblDivisionSubject);
        $filter->load();

        $personData = array();
        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblPersonList = $this->getStudentAllByDivision($tblDivision))) {
            foreach ($tblPersonList as $tblPerson) {

                if (!$this->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubject, $tblPerson)
                    && $filter->isFilterFulfilledByPerson($tblPerson)
                ) {
                    $personData[$tblPerson->getId()] = $tblPerson;
                }
            }

            (new Data($this->getBinding()))->addAllAvailableStudentsToSubjectGroup($tblDivisionSubject, $personData);
        }

        return true;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeAllSelectedStudentsFromSubjectGroup(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->removeAllSelectedStudentsFromSubjectGroup($tblDivisionSubject);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function exitsSubjectGroup(TblDivision $tblDivision, TblSubject $tblSubject)
    {
        if (($tblDivisionSubjectList = $this->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision))) {
            if (count($tblDivisionSubjectList) > 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblDivisionStudent $tblDivisionStudent
     *
     * @return bool
     */
    public function restoreDivisionStudent(TblDivisionStudent $tblDivisionStudent)
    {

        return (new Data($this->getBinding()))->restoreDivisionStudent($tblDivisionStudent);
    }

    /**
     * @param $divisionName
     * @param TblYear|null $tblYear
     * @param TblType $tblType
     *
     * @return TblDivision[]
     */
    public function getDivisionAllByName($divisionName, TblYear $tblYear = null, TblType $tblType = null)
    {

        $divisionList = array();
        $divisionName = str_replace(' ', '', $divisionName);
        $divisionName = strtolower($divisionName);
        // bei der Eingabe einer Klassenstufen werden alle Klassen dieser Klassenstufe zurückgegeben
        if (preg_match('/^[1-9][0-9]*$/', $divisionName)
            && ($tblDivisionList = $this->getDivisionAllByLevelName($divisionName, $tblYear, $tblType))
        ) {
            return $tblDivisionList;
        } else {
            if (($tblDivisionAll = $this->getDivisionAll())) {
                foreach ($tblDivisionAll as $tblDivision) {
                    // filter $tblYear
                    if ($tblYear
                        && ($tblYearDivision = $tblDivision->getServiceTblYear())
                        && $tblYear->getId() != $tblYearDivision->getId()
                    ) {
                        continue;
                    }

                    // filter $tblDivision
                    if ($tblType
                        && ($tblLevel = $tblDivision->getTblLevel())
                        && ($tblTypeDivision = $tblLevel->getServiceTblType())
                        && $tblType->getId() != $tblTypeDivision->getId()
                    ) {
                        continue;
                    }

                    if ($divisionName == str_replace(' ', '', strtolower($tblDivision->getDisplayName()))) {
                        $divisionList[] = $tblDivision;
                    }
                }
            }
        }

        return $divisionList;
    }

    /**
     * @param TblDivisionStudent $tblDivisionStudent
     *
     * @return bool
     */
    public function activateDivisionStudent(TblDivisionStudent $tblDivisionStudent)
    {

        return (new Data($this->getBinding()))->updateDivisionStudentActivation($tblDivisionStudent, null, true);
    }

    /**
     * @param TblDivisionStudent $tblDivisionStudent
     * @param \DateTime $LeaveDate
     * @param $UseGradesInNewDivision
     *
     * @return bool
     */
    public function deactivateDivisionStudent(TblDivisionStudent $tblDivisionStudent, \DateTime $LeaveDate, $UseGradesInNewDivision)
    {

        return (new Data($this->getBinding()))->updateDivisionStudentActivation($tblDivisionStudent, $LeaveDate, $UseGradesInNewDivision);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param bool $withCurrentDivision
     *
     * @return TblDivision[]|bool
     */
    public function getOtherDivisionsByStudent(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        $withCurrentDivision = true
    ) {

        $list = array();
        if ($withCurrentDivision) {
            $list[] = $tblDivision;
        }

        if (($tblYear = $tblDivision->getServiceTblYear())
            && ($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))
        ) {
            foreach ($tblDivisionStudentList as $tblDivisionStudentItem) {
                if (($tblDivisionItem = $tblDivisionStudentItem->getTblDivision())
                    && $tblDivision->getId() != $tblDivisionItem->getId()
                    && ($tblYearItem = $tblDivisionItem->getServiceTblYear())
                    && $tblYear->getId() == $tblYearItem->getId()
                ) {
                    $list[] = $tblDivisionItem;
                }
            }
        }

        return empty($list) ? false : $list;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDivisionTeacher[]
     */
    public function getDivisionTeacherAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getDivisionTeacherAllByDivision($tblDivision);
    }

    /**
     * @return array|bool|TblCompany[]
     */
    public function getSchoolListForDivision()
    {
        $tblCompanyAllSchool = \SPHERE\Application\Corporation\Group\Group::useService()->getCompanyAllByGroup(
            \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        $tblCompanyAllOwn = array();

        // Normaler Inhalt
        $tblSchoolList = School::useService()->getSchoolAll();
        if ($tblSchoolList) {
            foreach ($tblSchoolList as $tblSchool) {
                if ($tblSchool->getServiceTblCompany()) {
                    $tblCompanyAllOwn[] = $tblSchool->getServiceTblCompany();
                }
            }
        }

        if (empty($tblCompanyAllOwn)) {
            $resultList = $tblCompanyAllSchool;
        } else {
            $resultList = $tblCompanyAllOwn;
        }

        return $resultList;
    }
}