<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;

use SPHERE\Common\Frontend\Ajax\IReceiverInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
abstract class AbstractReceiver extends Extension implements IReceiverInterface
{

    const IDENTIFIER_PREFIX = 'Sphere-Ajax-Receiver';

    /** @var string $Identifier */
    private $Identifier = '';
    /** @var string $Content */
    private $Content = '';

    const RESPONSE_CONTAINER = 'Response';

    /**
     * AbstractReceiver constructor.
     */
    public function __construct()
    {
        $this->Identifier = self::IDENTIFIER_PREFIX.'-' . sha1(uniqid('',true));
    }

    /**
     * @return string
     */
    final public function getIdentifier()
    {
        return $this->Identifier;
    }

    /**
     * @return string
     */
    final public function __toString()
    {
        return $this->getContainer();
    }

    /**
     * @return string
     */
    abstract public function getHandler();

    /**
     * @param string $Identifier
     * @return $this
     */
    public function setIdentifier($Identifier)
    {
        $this->Identifier = $Identifier;
        return $this;
    }

    /**
     * @param $Content
     * @return $this
     */
    protected function setContent( $Content )
    {
        $this->Content = $Content;
        return $this;
    }

    /**
     * @return string
     */
    protected function getContent()
    {
        return $this->Content;
    }

    /**
     * @param string $Content
     *
     * @return $this
     */
    public function initContent( $Content )
    {
        $this->setContent( $Content );
        return $this;
    }
}