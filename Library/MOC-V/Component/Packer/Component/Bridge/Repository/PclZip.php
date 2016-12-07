<?php
namespace MOC\V\Component\Packer\Component\Bridge\Repository;

use MOC\V\Component\Packer\Component\Bridge\Bridge;
use MOC\V\Component\Packer\Component\Exception\ComponentException;
use MOC\V\Component\Packer\Component\IBridgeInterface;
use MOC\V\Component\Packer\Component\Parameter\Repository\FileParameter;
use MOC\V\Core\FileSystem\FileSystem;

/**
 * Class PclZip
 *
 * @package MOC\V\Component\Packer\Component\Bridge\Repository
 */
class PclZip extends Bridge implements IBridgeInterface
{

    private $Instance = null;

    /**
     * PclZip constructor.
     */
    public function __construct()
    {
        require_once(__DIR__ . '/../../../Vendor/PclZip/2.8.2/pclzip.lib.php');
        $this->Instance = new \PclZip('');
    }

    /**
     * @param FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function loadFile(FileParameter $Location)
    {

        $this->setFileParameter($Location);
        $this->Instance->zipname = $this->getFileParameter()->getFile();
        return $this;
    }

    /**
     * @param null|FileParameter $Location
     * @return IBridgeInterface
     * @throws ComponentException
     */
    public function saveFile(FileParameter $Location = null)
    {

        throw new ComponentException(__METHOD__ . ' can not be used with this vendor');
    }

    /**
     * @param FileParameter $Location
     * @param null|string|false $RewriteBase (null = Use original Path structure, string = Remove this Path, false = Remove Path completely)
     *
     * @return IBridgeInterface
     */
    public function compactFile(FileParameter $Location, $RewriteBase = null)
    {
        if ($this->getFileParameter()->getFileInfo()->isFile()) {
            if ($RewriteBase === null) {
                $this->Instance->add($Location->getFile(), PCLZIP_OPT_TEMP_FILE_OFF);
            } else if ($RewriteBase === false) {
                $this->Instance->add($Location->getFile(), PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_TEMP_FILE_OFF);
            } else {
                $this->Instance->add($Location->getFile(), PCLZIP_OPT_REMOVE_PATH, $RewriteBase, PCLZIP_OPT_TEMP_FILE_OFF);
            }
        } else {
            if ($RewriteBase === null) {
                $this->Instance->create($Location->getFile(), PCLZIP_OPT_TEMP_FILE_OFF);
            } else if ($RewriteBase === false) {
                $this->Instance->create($Location->getFile(), PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_TEMP_FILE_OFF);
            } else {
                $this->Instance->create($Location->getFile(), PCLZIP_OPT_REMOVE_PATH, $RewriteBase, PCLZIP_OPT_TEMP_FILE_OFF);
            }
        }
        return $this;
    }

    /**
     * @return \MOC\V\Core\FileSystem\Component\IBridgeInterface[]
     * @throws ComponentException
     */
    public function extractFiles()
    {
        if ($this->getFileParameter()->getFileInfo()->isFile()) {
            $TmpDirectory = sys_get_temp_dir();
            $SubDirectory = uniqid(sha1(__METHOD__), true);

            $List = $this->Instance->extract(PCLZIP_OPT_PATH, $TmpDirectory . DIRECTORY_SEPARATOR . $SubDirectory);
            if (is_array($List) && !empty($List)) {
                array_walk($List,
                    function (&$File) {
                        if (!$File['folder']) {
                            $File = FileSystem::getFileLoader($File['filename']);
                        } else {
                            $File = false;
                        }
                    }
                );
                $List = array_filter($List);
                return $List;
            } else if (is_array($List)) {
                return array();
            } else {
                throw new ComponentException(__METHOD__ . ' Undefined Error');
            }
        }
        return array();
    }
}