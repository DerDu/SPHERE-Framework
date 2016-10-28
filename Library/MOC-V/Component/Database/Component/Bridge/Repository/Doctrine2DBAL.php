<?php
namespace MOC\V\Component\Database\Component\Bridge\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use MOC\V\Component\Database\Component\Bridge\Bridge;
use MOC\V\Component\Database\Component\Exception\ComponentException;
use MOC\V\Component\Database\Component\Exception\Repository\NoConnectionException;
use MOC\V\Component\Database\Component\IBridgeInterface;
use MOC\V\Component\Database\Component\Parameter\Repository\DatabaseParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\DriverParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\HostParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\PasswordParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\PortParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\UsernameParameter;
use MOC\V\Core\AutoLoader\AutoLoader;

/**
 * Class Doctrine2DBAL
 *
 * @package MOC\V\Component\Database\Component\Bridge
 */
class Doctrine2DBAL extends Bridge implements IBridgeInterface
{

    /** @var Connection $Connection */
    private $Connection = null;

    /**
     *
     */
    public function __construct()
    {

        AutoLoader::getNamespaceAutoLoader('Doctrine\DBAL',
            __DIR__.'/../../../Vendor/Doctrine2DBAL/2.5.0/lib');
        AutoLoader::getNamespaceAutoLoader('Doctrine\Common',
            __DIR__.'/../../../Vendor/Doctrine2Common/2.5.0/lib');
    }

    /**
     * @param UsernameParameter $Username
     * @param PasswordParameter $Password
     * @param DatabaseParameter $Database
     * @param DriverParameter $Driver
     * @param HostParameter $Host
     * @param PortParameter $Port
     * @param int $Timeout
     *
     * @return array
     */
    private function buildConnectionParameter(
        UsernameParameter $Username,
        PasswordParameter $Password,
        DatabaseParameter $Database,
        DriverParameter $Driver,
        HostParameter $Host,
        PortParameter $Port,
        $Timeout = 5
    ) {
        switch( $Driver->getDriver() ) {
            case DriverParameter::DRIVER_SQLSRV: {
                return array(
                    'driver'        => $Driver->getDriver(),
                    'user'          => $Username->getUsername(),
                    'password'      => $Password->getPassword(),
                    'host'          => $Host->getHost(),
                    'dbname'        => $Database->getDatabase(),
                    'port'          => $Port->getPort(),
                    'driverOptions'  => array(
                        'LoginTimeout' => $Timeout
                    )
                );
                break;
            }
            default: {
                return array(
                    'driver'        => $Driver->getDriver(),
                    'user'          => $Username->getUsername(),
                    'password'      => $Password->getPassword(),
                    'host'          => $Host->getHost(),
                    'dbname'        => $Database->getDatabase(),
                    'port'          => $Port->getPort(),
                    'driverOptions' => array(
                        \PDO::ATTR_TIMEOUT => $Timeout,
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                    )
                );
                break;
            }
        }
    }

    /**
     * @param UsernameParameter $Username
     * @param PasswordParameter $Password
     * @param DatabaseParameter $Database
     * @param DriverParameter   $Driver
     * @param HostParameter     $Host
     * @param PortParameter     $Port
     *
     * @param int               $Timeout
     *
     * @return IBridgeInterface
     * @throws ComponentException
     */
    public function registerConnection(
        UsernameParameter $Username,
        PasswordParameter $Password,
        DatabaseParameter $Database,
        DriverParameter $Driver,
        HostParameter $Host,
        PortParameter $Port,
        $Timeout = 5
    ) {

        try {
            $Parameter = $this->buildConnectionParameter(
                $Username, $Password, $Database, $Driver, $Host, $Port, $Timeout
            );
            $Connection = DriverManager::getConnection($Parameter);
        } catch (\Exception $E) {
            // @codeCoverageIgnoreStart
            throw new ComponentException($E->getMessage(), $E->getCode(), $E);
            // @codeCoverageIgnoreEnd
        }

        try {
            $Connection->connect();
        } catch (\Exception $E) {
            throw new ComponentException($E->getMessage(), $E->getCode(), $E);
        }

        $this->Connection = $Connection;
        return $this;
    }

    /**
     * WARNING: this may drop out with no replacement
     *
     * @return Connection
     * @throws NoConnectionException
     * @codeCoverageIgnore
     */
    public function getConnection()
    {

        return $this->prepareConnection();
    }

    /**
     * @throws NoConnectionException
     * @return Connection
     */
    private function prepareConnection()
    {

        if (null === $this->Connection) {
            // @codeCoverageIgnoreStart
            throw new NoConnectionException();
            // @codeCoverageIgnoreEnd
        }
        return $this->Connection;
    }

    /**
     * WARNING: this may drop out with no replacement
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     * @throws NoConnectionException
     * @codeCoverageIgnore
     */
    public function getSchemaManager()
    {

        return $this->prepareConnection()->getSchemaManager();
    }

    /**
     * WARNING: this may drop out with no replacement
     *
     * @return QueryBuilder
     * @throws NoConnectionException
     * @codeCoverageIgnore
     */
    public function getQueryBuilder()
    {

        return $this->prepareConnection()->createQueryBuilder();
    }

    /**
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executeWrite()
    {

        $Query = $this->prepareQuery();
        return $this->prepareConnection()->executeUpdate($Query[0], $Query[1], $Query[2]);
    }

    /**
     * @return array
     */
    private function prepareQuery()
    {

        /** @var Statement $Statement */
        $Statement = array_pop(self::$StatementList);
        $ParameterCount = substr_count($Statement, '?');
        $QueryValue = array();
        $QueryType = array();
        for ($Run = 0; $Run < $ParameterCount; $Run++) {
            $Parameter = array_pop(self::$ParameterList);
            array_unshift($QueryValue, $Parameter[0]);
            array_unshift($QueryType, $Parameter[1]);
        }
        return array($Statement, $QueryValue, $QueryType);
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executeRead()
    {

        $Query = $this->prepareQuery();
        return $this->prepareConnection()->executeQuery($Query[0], $Query[1], $Query[2])->fetchAll();
    }
}
