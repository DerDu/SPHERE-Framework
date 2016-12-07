<?php
namespace MOC\V\Component\Packer\Component;

use MOC\V\Component\Packer\Component\Parameter\Repository\FileParameter;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Component\Packer\Component
 */
interface IBridgeInterface
{

    /**
     * @param FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function loadFile(FileParameter $Location);

    /**
     * @param null|FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function saveFile(FileParameter $Location = null);

    /**
     * @param FileParameter $Location
     * @param null|string|false $RewriteBase (null = Use original Path structure, string = Remove this Path, false = Remove Path completely)
     *
     * @return IBridgeInterface
     */
    public function compactFile(FileParameter $Location, $RewriteBase = null);

    /**
     * @return \MOC\V\Core\FileSystem\Component\IBridgeInterface[]
     */
    public function extractFiles();
}
