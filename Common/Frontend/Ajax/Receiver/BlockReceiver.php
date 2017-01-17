<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;
/**
 * Class BlockReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class BlockReceiver extends AbstractReceiver
{

    /**
     * BlockReceiver constructor.
     *
     * @param string $Content
     */
    public function __construct( $Content = '' )
    {
        $this->setContent( $Content );
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return 'jQuery("' . $this->getSelector() . '").html(' . self::RESPONSE_CONTAINER . ');';
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        return '<div class="Dynamic-Frontend ' . $this->getIdentifier() . '">'.$this->getContent().'</div>';
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return '.'.$this->getIdentifier();
    }
}
