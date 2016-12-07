<?php
namespace MOC\V\TestSuite\Tests\Component\Packer;

use MOC\V\Component\Packer\Component\Bridge\Repository\PclZip;
use MOC\V\Component\Packer\Component\Parameter\Repository\FileParameter;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Packer
 */
class BridgeTest extends AbstractTestCase
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

    public function testPclZipPacker()
    {

        $Bridge = new PclZip();

        try {
            $Bridge->loadFile(new FileParameter(__DIR__));
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('MOC\V\Component\Packer\Component\Exception\Repository\TypeFileException',
                $Exception);
        }

        $Bridge->loadFile(new FileParameter(__DIR__ . '/Content/Test3.zip'));
        $List = $Bridge->extractFiles();
        foreach ($List as $File) {
            $this->assertTrue($File->getRealPath() ? true : false);
        }

        try {
            $Bridge->saveFile();
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('MOC\V\Component\Packer\Component\Exception\ComponentException', $Exception);
        }

        // Create 4
        $Bridge->loadFile(new FileParameter(__DIR__ . '/Content/Test4.zip'));
        $Bridge->compactFile(new FileParameter(__DIR__ . '/Content/Test1.txt'), __DIR__);
        $Bridge->compactFile(new FileParameter(__DIR__ . '/Content/Test2.txt'), __DIR__);
        // Read 4
        $List = $Bridge->extractFiles();
        foreach ($List as $File) {
            $this->assertTrue($File->getRealPath() ? true : false);
        }

        // Try 5
        $Bridge->loadFile(new FileParameter(__DIR__ . '/Content/Test5.zip'));
        $this->assertEmpty($Bridge->extractFiles());

        // Retry 4 Read
        $Bridge->loadFile(new FileParameter(__DIR__ . '/Content/Test4.zip'));
        $List = $Bridge->extractFiles();
        foreach ($List as $File) {
            $this->assertTrue($File->getRealPath() ? true : false);
        }
    }
}
