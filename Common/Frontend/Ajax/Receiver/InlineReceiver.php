<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;
/**
 * Class InlineReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class InlineReceiver extends AbstractReceiver
{
    /**
     * @return string
     */
    public function getHandler()
    {
        return 'jQuery("' . $this->getSelector() . '").html(' . self::RESPONSE_CONTAINER . ');';
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        return '<span class="' . $this->getIdentifier() . '"></span>';
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return '.'.$this->getIdentifier();
    }
}