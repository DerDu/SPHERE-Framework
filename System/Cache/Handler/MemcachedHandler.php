<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\CacheLogger;
use SPHERE\System\Debugger\Logger\ErrorLogger;

/**
 * Class MemcachedHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class MemcachedHandler extends AbstractHandler implements HandlerInterface
{

    /** @var null|\Memcached $Connection */
    private $Connection = null;

    private $Host = '';
    private $Port = '';

    /**
     * @param                 $Name
     * @param ReaderInterface $Config
     *
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        if (null === $this->Connection
            && null !== $Config
            && class_exists('\Memcached', false)
        ) {
            $Value = $Config->getValue($Name);
            if ($Value) {
                $this->Host = $Value->getContainer('Host');
                $this->Port = $Value->getContainer('Port');
                if ($this->Host && $this->Port) {
                    $this->Connection = new \Memcached();
                    if ($this->Connection->addServer((string)$this->Host, (string)$this->Port)) {
                        $this->Connection->setOption(\Memcached::OPT_TCP_NODELAY, true);
                        $this->Connection->setOption(\Memcached::OPT_NO_BLOCK, true);
                        $this->Connection->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 1);
                        $this->setValue('CheckRunningStatus', true);
                        if (true === $this->getValue('CheckRunningStatus')) {
                            $this->Connection->delete('CheckRunningStatus');
                            return $this;
                        } else {
                            (new DebuggerFactory())->createLogger(new ErrorLogger())
                                ->addLog(__METHOD__.' Error: Server not available -> Fallback');
                        }
                    }
                } else {
                    (new DebuggerFactory())->createLogger(new ErrorLogger())
                        ->addLog(__METHOD__.' Error: Configuration not available -> Fallback');
                }
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: Configuration not available -> Fallback');
            }
        } else {
            if (null === $Config) {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: Configuration not available -> Fallback');
            }
        }
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__.' Error: Memcached not available -> Fallback');
        return (new CacheFactory())->createHandler(new DefaultHandler());
    }

    /**
     * @param string $Key
     * @param mixed  $Value
     * @param int    $Timeout
     * @param string $Region
     *
     * @return MemcachedHandler
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {

        if ($this->isValid()) {
            $this->Connection->set(preg_replace('!\s+!is', '', $this->getSlotRegion($Region).'#'.$Key), $Value,
                ( !$Timeout ? null : time() + $Timeout ));
            // 0 = MEMCACHED_SUCCESS
            if (0 == ( $Code = $this->Connection->getResultCode() )) {
                return $this;
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: '
                        .$Region.'->'.$Key.' - '
                        .$Code.' - '
                        .$this->Connection->getResultMessage()
                    );
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function isValid()
    {

        return ( null === $this->Connection ? false : true );
    }

    /**
     * @param $Region
     *
     * @return string
     */
    public function getSlotRegion($Region)
    {

        return $this->getSlot().':'.$Region;
    }

    /**
     * @return string
     */
    public function getSlot()
    {

        if (isset( $_SESSION['Memcached-Slot'] )) {
            return $_SESSION['Memcached-Slot'];
        }
        return 'PUBLIC';
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return mixed
     */
    public function getValue($Key, $Region = 'Default')
    {

        if ($this->isValid()) {
            $Value = $this->Connection->get(preg_replace('!\s+!is', '', $this->getSlotRegion($Region).'#'.$Key));
            // 0 = MEMCACHED_SUCCESS
            if (0 == ( $Code = $this->Connection->getResultCode() )) {
                return $Value;
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: '
                        .$Region.'->'.$Key.' - '
                        .$Code.' - '
                        .$this->Connection->getResultMessage()
                    );
            }
        }
        return null;
    }

    /**
     * @return \Memcached|null
     */
    public function getCache()
    {

        return $this->Connection;
    }

    /**
     * @return MemcachedHandler
     */
    public function clearCache()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Clear MemCached');
        if ($this->isValid()) {
            $this->Connection->flush();
            // 0 = MEMCACHED_SUCCESS
            if (0 == ( $Code = $this->Connection->getResultCode() )) {
                return $this;
            } else {
                (new DebuggerFactory())->createLogger(new ErrorLogger())
                    ->addLog(__METHOD__.' Error: '
                        .$Code.' - '
                        .$this->Connection->getResultMessage()
                    );
            }
        }
        return $this;
    }

    public function clearSlot($Slot)
    {

        (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Requested Memcached-Slot-Clear: '.$Slot);
        $Pattern = '!^'.preg_quote($Slot, '!').':!is';
        if (!( $CacheList = $this->fetchKeys() )) {
            $CacheList = array();
        }
        $KeyList = preg_grep($Pattern, $CacheList);
        if (!empty( $KeyList )) {
            $this->removeKeys($KeyList);
            (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Cleared Memcached-Slot: '.implode(',',
                    $KeyList));
        }
    }

    /**
     * Internal
     *
     * Get all cache Keys
     *
     * @return array
     */
    public function fetchKeys()
    {

        $List = $this->Connection->getAllKeys();
        // 0 = MEMCACHED_SUCCESS
        if (0 == ( $Code = $this->Connection->getResultCode() )) {
            return $List;
        } else {
            (new DebuggerFactory())->createLogger(new ErrorLogger())
                ->addLog(__METHOD__.' Error: '
                    .$Code.' - '
                    .$this->Connection->getResultMessage()
                );
        }
        return array();
    }

    /**
     * Internal
     *
     * Remove cache by Key
     *
     * @param $List
     *
     * @return MemcachedHandler
     */
    public function removeKeys($List)
    {

        $this->Connection->deleteMulti($List);
        // 0 = MEMCACHED_SUCCESS
        if (0 == ( $Code = $this->Connection->getResultCode() )) {
            return $this;
        } else {
            (new DebuggerFactory())->createLogger(new ErrorLogger())
                ->addLog(__METHOD__.' Error: '
                    .$Code.' - '
                    .$this->Connection->getResultMessage()
                );
        }
        return $this;
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog('Status MemCached');
        if ($this->isValid()) {
            $Status = $this->Connection->getStats();
            $Status = $Status[$this->Host.':'.$this->Port];
            return new CacheStatus(
                $Status['get_hits'], $Status['get_misses'], $Status['limit_maxbytes'],
                $Status['bytes'], $Status['limit_maxbytes'] - $Status['bytes'], 0
            );
        } else {
            return new CacheStatus();
        }
    }

    /**
     * Internal
     *
     * Memcached exists
     *
     * @return bool
     */
    public function isEnabled()
    {

        return $this->isValid();
    }
}
