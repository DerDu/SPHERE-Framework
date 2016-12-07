<?php
namespace MOC\V\Component\Packer;

use MOC\V\Component\Packer\Component\Bridge\Repository\PclZip;
use MOC\V\Component\Packer\Component\IBridgeInterface;
use MOC\V\Component\Packer\Component\IVendorInterface;
use MOC\V\Component\Packer\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Packer\Exception\PackerTypeException;
use MOC\V\Component\Packer\Vendor\Vendor;

/**
 * Class Packer
 *
 * @package MOC\V\Component\Packer
 */
class Packer implements IVendorInterface
{

    /** @var IVendorInterface $VendorInterface */
    private $VendorInterface = null;

    /**
     * @param IVendorInterface $VendorInterface
     */
    public function __construct(IVendorInterface $VendorInterface)
    {

        $this->setVendorInterface($VendorInterface);
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     * @throws PackerTypeException
     */
    public static function getPacker($Location)
    {

        $FileInfo = new \SplFileInfo($Location);
        switch (strtolower($FileInfo->getExtension())) {
            case 'zip': {
                return self::getZipPacker($Location);
            }
            default:
                throw new PackerTypeException();
        }
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    public static function getZipPacker($Location)
    {

        $Packer = new Packer(
            new Vendor(
                new PclZip()
            )
        );

        if (file_exists(new FileParameter($Location))) {
            $Packer->getBridgeInterface()->loadFile(new FileParameter($Location));
        }

        return $Packer->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @return IVendorInterface
     */
    public function getVendorInterface()
    {

        return $this->VendorInterface;
    }

    /**
     * @param IVendorInterface $VendorInterface
     *
     * @return IVendorInterface
     */
    public function setVendorInterface(IVendorInterface $VendorInterface)
    {

        $this->VendorInterface = $VendorInterface;
        return $this;
    }

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IBridgeInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface)
    {

        return $this->VendorInterface->setBridgeInterface($BridgeInterface);
    }
}
