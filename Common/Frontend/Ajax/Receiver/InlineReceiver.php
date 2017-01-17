<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;
/**
 * Class InlineReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class InlineReceiver extends AbstractReceiver
{

    /**
     * InlineReceiver constructor.
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
        return '<span class="Dynamic-Frontend ' . $this->getIdentifier() . '">'.$this->getContent().'</span>';
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return '.'.$this->getIdentifier();
    }
}
