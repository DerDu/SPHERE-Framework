<?php
namespace SPHERE\System\Database\Filter\Comparison;

/**
 * Class LikeComparison
 *
 * @package SPHERE\System\Database\Filter\Comparison
 */
class LikeComparison extends AbstractComparison
{
    /**
     * LikeComparison constructor.
     *
     * @param $Value
     */
    public function __construct($Value)
    {
        $this->setValue($Value);
    }
}