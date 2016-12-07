<?php
namespace SPHERE\System\Database\Fitting;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\HandlerInterface;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Debugger\Logger\CacheLogger;
use SPHERE\System\Debugger\Logger\QueryLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Cacheable
 *
 * @package SPHERE\System\Database\Fitting
 */
abstract class Cacheable extends Extension
{
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /** @var null|HandlerInterface $CacheSystem */
    private static $CacheSystem = null;
    /** @var bool $Enabled */
    private $Enabled = true;
    /** @var null|bool $Debug */
    private $Debug = null;

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param int     $Id
     *
     * @return false|Element
     * @throws \Exception
     */
    final protected function getCachedEntityById($__METHOD__, Manager $EntityManager, $EntityName, $Id)
    {

        // Only if NOT REMOVED
        $Parameter['EntityRemove'] = null;
        $Parameter['Id'] = $Id;

        $Key = $this->getKeyHash($EntityName, $Parameter);

        $Memory = $this->getMemoryCacheHandler();
        if (!$this->Enabled || null === ( $Entity = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem();
            $Entity = null;
            if (!$this->Enabled || null === ( $Entity = $Cache->getValue($Key, $__METHOD__) )) {
                $Entity = $EntityManager->getEntity($EntityName)->findOneBy($Parameter);
                if (null === $Entity) {
                    $Entity = false;
                }
                $Cache->setValue($Key, $Entity, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $Entity, $Parameter);
            } else {
                $this->debugCache($__METHOD__, $Entity, $Parameter);
            }
            $Memory->setValue($Key, $Entity, 0, $__METHOD__);
            return ( null === $Entity || false === $Entity ? false : $Entity );
        }
        return ( null === $Entity || false === $Entity ? false : $Entity );
    }

    /**
     * @param string       $EntityName
     * @param string|array $Parameter
     *
     * @return string
     */
    private function getKeyHash($EntityName, $Parameter)
    {

        if (is_object($Parameter)) {
            $Parameter = json_decode(json_encode($Parameter), true);
        }
        $Keys = array_keys( (array)$Parameter );
        $Values = array_values( (array)$Parameter );

        return md5($EntityName . ':' . implode('#', $Keys). implode('#', $Values));
    }

    /**
     * @return HandlerInterface
     */
    private function getCacheSystem()
    {

        if (null === self::$CacheSystem || !self::$CacheSystem instanceof MemcachedHandler) {
            self::$CacheSystem = $this->getCache(new MemcachedHandler());
        }
        return self::$CacheSystem;
    }

    /**
     * @param string                $__METHOD__
     * @param array|object|bool|int $EntityList
     * @param array|string|int      $Parameter
     */
    protected function debugFactory($__METHOD__, $EntityList, $Parameter)
    {

        if ($this->useDebugger()) {
            $this->getLogger(new QueryLogger())->addLog(
                $__METHOD__.' ['.implode('], [', (array)$Parameter).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
            );
        }
    }

    /**
     * @return bool|null
     */
    protected function useDebugger()
    {
        if ($this->Debug === null) {
            $this->Debug = (
                $this->getLogger(new CacheLogger())->isEnabled()
                || $this->getLogger(new QueryLogger())->isEnabled()
            );
        }
        return $this->Debug;
    }

    /**
     * @param string                $__METHOD__
     * @param array|object|bool|int $EntityList
     * @param array |string|int     $Parameter
     */
    private function debugCache($__METHOD__, $EntityList, $Parameter)
    {

        if ($this->useDebugger()) {
            $this->getLogger(new CacheLogger())->addLog(
                $__METHOD__.' ['.implode('], [', (array)$Parameter).'] Result: '.(
                $EntityList ? 'Ok' : ( null === $EntityList ? 'None' : 'Error' ) )
            );
        }
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
    final protected function getCachedEntityBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        // Only if NOT REMOVED
        $Parameter['EntityRemove'] = null;
        $Key = $this->getKeyHash($EntityName, $Parameter);

        $Memory = $this->getMemoryCacheHandler();
        if (!$this->Enabled || null === ( $Entity = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem();
            $Entity = null;
            if (!$this->Enabled || null === ( $Entity = $Cache->getValue($Key, $__METHOD__) )) {
                $Entity = $EntityManager->getEntity($EntityName)->findOneBy($Parameter);
                if (null === $Entity) {
                    $Entity = false;
                }
                $Cache->setValue($Key, $Entity, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $Entity, $Parameter);
            } else {
                $this->debugCache($__METHOD__, $Entity, $Parameter);
            }
            $Memory->setValue($Key, $Entity, 0, $__METHOD__);
            return ( null === $Entity || false === $Entity ? false : $Entity );
        }
        return ( null === $Entity || false === $Entity ? false : $Entity );
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param array   $Parameter  Initiator Parameter-Array
     * @param array   $OrderBy array( 'Column' => ORDER_{ASC|DESC}, ... )
     *
     * @return false|Element[]
     */
    final protected function getCachedEntityListBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter, $OrderBy = array( 'EntityCreate' => self::ORDER_DESC ))
    {

        // Only if NOT REMOVED
        $Parameter['EntityRemove'] = null;
        $Key = $this->getKeyHash($EntityName, $Parameter).$this->getKeyHash($EntityName, $OrderBy);

        $Memory = $this->getMemoryCacheHandler();
        if (!$this->Enabled || null === ( $EntityList = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem();
            $EntityList = null;
            if (!$this->Enabled || null === ( $EntityList = $Cache->getValue($Key, $__METHOD__) )) {
                $EntityList = $EntityManager->getEntity($EntityName)->findBy($Parameter, $OrderBy);
                $Cache->setValue($Key, $EntityList, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $EntityList, $Parameter);
            } else {
                $this->debugCache($__METHOD__, $EntityList, $Parameter);
            }
            $Memory->setValue($Key, $EntityList, 0, $__METHOD__);
            return ( empty( $EntityList ) ? false : $EntityList );
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string  $__METHOD__ Initiator
     * @param Manager $EntityManager
     * @param string  $EntityName
     * @param array   $OrderBy array( 'Column' => ORDER_{ASC|DESC}, ... )
     *
     * @return false|Element[]
     */
    final protected function getCachedEntityList($__METHOD__, Manager $EntityManager, $EntityName, $OrderBy = array( 'EntityCreate' => self::ORDER_DESC ))
    {

        $Key = $this->getKeyHash($EntityName, 'All').$this->getKeyHash($EntityName, $OrderBy);

        $Memory = $this->getMemoryCacheHandler();
        if (!$this->Enabled || null === ( $EntityList = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem();
            $EntityList = null;
            if (!$this->Enabled || null === ( $EntityList = $Cache->getValue($Key, $__METHOD__) )) {
                // Only if NOT REMOVED
                $Parameter['EntityRemove'] = null;
                $EntityList = $EntityManager->getEntity($EntityName)->findBy($Parameter, $OrderBy);
                $Cache->setValue($Key, $EntityList, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $EntityList, 'All');
            } else {
                $this->debugCache($__METHOD__, $EntityList, 'All');
            }
            $Memory->setValue($Key, $EntityList, 0, $__METHOD__);
            return ( empty( $EntityList ) ? false : $EntityList );
        }
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
    final protected function getCachedCountBy($__METHOD__, Manager $EntityManager, $EntityName, $Parameter)
    {

        // Only if NOT REMOVED
        $Parameter['EntityRemove'] = null;
        $Key = $this->getKeyHash($EntityName, $Parameter);

        $Memory = $this->getMemoryCacheHandler();
        if (!$this->Enabled || null === ( $Entity = $Memory->getValue($Key, $__METHOD__) )) {

            $Cache = self::getCacheSystem();
            $Entity = null;
            if (!$this->Enabled || null === ( $Entity = $Cache->getValue($Key, $__METHOD__) )) {
                $Entity = $EntityManager->getEntity($EntityName)->countBy($Parameter);
                if (null === $Entity) {
                    $Entity = false;
                }
                $Cache->setValue($Key, $Entity, 0, $__METHOD__);
                $this->debugFactory($__METHOD__, $Entity, $Parameter);
            } else {
                $this->debugCache($__METHOD__, $Entity, $Parameter);
            }
            $Memory->setValue($Key, $Entity, 0, $__METHOD__);
            return ( null === $Entity || false === $Entity ? false : $Entity );
        }
        return ( null === $Entity || false === $Entity ? false : $Entity );
    }

    /** @var null|MemoryHandler $MemoryCacheHandler */
    private static $MemoryCacheHandler = null;

    /**
     * @return MemoryHandler
     */
    private function getMemoryCacheHandler()
    {
        if( self::$MemoryCacheHandler === null ) {
            self::$MemoryCacheHandler = (new CacheFactory())->createHandler(new MemoryHandler());
        }
        return self::$MemoryCacheHandler;
    }
}
