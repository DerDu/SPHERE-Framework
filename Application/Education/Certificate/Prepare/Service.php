<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Setup;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
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
     * @return false|TblPrepareCertificate
     */
    public function getPrepareById($Id)
    {

        return (new Data($this->getBinding()))->getPrepareById($Id);
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool $IsGradeInformation
     *
     * @return false|Service\Entity\TblPrepareCertificate[]
     */
    public function getPrepareAllByDivision(TblDivision $tblDivision, $IsGradeInformation = false)
    {

        return (new Data($this->getBinding()))->getPrepareAllByDivision($tblDivision, $IsGradeInformation);
    }

    /**
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAll()
    {

        return (new Data($this->getBinding()))->getPrepareAll();
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAllByYear(TblYear $tblYear)
    {

        $resultList = array();
        $entityList = $this->getPrepareAll();
        if ($entityList) {
            foreach ($entityList as $tblPrepare) {
                if (($tblDivision = $tblPrepare->getServiceTblDivision())
                    && $tblDivision->getServiceTblYear()
                    && $tblDivision->getServiceTblYear()->getId() == $tblYear->getId()
                ) {
                    $resultList[] = $tblPrepare;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return false|TblPrepareStudent
     */
    public function getPrepareStudentBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getPrepareStudentBy($tblPrepare, $tblPerson);
    }

    /**
     * @param bool|false $IsApproved
     * @param bool|false $IsPrinted
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllWhere($IsApproved = false, $IsPrinted = false)
    {

        return (new Data($this->getBinding()))->getPrepareStudentAllWhere($IsApproved, $IsPrinted);
    }

    /**
     * Fach-Note
     *
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeBySubject(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeBySubject(
            $tblPrepare,
            $tblPerson,
            $tblDivision,
            $tblSubject,
            $tblTestType
        );
    }

    /**
     * Kopf-Note
     *
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeByGradeType($tblPrepare, $tblPerson, $tblDivision,
            $tblTestType, $tblGradeType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPerson(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeAllByPerson($tblPrepare, $tblPerson, $tblTestType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPrepare(
        TblPrepareCertificate $tblPrepare,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeAllByPrepare(
            $tblPrepare, $tblTestType
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return false|TblPrepareInformation[]
     */
    public function getPrepareInformationAllByPerson(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getPrepareInformationAllByPerson($tblPrepare, $tblPerson);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Field
     *
     * @return false|TblPrepareInformation
     */
    public function getPrepareInformationBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, $Field)
    {

        return (new Data($this->getBinding()))->getPrepareInformationBy($tblPrepare, $tblPerson, $Field);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepareSetSigner(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        $tblPerson = Person::useService()->getPersonById($Data);
        if (!$tblPerson) {
            $Stage->setError('Data', 'Bitte wählen Sie eine Person aus');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updatePrepare(
                $tblPrepare,
                $tblPrepare->getDate(),
                $tblPrepare->getName(),
                $tblPrepare->getServiceTblAppointedDateTask() ? $tblPrepare->getServiceTblAppointedDateTask() : null,
                $tblPrepare->getServiceTblBehaviorTask() ? $tblPrepare->getServiceTblBehaviorTask() : null,
                $tblPerson
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Unterzeichner wurde ausgewählt.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId()
                ));
        }

        return $Stage;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblCertificate|null $tblCertificate
     *
     * @return string
     */
    public function updatePrepareStudentSetCertificate(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblCertificate $tblCertificate = null
    ) {

        if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
            (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate ? $tblCertificate : null,
                $tblPrepareStudent->isApproved(),
                $tblPrepareStudent->isPrinted(),
                $tblPrepareStudent->getExcusedDays(),
                $tblPrepareStudent->getUnexcusedDays()
            );
        } else {
            (new Data($this->getBinding()))->createPrepareStudent(
                $tblPrepare,
                $tblPerson,
                $tblCertificate ? $tblCertificate : null
            );
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Zeugnisvorlage wurde ausgewählt.')
            . new Redirect('/Education/Certificate/Prepare/Certificate', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'PersonId' => $tblPerson->getId()
            ));
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool
     */
    public function updatePrepareStudentSetApproved(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepareStudent->getTblPrepareCertificate()->getServiceTblDivision())
        ) {

            return (new Data($this->getBinding()))->copySubjectGradesByPerson($tblPrepare, $tblPerson);
        } else {
            return false;
        }
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool
     */
    public function updatePrepareStudentSetPrinted(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepareStudent->getTblPrepareCertificate()->getServiceTblDivision())
        ) {
            return (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate,
                $tblPrepareStudent->isApproved(),
                true,
                $tblPrepareStudent->getExcusedDays(),
                $tblPrepareStudent->getUnexcusedDays()
            );
        } else {
            return false;
        }
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool
     */
    public function updatePrepareStudentResetApproved(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepareStudent->getTblPrepareCertificate()->getServiceTblDivision())
        ) {
            return (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate,
                false,
                false,
                $tblPrepareStudent->getExcusedDays(),
                $tblPrepareStudent->getUnexcusedDays()
            );
        } else {
            return false;
        }
    }


    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param string $Route
     * @param array $Data
     * @param array $CertificateList
     * @return IFormInterface|string
     */
    public function updatePrepareInformationList(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        $Route,
        $Data,
        $CertificateList
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $this->setSignerFromSignedInPerson($tblPrepare);

        foreach ($Data as $personId => $array) {
            if (($tblPerson = Person::useService()->getPersonById($personId)) && is_array($array)) {
                if (isset($CertificateList[$personId])) {

                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = $CertificateList[$personId];
                    $tblCertificate = $Certificate->getCertificateEntity();

                    /*
                     * Fehlzeiten
                     */
                    if ($tblCertificate && isset($array['ExcusedDays']) && isset($array['UnexcusedDays'])) {
                        if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                            (new Data($this->getBinding()))->updatePrepareStudent(
                                $tblPrepareStudent,
                                $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : $tblCertificate,
                                $tblPrepareStudent->isApproved(),
                                $tblPrepareStudent->isPrinted(),
                                $array['ExcusedDays'],
                                $array['UnexcusedDays']
                            );
                        } else {
                            (new Data($this->getBinding()))->createPrepareStudent(
                                $tblPrepare,
                                $tblPerson,
                                $tblCertificate,
                                false,
                                false,
                                $array['ExcusedDays'],
                                $array['UnexcusedDays']
                            );
                        }
                    }

                    /*
                     * Sonstige Informationen
                     */
                    foreach ($array as $field => $value) {
                        if ($field == 'ExcusedDays' || $field == 'UnexcusedDays') {
                            continue;
                        } else {
                            if ($field == 'SchoolType'
                                && method_exists($Certificate, 'selectValuesSchoolType')
                            ) {
                                $value = $Certificate->selectValuesSchoolType()[$value];
                            } elseif ($field == 'Type'
                                && method_exists($Certificate, 'selectValuesType')
                            ) {
                                $value = $Certificate->selectValuesType()[$value];
                            } elseif ($field == 'Transfer'
                                && method_exists($Certificate, 'selectValuesTransfer')
                            ) {
                                $value = $Certificate->selectValuesTransfer()[$value];
                            }

                            if (!empty(trim($value))) {
                                $value = trim($value);
                                // Zeichenbegrenzen
                                if (($CharCount = Generator::useService()->getCharCountByCertificateAndField(
                                    $tblCertificate, $field, !isset($array['TeamExtra'])))
                                ) {
                                    $value = str_replace("\n", " ", $value);

                                    if (strlen($value) > $CharCount) {
                                        $value = substr($value, 0, $CharCount);
                                    }
                                }

                                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson,
                                    $field))
                                ) {
                                    (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation,
                                        $field,
                                        $value);
                                } else {
                                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson,
                                        $field,
                                        $value);
                                }
                            }
                        }
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'Route' => $Route
            ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getCertificateContent(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {

        $Content = array();

        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))
        ) {

            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);

            // Company
            $tblCompany = false;
            if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
                && $tblStudent
            ) {
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblTransferType);
                if ($tblStudentTransfer) {
                    $tblCompany = $tblStudentTransfer->getServiceTblCompany();

                    // Abschluss (Bildungsgang)
                    $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                    if ($tblCourse) {
                        if ($tblCourse->getName() == 'Hauptschule') {
                            $Content['Student']['Course']['Degree'] = 'Hauptschulabschlusses';
                        } elseif ($tblCourse->getName() == 'Realschule') {
                            $Content['Student']['Course']['Degree'] = 'Realschulabschlusses';
                        }
                    }
                }
            }
            if ($tblCompany) {
                $Content['Company']['Id'] = $tblCompany->getId();
            }

            // Arbeitsgemeinschaften
            if ($tblStudent
                && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                    $tblStudent, $tblSubjectType
                ))
            ) {
                $tempList = array();
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if ($tblStudentSubject->getServiceTblSubject()) {
                        $tempList[] = $tblStudentSubject->getServiceTblSubject()->getName();
                    }
                }
                if (!empty($tempList)) {
                    $Content['Subject']['Team'] = implode(', ', $tempList);
                }
            }

            // Fremdsprache ab Klassenstufe
            if ($tblStudent
                && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                    $tblStudent, $tblSubjectType
                ))
            ) {
                if ($tblStudentSubjectList) {
                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
                        if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                            && ($level = $tblStudentSubject->getServiceTblLevelFrom())
                        ) {
                            $Content['Subject']['Level'][$tblSubject->getAcronym()] = $level->getName();
                        }
                    }
                }
            }

            // Division
            if (($tblLevel = $tblDivision->getTblLevel())) {
                $Content['Division']['Id'] = $tblDivision->getId();
                $Content['Division']['Data']['Level']['Name'] = $tblLevel->getName();
                $Content['Division']['Data']['Name'] = $tblDivision->getName();
            }
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                $Content['Division']['Data']['Year'] = $tblYear->getName();
            }
            if ($tblPrepare->getServiceTblPersonSigner()) {
                $Content['Division']['Data']['Teacher'] = $tblPrepare->getServiceTblPersonSigner()->getFullName();
            }

            // Person
            $Content['Person']['Id'] = $tblPerson->getId();

            // zusätzliche Informationen
            $tblPrepareInformationList = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare,
                $tblPerson);
            if ($tblPrepareInformationList) {
                // Spezialfall Arbeitsgemeinschaften im Bemerkungsfeld
                $team = '';
                $remark = '';

                foreach ($tblPrepareInformationList as $tblPrepareInformation) {
                    if ($tblPrepareInformation->getField() == 'Team') {
                        $team = 'Arbeitsgemeinschaften: ' . $tblPrepareInformation->getValue();
                    } elseif ($tblPrepareInformation->getField() == 'Remark') {
                        $remark = $tblPrepareInformation->getValue();
                    } elseif ($tblPrepareInformation->getField() == 'Transfer') {
                        $Content['Input'][$tblPrepareInformation->getField()] = $tblPerson->getFirstSecondName()
                            . ' ' . $tblPerson->getLastName() . ' ' . $tblPrepareInformation->getValue();
                    } else {
                        $Content['Input'][$tblPrepareInformation->getField()] = $tblPrepareInformation->getValue();
                    }
                }

                // Streichung leeres Bemerkungsfeld
                if ($remark == '') {
                    $remark = '---';
                }

                if ($team || $remark) {
                    if ($team) {
                        $remark = $team . " \n\n " . $remark;
                    }
                }
                $Content['Input']['Remark'] = $remark;
            } else {
                $Content['Input']['Remark'] = '---';
            }

            // Klassenlehrer
            if ($tblPrepare->getServiceTblPersonSigner()) {
                if (($tblConsumer = Consumer::useService()->getConsumerBySession())
                    && $tblConsumer->getAcronym() == 'EVSR'
                ) {
                    $firstName = $tblPrepare->getServiceTblPersonSigner()->getFirstName();
                    if (strlen($firstName) > 1) {
                        $firstName = substr($firstName, 0, 1) . '.';
                    }
                    $Content['DivisionTeacher']['Name'] = $firstName . ' '
                        . $tblPrepare->getServiceTblPersonSigner()->getLastName();
                } elseif (($tblConsumer = Consumer::useService()->getConsumerBySession())
                    && $tblConsumer->getAcronym() == 'ESZC'
                ) {
                    $Content['DivisionTeacher']['Name'] = trim($tblPrepare->getServiceTblPersonSigner()->getSalutation()
                        . " " . $tblPrepare->getServiceTblPersonSigner()->getLastName());
                } else {
                    $Content['DivisionTeacher']['Name'] = $tblPrepare->getServiceTblPersonSigner()->getFullName();
                }
            }

            // Schulleitung
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && $tblGenerateCertificate->getHeadmasterName()
            ) {
                $Content['Headmaster']['Name'] = $tblGenerateCertificate->getHeadmasterName();
            }

            // Kopfnoten
            $tblPrepareGradeBehaviorList = Prepare::useService()->getPrepareGradeAllByPerson(
                $tblPrepare,
                $tblPerson,
                Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK')
            );
            if ($tblPrepareGradeBehaviorList) {
                foreach ($tblPrepareGradeBehaviorList as $tblPrepareGrade) {
                    if ($tblPrepareGrade->getServiceTblGradeType()) {
                        $Content['Input'][$tblPrepareGrade->getServiceTblGradeType()->getCode()] = $tblPrepareGrade->getGrade();
                    }
                }
            }
            // Kopfnoten von Fachlehrern für Noteninformation
            if ($tblPrepare->isGradeInformation() && ($tblBehaviorTask = $tblPrepare->getServiceTblBehaviorTask())) {
                if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblBehaviorTask))) {
                    /** @var TblTest $testItem */
                    foreach ($tblTestAllByTask as $testItem) {
                        if (($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($testItem, $tblPerson))
                            && $testItem->getServiceTblGradeType()
                            && $testItem->getServiceTblSubject()
                        ) {
                            $Content['Input']['BehaviorTeacher'][$testItem->getServiceTblSubject()->getAcronym()]
                            [$testItem->getServiceTblGradeType()->getCode()] = $tblGrade->getDisplayGrade();
                        }
                    }
                }
            }

            // Fachnoten
            if ($tblPrepare->isGradeInformation() || ($tblPrepareStudent && !$tblPrepareStudent->isApproved())) {
                if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                    && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask))
                ) {
                    foreach ($tblTestList as $tblTest) {
                        if (($tblGradeItem = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson))
                            && $tblTest->getServiceTblSubject()
                        ) {
                            // keine Tendenzen auf Zeugnissen
                            $withTrend = true;
                            if ($tblPrepareStudent
                                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                && !$tblCertificate->isInformation()
                            ) {
                                $withTrend = false;
                            }

                            $Content['Grade']['Data'][$tblTest->getServiceTblSubject()->getAcronym()]
                                = $tblGradeItem->getDisplayGrade($withTrend);

                            // bei Zeugnistext als Note Schriftgröße verkleinern
                            if ($tblGradeItem->getTblGradeText()) {
                                $Content['Grade']['Data']['IsShrinkSize'][$tblTest->getServiceTblSubject()->getAcronym()] = true;
                            }
                        }
                    }
                }
            } else {
                $tblPrepareGradeSubjectList = Prepare::useService()->getPrepareGradeAllByPerson(
                    $tblPrepare,
                    $tblPerson,
                    Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
                );
                if ($tblPrepareGradeSubjectList) {
                    foreach ($tblPrepareGradeSubjectList as $tblPrepareGrade) {
                        if ($tblPrepareGrade->getServiceTblSubject()) {
                            $Content['Grade']['Data'][$tblPrepareGrade->getServiceTblSubject()->getAcronym()] = $tblPrepareGrade->getGrade();
                        }
                    }
                }
            }

            // Fehlzeiten
            $excusedDays = $tblPrepareStudent->getExcusedDays();
            $unexcusedDays = $tblPrepareStudent->getUnexcusedDays();
            if ($excusedDays === null) {
                $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision,
                    new \DateTime($tblPrepare->getDate()));
            }
            if ($unexcusedDays === null) {
                $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblDivision,
                    new \DateTime($tblPrepare->getDate()));
            }
            $Content['Input']['Missing'] = $excusedDays;
            $Content['Input']['Bad']['Missing'] = $unexcusedDays;
            $Content['Input']['Total']['Missing'] = $excusedDays + $unexcusedDays;

            // Zeugnisdatum
            $Content['Input']['Date'] = $tblPrepare->getDate();

            // Notendurchschnitt der angegebenen Fächer für Bildungsempfehlung
            if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                && $tblCertificate->getName() == 'Bildungsempfehlung'
            ) {
                $average = $this->calcSubjectGradesAverage($tblPrepareStudent);
                if ($average) {
                    $Content['Grade']['Data']['Average'] = str_replace('.', ',', $average);
                }
            }

            // Notendurchschnitt aller anderen Fächer für Bildungsempfehlung
            if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                && $tblCertificate->getName() == 'Bildungsempfehlung'
            ) {
                $average = $this->calcSubjectGradesAverageOthers($tblPrepareStudent);
                if ($average) {
                    $Content['Grade']['Data']['AverageOthers'] = str_replace('.', ',', $average);
                }
            }

            // Wahlpflichtbereich
            if ($tblStudent) {

                // Vertiefungskurs
                if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ADVANCED'))
                    && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    /** @var TblStudentSubject $tblStudentSubject */
                    $tblStudentSubject = current($tblStudentSubjectList);
                    if (($tblSubjectAdvanced = $tblStudentSubject->getServiceTblSubject())) {
                        $Content['Student']['Advanced'][$tblSubjectAdvanced->getAcronym()]['Name'] = $tblSubjectAdvanced->getName();
                    }
                }

                // Neigungskurs
                if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                    && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    /** @var TblStudentSubject $tblStudentSubject */
                    $tblStudentSubject = current($tblStudentSubjectList);
                    if (($tblSubjectOrientation = $tblStudentSubject->getServiceTblSubject())) {
                        $Content['Student']['Orientation'][str_replace(' ', '',
                            $tblSubjectOrientation->getAcronym())]['Name'] = $tblSubjectOrientation->getName();
                    }
                }

                // 2. Fremdsprache
                if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                    && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    /** @var TblStudentSubject $tblStudentSubject */
                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
                        if ($tblStudentSubject->getTblStudentSubjectRanking()
                            && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                            && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                        ) {
                            $Content['Student']['ForeignLanguage'][str_replace(' ', '',
                                $tblSubjectForeignLanguage->getAcronym())]['Name'] = $tblSubjectForeignLanguage->getName();
                        }
                    }
                }

                // Profil
                if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
                    && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    /** @var TblStudentSubject $tblStudentSubject */
                    $tblStudentSubject = current($tblStudentSubjectList);
                    if (($tblSubjectProfile = $tblStudentSubject->getServiceTblSubject())) {
                        $Content['Student']['Profile'][str_replace(' ', '', $tblSubjectProfile->getAcronym())]['Name']
                            = str_replace('Profil', '', $tblSubjectProfile->getName());
                    }
                }
            }
        }

        return $Content;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool|float
     */
    private function calcSubjectGradesAverage(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {
            $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate);

            if ($tblCertificateSubjectAll) {
                $gradeList = array();
                foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                    if (($tblSubject = $tblCertificateSubject->getServiceTblSubject())) {
                        $tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject(
                            $tblPrepare, $tblPerson, $tblDivision, $tblSubject,
                            Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
                        );
                        if ($tblPrepareGrade && $tblPrepareGrade->getGrade() != '') {
                            $grade = str_replace('+', '', $tblPrepareGrade->getGrade());
                            $grade = str_replace('-', '', $grade);
                            if (is_numeric($grade)) {
                                $gradeList[] = $grade;
                            }
                        }

                    }
                }

                if (!empty($gradeList)) {
                    return round(floatval(array_sum($gradeList) / count($gradeList)), 1);
                }
            }
        }

        return false;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool|float
     */
    private function calcSubjectGradesAverageOthers(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $tblPrepareGradeList = $this->getPrepareGradeAllByPerson(
                $tblPrepare,
                $tblPerson,
                Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
            );

            if ($tblPrepareGradeList) {
                $gradeList = array();
                /** @var TblPrepareGrade $tblPrepareGrade */
                foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                    if (($tblSubject = $tblPrepareGrade->getServiceTblSubject())) {
                        $tblCertificateSubject = Generator::useService()->getCertificateSubjectBySubject(
                            $tblCertificate,
                            $tblSubject
                        );
                        if (!$tblCertificateSubject
                            && $tblPrepareGrade && $tblPrepareGrade->getGrade() != ''
                        ) {
                            $grade = str_replace('+', '', $tblPrepareGrade->getGrade());
                            $grade = str_replace('-', '', $grade);
                            if (is_numeric($grade)) {
                                $gradeList[] = $grade;
                            }
                        }
                    }
                }

                if (!empty($gradeList)) {
                    return round(floatval(array_sum($gradeList) / count($gradeList)), 1);
                }
            }
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param $Date
     * @param $Name
     * @param bool $IsGradeInformation
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param TblTask $tblAppointedDateTask
     * @param TblTask $tblBehaviorTask
     *
     * @return TblPrepareCertificate
     */
    public function createPrepareData(
        TblDivision $tblDivision,
        $Date,
        $Name,
        $IsGradeInformation = false,
        TblGenerateCertificate $tblGenerateCertificate = null,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null
    ) {

        return (new Data($this->getBinding()))->createPrepare(
            $tblDivision, $Date, $Name, $IsGradeInformation, $tblGenerateCertificate, $tblAppointedDateTask,
            $tblBehaviorTask);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $Date
     * @param $Name
     * @param TblTask|null $tblAppointedDateTask
     * @param TblTask|null $tblBehaviorTask
     * @param TblPerson|null $tblPersonSigner
     *
     * @return bool
     */
    public function updatePrepareData(
        TblPrepareCertificate $tblPrepare,
        $Date,
        $Name,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null,
        TblPerson $tblPersonSigner = null
    ) {

        return (new Data($this->getBinding()))->updatePrepare($tblPrepare, $Date, $Name, $tblAppointedDateTask,
            $tblBehaviorTask, $tblPersonSigner);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     * @param $Grade
     *
     * @return TblPrepareGrade
     */
    public function updatePrepareGradeForBehavior(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType,
        $Grade
    ) {

        return (new Data($this->getBinding()))->updatePrepareGradeForBehavior($tblPrepare, $tblPerson, $tblDivision,
            $tblTestType, $tblGradeType, $Grade);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblPrepareStudent
     */
    public function updatePrepareStudentSetTemplate(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblCertificate $tblCertificate
    ) {

        if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
            (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate,
                $tblPrepareStudent->isApproved(),
                $tblPrepareStudent->isPrinted(),
                $tblPrepareStudent->getExcusedDays(),
                $tblPrepareStudent->getUnexcusedDays()
            );

            return $tblPrepareStudent;
        } else {
            return (new Data($this->getBinding()))->createPrepareStudent(
                $tblPrepare,
                $tblPerson,
                $tblCertificate
            );
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Content
     * @param Certificate $Certificate
     */
    public function updatePrepareInformationDataList(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Content,
        Certificate $Certificate = null
    ) {

        if (isset($Content['Input']) && is_array($Content['Input'])) {
            foreach ($Content['Input'] as $field => $value) {
                if ($field == 'SchoolType'
                    && method_exists($Certificate, 'selectValuesSchoolType')
                ) {
                    $value = $Certificate->selectValuesSchoolType()[$value];
                } elseif ($field == 'Type'
                    && method_exists($Certificate, 'selectValuesType')
                ) {
                    $value = $Certificate->selectValuesType()[$value];
                } elseif ($field == 'Transfer'
                    && method_exists($Certificate, 'selectValuesTransfer')
                ) {
                    $value = $Certificate->selectValuesTransfer()[$value];
                }

                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, $field))) {
                    (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, $field, $value);
                } else {
                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, $field, $value);
                }
            }
        }
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAllByGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate)
    {

        return (new Data($this->getBinding()))->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGradeType $tblGradeType
     * @param TblGradeType $tblNextGradeType
     * @param string $Route
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepareBehaviorGrades(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        TblGradeType $tblGradeType,
        TblGradeType $tblNextGradeType = null,
        $Route,
        $Data,
        $Trend
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $error = false;

        foreach ($Data as $gradeTypeId => $value) {
            if (trim($value) !== '') {
                if (!preg_match('!^[1-5]{1}$!is', trim($value))) {
                    $error = true;
                    break;
                }
            }
        }

        $this->setSignerFromSignedInPerson($tblPrepare);

        if ($error) {
            $Stage->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1-5).
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $Stage;
        } else {
            if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'))
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
            ) {

                foreach ($Data as $personId => $value) {
                    if (($tblPerson = Person::useService()->getPersonById($personId))) {
                        if (trim($value) && trim($value) !== ''
                        ) {
                            if (isset($Trend[$personId])) {
                                if ($Trend[$personId] == TblGrade::VALUE_TREND_PLUS) {
                                    $value = trim($value) . '+';
                                } elseif ($Trend[$personId] == TblGrade::VALUE_TREND_MINUS) {
                                    $value = trim($value) . '-';
                                }
                            }

                            Prepare::useService()->updatePrepareGradeForBehavior(
                                $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblGradeType,
                                trim($value)
                            );
                        }
                    }
                }

                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnoten wurden gespeichert.')
                    . new Redirect('/Education/Certificate/Prepare/Prepare/Setting',
                        Redirect::TIMEOUT_SUCCESS,
                        $tblNextGradeType ? array(
                            'PrepareId' => $tblPrepare->getId(),
                            'Route' => $Route,
                            'GradeTypeId' => $tblNextGradeType->getId()
                        )
                            : array(
                            'PrepareId' => $tblPrepare->getId(),
                            'Route' => $Route,
                            'IsNotGradeType' => true
                        )
                    );
            }
        }

        return $Stage;
    }

    /**
     * Unterzeichner Klassenlehrer automatisch die angemeldete Person setzen
     *
     * @param TblPrepareCertificate $tblPrepare
     */
    private function setSignerFromSignedInPerson(TblPrepareCertificate $tblPrepare)
    {

        // Unterzeichner Klassenlehrer automatisch die angemeldete Person setzen
        if (!$tblPrepare->getServiceTblPersonSigner()
            && $tblPrepare->getServiceTblGenerateCertificate()
            && $tblPrepare->getServiceTblGenerateCertificate()->isDivisionTeacherAvailable()
        ) {
            $tblPerson = false;
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAllByAccount) {
                    $tblPerson = $tblPersonAllByAccount[0];
                }
            }
            if ($tblPerson) {
                (new Data($this->getBinding()))->updatePrepare(
                    $tblPrepare,
                    $tblPrepare->getDate(),
                    $tblPrepare->getName(),
                    $tblPrepare->getServiceTblAppointedDateTask() ? $tblPrepare->getServiceTblAppointedDateTask() : null,
                    $tblPrepare->getServiceTblBehaviorTask() ? $tblPrepare->getServiceTblBehaviorTask() : null,
                    $tblPerson
                );
            }
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     */
    public function setTemplatesAllByPrepareCertificate(TblPrepareCertificate $tblPrepare)
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                // Template bereits gesetzt
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                    if ($tblPrepareStudent->getServiceTblCertificate()) {
                        continue;
                    }
                }

                // Noteninformation
                if ($tblPrepare->isGradeInformation())
                {
                    $this->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson,
                        Generator::useService()->getCertificateByCertificateClassName('GradeInformation')
                    );
                    continue;
                }

                if ($tblConsumer) {
                    // Eigene Vorlage
                    if (($certificateList = Generate::useService()->getPossibleCertificates($tblPrepare, $tblPerson,
                        $tblConsumer))
                    ) {
                        if (count($certificateList) == 1) {
                            $this->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, current($certificateList));
                        } else {
                            continue;
                        }
                        // Standard Vorlagen
                    } elseif (($certificateList = Generate::useService()->getPossibleCertificates($tblPrepare,
                        $tblPerson))
                    ) {
                        if (count($certificateList) == 1) {
                            $this->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, current($certificateList));
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareDivisionSetApproved(TblPrepareCertificate $tblPrepare)
    {

        if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
            return (new Data($this->getBinding()))->copySubjectGradesByPrepare($tblPrepare);
        } else {
            return false;
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareDivisionResetApproved(TblPrepareCertificate $tblPrepare)
    {

        if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
            return (new Data($this->getBinding()))->updatePrepareStudentDivisionResetApproved($tblPrepare);
        } else {
            return false;
        }
    }
}