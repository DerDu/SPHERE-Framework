<?php
namespace SPHERE\Application\Transfer\Import\Indiware\Lectureship;

use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Service\Entity\TblFileCategory;
use SPHERE\Application\Document\Storage\Service\Entity\TblPartition;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Indiware\Lectureship
 */
class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function uploadLectureshipFile(
        IFormInterface $Form = null,
        UploadedFile $File = null
    )
    {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if ($File->getError()) {
            $Form->setError('File', 'Fehler');
        } else {

            /**
             * Prepare
             */
            $Dummy = new FilePointer($File->getClientOriginalExtension(), $File->getClientOriginalName());
            $Dummy->saveFile();
            $Upload = $File->move(dirname($Dummy->getRealPath()), basename($Dummy->getFileLocation()));
            $Dummy->loadFile();

            // Allowed MimeType ?
            if (($tblMimeType = Storage::useService()->getFileTypeByMimeType($Dummy->getMimeType()))) {
                if ( // Document && MimeType-Extension ?
                    $tblMimeType->getTblFileCategory()->getIdentifier() == TblFileCategory::CATEGORY_DOCUMENT
                    && $tblMimeType->getExtension() == $Upload->getExtension()
                ) {
                    // Correct MimeType (manual, sometimes csv => text/plain)
                    $tblFileType = Storage::useService()->getFileTypeByMimeType('text/csv');
                    /**
                     * Save
                     */
                    $tblPartition = Storage::useService()->getPartitionByIdentifier(TblPartition::IDENTIFIER_IMPORT_STORAGE);
                    $tblDirectory = Storage::useService()->createDirectory(
                        $tblPartition,
                        Account::useService()->getAccountBySession()->getUsername(),
                        'Hochgeladene Dateien für Import'
                    );
                    $tblDirectory = Storage::useService()->createDirectory(
                        $tblPartition,
                        'INDIWARE-LECTURESHIP',
                        'Import-Typ',
                        $tblDirectory
                    );
                    $tblBinary = Storage::useService()->createBinary($Dummy->getFileContent());
                    if (Storage::useService()->createFile($tblBinary, $tblDirectory, $tblFileType,
                        $File->getClientOriginalName(), date('d.m.Y H:i:s'))
                    ) {
                        return new Success('Die Datei wurde erfolgreich hochgeladen.',
                            new \SPHERE\Common\Frontend\Icon\Repository\Success()
                        )
                        . new Redirect('/Transfer/Import/Indiware/Lectureship', Redirect::TIMEOUT_SUCCESS);
                    } else {
                        $Form->setError('File', 'Fehler beim Speichern der Datei');
                    }

                } else {
                    $Form->setError('File', 'Falsches Dateiformat');
                }
            } else {
                $Form->setError('File', 'Falsches Dateiformat');
            }
        }
        return $Form;
    }
}