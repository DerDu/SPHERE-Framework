<?php
namespace SPHERE\Application\Document\Explorer\Storage;

use MOC\V\Core\GlobalsKernel\GlobalsKernel;

/**
 * @deprecated
 * Class AbstractStorage
 *
 * @package SPHERE\Application\Document\Explorer\Storage
 */
abstract class AbstractStorage
{

    /** @var string $Name */
    private $Name = '';
    /** @var string $Description */
    private $Description = '';
    /** @var string $FileLocation */
    private $FileLocation = '';
    /** @var string $FilePath */
    private $FilePath = '';
    /** @var string $FileName */
    private $FileName = '';
    /** @var string $FileExtension */
    private $FileExtension = '';
    /** @var string $FileContent */
    private $FileContent = '';
    /** @var string $FileType */
    private $FileType = '';
    /** @var int $FileSize */
    private $FileSize = 0;

    /**
     * @deprecated
     * @return string
     */
    public function getName()
    {

        if (empty( $this->Name )) {
            $this->Name = preg_replace('![^0-9a-z\s]!is', '', $this->FileName);
        }
        return $this->Name;
    }

    /**
     * @deprecated
     *
*@param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @deprecated
     *
*@param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getFileName()
    {

        if (empty( $this->FileName )) {
            $this->FileName = preg_replace('![^0-9a-z]!is', '', $this->Name);
        }
        return $this->FileName;
    }

    /**
     * @deprecated
     *
*@param string $FileName
     */
    public function setFileName($FileName)
    {

        $this->FileName = $FileName;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getFileExtension()
    {

        return $this->FileExtension;
    }

    /**
     * @deprecated
     * @param string $FileExtension
     */
    public function setFileExtension($FileExtension)
    {

        $this->FileExtension = $FileExtension;
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
     * @param string $FileContent
     */
    public function setFileContent($FileContent)
    {

        $this->FileContent = (string)$FileContent;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getFileType()
    {

        return $this->FileType;
    }

    /**
     * @deprecated
     * @param string $FileType
     */
    public function setFileType($FileType)
    {

        $this->FileType = $FileType;
    }

    /**
     * @deprecated
     * @return int
     */
    public function getFileSize()
    {

        if (empty( $this->FileSize )) {
            $this->setFileSize(filesize($this->getRealPath()));
        }
        return $this->FileSize;
    }

    /**
     * @deprecated
     * @param int $FileSize
     */
    public function setFileSize($FileSize)
    {

        $this->FileSize = $FileSize;
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
     * @param string $FileLocation
     */
    public function setFileLocation($FileLocation)
    {

        $this->FileLocation = $FileLocation;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getFilePath()
    {

        if (empty( $this->FilePath )) {
            $this->setFilePath(dirname($this->getFileLocation()));
        }
        return $this->FilePath;
    }

    /**
     * @deprecated
     * @param string $FilePath
     */
    public function setFilePath($FilePath)
    {

        $this->FilePath = $FilePath;
    }
}
