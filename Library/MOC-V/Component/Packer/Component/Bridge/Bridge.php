<?php
namespace MOC\V\Component\Packer\Component\Bridge;

use MOC\V\Component\Packer\Component\IBridgeInterface;
use MOC\V\Component\Packer\Component\Parameter\Repository\FileParameter;

/**
 * Class Bridge
 *
 * @package MOC\V\Component\Packer\Component\Bridge
 */
abstract class Bridge implements IBridgeInterface
{

    /** @var null|FileParameter $FileParameter */
    private $FileParameter = null;

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
