<?php
namespace MOC\V\TestSuite\Tests\Component\Packer;

use MOC\V\Component\Packer\Component\Exception\ComponentException;
use MOC\V\Component\Packer\Exception\PackerException;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ExceptionTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Packer
 */
class ExceptionTest extends AbstractTestCase
{

    public function testPackerException()
    {

        try {
            throw new PackerException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Packer\Exception\PackerException', $E);
        }

        try {
            throw new ComponentException();
        } catch (\Exception $E) {
            $this->assertInstanceOf('\MOC\V\Component\Packer\Component\Exception\ComponentException', $E);
        }
    }
}
