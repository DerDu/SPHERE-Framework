<?php
namespace MOC\V\Component\Packer\Vendor;

use MOC\V\Component\Packer\Component\IBridgeInterface;
use MOC\V\Component\Packer\Component\IVendorInterface;

/**
 * Class Vendor
 *
 * @package MOC\V\Component\Packer\Vendor
 */
class Vendor implements IVendorInterface
{

    /** @var IBridgeInterface $BridgeInterface */
    private $BridgeInterface = null;

    /**
     * @param IBridgeInterface $BridgeInterface
     */
    public function __construct(IBridgeInterface $BridgeInterface)
    {

        $this->setBridgeInterface($BridgeInterface);
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->BridgeInterface;
    }

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IVendorInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface)
    {

        $this->BridgeInterface = $BridgeInterface;
        return $this;
    }
}
