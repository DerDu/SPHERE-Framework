<?php
namespace SPHERE\Application\Document\Storage;

use MOC\V\Core\FileSystem\FileSystem;
use MOC\V\Core\GlobalsKernel\GlobalsKernel;

/**
 * Class DummyFile
 *
 * @package SPHERE\Application\Document\Storage
 */
class DummyFile
{

    /** @var string $FileLocation */
    private $FileLocation = '';
    /** @var string $FileContent */
    private $FileContent = '';

    /** @var bool $Destruct */
    private $Destruct = true;

    /**
     * @param string $Extension
     * @param string $Prefix
     * @param bool   $Destruct
     */
    public function __construct($Extension = 'document-storage', $Prefix = 'SPHERE-Temporary', $Destruct = true)
    {

        $Location = sys_get_temp_dir().DIRECTORY_SEPARATOR.$Prefix.'-'.md5(uniqid($Prefix, true)).'.'.$Extension;
        $this->setFileLocation($Location);
        $this->Destruct = (bool)$Destruct;
    }

    /**
     *
     */
    public function __destruct()
    {

        if ($this->Destruct && $this->getRealPath()) {
            unlink($this->getRealPath());
        }
    }

    /**
     * @return string
     */
    public function getRealPath()
    {

        $SERVER = GlobalsKernel::getGlobals()->getSERVER();
        $SplFileInfo = (new \SplFileInfo($this->getFileLocation()));
        if (!$SplFileInfo->getRealPath()) {
            $SplFileInfo = (new \SplFileInfo($SERVER['DOCUMENT_ROOT'].$this->getFileLocation()));
        }
        return $SplFileInfo->getRealPath() ? $SplFileInfo->getRealPath() : '';
    }

    /**
     * @return string
     */
    public function getFileLocation()
    {

        return $this->FileLocation;
    }

    /**
     * @param string $FileLocation
     */
    public function setFileLocation($FileLocation)
    {

        $this->FileLocation = $FileLocation;
    }

    /**
     *
     */
    public function loadFile()
    {

        $this->setFileContent(file_get_contents($this->getRealPath()));
    }

    /**
     *
     */
    public function saveFile()
    {

        if (!$this->getRealPath()) {
            touch($this->getFileLocation());
        }
        file_put_contents($this->getRealPath(), $this->getFileContent(), LOCK_EX);
    }

    /**
     * @return string
     */
    public function getFileContent()
    {

        return $this->FileContent;
    }

    /**
     * @param string $FileContent
     */
    public function setFileContent($FileContent)
    {

        $this->FileContent = (string)$FileContent;
    }

    /**
     * @return bool
     */
    public function getFileExists()
    {

        if ($this->getRealPath()) {
            return true;
        }
        return false;
    }

    /**
     * @return false|null|string
     */
    public function getMimeType()
    {

        return FileSystem::getFileLoader($this->getFileLocation())->getMimeType();
    }
}