<?php
namespace SPHERE\Common\Style\Font;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;
use MOC\V\Core\HttpKernel\Component\Bridge\Repository\UniversalRequest;
use SPHERE\Common\Frontend\ITemplateInterface;

/**
 * Class Font
 *
 * @package SPHERE\Common\Style\Font
 */
class Font implements ITemplateInterface
{
    /** @var IBridgeInterface|null $Template */
    private $Template = null;

    /**
     * Font constructor.
     */
    public function __construct()
    {
        $this->Template = Template::getTwigTemplateString(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Font.css'));
        switch ((new UniversalRequest())->getPort()) {
            case 443:
                $Protocol = 'https://';
                break;
            default:
                $Protocol = 'http://';
        }
        $this->Template->setVariable('UriPath', $Protocol . trim((new UniversalRequest())->getHost(), '/'));
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
        return $this->Template->getContent();
    }
}