<?php
namespace SPHERE\Common\Frontend\Ajax\Template;

use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter\NotifyScript;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;

/**
 * Class Notify
 *
 * @package SPHERE\Common\Frontend\Ajax\Template
 */
class Notify extends AbstractTemplate
{
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_DANGER = 'danger';

    /**
     * Notify constructor.
     *
     * @param string $Title
     * @param string $Message ''
     * @param string $Type self::TYPE_INFO
     * @param int $Delay 1500
     */
    public function __construct($Title, $Message = '', $Type = self::TYPE_INFO, $Delay = 1500)
    {

        $Script = new NotifyScript($Title, $Message, $Type, $Delay);
        $this->setTemplate( new InlineReceiver() );
        $Emitter = new ScriptEmitter($this->getTemplate(), $Script);
        $Pipeline = new Pipeline();

        $Pipeline->addEmitter($Emitter);
        $this->getTemplate()->initContent($Pipeline);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getTemplate();
    }
}