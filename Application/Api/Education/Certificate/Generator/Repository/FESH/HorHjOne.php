<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FESH;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class HorHjOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class HorHjOne extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        $TextSize = '12px';

        $Header = ( ( new Element\Sample() )
            ->styleTextSize('30px')
            ->styleHeight('12px')
        );

        return ( new Frame() )->addDocument(( new Document() )
            ->addPage(( new Page() )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addElementColumn(
                            ( $IsSample
                                ? $Header
                                : ( ( new Element() )
                                    ->setContent('&nbsp;')
                                    ->styleHeight('12px')
                                )
                            )
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/Hormersdorf_logo.jpg', '155px', '90px') )
                            ->styleAlignCenter()
                            , '25%')
                        ->addSliceColumn(( new Slice() )
                            ->addSection(( new Section() )
                                ->addElementColumn(( new Element() )
                                    ->setContent('Name der Schule:')
                                    ->styleTextSize('11px')
                                    ->styleMarginTop('6px')
                                    , '20%')
                                ->addElementColumn(( new Element() )
                                    ->setContent('Freie Evangelische Grundschule Hormersdorf')
                                    ->styleTextSize('17px')
                                    ->styleTextBold()
                                    ->styleBorderBottom('0.5px', '#767676')
                                    ->styleAlignCenter()
                                    , '78%')
                                ->addElementColumn(( new Element() )
                                    , '2%'
                                )
                            )
                            ->addSection(( new Section() )
                                ->addElementColumn(( new Element() )
                                    , '27%')
                                ->addElementColumn(( new Element() )
                                    ->setContent('(Staatlich anerkannte Ersatzschule)')
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    , '71%')
                                ->addElementColumn(( new Element() )
                                    , '2%'
                                )
                            )
                            ->styleMarginTop('40px')
                            , '75%')
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            ->setContent('HALBJAHRESINFORMATION DER GRUNDSCHULE')
                            ->styleTextSize('24px')
                            ->styleTextBold()
                            ->styleAlignCenter()
                            ->styleMarginTop('20px')
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->setContent('Klasse')
                            ->styleTextSize($TextSize)
                            ->styleMarginTop('33px')
                            , '8%')
                        ->addElementColumn(( new Element() )
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->styleMarginTop('32px')
                            , '43%')
                        ->addElementColumn(( new Element() )
                            ->setContent('1. Schulhalbjahr')
                            ->styleTextSize($TextSize)
                            ->styleAlignRight()
                            ->styleMarginTop('33px')
                            , '30%')
                        ->addElementColumn(( new Element() )
                            ->setContent('{{ Content.Division.Data.Year }}')
                            ->styleAlignCenter()
                            ->styleMarginTop('32px')
                            , '15%')
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->styleBorderBottom('0.5px', '#767676')
                            , '96%'
                        )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->setContent('Vor- und Zuname:')
                            ->styleTextSize($TextSize)
                            ->styleMarginTop('7px')
                            , '18%')
                        ->addElementColumn(( new Element() )
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleMarginTop('5px')
                            , '78%')
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->styleBorderBottom('0.5px', '#767676')
                            , '96%'
                        )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('13px')
                            ->styleHeight('540px')
                            ->styleMarginTop('35px')
                            ->stylePaddingLeft('15px')
                            ->stylePaddingRight('15px')
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->setContent('Fehltage entschuldigt:')
                            ->styleTextSize($TextSize)
                            ->styleMarginTop('2px')
                            , '19%')
                        ->addElementColumn(( new Element() )
                            ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            , '12%')
                        ->addElementColumn(( new Element() )
                            ->setContent('unentschuldigt:')
                            ->styleTextSize($TextSize)
                            ->styleMarginTop('2px')
                            , '14%')
                        ->addElementColumn(( new Element() )
                            ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            , '51%')
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->styleBorderBottom('0.5px', '#767676')
                            , '96%'
                        )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->setContent('Datum:')
                            ->styleTextSize($TextSize)
                            ->styleMarginTop('32px')
                            , '15%')
                        ->addElementColumn(( new Element() )
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleMarginTop('30px')
                            , '20%')
                        ->addElementColumn(( new Element() )
                            ->styleMarginTop('30px')
                            , '63%')
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->styleBorderBottom('0.5px', '#767676')
                            , '35%'
                        )
                        ->addElementColumn(( new Element() )
                            , '63%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            , '33%')
                        ->addElementColumn(( new Element() )
                            ->setContent('Dienststempel der Schule')
                            ->styleTextSize('9px')
                            ->styleAlignCenter()
                            ->styleMarginTop('30px')
                            , '30%')
                        ->addElementColumn(( new Element() )
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('0.5px', '#767676')
                            ->styleMarginTop('30px')
                            , '33%')
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '35%')
                        ->addElementColumn(( new Element() )
                            , '30%')
                        ->addElementColumn(( new Element() )
                            ->setContent('Klassenlehrer/in')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '33%')
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '35%')
                        ->addElementColumn(( new Element() )
                            , '30%')
                        ->addElementColumn(( new Element() )
                            ->setContent('{% if(Content.DivisionTeacher.Name is not empty) %}
                                    {{ Content.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
                            ->styleAlignCenter()
                            , '33%')
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                        ->addElementColumn(( new Element() )
                            ->setContent('Zur Kenntnis genommen:')
                            ->styleTextSize($TextSize)
                            ->styleMarginTop('20px')
                            , '96%'
                        )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            , '23%'
                        )
                        ->addElementColumn(( new Element() )
                            ->styleBorderBottom('0.5px', '#767676')
                            , '75%'
                        )
                        ->addElementColumn(( new Element() )
                            , '2%'
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            ->setContent('Personensorgeberechtigte/r')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->stylePaddingLeft('15px')
                            ->stylePaddingRight('15px')
                        )
                    )
                    ->styleBorderAll('2px', '#767676')
                    ->styleHeight('1020px')
                )
            )
        );
    }
}
