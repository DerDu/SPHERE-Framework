<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;

/**
 * Class CheckBox
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class CheckBox extends AbstractField implements IFieldInterface
{

    /** @var mixed $Value */
    private $Value = '';
    /**
     * @param string $Name
     * @param string $Label
     * @param mixed  $Value
     * @param array  $ToggleTarget
     */
    public function __construct(
        $Name,
        $Label,
        $Value,
        $ToggleTarget = array()
    ) {

        $this->Name = $Name;
        $this->Value = $Value;
        $this->Template = $this->getTemplate(__DIR__.'/CheckBox.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementValue', $Value);
        $this->Template->setVariable('ElementToggleTarget', $ToggleTarget);
        $this->Template->setVariable('ElementHash', md5($Name . $Label . $Value . (new \DateTime())->getTimestamp()));
    }

    /**
     * @return CheckBox
     */
    public function setChecked()
    {

        $this->isForceDefaultValue = true;
        return $this;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function getContent()
    {

        if (
            $this->isChecked($this->getName(), $this->Value)
            || $this->isForceDefaultValue
        ) {
            $this->Template->setVariable('ElementChecked', 'checked="checked"');
        }

        return $this->Template->getContent();
    }
}
