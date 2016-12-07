<?php
namespace MOC\V\TestSuite\Tests\Core\FileSystem;

use MOC\V\Core\FileSystem\Component\Bridge\Repository\SymfonyFinder;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalDownload;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalFileLoader;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalFileWriter;
use MOC\V\Core\FileSystem\Component\Parameter\Repository\FileParameter;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Core\FileSystem
 */
class BridgeTest extends AbstractTestCase
{

    public function testSymfonyFinder()
    {

        if (getenv('CI')) {
            $this->markTestSkipped(
                'Finder is not available on CircleCI'
            );
        }

        new SymfonyFinder(
            new FileParameter(__FILE__)
        );
    }

    public function testUniversalFileLoader()
    {

        new UniversalFileLoader(
            new FileParameter(__FILE__)
        );
    }

    public function testUniversalFileWriter()
    {

        new UniversalFileWriter(
            new FileParameter(__FILE__)
        );
    }

    public function testUniversalDownload()
    {

        new UniversalDownload(
            new FileParameter(__FILE__)
        );
    }
}
