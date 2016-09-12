<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:17
 */

namespace SPHERE\Application\Document\Generator\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;

/**
 * Class Element
 *
 * @package SPHERE\Application\Document\Generator\Repository
 */
class Element extends Style
{

    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var string $Content */
    private $Content = '';

    /**
     * Element constructor.
     */
    public function __construct()
    {

        $this->Template = Template::getTwigTemplateString('<div class="Element {{ Design }}" style="{{ Style }}">{{ Content }}</div>');
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

        $this->Template->setVariable('Design', implode(' ', $this->Design));
        $this->Template->setVariable('Style', implode(' ', $this->Style));
        $this->Template->setVariable('Content', $this->Content);
        return $this->Template->getContent();
    }

    /**
     * @param $Content
     *
     * @return Element
     */
    public function setContent($Content)
    {

        $this->Content = $Content;
        return $this;
    }
}
