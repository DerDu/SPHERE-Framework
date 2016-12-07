<?php
namespace MOC\V\Component\Document\Component\Bridge;

use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;

/**
 * Class Bridge
 *
 * @package MOC\V\Component\Document\Component\Bridge
 */
abstract class Bridge implements IBridgeInterface
{

    /** @var null|FileParameter $FileParameter */
    private $FileParameter = null;
    /** @var null|PaperOrientationParameter $PaperOrientationParameter */
    private $PaperOrientationParameter = null;
    /** @var null|PaperSizeParameter $PaperSizeParameter */
    private $PaperSizeParameter = null;

    /**
     * @return null|PaperOrientationParameter
     */
    public function getPaperOrientationParameter()
    {

        if (null === $this->PaperOrientationParameter) {
            $this->setPaperOrientationParameter(new PaperOrientationParameter());
        }
        return $this->PaperOrientationParameter;
    }

    /**
     * @param PaperOrientationParameter $PaperOrientation
     *
     * @return IBridgeInterface
     */
    public function setPaperOrientationParameter(PaperOrientationParameter $PaperOrientation)
    {

        $this->PaperOrientationParameter = $PaperOrientation;
        return $this;
    }

    /**
     * @return null|PaperSizeParameter
     */
    public function getPaperSizeParameter()
    {

        if (null === $this->PaperSizeParameter) {
            $this->setPaperSizeParameter(new PaperSizeParameter());
        }
        return $this->PaperSizeParameter;
    }

    /**
     * @param PaperSizeParameter $PaperSize
     *
     * @return IBridgeInterface
     */
    public function setPaperSizeParameter(PaperSizeParameter $PaperSize)
    {

        $this->PaperSizeParameter = $PaperSize;
        return $this;
    }

    /**
     * @return null|FileParameter
     */
    protected function getFileParameter()
    {

        return $this->FileParameter;
    }

    /**
     * @param FileParameter $FileParameter
     *
     * @return IBridgeInterface
     */
    protected function setFileParameter(FileParameter $FileParameter)
    {

        $this->FileParameter = $FileParameter;
        return $this;
    }
}
