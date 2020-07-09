<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.10.2017
 * Time: 09:51
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GymKurshalbjahreszeugnis extends Certificate
{

    /**
     * @var array|false
     */
    private $AdvancedCourses = false;

    /**
     * @var array|false
     */
    private $BasicCourses = false;

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample(), false);

        $this->setCourses($tblPerson);

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Kurshalbjahreszeugnis'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Kurshalbjahr')
                        , '7%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '  {% if(Content.P' . $personId . '.Division.Data.Course.Name is not empty) %}
                                {{ Content.P' . $personId . '.Division.Data.Course.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        , '15%')
                    ->addElementColumn((new Element())
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr')
                        ->styleAlignRight()
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        , '15%')
                )->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                              {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern¹:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungskurse')
                    ->styleMarginTop('10px')
                )
            )
            ->addSlice($this->getAdvancedCourses($tblPerson))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Grundkurse')
                    ->styleMarginTop('10px')
                )
            )
            ->addSlice($this->getBasicCourses($tblPerson))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Die Schülerin
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Der Schüler
                                {% else %}
                                    Die Schülerin/Der Schüler¹ kann ihre/seine¹ Ausbildung am Gymnasium fortsetzen.
                                {% endif %}
                            {% endif %}
                            erbringt eine Besondere Lernleistung mit dem Thema:'
                        )
                        ->styleMarginTop('10px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.Input.BellSubject is not empty) %}
                                {{ Content.P' . $personId . '.Input.BellSubject }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleBorderBottom()
                        ->styleMarginTop('5px')
                    )
                )
            )
            ->addSlice($this->getDescriptionContent($personId, '45px', '10px', 'Bemerkungen: &nbsp;&nbsp;&nbsp; ', '11pt'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getOwnSignPart($personId))
            ->addSlice($this->getParentSign())
            ->addSlice($this->setPointsOverview())
            ->addSlice($this->getInfo('10px',
                '¹ &nbsp;&nbsp;&nbsp;&nbsp; Bei Fächern, die nicht belegt wurden, ist das betreffende Feld zu sperren.',
                '² &nbsp;&nbsp;&nbsp;&nbsp; für Schüler der vertieften Ausbildung nach § 4 der Schulordnung Gymnasien Abiturprüfung'
            //,'³ &nbsp;&nbsp;&nbsp;&nbsp; Nichtzutreffendes ist zu streichen.  '
            )
            );
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    private function setCourses(TblPerson $tblPerson = null)
    {

        $advancedCourses = array();
        $basicCourses = array();
        if ($tblPerson && ($tblDivision = $this->getTblDivision())
            && ($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))
        ) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                if (($tblSubjectGroup = $tblDivisionSubjectItem->getTblSubjectGroup())) {

                    if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject(
                        $tblDivisionSubjectItem))
                    ) {
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            if (($tblSubject = $tblDivisionSubjectItem->getServiceTblSubject())
                                && ($tblPersonStudent = $tblSubjectStudent->getServiceTblPerson())
                                && $tblPerson->getId() == $tblPersonStudent->getId()
                            ) {
                                if ($tblSubjectGroup->isAdvancedCourse()) {
                                    if ($tblSubject->getName() == 'Deutsch' || $tblSubject->getName() == 'Mathematik') {
                                        $advancedCourses[0] = $tblSubject->getAcronym();
                                    } else {
                                        $advancedCourses[1] = $tblSubject->getAcronym();
                                    }
                                } else {
                                    $basicCourses[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($advancedCourses)) {
            $this->AdvancedCourses = $advancedCourses;
        }
        if (!empty($basicCourses)) {
            $this->BasicCourses = $basicCourses;
        }
    }

    private function getAdvancedCourses(TblPerson $tblPerson = null, $IsGradeUnderlined = true)
    {

        $slice = new Slice();
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $section = new Section();
        if ($this->AdvancedCourses && isset($this->AdvancedCourses[0])
            && ($tblSubject = Subject::useService()->getSubjectByAcronym($this->AdvancedCourses[0]))
        ) {
            $this->setCourseSubject($tblSubject, $section, true, $IsGradeUnderlined, $personId);
        } else {
            $this->setCourseSubject(null, $section, true, $IsGradeUnderlined, $personId);
        }
        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '8%');
        $this->setCourseSubject(null, $section, true, $IsGradeUnderlined, $personId, '10px', true);
        $slice->addSection($section);

        $section = new Section();
        if ($this->AdvancedCourses && isset($this->AdvancedCourses[1])
            && ($tblSubject = Subject::useService()->getSubjectByAcronym($this->AdvancedCourses[1]))
        ) {
            $this->setCourseSubject($tblSubject, $section, true, $IsGradeUnderlined, $personId);
        } else {
            $this->setCourseSubject(null, $section, true, $IsGradeUnderlined, $personId);
        }
        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
            );
        $slice->addSection($section);

        return $slice;
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    private function getBasicCourses(TblPerson $tblPerson = null, $IsGradeUnderlined = true)
    {

        $slice = new Slice();
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $SubjectStructure = array();

        if (($tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity()))) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $isAddSubject = false;
                    // Student has basicCourse? => Add Subject to Certificate
                    if (isset($this->BasicCourses[$tblSubject->getAcronym()])) {
                        $isAddSubject = true;
                    } else {
                        // Grade Missing, But Subject Essential => Add Subject to Certificate
                        if ($tblCertificateSubject->isEssential()) {
                            $isAddSubject = true;
                        }
                    }

                    if ($isAddSubject) {
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$tblCertificateSubject->getRanking()][$tblCertificateSubject->getLane()]['SubjectName']
                            = $tblSubject->getName();
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
        }

        $count = 0;
        $subSection = false;
        $isShrinkMarginTop = false;
        foreach ($SubjectStructure as $SubjectList) {
            $count++;
            // Sort Lane-Ranking (1,2...)
            ksort($SubjectList);

            $section = new Section();
            if (count($SubjectList) == 1 && isset($SubjectList[2])) {
                $section->addElementColumn((new Element()), 'auto');
                $isSecondLane = true;
            } else {
                $isSecondLane = false;
            }

            foreach ($SubjectList as $Lane => $Subject) {
                if (($tblSubject = Subject::useService()->getSubjectByAcronym($Subject['SubjectAcronym']))) {
                    if (isset($this->AdvancedCourses[0]) && $this->AdvancedCourses[0] == $tblSubject->getAcronym()) {
                        $isAdvancedCourse = true;
                    } elseif (isset($this->AdvancedCourses[1]) && $this->AdvancedCourses[1] == $tblSubject->getAcronym()) {
                        $isAdvancedCourse = true;
                    } else {
                        $isAdvancedCourse = false;
                    }

                    if (($tblCategory = Subject::useService()->getCategoryByIdentifier('FOREIGNLANGUAGE'))
                        && Subject::useService()->existsCategorySubject($tblCategory, $tblSubject)
                    ) {
                        $isLanguage = true;
                    } else {
                        $isLanguage = false;
                    }

                    $this->setCourseSubject($tblSubject, $section, false, $IsGradeUnderlined, $personId,
                        $isShrinkMarginTop ? '2px' : '10px', false, !$isAdvancedCourse, $isLanguage);

                    if ($isLanguage) {
                        $subSection = new Section();
                        $subSection
                            ->addElementColumn((new Element())
                                ->setContent('Fremdsprache')
                                ->styleMarginTop('0px')
                                ->styleMarginBottom('0px')
                                ->styleTextSize('8px')
                            );
                    }

                    if ($isSecondLane) {
                        $slice->addSection($section);
                        if ($subSection) {
                            $slice->addSection($subSection);
                            $subSection = false;
                            $isShrinkMarginTop = true;
                        } else {
                            $isShrinkMarginTop = false;
                        }
                        $section = new Section();
                    } else {
                        $section
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleMarginTop($isShrinkMarginTop ? '2px' : '10px')
                                , '8%');
                    }

                    $isSecondLane = !$isSecondLane;
                }
            }

            if ($isSecondLane) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                    );
                $slice->addSection($section);
            }
        }

        return $slice
            ->styleHeight('290px');
    }

    /**
     * @param TblSubject|null $tblSubject
     * @param Section $section
     * @param $isSubjectUnderlined
     * @param $isGradeUnderlined
     * @param $personId
     * @param string $marginTop
     * @param bool $isFootnote
     * @param bool $isGradeShown
     * @param bool $isLanguage
     */
    private function setCourseSubject(
        TblSubject $tblSubject = null,
        Section $section,
        $isSubjectUnderlined,
        $isGradeUnderlined,
        $personId,
        $marginTop = '10px',
        $isFootnote = false,
        $isGradeShown = true,
        $isLanguage = false
    ) {

        if ($tblSubject) {
            $section
                ->addElementColumn((new Element())
                    ->setContent($tblSubject->getName() == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft'
                        ? 'Gemeinschaftskunde/ Rechtserziehung/Wirtschaft' : $tblSubject->getName())
                    ->styleBorderBottom($isSubjectUnderlined || $isLanguage ? '1px' : '0px')
                    ->styleMarginTop($marginTop)
                    , '33%');
        } elseif ($isFootnote) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('---')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    ->styleMarginTop($marginTop)
                    , '31%');
            $section
                ->addElementColumn((new Element())
                    ->setContent('²')
                    ->styleBorderBottom()
                    ->styleAlignRight()
                    ->styleMarginTop($marginTop)
                    , '2%');
        } else {
            $section
                ->addElementColumn((new Element())
                    ->setContent('---')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    ->styleMarginTop($marginTop)
                    , '33%');
        }
        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleMarginTop($marginTop)
                , '1%')
            ->addElementColumn((new Element())
                ->setContent(
                    $tblSubject && $isGradeShown ?
                        '{% if(Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] }}
                        {% else %}
                             &ndash;
                        {% endif %}'
                        : '&ndash;')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->styleBorderBottom($isGradeUnderlined ? '1px' : '0px', '#000')
                ->styleMarginTop($marginTop)
                , '12%');
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    private function setPointsOverview($MarginTop = '20px')
    {

        $textSize = '10px';
        $slice = new Slice();
        $slice
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Für die Umsetzung der Noten in Punkte gilt:')
                    ->styleTextSize($textSize)
                )
            );

        $section = new Section();
        $this->setColumnElement($section, 'Notenstufen', $textSize);
        $this->setColumnElement($section, 'sehr gut', $textSize);
        $this->setColumnElement($section, 'gut', $textSize);
        $this->setColumnElement($section, 'befriedigend', $textSize);
        $this->setColumnElement($section, 'ausreichend', $textSize);
        $this->setColumnElement($section, 'mangelhaft', $textSize);
        $this->setColumnElement($section, 'ungenügend', $textSize, false, true);
        $slice
            ->addSection($section);

        $section = new Section();
        $this->setColumnElement($section, 'Noten', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;2&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;3&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;4&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;5&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '6', $textSize, false, true);
        $slice
            ->addSection($section);

        $section = new Section();
        $this->setColumnElement($section, 'Punkte', $textSize, true);
        $this->setColumnElement($section, '15 14 13', $textSize, true);
        $this->setColumnElement($section, '12 11 10', $textSize, true);
        $this->setColumnElement($section, '09 08 07', $textSize, true);
        $this->setColumnElement($section, '06 05 04', $textSize, true);
        $this->setColumnElement($section, '03 02 01', $textSize, true);
        $this->setColumnElement($section, '00', $textSize, true, true);
        $slice
            ->addSection($section);

        return $slice
            ->styleMarginTop($MarginTop);
    }

    /**
     * @param Section $section
     * @param string $name
     * @param $textSize
     * @param bool $isBorderBottom
     * @param bool $isBorderRight
     */
    private function setColumnElement(
        Section $section,
        $name,
        $textSize,
        $isBorderBottom = false,
        $isBorderRight = false
    ) {

        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderRight($isBorderRight ? '1px' : '0px')
                ->styleBorderBottom($isBorderBottom ? '1px' : '0px')
                , '14.28%');
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getOwnSignPart($personId, $MarginTop = '25px')
    {
        $SignSlice = (new Slice());

        $SignSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#000')
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('Dienstsiegel')
                ->styleAlignCenter()
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
                    ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}'
                    )
                    ->styleAlignCenter()
//                    ->styleTextSize('11px')
                    , '30%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('der Schule')
                    ->styleAlignCenter()
//                        ->styleTextSize('11px')
                    , '30%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if(Content.P' . $personId . '.Tudor.Description is not empty) %}
                                {{ Content.P' . $personId . '.Tudor.Description }}
                            {% else %}
                                Tutor(in)
                            {% endif %}'
                    )
                    ->styleAlignCenter()
//                        ->styleTextSize('11px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                    )
//                        ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                    )
//                        ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '30%')
            );

        return $SignSlice;
    }
}