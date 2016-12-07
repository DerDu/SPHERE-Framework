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
class RadebeulHalbjahresinformation extends Certificate
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
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                            '80px', '80px'))
                            ->styleAlignCenter()
                            , '20%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent(
                                        'FREISTAAT SACHSEN'
                                    )
                                    ->styleTextSize('20px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent(
                                        'Evangelische Grundschule Radebeul'
                                    )
                                    ->styleTextSize('20px')
                                    ->styleTextColor('DarkOrange')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent(
                                        '(Staatlich anerkannte Ersatzschule in freier Trägerschaft)'
                                    )
                                    ->styleTextSize('14px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn(
                                    $IsSample
                                        ? (new Element())
                                        ->setContent('MUSTER')
                                        ->styleAlignCenter()
                                        ->styleTextBold()
                                        ->styleTextColor('darkred')
                                        ->styleTextSize('24px')
                                        ->styleMarginBottom('0px')
                                        : (new Element())
                                        ->setContent('&nbsp;')
                                        ->styleHeight('28px')
                                        ->styleMarginBottom('0px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent(
                                        'Halbjahresinformation'
                                    )
                                    ->styleTextBold()
                                    ->styleMarginBottom('0px')
                                    ->styleTextSize('23px')
                                    ->styleTextColor('DarkOrange')
                                    ->styleAlignCenter()
                                )
                            )
                        )
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                            '80px', '80px'))
                            ->styleAlignCenter()
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element()), '4%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Vor- und Zuname:')
                                    ->styleMarginTop('30px')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('{{ Content.Person.Data.Name.First }}
                                                  {{ Content.Person.Data.Name.Last }}')
                                    ->styleMarginTop('30px')
                                    , '24%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleMarginTop('30px')
                                    , '4%')
                                ->addElementColumn((new Element())
                                    ->setContent('Geboren am:')
                                    ->styleMarginTop('30px')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                            {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleMarginTop('30px')
                                    , '24%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Klasse:')
                                    ->styleMarginTop('10px')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                                    ->styleMarginTop('10px')
                                    , '24%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleMarginTop('10px')
                                    , '4%')
                                ->addElementColumn((new Element())
                                    ->setContent('Schuljahr:')
                                    ->styleMarginTop('10px')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('{{ Content.Division.Data.Year }}')
                                    ->styleMarginTop('10px')
                                    , '24%')
                            )
                            ->addSection((new Section())
                                ->addSliceColumn($this->getGradeLanes('14px', false))
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Bemerkungen:')
                                    ->styleTextBold()
                                    ->styleMarginTop('10px')
                                    , '16%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Rating is not empty) %}
                                            {{ Content.Input.Rating|nl2br }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}')
                                    ->styleMarginTop('10px')
                                    ->styleHeight('50px')
                                    , '84%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Leistung in den einzelnen Fächern')
                                    ->styleTextBold()
                                    ->styleTextSize('16px')
                                    ->styleMarginTop('10px')
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn($this->getSubjectLanes(true, array(), '14px', false))
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Bemerkungen:')
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
                                    ->styleHeight('120px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Versetzungsvermerk:')
                                    ->styleMarginTop('10px')
                                    , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Transfer) %}
                                            {{ Content.Input.Transfer|nl2br }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleMarginTop('10px')
                                    ->styleHeight('50px')
                                    , '78%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Fehltage entschuldigt:')
                                    ->styleMarginTop('10px')
                                    , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Missing is not empty) %}
                                            {{ Content.Input.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                                    )
                                    ->styleMarginTop('10px')
                                    , '8%')
                                ->addElementColumn((new Element())
                                    ->setContent('unentschuldigt:')
                                    ->styleMarginTop('10px')
                                    , '18%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Bad.Missing is not empty) %}
                                            {{ Content.Input.Bad.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                                    )
                                    ->styleMarginTop('10px')
                                    , '8%')
                                ->addElementColumn((new Element()), '44%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Datum:')
                                    ->styleMarginTop('25px')
                                    , '7%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.Input.Date is not empty) %}
                                            {{ Content.Input.Date }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}'
                                    )
                                    ->styleMarginTop('25px')
                                    ->styleAlignCenter()
                                    , '23%')
                                ->addElementColumn((new Element())
                                    , '70%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleMarginTop('25px')
                                    ->styleBorderBottom()
                                    , '30%')
                                ->addElementColumn((new Element())
                                    ->setContent('Dienstsiegel der Schule')
                                    ->styleAlignCenter()
                                    ->styleMarginTop('25px')
                                    ->styleTextSize('11px')
                                    , '40%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleMarginTop('25px')
                                    ->styleBorderBottom('1px', '#000')
                                    , '30%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schulleiter/in')
                                    ->styleTextSize('11px')
                                    , '30%')
                                ->addElementColumn((new Element())
                                    , '40%')
                                ->addElementColumn((new Element())
                                    ->setContent('Klassenlehrer/in')
                                    ->styleTextSize('11px')
                                    , '30%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    , '70%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                        {% if(Content.DivisionTeacher.Name is not empty) %}
                                            {{ Content.DivisionTeacher.Name }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}
                                    ')
                                    ->styleTextSize('11px')
                                    ->stylePaddingTop('2px')
                                    , '30%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Zur Kenntnis genommen:')
                                    ->styleMarginTop('25px')
                                    , '30%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleMarginTop('25px')
                                    ->styleBorderBottom()
                                    , '70%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    , '30%')
                                ->addElementColumn((new Element())
                                    ->setContent('Personensorgeberechtigte/r')
                                    ->styleTextSize('11px')
                                    , '40%')
                                ->addElementColumn((new Element())
                                    , '30%')
                            )
                        )
                        ->addElementColumn((new Element()), '4%')
                    )
                    ->styleBorderAll('2px')
                    ->styleHeight('990px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Notenstufen: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend,
                                5 = mangelhaft, 6 = ungenügend')
                            ->styleTextSize('12px')
                            ->styleMarginTop('5px')
                        )
                    )
                )
            )
        );
    }
}
