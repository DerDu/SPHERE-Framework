<?php
namespace SPHERE\System\Extension\Repository;

use Doctrine\ORM\Query\Expr\Literal;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Tools\Pagination\Paginator;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\Repository;
use SPHERE\System\Extension\Extension;

/**
 * Class DataTables
 *
 * @package SPHERE\System\Extension\Repository
 */
class DataTables extends Extension
{

    const QUERY_BUILDER_ALIAS = 'q';

    /** @var \Doctrine\ORM\QueryBuilder $QueryBuilder */
    private $QueryBuilder = null;
    /** @var int $TotalCount */
    private $TotalCount = 0;
    /** @var int $SearchCount */
    private $SearchCount = 0;
    /** @var array $ColumnNames */
    private $ColumnNames = array();
    /** @var array $Request */
    private $Request = array();
    /** @var null $CallbackFunction */
    private $CallbackFunction = null;
    /** @var null|mixed $CallbackParameter */
    private $CallbackParameter = null;

    /**
     * @param Repository $EntityRepository
     * @param array      $Filter array( 'ColumnName' => 'Value', ... )
     */
    public function __construct(Repository $EntityRepository, $Filter = array())
    {

        $this->QueryBuilder = $EntityRepository->createQueryBuilder(self::QUERY_BUILDER_ALIAS);
        $this->ColumnNames = $this->QueryBuilder->getEntityManager()->getClassMetadata($EntityRepository->getClassName())->getFieldNames();
        $this->Request = $this->getRequest()->getParameterArray();

        $this->setFilter($Filter);
        $this->setSearch();
        $this->setOrderBy();
    }

    /**
     * @param array $Filter
     *
     * @return void
     */
    private function setFilter($Filter)
    {

        if (!empty( $Filter )) {
            $Restrict = $this->QueryBuilder->expr()->andX();
            foreach ((array)$Filter as $Column => $Value) {
                $Restrict->add(
                    $this->QueryBuilder->expr()->eq(
                        self::QUERY_BUILDER_ALIAS.'.'.$Column,
                        $this->QueryBuilder->expr()->literal($Value)
                    )
                );
            }
            $this->QueryBuilder->andWhere($Restrict);
        }
        $this->TotalCount = (new Paginator($this->QueryBuilder->getQuery()))->count();
    }

    /**
     * @return void
     */
    private function setSearch()
    {

        $List = $this->getTableSearch();
        if (!empty( $List )) {
            $Search = $this->QueryBuilder->expr()->andX();
            foreach ((array)$List as $Literal) {
                $Values = $this->QueryBuilder->expr()->orX();
                foreach ((array)$this->ColumnNames as $Column) {
                    $Values->add(
                        $this->QueryBuilder->expr()->like(self::QUERY_BUILDER_ALIAS.'.'.$Column, $Literal)
                    );
                }
                $Search->add($Values);
            }
            $this->QueryBuilder->andWhere($Search);
        }
        $this->SearchCount = (new Paginator($this->QueryBuilder->getQuery()))->count();
    }

    /**
     * @return Literal[]
     */
    private function getTableSearch()
    {

        if (isset( $this->Request['search'] )) {
            $Search = explode(' ', $this->Request['search']['value']);
        } else {
            $Search = array();
        }
        array_walk($Search, function (&$P) {

            if (empty( $P )) {
                $P = false;
            } else {
                $P = $this->QueryBuilder->expr()->literal('%'.$P.'%');
            }
        });
        return array_filter($Search);
    }

    /**
     * @return void
     */
    private function setOrderBy()
    {

        $List = $this->getTableOrderBy();
        foreach ((array)$List as $Literal) {
            $this->QueryBuilder->addOrderBy($Literal);
        }
    }

    /**
     * @return OrderBy[]|null
     */
    private function getTableOrderBy()
    {

        if (isset( $this->Request['order'] )) {
            $OrderBy = array();
            foreach ((array)$this->Request['order'] as $Column) {
                if ($this->Request['columns'][$Column['column']]['orderable'] != 'false') {
                    array_push(
                        $OrderBy,
                        new OrderBy(
                            self::QUERY_BUILDER_ALIAS.'.'.$this->Request['columns'][$Column['column']]['data'],
                            strtoupper($Column['dir'])
                        )
                    );
                }
            }
            return $OrderBy;
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getResult()
    {

        $Result = $this->getTableQuery()->getResult();
        array_walk($Result, function (Element &$Entity) {

            if (isset( $this->CallbackFunction ) && is_callable($this->CallbackFunction)) {
                if (null === $this->CallbackParameter) {
                    $Entity = call_user_func_array(
                        $this->CallbackFunction,
                        array($Entity)
                    );
                } else {
                    $Entity = call_user_func_array(
                        $this->CallbackFunction,
                        array($Entity, $this->CallbackParameter)
                    );
                }
            }
            if (is_object($Entity)) {
                $Entity = $Entity->__toArray();
            }
        });

        return json_encode(array(
            'draw'            => (int)$this->getTableDraw(),
            'recordsTotal'    => (int)$this->TotalCount,
            'recordsFiltered' => (int)$this->SearchCount,
            'data'            => $Result
        ));
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    private function getTableQuery()
    {

        $this->QueryBuilder->setMaxResults($this->getTableLength());
        $this->QueryBuilder->setFirstResult($this->getTableOffset());
        return $this->QueryBuilder->getQuery();
    }

    /**
     * @return int|null
     */
    private function getTableLength()
    {

        if (isset( $this->Request['length'] )) {
            return (int)$this->Request['length'];
        }
        return null;
    }

    /**
     * @return int|null
     */
    private function getTableOffset()
    {

        if (isset( $this->Request['start'] )) {
            return (int)$this->Request['start'];
        }
        return null;
    }

    /**
     * @return int|null
     */
    private function getTableDraw()
    {

        if (isset( $this->Request['draw'] )) {
            return (int)$this->Request['draw'];
        }
        return null;
    }

    /**
     * @param            $Function
     * @param null|mixed $Parameter
     *
     * @return DataTables
     */
    public function setCallbackFunction($Function, $Parameter = null)
    {

        if (is_callable($Function)) {
            $this->CallbackFunction = $Function;
            $this->CallbackParameter = $Parameter;
        }
        return $this;
    }
}
