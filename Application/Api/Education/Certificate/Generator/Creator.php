<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\DummyFile;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Window\Stage;

/**
 * Class Creator
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator
 */
class Creator
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
     * @param null $PrepareId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function previewPdf($PrepareId = null, $PersonId = null)
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

                        $FileName = "Zeugnis Muster " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

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
     * @return DummyFile
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
        $File = new DummyFile('pdf', $Prefix);
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($File->getFileLocation());
        $Document->setContent($Certificate->createCertificate($Data));
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    /**
     * @param DummyFile $File
     * @param string $FileName
     *
     * @return string
     */
    private function buildDownloadFile(DummyFile $File, $FileName = '')
    {

        return FileSystem::getDownload(
            $File->getRealPath(),
            $FileName ? $FileName : "Zeugnis-Test-" . date("Y-m-d H:i:s") . ".pdf"
        )->__toString();
    }
}
