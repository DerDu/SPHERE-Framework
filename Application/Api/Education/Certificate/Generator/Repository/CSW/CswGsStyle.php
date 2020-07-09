<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CswGsStyle
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswGsStyle
{
    /**
     * @param $IsSample
     *
     * @return Slice
     */
    public static function getHeader($IsSample)
    {
        $height = '66px';
        $width = '214px';

        $slice = new Slice();
        $section = new Section();

        // Individually Logo
        $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/CSW_GS.jpg', 'auto', $height)), '39%');

        // Sample
        if($IsSample){
            $section->addElementColumn((new Element\Sample())->styleTextSize('30px'));
        } else {
            $section->addElementColumn((new Element()), '22%');
        }

        // Standard Logo
        $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
            $width, $height))
            ->styleAlignRight()
            , '39%');

        $slice->stylePaddingTop('24px');
        $slice->styleHeight('100px');
        $slice->addSection($section);

        return $slice;
    }

    public static function buildSecondPage(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element\Image('/Common/Style/Resource/Logo/CSW_GS.jpg',
                    '', '165px'))
                    ->styleAlignCenter()
                    ->styleMarginTop('50px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('
                        Grumbach, 
                        {% if(Content.P'.$personId.'.Input.Date is not empty) %}
                            {{ Content.P'.$personId.'.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    ->styleAlignRight()
                    ->styleFontFamily('AndikaNewBasic')
                    ->styleHeight('11pt')
                    ->styleMarginTop('50px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.StudentLetter is not empty) %}
                            {{ Content.P' . $personId . '.Input.StudentLetter|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    ->styleFontFamily('AndikaNewBasic')
                    ->styleHeight('11pt')
                    ->styleLineHeight('90%')
                    ->styleMarginTop('20px')
                )
            );
    }
}