<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BeSOFS
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BeSOFS extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice($this->getSchoolName($personId, '10px'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Bildungsempfehlung gemäß § 34 Absatz 3 der Schulordnung Förderschulen')
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
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->stylePaddingTop()
                        ->stylePaddingLeft()
                        ->styleBorderBottom()
                        , '46%')
                    ->addElementColumn((new Element())
                        , '4%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->stylePaddingTop()
                        ->stylePaddingLeft()
                        ->styleBorderBottom()
                        , '23%')
                    ->addElementColumn((new Element())
                        , '4%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
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
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
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
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
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
                    ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->stylePaddingLeft('5px')
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('wohnhaft in')
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->stylePaddingLeft()
                    ->styleTextSize('13px')
                    ->styleMarginBottom('8px')
                )
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Person.Parent.CommaSeparated) %}
                            {{ Content.P' . $personId . '.Person.Parent.CommaSeparated }}
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
                            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                                Die Schülerin
                                            {% else %}
                                              {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                                    Der Schüler
                                                {% else %}
                                                  Die Schülerin/Der Schüler¹
                                                {% endif %}
                                            {% endif %}
                                hat ausweislich der Halbjahresinformation vom
                             {% if(Content.P' . $personId . '.Input.DateCertifcate is not empty) %}
                                {{ Content.P' . $personId . '.Input.DateCertifcate }}
                            {% else %}
                                ______________
                            {% endif %}
                            folgende Leistungen erreicht:')
                        ->stylePaddingBottom('25px')
                    )
                )
            )
            ->addSlice($this->getSubjectLanes($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Durchschnitt der Noten aus den angegebenen Fächern')
                        ->styleMarginTop('20px')
                        , '80%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Grade.Data.Average is not empty) %}
                                    {{ Content.P' . $personId . '.Grade.Data.Average }}
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
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                    der Schülerin empfohlen, ihre
                                {% else %}
                                    {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
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
                            {% if(Content.P' . $personId . '.Input.DateConference is not empty) %}
                                {{ Content.P' . $personId . '.Input.DateConference }}
                            {% else %}
                                ______________
                            {% endif %}
                                beschlossen.
                                ')
                    ->styleMarginTop('20px')
                    ->styleMarginBottom('159px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Datum:')
                        , '7%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Date }}
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
                        ->setContent('
                             {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter/in
                            {% endif %}
                        ')
                        ->styleTextSize('13px')
                        ->stylePaddingTop('5px')
                        , '35%')
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer/in
                            {% endif %}
                        ')
                        ->styleTextSize('13px')
                        ->stylePaddingTop('5px')
                        , '35%')
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
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        , '35%')
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        , '35%')
                )
                ->stylePaddingTop()
                ->stylePaddingBottom()
                ->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '70%')
                )->styleMarginTop('10px')
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '¹ Nichtzutreffendes streichen.'
                        )
                        ->styleTextSize('9px')
                        ->styleMarginTop('5px')
                    )
                )
            );
    }
}
