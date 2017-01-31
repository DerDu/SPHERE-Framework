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
 * Class GymAbgHs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GymAbgHs extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        if ($IsSample) {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() )
                        ->setContent('&nbsp;')
                        ->styleTextSize('12px')
                        ->styleTextColor('#CCC')
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn(( new Element\Sample() )
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        , '25%')
                );
        } else {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() ), '75%')
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        , '25%')
                );
        }

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('ABGANGSZEUGNIS')
                        ->styleTextSize('27px')
                        ->styleAlignCenter()
                        ->styleMarginTop('32%')
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
                        ->styleMarginTop('5px')
                    )
                )
            )
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname und Name:')
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
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
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
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
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthplace }}
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
                            ->setContent('{% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {{ Content.Person.Address.Street.Number }},
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
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
                            ->setContent('hat')
                            , '5%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Company.Data.Name) %}
                                    {{ Content.Company.Data.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px')
                            ->styleAlignCenter()
                        )
                        ->addElementColumn((new Element())
                            ->styleBorderBottom('1px')
                            ->setContent('&nbsp;')
                            , '5%')
                    )
                    ->styleMarginTop('20px')
                )
//                ->addSlice(
//                    (new Slice())
//                        ->addElement(
//                            (new Element())
//                                ->setContent('
//                                            {{ Content.Company.Data.Name }},
//                                        ')
//                                ->styleBorderBottom('1px', '#BBB')
//                                ->styleAlignCenter()
//                        )
//                        ->styleMarginTop('10px')
//                )
                ->addSlice(
                    (new Slice())
                        ->addElement(
                            (new Element())
                                ->setContent('{% if(Content.Company.Address.Street.Name) %}
                                    {{ Content.Company.Address.Street.Name }}
                                    {{ Content.Company.Address.Street.Number }},
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                                ->styleBorderBottom('1px')
                                ->styleAlignCenter()
                        )
                        ->styleMarginTop('10px')
                )
                ->addSlice(
                    (new Slice())
                        ->addSection(
                            (new Section())
                                ->addElementColumn(
                                    (new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom('1px')
                                    , '10%')
                                ->addElementColumn(
                                    (new Element())
                                        ->setContent('{% if(Content.Company.Address.City.Name) %}
                                            {{ Content.Company.Address.City.Code }}
                                            {{ Content.Company.Address.City.Name }}
                                        {% else %}
                                              &nbsp;
                                        {% endif %}')
                                        ->styleBorderBottom('1px')
                                        ->styleAlignCenter()
                                )
                                ->addElementColumn(
                                    (new Element())
                                        ->setContent('besucht')
                                        ->styleAlignRight()
                                    , '10%')
                        )
                        ->styleMarginTop('10px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Name und Anschrift der Schule')
                        ->styleTextSize('9px')
                        ->styleTextColor('#999')
                        ->styleAlignCenter()
                        ->styleMarginTop('5px')
                        ->styleMarginBottom('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('und verlässt nach Erfüllung der Vollzeitschulpflicht gemäß § 28 Abs. 1 Nr. 1 SchulG das Gymnasium.')
                        ->styleMarginTop('8px')
                        ->styleAlignLeft()
                    )->styleMarginTop('30%')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())/* nicht Ausgewählt = 0; männlich = 1; weiblich = 2 */
                        ->setContent('
                            {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                Frau
                            {% else %}
                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    Herr
                                {% else %}
                                    Frau/Herr
                                {% endif %}
                            {% endif %}
                            {{ Content.Person.Data.Name.First }} {{ Content.Person.Data.Name.Last }} hat, gemäß § 30
                            Abs. 7 Satz 2 SOGYA, mit der Versetzung von Klassenstufe 9 nach Klassenstufe 10 des Gymnasiums
                            einen dem Hauptschulabschluss gleichgestellten Schulabschluss erworben.')
                            ->stylePaddingBottom()
                        )
                    )->styleMarginTop('1px')
                )
//                ->addSlice((new Slice())
//                    ->addSection((new Section())
//                        ->addElementColumn((new Element())
//                            ->setContent('¹ Zutreffendes ist zu unterstreichen.<br/>
//                                          ² Zutreffendes ist anzukreuzen')
//                            ->styleTextSize('9.5px')
//                            ->styleBorderTop()
//                            , '33%')
//                        ->addElementColumn((new Element())
//                        )
//                    )
//                    ->styleMarginTop('410px')
//                )
            )
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname und Name:')
                            , '21%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom()
                            , '59%')
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->styleAlignCenter()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
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
                ->addSlice($this->getSubjectLanes()->styleHeight('270px'))
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Wahlpflichtbereich¹:')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleTextBold()
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Choose is not empty) %}
                                    {{ Content.Input.Choose }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleAlignLeft()
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '32%')
                        ->addElementColumn((new Element())
                            ->setContent('Profil mit Informatischer Bildung²')
                            ->stylePaddingTop()
                            ->stylePaddingLeft('6px')
                            , '48%'
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('besuchtes Profil¹')
                            ->styleAlignCenter()
                            ->styleTextSize('9.5px')
                            , '22%')
                        ->addElementColumn((new Element())
                            , '48%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Profil')
                            ->stylePaddingTop()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleBorderBottom()
                            , '48%')
                    )->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '52%')
                        ->addElementColumn((new Element())
                            ->setContent('Fremdsprache (ab Klassenstufe {{ Content.Input.LevelThree }} ) Im sprachlichen Profil')
                            ->styleTextSize('9.5px')
                            ->styleAlignCenter()
                            , '48%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vertiefungsrichtung³:')
                            ->styleTextBold()
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Deepening is not empty) %}
                                    {{ Content.Input.Deepening }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom()
                        )
                    )->styleMarginTop('15px')
                )
                ->addSlice($this->getDescriptionHead())
                ->addSlice($this->getDescriptionContent('135px', '15px'))
                ->addSlice($this->getDateLine())
                ->addSlice($this->getSignPart())
                ->addSlice($this->getParentSign())
                ->addSlice($this->getInfo('70px',
                    'Notenerläuterung:',
                    '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                         6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)',
                    '¹ Gilt nicht an Gymnasien mit vertiefter Ausbildung gemäß § 4 SOGYA.',
                    '² In Klassenstufe 8 ist der Zusatz „mit informatischer Bildung“ zu streichen. 
                                    Beim sprachlichen Profil ist der Zusatz „mit informatischer Bildung“ zu
                                    streichen und die Fremdsprache anzugeben.',
                    '³ Nur für Schüler mit vertiefter Ausbildung gemäß § 4 SOGYA'))
            )
        );
    }
}
