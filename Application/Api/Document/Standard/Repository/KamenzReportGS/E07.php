<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 15:20
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E07
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E07. Schüler im Schuljahr {{ Content.Schooyear.Current }} nach Klassenstufen und der im vergangenen Schuljahr besuchten Schulart')
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
                    ->setContent('Im vergang. Schuljahr besuchte Schulart')
                    ->styleBorderRight()
                    ->stylePaddingTop('26.1px')
                    ->stylePaddingBottom('26.1px'), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe in diesem Schuljahr')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->styleBorderBottom()
                            ->setContent('4')
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '12.5%'
                        )
                    ), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorb.kl. u.<br/>-gr. f.<br/>Migranten')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleTextBold()
                            ->styleBorderBottom()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17.1px'), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '25%'
                        )
                    ), '20%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schulanfänger')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Grundschule')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Freie Waldorfschule')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Allgemeinbildende Förderschule')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sonstige Schularten')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zugang aus dem Ausland')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zugang aus anderen Bundesländern')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '5%'
                )
            );

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
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '5%'
                )
            );

        return $sliceList;
    }
}