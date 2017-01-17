<?php
namespace SPHERE\Common\Frontend\Ajax\Template;

use SPHERE\Common\Frontend\Ajax\Emitter\AbstractEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter\CloseModalScript;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;

/**
 * Class CloseModal
 *
 * @package SPHERE\Common\Frontend\Ajax\Template
 */
class CloseModal extends AbstractTemplate
{
    /**
     * CloseModal constructor.
     *
     * @param ModalReceiver|string $TargetModalReceiver Object or Identifier
     */
    public function __construct($TargetModalReceiver)
    {
        // Sanitize $ModalReceiver
        if (!$TargetModalReceiver instanceof ModalReceiver) {
            $TargetModalReceiver = (new ModalReceiver())->setIdentifier($TargetModalReceiver);
        }

        // Construct Emitter
        $Script = new CloseModalScript($TargetModalReceiver);
        $Emitter = new ScriptEmitter( self::CloseModalReceiver(), $Script);

        $this->setTemplate($Emitter);
    }

    /**
     * @return AbstractEmitter
     */
    public function getEmitter()
    {
        return $this->getTemplate();
    }

    /**
     * @return AbstractReceiver
     */
    public static function CloseModalReceiver()
    {
        return (new InlineReceiver())->setIdentifier( InlineReceiver::IDENTIFIER_PREFIX.'-Template-CloseModal');
    }
}