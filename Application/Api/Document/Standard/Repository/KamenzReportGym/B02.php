<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 08:57
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class B02
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('B02. Absolventen/Abgänger aus dem Schuljahr {{ Content.Schoolyear.Past }} nach Geburtsjahren und Abschlussarten')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Geburts-<br/>jahr')
                    ->styleBorderRight()
                    ->stylePaddingTop('9.1px')
                    ->stylePaddingBottom('9px'), '12%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Abgangszeugnis<br/><b>ohne</b> Vermerk')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '17.6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Hauptschul-<br/>abschluss')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '17.6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Realschul-<br/>abschluss')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '17.6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Allgemeine<br/>Hochschulreife')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , '17.6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleTextBold()
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '17.6%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleTextBold()
                            ->styleBorderRight(), '8.8%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleTextBold(), '8.8%'
                        )
                    )
                )
            );

        for ($i = 0; $i < 10; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B02.Y' . $i . '.YearName is not empty) %}
                                {{ Content.B02.Y' . $i . '.YearName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                );

            for ($j = 0; $j < 4; $j++) {
                switch ($j) {
                    case 0:
                        $identifier = 'Leave';
                        break;
                    case 1:
                        $identifier = 'MsAbsHs';
                        break;
                    case 2:
                        $identifier = 'MsAbsRs';
                        break;
                    case 3:
                        $identifier = 'GymAbitur';;
                        break;
                    default:
                        $identifier = '';
                }
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.B02.Y' . $i . '.' . $identifier . '.m is not empty) %}
                                {{ Content.B02.Y' . $i . '.' . $identifier . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '8.8%'
                    );
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.B02.Y' . $i . '.' . $identifier . '.w is not empty) %}
                                {{ Content.B02.Y' . $i . '.' . $identifier . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '8.8%'
                    );
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B02.Y' . $i . '.m is not empty) %}
                                {{ Content.B02.Y' . $i . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->styleBorderRight(), '8.8%'
                );
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B02.Y' . $i . '.w is not empty) %}
                                {{ Content.B02.Y' . $i . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    , '8.8%'
                );

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection($section);
        }

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '8.8%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '8.8%'
                )
            );

        return $sliceList;
    }

}