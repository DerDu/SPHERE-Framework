<?php
namespace SPHERE\Application\Document\Storage\Service;

use SPHERE\Application\Document\Storage\Service\Entity\TblBinary;
use SPHERE\Application\Document\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Storage\Service\Entity\TblFileCategory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFileType;
use SPHERE\Application\Document\Storage\Service\Entity\TblPartition;
use SPHERE\Application\Document\Storage\Service\Entity\TblReferenceType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Service
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {
        $this->createPartition(
            'Import-Dateien', 'temporäre Speicherung von Dateien für Importe', true,
            TblPartition::IDENTIFIER_IMPORT_STORAGE
        );
        $this->createPartition(
            'Zeugnisse', 'revisionssichere Archivierung', true, TblPartition::IDENTIFIER_CERTIFICATE_STORAGE
        );

        $FileCategoryDOCUMENT = $this->createFileCategory('Dokumente', TblFileCategory::CATEGORY_DOCUMENT);
        $FileCategoryIMAGE = $this->createFileCategory('Bilder', TblFileCategory::CATEGORY_IMAGE);

        // Documents
        $this->createFileType('PDF-Datei', 'pdf', 'application/pdf', $FileCategoryDOCUMENT);
        $this->createFileType('CSV-Datei', 'csv', 'text/comma-separated-values', $FileCategoryDOCUMENT);
        $this->createFileType('CSV-Datei', 'csv', 'text/csv', $FileCategoryDOCUMENT);
        $this->createFileType('CSV-Datei', 'csv', 'text/plain', $FileCategoryDOCUMENT);
        // Images
        $this->createFileType('PNG-Datei', 'png', 'image/png', $FileCategoryIMAGE);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $IsLocked
     * @param string $Identifier
     *
     * @return TblPartition
     */
    public function createPartition($Name, $Description = '', $IsLocked = false, $Identifier = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblPartition')->findOneBy(array(
                TblPartition::ATTR_IS_LOCKED  => $IsLocked,
                TblPartition::ATTR_IDENTIFIER => $Identifier,
                TblPartition::ENTITY_REMOVE   => null
            ));
        } else {
            $Entity = $Manager->getEntity('TblPartition')->findOneBy(array(
                TblPartition::ATTR_NAME     => $Name,
                TblPartition::ENTITY_REMOVE => null
            ));
        }

        if (null === $Entity) {
            $Entity = new TblPartition();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Identifier
     *
     * @return TblFileCategory
     */
    public function createFileCategory($Name, $Identifier)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFileCategory')->findOneBy(array(
            TblFileCategory::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));

        if (null === $Entity) {
            $Entity = new TblFileCategory();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string          $Name
     * @param string          $Extension
     * @param string          $MimeType
     * @param TblFileCategory $tblFileCategory
     *
     * @return TblFileType
     */
    public function createFileType($Name, $Extension, $MimeType, TblFileCategory $tblFileCategory)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFileType')->findOneBy(array(
            TblFileType::ATTR_EXTENSION => $Extension,
            TblFileType::ATTR_MIME_TYPE => $MimeType
        ));

        if (null === $Entity) {
            $Entity = new TblFileType();
            $Entity->setName($Name);
            $Entity->setExtension($Extension);
            $Entity->setMimeType($MimeType);
            $Entity->setTblFileCategory($tblFileCategory);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPartition $tblPartition
     * @param string       $Name
     * @param string       $Description
     * @param TblDirectory $tblDirectory
     * @param bool         $IsLocked
     * @param string       $Identifier
     *
     * @return TblDirectory
     */
    public function createDirectory(
        TblPartition $tblPartition,
        $Name,
        $Description,
        TblDirectory $tblDirectory = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblDirectory')->findOneBy(array(
                TblDirectory::ATTR_IS_LOCKED     => $IsLocked,
                TblDirectory::ATTR_IDENTIFIER    => strtoupper($Identifier),
                TblDirectory::ATTR_TBL_PARTITION => $tblPartition->getId(),
                TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectory ? $tblDirectory->getId() : null ),
                TblDirectory::ENTITY_REMOVE      => null
            ));
        } else {
            $Entity = $Manager->getEntity('TblDirectory')->findOneBy(array(
                TblDirectory::ATTR_NAME          => $Name,
                TblDirectory::ATTR_TBL_PARTITION => $tblPartition->getId(),
                TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectory ? $tblDirectory->getId() : null ),
                TblDirectory::ENTITY_REMOVE      => null
            ));
        }

        if (null === $Entity) {
            $Entity = new TblDirectory();
            $Entity->setTblPartition($tblPartition);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTblDirectory($tblDirectory);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier(strtoupper($Identifier));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return false|TblPartition
     */
    public function getPartitionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblPartition
     */
    public function getPartitionByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition', array(
            TblPartition::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
    }

    /**
     * @return false|TblPartition[]
     */
    public function getPartitionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition');
    }

    /**
     * @param int $Id
     *
     * @return false|TblBinary
     */
    public function getBinaryById($Id)
    {

        // MUST NOT BE CACHED! Contains Resource-Id (un-cacheable)
        if (( $Entity = $this->getConnection()->getEntityManager()->getEntity('TblBinary')->findOneBy(array(
            TblBinary::ENTITY_ID     => $Id,
            TblBinary::ENTITY_REMOVE => null
        )) )
        ) {
            return $Entity;
        }
        return false;
    }

    /**
     * @return false|TblBinary[]
     */
    public function getBinaryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBinary');
    }

    /**
     * @param int $Id
     *
     * @return false|TblFile
     */
    public function getFileById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile', $Id);
    }

    /**
     * @return false|TblFile[]
     */
    public function getFileAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile');
    }

    /**
     * @param null|TblDirectory $tblDirectory
     *
     * @return false|TblFile[]
     */
    public function getFileAllByDirectory(TblDirectory $tblDirectory = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile',
            array(
                TblFile::ATTR_TBL_DIRECTORY => ( $tblDirectory ? $tblDirectory->getId() : null )
            ));
    }

    /**
     * @param int $Id
     *
     * @return false|TblDirectory
     */
    public function getDirectoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblDirectory
     */
    public function getDirectoryByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory', array(
            TblDirectory::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
    }

    /**
     * @param null|TblPartition $tblPartition
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByPartition(TblPartition $tblPartition = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory',
            array(
                TblDirectory::ATTR_TBL_PARTITION => ( $tblPartition ? $tblPartition->getId() : null )
            ));
    }

    /**
     * @param null|TblDirectory $tblDirectory
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByParent(TblDirectory $tblDirectory = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory',
            array(
                TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectory ? $tblDirectory->getId() : null )
            ));
    }

    /**
     * @return false|TblDirectory[]
     */
    public function getDirectoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory');
    }

    /**
     * @param string $BinaryBlob
     *
     * @return TblBinary
     */
    public function createBinary($BinaryBlob)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $New = new TblBinary();
        $New->setBinaryBlob($BinaryBlob);
        $Hash = $New->getHash();

        $Entity = $Manager->getEntity('TblBinary')->findOneBy(array(
            TblBinary::ATTR_HASH     => $Hash,
            TblBinary::ENTITY_REMOVE => null
        ));

        if (null === $Entity) {
            $Entity = $New;
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBinary    $tblBinary
     * @param TblDirectory $tblDirectory
     * @param TblFileType  $tblFileType
     * @param string       $Name
     * @param string       $Description
     * @param bool         $IsLocked
     *
     * @return TblFile
     */
    public function createFile(
        TblBinary $tblBinary,
        TblDirectory $tblDirectory,
        TblFileType $tblFileType,
        $Name,
        $Description = '',
        $IsLocked = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFile')->findOneBy(array(
            TblFile::ATTR_TBL_DIRECTORY => $tblDirectory->getId(),
            TblFile::ATTR_TBL_FILE_TYPE => $tblFileType->getId(),
            TblFile::ATTR_NAME          => $Name,
            TblFile::ENTITY_REMOVE      => null
        ));

        if (null === $Entity) {
            $Entity = new TblFile();
            $Entity->setTblBinary($tblBinary);
            $Entity->setTblDirectory($tblDirectory);
            $Entity->setTblFileType($tblFileType);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $MimeType
     *
     * @return false|TblFileType
     */
    public function getFileTypeByMimeType($MimeType)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFileType', array(
            TblFileType::ATTR_MIME_TYPE => $MimeType
        ));
    }

    /**
     * @param int $Id
     *
     * @return false|TblFileType
     */
    public function getFileTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFileType', $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblFileCategory
     */
    public function getFileCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFileCategory',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return false|TblReferenceType
     */
    public function getReferenceTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblReferenceType',
            $Id);
    }
}
