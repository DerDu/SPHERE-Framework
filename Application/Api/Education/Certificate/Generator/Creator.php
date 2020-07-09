<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\MultiCertificate;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Window\Display;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\RedirectScript;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\PdfMerge;

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

                        $tblDivision = $tblPrepare->getServiceTblDivision();
                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                        $Certificate = new $CertificateClass($tblDivision ? $tblDivision : null, $tblPrepare, false);

                        // get Content
                        $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);
                        $personId = $tblPerson->getId();
                        if (isset($Content['P' . $personId]['Grade'])) {
                            $Certificate->setGrade($Content['P' . $personId]['Grade']);
                        }
                        if (isset($Content['P' . $personId]['AdditionalGrade'])) {
                            $Certificate->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
                        }

                        $File = $this->buildDummyFile($Certificate, $tblPerson, $Content);

                        $FileName = "Zeugnis " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

                        // Revisionssicher speichern
                        if (($tblDivision = $tblPrepare->getServiceTblDivision()) && !$tblPrepareStudent->isPrinted()) {
                            if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivision, $Certificate,
                                $File, $tblPrepare)
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
     * @param TblPerson $tblPerson
     * @param array $Data
     * @return FilePointer
     */
    private function buildDummyFile(Certificate $Certificate, TblPerson $tblPerson, $Data = array())
    {

        $tblYear = isset($Data['Division']['Data']['Year']) ? $Data['Division']['Data']['Year'] : '';
        $personName = '';
        if (isset($Data['Person']['Data']['Name']['First']) && isset($Data['Person']['Data']['Name']['Last'])) {
            $personName = $Data['Person']['Data']['Name']['Last'] . ', ' . $Data['Person']['Data']['Name']['First'];
        }
        $Prefix = md5($tblYear . $personName . (isset($Data['Person']['Student']['Id']) ? $Data['Person']['Student']['Id'] : ''));

        // Create Tmp
        $File = Storage::createFilePointer('pdf', $Prefix);
        $pageList[$tblPerson->getId()] = $Certificate->buildPages($tblPerson);
        $bridge = $Certificate->createCertificate($Data, $pageList);
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($File->getFileLocation());
        $Document->setContent($bridge);
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    /**
     * @param FilePointer $File
     * @param string $FileName
     *
     * @return string
     */
    private static function buildDownloadFile(FilePointer $File, $FileName = '')
    {

        return FileSystem::getStream(
            $File->getRealPath(),
            $FileName ? $FileName : "Zeugnis-Test-" . date("Y-m-d H:i:s") . ".pdf"
        )->__toString();
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public function previewPdf($PrepareId = null, $PersonId = null, $Name = 'Zeugnis Muster', $Redirect = true)
    {

        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/Preview', array(
                'PrepareId' => $PrepareId,
                'PersonId' => $PersonId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {

            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        $tblDivision = $tblPrepare->getServiceTblDivision();
                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                        $Certificate = new $CertificateClass($tblDivision ? $tblDivision : null, $tblPrepare);

                        // get Content
                        $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);
                        $personId = $tblPerson->getId();
                        if (isset($Content['P' . $personId]['Grade'])) {
                            $Certificate->setGrade($Content['P' . $personId]['Grade']);
                        }
                        if (isset($Content['P' . $personId]['AdditionalGrade'])) {
                            $Certificate->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
                        }

                        $File = $this->buildDummyFile($Certificate, $tblPerson, $Content);

                        $FileName = $Name . " " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

                        return $this->buildDownloadFile($File, $FileName);
                    }
                }
            }

        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param null $LeaveStudentId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return Display|Stage|string
     */
    public function previewLeavePdf($LeaveStudentId = null, $Name = 'Zeugnis Muster', $Redirect = true)
    {

        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/PreviewLeave', array(
                'LeaveStudentId' => $LeaveStudentId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($LeaveStudentId))) {
            if (($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();

                if (class_exists($CertificateClass)) {
                    $tblDivision = $tblLeaveStudent->getServiceTblDivision();
                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = new $CertificateClass($tblDivision ? $tblDivision : null);

                    // get Content
                    $Content = Prepare::useService()->getLeaveCertificateContent($tblLeaveStudent);
                    $tblPerson = $tblLeaveStudent->getServiceTblPerson();
                    $personId = $tblPerson ? $tblPerson->getId() : 0;
                    if (isset($Content['P' . $personId]['Grade'])) {
                        $Certificate->setGrade($Content['P' . $personId]['Grade']);
                    }

                    $File = $this->buildDummyFile($Certificate, $tblPerson, $Content);

                    $FileName = $Name . " " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

                    return $this->buildDownloadFile($File, $FileName);
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

            return FileSystem::getStream($File->getFileLocation(),
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
                            $Certificate = new $CertificateClass($tblDivision, $tblPrepare);

                            // get Content
                            $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);
                            $personId = $tblPerson->getId();
                            if (isset($Content['P' . $personId]['Grade'])) {
                                $Certificate->setGrade($Content['P' . $personId]['Grade']);
                            }
                            if (isset($Content['P' . $personId]['AdditionalGrade'])) {
                                $Certificate->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
                            }

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

    /**
     * Herunterladen in einem Zip-Ordner und revisionssicher speichern
     *
     * @param null $PrepareId
     * @param string $Name
     *
     * @return Stage|string
     */
    public function downloadZip($PrepareId = null, $Name = 'Zeugnis')
    {

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            $FileList = array();
            foreach ($tblStudentList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && $tblPrepareStudent->isApproved()
                    && !$tblPrepareStudent->isPrinted()
                ) {
                    if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                        $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                        if (class_exists($CertificateClass)) {

                            /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                            $Certificate = new $CertificateClass($tblDivision, $tblPrepare, false);

                            // get Content
                            $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);
                            $personId = $tblPerson->getId();
                            if (isset($Content['P' . $personId]['Grade'])) {
                                $Certificate->setGrade($Content['P' . $personId]['Grade']);
                            }
                            if (isset($Content['P' . $personId]['AdditionalGrade'])) {
                                $Certificate->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
                            }

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

                            // Revisionssicher speichern
                            if (($tblDivision = $tblPrepare->getServiceTblDivision()) && !$tblPrepareStudent->isPrinted()) {
                                if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivision,
                                    $Certificate,
                                    $File, $tblPrepare)
                                ) {
                                    Prepare::useService()->updatePrepareStudentSetPrinted($tblPrepareStudent);
                                }
                            }

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
            } else {
                return new Stage($Name, 'Keine weiteren Zeungnisse zum Druck bereit.')
                    . new Redirect('/Education/Certificate/PrintCertificate');
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param null $PrepareId
     * @param string $Name
     *
     * @return Stage|string
     */
    public function downloadHistoryZip($PrepareId = null, $Name = 'Zeugnis')
    {

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))
        ) {
            $FileList = array();
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())) {
                    $tblFileList = Storage::useService()->getCertificateRevisionFileAllByPerson($tblPerson);
                    if ($tblFileList) {
                        foreach ($tblFileList as $tblFile) {
                            $name = explode(' - ', $tblFile->getName());
                            if (count($name) >= 4 && $name[3] == $tblPrepare->getId()) {
                                $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
                                $personLastName = str_replace('ü', 'ue', $personLastName);
                                $personLastName = str_replace('ö', 'oe', $personLastName);
                                $personLastName = str_replace('ß', 'ss', $personLastName);
                                $File = Storage::createFilePointer('pdf', $Name . '-' . $personLastName
                                    . '-' . date('Y-m-d') . '--');
                                $File->setFileContent(stream_get_contents($tblFile->getTblBinary()->getBinaryBlob()));
                                $File->saveFile();

                                $FileList[] = $File;
                            }
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
            } else {
                return new Stage($Name, 'Keine weiteren Zeungnisse zum Druck bereit.')
                    . new Redirect('/Education/Certificate/PrintCertificate');
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }


    private static function buildMultiDummyFile($Data = array(), $pageList = array())
    {

        ini_set('memory_limit', '1G');

        $MultiCertificate = new MultiCertificate();

        // Create Tmp
        $File = Storage::createFilePointer('pdf');
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($File->getFileLocation());
        $Content = $MultiCertificate->createCertificate($Data, $pageList);
        $Document->setContent($Content);
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return string
     */
    public static function previewMultiPdf($PrepareId = null, $GroupId = null, $Name = 'Zeugnis', $Redirect = true)
    {

        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/PreviewMultiPdf', array(
                'PrepareId' => $PrepareId,
                'GroupId' => $GroupId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        $tblPrepareList = false;
        $tblGroup = false;
        $description = '';
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))) {
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                    $description = $tblDivision->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);
                }
            }
        }

        // Fieldpointer auf dem der Merge durchgeführt wird, (download)
        $MergeFile = Storage::createFilePointer('pdf');
        $PdfMerger = new PdfMerge();
        $FileList = array();

        if ($tblPrepareList) {
            foreach ($tblPrepareList as $tblPrepareItem) {
                if (($tblDivision = $tblPrepareItem->getServiceTblDivision())
                    && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
                ) {
                    foreach ($tblStudentList as $tblPerson) {
                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem,
                                    $tblPerson))
                                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                            ) {
                                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\'
                                    . $tblCertificate->getCertificate();
                                if (class_exists($CertificateClass)) {

                                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                                    $Certificate = new $CertificateClass($tblDivision, $tblPrepare);

                                    // get Content
                                    $Data = Prepare::useService()->getCertificateContent($tblPrepareItem,
                                        $tblPerson);
                                    $personId = $tblPerson->getId();
                                    if (isset($Data['P' . $personId]['Grade'])) {
                                        $Certificate->setGrade($Data['P' . $personId]['Grade']);
                                    }
                                    if (isset($Data['P' . $personId]['AdditionalGrade'])) {
                                        $Certificate->setAdditionalGrade($Data['P' . $personId]['AdditionalGrade']);
                                    }

                                    $page = $Certificate->buildPages($tblPerson);

                                    $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
                                    $personLastName = str_replace('ü', 'ue', $personLastName);
                                    $personLastName = str_replace('ö', 'oe', $personLastName);
                                    $personLastName = str_replace('ß', 'ss', $personLastName);
                                    $File = Storage::createFilePointer('pdf', $Name . '-' . $personLastName
                                        . '-' . date('Y-m-d') . '--');
                                    /** @var DomPdf $Document */
                                    $Document = Document::getPdfDocument($File->getFileLocation());
                                    $Content = $Certificate->createCertificate($Data, array(0 => $page));
                                    $Document->setContent($Content);
                                    $Document->saveFile(new FileParameter($File->getFileLocation()));

                                    // hinzufügen für das mergen
                                    $PdfMerger->addPdf($File);
                                    // speichern der Files zum nachträglichem bereinigen
                                    $FileList[] = $File;
                                }
                            }
                        }
                    }
                }
            }

            // mergen aller hinzugefügten PDF-Datein
            $PdfMerger->mergePdf($MergeFile);
            if(!empty($FileList)){
                // aufräumen der Temp-Files
                /** @var FilePointer $File */
                foreach($FileList as $File){
                    $File->setDestruct();
                }
            }
        }

        if (!empty($FileList) && $tblPrepare) {
            $FileName = $Name . ' ' . ($description ? $description : '-') . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($MergeFile, $FileName);
        }

        return "Keine Zeugnisse vorhanden!";
    }

    /**
     * @param null $DivisionId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return Display|string
     */
    public static function previewMultiLeavePdf($DivisionId = null, $Name = 'Abgangszeugnis', $Redirect = true)
    {

        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/PreviewMultiLeavePdf', array(
                'DivisionId' => $DivisionId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        $pageList = array();
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))
            && ($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByDivision($tblDivision))
        ) {
            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                    && ($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())
                ) {

                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                        $Certificate = new $CertificateClass($tblDivision);

                        // get Content
                        $Data = Prepare::useService()->getLeaveCertificateContent($tblLeaveStudent);
                        $tblPerson = $tblLeaveStudent->getServiceTblPerson();
                        $personId = $tblPerson ? $tblPerson->getId() : 0;
                        if (isset($Data['P' . $personId]['Grade'])) {
                            $Certificate->setGrade($Data['P' . $personId]['Grade']);
                        }

                        $page = $Certificate->buildPages($tblPerson);
                        $pageList[$tblPerson->getId()] = $page;
                    }
                }
            }

            if (!empty($pageList)) {
                $Data = Prepare::useService()->getCertificateMultiLeaveContent($tblDivision);

                $File = self::buildMultiDummyFile($Data, $pageList);
                $FileName = $Name . ' ' . $tblDivision->getDisplayName() . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($File, $FileName);
            }
        }

        return "Keine Zeugnisse vorhanden!";
    }

    /**
     * @param null $PrepareId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return string
     */
    public static function downloadMultiPdf($PrepareId = null, $Name = 'Zeugnis', $Redirect = true)
    {

        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/DownLoadMultiPdf', array(
                'PrepareId' => $PrepareId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        // Fieldpointer auf dem der Merge durchgeführt wird, (download)
        $MergeFile = Storage::createFilePointer('pdf');
        $PdfMerger = new PdfMerge();
        $FileList = array();

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            if (($tblCertificateType = $tblPrepare->getCertificateType())
                && $tblCertificateType->isAutomaticallyApproved()
            ) {
                $isAutomaticallyApproved = true;
            } else {
                $isAutomaticallyApproved = false;
            }

            foreach ($tblStudentList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && !$tblPrepareStudent->isPrinted()
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                ) {
                    $isApproved = $tblPrepareStudent->isApproved();
                    // bei automatischer Freigabe --> freigeben + kopieren der Zensuren und Fehlzeiten (optional)
                    if (!$isApproved && $isAutomaticallyApproved) {
                        Prepare::useService()->updatePrepareStudentSetApproved($tblPrepareStudent);
                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson, true);
                        $isApproved = true;
                    }

                    if ($isApproved) {
                        $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                        if (class_exists($CertificateClass)) {

                            /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                            $Certificate = new $CertificateClass($tblDivision, $tblPrepare, false);

                            // get Content
                            $Data = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);
                            $personId = $tblPerson->getId();
                            if (isset($Data['P' . $personId]['Grade'])) {
                                $Certificate->setGrade($Data['P' . $personId]['Grade']);
                            }
                            if (isset($Data['P' . $personId]['AdditionalGrade'])) {
                                $Certificate->setAdditionalGrade($Data['P' . $personId]['AdditionalGrade']);
                            }

                            $page = $Certificate->buildPages($tblPerson);

                            $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
                            $personLastName = str_replace('ü', 'ue', $personLastName);
                            $personLastName = str_replace('ö', 'oe', $personLastName);
                            $personLastName = str_replace('ß', 'ss', $personLastName);
                            $File = Storage::createFilePointer('pdf', $Name . '-' . $personLastName
                                . '-' . date('Y-m-d') . '--');
                            /** @var DomPdf $Document */
                            $Document = Document::getPdfDocument($File->getFileLocation());
                            $Content = $Certificate->createCertificate($Data, array(0 => $page));
                            $Document->setContent($Content);
                            $Document->saveFile(new FileParameter($File->getFileLocation()));

                            // Revisionssicher speichern
                            if (($tblDivision = $tblPrepare->getServiceTblDivision()) && !$tblPrepareStudent->isPrinted()) {
                                if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivision,
                                    $Certificate,
                                    $File, $tblPrepare)
                                ) {
                                    Prepare::useService()->updatePrepareStudentSetPrinted($tblPrepareStudent);
                                }
                            }

                            // hinzufügen für das mergen
                            $PdfMerger->addPdf($File);
                            // speichern der Files zum nachträglichem bereinigen
                            $FileList[] = $File;
                        }
                    }
                }
            }

            if(!empty($FileList)){
                // mergen aller hinzugefügten PDF-Datein
                $PdfMerger->mergePdf($MergeFile);

                // aufräumen der Temp-Files
                /** @var FilePointer $File */
                foreach($FileList as $File){
                    $File->setDestruct();
                }
            }

            if (!empty($FileList) && $tblPrepare) {
                $FileName = $Name . ' ' . ($tblDivision ? $tblDivision->getDisplayName() : '-') . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($MergeFile, $FileName);

            } else {

                return new Stage($Name, 'Keine weiteren Zeungnisse zum Druck bereit.')
                    . new Redirect('/Education/Certificate/PrintCertificate');
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param null $DivisionId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return Display|Stage|string
     */
    public static function downloadMultiLeavePdf($DivisionId = null, $Name = 'Abgangszeugnis', $Redirect = true)
    {

        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/DownLoadMultiLeavePdf', array(
                'DivisionId' => $DivisionId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        $pageList = array();

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            if (($tblCertificateTypeLeave = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
                && $tblCertificateTypeLeave->isAutomaticallyApproved()
            ) {
                $isAutomaticallyApproved = true;
            } else {
                $isAutomaticallyApproved = false;
            }

            if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByDivision($tblDivision))) {
                foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                    if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                        && !$tblLeaveStudent->isPrinted()
                        && ($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())
                    ) {
                        $isApproved = $tblLeaveStudent->isApproved();
                        // bei automatischer Freigabe --> freigeben + kopieren der Zensuren
                        if (!$isApproved && $isAutomaticallyApproved) {
                            Prepare::useService()->updateLeaveStudent($tblLeaveStudent, true, $tblLeaveStudent->isPrinted());
                            $isApproved = true;
                        }

                        if ($isApproved) {
                            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                            if (class_exists($CertificateClass)) {

                                /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                                $Certificate = new $CertificateClass($tblDivision, null, false);

                                // get Content
                                $Data = Prepare::useService()->getLeaveCertificateContent($tblLeaveStudent);
                                $tblPerson = $tblLeaveStudent->getServiceTblPerson();
                                $personId = $tblPerson ? $tblPerson->getId() : 0;
                                if (isset($Data['P' . $personId]['Grade'])) {
                                    $Certificate->setGrade($Data['P' . $personId]['Grade']);
                                }

                                $page = $Certificate->buildPages($tblPerson);
                                $pageList[$tblPerson->getId()] = $page;

                                $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
                                $personLastName = str_replace('ü', 'ue', $personLastName);
                                $personLastName = str_replace('ö', 'oe', $personLastName);
                                $personLastName = str_replace('ß', 'ss', $personLastName);
                                $File = Storage::createFilePointer('pdf', $Name . '-' . $personLastName
                                    . '-' . date('Y-m-d') . '--');
                                /** @var DomPdf $Document */
                                $Document = Document::getPdfDocument($File->getFileLocation());
                                $Content = $Certificate->createCertificate($Data, array(0 => $page));
                                $Document->setContent($Content);
                                $Document->saveFile(new FileParameter($File->getFileLocation()));

                                // Revisionssicher speichern
                                if (Storage::useService()->saveCertificateRevision(
                                    $tblPerson,
                                    $tblDivision,
                                    $Certificate,
                                    $File)
                                ) {
                                    Prepare::useService()->updateLeaveStudent($tblLeaveStudent, $isApproved, true);
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($pageList)) {
                $Data = Prepare::useService()->getCertificateMultiLeaveContent($tblDivision);
                $File = self::buildMultiDummyFile($Data, $pageList);
                $FileName = $Name . ' ' . ($tblDivision ? $tblDivision->getDisplayName() : '-') . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($File, $FileName);

            } else {

                return new Stage($Name, 'Keine weiteren Zeungnisse zum Druck bereit.')
                    . new Redirect('/Education/Certificate/PrintCertificate');
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param string $Route
     * @param array $parameters
     *
     * @return Display
     */
    public static function displayWaitingPage($Route, $parameters)
    {

        $Display = new Display();
        $Stage = new Stage('Dokument wird vorbereitet');
        $Stage->setContent(new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Paragraph('Dieser Vorgang kann längere Zeit in Anspruch nehmen.'),
                        (new ProgressBar(0, 100, 0, 10))->setColor(
                            ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS,
                            ProgressBar::BAR_COLOR_STRIPED
                        ),
                        new Paragraph('Bitte warten ..'),
                        "<button type=\"button\" class=\"btn btn-default\" onclick=\"window.open('', '_self', ''); window.close();\">Abbrechen</button>"
                    ), 4),
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new RedirectScript($Route, 0, $parameters)
                    )
                ),
            )))
        );
        $Display->setContent($Stage);

        return $Display;
    }
}
