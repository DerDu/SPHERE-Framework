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
 * @Table(name="tblDirectory")
 * @Cache(usage="READ_ONLY")
 */
class TblDirectory extends Element
{

    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_IS_LOCKED = 'IsLocked';
    const ATTR_TBL_DIRECTORY = 'tblDirectory';
    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;
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
     * @deprecated
     * @return string
     */
    public function getIdentifier()
    {

        return $this->Identifier;
    }

    /**
     * @deprecated
     *
*@param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = $Identifier;
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
     * Parent Directory
     *
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
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @deprecated
     * @param string $Name
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


}
