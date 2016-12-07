<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;

use SPHERE\Common\Frontend\Ajax\IReceiverInterface;

/**
 * Class AbstractReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
abstract class AbstractReceiver implements IReceiverInterface
{
    /** @var int $IdentifierCounter */
    private static $IdentifierCounter = 0;
    /** @var string $ReceiverIdentifier */
    private $Identifier = '';

    const RESPONSE_CONTAINER = 'Response';

    /**
     * AbstractReceiver constructor.
     */
    public function __construct()
    {
        self::$IdentifierCounter++;
        $this->Identifier = 'Sphere-Ajax-Node-' . self::$IdentifierCounter;
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
     */
    public function setIdentifier($Identifier)
    {
        $this->Identifier = $Identifier;
    }
}