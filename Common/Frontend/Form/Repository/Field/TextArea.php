<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class TextArea
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class TextArea extends AbstractField implements IFieldInterface
{

    /**
     * @param string         $Name
     * @param null|string    $Placeholder
     * @param null|string    $Label
     * @param IIconInterface $Icon
     */
    public function __construct(
        $Name,
        $Placeholder = '',
        $Label = '',
        IIconInterface $Icon = null
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/TextArea.twig');
        $this->Template->setVariable('ElementHash', md5(uniqid(microtime(),true)));
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        $this->setPostValue($this->Template, $Name, 'ElementValue');
    }

    /**
     * @param int $Value
     * @param bool $allowLineFeed Default: Disabled
     *
     * @return TextArea
     */
    public function setMaxLengthValue($Value, $allowLineFeed = false)
    {

        $this->Template->setVariable('MaxLength', (int)$Value);
        if( !$allowLineFeed ) {
            $this->Template->setVariable('DisableLineFeed', 'DisableLineFeed');
        }
        return $this;
    }
}
