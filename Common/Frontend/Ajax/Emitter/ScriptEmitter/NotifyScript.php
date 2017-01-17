<?php
namespace SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;

/**
 * Class NotifyScript
 *
 * @package SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter
 */
class NotifyScript extends AbstractScript
{
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_DANGER = 'danger';

    /**
     * NotifyScript constructor.
     * @param string $Title
     * @param string $Message
     * @param string $Type
     * @param int $Delay 0 = Do not close automatically
     */
    public function __construct( $Title, $Message = '', $Type = self::TYPE_INFO, $Delay = 1000 )
    {
        $Template = $this->getTemplate(__DIR__ . '/NotifyScript.twig');
        $Template->setVariable('Title', json_encode( $Title ));
        $Template->setVariable('Message', json_encode( $Message ));
        $Template->setVariable('Type', json_encode( $Type ));
        $Template->setVariable('Delay', json_encode( $Delay ));
        $this->setScript($Template->getContent());
    }
}
