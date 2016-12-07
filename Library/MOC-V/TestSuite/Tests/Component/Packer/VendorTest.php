<?php
namespace MOC\V\TestSuite\Tests\Component\Packer;

use MOC\V\Component\Packer\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Packer
 */
class VendorTest extends AbstractTestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Component\Packer\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass('MOC\V\Component\Packer\Component\Bridge\Bridge');

        $Vendor = new Vendor($MockBridge);

        $this->assertInstanceOf('MOC\V\Component\Packer\Component\IBridgeInterface',
            $Vendor->getBridgeInterface());

        $this->assertInstanceOf('MOC\V\Component\Packer\Component\IVendorInterface',
            $Vendor->setBridgeInterface($MockBridge));
    }
}
