<?php
namespace SPHERE\Application\Document\Explorer\Storage;

use SPHERE\Application\Document\Explorer\Storage\Service\Data;
use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Explorer\Storage\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * @deprecated
 * Class Service
 *
 * @package SPHERE\Application\Document\Explorer\Storage
 */
class Service extends AbstractService
{

    /**
     * @deprecated
     *
*@param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
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

        return (new Data($this->getBinding()))->getFileById($Id);
    }

    /**
     * @deprecated
     * @return false|TblFile[]
     */
    public function getFileAll()
    {

        return (new Data($this->getBinding()))->getFileAll();
    }

    /**
     * @deprecated
     *
*@param string       $Name
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
    public function insertFile(
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

        return (new Data($this->getBinding()))->createFile(
            $Name, $Description, $FileName, $FileExtension, $FileContent, $FileType, $FileSize, $tblDirectory, $IsLocked
        );
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
    public function changeFile(
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

        return (new Data($this->getBinding()))->updateFile(
            $tblFile, $Name, $Description, $FileName, $FileExtension, $FileContent, $FileType, $FileSize, $tblDirectory,
            $IsLocked
        );
    }

    /**
     * @deprecated
     * @param int $Id
     *
     * @return false|TblDirectory
     */
    public function getDirectoryById($Id)
    {

        return (new Data($this->getBinding()))->getDirectoryById($Id);
    }

    /**
     * @deprecated
     * @param null|TblDirectory $tblDirectory
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByParent(TblDirectory $tblDirectory = null)
    {

        return (new Data($this->getBinding()))->getDirectoryAllByParent($tblDirectory);
    }

    /**
     * @deprecated
     * @return false|TblDirectory[]
     */
    public function getDirectoryAll()
    {

        return (new Data($this->getBinding()))->getDirectoryAll();
    }

    /**
     * @deprecated
     * @param string       $Name
     * @param string       $Description
     * @param TblDirectory $tblDirectoryParent
     * @param bool         $IsLocked
     * @param string       $Identifier
     *
     * @return TblFile
     */
    public function insertDirectory(
        $Name,
        $Description,
        TblDirectory $tblDirectoryParent = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        return (new Data($this->getBinding()))->createDirectory(
            $Name, $Description, $tblDirectoryParent, $IsLocked, $Identifier
        );
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
    public function changeDirectory(
        TblDirectory $tblDirectory,
        $Name,
        $Description,
        TblDirectory $tblDirectoryParent = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        return (new Data($this->getBinding()))->updateDirectory(
            $tblDirectory, $Name, $Description, $tblDirectoryParent, $IsLocked, $Identifier
        );
    }
}
