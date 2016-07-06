<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionCustody;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Setup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
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

        if (!( Type::useService()->getTypeById($Level['Type']) )) {
            $Form->setError('Level[Type]', 'Schulart erforderlich! Bitte auswählen');
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

        // Group
        if (isset($Division['Name']) && empty($Division['Name']) && isset($Level['Check'])) {
            $Form->setError('Division[Name]', 'Bitte geben Sie eine Klassengruppe an');
            $Error = true;
        }

        // Level
        if (!$Error) {
            $tblLevel = null;
            if (!isset($Level['Check'])) {
                if (isset($Level['Name']) && empty($Level['Name'])) {
                    $Form->setError('Level[Name]', 'Bitte geben Sie eine Klassenstufe für die Schulart an <br/>');
                    $Error = true;
                } else {
                    $tblType = Type::useService()->getTypeById($Level['Type']);
                    $tblLevel = (new Data($this->getBinding()))->createLevel($tblType, $Level['Name']);
                }
            } else {
                if ($tblType = Type::useService()->getTypeById($Level['Type'])) {
                    $tblLevel = (new Data($this->getBinding()))->createLevel($tblType, '', '', $Level['Check']);
                }
            }
        } else {
            return $Form;
        }

        // Create
        if (!$Error) {

            if ($this->checkDivisionExists($tblYear, $Division['Name'], $tblLevel)
            ) {
                $Form->setError('Division[Name]', 'Name wird in der Klassenstufe/Jahrgang bereits verwendet');
            } else {

                (new Data($this->getBinding()))->createDivision(
                    $tblYear, $tblLevel, $Division['Name'], $Division['Description']
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
     *
     * @return false|Element[]
     */
    public function getDivisionAllByNameAndLevel($Name, TblLevel $tblLevel = null)
    {

        return (new Data($this->getBinding()))->getDivisionAllByNameAndLevel($Name, $tblLevel);
    }

    /**
     * @param string       $Name
     * @param TblYear|null $tblYear
     *
     * @return false|Element[]
     */
    public function getDivisionAllByNameAndYear($Name, TblYear $tblYear = null)
    {

        return (new Data($this->getBinding()))->getDivisionAllByNameAndYear($Name, $tblYear);
    }
    
    /**
     * @param      $Name
     * @param null $Level
     * @param      $Year
     *
     * @return bool|false|\SPHERE\System\Database\Fitting\Element
     */
    public function getDivisionByGroupAndLevelAndYear($Name, $Level = null, $Year)
    {

        if ($Level !== null) {
            $tblLevel = $this->getLevelById($Level);
        } else {
            $tblLevel = null;
        }
        $tblYear = Term::useService()->getYearById($Year);
        return (new Data($this->getBinding()))->getDivisionByGroupAndLevelAndYear($Name, $tblLevel, $tblYear);
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
     *
     * @return null|TblDivision
     */
    public function insertDivision(TblYear $tblYear, TblLevel $tblLevel, $Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createDivision($tblYear, $tblLevel, $Name, $Description);
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblSubjectGroup
     */
    public function createSubjectGroup($Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createSubjectGroup($Name, $Description);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removeStudentToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $tblStudentSubjectList = (new Data($this->getBinding()))->getSubjectStudentByPerson($tblPerson);
        if ($tblStudentSubjectList) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                (new Data($this->getBinding()))->removeSubjectStudent($tblStudentSubject);
            }
        }

        return (new Data($this->getBinding()))->removeStudentToDivision($tblDivision, $tblPerson);
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

        return (new Data($this->getBinding()))->addDivisionStudent($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision          $tblDivision
     * @param TblSubject           $tblSubject
     * @param TblPerson            $tblPerson
     * @param null|TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function checkSubjectTeacherExists(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->checkSubjectTeacherExists($tblDivision, $tblSubject, $tblPerson,
            $tblSubjectGroup);

    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool
     * @deprecated use removeDivisionTeacher()
     */
    public function removeTeacherToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return $this->removeDivisionTeacher($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return bool
     */
    public function removeDivisionTeacher(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->removeDivisionTeacher($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removePersonToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->removePersonToDivision($tblDivision, $tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return string
     */
    public function removeSubjectToDivision(TblDivision $tblDivision, TblSubject $tblSubject)
    {

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
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getDivisionSubjectByDivision($tblDivision);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return mixed
     */
    public function removeDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->removeDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeSubjectGroup(TblSubjectGroup $tblSubjectGroup, TblDivisionSubject $tblDivisionSubject)
    {

        if ($tblDivisionSubject->getTblSubjectGroup()->getId() === $tblSubjectGroup->getId()) {
            (new Data($this->getBinding()))->removeSubjectStudentByDivisionSubject($tblDivisionSubject);
            (new Data($this->getBinding()))->removeSubjectTeacherByDivisionSubject($tblDivisionSubject);
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
    public function addDivisionTeacher(TblDivision $tblDivision, TblPerson $tblPerson, $Description)
    {

        return (new Data($this->getBinding()))->addDivisionTeacher($tblDivision, $tblPerson, $Description);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param             $Description
     *
     * @return null|TblDivisionCustody
     */
    public function addDivisionCustody(TblDivision $tblDivision, TblPerson $tblPerson, $Description)
    {

        return (new Data($this->getBinding()))->addDivisionCustody($tblDivision, $tblPerson, $Description);

    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return null|object|TblDivisionSubject
     */
    public function addSubjectToDivision(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->addDivisionSubject($tblDivision, $tblSubject);
    }

    /**
     * @param TblDivision     $tblDivision
     * @param TblSubject      $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return TblDivisionSubject
     */
    public function createDivisionSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup
    ) {

        return (new Data($this->getBinding()))->addDivisionSubject($tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param IFormInterface $Form
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param array $Group
     * @param int $DivisionSubjectId
     *
     * @return null|object|TblDivisionSubject|IFormInterface
     */
    public function addSubjectToDivisionWithGroup(
        IFormInterface $Form,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        $Group,
        $DivisionSubjectId
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
        }

        if (!$Error) {
            $tblGroup = (new Data($this->getBinding()))->createSubjectGroup($Group['Name'], $Group['Description']);
            if ($tblGroup) {
                if ((new Data($this->getBinding()))->addDivisionSubject($tblDivision, $tblSubject, $tblGroup)) {
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
     * @param IFormInterface $Form
     * @param int $DivisionSubject
     * @param array $Student
     * @param int $DivisionId
     *
     * @return IFormInterface|string
     */
    public function addSubjectStudent(IFormInterface $Form, $DivisionSubject, $Student, $DivisionId)
    {

        $Global = $this->getGlobal();

        /**
         * Skip to Frontend
         */
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Form;
        }

        $Error = false;
        if (empty($DivisionSubject)) {
            $Form .= new Warning('Keine Zuordnung ohne Fach möglich');
            $Error = true;
        }

        if (!$Error) {

            // Remove old Link
            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if (!$tblDivision) {
                return new Danger('Klasse nicht gefunden.', new Ban())
                . new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
            }

            $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubject);
            $tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
            if (is_array($tblSubjectStudentList)) {
                array_walk($tblSubjectStudentList, function ($tblSubjectStudentList) {

                    if (!$this->removeSubjectStudent($tblSubjectStudentList)) {
                    }
                });
            }

            // Add new Link
            if (is_array($Student)) {
                array_walk($Student, function ($Student) use ($tblDivisionSubject, &$Error) {

                    $tblPerson = Person::useService()->getPersonById($Student);
                    if ($tblPerson) {
                        if (!(new Data($this->getBinding()))->addSubjectStudent($tblDivisionSubject, $tblPerson)
                        ) {
                            $Error = false;
                        }
                    }
                });
            }

            if (!$Error) {
                return new Success('Die Gruppe mit Personen wurden erfolgreich angelegt')
                . new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblDivision->getId()));
            } else {
                return new Danger('Einige Personen konnte nicht in der Gruppe angelegt werden')
                . new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblDivision->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivision
     */
    public function getDivisionById($Id)
    {

        return (new Data($this->getBinding()))->getDivisionById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivisionSubject
     */
    public function getDivisionSubjectById($Id)
    {

        return (new Data($this->getBinding()))->getDivisionSubjectById($Id);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->getSubjectStudentByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblSubjectStudent $tblSubjectStudent
     *
     * @return string
     */
    public function removeSubjectStudent(TblSubjectStudent $tblSubjectStudent)
    {

        return (new Data($this->getBinding()))->removeSubjectStudent($tblSubjectStudent);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblPerson[]
     */
    public function getStudentByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->getStudentByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivisionStudent
     */
    public function getDivisionStudentById($Id)
    {

        return (new Data($this->getBinding()))->getDivisionStudentById($Id);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson          $tblPerson
     *
     * @return TblSubjectTeacher
     */
    public function createSubjectTeacher(TblDivisionSubject $tblDivisionSubject, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addSubjectTeacher($tblDivisionSubject, $tblPerson);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson          $tblPerson
     *
     * @return TblSubjectTeacher
     */
    public function addSubjectTeacher(TblDivisionSubject $tblDivisionSubject, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addSubjectTeacher($tblDivisionSubject, $tblPerson);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getSubjectTeacherByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblSubjectTeacher $tblSubjectTeacher
     *
     * @return bool
     */
    public function removeSubjectTeacher(TblSubjectTeacher $tblSubjectTeacher)
    {

        return (new Data($this->getBinding()))->removeSubjectTeacher($tblSubjectTeacher);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return TblDivisionStudent
     */
    public function insertDivisionStudent(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addDivisionStudent($tblDivision, $tblPerson);
    }

    /**
     * @param IFormInterface $Form
     * @param null|array $Division
     * @param int $Id
     *
     * @return IFormInterface|string
     */
    public function changeDivision(IFormInterface $Form, $Division, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Division) {
            return $Form;
        }

        $Error = false;

        if (isset($Division['Name']) && empty($Division['Name'])
        ) {
            $Form->setError('Division[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
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
                    $tblDivision, $Division['Name'], $Division['Description']
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
    public function getSubjectStudentById($Id)
    {

        return (new Data($this->getBinding()))->getSubjectStudentById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectTeacher
     */
    public function getSubjectTeacherById($Id)
    {

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
     * @param IFormInterface $Form
     * @param                $Group
     * @param                $Id
     * @param                $DivisionId
     * @param                $DivisionSubjectId
     *
     * @return IFormInterface|string
     */
    public function changeSubjectGroup(IFormInterface $Form, $Group, $Id, $DivisionId, $DivisionSubjectId)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        $tblSubjectGroup = Division::useService()->getSubjectGroupById($Id);

        if (isset($SubjectGroup['Name']) && empty($SubjectGroup['Name'])) {
            $Form->setError('Group[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        } else {
//            $SubjectGroupTest = Division::useService()->checkSubjectGroupExists($Group['Name'], $Group['Description']);   // Test auf doppelte Namen sinnvoll?
//            if ($SubjectGroupTest) {
//                if ($SubjectGroupTest->getId() !== $tblSubjectGroup->getId()) {
//                    $Form->setError('Group[Name]', 'Kombination schon vergeben');
//                    $Form->setError('Group[Description]', 'Beschreibung oder Gruppenname ändern');
//                    $Error = true;
//                }
//            }

        }

        if (!$Error) {

            if ($tblSubjectGroup) {
                if ((new Data($this->getBinding()))->updateSubjectGroup(
                    $tblSubjectGroup, $Group['Name'], $Group['Description']
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
    public function getSubjectGroupById($Id)
    {

        return (new Data($this->getBinding()))->getSubjectGroupById($Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool
     */
    public function destroyDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->destroyDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getTeacherAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTeacherAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getCustodyAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getCustodyAllByDivision($tblDivision);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getTeacherAllByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        return (new Data($this->getBinding()))->getTeacherAllByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return string
     */
    public function destroyLevel(TblLevel $tblLevel)
    {

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
    public function getDivisionByLevel(TblLevel $tblLevel)
    {

        return (new Data($this->getBinding()))->getDivisionByLevel($tblLevel);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getDivisionByYear($tblYear);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionStudent[]
     */
    public function getDivisionStudentAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionStudentAllByPerson($tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionStudentAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->countDivisionStudentAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionTeacherAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->countDivisionTeacherAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionCustodyAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->countDivisionCustodyAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionSubjectAllByDivision(TblDivision $tblDivision)
    {

        $Sum = (new Data($this->getBinding()))->countDivisionSubjectAllByDivision($tblDivision);
        $Sub = (new Data($this->getBinding()))->countDivisionSubjectGroupByDivision($tblDivision);
        return ($Sum - $Sub);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return int
     */
    public function countDivisionSubjectUsedByDivision(TblDivision $tblDivision)
    {

        $DivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
        $SubjectUsedCount = 0;
        if ($DivisionSubjectList) {
            foreach ($DivisionSubjectList as $DivisionSubject) {

                if (!$DivisionSubject->getTblSubjectGroup()) {
                    if ($DivisionSubject->getServiceTblSubject()) {
                        $tblDivisionSubjectActiveList = Division::useService()
                            ->getDivisionSubjectBySubjectAndDivision($DivisionSubject->getServiceTblSubject(),
                                $tblDivision);
                        $TeacherGroup = array();
                        if ($tblDivisionSubjectActiveList) {
                            /**@var TblDivisionSubject $tblDivisionSubjectActive */
                            foreach ($tblDivisionSubjectActiveList as $tblDivisionSubjectActive) {
                                $TempList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectActive);
                                if ($TempList) {
                                    foreach ($TempList as $Temp) {
                                        if ($Temp->getServiceTblPerson()) {
                                            array_push($TeacherGroup, $Temp->getId());
                                        }
                                    }
                                }
                            }
                            if (empty($TeacherGroup)) {
                                $SubjectUsedCount = $SubjectUsedCount + 1;
                            }
                        }
                    }
                }
            }
        }
        return $SubjectUsedCount;
    }

    /**
     * @param TblSubject $tblSubject
     * @param TblDivision $tblDivision
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectBySubjectAndDivision(TblSubject $tblSubject, TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
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
    public function getSubjectTeacherAllByTeacher(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getSubjectTeacherAllByTeacher($tblPerson);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|TblDivisionSubject
     * @deprecated use getDivisionSubject()
     */
    public function getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return $this->getDivisionSubject($tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param TblDivision          $tblDivision
     * @param TblSubject           $tblSubject
     * @param null|TblSubjectGroup $tblSubjectGroup
     *
     * @return false|TblDivisionSubject
     */
    public function getDivisionSubject(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getDivisionSubject($tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionTeacher[]
     */
    public function getDivisionTeacherAllByTeacher(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDivisionTeacherAllByTeacher($tblPerson);
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectStudent
     */
    public function getSubjectStudentByDivisionSubjectAndPerson(
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
    public function countSubjectStudentByDivisionSubject(
        TblDivisionSubject $tblDivisionSubject
    ) {

        return (new Data($this->getBinding()))->countSubjectStudentByDivisionSubject($tblDivisionSubject);
    }

    /**
     * @param IFormInterface $Form
     * @param                $tblDivision
     * @param                $Level
     * @param                $Division
     *
     * @return IFormInterface|string
     */
    public function copyDivision(IFormInterface $Form, TblDivision $tblDivision, $Level, $Division)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Level && null === $Division) {
            return $Form;
        }

        $Error = false;

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

        // Group
        if (isset($Division['Name']) && empty($Division['Name']) && isset($Level['Check'])) {
            $Form->setError('Division[Name]', 'Bitte geben Sie eine Klassengruppe an');
            $Error = true;
        }

        // Level
        if (!$Error) {
            $tblLevel = null;
            if (!isset($Level['Check'])) {
                if (isset($Level['Name']) && empty($Level['Name'])) {
                    $Form->setError('Level[Name]', 'Bitte geben Sie eine Klassenstufe für die Schulart an <br/>');
                    $Error = true;
                } else {
                    $tblType = Type::useService()->getTypeById($Level['Type']);
                    $tblLevel = (new Data($this->getBinding()))->createLevel($tblType, $Level['Name']);
                }
            } else {
                if ($tblType = Type::useService()->getTypeById($Level['Type'])) {
                    $tblLevel = (new Data($this->getBinding()))->createLevel($tblType, '', '', $Level['Check']);
                }
            }
        } else {
            return $Form;
        }

        // Create
        if (!$Error) {

            if ($this->checkDivisionExists($tblYear, $Division['Name'], $tblLevel)
            ) {
                $Form->setError('Division[Name]', 'Name wird in der Klassenstufe/Jahrgang bereits verwendet');
            } else {

                $tblDivisionCopy = (new Data($this->getBinding()))->createDivision(
                    $tblYear, $tblLevel, $Division['Name'], $Division['Description']
                );

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


                $tblDivisionStudentList = $this->getStudentAllByDivision($tblDivision);
                if ($tblDivisionStudentList) {
                    foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                        (new Data($this->getBinding()))->addDivisionStudent($tblDivisionCopy, $tblDivisionStudent);
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
    public function getMinDivisionByLevelType(TblType $tblType)
    {

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
    public function getLevelByServiceTblType(TblType $serviceTblType)
    {

        return (new Data($this->getBinding()))->getLevelByServiceTblType($serviceTblType);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblDivision $tblDivisionCopy
     *
     * @return bool
     */
    public function addSubjectWithoutGroups(TblDivision $tblDivision, TblDivision $tblDivisionCopy)
    {

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
                                $tblDivisionSubject->getServiceTblSubject());

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
     *
     * @return bool|TblSubject[]
     */
    public function getSubjectAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getSubjectAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblDivision $tblDivisionCopy
     */
    public function addSubjectWithGroups(TblDivision $tblDivision, TblDivision $tblDivisionCopy)
    {

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
                                $tblSubjectGroup->getDescription());
                        }

                        if ($tblDivisionSubject->getServiceTblSubject()) {
                            if (isset($tblSubjectGroupCopy)) {
                                $tblDivisionSubjectCopy = (new Data($this->getBinding()))->addDivisionSubject($tblDivisionCopy,
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblSubjectGroupCopy);

                            } else {
                                $tblDivisionSubjectCopy = (new Data($this->getBinding()))->addDivisionSubject($tblDivisionCopy,
                                    $tblDivisionSubject->getServiceTblSubject());
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
                                $tblDivisionSubject->getServiceTblSubject());

                            $tblSubjectTeacherList = $this->getSubjectTeacherByDivisionSubject($tblDivisionSubject);

                            if ($tblSubjectTeacherList) {
                                foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
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

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getStudentAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getStudentAllByDivision($tblDivision);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return string
     */
    public function getSubjectTeacherNameList(
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

                if ($isAdd){
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
}
