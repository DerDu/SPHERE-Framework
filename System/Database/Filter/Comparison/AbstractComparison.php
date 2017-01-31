<?php
namespace SPHERE\System\Database\Filter\Comparison;

/**
 * Class AbstractComparison
 *
 * @package SPHERE\System\Database\Filter\Comparison
 */
abstract class AbstractComparison
{

    /** @var string $Value */
    private $Value = '';

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }
}