<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Extension\Extension;

/**
 * Class Exchange
 *
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class Exchange extends Extension implements ILinkInterface
{

    const EXCHANGE_TYPE_PLUS = 0;
    const EXCHANGE_TYPE_MINUS = 1;
    /** @var string $Content */
    private $Content = '';

    /**
     * Exchange constructor.
     *
     * @param int|string $Handler EXCHANGE_TYPE_{PLUS|MINUS} or Css-Selector
     * @param array      $Data
     * @param string     $Title   Button-Text
     */
    public function __construct($Handler, $Data = array(), $Title = '')
    {

        if (is_integer($Handler)) {

            switch ($Handler) {
                case Exchange::EXCHANGE_TYPE_MINUS:
                    $Button = new Center('<span class="btn btn-default">'.new MinusSign().' '.$Title.'</span>');
                    break;
                case Exchange::EXCHANGE_TYPE_PLUS:
                    $Button = new Center('<span class="btn btn-default">'.new PlusSign().' '.$Title.'</span>');
                    break;
                default:
                    $Button = new Center('<span class="btn btn-default">'.new PlusSign().' '.$Title.'</span>');
                    break;
            }
        } else {
            $Button = new Center('<span class="btn btn-default '.$Handler.'">'.new PlusSign().' '.$Title.'</span>');
        }

        $this->Content = $Button.'<span class="ExchangeData" style="display: none;">'
            .json_encode($Data, JSON_FORCE_OBJECT).'</span>';
    }


    /**
     * @return string
     */
    public function getName()
    {

        return '';
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Content;
    }

}
