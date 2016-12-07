<?php
namespace SPHERE\Common\Frontend\Ajax\Emitter;

use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractEmitter
 *
 * @package SPHERE\Common\Frontend\Ajax
 */
abstract class AbstractEmitter extends Extension
{
    /** @var string $AjaxUri */
    private $AjaxUri = '';
    /** @var array $AjaxGetPayload */
    private $AjaxGetPayload = array();
    /** @var array $AjaxPostPayload */
    private $AjaxPostPayload = array();
    /** @var AbstractReceiver[] $AjaxReceiver */
    private $AjaxReceiver = array();

    /**
     * Emitter constructor.
     * @param Route $Route
     * @param AbstractReceiver|AbstractReceiver[] $Receiver
     */
    public function __construct( Route $Route, $Receiver )
    {
        $this->AjaxUri = $Route;
        if( is_array( $Receiver ) ) {
            $this->AjaxReceiver = $Receiver;
        } else {
            $this->AjaxReceiver = array( $Receiver );
        }
    }

    /**
     * @param array $Data
     * @return $this
     */
    final public function setGetPayload($Data = array())
    {
        $this->AjaxGetPayload = $Data;
        return $this;
    }

    /**
     * @param array $Data
     * @return $this
     */
    final public function setPostPayload($Data = array())
    {
        $this->AjaxPostPayload = $Data;
        return $this;
    }

    /**
     * @return string
     */
    final public function getAjaxGetPayload()
    {
        if (!empty($this->AjaxGetPayload)) {
            $Signature = (new Authenticator(new Get()))->getAuthenticator();
            $Query = '?' . http_build_query($Signature->createSignature($this->AjaxGetPayload, $this->AjaxUri));
        } else {
            $Query = '';
        }
        return $Query;
    }

    /**
     * @return string
     */
    final public function getAjaxPostPayload()
    {
        if (!empty($this->AjaxPostPayload)) {
            // MUST NOT BE USED
            // $Signature = (new Authenticator(new Post()))->getAuthenticator();
            // $Query = $Signature->createSignature($this->AjaxPayload, $this->AjaxUri);
            $Query = $this->AjaxPostPayload;
        } else {
            $Query = array();
        }

        return json_encode( $Query, JSON_FORCE_OBJECT );
    }

    /**
     * @return string
     */
    final public function getAjaxUri()
    {
        return $this->AjaxUri;
    }

    /**
     * @return AbstractReceiver[]
     */
    final public function getAjaxReceiver()
    {
        return $this->AjaxReceiver;
    }
}