<?php
namespace MOC\V\TestSuite\Tests\Core\FileSystem;

use MOC\V\Core\FileSystem\FileSystem;
use MOC\V\Core\FileSystem\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Core\FileSystem
 */
class ModuleTest extends AbstractTestCase
{

    public function testModule()
    {

        /** @var \MOC\V\Core\FileSystem\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder('MOC\V\Core\FileSystem\Component\Bridge\Bridge')->getMock();
        $Vendor = new Vendor(new $MockBridge);
        $Module = new FileSystem($Vendor);

        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IVendorInterface',
            $Module->setBridgeInterface($MockBridge)
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );

    }

    public function testStaticUniversalDownload()
    {

        if (getenv('CI')) {
            $this->markTestSkipped(
                'Finder is not available on CircleCI'
            );
        }
        try {
            $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalDownload', array(__DIR__));
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E);
        }

        $Loader = $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalDownload',
            array(__FILE__)
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface', $Loader);

        $this->assertEquals(__FILE__, $Loader->getLocation());
        $this->assertEquals(__FILE__, $Loader->getRealPath());
        if (( $MimeType = $Loader->getMimeType() )) {
            $this->assertEquals('text/x-php', $MimeType);
        } else {
            $this->assertEquals(false, $MimeType);
        }
        $this->assertInternalType('string', (string)$Loader);
        $this->assertStringEqualsFile(__FILE__, (string)$Loader);

        $Loader = $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalDownload',
            array(basename(__FILE__))
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface', $Loader);

        $this->assertEquals(basename(__FILE__), $Loader->getLocation());
        $this->assertEquals('', $Loader->getRealPath());
        if (( $MimeType = $Loader->getMimeType() )) {
            $this->assertEquals('text/x-php', $MimeType);
        } else {
            $this->assertEquals(false, $MimeType);
        }
        $this->assertInternalType('string', (string)$Loader);
        $this->assertEquals('', (string)$Loader);
    }

    public function testStaticUniversalFileLoader()
    {

        try {
            $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalFileLoader', array(__DIR__));
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E);
        }

        $Loader = $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalFileLoader',
            array(__FILE__)
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface', $Loader);

        $this->assertEquals(__FILE__, $Loader->getLocation());
        $this->assertEquals(__FILE__, $Loader->getRealPath());
        if (( $MimeType = $Loader->getMimeType() )) {
            $this->assertEquals('text/x-php', $MimeType);
        } else {
            $this->assertEquals(false, $MimeType);
        }
        $this->assertInternalType('string', (string)$Loader);
        $this->assertStringEqualsFile(__FILE__, (string)$Loader);

        $Loader = $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalFileLoader',
            array(basename(__FILE__))
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface', $Loader);

        $this->assertEquals(basename(__FILE__), $Loader->getLocation());
        $this->assertEquals('', $Loader->getRealPath());
        if (( $MimeType = $Loader->getMimeType() )) {
            $this->assertEquals('text/x-php', $MimeType);
        } else {
            $this->assertEquals(false, $MimeType);
        }
        $this->assertInternalType('string', (string)$Loader);
        $this->assertEquals('', (string)$Loader);
    }

    public function testStaticSymfonyFinder()
    {

        if (getenv('CI')) {
            $this->markTestSkipped(
                'Finder is not available on CircleCI'
            );
        }

        try {
            $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getSymfonyFinder', array(__DIR__));
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E);
        }

        $Loader = $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getSymfonyFinder', array(__FILE__));
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface', $Loader);

        $this->assertEquals(__FILE__, $Loader->getLocation());
        $this->assertEquals(__FILE__, $Loader->getRealPath());
        if (( $MimeType = $Loader->getMimeType() )) {
            $this->assertEquals('text/x-php', $MimeType);
        } else {
            $this->assertEquals(false, $MimeType);
        }
        $this->assertInternalType('string', (string)$Loader);
        $this->assertStringEqualsFile(__FILE__, (string)$Loader);

        $Loader = $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getSymfonyFinder',
            array(basename(__FILE__))
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface', $Loader);

        $this->assertEquals(basename(__FILE__), $Loader->getLocation());
        $this->assertEquals('', $Loader->getRealPath());
        if (( $MimeType = $Loader->getMimeType() )) {
            $this->assertEquals('text/x-php', $MimeType);
        } else {
            $this->assertEquals(false, $MimeType);
        }
        $this->assertInternalType('string', (string)$Loader);
        $this->assertEquals('', (string)$Loader);
    }

    public function testStaticUniversalFileWriter()
    {

        try {
            $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalFileWriter', array(__DIR__));
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E);
        }

        $Writer = $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalFileWriter',
            array(__FILE__)
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface', $Writer);

        $this->assertEquals(__FILE__, $Writer->getLocation());
        $this->assertEquals(__FILE__, $Writer->getRealPath());
        if (( $MimeType = $Writer->getMimeType() )) {
            $this->assertEquals('text/x-php', $MimeType);
        } else {
            $this->assertEquals(false, $MimeType);
        }
        $this->assertInternalType('string', (string)$Writer);
        $this->assertStringEqualsFile(__FILE__, (string)$Writer);

        $Writer = $this->invokeClassMethod('MOC\V\Core\FileSystem\FileSystem', 'getUniversalFileWriter',
            array(basename(__FILE__))
        );
        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface', $Writer);

        $this->assertEquals(basename(__FILE__), $Writer->getLocation());
        $this->assertEquals('', $Writer->getRealPath());
        if (( $MimeType = $Writer->getMimeType() )) {
            $this->assertEquals('text/x-php', $MimeType);
        } else {
            $this->assertEquals(false, $MimeType);
        }
        $this->assertInternalType('string', (string)$Writer);
        $this->assertEquals('', (string)$Writer);
    }

    public function testStaticFileWriter()
    {

        try {
            FileSystem::getFileWriter(__DIR__);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E);
        }
        try {
            FileSystem::getFileWriter(__FILE__);
        } catch (\Exception $E) {

        }
    }

    public function testStaticFileLoader()
    {

        try {
            FileSystem::getFileLoader(__DIR__);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E);
        }
        try {
            FileSystem::getFileLoader(__FILE__);
        } catch (\Exception $E) {

        }
    }

    public function testStaticDownload()
    {

        try {
            FileSystem::getDownload(__DIR__);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException', $E);
        }
        try {
            FileSystem::getDownload(__FILE__);
        } catch (\Exception $E) {

        }
    }
}
