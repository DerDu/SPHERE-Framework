<?php
namespace SPHERE\System\Database\Binding;

use SPHERE\System\Database\Filter\Logic\AbstractLogic;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;
use SPHERE\System\Database\Fitting\ColumnHydrator;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class AbstractData
 *
 * @package SPHERE\System\Database\Binding
 */
abstract class AbstractData extends Cacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    final public function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    /**
     * @return void
     */
    abstract public function setupDatabaseContent();

    /**
     * Internal
     *
     * @param Element $Entity
     * @param AbstractLogic $Logic
     *
     * @return Element[]
     */
    protected function getEntityAllByLogic(Element $Entity, AbstractLogic $Logic)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Builder = $Manager->getQueryBuilder();

        $Builder->select('E')->from( $Entity->getEntityFullName(), 'E' );
        $Builder->andWhere( $Logic->getExpression() );
        $Builder->distinct(true);
        $Query = $Builder->getQuery();
        $Query->useQueryCache(true);

        // TODO: Remove
        //$this->getDebugger()->screenDump( $Query->getSQL() );

        return $Query->getResult();
    }

    /**
     * @return Binding
     */
    final public function getConnection()
    {

        return $this->Connection;
    }

    /**
     * Internal
     *
     * @param Element       $Entity
     * @param AbstractLogic $Logic
     * @param string        $Column
     *
     * @return array
     */
    protected function getColumnAllByLogic(Element $Entity, AbstractLogic $Logic, $Column = 'Id')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Builder = $Manager->getQueryBuilder();

        $Builder->select('E.'.$Column)->from($Entity->getEntityFullName(), 'E');
        $Builder->andWhere($Logic->getExpression());
        $Query = $Builder->getQuery();
        $Query->useQueryCache(true);

        // TODO: Remove
        // $this->getDebugger()->screenDump($Query->getSQL());

        return $Query->getResult(ColumnHydrator::HYDRATION_MODE);
    }
}
