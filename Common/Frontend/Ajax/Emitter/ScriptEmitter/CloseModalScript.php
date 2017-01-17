<?php
namespace SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;

use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;

/**
 * Class CloseModalScript
 * @package SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter
 */
class CloseModalScript extends AbstractScript
{
    /**
     * CloseModalScript constructor.
     *
     * @param ModalReceiver $ModalReceiver
     */
    public function __construct(ModalReceiver $ModalReceiver)
    {
        $Template = $this->getTemplate(__DIR__ . '/CloseModalScript.twig');
        $Template->setVariable('Modal', $ModalReceiver->getIdentifier());
        $this->setScript($Template->getContent());
    }
}
