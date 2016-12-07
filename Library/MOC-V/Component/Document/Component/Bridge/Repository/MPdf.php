<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository;

use MOC\V\Component\Document\Component\Bridge\Bridge;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Template\Component\IBridgeInterface as IBridgeInterface_Template;

/**
 * Class MPdf
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository
 */
class MPdf extends Bridge implements IBridgeInterface
{

    /** @var string $Source */
    private $Source = '';

    /**
     * MPdf constructor.
     */
    public function __construct()
    {

        require_once( __DIR__.'/../../../Vendor/mPdf/6.0.0/mpdf.php' );
    }

    /**
     * @param FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function loadFile(FileParameter $Location)
    {

        $this->setFileParameter($Location);
        return $this;
    }

    /**
     * @param IBridgeInterface_Template $Template
     *
     * @return IBridgeInterface
     */
    public function setContent(IBridgeInterface_Template $Template)
    {

        $this->Source = $Template->getContent();
        return $this;
    }

    /**
     * @param null|FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function saveFile(FileParameter $Location = null)
    {

        $Content = $this->getContent();
        if (null === $Location) {
            file_put_contents($this->getFileParameter()->getFile(), $Content);
        } else {
            file_put_contents($Location->getFile(), $Content);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $Renderer = new \mPDF(
            'utf-8',
            $this->getPaperSizeParameter()->getSize(),
            0, '', 15, 15, 16, 16, 9, 9,
            substr($this->getPaperOrientationParameter()->getOrientation(), 0, 1)
        );
        $Renderer->debug = true;
        $Renderer->WriteHTML($this->Source);
        return $Renderer->Output('', 'S');
    }

}
