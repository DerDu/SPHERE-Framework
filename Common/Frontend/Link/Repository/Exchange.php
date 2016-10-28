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
     * @param int|string $ExchangeType EXCHANGE_TYPE_{PLUS|MINUS}
     * @param array $Data Exchange-Payload
     * @param string $Title Button-Text
     * @param string $HandlerClass Css-Selector
     */
    public function __construct($ExchangeType = self::EXCHANGE_TYPE_PLUS, $Data = array(), $Title = '', $HandlerClass = '')
    {

        if (is_integer($ExchangeType) && empty($HandlerClass)) {

            switch ($ExchangeType) {
                case Exchange::EXCHANGE_TYPE_MINUS:
                    $Button = new Center('<span class="btn btn-default">' . new MinusSign() . ' ' . $Title . '</span>');
                    break;
                case Exchange::EXCHANGE_TYPE_PLUS:
                    $Button = new Center('<span class="btn btn-default">' . new PlusSign() . ' ' . $Title . '</span>');
                    break;
                default:
                    $Button = new Center('<span class="btn btn-default">' . new PlusSign() . ' ' . $Title . '</span>');
                    break;
            }
        } else {
            switch ($ExchangeType) {
                case Exchange::EXCHANGE_TYPE_MINUS:
                    $Button = new Center('<span class="btn btn-default ' . $HandlerClass . '">' . new MinusSign() . ' ' . $Title . '</span>');
                    break;
                case Exchange::EXCHANGE_TYPE_PLUS:
                    $Button = new Center('<span class="btn btn-default ' . $HandlerClass . '">' . new PlusSign() . ' ' . $Title . '</span>');
                    break;
                default:
                    $Button = new Center('<span class="btn btn-default ' . $HandlerClass . '">' . new PlusSign() . ' ' . $Title . '</span>');
                    break;
            }
        }

        $this->Content = $Button . '<span class="ExchangeData" style="display: none;">'
            . json_encode($Data, JSON_FORCE_OBJECT) . '</span>';
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
