<?php
namespace SPHERE\Common\Frontend\Ajax\Emitter;

use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractEmitter
 *
 * @package SPHERE\Common\Frontend\Ajax
 */
abstract class AbstractEmitter extends Extension
{
    /** @var AbstractReceiver[] $AjaxReceiver */
    private $AjaxReceiver = array();
    /** @var string $SuccessTitle */
    private $SuccessTitle = '';
    /** @var string $SuccessMessage */
    private $SuccessMessage = '';
    /** @var string $LoadingTitle */
    private $LoadingTitle = '';
    /** @var string $LoadingMessage */
    private $LoadingMessage = '';

    /**
     * @return AbstractReceiver[]
     */
    final public function getAjaxReceiver()
    {
        return $this->AjaxReceiver;
    }

    /**
     * @param AbstractReceiver[] $AjaxReceiver
     * @throws \Exception
     */
    final public function setAjaxReceiver($AjaxReceiver)
    {
        foreach( $AjaxReceiver as $Receiver ) {
            if( !$Receiver instanceof AbstractReceiver ) {
                throw new \Exception( 'Parameter must be valid Receiver. See AbstractReceiver' );
            }
        }
        $this->AjaxReceiver = $AjaxReceiver;
    }

    /**
     * @param string $Title
     * @param string $Message
     * @return $this
     */
    final public function setSuccessMessage($Title, $Message = '')
    {
        $this->SuccessTitle = $Title;
        $this->SuccessMessage = $Message;
        return $this;
    }

    /**
     * @param string $Title
     * @param string $Message
     * @return $this
     */
    final public function setLoadingMessage($Title, $Message = '')
    {
        $this->LoadingTitle = $Title;
        $this->LoadingMessage = $Message;
        return $this;
    }

    /**
     * @return string
     */
    public function getSuccessTitle()
    {
        return $this->SuccessTitle;
    }

    /**
     * @return string
     */
    public function getSuccessMessage()
    {
        return $this->SuccessMessage;
    }

    /**
     * @return string
     */
    public function getLoadingTitle()
    {
        return $this->LoadingTitle;
    }

    /**
     * @return string
     */
    public function getLoadingMessage()
    {
        return $this->LoadingMessage;
    }
}