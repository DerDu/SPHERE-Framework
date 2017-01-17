<?php
namespace SPHERE\Common\Frontend\Form\Repository\Button;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Form\IButtonInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\System\Extension\Extension;

/**
 * Class Close
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Button
 */
class Close extends Extension implements IButtonInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var IBridgeInterface $Template */
    protected $Template = null;

    /**
     * @param string $Name
     * @param IIconInterface $Icon
     */
    public function __construct($Name = 'Schließen', IIconInterface $Icon = null)
    {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__ . '/Close.twig');
        $this->Template->setVariable('Name', $Name);
        if (null !== $Icon) {
            $this->Template->setVariable('Icon', $Icon);
        } else {
            $this->Template->setVariable('Icon', new Disable());
        }
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Template->getContent();
    }
}
