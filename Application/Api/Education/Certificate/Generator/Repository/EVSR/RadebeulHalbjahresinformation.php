<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.11.2016
 * Time: 14:36
 */
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class RadebeulJahreszeugnis
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulHalbjahresinformation extends Certificate
{

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $textColorBlue = 'rgb(25,59,100)';
        $textColorRed = 'rgb(202,23,63)';
        $textSize = '11pt';
        $fontFamily = 'MetaPro';

        return (new Page())
//            ->addSlice((new Slice())
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        ->setContent(
//                            '&nbsp;'
//                        )
//                        ->styleHeight('35px')
//                    )
//                )
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        , '10%')
//                    ->addSliceColumn((new Slice())
//                        ->addSection((new Section())
//                            ->addElementColumn((new Element())
//                                ->setContent('{% if( Content.P' . $personId . '.Company.Data.Name is not empty) %}
//                                        {{ Content.P' . $personId . '.Company.Data.Name }}
//                                    {% else %}
//                                        Evangelisches Schulzentrum Radebeul
//                                    {% endif %}')
//                                ->styleMarginTop('-9px')
//                                ->styleTextSize('26px')
//                                ->styleTextColor($textColorRed)
//                                ->styleAlignCenter()
//                                ->styleTextBold()
//                                ->styleFontFamily($fontFamily)
//                            )
//                        )
//                        ->addSection((new Section())
//                            ->addElementColumn((new Element())
//                                ->setContent('- Grundschule -')
//                                ->styleMarginTop('-9px')
//                                ->styleTextSize('22px')
//                                ->styleTextColor($textColorRed)
//                                ->styleAlignCenter()
//                                ->styleTextBold()
//                                ->styleFontFamily($fontFamily)
//                            )
//                        )
//                        ->addSection((new Section())
//                            ->addElementColumn((new Element())
//                                ->setContent(
//                                    'Staatlich anerkannte Ersatzschule in freier Trägerschaft'
//                                )
//                                ->styleMarginTop('-8px')
//                                ->styleTextColor($textColorBlue)
//                                ->styleTextSize('15px')
//                                ->styleAlignCenter()
//                                ->styleFontFamily($fontFamily)
//                            )
//                        )
//                        ->addSection((new Section())
//                            ->addElementColumn((new Element())
//                                ->setContent(
//                                    'im Freistaat Sachsen'
//                                )
//                                ->styleMarginTop('-10px')
//                                ->styleTextColor($textColorBlue)
//                                ->styleTextSize('15px')
//                                ->styleAlignCenter()
//                                ->styleFontFamily($fontFamily)
//                            )
//                        )
//                        ->addSection((new Section())
//                            ->addElementColumn(
//                                $this->isSample()
//                                    ? (new Element())
//                                    ->styleMarginTop('2px')
//                                    ->setContent('MUSTER')
//                                    ->styleAlignCenter()
//                                    ->styleTextBold()
//                                    ->styleTextColor('darkred')
//                                    ->styleTextSize('16px')
//                                    ->styleMarginBottom('0px')
//                                    : (new Element())
//                                    ->styleMarginTop('2px')
//                                    ->setContent('&nbsp;')
//                                    ->styleHeight('20px')
//                                    ->styleMarginBottom('0px')
//                            )
//                        )
//                        ->addSection((new Section())
//                            ->addElementColumn((new Element())
//                                ->setContent(
//                                    'Halbjahresinformation'
//                                )
//                                ->styleMarginTop('-14px')
//                                ->styleTextBold()
//                                ->styleTextSize('32px')
//                                ->styleTextColor($textColorRed)
//                                ->styleAlignCenter()
//                                ->styleFontFamily($fontFamily)
//                            )
//                        )
//                    )
//                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSR.jpg',
//                        '80px', '80px'))
//                        ->styleAlignCenter()
//                        , '10%')
//                )
            ->addSlice(RadebeulOsJahreszeugnis::getHeader('Halbjahresinformation', '- Grundschule -', 'anerkannte'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Vor- und Zuname:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                                  {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                                ->styleTextBold()
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '45%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('Klasse:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '15%')
                            ->addElementColumn((new Element())
                                ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }} {{ Content.P' . $personId . '.Division.Data.Name }}')
                                ->styleTextBold()
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '16%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('geboren am:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('4px')
                                , '20%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                        {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                            {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                ->styleTextBold()
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('4px')
                                , '45%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('4px')
                                , '4%')
                            ->addElementColumn((new Element())
                                ->setContent('Schuljahr:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('4px')
                                , '15%')
                            ->addElementColumn((new Element())
                                ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                                ->styleTextBold()
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('4px')
                                , '16%')
                        )
                        ->addSection((new Section())
                            ->addSliceColumn($this->getGradeLanesForRadebeul($personId, $textColorBlue, '10pt'))
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Leistung in den einzelnen Fächern:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextBold()
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('15px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn($this->getSubjectLanesForRadebeul($personId, $textColorBlue, '10pt'))
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Bemerkungen:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleTextBold()
                                ->styleMarginTop('20px')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('
                                        {% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleLineHeight('80%')
                                ->styleHeight('175px')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Fehltage entschuldigt:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('0px')
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                        {% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                                )
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('0px')
                                , '8%')
                            ->addElementColumn((new Element())
                                ->setContent('unentschuldigt:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('0px')
                                , '18%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                        {% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                                )
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('0px')
                                , '8%')
                            ->addElementColumn((new Element()), '44%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Datum:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '7%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                        {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Date }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}'
                                )
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                ->styleAlignCenter()
                                , '23%')
                            ->addElementColumn((new Element())
                                , '70%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleBorderBottom('1px', $textColorBlue)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '30%')
                            ->addElementColumn((new Element())
                                ->setContent('Dienstsiegel der Schule')
                                ->styleTextColor($textColorBlue)
                                ->styleAlignCenter()
                                ->styleTextSize('10px')
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextColor($textColorBlue)
                                ->styleBorderBottom('1px', $textColorBlue)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('20px')
                                , '30%')
                        )
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
                                ->styleFontFamily($fontFamily)
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize('10px')
                                , '30%')
                            ->addElementColumn((new Element())
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}'
                                )
                                ->styleAlignCenter()
                                ->styleFontFamily($fontFamily)
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize('10px')
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
                                ->styleAlignCenter()
                                ->styleMarginTop('-3px')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize('10px')
                                ->styleFontFamily($fontFamily)
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
                                ->styleAlignCenter()
                                ->styleMarginTop('-3px')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize('10px')
                                ->styleFontFamily($fontFamily)
                                , '30%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Zur Kenntnis genommen:')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('25px')
                                , '30%')
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize($textSize)
                                ->styleBorderBottom('1px', $textColorBlue)
                                ->styleFontFamily($fontFamily)
                                ->styleMarginTop('25px')
                                , '70%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                , '30%')
                            ->addElementColumn((new Element())
                                ->setContent('Personensorgeberechtigte/r')
                                ->styleAlignCenter()
                                ->styleMarginTop('-3px')
                                ->styleTextColor($textColorBlue)
                                ->styleTextSize('10px')
                                ->styleFontFamily($fontFamily)
                                , '40%')
                            ->addElementColumn((new Element())
                                , '30%')
                        )
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Notenstufen: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend,
                                5 = mangelhaft, 6 = ungenügend')
                        ->styleTextColor($textColorBlue)
                        ->styleTextSize('10px')
                        ->styleFontFamily($fontFamily)
                        ->styleMarginTop('15px')
                    )
                )
            );
    }
}
