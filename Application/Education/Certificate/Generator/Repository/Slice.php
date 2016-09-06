<?php
namespace SPHERE\Application\Education\Certificate\Generator\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;

class Slice extends Style
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /** @var array $Elements */
    private $Elements = array();

    /**
     * Document constructor.
     */
    public function __construct()
    {

        $this->Template = Template::getTwigTemplateString('<div class="Slice {{ Design }}" style="{{ Style }}">{{ Elements }}</div>');
    }

    /**
     * @param Element $Element
     *
     * @return Slice
     */
    public function addElement(Element $Element)
    {

        $this->Elements[] = $Element;
        return $this;
    }

    /**
     * @param Section $Section
     *
     * @return Slice
     */
    public function addSection(Section $Section)
    {

        $this->Elements[] = $Section;
        return $this;
    }

    /**
     * @param Section[] $SectionList
     *
     * @return $this
     */
    public function addSectionList($SectionList){
        if (is_array($SectionList)){
            /** @var Section $Section */
            foreach ($SectionList as $Section){
                $this->Elements[] = $Section;
            }
        }

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

        $this->Template->setVariable('Design', implode(' ', $this->Design));
        $this->Template->setVariable('Style', implode(' ', $this->Style));
        $this->Template->setVariable('Elements', implode("\n", $this->Elements));
        return $this->Template->getContent();
    }
}
