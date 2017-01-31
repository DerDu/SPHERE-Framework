<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:24
 */

namespace SPHERE\Application\Education\Certificate\Generate;

use SPHERE\Application\Education\Certificate\Generate\Service\Data;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generate\Service\Setup;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblGenerateCertificate
     */
    public function getGenerateCertificateById($Id)
    {

        return (new Data($this->getBinding()))->getGenerateCertificateById($Id);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblGenerateCertificate[]
     */
    public function getGenerateCertificateAllByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getGenerateCertificateAllByYear($tblYear);
    }

    /**
     * @param IFormInterface|null $Form
     * @param null $Data
     * @param TblYear $tblYear
     *
     * @return IFormInterface|string
     */
    public function createGenerateCertificate(IFormInterface $Form = null, $Data = null, TblYear $tblYear)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        $Error = false;
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $Form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (!($tblCertificateType = Generator::useService()->getCertificateTypeById($Data['Type']))) {
            $Form->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }

        if ($Error) {
            return $Form;
        }

        $tblAppointedDateTask = Evaluation::useService()->getTaskById($Data['AppointedDateTask']);
        $tblBehaviorTask = Evaluation::useService()->getTaskById($Data['BehaviorTask']);

        if ($tblAppointedDateTask && $tblBehaviorTask) {
            $Name = $tblAppointedDateTask->getName() . ', ' . $tblBehaviorTask->getName();
        } elseif ($tblAppointedDateTask) {
            $Name = $tblAppointedDateTask->getName();
        } elseif ($tblBehaviorTask) {
            $Name = $tblBehaviorTask->getName();
        } else {
            $Name = $tblCertificateType->getName();
        }

        if ($tblGenerateCertificate = (new Data($this->getBinding()))->createGenerateCertificate(
            $tblYear,
            $Data['Date'],
            $Name,
            $tblCertificateType,
            $tblAppointedDateTask ? $tblAppointedDateTask : null,
            $tblBehaviorTask ? $tblBehaviorTask : null,
            $Data['HeadmasterName'],
            isset($Data['IsTeacherAvailable'])
        )
        ) {
            return new Success('Die Zeugniserstellung ist angelegt worden',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Certificate/Generate/Division/Select', Redirect::TIMEOUT_SUCCESS, array(
                'GenerateCertificateId' => $tblGenerateCertificate->getId()
            ));
        } else {
            return new Danger('Die Zeugniserstellung konnte nicht angelegt werden', new Exclamation())
            . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS);
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function createPrepareCertificates(
        IFormInterface $Form = null,
        TblGenerateCertificate $tblGenerateCertificate,
        $Data = null
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Form;
        }

        if ($Data !== null && !empty($Data)) {
            foreach ($Data['Division'] as $divisionId => $value) {
                if (($tblDivision = Division::useService()->getDivisionById($divisionId))) {
                    Prepare::useService()->createPrepareData(
                        $tblDivision,
                        $tblGenerateCertificate->getDate(),
                        $tblGenerateCertificate->getName(),
                        $tblGenerateCertificate->getServiceTblCertificateType()
                            ? ($tblGenerateCertificate->getServiceTblCertificateType()->getIdentifier() == 'GRADE_INFORMATION'
                            ? true : false)
                            : false,
                        $tblGenerateCertificate,
                        $tblGenerateCertificate->getServiceTblAppointedDateTask()
                            ? $tblGenerateCertificate->getServiceTblAppointedDateTask() : null,
                        $tblGenerateCertificate->getServiceTblBehaviorTask()
                            ? $tblGenerateCertificate->getServiceTblBehaviorTask() : null
                    );
                }
            }
        }

        return new Success('Die Klassen wurden erfolgreich zugeordnet.',
            new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect('/Education/Certificate/Generate/Division', Redirect::TIMEOUT_SUCCESS,
            array('GenerateCertificateId' => $tblGenerateCertificate->getId()));
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param int $countStudents
     * @param array $certificateNameList
     * @param array $schoolNameList
     *
     * @return int
     */
    public function setCertificateTemplates(
        TblPrepareCertificate $tblPrepare,
        &$countStudents = 0,
        &$certificateNameList = array(),
        &$schoolNameList = array()
    ) {

        $countTemplates = 0;
        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {

            $countStudents = count($tblPersonList);
            $tblConsumerBySession = Consumer::useService()->getConsumerBySession();
            foreach ($tblPersonList as $tblPerson) {
                // Schulnamen
                $tblCompany = false;
                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                    && ($tblStudent = $tblPerson->getStudent())
                ) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType);
                    if ($tblStudentTransfer) {
                        $tblCompany = $tblStudentTransfer->getServiceTblCompany();
                    }
                }
                if ($tblCompany) {
                    if (!array_search($tblCompany->getName(), $schoolNameList)) {
                        $schoolNameList[$tblCompany->getId()] = $tblCompany->getName();
                    }
                } else {
                    $schoolNameList[0] = new Warning(
                        new Exclamation() . ' Keine aktuelle Schule in der Schülerakte gepflegt'
                    );
                }

                // Template bereits gesetzt
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                    if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                        $countTemplates++;
                        if (!isset($certificateNameList[$tblCertificate->getId()])) {
                            $tblConsumer = $tblCertificate->getServiceTblConsumer();
                            $certificateNameList[$tblCertificate->getId()]
                                = ($tblConsumer ? $tblConsumer->getAcronym() . ' ' : '')
                                . $tblCertificate->getName() . ($tblCertificate->getDescription()
                                    ? ' ' . $tblCertificate->getDescription() : '');
                        }

                        continue;
                    }
                }

                if ($tblConsumerBySession) {
                    // Eigene Vorlage
                    if (($certificateList = $this->getPossibleCertificates($tblPrepare, $tblPerson, $tblConsumerBySession))) {
                        if (count($certificateList) == 1) {
                            // Aus Performance gründen Speicherung beim Aufruf in der Zeugnisvorbereitung
//                            Prepare::useService()->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, current($certificateList));
                            $countTemplates++;
                            /** @var TblCertificate $tblCertificate */
                            $tblCertificate = current($certificateList);
                            if (!isset($certificateNameList[$tblCertificate->getId()])) {
                                $tblConsumer = $tblCertificate->getServiceTblConsumer();
                                $certificateNameList[$tblCertificate->getId()]
                                    = ($tblConsumer ? $tblConsumer->getAcronym() . ' ' : '')
                                    . $tblCertificate->getName() . ($tblCertificate->getDescription()
                                        ? ' ' . $tblCertificate->getDescription() : '');
                            }
                        } else {
                            continue;
                        }
                        // Standard Vorlagen
                    } elseif (($certificateList = $this->getPossibleCertificates($tblPrepare, $tblPerson))) {
                        if (count($certificateList) == 1) {
                            // Aus Performance gründen Speicherung beim Aufruf in der Zeugnisvorbereitung
//                            Prepare::useService()->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, current($certificateList));
                            $countTemplates++;
                            /** @var TblCertificate $tblCertificate */
                            $tblCertificate = current($certificateList);
                            if (!isset($certificateNameList[$tblCertificate->getId()])) {
                                $tblConsumer = $tblCertificate->getServiceTblConsumer();
                                $certificateNameList[$tblCertificate->getId()]
                                    = ($tblConsumer ? $tblConsumer->getAcronym() . ' ' : '')
                                    . $tblCertificate->getName() . ($tblCertificate->getDescription()
                                        ? ' ' . $tblCertificate->getDescription() : '');
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
        }

        return $countTemplates;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblConsumer|null $tblConsumer
     *
     * @return array|bool
     */
    public function getPossibleCertificates(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblConsumer $tblConsumer = null
    ) {

        $certificateList = array();

        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblLevel = $tblDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
            && $tblPrepare->getServiceTblGenerateCertificate()
            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
            && ($tblCertificateList = Generator::useService()->getCertificateAllBy(
                $tblConsumer ? $tblConsumer : null,
                $tblCertificateType ? $tblCertificateType : null,
                $tblSchoolType
            ))
        ) {

            $tblCourse = false;
            // Bildungsgang nur hier relevant sonst klappt es bei den anderen nicht korrekt
            if (preg_match('!(Mittelschule|Oberschule)!is', $tblSchoolType->getName())
                && preg_match('!(0?(7|8|9)|10)!is', $tblLevel->getName())
            ) {
                if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                    && ($tblStudent = $tblPerson->getStudent())
                ) {
                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblTransferType);
                    if ($tblStudentTransfer) {
                        $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                    }
                }
            }

            foreach ($tblCertificateList as $tblCertificate) {
                // Schüler hat keinen Bildungsgang
                if (!$tblCourse) {
                    $tblCertificateLevelList = Generator::useService()->getCertificateLevelAllByCertificate($tblCertificate);
                    if ($tblCertificateLevelList) {
                        if ($this->findLevel($tblCertificateLevelList, $tblDivision->getTblLevel())) {
                            $certificateList[] = $tblCertificate;
                        }
                    } else {
                        $certificateList[] = $tblCertificate;
                    }
                    //  Schüler hat Bildungsgang der Vorlage
                } elseif ($tblCourse
                    && $tblCertificate->getServiceTblCourse()
                    && $tblCourse->getId() == $tblCertificate->getServiceTblCourse()->getId()
                ) {
                    if (($tblCertificateLevelList = Generator::useService()->getCertificateLevelAllByCertificate($tblCertificate))) {
                        if ($this->findLevel($tblCertificateLevelList, $tblDivision->getTblLevel())) {
                            $certificateList[] = $tblCertificate;
                        }
                    } else {
                        $certificateList[] = $tblCertificate;
                    }
                }
            }
        }

        return empty($certificateList) ? false : $certificateList;
    }

    /**
     * @param $tblCertificateLevelList
     * @param TblLevel $tblLevel
     *
     * @return bool|TblLevel
     */
    private function findLevel($tblCertificateLevelList, TblLevel $tblLevel)
    {
        /** @var TblCertificateLevel $tblCertificateLevel */
        foreach ($tblCertificateLevelList as $tblCertificateLevel) {
            if ($tblCertificateLevel->getServiceTblLevel()
                && $tblCertificateLevel->getServiceTblLevel()->getId() == $tblLevel->getId()
            ) {
                return $tblLevel;
            }
        }

        return false;
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblPrepareCertificate $tblPrepare
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function editCertificateTemplates(
        IFormInterface $Form = null,
        TblPrepareCertificate $tblPrepare,
        $Data = null
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Form;
        }

        if ($Data !== null && !empty($Data)) {
            foreach ($Data as $personId => $value) {
                if (($tblPerson = Person::useService()->getPersonById($personId))) {
                    $tblCertificate = Generator::useService()->getCertificateById($value);
                    Prepare::useService()->updatePrepareStudentSetCertificate($tblPrepare, $tblPerson,
                        $tblCertificate ? $tblCertificate : null);
                }
            }
        }

        return new Success('Die Zeugnisvorlagen wurden erfolgreich zugeordnet.',
            new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect('/Education/Certificate/Generate/Division', Redirect::TIMEOUT_SUCCESS,
            array('GenerateCertificateId' => $tblPrepare->getServiceTblGenerateCertificate()->getId()));
    }
}