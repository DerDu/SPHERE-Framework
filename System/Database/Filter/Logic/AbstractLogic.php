<?php
namespace SPHERE\System\Database\Filter\Logic;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AbstractLogic
 * @package SPHERE\System\Database\Filter\Logic
 */
abstract class AbstractLogic
{

    const COMPARISON_EXACT = 0;
    const COMPARISON_LIKE = 1;
    const COMPARISON_IN = 2;

    /** @var AbstractLogic[] $Logic */
    private $Logic = array();
    /** @var array $Criteria */
    private $Criteria = array();
    /** @var QueryBuilder $Builder */
    private $Builder;

    /**
     * AbstractLogic constructor.
     * @param QueryBuilder $Builder
     */
    final public function __construct(QueryBuilder $Builder)
    {
        $this->Builder = $Builder;
    }

    /**
     * @param AbstractLogic $Logic
     * @return $this
     */
    final public function addLogic(AbstractLogic $Logic)
    {
        array_push($this->Logic, $Logic->getExpression());
        return $this;
    }

    /**
     * @return \Doctrine\ORM\Query\Expr\Base
     */
    abstract public function getExpression();

    /**
     * @param array $Criteria
     * @param int $Comparison
     * @return $this
     */
    final public function addCriteriaList($Criteria, $Comparison = self::COMPARISON_EXACT)
    {
        foreach ($Criteria as $Property => $Value) {
            $this->addCriteria($Property, $Value, $Comparison);
        }
        return $this;
    }

    /**
     * @param string $Property
     * @param mixed $Value
     * @param int $Comparison
     * @return $this
     */
    final public function addCriteria($Property, $Value, $Comparison = self::COMPARISON_EXACT)
    {
        if (is_array($Value) && $Comparison != self::COMPARISON_IN) {
            foreach ($Value as $Part) {
                $Expression = $this->getExpressionComparison($Property, $Part, $Comparison);
                if (!in_array($Expression, $this->Criteria)) {
                    array_push($this->Criteria, $Expression);
                }
            }
        } else {
            $Expression = $this->getExpressionComparison($Property, $Value, $Comparison);
            if (!in_array($Expression, $this->Criteria)) {
                array_push($this->Criteria, $Expression);
            }
        }
        return $this;
    }

    /**
     * @param string $Property
     * @param mixed $Value
     * @param int $Comparison
     * @return Comparison|string
     */
    final private function getExpressionComparison($Property, $Value, $Comparison = self::COMPARISON_EXACT)
    {
        if ($Value === null) {
            return $this->getExpressionBuilder()->isNull('E.' . $Property);
        }

        switch ($Comparison) {
            case self::COMPARISON_EXACT:
                return $this->getExpressionBuilder()->eq('E.' . $Property,
                    $this->getExpressionBuilder()->literal($Value));
            case self::COMPARISON_LIKE:
                return $this->getExpressionBuilder()->like('E.' . $Property,
                    $this->getExpressionBuilder()->literal('%' . $Value . '%'));
            case self::COMPARISON_IN:
                return $this->getExpressionBuilder()->in('E.' . $Property, array_unique( $Value ));
            default:
                return $this->getExpressionBuilder()->eq('E.' . $Property,
                    $this->getExpressionBuilder()->literal($Value));
        }
    }

    /**
     * @return Expr
     */
    final protected function getExpressionBuilder()
    {
        return $this->Builder->expr();
    }

    /**
     * @return \Doctrine\ORM\Query\Expr\Base[]
     */
    final protected function getLogic()
    {
        return $this->Criteria + $this->Logic;
    }
}
