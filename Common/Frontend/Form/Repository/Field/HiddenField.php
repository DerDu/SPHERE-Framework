<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class HiddenField
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 * @deprecated
 */
class HiddenField extends AbstractField implements IFieldInterface
{

    /**
     * @param string         $Name
     * @param null|string    $Placeholder
     * @param null|string    $Label
     * @param IIconInterface $Icon
     * @deprecated
     */
    public function __construct(
        $Name,
        $Placeholder = '',
        $Label = '',
        IIconInterface $Icon = null
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/HiddenField.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
    }

}
