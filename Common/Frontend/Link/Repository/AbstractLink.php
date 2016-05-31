<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractLink
 *
 * @package SPHERE\Common\Frontend\Link\Repository
 */
abstract class AbstractLink extends Extension implements ILinkInterface
{

    const TYPE_LINK = 'italic';
    const TYPE_DEFAULT = 'btn btn-default';
    const TYPE_DANGER = 'btn btn-danger';
    const TYPE_WARNING = 'btn btn-warning';
    const TYPE_SUCCESS = 'btn btn-success';
    const TYPE_PRIMARY = 'btn btn-primary';

    protected $Type = self::TYPE_DEFAULT;
    /** @var string $Name */
    protected $Name;
    /** @var string $Path */
    protected $Path;
    /** @var string $Link */
    protected $Link;
    /** @var IBridgeInterface $Template */
    protected $Template = null;

    /**
     * AbstractLink constructor.
     *
     * @param string              $Name
     * @param string              $Path
     * @param IIconInterface|null $Icon
     * @param array               $Data
     * @param bool|string         $ToolTip
     */
    public function __construct($Name, $Path, IIconInterface $Icon = null, $Data = array(), $ToolTip = false)
    {

        $this->setName($Name);
        if (false !== strpos($Path, '\\')) {
            $this->Path = new Route($Path);
        } else {
            $this->Path = $Path;
        }

        $this->Template = $this->getTemplate(__DIR__.'/Link.twig');
        $this->Template->setVariable('ElementType', $this->Type);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        if (!empty( $Data )) {
            $Signature = (new Authenticator(new Get()))->getAuthenticator();
            $Data = '?'.http_build_query($Signature->createSignature($Data, $this->Path));
        } else {
            $Data = '';
        }
        $this->Link = $this->getRequest()->getUrlBase().$this->Path.$Data;
        $this->Template->setVariable('ElementPath', $this->Path.$Data);
        $this->Template->setVariable('UrlBase', $this->getRequest()->getUrlBase());
        if ($ToolTip) {
            if (is_string($ToolTip)) {
                $this->Template->setVariable('ElementToolTip', $ToolTip);
            } else {
                $this->Template->setVariable('ElementToolTip', $Name);
            }
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
     * @param string $Name
     *
     * @return ILinkInterface
     */
    public function setName($Name)
    {

        $this->Name = $Name;
        return $this;
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
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        if (Access::useService()->hasAuthorization($this->Path)) {
            $this->Template->setVariable('ElementName', $this->Name);
            return $this->Template->getContent();
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {

        return $this->Path;
    }

    /**
     * @return string
     */
    public function getLink()
    {

        return $this->Link;
    }

    /**
     * @return string
     */
    public function getType()
    {

        return $this->Type;
    }

    /**
     * @param string $Type
     *
     * @return ILinkInterface
     */
    public function setType($Type = self::TYPE_DEFAULT)
    {

        $this->Type = $Type;
        return $this;
    }
}
