<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 14:28
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E04_1
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E04.1 Schüler im Schuljahr {{ Content.Schoolyear.Current }} nach der Anzahl der derzeit erlernten Fremdsprachen und Klassenstufen')
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
                    ->setContent('Anzahl der<br/>Fremdsprachen')
                    ->styleBorderRight()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px'), '20%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe'), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '10%'
                        )
                    ), '60%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Vorb.kl. u.<br/>-gruppen f.<br/>Migranten')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleTextBold()
                    ->stylePaddingTop('17.1px')
                    ->stylePaddingBottom('17.1px'), '10%'
                )
            );

        //ToDo: keine
        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('keine')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey'), '10%'
                )
            );

        //ToDo: eine
        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('eine')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey'), '10%'
                )
            );

        //ToDo: zwei
        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('zwei')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey'), '10%'
                )
            );

        //ToDo: drei
        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('drei')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey'), '10%'
                )
            );

        //ToDo: vier und mehr
        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('vier und mehr')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('00')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey'), '10%'
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
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '10%'
                )
            );

        return $sliceList;
    }
}