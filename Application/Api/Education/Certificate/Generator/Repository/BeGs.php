<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class BeGs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BeGs extends Certificate
{

    /**
     * @return array
     */
    public function selectValuesType()
    {
        return array(
            1 => "der Halbjahresinformation",
            2 => "der für das Jahreszeugnis vorgesehenen Noten gemäß Beschluss der Klassenkonferenz"
        );
    }

    /**
     * @return array
     */
    public function selectValuesSchoolType()
    {
        return array(
            1 => "am Gymnasium",
            2 => "an der Oberschule"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $PaddingTopInfo = '5px';
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        if (!$this->getTblPrepareCertificate()
            || ($tblSurveyInformation = Prepare::useService()->getPrepareInformationBy($this->getTblPrepareCertificate(), $tblPerson, 'Survey'))
        ) {
            $hasSecondPage = true;
        } else {
            $hasSecondPage = false;
        }

        $pageList[] = (new Page())
            ->addSlice($this->getSchoolName($personId, '10px'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Bildungsempfehlung in der Klassenstufe 4')
                    ->styleAlignCenter()
                    ->styleTextSize('20px')
                    ->styleTextBold()
                    ->stylePaddingTop('15px')
                    ->stylePaddingBottom('35px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->stylePaddingTop($PaddingTopInfo)
                        ->stylePaddingLeft()
                        ->styleBorderBottom()
                        , '46%')
                    ->addElementColumn((new Element())
                        , '4%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->stylePaddingTop($PaddingTopInfo)
                        ->stylePaddingLeft()
                        ->styleBorderBottom()
                        , '23%')
                    ->addElementColumn((new Element())
                        , '4%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->stylePaddingTop($PaddingTopInfo)
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
                                {{ Content.P'.$personId.'.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->stylePaddingTop($PaddingTopInfo)
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
                        ->stylePaddingTop($PaddingTopInfo)
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
                            {{ Content.P'.$personId.'.Person.Address.Street.Name }}
                            {{ Content.P'.$personId.'.Person.Address.Street.Number }},
                            {{ Content.P'.$personId.'.Person.Address.City.Code }}
                            {{ Content.P'.$personId.'.Person.Address.City.Name }}
                        {% else %}
                              &nbsp;
                        {% endif %}')
                    ->stylePaddingTop($PaddingTopInfo)
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
                    ->stylePaddingTop($PaddingTopInfo)
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
                    ->stylePaddingBottom('20px')
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
                            {% if Content.P' . $personId . '.Input.Type is not empty %}
                                 hat ausweislich {{ Content.P' . $personId . '.Input.Type }}
                            {% else %}
                                hat ausweislich der Halbjahresinformation /
                                der für das Jahreszeugnis vorgesehenen Noten gemäß Beschluss der Klassenkonferenz¹
                            {% endif %}
                                vom
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
                                {{ Content.P'.$personId.'.Grade.Data.Average }}
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
                    ->setContent('2. Pädagogische Einschätzung²')
                    ->styleTextSize('16px')
                    ->styleTextBold()
                    ->styleMarginTop('15px')
                    ->stylePaddingBottom('7px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Auf Grund des Lern- und Arbeitsverhaltens, der Art und Ausprägung der schulischen
                        Leistungen und der Entwicklung wird
                        {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                            der Schülerin
                        {% else %}
                            {% if Content.P'.$personId.'.Person.Common.BirthDates.Gender == 1 %}
                                dem Schüler
                            {% else %}
                                der Schülerin/dem Schüler¹
                            {% endif %}
                        {% endif %} empfohlen,
                        {% if Content.P'.$personId.'.Person.Common.BirthDates.Gender == 2 %}
                        ihre Ausbildung
                        {% if(Content.P'.$personId.'.Input.SchoolType is not empty) %}
                            {{ Content.P'.$personId.'.Input.SchoolType }}
                            {% else %}
                                ________________________________
                            {% endif %}
                            fortzusetzen.
                        {% else %}
                            {% if Content.P'.$personId.'.Person.Common.BirthDates.Gender == 1 %}
                                seine Ausbildung
                                {% if(Content.P'.$personId.'.Input.SchoolType is not empty) %}
                                {{ Content.P'.$personId.'.Input.SchoolType }}
                                {% else %}
                                    ________________________________
                                {% endif %}
                            fortzusetzen.
                            {% else %}
                                ihre/seine¹ Ausbildung
                                 {% if(Content.P'.$personId.'.Input.SchoolType is not empty) %}
                                {{ Content.P'.$personId.'.Input.SchoolType }}
                                {% else %}
                                    ________________________________
                                {% endif %}
                                 fortzusetzen.
                            {% endif %}
                        {% endif %}')
                    ->styleLineHeight('200%')
                )
                ->stylePaddingTop()
                ->stylePaddingBottom()
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Diese Empfehlung wurde durch die Klassenkonferenz am
                        {% if(Content.P'.$personId.'.Input.DateConference is not empty) %}
                            {{ Content.P'.$personId.'.Input.DateConference }}
                        {% else %}
                            ______________
                        {% endif %}
                            beschlossen.
                        ')
                    ->styleMarginTop('30px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Mit den Eltern fand am 
                        {% if(Content.P'.$personId.'.Input.DateConsulting is not empty) %}
                            {{ Content.P'.$personId.'.Input.DateConsulting }}
                        {% else %}
                            ______________
                        {% endif %}
                            eine Bildungsberatung statt.
                        ')
                    ->styleMarginTop('30px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Datum:')
                        ->styleMarginTop('45px')
                        , '7%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                {{ Content.P'.$personId.'.Input.Date }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleBorderBottom('1px', '#000')
                        ->styleAlignCenter()
                        ->styleMarginTop('45px')
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
                ->stylePaddingTop()
                ->stylePaddingBottom()
                ->styleMarginTop('20px')
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
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '70%')
                )->styleMarginTop('47px')
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if Content.P' . $personId . '.Input.Type is empty %}
                                     ¹ Nichtzutreffendes streichen.
                                {% else %}
                                     {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 0 %}
                                        ¹ Nichtzutreffendes streichen.     
                                    {% endif %}
                                {% endif %}'
                            .new Container('² Kann auf Bitte der Eltern auf einem Beiblatt durch weitergehende
                            Ausführungen ergänzt werden.')
                        )
                        ->styleTextSize('9px')
                        ->styleMarginTop('5px')
                    )
                )
            );

        if ($hasSecondPage) {
            $pageList[] = (new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Beiblatt zum Gutachten der Bildungsempfehlung für:')
                        , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                        , '50%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Input.Survey is not empty) %}
                                {{ Content.P' . $personId . '.Input.Survey|nl2br }}
                            {% else %}
                                ---
                            {% endif %}
                        ')
                        ->styleMarginTop('50px')
                        ->styleHeight('920px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Ort, Datum:')
                            , '12%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}
                            ')
                            ->styleBorderBottom()
                            , '45%')
                        ->addElementColumn((new Element()))
                    )
                )
            ;
        }

        return $pageList;
    }
}
