<?php
namespace MOC\V\Component\Packer\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Component\Packer\Component
 */
interface IVendorInterface
{

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface();

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IVendorInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface);
}
