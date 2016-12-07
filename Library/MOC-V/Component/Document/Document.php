<?php
namespace MOC\V\Component\Document;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Bridge\Repository\MPdf;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpWord;
use MOC\V\Component\Document\Component\Bridge\Repository\UniversalXml;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\IVendorInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use MOC\V\Component\Document\Vendor\Vendor;

/**
 * Class Document
 *
 * @package MOC\V\Component\Document
 */
class Document implements IVendorInterface
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
     * @throws DocumentTypeException
     */
    public static function getDocument($Location)
    {

        $FileInfo = new \SplFileInfo($Location);
        switch (strtolower($FileInfo->getExtension())) {
            case 'pdf': {
                return self::getPdfDocument($Location);
            }
            case 'csv':
            case 'xls':
            case 'xlsx': {
                return self::getExcelDocument($Location);
            }
            case 'doc':
            case 'docx': {
                return self::getWordDocument($Location);
            }
            case 'xml': {
                return self::getXmlDocument($Location);
            }
            default:
                throw new DocumentTypeException();
        }
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    public static function getPdfDocument($Location)
    {

        $Document = new Document(
            new Vendor(
                new DomPdf()
            )
        );

        if (file_exists(new FileParameter($Location))) {
            $Document->getBridgeInterface()->loadFile(new FileParameter($Location));
        }

        return $Document->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    public static function getExcelDocument($Location)
    {

        $Document = new Document(
            new Vendor(
                new PhpExcel()
            )
        );
        /** @var PhpExcel $Bridge */
        $Bridge = $Document->getBridgeInterface();
        if (file_exists(new FileParameter($Location))) {
            $Bridge->loadFile(new FileParameter($Location));
        } else {
            $Bridge->newFile(new FileParameter($Location));
        }

        return $Bridge;
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    public static function getWordDocument($Location)
    {

        $Document = new Document(
            new Vendor(
                new PhpWord()
            )
        );
        /** @var PhpWord $Bridge */
        $Bridge = $Document->getBridgeInterface();
        if (file_exists(new FileParameter($Location))) {
            $Bridge->loadFile(new FileParameter($Location));
        } else {
            $Bridge->newFile(new FileParameter($Location));
        }

        return $Bridge;
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    public static function getXmlDocument($Location)
    {

        $Document = new Document(
            new Vendor(
                new UniversalXml()
            )
        );

        if (file_exists(new FileParameter($Location))) {
            $Document->getBridgeInterface()->loadFile(new FileParameter($Location));
        }

        return $Document->getBridgeInterface();
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    public static function getPdfCreator($Location)
    {

        $Document = new Document(
            new Vendor(
                new MPdf()
            )
        );

        if (file_exists(new FileParameter($Location))) {
            $Document->getBridgeInterface()->loadFile(new FileParameter($Location));
        }

        return $Document->getBridgeInterface();
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
