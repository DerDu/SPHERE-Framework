<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class CosHjPri
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CosHjPri extends Certificate
{

    const TEXT_SIZE = '13px';

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
                    ->addElementColumn(
                        $IsSample
                            ? ( new Element\Sample() )
                            ->setContent('MUSTER')
                            ->styleTextSize('30px')
                            : ( new Element() )
                        , '25%')
                    ->addElementColumn(( new Element() )
                        ->setContent('FREISTAAT SACHSEN')
                        ->styleTextSize('20px')
                        ->styleAlignCenter()
                        , '50%')
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        ->styleAlignCenter()
                        , '25%')
                );
        } else {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() )
                        , '25%')
                    ->addElementColumn(( new Element() )
                        ->setContent('FREISTAAT SACHSEN')
                        ->styleTextSize('20px')
                        ->styleAlignCenter()
                        , '50%')
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        ->styleAlignCenter()
                        , '25%')
                );
        }

        return ( new Frame() )->addDocument(( new Document() )
            ->addPage(( new Page() )
                ->addSlice($Header)
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->setContent('Evangelische Schule Coswig')
                                ->styleTextSize('20px')
                                ->styleTextBold()
                                ->styleAlignCenter()
                                ->styleMarginTop('15px')
                            )
                            ->addElement((new Element())
                                ->setContent('staatlich anerkannte Ersatzschule')
                                ->styleTextSize('16px')
                                ->styleAlignCenter()
                            )
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Halbjahresinformation der Schule (Primarstufe)')
                        ->styleTextSize('20px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('20px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
//                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '57%')
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '16%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Year }}')
//                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '10%')
                    )->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vor- und Zuname:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '18%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
//                            ->styleBorderBottom()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '64%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
//                            ->styleBorderBottom()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '18%')
                    )->styleMarginTop('25px')
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            ->setContent('
                                {% if(Content.Student.Course.Degree is not empty) %}
                                        nahm am Unterricht der Schulart Mittelschule mit dem Ziel des
                                        {{ Content.Student.Course.Degree }} teil.
                                    {% else %}
                                        &nbsp;
                                    {% endif %}')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '100%')
                    )->styleMarginTop('12px')
                )
                ->addSlice($this->getGradeLanes(self::TEXT_SIZE, false))
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Leistung in den einzelnen Fächern')
                        ->styleTextItalic()
                        ->styleTextBold()
                        ->styleMarginTop('20px')
                        ->styleTextSize(self::TEXT_SIZE)
                    )
                )
                ->addSlice($this->getSubjectLanes(true, array(), self::TEXT_SIZE, false)
                    ->styleHeight('210px'))
                ->addSlice((new Slice())
                    ->addElement(( new Element() )
                        ->setContent('Bemerkungen:')
                        ->styleTextItalic()
                        ->styleTextSize(self::TEXT_SIZE)
                    )
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element() )
                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '85%')
                    )
                    ->styleHeight('175px')
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addElementColumn((new Element())
                            ->setContent('Fehltage entschuldigt:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            , '5%')
                        ->addElementColumn((new Element())
                            ->setContent('unentschuldigt:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '15%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            , '44%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '20%')
                        ->addElementColumn((new Element())
                            , '56%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulleiter/in')
                            ->styleTextSize('11px')
                            , '35%'
                        )
                        ->addElementColumn((new Element())
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Klassenleiter/in')
                            ->styleTextSize('11px')
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.DivisionTeacher.Name is not empty) %}
                                    {{ Content.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
                            , '35%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Zur Kenntnis genommen:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '75%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Personensorgeberechtigte/r')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '100%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Notenstufen 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                            ->styleTextSize('9px')
                            ->styleMarginTop('17px')
                        )
                    )
                )
            )
        );
    }
}
