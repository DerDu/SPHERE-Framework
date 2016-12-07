<?php
namespace MOC\V\TestSuite\Tests\Component\Database;

use MOC\V\Component\Database\Component\Bridge\Repository\Doctrine2DBAL;
use MOC\V\Component\Database\Component\Bridge\Repository\Doctrine2ORM;
use MOC\V\Component\Database\Component\Exception\ComponentException;
use MOC\V\Component\Database\Component\Parameter\Repository\DatabaseParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\DriverParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\HostParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\PasswordParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\PortParameter;
use MOC\V\Component\Database\Component\Parameter\Repository\UsernameParameter;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Database
 */
class BridgeTest extends AbstractTestCase
{

    public function testDoctrine2DBAL()
    {

        $Bridge = new Doctrine2DBAL();

        try {
            $Bridge->registerConnection(
                new UsernameParameter(''),
                new PasswordParameter(''),
                new DatabaseParameter(''),
                new DriverParameter(DriverParameter::DRIVER_PDO_MYSQL),
                new HostParameter(null),
                new PortParameter(null)
            );
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Database\Component\Exception\ComponentException', $E);
        }

        $Bridge->registerConnection(
            new UsernameParameter(''),
            new PasswordParameter(''),
            new DatabaseParameter(''),
            new DriverParameter(DriverParameter::DRIVER_PDO_SQLITE),
            new HostParameter('sqlite::memory:'),
            new PortParameter(null)
        );

        $this->assertInstanceOf('MOC\V\Component\Database\Component\IBridgeInterface',
            $Bridge->prepareStatement("SELECT * FROM UnitTest WHERE Id = ?")
        );
        $this->assertInstanceOf('MOC\V\Component\Database\Component\IBridgeInterface',
            $Bridge->defineParameter(1)
        );
        $this->assertInstanceOf('MOC\V\Component\Database\Component\IBridgeInterface',
            $Bridge->prepareStatement(array("SELECT * FROM UnitTest", "SELECT * FROM UnitTest"))
        );
        try {
            try {
                $Bridge->executeRead();
            } catch (\Exception $E) {
                throw new ComponentException($E->getMessage(), $E->getCode(), $E);
            }
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Database\Component\Exception\ComponentException', $E);
        }
        try {
            try {
                $Bridge->executeWrite();
            } catch (\Exception $E) {
                throw new ComponentException($E->getMessage(), $E->getCode(), $E);
            }
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Database\Component\Exception\ComponentException', $E);
        }
    }

    public function testDoctrine2ORM()
    {

        $Bridge = new Doctrine2ORM();

        try {
            $Bridge->registerConnection(
                new UsernameParameter(''),
                new PasswordParameter(''),
                new DatabaseParameter(''),
                new DriverParameter(DriverParameter::DRIVER_PDO_MYSQL),
                new HostParameter(null),
                new PortParameter(null)
            );
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Database\Component\Exception\ComponentException', $E);
        }

        $Bridge->registerConnection(
            new UsernameParameter(''),
            new PasswordParameter(''),
            new DatabaseParameter(''),
            new DriverParameter(DriverParameter::DRIVER_PDO_SQLITE),
            new HostParameter('sqlite::memory:'),
            new PortParameter(null)
        );

        $this->assertInstanceOf('MOC\V\Component\Database\Component\IBridgeInterface',
            $Bridge->prepareStatement("SELECT * FROM UnitTest WHERE Id = ?")
        );
        $this->assertInstanceOf('MOC\V\Component\Database\Component\IBridgeInterface',
            $Bridge->defineParameter(1)
        );
        $this->assertInstanceOf('MOC\V\Component\Database\Component\IBridgeInterface',
            $Bridge->prepareStatement(array("SELECT * FROM UnitTest", "SELECT * FROM UnitTest"))
        );
        try {
            try {
                $Bridge->executeRead();
            } catch (\Exception $E) {
                throw new ComponentException($E->getMessage(), $E->getCode(), $E);
            }
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Database\Component\Exception\ComponentException', $E);
        }
        try {
            try {
                $Bridge->executeWrite();
            } catch (\Exception $E) {
                throw new ComponentException($E->getMessage(), $E->getCode(), $E);
            }
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Database\Component\Exception\ComponentException', $E);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function setUp()
    {

        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped(
                'PDO SqLite Library required'
            );
        }
    }

}
