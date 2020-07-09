<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 30.09.2016
 * Time: 14:45
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GradeInformation
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class GradeInformation extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null){

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice((new Slice())
                ->styleHeight('70px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Noteninformation für ')
                        ->styleTextItalic()
                        ->styleTextSize('15px')
                        ->styleTextBold()
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Data.Name is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                    {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize('13px')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                        , '45%')
                    ->addElementColumn((new Element())
                        ->setContent(', Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}
                        {{ Content.P' . $personId . '.Division.Data.Name }}, {{ Content.P' . $personId . '.Input.Date }}')
                        ->styleTextItalic()
                        ->styleTextSize('15px')
                        ->stylePaddingLeft('4px')
                        ->styleTextBold()
                        , '33%')
                )
            )
            ->addSlice($this->getGradeLanesForGradeInformation($tblPerson))
            ->addSlice($this->getSubjectLanesForGradeInformation($tblPerson))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Bemerkungen:')
                    )
                )
                ->styleMarginTop('25px')
            )
            ->addSlice($this->getDescriptionContent($personId, '100px', '5px'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Unterschrift des Klassenlehrers:')
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom()
                        , '50%')
                    ->addElementColumn((new Element())
                        , '20%')
                )->styleMarginTop('40px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Unterschrift der Eltern:')
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom()
                        , '50%')
                    ->addElementColumn((new Element())
                        , '20%')
                )->styleMarginTop('40px')
            );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Slice
     */
    protected function getGradeLanesForGradeInformation(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $slice = (new Slice());

        $subjectList = $this->getSubjectList($tblPerson);

        if (!empty($subjectList)) {
            ksort($subjectList);
            $count = count($subjectList);
        } else {
            $count = 1;
        }

        $paddingLeft = '0px';
        $columnWidth = floor(90 / ($count + 1));
        $leftWidth = 100 - (($count + 1) * $columnWidth);

        $section = new Section();
        $top = '30px';
        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleMarginTop($top)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderBottom()
                , $leftWidth . '%')
            ->addElementColumn((new Element())
                ->setContent('KL')
                ->styleMarginTop($top)
                ->styleAlignCenter()
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderBottom()
                , $columnWidth . '%')
            ->addElementColumn((new Element())
                ->setContent('Fachlehrer')
                ->styleMarginTop($top)
                ->stylePaddingLeft('5px')
                ->styleBorderAll()
                , ($count * $columnWidth) . '%');
        $slice->addSection($section);

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderLeft()
                ->styleBorderBottom()
                , $leftWidth . '%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderLeft()
                ->styleBorderBottom()
                , $columnWidth . '%');
        $index = 0;
        /** @var TblSubject $tblSubject */
        foreach ($subjectList as $tblSubject) {
            $index++;
            if ($index == $count) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent($tblSubject->getAcronym())
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        , $columnWidth . '%');
            } else {
                $section
                    ->addElementColumn((new Element())
                        ->setContent($tblSubject->getAcronym())
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        , $columnWidth . '%');
            }
        }
        $slice->addSection($section);

        $gradeTypeList = array();
        $tblCertificateGradeTypeAll = Generator::useService()->getCertificateGradeAll($this->getCertificateEntity());
        if ($tblCertificateGradeTypeAll) {
            foreach ($tblCertificateGradeTypeAll as $tblCertificateGradeType) {
                if (($tblGradeType = $tblCertificateGradeType->getServiceTblGradeType())) {
                    $gradeTypeList[] = $tblGradeType;
                }
            }
        }
        if (!empty($gradeTypeList)) {
            /** @var TblGradeType $gradeType */
            foreach ($gradeTypeList as $gradeType) {
                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent($gradeType->getName())
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        , $leftWidth . '%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Input["'.$gradeType->getCode().'"] is not empty) %}
                                 {{ Content.P' . $personId . '.Input["'.$gradeType->getCode().'"] }}
                            {% else %}
                                 &nbsp;
                            {% endif %}
                        ')
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        , $columnWidth . '%');
                $index = 0;
                foreach ($subjectList as $tblSubject) {
                    $index++;
                    $content = '{% if(Content.P' . $personId . '.Input.BehaviorTeacher["'.$tblSubject->getAcronym().'"]["'.$gradeType->getCode().'"] is not empty) %}
                                    {{ Content.P' . $personId . '.Input.BehaviorTeacher["'.$tblSubject->getAcronym().'"]["'.$gradeType->getCode().'"] }}
                                {% else %}
                                    &nbsp;
                                {% endif %}';
                    if ($index == $count) {
                        $section
                            ->addElementColumn((new Element())
                                ->setContent($content)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleBorderLeft()
                                ->styleBorderBottom()
                                ->styleBorderRight()
                                ->styleAlignCenter()
                                , $columnWidth . '%');
                    } else {
                        $section
                            ->addElementColumn((new Element())
                                ->setContent($content)
                                ->stylePaddingLeft($paddingLeft)
                                ->styleBorderLeft()
                                ->styleBorderBottom()
                                ->styleAlignCenter()
                                , $columnWidth . '%');
                    }
                }
                $slice->addSection($section);
            }
        }

        return $slice;
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Slice
     */
    protected function getSubjectLanesForGradeInformation(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $slice = (new Slice());

        $subjectList = $this->getSubjectList($tblPerson);
        if (!empty($subjectList)) {
            ksort($subjectList);
        }

        $paddingLeft = '5px';
        $paddingTop = '2px';

        $section = new Section();
        $top = '30px';
//        $height = '50px';
        $height = '30px';
        $fontSize = '17px';
        $section
            ->addElementColumn((new Element())
                ->setContent('Fächer')
                ->styleMarginTop($top)
                ->stylePaddingLeft($paddingLeft)
                ->stylePaddingTop('5px')
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderBottom()
                ->styleBackgroundColor('#BBB')
                ->styleHeight($height)
                ->styleTextSize($fontSize)
                , '50%')
            ->addElementColumn((new Element())
                ->setContent('derzeitige Note')
                ->styleMarginTop($top)
                ->stylePaddingLeft($paddingLeft)
                ->stylePaddingTop('5px')
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderBottom()
                ->styleBorderRight()
                ->styleBackgroundColor('#BBB')
                ->styleHeight($height)
                ->styleAlignCenter()
                ->styleTextSize($fontSize)
                , '50%');
//            ->addElementColumn((new Element())
//                ->setContent('Bemerkungen, vergessene' . '<br>' . 'Arbeitsmittel')
//                ->styleMarginTop($top)
//                ->stylePaddingLeft($paddingLeft)
//                ->styleBorderAll()
//                ->styleBackgroundColor('#BBB')
//                ->styleHeight($height)
//                ->styleAlignCenter()
//                ->styleTextSize($fontSize)
//                , '40%');
        $slice->addSection($section);

        $heightRow = '20px';
        /** @var TblSubject $subject */
        foreach ($subjectList as $subject) {
            $section = new Section();

            $section
                ->addElementColumn((new Element())
                    ->setContent($subject->getName())
                    ->stylePaddingLeft($paddingLeft)
                    ->stylePaddingTop($paddingTop)
                    ->styleBorderLeft()
                    ->styleBorderBottom()
                    ->styleHeight($heightRow)
                    , '50%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Grade.Data["'.$subject->getAcronym().'"] is not empty) %}
                            {{ Content.P' . $personId . '.Grade.Data["'.$subject->getAcronym().'"] }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingLeft('10px')
                    ->stylePaddingTop($paddingTop)
                    ->styleBorderLeft()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    ->styleHeight($heightRow)
                    , '50%');
//                ->addElementColumn((new Element())
//                    ->setContent('&nbsp;')
//                    ->stylePaddingLeft($paddingLeft)
//                    ->styleBorderLeft()
//                    ->styleBorderBottom()
//                    ->styleBorderRight()
//                    ->styleHeight($heightRow)
//                    , '40%');
            $slice->addSection($section);
        }

        return $slice;
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return array
     */
    private function getSubjectList(TblPerson $tblPerson = null)
    {
        $subjectList = array();
        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        if ($tblCertificateSubjectAll) {
            /** @var TblCertificateSubject $tblCertificateSubject */
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                if (($tblSubject = $tblCertificateSubject->getServiceTblSubject())) {
                    if ($tblCertificateSubject->isEssential()) {
                        $subjectList[$tblCertificateSubject->getRanking()] = $tblSubject;

                        // Überprüfen ob der Schüler dieses Fach im Unterricht hat --> dann anzeigen
                    } elseif ($tblPerson && ($tblDivision = $this->getTblDivision())) {
                        // in Gruppe
                        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                            $tblDivision, $tblSubject
                        ))
                        ) {
                            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                                if (Division::useService()->getSubjectStudentByDivisionSubjectAndPerson(
                                    $tblDivisionSubjectItem, $tblPerson
                                )
                                ) {
                                    $subjectList[$tblCertificateSubject->getRanking()] = $tblSubject;
                                    break;
                                }
                            }
                        // keine Gruppe und in der Klasse
                        } elseif ($tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                            $tblDivision, $tblSubject
                        )
                        ) {
                            $subjectList[$tblCertificateSubject->getRanking()] = $tblSubject;
                        }
                    }
                }
            }
            return $subjectList;
        }
        return $subjectList;
    }
}