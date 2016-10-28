<?php
namespace SPHERE\System\Database;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use MOC\V\Component\Database\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Flash;
use SPHERE\Common\Frontend\Icon\Repository\Off;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Icon\Repository\Warning;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\System\Cache\Handler\APCuHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;
use SPHERE\System\Database\Fitting\ApcCache;
use SPHERE\System\Database\Fitting\ColumnHydrator;
use SPHERE\System\Database\Fitting\IdHydrator;
use SPHERE\System\Database\Fitting\Logger;
use SPHERE\System\Database\Fitting\Manager;
use SPHERE\System\Database\Link\Connection;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Database\Link\Register;
use SPHERE\System\Debugger\Logger\QueryLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Database
 *
 * @package SPHERE\System\Database
 */
class Database extends Extension
{

    /** @var Identifier $Identifier */
    private $Identifier = null;
    /** @var array $Configuration */
    private $Configuration = array();
    /** @var array $Protocol */
    private $Protocol = array();
    /** @var int $Timeout */
    private $Timeout = 1;
    /** @var bool $UseCache */
    private $UseCache = true;
    /** @var null|bool $Debug */
    private $Debug = null;

    /**
     * @param Identifier $Identifier
     *
     * @throws \Exception
     */
    public function __construct(Identifier $Identifier)
    {

        $this->Identifier = $Identifier;
        $Register = new Register();
        if (!$Register->hasConnection($this->Identifier)) {
            $Configuration = (new ConfigFactory())
                ->createReader(__DIR__.'/Configuration.ini', new IniReader())
                ->getConfig();

            if (null !== $Configuration->getContainer($this->Identifier->getConfiguration(true))) {
                $this->Configuration = $Configuration->getContainer($this->Identifier->getConfiguration(true));
                $Driver = '\\SPHERE\\System\\Database\\Type\\'.$this->Configuration->getContainer('Driver')->getValue();
                $Register->addConnection(
                    $this->Identifier,
                    new Connection(
                        $this->Identifier,
                        new $Driver,
                        $this->Configuration->getContainer('Username')->getValue(),
                        $this->Configuration->getContainer('Password')->getValue(),
                        ( null === $this->Configuration->getContainer('Database')
                            ? str_replace(':', '', $this->Identifier->getConfiguration(false))
                            : $this->Configuration->getContainer('Database')->getValue() ),
                        $this->Configuration->getContainer('Host')->getValue(),
                        ( null === $this->Configuration->getContainer('Port')
                            ? null
                            : $this->Configuration->getContainer('Port')->getValue() ),
                        $this->Timeout
                    )
                );
            } else {
                if (null !== $Configuration->getContainer($this->Identifier->getConfiguration(false))) {
                    $this->Configuration = $Configuration->getContainer($this->Identifier->getConfiguration(false));
                    $Driver = '\\SPHERE\\System\\Database\\Type\\'.$this->Configuration->getContainer('Driver')->getValue();
                    $Register->addConnection(
                        $this->Identifier,
                        new Connection(
                            $this->Identifier,
                            new $Driver,
                            $this->Configuration->getContainer('Username')->getValue(),
                            $this->Configuration->getContainer('Password')->getValue(),
                            ( null === $this->Configuration->getContainer('Database')
                                ? str_replace(':', '', $this->Identifier->getConfiguration(false))
                                : $this->Configuration->getContainer('Database')->getValue() ),
                            $this->Configuration->getContainer('Host')->getValue(),
                            ( null === $this->Configuration->getContainer('Port')
                                ? null
                                : $this->Configuration->getContainer('Port')->getValue() ),
                            $this->Timeout
                        )
                    );
                } else {
                    throw new \Exception(__CLASS__.' > Missing Configuration: ('.$this->Identifier->getConfiguration().')');
                }
            }
        }
    }

    /**
     * EntityManager
     *
     * @param string $EntityPath
     * @param string $EntityNamespace
     * @param bool $useCache disable this if Unit-of-Work is Out-of-Sync with cached Manager (sometimes)
     *
     * @return Manager
     * @throws \Doctrine\ORM\ORMException
     */
    public function getEntityManager($EntityPath, $EntityNamespace, $useCache = true)
    {

        // Sanitize Namespace
        $EntityNamespace = trim(str_replace(array('/', '\\'), '\\', $EntityNamespace), '\\').'\\';

        // Manager Cache
        /** @var MemoryHandler $SystemMemcached */
        $ManagerCache = $this->getCache(new MemoryHandler());
        $Manager = $ManagerCache->getValue((string)$this->Identifier.$EntityNamespace.$EntityPath, __METHOD__);

        if (!$useCache || null === $Manager) {

            // System Cache
            $MemcachedHandler = $this->getCache(new MemcachedHandler());
            $APCuHandler = $this->getCache(new APCuHandler());

            $MetadataConfiguration = Setup::createAnnotationMetadataConfiguration(array($EntityPath));
            $MetadataConfiguration->setDefaultRepositoryClassName('\SPHERE\System\Database\Fitting\Repository');
            $MetadataConfiguration->addCustomHydrationMode(
                ColumnHydrator::HYDRATION_MODE, '\SPHERE\System\Database\Fitting\ColumnHydrator'
            );
            $MetadataConfiguration->addCustomHydrationMode(
                IdHydrator::HYDRATION_MODE, '\SPHERE\System\Database\Fitting\IdHydrator'
            );

            $ConnectionConfig = $this->getConnection()->getConnection()->getConfiguration();

            if ($this->UseCache) {
                if ($MemcachedHandler instanceof MemcachedHandler) {
                    $Cache = new MemcachedCache();
                    $Cache->setMemcached($MemcachedHandler->getCache());
                    $Cache->setNamespace($EntityPath);
                    $ConnectionConfig->setResultCacheImpl($Cache);
                    $MetadataConfiguration->setHydrationCacheImpl($Cache);
                    if ($APCuHandler instanceof APCuHandler) {
                        $MetadataConfiguration->setMetadataCacheImpl(new ApcCache());
                        $MetadataConfiguration->setQueryCacheImpl(new ApcCache());
                    } else {
                        $MetadataConfiguration->setMetadataCacheImpl(new ArrayCache());
                        $MetadataConfiguration->setQueryCacheImpl(new ArrayCache());
                    }
                } else {
                    if ($APCuHandler instanceof APCuHandler) {
                        $MetadataConfiguration->setMetadataCacheImpl(new ApcCache());
                    } else {
                        $MetadataConfiguration->setMetadataCacheImpl(new ArrayCache());
                    }
                    $MetadataConfiguration->setQueryCacheImpl(new ArrayCache());
                    $MetadataConfiguration->setHydrationCacheImpl(new ArrayCache());
                    $ConnectionConfig->setResultCacheImpl(new ArrayCache());
                }
            }

            if ($this->useDebugger()) {
                $ConnectionConfig->setSQLLogger(new Logger());
            }

            $Manager = new Manager(
                EntityManager::create($this->getConnection()->getConnection(), $MetadataConfiguration), $EntityNamespace, $this->Identifier
            );

            $ManagerCache->setValue((string)$this->Identifier.$EntityNamespace.$EntityPath, $Manager, 0, __METHOD__);
        }
        return $Manager;
    }

    /**
     * @return IBridgeInterface|null
     * @throws \Exception
     */
    public function getConnection()
    {

        return (new Register())->getConnection($this->Identifier)->getConnection();
    }

    /**
     * @return bool|null
     */
    private function useDebugger()
    {
        if ($this->Debug === null) {
            $this->Debug = $this->getLogger(new QueryLogger())->isEnabled();
        }
        return $this->Debug;
    }

    /**
     * @param $Statement
     *
     * @return int The number of affected rows
     */
    public function setStatement($Statement)
    {

        return $this->getConnection()->prepareStatement($Statement)->executeWrite();
    }

    /**
     * @param $Statement
     *
     * @return array
     */
    public function getStatement($Statement)
    {

        return $this->getConnection()->prepareStatement($Statement)->executeRead();
    }

    /**
     * @return AbstractPlatform
     * @throws \Exception
     */
    public function getPlatform()
    {

        return $this->getConnection()->getConnection()->getDatabasePlatform();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDatabase()
    {

        return $this->getConnection()->getConnection()->getDatabase();
    }

    /**
     * @param string $ViewName
     *
     * @return bool
     */
    public function hasView($ViewName)
    {

        return array_key_exists(strtolower($ViewName), $this->getSchemaManager()->listViews());
    }

    /**
     * @return AbstractSchemaManager
     */
    public function getSchemaManager()
    {

        return $this->getConnection()->getSchemaManager();
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {

        return $this->getSchemaManager()->createSchema();
    }

    /**
     * @param string $TableName
     * @param string $ColumnName
     *
     * @return bool
     */
    public function hasColumn($TableName, $ColumnName)
    {

        return in_array(strtolower($ColumnName),
            array_keys($this->getSchemaManager()->listTableColumns($TableName)));
    }

    /**
     * @param Table $Table
     * @param array $ColumnList
     *
     * @return bool
     */
    public function hasIndex(Table $Table, $ColumnList)
    {

        if ($Table->columnsAreIndexed($ColumnList)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Table $Table
     * @param array $ColumnList
     *
     * @return void
     */
    public function removeIndex(Table $Table, $ColumnList)
    {

        $IndexList = $Table->getIndexes();
        /** @var Index $Index */
        foreach ($IndexList as $Index) {

            if ($Index->spansColumns($ColumnList)
                && count($ColumnList) === count($Index->getColumns())
                && (
                    $Index->isSimpleIndex()
                    || $Index->isUnique()
                )
            ) {
                $Table->dropIndex($Index->getName());
            }
        }
    }

    /**
     * @param string $TableName
     *
     * @return bool
     */
    public function hasTable($TableName)
    {

        return in_array($TableName, $this->getSchemaManager()->listTableNames());
    }

    /**
     * e.g. Dead Columns
     * @param string $Item
     */
    public function deadProtocol($Item)
    {
        $this->Protocol[] = new \SPHERE\Common\Frontend\Message\Repository\Warning(
            'Possible Dead: '.$Item, new Exclamation()
        );
    }

    /**
     * @param string $Item
     */
    public function addProtocol($Item)
    {

        if (empty( $this->Protocol )) {
            $this->Protocol[] = '<samp>'.$Item.'</samp>';
        } else {
            $this->Protocol[] = '<div>'.new Transfer().'&nbsp;<samp>'.$Item.'</samp></div>';
        }
    }

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function getProtocol($Simulate = false)
    {
        $MessageList = array();
        foreach ( $this->Protocol as $Index => $Item ) {
            if( $Item instanceof \SPHERE\Common\Frontend\Message\Repository\Warning ) {
                $MessageList[] = $Item;
                $this->Protocol[$Index] = false;
            }
        }
        $this->Protocol = array_filter( $this->Protocol );

        if (count($this->Protocol) == 1) {
            $Protocol = new Success(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(implode('', $MessageList)),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn( !empty($this->Protocol) ? new Ok().'&nbsp'.implode('', $this->Protocol) : '', 9),
                        new LayoutColumn(new Off().'&nbsp;Kein Update notwendig', 3)
                    ))
                )))
            );
        } else {
            $Protocol = new Info(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(implode('', $MessageList)),
                    )),
                    new LayoutRow(array(
                      new LayoutColumn(new Flash().'&nbsp;'.implode('', $this->Protocol), 9),
                        new LayoutColumn(
                            ( $Simulate
                                ? new Warning().'&nbsp;Update notwendig'
                                : new Ok().'&nbsp;Update durchgeführt'
                            ), 3)
                    ))
                )))
            );
        }
        $this->Protocol = array();
        return $Protocol;
    }
}
