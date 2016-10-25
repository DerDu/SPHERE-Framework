<?php
namespace SPHERE\Application\Document\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblFile")
 * @Cache(usage="READ_ONLY")
 */
class TblFile extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_TBL_DIRECTORY = 'tblDirectory';
    const ATTR_TBL_FILE_TYPE = 'tblFileType';

    /**
     * @Column(type="bigint")
     */
    protected $tblFileType;
    /**
     * @Column(type="bigint")
     */
    protected $tblDirectory;
    /**
     * @Column(type="bigint")
     */
    protected $tblBinary;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
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
     * @param null|TblDirectory $tblDirectory
     */
    public function setTblDirectory(TblDirectory $tblDirectory = null)
    {

        $this->tblDirectory = ( null === $tblDirectory ? null : $tblDirectory->getId() );
    }

    /**
     * @return bool
     */
    public function isLocked()
    {

        return (bool)$this->IsLocked;
    }

    /**
     * @param bool $IsLocked
     */
    public function setLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return FilePointer
     */
    public function getFilePointer()
    {
        return $this->getDummyFile();
    }

    /**
     * @deprecated
     * @see getFilePointer
     * @return FilePointer
     */
    public function getDummyFile()
    {

        $FilePointer = new FilePointer($this->getTblFileType()->getExtension(), $this->getName());
        $FilePointer->setFileContent($this->getTblBinary()->getBinaryBlob());
        $FilePointer->saveFile();

        return $FilePointer;
    }

    /**
     * @return bool|TblFileType
     */
    public function getTblFileType()
    {

        if (null === $this->tblFileType) {
            return false;
        } else {
            return Storage::useService()->getFileTypeById($this->tblFileType);
        }
    }

    /**
     * @param null|TblFileType $tblFileType
     */
    public function setTblFileType(TblFileType $tblFileType = null)
    {

        $this->tblFileType = ( null === $tblFileType ? null : $tblFileType->getId() );
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return bool|TblBinary
     */
    public function getTblBinary()
    {

        if (null === $this->tblBinary) {
            return false;
        } else {
            return Storage::useService()->getBinaryById($this->tblBinary);
        }
    }

    /**
     * @param null|TblBinary $tblBinary
     */
    public function setTblBinary(TblBinary $tblBinary = null)
    {

        $this->tblBinary = ( null === $tblBinary ? null : $tblBinary->getId() );
    }
}
