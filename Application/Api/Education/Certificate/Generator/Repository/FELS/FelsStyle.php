<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class FelsStyle
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS
 */
class FelsStyle
{
    const TEXT_COLOR = '#0094CC';

    /**
     * @param $IsSample
     * @param $schoolTypeName
     *
     * @return Slice
     */
    public static function getHeader($IsSample, $schoolTypeName)
    {
        $slice =
            (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Freies Evangelisches Limbacher Schulzentrum')
                        ->styleTextBold()
                        ->styleTextSize('22px')
                        ->styleTextColor(self::TEXT_COLOR)
                        ->styleMarginTop('20px')
                    )
                );
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        ->setContent($schoolTypeName)
//                        ->styleTextBold()
//                        ->styleTextSize('22px')
//                        ->styleMarginTop('5px')
//                        ->styleTextColor(self::TEXT_COLOR)
//                    )
//                );
        // Sample
        if($IsSample){
            $slice
                ->addSection((new Section())
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                        ->styleMarginTop('5px')
                    )
                );
        }

        $section = new Section();
        $section->addSliceColumn($slice, '75%');

        $section
            ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/FELS.jpg', 'auto', '66px'))->styleAlignRight(), '25%');

        return
            (new Slice())
                ->addSection($section)
                ->stylePaddingTop('24px')
                ->styleHeight('80px');
    }
}