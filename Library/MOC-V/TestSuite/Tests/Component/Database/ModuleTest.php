<?php
namespace MOC\V\TestSuite\Tests\Component\Database;

use MOC\V\Component\Database\Component\Parameter\Repository\DriverParameter;
use MOC\V\Component\Database\Database;
use MOC\V\Component\Database\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Database
 */
class ModuleTest extends AbstractTestCase
{

    public function testModule()
    {

        /** @var \MOC\V\Component\Database\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder('MOC\V\Component\Database\Component\Bridge\Bridge')->getMock();
        $Vendor = new Vendor(new $MockBridge);
        $Module = new Database($Vendor);

        $this->assertInstanceOf('MOC\V\Component\Database\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf('MOC\V\Component\Database\Component\IVendorInterface',
            $Module->setBridgeInterface($MockBridge)
        );
        $this->assertInstanceOf('MOC\V\Component\Database\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );

    }

    public function testStaticDoctrineDatabase()
    {

        $Database = Database::getDoctrineDatabase('', '', '', DriverParameter::DRIVER_PDO_SQLITE, 'sqlite::memory:');
        $this->assertInstanceOf('MOC\V\Component\Database\Component\IBridgeInterface', $Database);
    }

    public function testStaticDatabase()
    {

        try {
            Database::getDatabase('', '', '', DriverParameter::DRIVER_PDO_SQLITE, 'sqlite::memory:');
        } catch (\Exception $E) {

        }
        try {
            Database::getDatabase('', '', '', 0, 'Wrong');
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Database\Exception\DatabaseException', $E);
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
