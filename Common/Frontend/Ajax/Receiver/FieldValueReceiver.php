<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;

use SPHERE\Common\Frontend\Form\Repository\AbstractField;

/**
 * Class FieldValueReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class FieldValueReceiver extends AbstractReceiver
{

    /** @var null|AbstractField $Field */
    private $Field = null;

    /**
     * FieldValueReceiver constructor.
     *
     * @param AbstractField $Field
     */
    public function __construct( AbstractField $Field )
    {
        $this->Field = $Field;
        parent::__construct();
        $this->setIdentifier( $this->Field->getName() );
    }

    /**
     * @return string
     */
    public function getSelector()
    {
        return '[name="' . $this->getIdentifier() . '"]';
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return 'jQuery(\''.$this->getSelector().'\').val(' . self::RESPONSE_CONTAINER . ');';
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        return (string)$this->Field;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Field->getName();
    }
}