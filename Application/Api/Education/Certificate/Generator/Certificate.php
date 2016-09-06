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
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
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
            $Data['Company']['Data']['Name'] = $tblCompany->getDisplayName();
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
     * @param bool|true $isSlice
     * @param array $languagesWithStartLevel
     *
     * @return \SPHERE\Application\Education\Certificate\Generator\Repository\Section[]|Slice
     * @throws \Exception
     */
    protected function getSubjectLanes($isSlice = true, $languagesWithStartLevel = array())
    {

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
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym'] = $tblSubject->getAcronym();
                    $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName'] = $tblSubject->getName();
                } else {
                    // Grade Missing, But Subject Essential => Add Subject to Certificate
                    if ($tblCertificateSubject->isEssential()) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym'] = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName'] = $tblSubject->getName();

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

            $hasAdditionalLine = false;
            $isShrinkMarginTop = false;

            foreach ($SubjectStructure as $SubjectList) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    // Fremdsprache ab Klassenstufe
                    if (isset($languagesWithStartLevel[$Subject['SubjectAcronym']])) {
                        $hasAdditionalLine = $languagesWithStartLevel[$Subject['SubjectAcronym']];
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
                            , '37%');
                        $SubjectSection->addElementColumn((new Element()), '2%');
                    } elseif ($isShrinkMarginTop) {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('0px')
                            , '39%');
                    } else {
                        $SubjectSection->addElementColumn((new Element())
                            ->setContent($Subject['SubjectName'])
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            , '39%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.Grade.Data.' . $Subject['SubjectAcronym'] . ' is not empty) %}
                                             {{ Content.Grade.Data.' . $Subject['SubjectAcronym'] . ' }}
                                         {% else %}
                                             ---
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom('1px', '#000')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom()
                        ->styleMarginTop($isShrinkMarginTop ? '0px' : '10px')
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
                            '{% if(Content.Subject.Level.' . $hasAdditionalLine['SubjectAcronym'] . ' is not empty) %}
                                     {{ Content.Subject.Level.' . $hasAdditionalLine['SubjectAcronym'] . ' }}
                                 {% else %}
                                    -
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
                    $isShrinkMarginTop = true;

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
     * @return Slice
     * @throws \Exception
     */
    protected
    function getGradeLanes()
    {

        $GradeSlice = (new Slice());

        $tblCertificateGradeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        $GradeStructure = array();
        if (!empty($tblCertificateGradeAll)) {
            foreach ($tblCertificateGradeAll as $tblCertificateGrade) {
                $tblGradeType = $tblCertificateGrade->getServiceTblGradeType();

                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeAcronym'] = $tblGradeType->getCode();
                $GradeStructure[$tblCertificateGrade->getRanking()][$tblCertificateGrade->getLane()]['GradeName'] = $tblGradeType->getName();

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
                        , '39%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.Input.' . $Grade['GradeAcronym'] . ' is not empty) %}
                                         {{ Content.Input.' . $Grade['GradeAcronym'] . ' }}
                                     {% else %}
                                         ---
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom('1px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('10px')
                        , '9%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '52%');
                }

                $GradeSlice->addSection($GradeSection)->styleMarginTop('15px');
            }
        }

        return $GradeSlice;
    }
}
