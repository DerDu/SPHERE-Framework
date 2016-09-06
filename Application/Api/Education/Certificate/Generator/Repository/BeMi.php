<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class BeMi
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BeMi extends Certificate
{

    /**
     * @return array
     */
    public function selectValuesType()
    {
        return array(
            1 => "der Halbjahresinformation",
            2 => "der für das Jahreszeugnis vorgesehene Noten gemäß Beschluss der Klassenkonferenz"
        );
    }

    /**
     * @return array
     */
    public function selectValuesSchoolType()
    {
        return array(
            1 => "am Gymnasium",
            2 => "an der Mittelschule"
        );
    }

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
                        ->setContent('Bildungsempfehlung in den Klassenstufen 5-6')
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
                                hat ausweislich der Halbjahresinformation /
                            der für das Jahreszeugnis vorgesehene Noten gemäß Beschluss der Klassenkonferenz¹
                             vom
                             {% if(Content.Input.DateCertifcate is not empty) %}
                                {{ Content.Input.DateCertifcate }}
                            {% else %}
                                ______________
                            {% endif %}
                            folgende Leistungen erreicht:')
                            ->stylePaddingBottom('25px')
                        )
                    )
                )
                ->addSlice( $this->getSubjectLanes() )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Durchschnitt der Noten aus den angegebenen Fächern')
                            ->styleMarginTop('15px')
                            , '80%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.Average is not empty) %}
                                    {{ Content.Grade.Data.Average }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleMarginTop('10px')
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
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Durchschnitt der Noten in allen anderen Fächern')
                            ->styleMarginTop('10px')
                            , '80%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Grade.Data.AverageOthers is not empty) %}
                                    {{ Content.Grade.Data.AverageOthers }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleMarginTop('5px')
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
                        ->setContent('2. Gutachten²')
                        ->styleTextSize('16px')
                        ->styleTextBold()
                        ->styleMarginTop('6px')
                        ->stylePaddingBottom('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Input.Survey is not empty) %}
                                    {{ Content.Input.Survey|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleHeight('135px')
                        ->stylePaddingBottom()
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Auf Grund des Leitungsstandes und des Gutachtens wird
                        {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                der Schülerin
                            {% else %}
                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    dem Schüler
                                {% else %}
                                    der Schülerin/dem Schüler¹
                                {% endif %}
                            {% endif %} empfohlen,')
                        ->styleMarginTop('15px')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if Content.Person.Common.BirthDates.Gender == 2 %}
                                ihre Ausbildung
                                {% if(Content.Input.SchoolType is not empty) %}
                                {{ Content.Input.SchoolType }}
                                {% else %}
                                    ________________________________
                                {% endif %}
                                fortzusetzen.
                            {% else %}
                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    seine Ausbildung
                                    {% if(Content.Input.SchoolType is not empty) %}
                                    {{ Content.Input.SchoolType }}
                                    {% else %}
                                        ________________________________
                                    {% endif %}
                                fortzusetzen.
                                {% else %}
                                    ihre/seine¹ Ausbildung
                                     {% if(Content.Input.SchoolType is not empty) %}
                                    {{ Content.Input.SchoolType }}
                                    {% else %}
                                        ________________________________
                                    {% endif %}
                                     fortzusetzen.
                                {% endif %}
                            {% endif %}')
                        ->styleMarginTop('13px')

                    )
                    ->stylePaddingTop()
                    ->stylePaddingBottom()
                    ->styleMarginTop('20px')
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
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum:')
                            ->styleMarginTop('50px')
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            ->styleMarginTop('50px')
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
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent(
                                '{% if(Content.DivisionTeacher.Name is not empty) %}
                                    {{ Content.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                            )
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
//                            ->styleAlignCenter()
                            , '35%')
                    )
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
//                            . new Container('² An sorbische Schulen, an denen Sorbisch je nach Unterrichtsfach und Klassenstufe
//                            Unterrichtssprache ist, kann nach Entscheidung ')
//                            . new Container('&nbsp;&nbsp;der Schulkonferenz gem. § 21 Abs. 5 SOGS das
//                            Fach Deutsch durch das Fach Sorbisch ersetzt werden.')
                                . new Container('² Falls der Raum für Eintragungen nicht ausreicht, ist ein Beiblatt zu verwenden.')
                            )
                            ->styleTextSize('9px')
                            ->styleMarginTop('5px')
                        )
                    )
                )
            )
        );
    }
}
