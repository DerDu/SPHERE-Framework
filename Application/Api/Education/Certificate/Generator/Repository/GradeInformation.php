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
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;

/**
 * Class GradeInformation
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class GradeInformation extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Noteninformation für ')
                            ->styleTextItalic()
                            ->styleTextSize('15px')
                            ->styleTextBold()
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Data.Name is not empty) %}
                                    {{ Content.Person.Data.Name.First }}
                                    {{ Content.Person.Data.Name.Last }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('13px')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '45%')
                        ->addElementColumn((new Element())
                            ->setContent(', Klasse {{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}, {{ Content.Input.Date }}')
                            ->styleTextItalic()
                            ->styleTextSize('15px')
                            ->stylePaddingLeft('4px')
                            ->styleTextBold()
                            , '33%')
                    )
                )
                ->addSlice($this->getGradeLanesForGradeInformation())
                ->addSlice($this->getSubjectLanesForGradeInformation())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Bemerkungen:')
                        )
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleHeight('250px')
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Unterschrift der Eltern:')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            , '55%')
                        ->addElementColumn((new Element())
                            , '20%')
                    )->styleMarginTop('75px')
                )
            )
        );
    }

    /**
     * @return Slice
     *
     * @throws \Exception
     */
    protected function getGradeLanesForGradeInformation()
    {

        $slice = (new Slice());

        $subjectList = array();
        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        if ($tblCertificateSubjectAll) {
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                if (($tblSubject = $tblCertificateSubject->getServiceTblSubject())) {
                    $subjectList[$tblCertificateSubject->getRanking()] = $tblSubject->getAcronym();
                }
            }
        }

        if (!empty($subjectList)) {
            ksort($subjectList);
            $count = count($subjectList);
        } else {
            $count = 1;
        }

        $paddingLeft = '5px';
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
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderBottom()
                , $columnWidth . '%')
            ->addElementColumn((new Element())
                ->setContent('Fachlehrer')
                ->styleMarginTop($top)
                ->stylePaddingLeft($paddingLeft)
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
        foreach ($subjectList as $subjectAcronym) {
            $index++;
            if ($index == $count) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent($subjectAcronym)
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        , $columnWidth . '%');
            } else {
                $section
                    ->addElementColumn((new Element())
                        ->setContent($subjectAcronym)
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
                            {% if(Content.Input.' . $gradeType->getCode() . ' is not empty) %}
                                 {{ Content.Input.' . $gradeType->getCode() . ' }}
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
                foreach ($subjectList as $subjectAcronym) {
                    $index++;
                    $content = '{% if(Content.Input.BehaviorTeacher.' . $subjectAcronym . '.' . $gradeType->getCode() . ' is not empty) %}
                                    {{ Content.Input.BehaviorTeacher.' . $subjectAcronym . '.' . $gradeType->getCode() . ' }}
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
     * @return Slice
     * @throws \Exception
     */
    protected function getSubjectLanesForGradeInformation()
    {

        $slice = (new Slice());

        $subjectList = array();
        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($this->getCertificateEntity());
        if ($tblCertificateSubjectAll) {
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                if (($tblSubject = $tblCertificateSubject->getServiceTblSubject())) {
                    $subjectList[$tblCertificateSubject->getRanking()] = $tblSubject;
                }
            }
        }
        if (!empty($subjectList)) {
            ksort($subjectList);
        }

        $paddingLeft = '5px';

        $section = new Section();
        $top = '30px';
        $height = '50px';
        $fontSize = '17px';
        $section
            ->addElementColumn((new Element())
                ->setContent('Fächer')
                ->styleMarginTop($top)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderBottom()
                ->styleBackgroundColor('#BBB')
                ->styleHeight($height)
                ->styleTextSize($fontSize)
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('derzeitige Note' . '<br>' . '(mit Signum)')
                ->styleMarginTop($top)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderBottom()
                ->styleBackgroundColor('#BBB')
                ->styleHeight($height)
                ->styleAlignCenter()
                ->styleTextSize($fontSize)
                , '40%')
            ->addElementColumn((new Element())
                ->setContent('Bemerkungen, vergessene' . '<br>' . 'Arbeitsmittel')
                ->styleMarginTop($top)
                ->stylePaddingLeft($paddingLeft)
                ->styleBorderAll()
                ->styleBackgroundColor('#BBB')
                ->styleHeight($height)
                ->styleAlignCenter()
                ->styleTextSize($fontSize)
                , '40%');
        $slice->addSection($section);

        $heightRow = '25px';
        $index = 0;
        /** @var TblSubject $subject */
        foreach ($subjectList as $subject) {
            $section = new Section();
            $index++;
            if ($index == 4) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        ->styleHeight($heightRow)
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingLeft('10px')
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        ->styleHeight($heightRow)
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingLeft($paddingLeft)
                        ->styleBorderLeft()
                        ->styleBorderBottom()
                        ->styleBorderRight()
                        ->styleHeight($heightRow)
                        , '40%');

                $slice->addSection($section);
                $section = new Section();
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent($subject->getName())
                    ->stylePaddingLeft($paddingLeft)
                    ->styleBorderLeft()
                    ->styleBorderBottom()
                    ->styleHeight($heightRow)
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.Grade.Data.' . $subject->getAcronym() . ' is not empty) %}
                            {{ Content.Grade.Data.' . $subject->getAcronym() . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingLeft('10px')
                    ->styleBorderLeft()
                    ->styleBorderBottom()
                    ->styleHeight($heightRow)
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingLeft($paddingLeft)
                    ->styleBorderLeft()
                    ->styleBorderBottom()
                    ->styleBorderRight()
                    ->styleHeight($heightRow)
                    , '40%');
            $slice->addSection($section);
        }

        return $slice;
    }
}