<?php
namespace SPHERE\Application\Document\Storage;

use MOC\V\Core\FileSystem\FileSystem;
use MOC\V\Core\GlobalsKernel\GlobalsKernel;

/**
 * Class FilePointer
 *
 * @package SPHERE\Application\Document\Storage
 */
class FilePointer extends DummyFile
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

    const TYPE_UNIQUE = 0;
    const TYPE_DATE = 1;

    /**
     * @param string $Extension
     * @param string $Prefix
     * @param bool $Destruct
     * @param int $Type FilePointer::TYPE_UNIQUE
     */
    public function __construct(
        $Extension = 'document-storage',
        $Prefix = 'SPHERE-Temporary',
        $Destruct = true,
        $Type = FilePointer::TYPE_UNIQUE
    ) {

        $this->FileDirectory = sys_get_temp_dir();
        switch( $Type ) {
            case FilePointer::TYPE_UNIQUE:
                $this->FileName = $Prefix . '-' . md5(uniqid($Prefix, true)) . '.' . $Extension;
                break;
            case FilePointer::TYPE_DATE:
                $this->FileName = $Prefix . '-' . date('ymd') . '.' . $Extension;
                break;
            default:
                $this->FileName = $Prefix . '-' . md5(uniqid($Prefix, true)) . '.' . $Extension;
                break;
        }
        $Location = $this->FileDirectory.DIRECTORY_SEPARATOR.$this->FileName;
        $this->setFileLocation($Location);
        $this->Destruct = (bool)$Destruct;
    }

    /**
     * @param bool $isDestruct
     */
    public function setDestruct($isDestruct = true)
    {

        $this->Destruct = $isDestruct;
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
        $SplFileInfo = ( new \SplFileInfo($this->getFileLocation()) );
        if (!$SplFileInfo->getRealPath()) {
            $SplFileInfo = ( new \SplFileInfo($SERVER['DOCUMENT_ROOT'].$this->getFileLocation()) );
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

    /**
     * @return string
     */
    public function getFileName()
    {

        return $this->FileName;
    }

    /**
     * @return string
     */
    public function getFileDirectory()
    {

        return $this->FileDirectory;
    }
}
