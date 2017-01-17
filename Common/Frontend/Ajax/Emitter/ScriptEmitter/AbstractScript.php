<?php
namespace SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;

use SPHERE\System\Extension\Extension;

/**
 * Class AbstractScript
 *
 * @package SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter
 */
abstract class AbstractScript extends Extension
{
    /** @var string $Script */
    private $Script = '';

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->Script;
    }

    /**
     * @param string $Script
     * @return $this
     */
    public function setScript($Script)
    {
        $this->Script = (string)$Script;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getScript();
    }
}
