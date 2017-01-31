<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class ToggleCheckbox
 *
 * @internal EXPERIMENTAL
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class ToggleCheckbox extends Extension implements ILinkInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var IBridgeInterface $Template */
    protected $Template = null;

    private static $LinkCounter = 0;

    /**
     * ToggleCheckbox constructor.
     *
     * @param string $Name
     * @param IFrontendInterface $Frontend
     * @param IIconInterface|null $Icon
     * @param bool $ToolTip
     */
    public function __construct($Name, IFrontendInterface $Frontend, IIconInterface $Icon = null, $ToolTip = false)
    {
        // Generate Hash
        self::$LinkCounter++;

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/ToggleCheckbox.twig');

        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }

        if ($ToolTip) {
            if (is_string($ToolTip)) {
                $this->Template->setVariable('ElementToolTip', $ToolTip);
            } else {
                $this->Template->setVariable('ElementToolTip', $Name);
            }
        }

        $this->Template->setVariable('ElementHash', $this->getHash());
        $this->Template->setVariable('ElementName', $this->Name);
        $this->Template->setVariable('TargetHash', $Frontend->getHash());
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return 'Toggle-Hash-'.self::$LinkCounter;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @return ILinkInterface
     */
    public function setDisabled()
    {

        $this->Template->setVariable('Disabled', true);
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }

}