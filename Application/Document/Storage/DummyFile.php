<?php
namespace SPHERE\Application\Document\Storage;

use MOC\V\Core\FileSystem\FileSystem;
use MOC\V\Core\GlobalsKernel\GlobalsKernel;

/**
 * @deprecated
 * Class DummyFile
 *
 * @package SPHERE\Application\Document\Storage
 */
class DummyFile
{

    /** @var string $FileName */
    private $FileDirectory = '';
    /** @var string $FileName */
    private $FileName = '';
    /** @var string $FileLocation */
    private $FileLocation = '';
    /** @var string $FileContent */
    private $FileContent = '';

    /** @var bool $Destruct */
    private $Destruct = true;

    /**
     * @deprecated
     *
*@param string $Extension
     * @param string $Prefix
     * @param bool   $Destruct
     */
    public function __construct($Extension = 'document-storage', $Prefix = 'SPHERE-Temporary', $Destruct = true)
    {

        $this->FileDirectory = sys_get_temp_dir();
        $this->FileName = $Prefix.'-'.md5(uniqid($Prefix, true)).'.'.$Extension;
        $Location = $this->FileDirectory.DIRECTORY_SEPARATOR.$this->FileName;
        $this->setFileLocation($Location);
        $this->Destruct = (bool)$Destruct;
    }

    /**
     * @deprecated
     */
    public function __destruct()
    {

        if ($this->Destruct && $this->getRealPath()) {
            unlink($this->getRealPath());
        }
    }

    /**
     * @deprecated
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
     * @deprecated
     * @return string
     */
    public function getFileLocation()
    {

        return $this->FileLocation;
    }

    /**
     * @deprecated
     *
*@param string $FileLocation
     */
    public function setFileLocation($FileLocation)
    {

        $this->FileLocation = $FileLocation;
    }

    /**
     * @deprecated
     */
    public function loadFile()
    {

        $this->setFileContent(file_get_contents($this->getRealPath()));
    }

    /**
     * @deprecated
     */
    public function saveFile()
    {

        if (!$this->getRealPath()) {
            touch($this->getFileLocation());
        }
        file_put_contents($this->getRealPath(), $this->getFileContent(), LOCK_EX);
    }

    /**
     * @deprecated
     * @return string
     */
    public function getFileContent()
    {

        return $this->FileContent;
    }

    /**
     * @deprecated
     *
*@param string $FileContent
     */
    public function setFileContent($FileContent)
    {

        $this->FileContent = (string)$FileContent;
    }

    /**
     * @deprecated
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
     * @deprecated
     * @return false|null|string
     */
    public function getMimeType()
    {

        return FileSystem::getFileLoader($this->getFileLocation())->getMimeType();
    }

    /**
     * @deprecated
     * @return string
     */
    public function getFileName()
    {

        return $this->FileName;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getFileDirectory()
    {

        return $this->FileDirectory;
    }
}
