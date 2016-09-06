<?php
namespace SPHERE\Application\Document\Explorer\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\System\Database\Fitting\Element;

/**
 * @deprecated
 * @Entity()
 * @Table(name="tblFile")
 * @Cache(usage="READ_ONLY")
 */
class TblFile extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_TBL_DIRECTORY = 'tblDirectory';

    /**
     * @Column(type="bigint")
     */
    protected $tblDirectory;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;
    /**
     * @Column(type="string")
     */
    protected $FileName;
    /**
     * @Column(type="string")
     */
    protected $FileExtension;
    /**
     * @Column(type="blob")
     */
    protected $FileContent;
    /**
     * @Column(type="string")
     */
    protected $FileType;
    /**
     * @Column(type="integer")
     */
    protected $FileSize;

    /**
     * @deprecated
     * @return bool|TblDirectory
     */
    public function getTblDirectory()
    {

        if (null === $this->tblDirectory) {
            return false;
        } else {
            return Storage::useService()->getDirectoryById($this->tblDirectory);
        }
    }

    /**
     * @deprecated
     *
*@param null|TblDirectory $tblDirectory
     */
    public function setTblDirectory(TblDirectory $tblDirectory = null)
    {

        $this->tblDirectory = ( null === $tblDirectory ? null : $tblDirectory->getId() );
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isLocked()
    {

        return (bool)$this->IsLocked;
    }

    /**
     * @deprecated
     *
*@param bool $IsLocked
     */
    public function setLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getName()
    {

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
     * @param string $Description
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

        return $this->FileName;
    }

    /**
     * @deprecated
     * @param string $FileName
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
     * @return string|resource
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

        $this->FileContent = $FileContent;
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
}
