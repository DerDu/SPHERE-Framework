<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESZC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class CheJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CheJ extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        if( $IsSample ) {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() )
                        ->setContent('Chemnitz Jahreszeugnis.pdf')
                        ->styleTextSize('12px')
                        ->styleTextColor('#CCC')
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn(( new Element\Sample() )
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                            '200px') )
                        , '25%')
                );
        } else {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() ), '25%')
                    ->addElementColumn(( new Element() ))
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                            '200px') )
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
                        ->setContent('{% if(Content.Company.Data.Name is not empty) %}
                                {{ Content.Company.Data.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        ->styleTextSize('16px')
                        ->styleTextBold()
                    )->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Name der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Jahreszeugnis der Mittelschule')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('15px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse:')
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            , '55%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr:')
                            ->styleAlignRight()
                            , '18%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Year }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '13%'
                        )
                    )->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname und Name:')
                            , '21%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom()
                            , '79%')
                    )->styleMarginTop('5px')
                )
                ->addSlice( $this->getGradeLanes() )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Einschätzung:')
                            , '16%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Rating is not empty) %}
                                    {{ Content.Input.Rating|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleHeight('60px')
                            , '84%')
                    )
                    ->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                    )
                )
                ->addSlice( $this->getSubjectLanes() )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Wahlpflichtbereich:')
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Choose is not empty) %}
                                    {{ Content.Input.Choose }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')//ToDO Wahlpflichtbereich
                            ->styleBorderBottom()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom('1px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            , '9%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Data.ToDO is not empty) %}
                                    Neigungskurs (Neigungskursbereich)
                                {% else %}
                                    {% if(Content.Person.Data.ToDO is not empty) %}
                                        2. Fremdsprache (abschlussorientiert)
                                    {% else %}
                                        &nbsp;
                                    {% endif %}
                                {% endif %}')//ToDO Wahlpflichtbereich
                            ->styleTextSize('11px')
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Bemerkungen:')
                            , '16%')
                        ->addElementColumn((new Element())
                            ->setContent('Fehltage entschuldigt:')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignRight()
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    0
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                            , '18%')
                        ->addElementColumn((new Element())
                            ->setContent('unentschuldigt:')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignRight()
                            , '14%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    0
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                            , '18%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                            , '4%')
                    )
                    ->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleHeight('88px')
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Versetzungsvermerk:')
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Transfer) %}
                                    {{ Content.Input.Transfer }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '78%')
                    )
                    ->styleMarginTop('5px')
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
                            , '23%')
                        ->addElementColumn((new Element())
                            , '70%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('Dienstsiegel der Schule')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '40%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#000')
                            , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulleiter(in)')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '5%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            , '5%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenlehrer(in)')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '35%')
                        ->addElementColumn((new Element())
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.DivisionTeacher.Name is not empty) %}
                                    {{ Content.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
                            ->styleAlignCenter()
                            , '30%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Zur Kenntnis genommen:')
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            , '40%')
                        ->addElementColumn((new Element())
                            , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('Eltern')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '40%')
                        ->addElementColumn((new Element())
                            , '30%')
                    )->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element())
                            , '70%')
                    )->styleMarginTop('11px')
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Notenerläuterung:'
                                .new Container('1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                                6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)'))
                            ->styleTextSize('9.5px')
                            , '30%')
                    )
                )
            )
        );
    }
}
