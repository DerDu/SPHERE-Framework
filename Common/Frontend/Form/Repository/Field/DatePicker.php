<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class DatePicker
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class DatePicker extends AbstractField implements IFieldInterface
{

    /** @var string $Label */
    private $Label = '';

    /**
     * @param string         $Name
     * @param null|string    $Placeholder
     * @param null|string    $Label
     * @param IIconInterface $Icon
     * @param null|array     $Option
     */
    public function __construct(
        $Name,
        $Placeholder = '',
        $Label = '',
        IIconInterface $Icon = null,
        $Option = null
    ) {

        $this->Name = $Name;
        $this->Label = $Label;
        $this->Template = $this->getTemplate(__DIR__.'/DatePicker.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        $this->setPostValue($this->Template, $Name, 'ElementValue');
        if (is_array($Option)) {
            $this->Template->setVariable('ElementOption', json_encode($Option));
        }
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->Label;
    }
}
