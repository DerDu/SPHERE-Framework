<?php
namespace SPHERE\Common\Frontend\Ajax\Template;

use SPHERE\Common\Frontend\Ajax\Emitter\AbstractEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;

/**
 * Class AbstractTemplate
 *
 * @package SPHERE\Common\Frontend\Ajax\Template
 */
abstract class AbstractTemplate
{
    /** @var null|AbstractReceiver|AbstractEmitter|Pipeline $Template */
    private $Template = null;

    /**
     * @return null|AbstractReceiver|AbstractEmitter|Pipeline
     */
    protected function getTemplate()
    {
        return $this->Template;
    }

    /**
     * @param null|AbstractReceiver|AbstractEmitter|Pipeline $Template
     *
     * @return $this
     */
    protected function setTemplate($Template)
    {
        $this->Template = $Template;
        return $this;
    }
}