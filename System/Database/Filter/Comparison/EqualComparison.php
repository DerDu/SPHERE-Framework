<?php
namespace SPHERE\System\Database\Filter\Comparison;

/**
 * Class EqualComparison
 *
 * @package SPHERE\System\Database\Filter\Comparison
 */
class EqualComparison extends AbstractComparison
{
    /**
     * EqualComparison constructor.
     *
     * @param $Value
     */
    public function __construct($Value)
    {
        $this->setValue($Value);
    }
}