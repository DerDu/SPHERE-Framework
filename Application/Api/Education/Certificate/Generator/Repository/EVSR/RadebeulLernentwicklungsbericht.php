<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.11.2016
 * Time: 11:23
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class RadebeulKinderbrief
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulLernentwicklungsbericht extends Certificate
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
                        ->addElementColumn(
                            $IsSample
                                ? (new Element())
                                ->setContent('MUSTER')
                                ->styleAlignCenter()
                                ->styleTextBold()
                                ->styleTextColor('darkred')
                                ->styleTextSize('24px')
                                ->styleMarginBottom('0px')
                                ->styleHeight('100px')
                                : (new Element())
                                ->setContent('&nbsp;')
                                ->styleHeight('100px')
                                ->styleMarginBottom('0px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                new Container('Lernentwicklungsbericht') .
                                new Container('der') .
                                new Container('Evangelischen Grundschule Radebeul')
                            )
                            ->styleTextSize('20px')
                            ->styleAlignCenter()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('- staatlich anerkannte Ersatzschule in freier Trägerschaft -')
                            ->styleAlignCenter()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                new Container('zum') .
                                new Container('Schuljahr {{ Content.Division.Data.Year }}')
                            )
                            ->styleMarginTop('10px')
                            ->styleTextSize('20px')
                            ->styleAlignCenter()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Für:')
                            ->styleMarginTop('20px')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.Last }}, {{ Content.Person.Data.Name.First }}')
                            ->styleMarginTop('20px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Geboren am:')
                            ->styleMarginTop('5px')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleMarginTop('5px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse:')
                            ->styleMarginTop('5px')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {{ Content.Division.Data.Level.Name }}
                            ')
                            ->styleMarginTop('5px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Hinweis zu den einzelnen Lernbereichen / Fächern:')
                            ->styleMarginTop('5px')
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement(( new Element() )
                        ->setContent('
                            {% if(Content.Input.Rating is not empty) %}
                                {{ Content.Input.Rating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleMarginTop('10px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Versäumte Tage:')
                            ->styleMarginTop('20px')
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                        {% if(Content.Input.Total.Missing is not empty) %}
                                            {{ Content.Input.Total.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                            )
                            ->styleMarginTop('20px')
                            , '8%')
                        ->addElementColumn((new Element())
                            ->setContent('davon unentschuldigt:')
                            ->styleMarginTop('20px')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                        {% if(Content.Input.Bad.Missing is not empty) %}
                                            {{ Content.Input.Bad.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                            )
                            ->styleMarginTop('20px')
                            ->styleAlignRight()
                            , '8%')
                        ->addElementColumn((new Element()))
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                Radebeul, {{ Content.Input.Date }}
                            ')
                            ->styleMarginTop('20px')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element()))
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                Ort, Datum
                            ')
                            ->styleMarginTop('0px')
                            , '30%')
                        ->addElementColumn((new Element()))
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleMarginTop('25px')
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element()), '40%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleMarginTop('25px')
                            ->styleBorderBottom()
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
                            , '50%')
                        ->addElementColumn((new Element())
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('Eltern')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            , '50%')
                        ->addElementColumn((new Element())
                            , '20%')
                    )
                )
            )
        );
    }
}