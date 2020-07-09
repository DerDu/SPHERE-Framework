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
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificateSetting;
use SPHERE\Application\Education\Certificate\Generate\Service\Setup;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Common\Common;
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
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
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
     * @return false|TblGenerateCertificate[]
     */
    public function getGenerateCertificateAll()
    {

        return (new Data($this->getBinding()))->getGenerateCertificateAll();
    }

    /**
     * @param IFormInterface|null $Form
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function createGenerateCertificate(IFormInterface $Form = null, $Data = null)
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
        if (!($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $Form->setError('Data[Year]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if ($Error) {
            return $Form;
        }

        $tblAppointedDateTask = Evaluation::useService()->getTaskById($Data['AppointedDateTask']);
        $tblBehaviorTask = Evaluation::useService()->getTaskById($Data['BehaviorTask']);

        if ($tblGenerateCertificate = (new Data($this->getBinding()))->createGenerateCertificate(
            $tblYear,
            $Data['Date'],
            $Data['Name'],
            $tblCertificateType,
            $tblAppointedDateTask ? $tblAppointedDateTask : null,
            $tblBehaviorTask ? $tblBehaviorTask : null,
            isset($Data['HeadmasterName']) ? $Data['HeadmasterName'] : '',
            isset($Data['IsTeacherAvailable']),
            isset($Data['GenderHeadmaster'])
            && ($tblCommonGender = Common::useService()->getCommonGenderById($Data['GenderHeadmaster']))
                ? $tblCommonGender : null
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
        if ($Data === null) {
            return $Form;
        }

        if ($Data !== null && isset($Data['Division'])) {
            $saveCertificatesForStudents = array();
            $tblConsumerBySession = Consumer::useService()->getConsumerBySession();
            foreach ($Data['Division'] as $divisionId => $value) {
                if (($tblDivision = Division::useService()->getDivisionById($divisionId))) {
                    if (($tblPrepare = Prepare::useService()->createPrepareData(
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
                    ))
                    ) {

                        if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                            if (($tblLevel = $tblDivision->getTblLevel())) {
                                $tblType = $tblLevel->getServiceTblType();
                            } else {
                                $tblType = false;
                            }

                            foreach ($tblPersonList as $tblPerson) {
                                // Template bereits gesetzt
                                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                    $tblPerson))
                                ) {
                                    if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                                        continue;
                                    }
                                }

                                // bei Mittelschule und Primärer Förderschwerpunkt Lernen oder geistige Entwicklung soll keine
                                // Zeugnisvorlage vorausgewählt werden
                                if ($tblType && !$this->checkAutoSelect($tblPerson, $tblType)) {
                                    continue;
                                }

                                if ($tblConsumerBySession) {
                                    // Eigene Vorlage
                                    if (($certificateList = $this->getPossibleCertificates($tblPrepare, $tblPerson,
                                        $tblConsumerBySession))
                                    ) {
                                        if (count($certificateList) == 1) {
                                            /** @var TblCertificate $tblCertificate */
                                            $tblCertificate = current($certificateList);
                                            $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                        } elseif (count($certificateList) > 1) {
                                            /** @var TblCertificate $certificate */
                                            $ChosenCertificate = false;
                                            foreach ($certificateList as $certificate) {
                                                if ($certificate->isChosenDefault()) {
                                                    $ChosenCertificate = $certificate;
                                                    break;
                                                }
                                            }
                                            if ($ChosenCertificate) {
                                                $tblCertificate = $ChosenCertificate;
                                                $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                            }
                                        } else {
                                            continue;
                                        }
                                        // Standard Vorlagen
                                    } elseif (($certificateList = $this->getPossibleCertificates($tblPrepare, $tblPerson))) {
                                        if (count($certificateList) == 1) {
                                            /** @var TblCertificate $tblCertificate */
                                            $tblCertificate = current($certificateList);
                                            if (!isset($certificateNameList[$tblCertificate->getId()])) {
                                                $tblConsumer = $tblCertificate->getServiceTblConsumer();
                                                $certificateNameList[$tblCertificate->getId()]
                                                    = ($tblConsumer ? $tblConsumer->getAcronym() . ' ' : '')
                                                    . $tblCertificate->getName() . ($tblCertificate->getDescription()
                                                        ? ' ' . $tblCertificate->getDescription() : '');
                                            }
                                            $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                        } elseif (count($certificateList) > 1) {
                                            /** @var TblCertificate $certificate */
                                            $ChosenCertificate = false;
                                            foreach ($certificateList as $certificate) {
                                                if ($certificate->isChosenDefault()) {
                                                    $ChosenCertificate = $certificate;
                                                    break;
                                                }
                                            }
                                            if ($ChosenCertificate) {
                                                $tblCertificate = $ChosenCertificate;
                                                $saveCertificatesForStudents[$tblPrepare->getId()][$tblPerson->getId()] = $tblCertificate;
                                            }
                                        } else {
                                            continue;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($saveCertificatesForStudents)) {
                Prepare::useService()->createPrepareStudentSetBulkTemplates($saveCertificatesForStudents);
            }
        }

        return new Success('Die Klassen wurden erfolgreich zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Certificate/Generate/Division', Redirect::TIMEOUT_SUCCESS,
                array('GenerateCertificateId' => $tblGenerateCertificate->getId()));
    }


    /**
     * Prüft ob für die Person automatisch eine Zeugnisvorlage zugewiesen wird
     *
     * @param TblPerson $tblPerson
     * @param TblType $tblType
     *
     * @return bool
     */
    private function checkAutoSelect(TblPerson $tblPerson, TblType $tblType)
    {

        if ($tblType->getName() == 'Mittelschule / Oberschule') {
            if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
                && ($tblPrimaryFocus = Student::useService()->getPrimaryFocusBySupport($tblSupport))
            ) {
                if ($tblPrimaryFocus->getName() == 'Lernen') {
                    return false;
                }

                if ($tblPrimaryFocus->getName() == 'Geistige Entwicklung') {
                    return false;
                }
            }
        }

        return true;
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
            foreach ($tblPersonList as $tblPerson) {
                // Schulnamen
                if (($tblCompany = Student::useService()->getCurrentSchoolByPerson($tblPerson, $tblDivision))) {
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
            && ($tblCertificateList = Generator::useService()->getCertificateAllForAutoSelect(
                $tblConsumer ? $tblConsumer : null,
                $tblCertificateType ? $tblCertificateType : null,
                $tblSchoolType
            ))
        ) {
            // SSW-939 - Noteninformation Zuweisung Vorlage
            if ($tblCertificateType->getIdentifier() == 'GRADE_INFORMATION'
                && ($tblCertificate = Setting::useService()->getCertificateByCertificateClassName('GradeInformation'))
            ) {
                return $tblCertificateList;
            }

            $tblCourse = false;
            // Bildungsgang nur hier relevant sonst klappt es bei den anderen nicht korrekt
            // #SSW-1064 Automatische Zuordnung von Zeugnissen ist nicht korrekt in Coswig
            if ($this->getUseCourseForCertificateChoosing()) {
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
        if ($Data === null) {
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

    /**
     * @param IFormInterface|null $Form
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function updateGenerateCertificate(
        IFormInterface $Form = null,
        TblGenerateCertificate $tblGenerateCertificate,
        $Data = null
    ) {

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
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if ($Error) {
            return $Form;
        }

        if ($tblGenerateCertificate->isLocked()) {
            $tblAppointedDateTask = $tblGenerateCertificate->getServiceTblAppointedDateTask();
            $tblBehaviorTask = $tblGenerateCertificate->getServiceTblBehaviorTask();
        } else {
            $tblAppointedDateTask = isset($Data['AppointedDateTask'])
                ? Evaluation::useService()->getTaskById($Data['AppointedDateTask']) : false;
            $tblBehaviorTask = isset($Data['BehaviorTask'])
                ? Evaluation::useService()->getTaskById($Data['BehaviorTask']) : false;
        }

        if ((new Data($this->getBinding()))->updateGenerateCertificate(
            $tblGenerateCertificate,
            $Data['Date'],
            isset($Data['IsTeacherAvailable']),
            isset($Data['HeadmasterName']) ? $Data['HeadmasterName'] : '',
            isset($Data['GenderHeadmaster'])
            && ($tblCommonGender = Common::useService()->getCommonGenderById($Data['GenderHeadmaster']))
                ? $tblCommonGender : null,
            $tblAppointedDateTask ? $tblAppointedDateTask : null,
            $tblBehaviorTask ? $tblBehaviorTask : null,
            $Data['Name']
        )
        ) {
            if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                foreach ($tblPrepareList as $tblPrepare) {
                    Prepare::useService()->updatePrepareData(
                        $tblPrepare,
                        $Data['Date'],
                        $Data['Name'],
                        $tblAppointedDateTask ? $tblAppointedDateTask : null,
                        $tblBehaviorTask ? $tblBehaviorTask : null,
                        isset($Data['IsTeacherAvailable'])
                            ? ($tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : null)
                            : null
                    );
                }
            }

            return new Success('Die Zeugniserstellung ist geändert worden',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS, array(
                    'GenerateCertificateId' => $tblGenerateCertificate->getId()
                ));
        } else {
            return new Danger('Die Zeugniserstellung konnte nicht geändert werden', new Exclamation())
                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS);
        }
    }

    /**
     * @return bool
     */
    public function getUseCourseForCertificateChoosing()
    {
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
            'Education', 'Certificate', 'Generate', 'UseCourseForCertificateChoosing'))
        ) {
            return (boolean)$tblSetting->getValue();
        }

        return false;
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param bool $IsLocked
     *
     * @return bool
     */
    public function lockGenerateCertificate(
        TblGenerateCertificate $tblGenerateCertificate,
        $IsLocked = true
    ) {

        return (new Data($this->getBinding()))->lockGenerateCertificate($tblGenerateCertificate, $IsLocked);
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return bool
     */
    public function destroyGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate) {

        if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
            foreach ($tblPrepareList as $tblPrepare) {
                Prepare::useService()->destroyPrepareCertificate($tblPrepare);
            }
        }

        return (new Data($this->getBinding()))->destroyGenerateCertificate($tblGenerateCertificate);
    }

    /**
     * @param IFormInterface $form
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateAbiturSettings(
        IFormInterface $form,
        TblGenerateCertificate $tblGenerateCertificate,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        $tblPersonLeader = Person::useService()->getPersonById($Data['Leader']);
        if (($tblGenerateCertificateSettingLeader = $this->getGenerateCertificateSettingBy($tblGenerateCertificate, 'Leader'))) {
            (new Data($this->getBinding()))->updateGenerateCertificateSetting(
                $tblGenerateCertificateSettingLeader,
                $tblPersonLeader
            );
        } else {
            (new Data($this->getBinding()))->createGenerateCertificateSetting(
                $tblGenerateCertificate,
                'Leader',
                $tblPersonLeader
            );
        }

        $tblPersonFirstMember = Person::useService()->getPersonById($Data['FirstMember']);
        if (($tblGenerateCertificateSettingFirstMember = $this->getGenerateCertificateSettingBy($tblGenerateCertificate, 'FirstMember'))) {
            (new Data($this->getBinding()))->updateGenerateCertificateSetting(
                $tblGenerateCertificateSettingFirstMember,
                $tblPersonFirstMember
            );
        } else {
            (new Data($this->getBinding()))->createGenerateCertificateSetting(
                $tblGenerateCertificate,
                'FirstMember',
                $tblPersonFirstMember
            );
        }

        $tblPersonSecondMember = Person::useService()->getPersonById($Data['SecondMember']);
        if (($tblGenerateCertificateSettingSecondMember = $this->getGenerateCertificateSettingBy($tblGenerateCertificate, 'SecondMember'))) {
            (new Data($this->getBinding()))->updateGenerateCertificateSetting(
                $tblGenerateCertificateSettingSecondMember,
                $tblPersonSecondMember
            );
        } else {
            (new Data($this->getBinding()))->createGenerateCertificateSetting(
                $tblGenerateCertificate,
                'SecondMember',
                $tblPersonSecondMember
            );
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Generate/Setting', Redirect::TIMEOUT_SUCCESS, array(
                'GenerateCertificateId' => $tblGenerateCertificate->getId(),
            ));
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param $Field
     *
     * @return false|TblGenerateCertificateSetting
     * @throws \Exception
     */
    public function getGenerateCertificateSettingBy(TblGenerateCertificate $tblGenerateCertificate, $Field)
    {

        return (new Data($this->getBinding()))->getGenerateCertificateSettingBy($tblGenerateCertificate, $Field);
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return false|TblGenerateCertificateSetting[]
     * @throws \Exception
     */
    public function getGenerateCertificateSettingAllByGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate)
    {

        return (new Data($this->getBinding()))->getGenerateCertificateSettingAllByGenerateCertificate($tblGenerateCertificate);
    }
}