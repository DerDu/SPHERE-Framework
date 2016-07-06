<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class BeSOFS
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BeSOFS extends Certificate
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
                            ->setContent('Name der Schule:')
                            ->stylePaddingTop('10px')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('13px')
                            , '16%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Company.Data.Name is not empty) %}
                                    {{ Content.Company.Data.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop('9px')
                            ->styleBorderBottom()
                            , '84%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Bildungsempfehlung gemäß § 34 Abs. 3 SOFS')
                        ->styleAlignCenter()
                        ->styleTextSize('20px')
                        ->styleTextBold()
                        ->stylePaddingTop('15px')
                        ->stylePaddingBottom('20px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '46%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '23%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Year }}')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '23%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname und Name')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('13px')
                            , '46%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('13px')
                            , '23%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('13px')
                            ->styleMarginBottom('8px')
                            , '23%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '46%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '50%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren am')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('13px')
                            , '46%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('in')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('13px')
                            ->styleMarginBottom('8px')
                            , '50%')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {{ Content.Person.Address.Street.Number }},
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft('5px')
                        ->styleBorderBottom()
                    )
                    ->addElement((new Element())
                        ->setContent('Wohnhaft in')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('13px')
                        ->styleMarginBottom('8px')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Person.Parent) %}
                                    {{ Content.Person.Parent.Mother.Name.First }}
                                    {{ Content.Person.Parent.Mother.Name.Last }},
                                    {{ Content.Person.Parent.Father.Name.First }}
                                    {{ Content.Person.Parent.Father.Name.Last }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft('5px')
                        ->styleBorderBottom()
                    )
                    ->addElement((new Element())
                        ->setContent('Name der Eltern')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('13px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('1. Leistungsstand')
                        ->styleTextSize('16px')
                        ->styleTextBold()
                        ->styleMarginTop('15px')
                        ->stylePaddingBottom('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                            {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                                Die Schülerin
                                            {% else %}
                                              {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                                    Der Schüler
                                                {% else %}
                                                  Die Schülerin/Der Schüler¹
                                                {% endif %}
                                            {% endif %}
                                hat ausweislich der Halbjahresinformation vom
                             {% if(Content.Input.DateCertifcate is not empty) %}
                                {{ Content.Input.DateCertifcate }}
                            {% else %}
                                ______________
                            {% endif %}
                            folgende Leistungen erreicht:')
                            ->stylePaddingBottom('25px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Deutsch')
                            ->stylePaddingTop()
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.DE is not empty) %}
                                    {{ Content.Grade.Data.DE }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()

                            , '20%')
                        ->addElementColumn((new Element())
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('Mathematik')
                            ->stylePaddingTop()
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.MA is not empty) %}
                                    {{ Content.Grade.Data.MA }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Geschichte')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.GE is not empty) %}
                                    {{ Content.Grade.Data.GE }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleMarginTop('10px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            , '20%')
                        ->addElementColumn((new Element())
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('Biologie')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.BI is not empty) %}
                                    {{ Content.Grade.Data.BI }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            ->styleMarginTop('10px')
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Geographie')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.GEO is not empty) %}
                                    {{ Content.Grade.Data.GEO }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleMarginTop('10px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            , '20%')
                        ->addElementColumn((new Element())
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('Chemie')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.CH is not empty) %}
                                    {{ Content.Grade.Data.CH }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            ->styleMarginTop('10px')
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '55%')
                        ->addElementColumn((new Element())
                            ->setContent('Physik')
                            ->stylePaddingTop()
                            ->styleMarginTop('10px')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.PH is not empty) %}
                                    {{ Content.Grade.Data.PH }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            ->styleMarginTop('10px')
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Durchschnitt der Noten aus den angegebenen Fächern')
                            ->styleMarginTop('20px')
                            , '80%')
                        ->addElementColumn((new Element())
                            // ToDO EN replace to Sachunterricht
                            ->setContent('{% if(Content.Grade.Data.DE is not empty) %}
                                    {% if(Content.Grade.Data.MA is not empty) %}
                                        {% if(Content.Grade.Data.EN is not empty) %}
                                            {{ ((Content.Grade.Data.DE + Content.Grade.Data.MA + Content.Grade.Data.GE
                                            + Content.Grade.Data.BI + Content.Grade.Data.GEO + Content.Grade.Data.CH + Content.Grade.Data.PH)
                                             / 7)|round(2, "floor") }}
                                        {% else %}
                                            ---
                                        {% endif %}
                                    {% else %}
                                        ---
                                    {% endif %}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleMarginTop('20px')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '80%')
                        ->addElementColumn((new Element())
                            ->stylePaddingTop()
                            ->setContent('(in Ziffern)')
                            ->styleTextSize('9px')
                            ->styleAlignCenter()
                            , '20%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('2. Empfehlung der Klassenkonverenz')
                        ->styleTextSize('16px')
                        ->styleTextBold()
                        ->styleMarginTop('6px')
                        ->stylePaddingBottom('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Auf Grund des Leistungsstandes wird
                                {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                    der Schülerin empfohlen, ihre
                                {% else %}
                                    {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                        dem Schüler empfohlen, seine
                                    {% else %}
                                        der Schülerin/dem Schüler¹ empfohlen, ihre/seine¹
                                    {% endif %}
                                {% endif %}
                                Ausbildung in einer Klasse zur Erlangung des Hauptschulabschlusses fortzusetzen.
                                ')
                        ->stylePaddingBottom()
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Diese Empfehlung wurde durch die Klassenkonferenz am
                            {% if(Content.Input.DateConference is not empty) %}
                                {{ Content.Input.DateConference }}
                            {% else %}
                                ______________
                            {% endif %}
                                beschlossen.
                                ')
                        ->styleMarginTop('20px')
                        ->styleMarginBottom('220px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
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
                            , '28%')
                        ->addElementColumn((new Element())
                            , '65%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            ->styleMarginTop('50px')

                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('Dienstsiegel <br/> der Schule')
                            ->styleTextColor('#AAA')
                            ->styleTextSize('13px')
                            ->styleAlignCenter()
                            ->styleMarginTop('15px')
                            , '30%')
                        ->addElementColumn((new Element())
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            ->styleMarginTop('50px')
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulleiter/in')
                            ->styleTextSize('13px')
                            ->stylePaddingTop('5px')
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenlehrer/in')
                            ->styleTextSize('13px')
                            ->stylePaddingTop('5px')
                            , '35%')
                    )
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('¹ Nichtzutreffendes streichen.')
                        ->styleTextSize('9px')
                        ->styleMarginTop('5px')
                    )
                )
            )
        );
    }
}
