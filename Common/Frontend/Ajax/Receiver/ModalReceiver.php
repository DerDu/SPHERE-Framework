<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;

use MOC\V\Component\Template\Component\IBridgeInterface;

/**
 * Class ModalReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class ModalReceiver extends AbstractReceiver
{
    /** @var IBridgeInterface|null $Template */
    private $Template = null;
    /** @var null|string $Header */
    private $Header = null;
    /** @var null|string $Footer */
    private $Footer = null;

    /**
     * ModalReceiver constructor.
     * @param string|null $Header
     * @param string|null $Footer
     */
    public function __construct($Header = null, $Footer = null)
    {
        $this->Template = $this->getTemplate(__DIR__ . '/ModalReceiver.twig');
        $this->Header = $Header;
        $this->Footer = $Footer;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return 'jQuery("#' . $this->getSelector() . '").find("div.modal-body").html(' . self::RESPONSE_CONTAINER . ');  jQuery("#' . $this->getSelector() . '").modal();';
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return $this->getIdentifier();
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        $this->Template->setVariable('IDENTIFIER', $this->getIdentifier());
        $this->Template->setVariable('Header', $this->Header);
        $this->Template->setVariable('Footer', $this->Footer);
        $this->Template->setVariable('Content', $this->getContent());
        return $this->Template->getContent();
    }
}
