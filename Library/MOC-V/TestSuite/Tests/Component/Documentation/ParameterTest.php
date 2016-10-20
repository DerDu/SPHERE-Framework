<?php
namespace MOC\V\TestSuite\Tests\Component\Documentation;

use MOC\V\Component\Documentation\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Component\Documentation\Component\Parameter\Repository\ExcludeParameter;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ParameterTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Documentation
 */
class ParameterTest extends AbstractTestCase
{

    public function testAbstractParameter()
    {

        /** @var \MOC\V\Component\Documentation\Component\Parameter\Parameter $MockParameter */
        $MockParameter = $this->getMockForAbstractClass('MOC\V\Component\Documentation\Component\Parameter\Parameter');

        $Parameter = new $MockParameter();
        $this->assertInstanceOf('MOC\V\Component\Documentation\Component\Parameter\Parameter', $Parameter);

    }

    public function testDirectoryParameter()
    {

        try {
            new DirectoryParameter(null);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Documentation\Component\Exception\Repository\EmptyDirectoryException',
                $E);
        }

        $Parameter = new DirectoryParameter(__DIR__);
        $this->assertEquals(__DIR__, $Parameter->getDirectory());

        try {
            $Parameter->setDirectory(__FILE__);
        } catch (\Exception $E) {
            $this->assertInstanceOf('MOC\V\Component\Documentation\Component\Exception\Repository\TypeDirectoryException',
                $E);
        }

    }

    public function testExcludeParameter()
    {

        $Parameter = new ExcludeParameter(array('/Test1/*', '/Test2/*'));
        $this->assertEquals('/Test1/*,/Test2/*', $Parameter->getGlobString());
    }
}
