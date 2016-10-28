<?php
namespace SPHERE\System\Database\Binding;

use SPHERE\System\Database\Filter\Logic\AbstractLogic;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;
use SPHERE\System\Database\Fitting\ColumnHydrator;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\Manager;

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
     * @return \SPHERE\System\Database\Fitting\Element[]
     * @throws \Exception
     */
    protected function getEntityAllByLogic(Element $Entity, AbstractLogic $Logic)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Builder = $Manager->getQueryBuilder();

        $Builder->select('E')->from($Entity->getEntityFullName(), 'E');
        $Builder->andWhere($Logic->getExpression());
        $Builder->distinct(true);
        $Query = $Builder->getQuery();
        $Query->useQueryCache(true);

        if( $Entity instanceof AbstractView ) {
            $Result = $Query->getResult();
            $Validation = $Query->getArrayResult();
            if (count($Result) != count($Validation)) {
                throw new \Exception( 'View '.$Entity->getViewClassName().' Element-ID Missmatch.'
                    ."\n".'Multiple View-Elements with same Id-Value, please restructure Setup'
                    ."\n".'Possible Missmatch-Key: '.array_search( $Entity->getId(), $Entity->__toArray() )
                );
            }
            return $Result;
        }

        // TODO: Remove
//        $this->getDebugger()->screenDump( $Query->getSQL() );

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

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param int     $Id
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getForceEntityById($__METHOD__, Manager $EntityManager, $EntityName, $Id)
    {

        $Parameter['Id'] = $Id;

        $Entity = $EntityManager->getEntity($EntityName)->findOneBy($Parameter);
        if (null === $Entity) {
            $Entity = false;
        }
        $this->debugFactory($__METHOD__, $Entity, $Parameter);
        return $Entity;
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param array   $Parameter  Initiator Parameter-Array
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getForceEntityBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        $Entity = $EntityManager->getEntity($EntityName)->findOneBy($Parameter);
        if (null === $Entity) {
            $Entity = false;
        }
        $this->debugFactory($__METHOD__, $Entity, $Parameter);
        return $Entity;
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param array   $Parameter  Initiator Parameter-Array
     *
     * @return false|Element[]
     * @throws \Exception
     */
    final protected function getForceEntityListBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        $EntityList = $EntityManager->getEntity($EntityName)->findBy($Parameter);
        $this->debugFactory($__METHOD__, $EntityList, $Parameter);
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     *
     * @return false|Element[]
     * @throws \Exception
     */
    final protected function getForceEntityList($__METHOD__, Manager $EntityManager, $EntityName)
    {

        $EntityList = $EntityManager->getEntity($EntityName)->findAll();
        $this->debugFactory($__METHOD__, $EntityList, 'All');
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param array   $Parameter  Initiator Parameter-Array
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getForceEntityCountBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        $Entity = $EntityManager->getEntity($EntityName)->countBy($Parameter);
        if (null === $Entity) {
            $Entity = false;
        }
        $this->debugFactory($__METHOD__, $Entity, $Parameter);
        return $Entity;
    }
}
