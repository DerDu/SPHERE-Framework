<?php
namespace SPHERE\Common\Frontend\Ajax\Emitter;

use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;

/**
 * Class ClientEmitter
 *
 * @package SPHERE\Common\Frontend\Ajax\Emitter
 */
class ClientEmitter extends AbstractEmitter
{

    /** @var string $Content */
    private $Content = '';

    /**
     * ClientEmitter constructor.
     *
     * @param AbstractReceiver|AbstractReceiver[] $Receiver
     * @param mixed $Content
     */
    public function __construct($Receiver, $Content = '')
    {
        $this->Content = json_encode((string)$Content);
        if (is_array($Receiver)) {
            $this->setAjaxReceiver($Receiver);
        } else {
            $this->setAjaxReceiver(array($Receiver));
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->Content;
    }


}
