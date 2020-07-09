<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class CmsStyle
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS
 */
abstract class CmsStyle extends Certificate
{

    const TEXT_SIZE = '10pt';

    /**
     * @param int $PictureHeight
     *
     * @return Slice
     */
    public function getCMSHead($PictureHeight = 100)
    {

        if ($this->isSample()) {
            $Header = (new Slice)->addSection((new Section())
                ->addElementColumn((new Element\Sample())
                    ->styleTextSize('30px')
                    ->stylePaddingTop('20px')
                    , '33%')
                ->addElementColumn((new Element\Image('Common/Style/Resource/Logo/CMS.jpg',
                    'auto', $PictureHeight.'px'))
                    ->styleAlignCenter()
                    , '34%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '33%')
            );

        } else {
            $Header = (new Slice)->addSection((new Section())
                ->addElementColumn((new Element())
                    , '33%')
                ->addElementColumn((new Element\Image('Common/Style/Resource/Logo/CMS.jpg',
                    'auto', $PictureHeight.'px'))
                    ->styleAlignCenter()
                    , '34%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '33%')
            );
        }
        return $Header;
    }

    /**
     * @param $personId
     *
     * @return Section[]
     */
    public function getCMSSchoolLine($personId)
    {

        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Company.Data.Name) %}
                    {{ Content.P'.$personId.'.Company.Data.Name }}
                {% else %}
                      &nbsp;
                {% endif %}')
            ->styleTextSize(self::TEXT_SIZE)
            ->styleAlignCenter()
        );
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Company.Data.ExtendedName) %}
                    {{ Content.P'.$personId.'.Company.Data.ExtendedName }}
                {% else %}
                      &nbsp;
                {% endif %}')
            ->styleTextSize(self::TEXT_SIZE)
            ->styleAlignCenter()
            ->stylePaddingTop()
            ->stylePaddingBottom()
            ->styleBorderBottom()
        );

        return $SectionList;
    }

    /**
     * @param string $Content
     *
     * @return Section
     */
    public function getCMSHeadLine($Content = '')
    {

        $Section = new Section();
        return $Section->addElementColumn((new Element())
            ->setContent($Content)
            ->styleTextSize('14pt')
            ->styleTextBold()
            ->styleAlignCenter()
        );
    }

    /**
     * @param int    $personId
     * @param string $YearString
     *
     * @return Section
     */
    public function getCMSDivisionAndYear($personId, $YearString = 'Schuljahr')
    {

        $Section = (new Section());
        $Section->addElementColumn((new Element())
            ->setContent('Klasse:')
            , '7%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Division.Data.Level.Name }}{{ Content.P'.$personId.'.Division.Data.Name }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '7%')
            ->addElementColumn((new Element())
                , '55%')
            ->addElementColumn((new Element())
                ->setContent($YearString.':')
                ->styleAlignRight()
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Division.Data.Year }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '13%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSName($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Vorname und Name:')
            , '21%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                              {{ Content.P'.$personId.'.Person.Data.Name.Last }}')
                ->styleBorderBottom()
                ->stylePaddingLeft('7px')
                , '79%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSExtendedName($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Beiblatt zum Zeugnis für:')
            , '24%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                              {{ Content.P'.$personId.'.Person.Data.Name.Last }}')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->stylePaddingRight('120px')
                ->stylePaddingLeft('7px')
                , '76%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSNameExtraPaper($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Beiblatt zum Zeugnis für:')
            , '21%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P'.$personId.'.Person.Data.Name.First }}
                              {{ Content.P'.$personId.'.Person.Data.Name.Last }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '79%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    protected function getCMSHeadGrade($personId)
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
                        , '39%');
                    $GradeSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input["'.$Grade['GradeAcronym'].'"] is not empty) %}
                                         {{ Content.P'.$personId.'.Input["'.$Grade['GradeAcronym'].'"] }}
                                     {% else %}
                                         &ndash;
                                     {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->styleMarginTop('10px')
                        , '9%');
                }

                if (count($GradeList) == 1 && isset($GradeList[1])) {
                    $GradeSection->addElementColumn((new Element()), '52%');
                }

                $GradeSlice->addSection($GradeSection);
            }
        }

        return $GradeSlice;
    }

    /**
     * @param int    $personId
     * @param bool   $IsHeadline
     * @param string $Height
     *
     * @return Slice
     */
    public function getCMSSubjectLanes($personId, $IsHeadline = true, $Height = '270px')
    {

        $SubjectSlice = (new Slice());

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        $tblGradeList = $this->getGrade();

        $SectionList = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
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

            if ($IsHeadline) {
                $HeaderSection = (new Section());
                $HeaderSection->addElementColumn((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleTextSize('10pt')
                    ->styleTextBold()
                    ->stylePaddingTop('10px')
                );
                $SectionList[] = $HeaderSection;
            }

            // shrink definition parameter
            $TextSize = '14px';
            $TextSizeSmall = '8.5px';
            $paddingTopShrinking = '5px';
            $paddingBottomShrinking = '6px';
            foreach ($SubjectStructure as $SubjectList) {
                $count++;
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);

                $SubjectSection = (new Section());

                if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {

                    if ($Lane > 1) {
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }
                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('10px')
                        , '39%');


                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                                         {% else %}
                                             &ndash;
                                         {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleMarginTop('10px')
                        ->stylePaddingTop(
                            '{% if((Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$Subject['SubjectAcronym'].'"] is not empty)
                                and (Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty)
                            ) %}
                                '.$paddingTopShrinking.' 
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if((Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$Subject['SubjectAcronym'].'"] is not empty)
                                and (Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty)
                            ) %}
                               '.$paddingBottomShrinking.' 
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleTextSize(
                            '{% if((Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$Subject['SubjectAcronym'].'"] is not empty)
                                and (Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty)
                            ) %}
                                 '.$TextSizeSmall.'
                             {% else %}
                                 '.$TextSize.'
                             {% endif %}'
                        )
                        , '9%');
                }

                if (count($SubjectList) == 1 && isset($SubjectList[1])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }
                $SectionList[] = $SubjectSection;
            }
            return $SubjectSlice->addSectionList($SectionList)
                ->styleHeight($Height);
        }

        return $SubjectSlice;
    }

    /**
     * @param        $personId
     * @param string $TextSize
     * @param bool   $IsGradeUnderlined
     *
     * @return Slice
     */
    public function getCMSOrientationStandard($personId, $TextSize = '14px', $IsGradeUnderlined = false)
    {

        $tblPerson = Person::useService()->getPersonById($personId);

        $marginTop = '3px';

        $slice = new Slice();
        $sectionList = array();

        $elementOrientationName = false;
        $elementOrientationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;

        // Zeugnisnoten im Wortlaut auf Abschlusszeugnissen --> breiter Zensurenfelder
        if (($tblCertificate = $this->getCertificateEntity())
            && ($tblCertificateType = $tblCertificate->getTblCertificateType())
            && ($tblCertificateType->getIdentifier() == 'DIPLOMA')
            && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
            && $tblSetting->getValue()
        ) {
            $subjectWidth = 89;
            $gradeWidth = 11;
            $TextSizeSmall = '13px';
            $paddingTopShrinking = '4px';
            $paddingBottomShrinking = '4px';
        } else {
            $subjectWidth = 91;
            $gradeWidth = 9;
            $TextSizeSmall = '8.5px';
            $paddingTopShrinking = '5px';
            $paddingBottomShrinking = '6px';
        }

        if ($tblPerson
            && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
        ) {

            // Neigungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblSubjectList);
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {

                    if (($tblSetting = Consumer::useService()->getSetting('Api', 'Education', 'Certificate',
                            'OrientationAcronym'))
                        && ($value = $tblSetting->getValue())
                    ) {
                        $subjectAcronymForGrade = $value;
                    } else {
                        $subjectAcronymForGrade = $tblSubject->getAcronym();
                    }

                    $elementOrientationName = new Element();
                    $elementOrientationName
                        ->setContent('
                            {% if(Content.P'.$personId.'.Student.Orientation["'.$tblSubject->getAcronym().'"] is not empty) %}
                                 {{ Content.P'.$personId.'.Student.Orientation["'.$tblSubject->getAcronym().'"].Name'.' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop('7px')
                        ->styleTextSize($TextSize);

                    $elementOrientationGrade = new Element();
                    $elementOrientationGrade
                        ->setContent('
                            {% if(Content.P'.$personId.'.Grade.Data["'.$subjectAcronymForGrade.'"] is not empty) %}
                                {{ Content.P'.$personId.'.Grade.Data["'.$subjectAcronymForGrade.'"] }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop(
                            '{% if(Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$subjectAcronymForGrade.'"] is not empty) %}
                                 '.$paddingTopShrinking.' 
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->stylePaddingBottom(
                            '{% if(Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$subjectAcronymForGrade.'"] is not empty) %}
                                  '.$paddingBottomShrinking.' 
                             {% else %}
                                 2px
                             {% endif %}'
                        )
                        ->styleTextSize(
                            '{% if(Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$subjectAcronymForGrade.'"] is not empty) %}
                                 '.$TextSizeSmall.'
                             {% else %}
                                 '.$TextSize.'
                             {% endif %}'
                        )
                        ->styleMarginTop($marginTop);
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
                            {% if(Content.P'.$personId.'.Student.ForeignLanguage["'.$tblSubject->getAcronym().'"] is not empty) %}
                                 {{ Content.P'.$personId.'.Student.ForeignLanguage["'.$tblSubject->getAcronym().'"].Name'.' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop('7px')
                            ->styleTextSize($TextSize);

                        $elementForeignLanguageGrade = new Element();
                        $elementForeignLanguageGrade
                            ->setContent('
                            {% if(Content.P'.$personId.'.Grade.Data["'.$tblSubject->getAcronym().'"] is not empty) %}
                                {{ Content.P'.$personId.'.Grade.Data["'.$tblSubject->getAcronym().'"] }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                            ->stylePaddingTop(
                                '{% if(Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$tblSubject->getAcronym().'"] is not empty) %}
                                 '.$paddingTopShrinking.' 
                             {% else %}
                                 2px
                             {% endif %}'
                            )
                            ->stylePaddingBottom(
                                '{% if(Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$tblSubject->getAcronym().'"] is not empty) %}
                                  '.$paddingBottomShrinking.' 
                             {% else %}
                                 2px
                             {% endif %}'
                            )
                            ->styleTextSize(
                                '{% if(Content.P'.$personId.'.Grade.Data.IsShrinkSize["'.$tblSubject->getAcronym().'"] is not empty) %}
                                 '.$TextSizeSmall.'
                             {% else %}
                                 '.$TextSize.'
                             {% endif %}'
                            )
                            ->styleMarginTop($marginTop);
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
                    ->styleMarginTop('10pt')
                    ->styleTextSize($TextSize)
                );
            $sectionList[] = $section;
//            }

            if ($elementOrientationName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementOrientationName, (string)$subjectWidth.'%')
                    ->addElementColumn($elementOrientationGrade, (string)$gradeWidth.'%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('<u>Neigungskurs (Neigungskursbereich)</u> / 2. Fremdsprache (abschlussorientiert)')
                        ->styleBorderTop()
                        ->styleMarginTop('0px')
                        ->stylePaddingTop()
                        ->styleTextSize('13px')
                        , (string)($subjectWidth - 2).'%')
                    ->addElementColumn((new Element()), (string)($gradeWidth + 2).'%');
                $sectionList[] = $section;
            } elseif ($elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementForeignLanguageName, (string)$subjectWidth.'%')
                    ->addElementColumn($elementForeignLanguageGrade, (string)$gradeWidth.'%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs (Neigungskursbereich) / <u>2. Fremdsprache (abschlussorientiert)</u>')
                        ->styleBorderTop()
                        ->styleMarginTop('0px')
                        ->stylePaddingTop()
                        ->styleTextSize('13px')
                        , (string)($subjectWidth - 2).'%')
                    ->addElementColumn((new Element()), (string)($gradeWidth + 2).'%');
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
        } else {

            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('Wahlpflichtbereich:')
                    ->styleTextBold()
                    ->styleMarginTop('10px')
                    ->styleTextSize($TextSize)
                );
            $sectionList[] = $section;

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

        return empty($sectionList) ? (new Slice())->styleHeight('60px') : $slice->addSectionList($sectionList);
    }

    /**
     * @param int    $personId
     * @param string $Height
     * @param bool   $IsHeadLine
     *
     * @return Section[]
     */
    public function getCMSRemark($personId, $Height = '100px', $IsHeadLine = false)
    {
        $SectionList = array();
        if ($IsHeadLine) {
            $Section = new Section();
            $Section->addElementColumn((new Element())
                ->setContent('Bemerkungen:')
                ->styleTextSize('10pt')
                ->styleTextBold()
            );
            $SectionList[] = $Section;
        }
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.Remark is not empty) %}
                    {{ Content.P'.$personId.'.Input.Remark|nl2br }}
                {% else %}
                    &nbsp;
                {% endif %}')
            ->styleAlignJustify()
//            ->styleBorderLeft()
            ->styleHeight($Height)
        );
        $SectionList[] = $Section;
        return $SectionList;
    }

    /**
     * @param int    $personId
     * @param string $Height
     * @param bool   $IsHeadLine
     *
     * @return Section[]
     */
    public function getCMSSecondRemark($personId, $Height = '100px', $IsHeadLine = false)
    {
        $SectionList = array();
        if ($IsHeadLine) {
            $Section = new Section();
            $Section->addElementColumn((new Element())
                ->setContent('Bemerkungen:')
                ->styleTextSize('10pt')
                ->styleTextBold()
            );
            $SectionList[] = $Section;
        }
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('{% if(Content.P'.$personId.'.Input.SecondRemark is not empty) %}
                    {{ Content.P'.$personId.'.Input.SecondRemark|nl2br }}
                {% else %}
                    &nbsp;
                {% endif %}')
            ->styleAlignJustify()
//            ->styleBorderLeft()
            ->styleHeight($Height)
        );
        $SectionList[] = $Section;
        return $SectionList;
    }

    public function getCMSMissing($personId)
    {

        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Fehltage entschuldigt:')
            , '21%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Missing is not empty) %}
                    {{ Content.P'.$personId.'.Input.Missing }}
                {% else %}
                    &nbsp;
                {% endif %}')
                ->styleAlignCenter()
                , '8%')
            ->addElementColumn((new Element())
                ->setContent('unentschuldigt:')
                , '13%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Bad.Missing is not empty) %}
                    &nbsp;{{ Content.P'.$personId.'.Input.Bad.Missing }}
                {% else %}
                    &nbsp;
                {% endif %}')
                ->styleAlignCenter()
                , '8%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '50%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSTransfer($personId)   // noch nicht angepasst
    {
        $Section = new Section();
        $Section->addElementColumn((new Element())
            ->setContent('Versetzungsvermerk:')
            , '21%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Transfer) %}
                                        {{ Content.P'.$personId.'.Input.Transfer }}.
                                    {% else %}
                                          &nbsp;
                                    {% endif %}')
                ->styleBorderBottom('1px')
                ->stylePaddingLeft('7px')
                , '58%')
            ->addElementColumn((new Element())
                , '20%');
        return $Section;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSDate($personId)
    {

        $Section = (new Section())->addElementColumn((new Element())
            ->setContent('Zwickau, den')
            , '15%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P'.$personId.'.Input.Date is not empty) %}
                            {{ Content.P'.$personId.'.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '20%')
            ->addElementColumn((new Element())
                , '65%');
        return $Section;
    }

    /**
     * @param        $personId
     * @param bool   $isExtended with directory and stamp
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCMSTeacher($personId, $isExtended = true, $MarginTop = '25px')
    {
        $SignSlice = (new Slice());
        if ($isExtended) {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Dienstsiegel der Schule')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P'.$personId.'.Headmaster.Description is not empty) %}
                                {{ Content.P'.$personId.'.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P'.$personId.'.DivisionTeacher.Description is not empty) %}
                                {{ Content.P'.$personId.'.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P'.$personId.'.Headmaster.Name is not empty) %}
                                {{ Content.P'.$personId.'.Headmaster.Name }}
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
                            '{% if(Content.P'.$personId.'.DivisionTeacher.Name is not empty) %}
                                {{ Content.P'.$personId.'.DivisionTeacher.Name }}
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
                    ->styleBorderBottom()
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('
                        {% if(Content.P'.$personId.'.DivisionTeacher.Description is not empty) %}
                                {{ Content.P'.$personId.'.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P'.$personId.'.DivisionTeacher.Name is not empty) %}
                                {{ Content.P'.$personId.'.DivisionTeacher.Name }}
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
     * @return Section[]
     */
    public function getCMSCustody()
    {

        $SectionList = array();
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent('Zur Kenntnis genommen:')
            , '25%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '50%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '25%');

        $SectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('Erziehungsberechtigte/r')
                ->styleTextSize('11px')
                ->styleAlignCenter()
            );

        return $SectionList;
    }

    /**
     * @return Section[]
     */
    public function getCMSFoot()
    {
        $SectionList = array();
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent('&nbsp;')
            ->styleTextSize('2px')
            ->styleBorderBottom()
            , '25%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleTextSize('2px')
                , '75%'
            );
        $SectionList[] = (new Section())->addElementColumn((new Element())
            ->setContent(
                'Notenerläuterung:'
                .new Container('Noten: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhalft,
                6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)')
            )
            ->styleTextSize('8.5px')
            ->stylePaddingTop()
        );
        return $SectionList;
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCMSCourse($personId)
    {

        $Section = (new Section())
            ->addElementColumn((new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.Student.Course.Degree is not empty) %}
                        nahm am Unterricht der Schulart Mittelschule mit dem Ziel des
                        {{ Content.P' . $personId . '.Student.Course.Degree }} teil.
                    {% else %}
                        nahm am Unterricht der Schulart Mittelschule teil.
                    {% endif %}'
                )
                ->styleMarginTop('15px')
            );
        return $Section;
    }
}