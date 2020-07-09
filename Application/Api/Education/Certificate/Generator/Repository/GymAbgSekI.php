<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.02.2018
 * Time: 14:12
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;


use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class GymAbgHs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GymAbgSekI extends Certificate
{

    const COURSE_HS = 1;
    const COURSE_RS = 2;
    const COURSE_HSQ = 3;

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample());

        // leere Seite
        $pageList[] = new Page();

        $pageList[] = (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ABGANGSZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20%')
                    ->styleTextBold()
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('des Gymnasiums')
                    ->styleTextSize('22px')
                    ->styleAlignCenter()
                    ->styleMarginTop('15px')
                )
            )->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('(Sekundarstufe I)')
                    ->styleTextSize('22px')
                    ->styleAlignCenter()
                    ->styleMarginTop('0px')
                )
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->styleAlignCenter()
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('wohnhaft in')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('10px')
            )
            ->addSliceArray(MsAbsRs::getSchoolPart($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent(
                        new Container('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß')
                        . new Container('§ 28 Absatz 1 Nummer 1 des Sächsischen Schulgesetzes')
                        . new Container('das Gymnasium.')
                    )
                    ->styleMarginTop('8px')
                    ->styleAlignCenter()
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(
                            '{% if(Content.P' . $personId . '.Input.EqualGraduation.RS is not empty) %}
                                X
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        , '4%')
                    ->addElementColumn((new Element())
                    ->setContent('
                            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Frau
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Herr
                                {% else %}
                                    Frau/Herr
                                {% endif %}
                            {% endif %}
                            <u> {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }} </u> hat
                            gemäß § 7 Absatz 7 Satz 2 des Sächsischen Schulgesetzes mit der Versetzung von Klassenstufe
                            10 nach Jahrgangsstufe 11 des Gymnasiums einen dem Realschulabschluss gleichgestellten mittleren
                            Schulabschluss erworben.¹')
                        ->stylePaddingBottom()
                    )
                )->styleMarginTop('45px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(
                            '{% if(Content.P' . $personId . '.Input.EqualGraduation.HS is not empty) %}
                                X
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        , '4%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Frau
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Herr
                                {% else %}
                                    Frau/Herr
                                {% endif %}
                            {% endif %}
                            <u> {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }} </u> hat
                            gemäß § 7 Absatz 7 Satz 1 des Sächsischen Schulgesetzes mit der Versetzung von Klassenstufe 9
                            nach Klassenstufe 10 des Gymnasiums einen dem Hauptschulabschluss gleichgestellten Schulabschluss erworben.¹')
                        ->stylePaddingBottom()
                    )
                )->styleMarginTop('15px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('¹ Zutreffendes ist anzukreuzen')
                        ->styleTextSize('9.5px')
                        ->styleBorderTop()
                        , '20%')
                    ->addElementColumn((new Element())
                    )
                )
                ->styleMarginTop('410px')
            );

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        , '21%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleBorderBottom()
                        , '59%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleAlignCenter()
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        , '10%')
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId, true, array('Lane' => 1, 'Rank' => 3))->styleHeight('300px'))
            ->addSlice($this->getProfileStandardNew($personId))
            ->addSlice($this->getDescriptionHead($personId))
            ->addSlice($this->getDescriptionContent($personId, '155px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId))
            ->addSlice($this->getParentSign())
            ->addSlice($this->getInfo('100px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend',
                '¹ &nbsp;&nbsp;&nbsp; Die Bezeichnung des besuchten schulspezifischen Profils ist anzugeben. Beim Erlernen einer dritten
                 Fremdsprache ist anstelle des Profils oder in der vertieften <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                 sprachlichen Ausbildung die Fremdsprache anzugeben.'
            ));

        return $pageList;
    }
}
