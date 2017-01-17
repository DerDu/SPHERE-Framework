<?php
namespace SPHERE\Common\Frontend\Ajax\Emitter;

use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter\AbstractScript;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;

/**
 * Class ScriptEmitter
 *
 * @package SPHERE\Common\Frontend\Ajax\Emitter
 */
class ScriptEmitter extends AbstractEmitter
{

    /** @var string $Module */
    private $Module = 'ModAlways';
    /** @var string $Content */
    private $Content = '';

    /**
     * ScriptEmitter constructor.
     *
     * see /Common/Script for available Modules
     *
     * @param AbstractReceiver|AbstractReceiver[] $Receiver
     * @param AbstractScript|string $jScript JavaScript
     * @param string $Module "ModAlways"
     */
    public function __construct($Receiver, $jScript, $Module = 'ModAlways')
    {
        $this->Module = $Module;
        $this->Content = (string)$jScript;
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
        $Template = $this->getTemplate(__DIR__ . '/ScriptEmitter.twig');

        $Template->setVariable('Module', $this->Module);
        $Template->setVariable('Content', $this->Content);

        return json_encode( $Template->getContent() );
    }
}
