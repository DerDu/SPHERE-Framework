<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

abstract class Certificate extends Extension
{

    /** @var null|Frame $Certificate */
    private $Certificate = null;

    /**
     * @var bool
     */
    private $IsSample;

    /**
     * @var array|false
     */
    private $Grade;

    /**
     * @var TblPerson|null
     */
    private $tblPerson = null;

    /**
     * @var TblDivision|null
     */
    private $tblDivision = null;

    /**
     * @param bool|true $IsSample
     */
    public function __construct($IsSample = true)
    {

        $this->getCache(new TwigHandler())->clearCache();

        $this->setGrade(false);
        $this->IsSample = (bool)$IsSample;
        $this->Certificate = $this->buildCertificate($this->IsSample);
    }

    /**
     * @param array $Data
     *
     * @return IBridgeInterface
     */
    public function createCertificate($Data = array())
    {

        if (isset($Data['Grade'])) {
            $this->setGrade($Data['Grade']);
        }
        if (isset($Data['Person']['Id'])) {
            if (($person = Person::useService()->getPersonById($Data['Person']['Id']))) {
                $this->setTblPerson($person);
                $this->allocatePersonData($Data);
                $this->allocatePersonAddress($Data);
                $this->allocatePersonCommon($Data);
                $this->allocatePersonParents($Data);
            } else {
                $this->setTblPerson(null);
            }
        }
        if (isset($Data['Company']['Id'])
            && ($tblCompany = Company::useService()->getCompanyById($Data['Company']['Id']))
        ) {
            $this->allocateCompanyData($Data);
            $this->allocateCompanyAddress($Data);
        }
        if (isset($Data['Division']['Id'])
            && ($tblDivision = Division::useService()->getDivisionById($Data['Division']['Id']))
        ) {
            $this->setTblDivision($tblDivision);
        }

        $this->Certificate = $this->buildCertificate($this->IsSample);

        // für Befreiung
        if (isset($Data['Grade'])) {
            $Data['Grade'] = $this->getGrade();
        }

        if (!empty($Data)) {
            $this->Certificate->setData($Data);
        }

        return $this->Certificate->getTemplate();
    }

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    abstract public function buildCertificate($IsSample = true);

    /**
     * @param $Grade
     */
    public function setGrade($Grade)
    {
        $this->Grade = $Grade;
    }

    /**
     * @return array|false
     */
    public function getGrade()
    {

        return $this->Grade;
    }

    /**
     * @return false|TblPerson
     */
    public function getTblPerson()
    {
        if (null === $this->tblPerson) {
            return false;
        } else {
            return $this->tblPerson;
        }
    }

    /**
     * @param false|TblPerson $tblPerson
     */
    public function setTblPerson(TblPerson $tblPerson = null)
    {

        $this->tblPerson = $tblPerson;
    }

    /**
     * @return false|TblDivision
     */
    public function getTblDivision()
    {
        if (null === $this->tblDivision) {
            return false;
        } else {
            return $this->tblDivision;
        }
    }

    /**
     * @param false|TblDivision $tblDivision
     */
    public function setTblDivision(TblDivision $tblDivision = null)
    {

        $this->tblDivision = $tblDivision;
    }

    /**
     * @return string Certificate-Name from Database-Settings
     * @throws \Exception
     */
    public function getCertificateName()
    {

        $Certificate = trim(str_replace(
            'SPHERE\Application\Api\Education\Certificate\Generator\Repository', '', get_class($this)
        ), '\\');

        $tblCertificate = Generator::useService()->getCertificateByCertificateClassName($Certificate);
        if ($tblCertificate) {
            return $tblCertificate->getName() . ($tblCertificate->getDescription()
                ? ' (' . $tblCertificate->getDescription() . ')'
                : ''
            );
        }
        throw new \Exception('Certificate Missing: ' . $Certificate);
    }

    /**
     * @return bool|TblCertificate
     * @throws \Exception
     */
    public function getCertificateEntity()
    {

        $Certificate = trim(str_replace(
            'SPHERE\Application\Api\Education\Certificate\Generator\Repository', '', get_class($this)
        ), '\\');

        $tblCertificate = Generator::useService()->getCertificateByCertificateClassName($Certificate);
        if ($tblCertificate) {
            return $tblCertificate;
        }
        throw new \Exception('Certificate Missing: ' . $Certificate);
    }

    /**
     * @return int Certificate-Id from Database-Settings
     * @throws \Exception
     */
    public function getCertificateId()
    {

        $Certificate = trim(str_replace(
            'SPHERE\Application\Api\Education\Certificate\Generator\Repository', '', get_class($this)
        ), '\\');

        $tblCertificate = Generator::useService()->getCertificateByCertificateClassName($Certificate);
        if ($tblCertificate) {
            return $tblCertificate->getId();
        }
        throw new \Exception('Certificate Missing: ' . $Certificate);
    }

    /**
     * @return null|Frame
     */
    public function getCertificate()
    {

        return $this->Certificate;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonData(&$Data)
    {

        if ($this->getTblPerson()) {
            $Data['Person']['Data']['Name']['Salutation'] = $this->getTblPerson()->getSalutation();
            $Data['Person']['Data']['Name']['First'] = $this->getTblPerson()->getFirstSecondName();
            $Data['Person']['Data']['Name']['Last'] = $this->getTblPerson()->getLastName();
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonAddress(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblAddress = $this->getTblPerson()->fetchMainAddress())) {
                $Data['Person']['Address']['Street']['Name'] = $tblAddress->getStreetName();
                $Data['Person']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
                $Data['Person']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
                $Data['Person']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonCommon(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblCommon = Common::useService()->getCommonByPerson($this->getTblPerson()))
                && $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()
            ) {
                $Data['Person']['Common']['BirthDates']['Gender'] = $tblCommonBirthDates->getGender();
                $Data['Person']['Common']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
                $Data['Person']['Common']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace()
                    ? $tblCommonBirthDates->getBirthplace() : '&nbsp;';
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocatePersonParents(&$Data)
    {

        if ($this->getTblPerson()) {
            if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($this->getTblPerson()))) {
                foreach ($tblRelationshipList as $tblToPerson) {
                    if (($tblFromPerson = $tblToPerson->getServiceTblPersonFrom())
                        && $tblToPerson->getServiceTblPersonTo()
                        && $tblToPerson->getTblType()->getName() == 'Sorgeberechtigt'
                        && $tblToPerson->getServiceTblPersonTo()->getId() == $this->getTblPerson()->getId()
                    ) {
                        if (!isset($Data['Person']['Parent']['Mother']['Name'])) {
                            $Data['Person']['Parent']['Mother']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                            $Data['Person']['Parent']['Mother']['Name']['Last'] = $tblFromPerson->getLastName();
                        } elseif (!isset($Data['Person']['Parent']['Father']['Name'])) {
                            $Data['Person']['Parent']['Father']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                            $Data['Person']['Parent']['Father']['Name']['Last'] = $tblFromPerson->getLastName();
                        }
                    }
                }
            }
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocateCompanyData(&$Data)
    {

        if (isset($Data['Company']['Id'])
            && ($tblCompany = Company::useService()->getCompanyById($Data['Company']['Id']))
        ) {
            $Data['Company']['Data']['Name'] = $tblCompany->getName();
        }

        return $Data;
    }

    /**
     * @param array $Data
     *
     * @return array $Data
     */
    private function allocateCompanyAddress(&$Data)
    {

        if (isset($Data['Company']['Id'])
            && ($tblCompany = Company::useService()->getCompanyById($Data['Company']['Id']))
        ) {
            if (($tblAddress = $tblCompany->fetchMainAddress())) {
                $Data['Company']['Address']['Street']['Name'] = $tblAddress->getStreetName();
                $Data['Company']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
                $Data['Company']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
                $Data['Company']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
            }
        }

        return $Data;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getSchoolName($MarginTop = '20px')
    {
        $SchoolSlice = (new Slice());
        $SchoolSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Name der Schule:')
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.Company.Data.Name) %}
                                        {{ Content.Company.Data.Name }}
                                    {% else %}
                                          &nbsp;
                                    {% endif %}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '64%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '18%')
        )->styleMarginTop($MarginTop);
        return $SchoolSlice;
    }

    /**
     * @param string $HeadLine
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCertificateHead($HeadLine = '', $MarginTop = '15px')
    {
        $CertificateSlice = (new Slice());
        $CertificateSlice->addElement((new Element())
            ->setContent($HeadLine)
            ->styleTextSize('18px')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleMarginTop($MarginTop)
        );
        return $CertificateSlice;
    }

    /**
     * @param string $MarginTop
     * @param string $YearString
     *
     * @return Slice
     */
    protected function getDivisionAndYear($MarginTop = '20px', $YearString = 'Schuljahr')
    {
        $YearDivisionSlice = (new Slice());
        $YearDivisionSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klasse:')
                , '7%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '7%')
            ->addElementColumn((new Element())
                , '55%')
            ->addElementColumn((new Element())
                ->setContent($YearString . ':')
                ->styleAlignRight()
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.Division.Data.Year }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '13%')
        )->styleMarginTop($MarginTop);
        return $YearDivisionSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getStudentName($MarginTop = '5px')
    {
        $StudentSlice = (new Slice());
        $StudentSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Vorname und Name:')
                , '21%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.Person.Data.Name.First }}
                              {{ Content.Person.Data.Name.Last }}')
                ->styleBorderBottom()
                , '79%')
        )->styleMarginTop($MarginTop);
        return $StudentSlice;
    }

    /**
     * @param bool|true $isSlice
     * @param array     $languagesWithStartLevel
     * @param string    $TextSize
     * @param bool      $IsGradeUnderlined
     *
     * @return Section[]|Slice
     */
    protected function getSubjectLanes(
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $TextSize = '14px',
        $IsGradeUnderlined = false
    ) {

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();

                // Grade Exists? => Add Subject to Certificate
                if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                        = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                        = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();

                        // es steht nur befreit auf dem Zeugnis -> jetzt auswählbar am Stichtagsnotenauftrag
//                        // Liberation?
//                        if (
//                            $this->getTblPerson()
//                            && ($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))
//                            && ($tblStudentLiberationCategory = $tblCertificateSubject->getServiceTblStudentLiberationCategory())
//                        ) {
//                            $tblStudentLiberationAll = Student::useService()->getStudentLiberationAllByStudent($tblStudent);
//                            if ($tblStudentLiberationAll) {
//                                foreach ($tblStudentLiberationAll as $tblStudentLiberation) {
//                                    if (($tblStudentLiberationType = $tblStudentLiberation->getTblStudentLiberationType())) {
//                                        $tblStudentLiberationType->getTblStudentLiberationCategory();
//                                        if ($tblStudentLiberationCategory->getId() == $tblStudentLiberationType->getTblStudentLiberationCategory()->getId()) {
//                                            $this->Grade['Data'][$tblSubject->getAcronym()] = $tblStudentLiberationType->getName();
//                                        }
//                                    }
//                                }
//                            }
//                        }
                    }
                }
            }

            // add SecondLanguageField, Fach wird aus der Schüleraktte des Schülers ermittelt
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if ($this->getTblPerson()
                        && ($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))
                    ) {
                        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            /** @var TblStudentSubject $tblStudentSubject */
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if ($tblStudentSubject->getTblStudentSubjectRanking()
                                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                                    && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                                ) {
                                    $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                                }
                            }
                        }
                    }
                }
            }

            // Shrink Lanes
            $LaneCounter = array(1 => 0, 2 => 0);
            $SubjectLayout = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
            $SubjectStructure = $SubjectLayout;

            $hasAdditionalLine = false;
            $isShrinkMarginTop = false;

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // 2. Fremdsprache ab Klassenstufe
                    if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])
                        && $languagesWithStartLevel['Lane'] == $Lane && $languagesWithStartLevel['Rank'] == $count
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguage
                            ? $tblSecondForeignLanguage->getAcronym() : 'Empty';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }
                    if ($hasAdditionalLine && $Lane == $hasAdditionalLine['Lane']) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->stylePaddingBottom('0px')
                            ->styleMarginBottom('0px')
                            ->styleBorderBottom('1px', '#000')
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '37%');
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    } elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            ->styleTextSize($TextSize)
                            , '39%');
                        // ToDo Dynamisch für alle zu langen Fächer
                    } elseif ($Subject['SubjectName'] == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent(new Container('Gemeinschaftskunde/')
                                . new Container('Rechtserziehung/Wirtschaft'))
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '39%');
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '39%');
                    }

                    $TextSizeSmall = '8px';

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if(Content.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 4px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 5px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : '10px')
                        ->styleTextSize(
                            '{% if(Content.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , '9%');

                    if ($isShrinkMarginTop && $Lane == 2) {
                        $isShrinkMarginTop = false;
                    }
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                    $isShrinkMarginTop = false;
                }

                $SubjectSlice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;

                if ($hasAdditionalLine) {
                    $SubjectSection = (new Section());

                    if ($hasAdditionalLine['Lane'] == 2) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($hasAdditionalLine['Ranking'] . '. Fremdsprache (ab Klassenstufe ' .
                            '{% if(Content.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] is not empty) %}
                                     {{ Content.Subject.Level["' . $hasAdditionalLine['SubjectAcronym'] . '"] }}
                                 {% else %}
                                    &nbsp;
                                 {% endif %}'
                            . ')')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , '39%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn((new Element()), '52%');
                    }

                    $hasAdditionalLine = false;

                    // es wird abstand gelassen, einkommentieren für keinen extra Abstand der nächsten Zeile
//                    $isShrinkMarginTop = true;

                    $SubjectSlice->addSection($SubjectSection);
                    $SectionList[] = $SubjectSection;
                }

            }
        }

        if ($isSlice) {
            return $SubjectSlice;
        } else {
            return $SectionList;
        }
    }

    /**
     * @param bool|true $isSlice
     * @param array     $languagesWithStartLevel
     * @param string    $TextSize
     * @param bool      $IsGradeUnderlined
     *
     * @return Section[]|Slice
     */
    protected function getSubjectLanesCoswig(
        $isSlice = true,
        $languagesWithStartLevel = array(),
        $TextSize = '14px',
        $IsGradeUnderlined = false
    ) {

        $SubjectSlice = ( new Slice() );

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();

                // Grade Exists? => Add Subject to Certificate
                if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                        = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                        = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();

                        // es steht nur befreit auf dem Zeugnis -> jetzt auswählbar am Stichtagsnotenauftrag
//                        // Liberation?
//                        if (
//                            $this->getTblPerson()
//                            && ($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))
//                            && ($tblStudentLiberationCategory = $tblCertificateSubject->getServiceTblStudentLiberationCategory())
//                        ) {
//                            $tblStudentLiberationAll = Student::useService()->getStudentLiberationAllByStudent($tblStudent);
//                            if ($tblStudentLiberationAll) {
//                                foreach ($tblStudentLiberationAll as $tblStudentLiberation) {
//                                    if (($tblStudentLiberationType = $tblStudentLiberation->getTblStudentLiberationType())) {
//                                        $tblStudentLiberationType->getTblStudentLiberationCategory();
//                                        if ($tblStudentLiberationCategory->getId() == $tblStudentLiberationType->getTblStudentLiberationCategory()->getId()) {
//                                            $this->Grade['Data'][$tblSubject->getAcronym()] = $tblStudentLiberationType->getName();
//                                        }
//                                    }
//                                }
//                            }
//                        }
                    }
                }
            }

            // add SecondLanguageField, Fach wird aus der Schüleraktte des Schülers ermittelt
            $tblSecondForeignLanguage = false;
            if (!empty($languagesWithStartLevel)) {
                if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])) {
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = 'Empty';
                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                    [$languagesWithStartLevel['Lane']]['SubjectName'] = '&nbsp;';
                    if ($this->getTblPerson()
                        && ( $tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()) )
                    ) {
                        if (( $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE') )
                            && ( $tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType) )
                        ) {
                            /** @var TblStudentSubject $tblStudentSubject */
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if ($tblStudentSubject->getTblStudentSubjectRanking()
                                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                                    && ( $tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject() )
                                ) {
                                    $tblSecondForeignLanguage = $tblSubjectForeignLanguage;
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectAcronym'] = $tblSubjectForeignLanguage->getAcronym();
                                    $SubjectStructure[$languagesWithStartLevel['Rank']]
                                    [$languagesWithStartLevel['Lane']]['SubjectName'] = $tblSubjectForeignLanguage->getName();
                                }
                            }
                        }
                    }
                }
            }

            // Shrink Lanes
            $LaneCounter = array(1 => 0, 2 => 0);
            $SubjectLayout = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
            $SubjectStructure = $SubjectLayout;

            $hasAdditionalLine = false;
            $isShrinkMarginTop = false;

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = ( new Section() );

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn(( new Element() ), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // 2. Fremdsprache ab Klassenstufe
                    if (isset($languagesWithStartLevel['Lane']) && isset($languagesWithStartLevel['Rank'])
                        && $languagesWithStartLevel['Lane'] == $Lane && $languagesWithStartLevel['Rank'] == $count
                    ) {
                        $hasAdditionalLine['Lane'] = $Lane;
                        $hasAdditionalLine['Ranking'] = 2;
                        $hasAdditionalLine['SubjectAcronym'] = $tblSecondForeignLanguage
                            ? $tblSecondForeignLanguage->getAcronym() : 'Empty';
                    }

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn(( new Element() )
                            , '4%');
                    }
                    if ($hasAdditionalLine && $Lane == $hasAdditionalLine['Lane']) {
                        $SubjectSection->addElementColumn(( new Element() )
                            ->setContent($Subject['SubjectName'])
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop()
                            ->stylePaddingBottom('0px')
                            ->styleMarginBottom('0px')
                            ->styleBorderBottom('1px', '#000')
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '37%');
                        $SubjectSection->addElementColumn(( new Element() ), '2%');
                    } elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn(( new Element() )
                            ->setContent($Subject['SubjectName'])
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            ->styleTextSize($TextSize)
                            , '39%');
                        // ToDo Dynamisch für alle zu langen Fächer
                    } elseif ($Subject['SubjectName'] == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                        $SubjectSection->addElementColumn(( new Element() )
                            ->setContent(new Container('Gemeinschaftskunde/')
                                .new Container('Rechtserziehung/Wirtschaft'))
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '39%');
                    } else {
                        $SubjectSection->addElementColumn(( new Element() )
                            ->setContent($Subject['SubjectName'])
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            ->styleTextSize($TextSize)
                            , '39%');
                    }

                    $TextSizeSmall = '8px';

                    $SubjectSection->addElementColumn(( new Element() )
                        ->setContent('{% if(Content.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                             {{ Content.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if(Content.Grade.Data.IsShrinkSize["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                 4px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.Grade.Data.IsShrinkSize["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                 5px
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : '10px')
                        ->styleTextSize(
                            '{% if(Content.Grade.Data.IsShrinkSize["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                 '.$TextSizeSmall.'
                             {% else %}
                                 '.$TextSize.'
                             {% endif %}'
                        )
                        , '9%');

                    if ($isShrinkMarginTop && $Lane == 2) {
                        $isShrinkMarginTop = false;
                    }
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn(( new Element() ), '52%');
                    $isShrinkMarginTop = false;
                }

                $SubjectSlice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;

                if ($hasAdditionalLine) {
                    $SubjectSection = ( new Section() );

                    if ($hasAdditionalLine['Lane'] == 2) {
                        $SubjectSection->addElementColumn(( new Element() ), '52%');
                    }
                    $SubjectSection->addElementColumn(( new Element() )
                        ->setContent($hasAdditionalLine['Ranking'].'. Fremdsprache (ab Klassenstufe '.
                            '{% if(Content.Subject.Level["'.$hasAdditionalLine['SubjectAcronym'].'"] is not empty) %}
                                     {{ Content.Subject.Level["'.$hasAdditionalLine['SubjectAcronym'].'"] }}
                                 {% else %}
                                    &nbsp;
                                 {% endif %}'
                            .')')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('0px')
                        ->styleMarginBottom('0px')
                        ->styleTextSize('9px')
                        , '39%');

                    if ($hasAdditionalLine['Lane'] == 1) {
                        $SubjectSection->addElementColumn(( new Element() ), '52%');
                    }

                    $hasAdditionalLine = false;

                    // es wird abstand gelassen, einkommentieren für keinen extra Abstand der nächsten Zeile
//                    $isShrinkMarginTop = true;

                    $SubjectSlice->addSection($SubjectSection);
                    $SectionList[] = $SubjectSection;
                }

            }
        }

        if ($isSlice) {
            return $SubjectSlice;
        } else {
            return $SectionList;
        }
    }

    /**
     * @param bool $isMissing
     *
     * @return Slice
     */
    protected function getDescriptionHead($isMissing = false)
    {
        $DescriptionSlice = (new Slice());
        if ($isMissing) {
            $DescriptionSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bemerkungen:')
                    , '16%')
                ->addElementColumn((new Element())
                    ->setContent('Fehltage entschuldigt:')
//                    ->styleBorderBottom('1px')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('unentschuldigt:')
//                    ->styleBorderBottom('1px')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '4%')
            )
                ->styleMarginTop('15px');
        } else {
            $DescriptionSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Bemerkungen:'))
            )->styleMarginTop('15px');
        }
        return $DescriptionSlice;
    }

    /**
     * @param string $Height
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getDescriptionContent($Height = '150px', $MarginTop = '0px')
    {
        $DescriptionSlice = (new Slice());
        $DescriptionSlice->addElement(( new Element() )
            ->setContent('{% if(Content.Input.Remark is not empty) %}
                        {{ Content.Input.Remark|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            ->styleHeight($Height)
            ->styleMarginTop($MarginTop)
        );
        return $DescriptionSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getTransfer($MarginTop = '5px')
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Versetzungsvermerk:')
                , '22%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.Input.Transfer) %}
                                        {{ Content.Input.Transfer }}
                                    {% else %}
                                          &nbsp;
                                    {% endif %}')
                ->styleBorderBottom('1px')
                , '58%')
            ->addElementColumn((new Element())
                , '20%')
        )
            ->styleMarginTop($MarginTop);
        return $TransferSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getDateLine($MarginTop = '25px')
    {
        $DateSlice = (new Slice());
        $DateSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Datum:')
                , '7%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                ->styleBorderBottom('1px', '#000')
                ->styleAlignCenter()
                , '23%')
            ->addElementColumn((new Element())
                , '70%')
        )
            ->styleMarginTop($MarginTop);
        return $DateSlice;
    }

    /**
     * @param bool $isExtended with directory and stamp
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getSignPart($isExtended = true, $MarginTop = '25px')
    {
        $SignSlice = (new Slice());
        if ($isExtended) {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Schulleiter(in)')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Klassenlehrer(in)')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.Headmaster.Name is not empty) %}
                                {{ Content.Headmaster.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.DivisionTeacher.Name is not empty) %}
                                {{ Content.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                );
        } else {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    , '70%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('Klassenlehrer(in)')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.DivisionTeacher.Name is not empty) %}
                                {{ Content.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                );
        }
        return $SignSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getParentSign($MarginTop = '25px')
    {
        $ParentSlice = (new Slice());
        $ParentSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Zur Kenntnis genommen:')
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '40%')
            ->addElementColumn((new Element())
                , '30%')
        )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Eltern')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '40%')
                ->addElementColumn((new Element())
                    , '30%')
            )
            ->styleMarginTop($MarginTop);
        return $ParentSlice;
    }

    /**
     * @param string $MarginTop
     * @param string $LineOne
     * @param string $LineTwo
     * @param string $LineThree
     * @param string $LineFour
     * @param string $LineFive
     *
     * @return Slice
     */
    protected function getInfo(
        $MarginTop = '10px',
        $LineOne = '',
        $LineTwo = '',
        $LineThree = '',
        $LineFour = '',
        $LineFive = ''
    ) {
        $InfoSlice = (new Slice());
        $InfoSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->styleBorderBottom()
                , '30%')
            ->addElementColumn((new Element())
                , '70%')
        )
            ->styleMarginTop($MarginTop);
        if ($LineOne !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineOne)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineTwo !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineTwo)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineThree !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineThree)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineFour !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineFour)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineFive !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineFive)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }

        return $InfoSlice;
    }

    /**
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getGradeLanes($TextSize = '14px', $IsGradeUnderlined = false, $MarginTop = '15px')
    {

        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym']
                    = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName']
                    = $tblGradeType->getName();

            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $GradeLayout = array();
        if ($GradeStructure) {
            ksort($GradeStructure);
            foreach ($GradeStructure as $GradeList) {
                ksort($GradeList);
                foreach ($GradeList as $Lane => $Grade) {
                    $GradeLayout[$LaneCounter[$Lane]][$Lane] = $Grade;
                    $LaneCounter[$Lane]++;
                }
            }
            $GradeStructure = $GradeLayout;

            foreach ($GradeStructure as $GradeList) {
                // Sort Lane-Ranking (1,2...)
                ksort($GradeList);

                $GradeSection = (new Section());

                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $GradeSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($GradeList as $Lane => $Grade) {

                    if ($Lane > 1) {
                        $GradeSection->addElementColumn((new Element())
                            , '4%');
                    }
                    $GradeSection->addElementColumn((new Element())
                        ->setContent($Grade['GradeName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '39%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                         {{ Content.Input["' . $Grade['GradeAcronym'] . '"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '9%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn(( new Element() ), '52%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop($MarginTop);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param string $TextSize
     * @param bool   $IsGradeUnderlined
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getGradeLanesCoswig($TextSize = '14px', $IsGradeUnderlined = false, $MarginTop = '15px')
    {

        $GradeSlice = ( new Slice() );

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym']
                    = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName']
                    = $tblGradeType->getName();

            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $GradeLayout = array();
        if ($GradeStructure) {
            ksort($GradeStructure);
            foreach ($GradeStructure as $GradeList) {
                ksort($GradeList);
                foreach ($GradeList as $Lane => $Grade) {
                    $GradeLayout[$LaneCounter[$Lane]][$Lane] = $Grade;
                    $LaneCounter[$Lane]++;
                }
            }
            $GradeStructure = $GradeLayout;

            foreach ($GradeStructure as $GradeList) {
                // Sort Lane-Ranking (1,2...)
                ksort($GradeList);

                $GradeSection = ( new Section() );

                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $GradeSection->addElementColumn(( new Element() ), 'auto');
                }

                foreach ($GradeList as $Lane => $Grade) {

                    if ($Lane > 1) {
                        $GradeSection->addElementColumn(( new Element() )
                            , '4%');
                    }
                    $GradeSection->addElementColumn(( new Element() )
                        ->setContent($Grade['GradeName'])
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '39%');
                    $GradeSection->addElementColumn(( new Element() )
                        ->setContent('{% if(Content.Input["'.$Grade['GradeAcronym'].'"] is not empty) %}
                                         {{ Content.Input["'.$Grade['GradeAcronym'].'"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('10px')
                        ->styleTextSize($TextSize)
                        , '9%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '52%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop($MarginTop);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    public function getProfileStandard($TextSize = '14px', $IsGradeUnderlined = false)
    {

        $slice = new Slice();
        $sectionList = array();

        $tblSubject = false;

        $profileAppendText = 'Profil';

        // Profil
        if ($this->getTblPerson()
            && ($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);
            if (($tblSubjectProfile = $tblStudentSubject->getServiceTblSubject())) {
                $tblSubject = $tblSubjectProfile;

                if (strpos(strtolower($tblSubject->getName()), 'naturwissen') !== false
                    && $this->getTblDivision()
                    && $this->getTblDivision()->getTblLevel()
                    && !preg_match('!(0?(8))!is', $this->getTblDivision()->getTblLevel()->getName())
                ) {
                    $profileAppendText = 'Profil mit informatischer Bildung';
                }
            }
        }

        $foreignLanguageName = '&nbsp;';
        // 3. Fremdsprache
        if ($this->getTblPerson()
            && ($tblStudent = $this->getTblPerson()->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if ($tblStudentSubject->getTblStudentSubjectRanking()
                    && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '3'
                    && ($tblSubjectForeign = $tblStudentSubject->getServiceTblSubject())
                ) {
                    $foreignLanguageName = $tblSubjectForeign->getName();
                }
            }
        }

        if ($tblSubject) {
            // Todo noch richtig Klären erstmal fest für Chemnitz
            // $SubjectAcronym = str_replace(' ', '', $tblSubject-getAcronym());
            $SubjectAcronym = 'PRO';

            $elementName = (new Element())
                // Profilname aus der Schülerakte
                // bei einem Leerzeichen im Acronymn stürzt das TWIG ab
                ->setContent('
                   {% if(Content.Student.Profile.' . str_replace(' ', '', $tblSubject->getAcronym()) . ' is not empty) %}
                       {{ Content.Student.Profile.' . str_replace(' ', '', $tblSubject->getAcronym()) . '.Name' . ' }}
                   {% else %}
                        &nbsp;
                   {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('
                    {% if(Content.Grade.Data.' . $SubjectAcronym . ' is not empty) %}
                        {{ Content.Grade.Data.' . $SubjectAcronym . ' }}
                    {% else %}
                        &ndash;
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop('0px')
                ->stylePaddingBottom('0px')
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);
        } else {
            $elementName = (new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('&ndash;')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop('0px')
                ->stylePaddingBottom('0px')
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);
        }

        $marginTop = '20px';

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Wahlpflichtbereich:')
                ->styleTextBold()
                ->styleMarginTop($marginTop)
                ->styleTextSize($TextSize)
                , '20%')
            ->addElementColumn($elementName
                ->styleMarginTop($marginTop)
                , '32%')
            ->addElementColumn((new Element())
                ->setContent($profileAppendText)
                ->styleMarginTop($marginTop)
                , '48%');
        $sectionList[] = $section;
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('besuchtes Profil')
                ->styleAlignCenter()
                ->styleTextSize('11px')
                , '32%')
            ->addElementColumn((new Element()), '48%');
        $sectionList[] = $section;

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Profil')
                ->styleTextSize($TextSize)
                ->styleMarginTop('5px')
                , '39%')
            ->addElementColumn($elementGrade
                ->styleMarginTop('5px')
                , '9%')
            ->addElementColumn((new Element())
                ->styleMarginTop('5px')
                , '4%')
            ->addElementColumn((new Element())
                ->styleMarginTop('5px')
                ->setContent($foreignLanguageName)
                ->styleBorderBottom()
                , '48%');
        $sectionList[] = $section;

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                , '52%')
            ->addElementColumn((new Element())
                ->setContent('Fremdsprache (ab Klassenstufe 8) im sprachlichen Profil')
                ->styleTextSize('11px')
                ->styleAlignCenter()
                , '48%');
        $sectionList[] = $section;


        return $slice->addSectionList($sectionList);
    }

    /**
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    public function getOrientationStandard($TextSize = '14px', $IsGradeUnderlined = false)
    {

        $marginTop = '5px';

        $slice = new Slice();
        $sectionList = array();

        $elementOrientationName = false;
        $elementOrientationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;
        if ($this->getTblPerson()
            && ($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))
        ) {

            // Neigungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblSubjectList);
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {

                    // Todo noch richtig Klären erstmal fest für Chemnitz
                    // $SubjectAcronym = $tblSubject-getAcronym();
                    $SubjectAcronym = 'NK';

                    $elementOrientationName = new Element();
                    $elementOrientationName
                        ->setContent('
                            {% if(Content.Student.Orientation.' . str_replace(' ', '', $tblSubject->getAcronym()) . ' is not empty) %}
                                 {{ Content.Student.Orientation.' . str_replace(' ', '', $tblSubject->getAcronym()) . '.Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize);

                    $elementOrientationGrade = new Element();
                    $elementOrientationGrade
                        ->setContent('
                            {% if(Content.Grade.Data.' . $SubjectAcronym . ' is not empty) %}
                                {{ Content.Grade.Data.' . $SubjectAcronym . ' }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize);
                }
            }

            // 2. Fremdsprache
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if ($tblStudentSubject->getTblStudentSubjectRanking()
                        && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                        && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    ) {
                        $elementForeignLanguageName = new Element();
                        $elementForeignLanguageName
                            ->setContent('
                            {% if(Content.Student.ForeignLanguage.' . $tblSubject->getAcronym() . ' is not empty) %}
                                 {{ Content.Student.ForeignLanguage.' . $tblSubject->getAcronym() . '.Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize);

                        $elementForeignLanguageGrade = new Element();
                        $elementForeignLanguageGrade
                            ->setContent('
                            {% if(Content.Grade.Data.' . $tblSubject->getAcronym() . ' is not empty) %}
                                {{ Content.Grade.Data.' . $tblSubject->getAcronym() . ' }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize);
                    }
                }
            }

            // aktuell immer anzeigen
//            if ($elementOrientationName || $elementForeignLanguageName) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('Wahlpflichtbereich:')
                    ->styleTextBold()
                    ->styleMarginTop('10px')
                    ->styleTextSize($TextSize)
                );
            $sectionList[] = $section;
//            }

            if ($elementOrientationName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementOrientationName, '91%')
                    ->addElementColumn($elementOrientationGrade, '9%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('<u>Neigungskurs (Neigungskursbereich)</u> / 2. Fremdsprache (abschlussorientiert)')
                        ->styleBorderTop()
                        ->styleMarginTop('0px')
                        ->stylePaddingTop()
                        ->styleTextSize('13px')
                        , '89%')
                    ->addElementColumn((new Element()), '11%');
                $sectionList[] = $section;
            } elseif ($elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementForeignLanguageName, '91%')
                    ->addElementColumn($elementForeignLanguageGrade, '9%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs (Neigungskursbereich) / <u>2. Fremdsprache (abschlussorientiert)</u>')
                        ->styleBorderTop()
                        ->styleMarginTop('0px')
                        ->stylePaddingTop()
                        ->styleTextSize('13px')
                        , '89%')
                    ->addElementColumn((new Element()), '11%');
                $sectionList[] = $section;
            } else {
                $elementName = (new Element())
                    ->setContent('---')
                    ->styleBorderBottom()
                    ->styleMarginTop($marginTop)
                    ->styleTextSize($TextSize);

                $elementGrade = (new Element())
                    ->setContent('&ndash;')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#BBB')
                    ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                    ->stylePaddingTop('0px')
                    ->stylePaddingBottom('0px')
                    ->styleMarginTop($marginTop)
                    ->styleTextSize($TextSize);

                $section = new Section();
                $section
                    ->addElementColumn($elementName
                        , '90%')
                    ->addElementColumn((new Element())
                        , '1%')
                    ->addElementColumn($elementGrade
                        , '9%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs (Neigungskursbereich)/2. Fremdsprache (abschlussorientiert)')
                        ->styleTextSize('11px')
                        , '50%');
                $sectionList[] = $section;
            }
        }

        return empty($sectionList) ? (new Slice())->styleHeight('60px') : $slice->addSectionList($sectionList);
    }

    /**
     * @param string $TextColor
     * @param string $TextSize
     * @param string $GradeFieldBackgroundColor
     * @param bool $IsGradeUnderlined
     * @param string $MarginTop
     * @param int $GradeFieldWidth
     * @param string $fontFamily
     *
     * @return Slice
     */
    protected function getGradeLanesForRadebeul(
        $TextColor = 'black',
        $TextSize = '13px',
        $GradeFieldBackgroundColor = 'rgb(224,226,231)',
        $IsGradeUnderlined = false,
        $MarginTop = '20px',
        $GradeFieldWidth = 28,
        $fontFamily = 'MetaPro'
    ) {

        $widthText = (50 - $GradeFieldWidth - 4) . '%';
        $widthGrade = $GradeFieldWidth . '%';

        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym']
                    = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName']
                    = $tblGradeType->getName();

            }
        }

        // Shrink Lanes
        $LaneCounter = array(1 => 0, 2 => 0);
        $GradeLayout = array();
        if ($GradeStructure) {
            ksort($GradeStructure);
            foreach ($GradeStructure as $GradeList) {
                ksort($GradeList);
                foreach ($GradeList as $Lane => $Grade) {
                    $GradeLayout[$LaneCounter[$Lane]][$Lane] = $Grade;
                    $LaneCounter[$Lane]++;
                }
            }
            $GradeStructure = $GradeLayout;

            foreach ($GradeStructure as $GradeList) {
                // Sort Lane-Ranking (1,2...)
                ksort($GradeList);

                $GradeSection = (new Section());

                if (count($GradeList) == 1 && isset($GradeList[2])) {
                    $GradeSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($GradeList as $Lane => $Grade) {

                    if ($Lane > 1) {
                        $GradeSection->addElementColumn((new Element())
                            , '8%');
                    }
                    $GradeSection->addElementColumn((new Element())
                        ->setContent($Grade['GradeName'] . ':')
                        ->styleTextColor($TextColor)
                        ->stylePaddingTop()
                        ->styleMarginTop('4px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily($fontFamily)
                        , $widthText);
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.Input["' . $Grade['GradeAcronym'] . '"] is not empty) %}
                                         {{ Content.Input["' . $Grade['GradeAcronym'] . '"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleTextColor($TextColor)
                        ->styleAlignCenter()
                        ->styleBackgroundColor($GradeFieldBackgroundColor)
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', $TextColor)
                        ->stylePaddingTop('-4px')
                        ->stylePaddingBottom('2px')
                        ->styleMarginTop('8px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily($fontFamily)
                        , $widthGrade);
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '54%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop($MarginTop);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param string $TextColor
     * @param string $TextSize
     * @param string $GradeFieldBackgroundColor
     * @param bool $IsGradeUnderlined
     * @param string $MarginTop
     * @param int $GradeFieldWidth
     * @param string $fontFamily
     *
     * @return Slice
     */
    protected function getSubjectLanesForRadebeul(
        $TextColor = 'black',
        $TextSize = '13px',
        $GradeFieldBackgroundColor = 'rgb(224,226,231)',
        $IsGradeUnderlined = false,
        $MarginTop = '8px',
        $GradeFieldWidth = 28,
        $fontFamily = 'MetaPro'
    ) {

        $widthText = (50 - $GradeFieldWidth - 4) . '%';
        $widthGrade = $GradeFieldWidth . '%';

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();

                // Grade Exists? => Add Subject to Certificate
                if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                        = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                        = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();

                        // Liberation?
                        if (
                            $this->getTblPerson()
                            && ($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))
                            && ($tblStudentLiberationCategory = $tblCertificateSubject->getServiceTblStudentLiberationCategory())
                        ) {
                            $tblStudentLiberationAll = Student::useService()->getStudentLiberationAllByStudent($tblStudent);
                            if ($tblStudentLiberationAll) {
                                foreach ($tblStudentLiberationAll as $tblStudentLiberation) {
                                    if (($tblStudentLiberationType = $tblStudentLiberation->getTblStudentLiberationType())) {
                                        $tblStudentLiberationType->getTblStudentLiberationCategory();
                                        if ($tblStudentLiberationCategory->getId() == $tblStudentLiberationType->getTblStudentLiberationCategory()->getId()) {
                                            $this->Grade['Data'][$tblSubject->getAcronym()] = $tblStudentLiberationType->getName();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Shrink Lanes
            $LaneCounter = array(1 => 0, 2 => 0);
            $SubjectLayout = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $SubjectList) {
                ksort($SubjectList);
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectLayout[$LaneCounter[$Lane]][$Lane] = $Subject;
                    $LaneCounter[$Lane]++;
                }
            }
            $SubjectStructure = $SubjectLayout;

            $count = 0;
            foreach ($SubjectStructure as $SubjectList) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                $count++;

                foreach ($SubjectList as $Lane => $Subject) {
                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '8%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'] . ':')
                        ->styleTextColor($TextColor)
                        ->stylePaddingTop()
                        ->styleMarginTop($count == 1 ? $MarginTop : '4px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily($fontFamily)
                        , $widthText);

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty) %}
                                             {{ Content.Grade.Data["' . $Subject['SubjectAcronym'] . '"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleTextColor($TextColor)
                        ->styleAlignCenter()
                        ->styleBackgroundColor($GradeFieldBackgroundColor)
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', $TextColor)
                        ->stylePaddingTop('-4px')
                        ->stylePaddingBottom('2px')
                        ->styleMarginTop($count == 1 ? '14px' : '8px')
                        ->styleTextSize($TextSize)
                        ->styleFontFamily($fontFamily)
                        , $widthGrade);
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '54%');
                }

                $SubjectSlice->addSection($SubjectSection);
                $SectionList[] = $SubjectSection;
            }
        }

        return $SubjectSlice;
    }
}
