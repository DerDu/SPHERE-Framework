<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer\Type;

use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Document\Explorer\Storage\Writer\AbstractWriter;

/**
 * @deprecated
 * Class Database
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Writer\Type
 */
class Database extends AbstractWriter
{

    /** @var false|TblFile $tblFile */
    private $tblFile = false;
    /** @var null|Temporary $Container */
    private $Container = null;

    /**
     * @deprecated
     *
*@param null|int $Id
     */
    public function __construct($Id = null)
    {

        if ($Id) {
            $this->tblFile = Storage::useService()->getFileById($Id);
        }
        $this->loadFile();
    }

    /**
     * @deprecated
     */
    public function loadFile()
    {

        if ($this->tblFile) {
            $this->setName($this->tblFile->getName());
            $this->setDescription($this->tblFile->getDescription());
            $this->setFileName($this->tblFile->getFileName());
            $this->setFileExtension($this->tblFile->getFileExtension());
            $this->setFileType($this->tblFile->getFileType());
            $this->setFileContent(stream_get_contents($this->tblFile->getFileContent()));
            $this->setFileSize($this->tblFile->getFileSize());

            $this->Container = new Temporary('SPHERE-Database', $this->getFileExtension());
            $this->Container->setName($this->getName());
            $this->Container->setDescription($this->getDescription());
            $this->Container->setFileName($this->getFileName());
            $this->Container->setFileExtension($this->getFileExtension());
            $this->Container->setFileType($this->getFileType());
            $this->Container->setFileContent($this->getFileContent());
            $this->Container->saveFile();

            $this->setFileLocation($this->Container->getFileLocation());
        } else {
            $this->Container = new Temporary();
        }
    }

    /**
     * @deprecated
     * @return bool
     */
    public function saveFile()
    {

        $this->Container->setName($this->getName());
        $this->Container->setDescription($this->getDescription());
        $this->Container->setFileName($this->getFileName());
        $this->Container->setFileExtension($this->getFileExtension());
        $this->Container->setFileType($this->getFileType());
        $this->Container->setFileContent($this->getFileContent());
        $this->Container->saveFile();

        if ($this->tblFile) {
            $Result = Storage::useService()->changeFile(
                $this->tblFile,
                $this->Container->getName(),
                $this->Container->getDescription(),
                $this->Container->getFileName(),
                $this->Container->getFileExtension(),
                $this->Container->getFileContent(),
                $this->Container->getFileType(),
                $this->Container->getFileSize()
            );
            $this->setFileSize($this->Container->getFileSize());
            return $Result;
        } else {
            if (( $Entity = Storage::useService()->insertFile(
                $this->Container->getName(),
                $this->Container->getDescription(),
                $this->Container->getFileName(),
                $this->Container->getFileExtension(),
                $this->Container->getFileContent(),
                $this->Container->getFileType(),
                $this->Container->getFileSize()
            ) )
            ) {
                $this->tblFile = $Entity;
                $this->loadFile();
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @deprecated
     * @return null|Temporary
     */
    public function getFile()
    {

        return $this->Container;
    }
}
