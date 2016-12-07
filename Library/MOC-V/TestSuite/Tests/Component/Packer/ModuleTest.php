<?php
namespace MOC\V\TestSuite\Tests\Component\Packer;

use MOC\V\Component\Packer\Packer;
use MOC\V\Component\Packer\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Packer
 */
class ModuleTest extends AbstractTestCase
{

    /**
     * @codeCoverageIgnore
     */
    public function tearDown()
    {

        if (false !== ($Path = realpath(__DIR__ . '/Content'))) {
            $Iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($Path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \SplFileInfo $FileInfo */
            foreach ($Iterator as $FileInfo) {
                if (
                    $FileInfo->getBasename() != 'README.md'
                    && $FileInfo->getBasename() != 'Test1.txt'
                    && $FileInfo->getBasename() != 'Test2.txt'
                    && $FileInfo->getBasename() != 'Test3.zip'
                ) {
                    if ($FileInfo->isFile()) {
                        unlink($FileInfo->getPathname());
                    }
                    if ($FileInfo->isDir()) {
                        rmdir($FileInfo->getPathname());
                    }
                }
            }
        }
    }

    public function testModule()
    {

        /** @var \MOC\V\Component\Packer\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder('MOC\V\Component\Packer\Component\Bridge\Bridge')->getMock();
        $Vendor = new Vendor(new $MockBridge);
        $Module = new Packer($Vendor);

        $this->assertInstanceOf('MOC\V\Component\Packer\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf('MOC\V\Component\Packer\Component\IVendorInterface',
            $Module->setBridgeInterface($MockBridge)
        );
        $this->assertInstanceOf('MOC\V\Component\Packer\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );

    }

    public function testStaticZipPacker()
    {

        $Packer = Packer::getZipPacker(__DIR__ . '/Content/Test3.zip');
        $this->assertInstanceOf('MOC\V\Component\Packer\Component\IBridgeInterface', $Packer);
    }

    public function testStaticPacker()
    {

        try {
            Packer::getPacker(__FILE__);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Packer\Exception\PackerTypeException', $E);
        }
        try {
            Packer::getPacker('Missing.zip');
        } catch (\Exception $E) {

        }
    }
}
