<?php
namespace SPHERE\Common\Frontend\Link\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class LinkGroup
 *
 * @package SPHERE\Common\Frontend\Link\Structure
 */
class LinkGroup extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var ILinkInterface[] $LinkList */
    protected $LinkList = array();

    /**
     * LinkGroup constructor.
     */
    public function __construct()
    {

        $this->Template = Template::getTwigTemplateString('<div class="btn-group">{{ ButtonList }}</div>&nbsp;');
    }

    /**
     * @param ILinkInterface $Link
     *
     * @return LinkGroup
     */
    public function addLink(ILinkInterface $Link)
    {

        array_push($this->LinkList, $Link);
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable('ButtonList', implode($this->LinkList));
        return $this->Template->getContent();
    }

}
