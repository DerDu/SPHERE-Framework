<?php
namespace SPHERE\Application\Document\Explorer\Storage\Service;

use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblFile;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * @deprecated
 * Class Data
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Service
 */
class Data extends AbstractData
{

    /**
     * @deprecated
     * @return void
     */
    public function setupDatabaseContent()
    {

        $this->createDirectory(
            'Zeugnisse', 'enthält (revisionssicher) alle erzeugten Zeugnisse',
            null, true, 'GRADUATION_CERTIFICATE'
        );
    }

    /**
     * @deprecated
     *
*@param string       $Name
     * @param string       $Description
     * @param TblDirectory $tblDirectoryParent
     * @param bool         $IsLocked
     * @param string       $Identifier
     *
     * @return TblDirectory
     */
    public function createDirectory(
        $Name,
        $Description,
        TblDirectory $tblDirectoryParent = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblDirectory')->findOneBy(array(
                TblDirectory::ATTR_IS_LOCKED     => $IsLocked,
                TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectoryParent ? $tblDirectoryParent->getId() : null ),
                TblDirectory::ATTR_NAME          => $Name
            ));
        } else {
            $Entity = $Manager->getEntity('TblDirectory')->findOneBy(array(
                TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectoryParent ? $tblDirectoryParent->getId() : null ),
                TblDirectory::ATTR_NAME          => $Name
            ));
        }

        if (null === $Entity) {
            $Entity = new TblDirectory();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTblDirectory($tblDirectoryParent);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @deprecated
     *
*@param int $Id
     *
     * @return false|TblFile
     */
    public function getFileById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblFile', $Id);
        return ( $Entity ? $Entity : false );
    }

    /**
     * @deprecated
     * @return false|TblFile[]
     */
    public function getFileAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblFile')->findAll();
        return ( !empty( $EntityList ) ? $EntityList : false );
    }

    /**
     * @deprecated
     *
*@param int $Id
     *
     * @return false|TblDirectory
     */
    public function getDirectoryById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblDirectory', $Id);
        return ( $Entity ? $Entity : false );
    }

    /**
     * @deprecated
     * @param null|TblDirectory $tblDirectory
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByParent(TblDirectory $tblDirectory = null)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDirectory')->findBy(array(
            TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectory ? $tblDirectory->getId() : null )
        ));
        return ( !empty( $EntityList ) ? $EntityList : false );
    }

    /**
     * @deprecated
     * @return false|TblDirectory[]
     */
    public function getDirectoryAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDirectory')->findAll();
        return ( !empty( $EntityList ) ? $EntityList : false );
    }

    /**
     * @deprecated
     * @param string       $Name
     * @param string       $Description
     * @param string       $FileName
     * @param string       $FileExtension
     * @param string       $FileContent
     * @param string       $FileType
     * @param int          $FileSize
     * @param TblDirectory $tblDirectory
     * @param bool         $IsLocked
     *
     * @return TblFile
     */
    public function createFile(
        $Name,
        $Description,
        $FileName,
        $FileExtension,
        $FileContent,
        $FileType,
        $FileSize,
        TblDirectory $tblDirectory = null,
        $IsLocked = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblFile();
        $Entity->setTblDirectory(( $tblDirectory ? $tblDirectory : null ));
        $Entity->setName($Name);
        $Entity->setDescription($Description);
        $Entity->setFileName($FileName);
        $Entity->setFileExtension($FileExtension);
        $Entity->setFileContent($FileContent);
        $Entity->setFileType($FileType);
        $Entity->setFileSize($FileSize);
        $Entity->setLocked($IsLocked);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @deprecated
     * @param TblFile      $tblFile
     * @param string       $Name
     * @param string       $Description
     * @param string       $FileName
     * @param string       $FileExtension
     * @param string       $FileContent
     * @param string       $FileType
     * @param int          $FileSize
     * @param TblDirectory $tblDirectory
     * @param bool         $IsLocked
     *
     * @return bool
     */
    public function updateFile(
        TblFile $tblFile,
        $Name,
        $Description,
        $FileName,
        $FileExtension,
        $FileContent,
        $FileType,
        $FileSize,
        TblDirectory $tblDirectory = null,
        $IsLocked = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblFile $Entity */
        $Entity = $Manager->getEntityById('TblFile', $tblFile->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setTblDirectory(( $tblDirectory ? $tblDirectory : null ));
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setFileName($FileName);
            $Entity->setFileExtension($FileExtension);
            $Entity->setFileContent($FileContent);
            $Entity->setFileType($FileType);
            $Entity->setFileSize($FileSize);
            $Entity->setLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @deprecated
     * @param TblDirectory $tblDirectory
     * @param string       $Name
     * @param string       $Description
     * @param TblDirectory $tblDirectoryParent
     * @param bool         $IsLocked
     * @param string       $Identifier
     *
     * @return bool
     */
    public function updateDirectory(
        TblDirectory $tblDirectory,
        $Name,
        $Description,
        TblDirectory $tblDirectoryParent = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblDirectory $Entity */
        $Entity = $Manager->getEntityById('TblDirectory', $tblDirectory->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTblDirectory($tblDirectoryParent);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
