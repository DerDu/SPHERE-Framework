<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Config\Reader\ReaderInterface;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\CacheLogger;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Debugger\Logger\QueryLogger;

/**
 * Class Cache
 *
 * @package SPHERE\Common\Frontend
 */
class DataCacheHandler extends AbstractHandler
{

    /** @var string $Identifier */
    private $Identifier = 'Default';
    /** @var Element[] $Dependency */
    private $Dependency = array();
    /** @var null|MemcachedHandler $Handler */
    private $Handler = null;

    /**
     * Frontend-Cache constructor.
     *
     * @param string    $Identifier
     * @param Element[] $Dependencies
     */
    public function __construct($Identifier, $Dependencies = array())
    {

        $this->Identifier = $Identifier;
        $this->Handler = (new CacheFactory())->createHandler(new MemcachedHandler());
        if (!empty( $Dependencies )) {
            foreach ($Dependencies as $Element) {
                $this->addDependency($Element);
            }
        }
    }

    /**
     * Add Entity-Dependency
     *
     * @param Element $Entity
     *
     * @return $this
     */
    public function addDependency(Element $Entity)
    {

        array_push($this->Dependency, $Entity);
        return $this;
    }

    /**
     * Save Value
     *
     * @param mixed $Value
     * @param int   $Timeout
     *
     * @return $this
     */
    public function setData($Value, $Timeout = 3600)
    {

        if ($this->Handler->isEnabled()) {
            (new DebuggerFactory())->createLogger(new QueryLogger())->addLog('Save DataCache: '.md5($this->Identifier));
            $this->Handler->setValue($this->createKey(), $Value, $Timeout, 'DataCache');
        }
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function createKey()
    {

        $KeyList = $this->createKeyList();
        array_unshift($KeyList, md5($this->Identifier));
        return json_encode($KeyList);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function createKeyList()
    {

        if (empty( $this->Dependency )) {
            throw new \Exception(__METHOD__.' Missing Dependency-Entities');
        } else {
            $KeyList = array();
            array_walk($this->Dependency, function (Element $Entity) use (&$KeyList) {

                array_push($KeyList, (string)crc32(get_class($Entity)));
            });
            return $KeyList;
        }
    }

    /**
     * Load Value
     *
     * @return mixed|null
     */
    public function getData()
    {

        if ($this->Handler->isEnabled()) {
            (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Load DataCache: '.md5($this->Identifier));
            return $this->Handler->getValue($this->createKey(), 'DataCache');
        }
        return null;
    }

    /**
     * @param string $Key
     * @param mixed $Value
     * @param int $Timeout
     * @param string $Region
     *
     * @internal use setData instead
     * @return HandlerInterface
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {
        // MUST NOT USE
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__ . ' Error: SET - MUST NOT BE USED! Use setData() instead.');
        return $this;
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @internal use getData instead
     * @return null|mixed
     */
    public function getValue($Key, $Region = 'Default')
    {
        // MUST NOT USE
        (new DebuggerFactory())->createLogger(new ErrorLogger())
            ->addLog(__METHOD__.' Error: GET - MUST NOT BE USED! Use getData() instead.');
        return null;
    }

    /**
     * @param string $Name
     * @param ReaderInterface $Config
     *
     * @return HandlerInterface
     */
    public function setConfig($Name, ReaderInterface $Config = null)
    {

        return $this;
    }

    /**
     * @return HandlerInterface
     */
    public function clearCache()
    {
        $this->clearData();
    }

    /**
     * Clear Frontend-Cache
     */
    public function clearData()
    {

        if ($this->Handler->isEnabled()) {
            $Pattern = $this->createPattern();
            if (!( $CacheList = $this->Handler->fetchKeys() )) {
                $CacheList = array();
            }
            $KeyList = preg_grep($Pattern, $CacheList);
            if (!empty( $KeyList )) {
                (new DebuggerFactory())->createLogger(new CacheLogger())->addLog('Clear DataCache: '.implode(',',
                        $KeyList));
                $this->Handler->removeKeys($KeyList);
            }
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function createPattern()
    {

        $KeyList = $this->createKeyList();
        return '!("'.implode('"|"', $KeyList).'")!is';
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {
        return $this->Handler->getStatus();
    }


}
