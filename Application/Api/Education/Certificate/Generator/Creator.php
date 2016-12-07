<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Creator
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator
 */
class Creator extends Extension
{

    /**
     * @param null $PrepareId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function createPdf($PrepareId = null, $PersonId = null)
    {

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                        $Certificate = new $CertificateClass(false);

                        // get Content
                        $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);

                        $File = $this->buildDummyFile($Certificate, $Content);

                        $FileName = "Zeugnis " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

                        // Revisionssicher speichern
                        if (($tblDivision = $tblPrepare->getServiceTblDivision()) && !$tblPrepareStudent->isPrinted()) {
                            if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivision, $Certificate,
                                $File)
                            ) {
                                Prepare::useService()->updatePrepareStudentSetPrinted($tblPrepareStudent);
                            }
                        }

                        return $this->buildDownloadFile($File, $FileName);
                    }
                }
            }

        }

        return new Stage('Zeugnis', 'Nicht gefunden');
    }

    /**
     * @param Certificate $Certificate
     * @param array $Data
     *
     * @return FilePointer
     */
    private function buildDummyFile(Certificate $Certificate, $Data = array())
    {

        $tblYear = isset($Data['Division']['Data']['Year']) ? $Data['Division']['Data']['Year'] : '';
        $personName = '';
        if (isset($Data['Person']['Data']['Name']['First']) && isset($Data['Person']['Data']['Name']['Last'])) {
            $personName = $Data['Person']['Data']['Name']['Last'] . ', ' . $Data['Person']['Data']['Name']['First'];
        }
        $Prefix = md5($tblYear . $personName . (isset($Data['Person']['Student']['Id']) ? $Data['Person']['Student']['Id'] : ''));

        // Create Tmp
        $File = Storage::createFilePointer('pdf', $Prefix);
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($File->getFileLocation());
        $Document->setContent($Certificate->createCertificate($Data));
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    /**
     * @param FilePointer $File
     * @param string $FileName
     *
     * @return string
     */
    private function buildDownloadFile(FilePointer $File, $FileName = '')
    {

        return FileSystem::getDownload(
            $File->getRealPath(),
            $FileName ? $FileName : "Zeugnis-Test-" . date("Y-m-d H:i:s") . ".pdf"
        )->__toString();
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param string $Name
     *
     * @return Stage|string
     */
    public function previewPdf($PrepareId = null, $PersonId = null, $Name = 'Zeugnis Muster')
    {

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                        $Certificate = new $CertificateClass();

                        // get Content
                        $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);

                        $File = $this->buildDummyFile($Certificate, $Content);

                        $FileName = $Name . " " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

                        return $this->buildDownloadFile($File, $FileName);
                    }
                }
            }

        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param null $FileId
     *
     * @return Stage|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function downloadPdf($FileId = null)
    {

        if (($tblFile = Storage::useService()->getFileById($FileId))) {

            $File = Storage::createFilePointer('pdf');
            $File->setFileContent(stream_get_contents($tblFile->getTblBinary()->getBinaryBlob()));
            $File->saveFile();

            return FileSystem::getDownload($File->getFileLocation(),
                $tblFile->getName()
                . " " . date("Y-m-d H:i:s") . ".pdf")->__toString();

        } else {

            return new Stage('Zeugnis', 'Nicht gefunden');
        }
    }

    /**
     * @param null $PrepareId
     * @param string $Name
     *
     * @return Stage|string
     */
    public function previewZip($PrepareId = null, $Name = 'Zeugnis Muster')
    {

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            $FileList = array();
            foreach ($tblStudentList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                    if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                        $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                        if (class_exists($CertificateClass)) {

                            /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                            $Certificate = new $CertificateClass();

                            // get Content
                            $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);
                            $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
                            $personLastName = str_replace('ü', 'ue', $personLastName);
                            $personLastName = str_replace('ö', 'oe', $personLastName);
                            $personLastName = str_replace('ß', 'ss', $personLastName);
                            $File = Storage::createFilePointer('pdf', $Name . '-' . $personLastName
                                . '-' . date('Y-m-d') . '--');
                            /** @var DomPdf $Document */
                            $Document = Document::getPdfDocument($File->getFileLocation());
                            $Document->setContent($Certificate->createCertificate($Content));
                            $Document->saveFile(new FileParameter($File->getFileLocation()));

                            $FileList[] = $File;
                        }
                    }
                }
            }

            if (!empty($FileList)) {
                $ZipFile = new FilePointer('zip');
                $ZipFile->saveFile();

                $ZipArchive = $this->getPacker($ZipFile->getRealPath());
                /** @var FilePointer $File */
                foreach ($FileList as $File) {
                    $ZipArchive->compactFile(
                        new \MOC\V\Component\Packer\Component\Parameter\Repository\FileParameter(
                            $File->getRealPath()
                        )
                        , false);
                }

                return FileSystem::getDownload(
                    $ZipFile->getRealPath(),
                    $Name . '-' . $tblDivision->getDisplayName() . '-' . date("Y-m-d H:i:s") . ".zip"
                )->__toString();
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }
}
