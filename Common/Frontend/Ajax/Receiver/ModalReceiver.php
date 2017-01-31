<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;
/**
 * Class ModalReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class ModalReceiver extends AbstractReceiver
{
    /**
     * @return string
     */
    public function getHandler()
    {
        return 'jQuery("#' . $this->getSelector() . '").find("div.modal-body").html(' . self::RESPONSE_CONTAINER . ');  jQuery("#' . $this->getSelector() . '").modal();';
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        $Template = $this->getTemplate( __DIR__.'/ModalReceiver.twig' );
        $Template->setVariable( 'IDENTIFIER', $this->getIdentifier() );
        return $Template->getContent();
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return $this->getIdentifier();
    }
}