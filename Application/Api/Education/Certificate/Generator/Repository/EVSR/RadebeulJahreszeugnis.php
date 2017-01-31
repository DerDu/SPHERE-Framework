<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.11.2016
 * Time: 14:36
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class RadebeulJahreszeugnis
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulJahreszeugnis extends Certificate
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
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        $textColorBlue = 'rgb(25,59,100)';
        $textColorRed = 'rgb(201,19,63)';
        $textSize = '13px';
        $fontFamily = 'MetaPro';

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                '&nbsp;'
                            )
                            ->styleHeight('35px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/RadebeulLogoFreistaatSachsen.PNG',
                            '80px', '80px'))
                            ->styleAlignCenter()
                            , '20%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent(
                                        'FREISTAAT SACHSEN'
                                    )
                                    ->styleMarginTop('-12px')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize('23px')
                                    ->styleAlignCenter()
                                    ->styleFontFamily($fontFamily)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent(
                                        'Evangelische Grundschule Radebeul'
                                    )
                                    ->styleMarginTop('-9px')
                                    ->styleTextSize('22px')
                                    ->styleTextColor($textColorRed)
                                    ->styleAlignCenter()
                                    ->styleTextBold()
                                    ->styleFontFamily($fontFamily)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent(
                                        '(Staatlich anerkannte Ersatzschule in freier Trägerschaft)'
                                    )
                                    ->styleMarginTop('-8px')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize('15px')
                                    ->styleAlignCenter()
                                    ->styleFontFamily($fontFamily)
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn(
                                    $IsSample
                                        ? (new Element())
                                        ->styleMarginTop('2px')
                                        ->setContent('MUSTER')
                                        ->styleAlignCenter()
                                        ->styleTextBold()
                                        ->styleTextColor('darkred')
                                        ->styleTextSize('26px')
                                        ->styleMarginBottom('0px')
                                        : (new Element())
                                        ->styleMarginTop('2px')
                                        ->setContent('&nbsp;')
                                        ->styleHeight('30px')
                                        ->styleMarginBottom('0px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent(
                                        'Jahreszeugnis'
                                    )
                                    ->styleMarginTop('-14px')
                                    ->styleTextBold()
                                    ->styleTextSize('32px')
                                    ->styleTextColor($textColorRed)
                                    ->styleAlignCenter()
                                    ->styleFontFamily($fontFamily)
                                )
                            )
                        )
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/RadebeulLogo.jpg',
                            '80px', '80px'))
                            ->styleAlignCenter()
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element()), '6%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Vor- und Zuname:')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('33px')
                                    , '16%')
                                ->addElementColumn((new Element())
                                    ->setContent('{{ Content.Person.Data.Name.First }}
                                                  {{ Content.Person.Data.Name.Last }}')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('33px')
                                    , '24%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('33px')
                                    , '8%')
                                ->addElementColumn((new Element())
                                    ->setContent('Geboren am:')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('33px')
                                    , '16%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                            {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('33px')
                                    , '24%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Klasse:')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('4px')
                                    , '16%')
                                ->addElementColumn((new Element())
                                    ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('4px')
                                    , '24%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('4px')
                                    , '8%')
                                ->addElementColumn((new Element())
                                    ->setContent('Schuljahr:')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('4px')
                                    , '16%')
                                ->addElementColumn((new Element())
                                    ->setContent('{{ Content.Division.Data.Year }}')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('4px')
                                    , '24%')
                            )
                            ->addSection((new Section())
                                ->addSliceColumn($this->getGradeLanesForRadebeul($textColorBlue, $textSize))
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Bemerkungen:')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleTextBold()
                                    ->styleFontFamily($fontFamily)
                                    ->styleLineHeight('80%')
                                    ->styleMarginTop('25px')
                                    , '16%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Rating is not empty) %}
                                            {{ Content.Input.Rating|nl2br }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleLineHeight('80%')
                                    ->styleMarginTop('25px')
                                    ->styleHeight('30px')
                                    , '84%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Leistung in den einzelnen Fächern')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextBold()
                                    ->styleTextSize('16px')
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('-1px')
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn($this->getSubjectLanesForRadebeul($textColorBlue, $textSize))
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Bemerkungen:')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleTextBold()
                                    ->styleMarginTop('10px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Remark is not empty) %}
                                            {{ Content.Input.Remark|nl2br }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleLineHeight('80%')
                                    ->styleHeight('90px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Versetzungsvermerk:')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('5px')
                                    , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Transfer) %}
                                            {{ Content.Input.Transfer|nl2br }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('5px')
                                    ->styleHeight('40px')
                                    , '78%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Fehltage entschuldigt:')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize($textSize)
                                    ->styleFontFamily($fontFamily)
                                    ->styleMarginTop('0px')
                                    , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Missing is not empty) %}
                                            {{ Content.Input.Missing }}
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
                                        {% if(Content.Input.Bad.Missing is not empty) %}
                                            {{ Content.Input.Bad.Missing }}
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
                                        {% if(Content.Input.Date is not empty) %}
                                            {{ Content.Input.Date }}
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
                                    ->setContent('Schulleiter/in')
                                    ->styleMarginTop('-3px')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize('10px')
                                    ->styleFontFamily($fontFamily)
                                    , '30%')
                                ->addElementColumn((new Element())
                                    , '40%')
                                ->addElementColumn((new Element())
                                    ->setContent('Klassenlehrer/in')
                                    ->styleMarginTop('-3px')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize('10px')
                                    ->styleFontFamily($fontFamily)
                                    , '30%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Headmaster.Name is not empty) %}
                                            {{ Content.Headmaster.Name }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleMarginTop('-3px')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize('10px')
//                                    ->stylePaddingTop('2px')
                                    ->styleFontFamily($fontFamily)
                                    , '30%')
                                ->addElementColumn((new Element())
                                    , '40%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.DivisionTeacher.Name is not empty) %}
                                            {{ Content.DivisionTeacher.Name }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleMarginTop('-3px')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize('10px')
//                                    ->stylePaddingTop('2px')
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
                                    ->styleMarginTop('-3px')
                                    ->styleTextColor($textColorBlue)
                                    ->styleTextSize('10px')
                                    ->styleFontFamily($fontFamily)
                                    , '40%')
                                ->addElementColumn((new Element())
                                    , '30%')
                            )
                        )
                        ->addElementColumn((new Element()), '6%')
                    )
                    ->styleBorderAll('2.5px', $textColorBlue)
                    ->styleHeight('992px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Notenstufen: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend,
                                5 = mangelhaft, 6 = ungenügend')
                            ->styleTextColor($textColorBlue)
                            ->styleTextSize('10px')
                            ->styleFontFamily($fontFamily)
                            ->styleMarginTop('0px')
                        )
                    )
                )
            )
        );
    }
}