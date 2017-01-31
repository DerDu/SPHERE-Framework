<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\View;
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

    private static $LinkCounter = 0;

    /**
     * @return string
     */
    public function getHash()
    {
        return 'Link-Hash-'.sha1($this->getName().$this->getPath()).'-'.self::$LinkCounter;
    }

    /**
     * AbstractLink constructor.
     *
     * @param string              $Name
     * @param string              $Path
     * @param IIconInterface|null $Icon
     * @param array               $Data
     * @param bool|string         $ToolTip
     * @param null|string         $Anchor
     */
    public function __construct($Name, $Path, IIconInterface $Icon = null, $Data = array(), $ToolTip = false, $Anchor = null)
    {
        // Generate Hash
        self::$LinkCounter++;

        if( !empty( $Anchor ) ) {
            $this->setName($Name.' '.new Link() );
        } else {
            $this->setName($Name);
        }

        if (false !== strpos($Path, '\\')) {
            $this->Path = new Route($Path);
        } else {
            $this->Path = $Path;
        }

        $this->Template = $this->getTemplate(__DIR__.'/Link.twig');

        if (null !== $Icon) {
            if( $Icon instanceof Edit) {
                $this->Type = $this->Type.' bg-info';
            }
            if( $Icon instanceof Remove) {
                $this->Type = $this->Type.' bg-danger';
            }
            if( $Icon instanceof Setup) {
                $this->Type = $this->Type.' bg-warning';
            }
            if( $Icon instanceof View) {
                $this->Type = $this->Type.' bg-success';
            }
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        $this->Template->setVariable('ElementType', $this->Type);

        if (!empty( $Data )) {
            $Signature = (new Authenticator(new Get()))->getAuthenticator();
            $Data = '?'.http_build_query($Signature->createSignature($Data, $this->Path));
        } else {
            $Data = '';
        }
        if( !empty( $Anchor ) ) {
            $Data .= '#'.(string)$Anchor;
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

        $this->Template->setVariable('ElementHash', $this->getHash());
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

    /**
     * @param Pipeline|Pipeline[] $Pipeline
     * @return $this
     */
    public function ajaxPipelineOnClick( $Pipeline )
    {
        $Script = '';
        if( is_array( $Pipeline ) ) {
            foreach( $Pipeline as $Element ) {
                $Script .= $Element->parseScript();
            }
        } else {
            $Script = $Pipeline->parseScript();
        }

        $this->Template->setVariable('AjaxEventClick', $Script);
        return $this;
    }
}
