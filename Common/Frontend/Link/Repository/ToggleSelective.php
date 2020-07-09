<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class ToggleCheckbox
 *
 * @internal EXPERIMENTAL
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class ToggleSelective extends Extension implements ILinkInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var string $Hash */
    protected $Hash = '';
    /** @var array $ToggleTarget */
    protected $ToggleTarget = array();
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var bool $isCheck */
    protected $isCheck = false;

    /**
     * @param string              $Name
     * @param array               $ToggleTarget
     * @param IIconInterface|null $Icon
     * @param bool                $ToolTip
     */
    public function __construct($Name, $ToggleTarget = array(), IIconInterface $Icon = null, $ToolTip = false)
    {

        $this->Template = $this->getTemplate(__DIR__.'/ToggleSelective.twig');
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        if ($ToolTip) {
            $this->Template->setVariable('ElementToolTip', $ToolTip);
        }
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementHash', $this->getHash());
        $this->Template->setVariable('ElementToggleTarget', $ToggleTarget);
        $this->Template->setVariable('isCheck', $this->isCheck);
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

        $this->Template->setVariable('ToggleTarget', $this->ToggleTarget);
        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {
        if (empty( $this->Hash )) {
            $this->Hash = 'Toggle-'.crc32( uniqid(__CLASS__, true) );
        }
        return $this->Hash;
    }

    /**
     * @return $this
     */
    public function setDisabled()
    {

        $this->Template->setVariable('Disabled', true);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param int $Mode
     * 1 = alle wählen
     * 2 = alle abwählen
     *
     * @return $this
     */
    public function setMode($Mode = 1)
    {
        $this->Template->setVariable('isActive', $Mode);
        return $this;
    }

}
