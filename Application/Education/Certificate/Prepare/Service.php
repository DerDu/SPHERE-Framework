<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekI;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekII;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockIView;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Setup;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Ajax\Template\Notify;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
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
     * @param bool $isForced
     *
     * @return false|TblPrepareStudent
     */
    public function getPrepareStudentBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getPrepareStudentBy($tblPrepare, $tblPerson, $isForced);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllByPerson(TblPerson $tblPerson, TblCertificate $tblCertificate = null)
    {
        return (new Data($this->getBinding()))->getPrepareStudentAllByPerson($tblPerson, $tblCertificate);
    }

    /**
     * @param $Id
     *
     * @return false|TblPrepareStudent
     */
    public function getPrepareStudentById($Id)
    {

        return (new Data($this->getBinding()))->getPrepareStudentById($Id);
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
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradesByPrepare(
        TblPrepareCertificate $tblPrepare,
        TblTestType $tblTestType
    ) {
        return (new Data($this->getBinding()))->getPrepareGradesByPrepare($tblPrepare, $tblTestType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     * @param bool $IsForced
     *
     * @return false|TblPrepareGrade[]
     * @throws \Exception
     */
    public function getPrepareGradeAllByPerson(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType,
        $IsForced = false
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeAllByPerson($tblPrepare, $tblPerson, $tblTestType, $IsForced);
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
     *
     * @return false|TblPrepareInformation[]
     */
    public function getPrepareInformationAllByPrepare(TblPrepareCertificate $tblPrepare)
    {
        return (new Data($this->getBinding()))->getPrepareInformationAllByPrepare($tblPrepare);
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
     * @param IFormInterface $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGroup|null $tblGroup
     * @param $Data
     * @param $Route
     *
     * @return IFormInterface|string
     */
    public function updatePrepareSetSigner(
        IFormInterface $Stage,
        TblPrepareCertificate $tblPrepare,
        TblGroup $tblGroup = null,
        $Data,
        $Route
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
            $tblPrepareList = false;
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($tblGroup) {
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                $tblPrepareList = array(0 => $tblPrepare);
            }

            if ($tblPrepareList) {
                foreach ($tblPrepareList as $tblPrepareItem) {
                    if (($tblPrepareStudentList = $this->getPrepareStudentAllByPrepare($tblPrepareItem))) {
                        foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                            if (!$tblGroup
                                || (($tblPersonTemp = $tblPrepareStudent->getServiceTblPerson())
                                    && Group::useService()->existsGroupPerson($tblGroup, $tblPersonTemp))
                            ) {
                                (new Data($this->getBinding()))->updatePrepareStudent(
                                    $tblPrepareStudent,
                                    $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : null,
                                    $tblPrepareStudent->isApproved(),
                                    $tblPrepareStudent->isPrinted(),
                                    $tblPrepareStudent->getExcusedDays(),
                                    $tblPrepareStudent->getUnexcusedDays(),
                                    $tblPerson
                                );
                            }
                        }
                    }
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Unterzeichner wurde ausgewählt.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                    'Route' => $Route
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
                $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
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

//            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
//                && !$tblGenerateCertificate->isLocked()
//            ) {
//                Generate::useService()->lockGenerateCertificate($tblGenerateCertificate, true);
//            }

            if (($tblCertificateType = $tblCertificate->getTblCertificateType())
                && $tblCertificateType->getIdentifier() == 'DIPLOMA'
            ) {
                $isDiploma = true;
            } else {
                $isDiploma = false;
            }

            return (new Data($this->getBinding()))->copySubjectGradesByPerson($tblPrepare, $tblPerson, $isDiploma);
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
                $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
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

        if (($tblSettingAbsence = ConsumerSetting::useService()->getSetting(
            'Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
        ) {
            $useClassRegisterForAbsence = $tblSettingAbsence->getValue();
        } else {
            $useClassRegisterForAbsence = false;
        }

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
                // Fehlzeiten zurücksetzen, bei automatischer Übernahme der Fehlzeiten
                $useClassRegisterForAbsence ? null : $tblPrepareStudent->getExcusedDays(),
                $useClassRegisterForAbsence ? null : $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
            );
        } else {
            return false;
        }
    }


    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGroup|null $tblGroup
     * @param string $Route
     * @param array $Data
     * @param array $CertificateList
     * @param null|integer $nextPage
     *
     * @return IFormInterface|string
     */
    public function updatePrepareInformationList(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        TblGroup $tblGroup = null,
        $Route,
        $Data,
        $CertificateList,
        $nextPage = null
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Stage;
        }

        foreach ($Data as $prepareStudentId => $array) {
            if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                && ($tblDivision = $tblPrepareItem->getServiceTblDivision())
                && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                && is_array($array)
            ) {

                $this->setSignerFromSignedInPerson($tblPrepareStudent);

                if (isset($CertificateList[$tblPerson->getId()])) {

                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = $CertificateList[$tblPerson->getId()];
                    $tblCertificate = $Certificate->getCertificateEntity();

                    /*
                     * Fehlzeiten
                     */
                    if ($tblCertificate && isset($array['ExcusedDays']) && isset($array['UnexcusedDays'])) {
                        (new Data($this->getBinding()))->updatePrepareStudent(
                            $tblPrepareStudent,
                            $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : $tblCertificate,
                            $tblPrepareStudent->isApproved(),
                            $tblPrepareStudent->isPrinted(),
                            $array['ExcusedDays'],
                            $array['UnexcusedDays'],
                            $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
                        );
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

                            if (trim($value) != '') {
                                $value = trim($value);

                                // erstmal deaktivieren, es werden teilweise zuviele Zeichen abgeschnitten
//                                // Zeichenbegrenzen
//                                if (($CharCount = Generator::useService()->getCharCountByCertificateAndField(
//                                    $tblCertificate, $field, !isset($array['TeamExtra'])))
//                                ) {
//                                    $value = str_replace("\n", " ", $value);
//
//                                    if (strlen($value) > $CharCount) {
//                                        $value = substr($value, 0, $CharCount);
//                                    }
//                                }

                                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepareItem, $tblPerson,
                                    $field))
                                ) {
                                    (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation,
                                        $field,
                                        $value);
                                } else {
                                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepareItem, $tblPerson,
                                        $field,
                                        $value);
                                }
                                // auf Leer zurücksetzen
                            } elseif (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepareItem, $tblPerson,
                                $field))
                            ) {
                                (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation,
                                    $field,
                                    $value);
                            }
                        }
                    }
                }
            }
        }

        if ($nextPage == null) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                    'Route' => $Route
                ));
        } else {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Setting',
                    Redirect::TIMEOUT_SUCCESS,
                   array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup : null,
                        'Route' => $Route,
                        'IsNotGradeType' => true,
                        'Page' => $nextPage
                    )
                );
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblLeaveStudent|null $tblLeaveStudent
     * @param TblPerson $tblPerson
     *
     * @param array $Content
     * @return array
     */
    private function createCertificateContent(
        TblPrepareCertificate $tblPrepare = null,
        TblLeaveStudent $tblLeaveStudent = null,
        TblPerson $tblPerson,
        $Content = array()
    ) {

        $tblDivision = false;
        $tblLevel = false;
        $tblPrepareStudent = false;
        $tblPersonSigner = false;
        $tblStudent = $tblPerson->getStudent();
        if ($tblPrepare) {
            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
            $tblDivision = $tblPrepare->getServiceTblDivision();
        } elseif ($tblLeaveStudent) {
            $tblDivision = $tblLeaveStudent->getServiceTblDivision();
        }

        if ($tblDivision) {
            $tblLevel = $tblDivision->getTblLevel();
        }
        $personId = $tblPerson->getId();

        // Person data
        $Content['P' . $personId]['Person']['Id'] = $tblPerson->getId();
        $Content['P' . $personId]['Person']['Data']['Name']['Salutation'] = $tblPerson->getSalutation();
        $Content['P' . $personId]['Person']['Data']['Name']['First'] = $tblPerson->getFirstSecondName();
        $Content['P' . $personId]['Person']['Data']['Name']['Last'] = $tblPerson->getLastName();

        // Person address
        if (($tblAddress = $tblPerson->fetchMainAddress())) {
            $Content['P' . $personId]['Person']['Address']['Street']['Name'] = $tblAddress->getStreetName();
            $Content['P' . $personId]['Person']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
            $Content['P' . $personId]['Person']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
            $Content['P' . $personId]['Person']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
        }

        // Person Common
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
            && $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()
        ) {
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Gender'] = $tblCommonBirthDates->getGender();
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace()
                ? $tblCommonBirthDates->getBirthplace() : '&nbsp;';
        }

        // Person Parents
        if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
            $mother = false;
            $father = false;
            // Standard false
            $IsTitle = false;
            if(($tblConsumerSetting = ConsumerSetting::useService()->getSetting('Education', 'Certificate', 'Prepare', 'ShowParentTitle'))){
                $IsTitle = $tblConsumerSetting->getValue();
            }
            foreach ($tblRelationshipList as $tblToPerson) {
                if (($tblFromPerson = $tblToPerson->getServiceTblPersonFrom())
                    && $tblToPerson->getServiceTblPersonTo()
                    && $tblToPerson->getTblType()->getName() == 'Sorgeberechtigt'
                    && $tblToPerson->getServiceTblPersonTo()->getId() == $tblPerson->getId()
                ) {
                    if (!isset($Content['P' . $personId]['Person']['Parent']['Mother']['Name'])) {
                        $Content['P' . $personId]['Person']['Parent']['Mother']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                        $Content['P' . $personId]['Person']['Parent']['Mother']['Name']['Last'] = $tblFromPerson->getLastName();
                        $mother = ($IsTitle ? $tblFromPerson->getTitle().' ' : '').
                            $tblFromPerson->getFirstSecondName().' '.$tblFromPerson->getLastName();
                    } elseif (!isset($Content['P' . $personId]['Person']['Parent']['Father']['Name'])) {
                        $Content['P' . $personId]['Person']['Parent']['Father']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                        $Content['P' . $personId]['Person']['Parent']['Father']['Name']['Last'] = $tblFromPerson->getLastName();
                        $father = ($IsTitle ? $tblFromPerson->getTitle().' ' : '').
                            $tblFromPerson->getFirstSecondName().' '.$tblFromPerson->getLastName();
                    }
                }
            }
            // comma decision
            // usage only for "Bildungsempfehlung" (Titel Option for Parent!)
            if($mother && $father){
                $Content['P' . $personId]['Person']['Parent']['CommaSeparated'] = $mother.', '.$father;
            } elseif($mother){
                $Content['P' . $personId]['Person']['Parent']['CommaSeparated'] = $mother;
            } elseif($father) {
                $Content['P' . $personId]['Person']['Parent']['CommaSeparated'] = $father;
            }
        }

        // Company
        $tblCompany = false;
        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
            && $tblStudent
        ) {
            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                $tblTransferType);
            if ($tblStudentTransfer) {
                $tblCompany = Student::useService()->getCurrentSchoolByPerson($tblPerson, $tblDivision ? $tblDivision : null);

                // Abschluss (Bildungsgang)
                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                if ($tblCourse) {
                    if ($tblLevel && (intval($tblLevel->getName()) > 6)) {
                        if ($tblCourse->getName() == 'Hauptschule') {
                            $Content['P' . $personId]['Student']['Course']['Degree'] = 'Hauptschulabschlusses';
                            $Content['P' . $personId]['Student']['Course']['Name'] = 'Hauptschulbildungsgang';
                        } elseif ($tblCourse->getName() == 'Realschule') {
                            $Content['P' . $personId]['Student']['Course']['Degree'] = 'Realschulabschlusses';
                            $Content['P' . $personId]['Student']['Course']['Name'] = 'Realschulbildungsgang';
                        }
                    }
                }
            }
        }
        if ($tblCompany) {
            $Content['P' . $personId]['Company']['Id'] = $tblCompany->getId();
            $Content['P' . $personId]['Company']['Data']['Name'] = $tblCompany->getName();
            $Content['P'.$personId]['Company']['Data']['ExtendedName'] = $tblCompany->getExtendedName();
            if (($tblAddress = $tblCompany->fetchMainAddress())) {
                $Content['P' . $personId]['Company']['Address']['Street']['Name'] = $tblAddress->getStreetName();
                $Content['P' . $personId]['Company']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
                $Content['P' . $personId]['Company']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
                $Content['P' . $personId]['Company']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
            }
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
                $Content['P' . $personId]['Subject']['Team'] = implode(', ', $tempList);
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
                        $Content['P' . $personId]['Subject']['Level'][$tblSubject->getAcronym()] = $level->getName();
                    }
                }
            }
        }

        $tblYear = false;
        if ($tblDivision && ($tblYear = $tblDivision->getServiceTblYear())) {
            $Content['P' . $personId]['Division']['Data']['Year'] = $tblYear->getName();
        }
        // Division
        if ($tblDivision && $tblLevel) {
            $Content['P' . $personId]['Division']['Id'] = $tblDivision->getId();
            $Content['P' . $personId]['Division']['Data']['Level']['Name'] = $tblLevel->getName();
            if(is_numeric($tblDivision->getName())){
                $Content['P' . $personId]['Division']['Data']['Name'] = '-'.$tblDivision->getName();
            } else {
                $Content['P' . $personId]['Division']['Data']['Name'] = $tblDivision->getName();
            }
//            $Content['P' . $personId]['Division']['Data']['Name'] = $tblDivision->getName();
            // hänge ein e an die Beschreibung, wenn es noch nicht da ist (Mandant-ESS)
            $Description = $tblDivision->getDescription();
            if($Description != '' && substr($Description, -1) != 'e'){
                $Description .= 'e';
            }
            $Content['P' . $personId]['Division']['Data']['DescriptionWithE'] = $Description;

            $course = $tblLevel->getName();
            // html funktioniert, allerdings kann es der DOM-PDF nicht, enable utf-8 for domPdf? oder eventuell Schriftart ändern
            // $midTerm = '/&#x2160;';
            $midTerm = '/I';
            if ($tblPrepare
                && ($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
                && $tblYear
                && ($tblPeriodList = $tblYear->getTblPeriodAll($tblLevel && $tblLevel->getName() == '12'))
                && ($tblPeriod = $tblAppointedDateTask->getServiceTblPeriodByDivision($tblDivision))
                && ($tblFirstPeriod = current($tblPeriodList))
                && $tblPeriod->getId() != $tblFirstPeriod->getId()
            ) {
                // $midTerm = '/&#x2161;';
                $midTerm = '/II';
            }
            $course .= $midTerm;
            $Content['P' . $personId]['Division']['Data']['Course']['Name'] = $course;
        }
        if (($tblPrepareStudent && ($tblPersonSigner = $tblPrepareStudent->getServiceTblPersonSigner()))
            || ($tblPrepare && $tblPersonSigner = $tblPrepare->getServiceTblPersonSigner())
        ) {
            $Content['P' . $personId]['Division']['Data']['Teacher'] = $tblPersonSigner->getFullName();
        }

        $tblCertificate = false;
        $isGradeVerbal = false;
        if ($tblPrepareStudent) {
            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
            if ($tblCertificate) {
                $isGradeVerbal = $tblCertificate->getIsGradeVerbal();
            }
        }

        if ($tblPrepare
            && ($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
            && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
            && $tblCertificateType->getIdentifier() == 'DIPLOMA'
            && ($tblSetting = ConsumerSetting::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
            && $tblSetting->getValue()
        ) {
            $isGradeVerbalOnDiploma = true;
        } else {
            $isGradeVerbalOnDiploma = false;
        }

        // zusätzliche Informationen
        if ($tblPrepare) {
            $tblPrepareInformationList = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare,
                $tblPerson);

            // Spezialfall für Förderzeugnisse Lernen
            $isSupportLearningCertificate = false;
            if ($tblPrepareStudent && $tblCertificate) {
                if (strpos($tblCertificate->getCertificate(), 'FsLernen') !== false) {
                    $isSupportLearningCertificate = true;
                }
            }

            if (($tblSetting = ConsumerSetting::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'HasRemarkBlocking'
            ))) {
                $hasRemarkBlocking = (boolean) $tblSetting->getValue();
            } else {
                $hasRemarkBlocking = true;
            }

            $tblConsumer = Consumer::useService()->getConsumerBySession();
            if ($tblPrepareInformationList) {
                // Spezialfall Arbeitsgemeinschaften im Bemerkungsfeld
                $team = '';
                $teamChange = '';
                $remark = '';
                $support = '';

                foreach ($tblPrepareInformationList as $tblPrepareInformation) {
                    if ($tblPrepareInformation->getField() == 'Team') {
                        if ($tblPrepareInformation->getValue() != '') {
                            $team = 'Arbeitsgemeinschaften: ' . $tblPrepareInformation->getValue();
                            $teamChange = $tblPrepareInformation->getValue();
                        }
                    } elseif ($tblPrepareInformation->getField() == 'Remark') {
                        $remark = $tblPrepareInformation->getValue();
                    } elseif ($tblPrepareInformation->getField() == 'Transfer') {
                        $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $tblPerson->getFirstSecondName()
                            . ' ' . $tblPerson->getLastName() . ' ' . $tblPrepareInformation->getValue();
                    } elseif ($tblPrepareInformation->getField() == 'IndividualTransfer') {
                        // SSWHD-262
                        if ($tblConsumer && $tblConsumer->getAcronym() == 'ESZC') {
                            $text = '';
                        } else {
                            $text = $tblPerson->getFirstSecondName() . ' ';
                        }

                        $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $text . $tblPrepareInformation->getValue();
                    } elseif ($isSupportLearningCertificate && $tblPrepareInformation->getField() == 'Support') {
                        $support = $tblPrepareInformation->getValue();
                    } else {
                        $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $tblPrepareInformation->getValue();
                    }
                }

                // Spezialfall für Förderzeugnisse Lernen
                if ($isSupportLearningCertificate) {
                    $remark = ($team ? $team . " \n " : '')
                        . ($support ? 'Inklusive Unterrichtung¹: ' . $support : 'Inklusive Unterrichtung¹: ' . '---' )
                        . " \n " . ($remark ? 'Bemerkung: ' . $remark : '');
                } else {
                    // Streichung leeres Bemerkungsfeld
                    if ($hasRemarkBlocking && $remark == '') {
                        $remark = '---';
                    }

                    if ($team || $remark) {
                        if ($team) {
                            if ($tblConsumer->getAcronym() == 'EVSR') {
                                // Arbeitsgemeinschaften am Ende der Bemerkungnen
                                $remark = $remark . " \n\n " . $team;
                            } elseif (($tblConsumer = Consumer::useService()->getConsumerBySession())
                                && $tblConsumer->getAcronym() == 'ESZC'
                                && $tblLevel && intval($tblLevel->getName()) <= 4
                            ) {
                                $remark = $teamChange . " \n " . $remark;
                            } else {
                                $remark = $team . " \n\n " . $remark;
                            }
                        }
                    }
                }

                $Content['P' . $personId]['Input']['Remark'] = $remark;
            } else {
                if ($isSupportLearningCertificate) {
                    $Content['P' . $personId]['Input']['Remark'] = 'Inklusive Unterrichtung¹: ---';
                } elseif ($hasRemarkBlocking) {
                    $Content['P' . $personId]['Input']['Remark'] = '---';
                } else {
                    $Content['P' . $personId]['Input']['Remark'] = '';
                }
            }
        }

        // Klassenlehrer
        $isDivisionTeacherAvailable = false;
        if ($tblPrepare) {
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())) {
                $isDivisionTeacherAvailable = $tblGenerateCertificate->isDivisionTeacherAvailable();
            }
        } elseif ($tblLeaveStudent) {
            $isDivisionTeacherAvailable = true;
            if ($personSignerInformation = $this->getLeaveInformationBy($tblLeaveStudent, 'DivisionTeacher')) {
                $tblPersonSigner = Person::useService()->getPersonById($personSignerInformation->getValue());
            }
        }
        // Todo als Mandanteneinstellung umbauen
        if ($tblPersonSigner) {
            $divisionTeacherDescription = 'Klassenlehrer';

            if($isDivisionTeacherAvailable){
                $ConsumerAcronym = '';
                $tblConsumer = Consumer::useService()->getConsumerBySession();
                if($tblConsumer){
                    $ConsumerAcronym = $tblConsumer->getAcronym();
                }
                switch ($ConsumerAcronym) {
                    case 'EVSR':
                        $firstName = $tblPersonSigner->getFirstName();
                        if (strlen($firstName) > 1) {
                            $firstName = substr($firstName, 0, 1) . '.';
                        }
                        $Content['P' . $personId]['DivisionTeacher']['Name'] = $firstName . ' '
                            . $tblPersonSigner->getLastName();
                    break;
                    case 'ESZC':
                        $Content['P' . $personId]['DivisionTeacher']['Name'] = trim($tblPersonSigner->getSalutation()
                            . " " . $tblPersonSigner->getLastName());
                    break;
                    case 'EVSC':
                    case 'EMSP':
                        $Content['P' . $personId]['DivisionTeacher']['Name'] = trim($tblPersonSigner->getFirstName()
                            . " " . $tblPersonSigner->getLastName());
                        $divisionTeacherDescription = 'Klassenleiter';
                    break;
                    case 'EGE':
                        $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                        if ($tblLevel
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                            && ($level = intval($tblLevel->getName()))
                            && $level < 9
                        ) {
                            $divisionTeacherDescription = 'Gruppenleiter';
                        }
                    break;
                    case 'EVAMTL':
                        $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                        if ($tblLevel
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getName() != 'Grundschule'
                        ){
                            $divisionTeacherDescription = 'Mentor';
                        }
                    break;
                    case 'CSW':
                        if ($tblLevel
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                        ) {
                            $Content['P' . $personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFirstSecondName()
                                . ' ' . $tblPersonSigner->getLastName();
                        } else {
                            $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                        }
                    break;
                    case 'FESH':
                    case 'ESS':
                        $Content['P' . $personId]['DivisionTeacher']['Name'] = trim($tblPersonSigner->getFirstName()
                            . " " . $tblPersonSigner->getLastName());
                    break;
                    default:
                        $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                    break;
                }
            }

            if (($genderValue = $this->getGenderByPerson($tblPersonSigner))) {
                $Content['P' . $personId]['DivisionTeacher']['Gender'] = $genderValue;
                if ($genderValue == 'M') {
                    $Content['P' . $personId]['DivisionTeacher']['Description'] = $divisionTeacherDescription;
                    $Content['P' . $personId]['Tudor']['Description'] = 'Tutor';
                } elseif ($genderValue == 'F') {
                    $Content['P' . $personId]['DivisionTeacher']['Description'] = $divisionTeacherDescription . 'in';
                    $Content['P' . $personId]['Tudor']['Description'] = 'Tutorin';
                }
            }
        }

        // Schulleitung
        if ($tblPrepare && ($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())) {
            if ($tblGenerateCertificate->getHeadmasterName()
                && $tblGenerateCertificate) {
                $Content['P' . $personId]['Headmaster']['Name'] = $tblGenerateCertificate->getHeadmasterName();
            }
            if (($tblCommonGender = $tblGenerateCertificate->getServiceTblCommonGenderHeadmaster())
                && $tblGenerateCertificate->isDivisionTeacherAvailable()) {
                if ($tblCommonGender->getName() == 'Männlich') {
                    $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiter';
                } elseif ($tblCommonGender->getName() == 'Weiblich') {
                    $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiterin';
                }
            }
        }

        if ($tblPrepare) {
            // Kopfnoten
            $tblPrepareGradeBehaviorList = Prepare::useService()->getPrepareGradeAllByPerson(
                $tblPrepare,
                $tblPerson,
                Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'),
                true
            );
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($tblPrepareGradeBehaviorList) {
                foreach ($tblPrepareGradeBehaviorList as $tblPrepareGrade) {
                    if ($tblPrepareGrade->getServiceTblGradeType()) {
                        if ($isGradeVerbal) {
                            $grade = $this->getVerbalGrade($tblPrepareGrade->getGrade());
                            $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblPrepareGrade->getServiceTblGradeType()->getCode()] = true;
                        } else {
                            $grade = $tblPrepareGrade->getGrade();
                        }

                        $Content['P' . $personId]['Input'][$tblPrepareGrade->getServiceTblGradeType()->getCode()] = $grade;
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
                            $Content['P' . $personId]['Input']['BehaviorTeacher'][$testItem->getServiceTblSubject()->getAcronym()]
                            [$testItem->getServiceTblGradeType()->getCode()] = $tblGrade->getDisplayGrade();
                        }
                    }
                }
            }

            // Fachnoten
            if ($tblPrepare->isGradeInformation() || ($tblPrepareStudent && !$tblPrepareStudent->isApproved())) {
                // Abschlusszeugnisse
                if ($tblGenerateCertificate
                    && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                    && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                ) {
                    // Abiturnoten werden direkt im Certificate in der API gedruckt
                    if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('EN'))
                        && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy(
                            $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                            if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                                if ($isGradeVerbalOnDiploma) {
                                    $grade = $this->getVerbalGrade($tblPrepareAdditionalGrade->getGrade());
                                    $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                                } else {
                                    $grade = $tblPrepareAdditionalGrade->getGrade();
                                    if ((Gradebook::useService()->getGradeTextByName($grade))
                                        && $grade != 'befreit'
                                    ) {
                                        $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                                    }
                                }

                                $Content['P' . $personId]['Grade']['Data'][$tblSubject->getAcronym()]
                                    = $grade;
                            }
                        }
                    }
                } else {
                    if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                        && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask))
                    ) {
                        foreach ($tblTestList as $tblTest) {
                            if (($tblGradeItem = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                    $tblPerson))
                                && $tblTest->getServiceTblSubject()
                            ) {
                                // keine Tendenzen auf Zeugnissen
                                $withTrend = true;
                                if ($tblPrepareStudent
                                    && $tblCertificate
                                    && !$tblCertificate->isInformation()
                                ) {
                                    $withTrend = false;
                                }

                                // Zensuren im Wortlaut
                                if ($isGradeVerbal) {
                                    if ($tblGradeItem->getTblGradeText()) {
                                        $Content['P' . $personId]['Grade']['Data'][$tblTest->getServiceTblSubject()->getAcronym()]
                                            = $tblGradeItem->getTblGradeText()->getName();
                                    } else {
                                        $Content['P' . $personId]['Grade']['Data'][$tblTest->getServiceTblSubject()->getAcronym()]
                                            = $this->getVerbalGrade($tblGradeItem->getGrade());
                                        $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblTest->getServiceTblSubject()->getAcronym()] = true;
                                    }
                                } else {
                                    $Content['P' . $personId]['Grade']['Data'][$tblTest->getServiceTblSubject()->getAcronym()]
                                        = $tblGradeItem->getDisplayGrade($withTrend);
                                }

                                // bei Zeugnistext als Note Schriftgröße verkleinern
                                if ($tblGradeItem->getTblGradeText()
                                    && $tblGradeItem->getTblGradeText()->getName() != 'befreit'
                                ) {
                                    $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblTest->getServiceTblSubject()->getAcronym()] = true;
                                }
                            }
                        }
                    }
                }
            } else {
                $tblPrepareGradeSubjectList = Prepare::useService()->getPrepareGradeAllByPerson(
                    $tblPrepare,
                    $tblPerson,
                    Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'),
                    true
                );
                if ($tblPrepareGradeSubjectList) {
                    foreach ($tblPrepareGradeSubjectList as $tblPrepareGrade) {
                        if (($tblSubject = $tblPrepareGrade->getServiceTblSubject())) {
                            if ($isGradeVerbalOnDiploma) {
                                $grade = $this->getVerbalGrade($tblPrepareGrade->getGrade());
                                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                            } elseif ($isGradeVerbal) {
                                $grade = $this->getVerbalGrade($tblPrepareGrade->getGrade());
                                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                            } else {
                                // bei Zeugnistext als Note Schriftgröße verkleinern
                                if (Gradebook::useService()->getGradeTextByName($tblPrepareGrade->getGrade())
                                    && $tblPrepareGrade->getGrade() != 'befreit'
                                ) {
                                    $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblPrepareGrade->getServiceTblSubject()->getAcronym()] = true;
                                }
                                $grade = $tblPrepareGrade->getGrade();
                            }

                            $Content['P' . $personId]['Grade']['Data'][$tblPrepareGrade->getServiceTblSubject()->getAcronym()]
                                = $grade;
                        }
                    }
                }
            }

            // Fachnoten von abgewählten Fächern vom Vorjahr
            if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy($tblPrepare, $tblPerson,
                    $tblPrepareAdditionalGradeType))
            ) {
                foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                    if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                        if ($isGradeVerbalOnDiploma) {
                            $grade = $this->getVerbalGrade($tblPrepareAdditionalGrade->getGrade());
                            $Content['P' . $personId]['AdditionalGrade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                        } else {
                            $grade = $tblPrepareAdditionalGrade->getGrade();
                        }

                        $Content['P' . $personId]['AdditionalGrade']['Data'][$tblSubject->getAcronym()]
                            = $grade;
                    }
                }
            }
        }

        // Fehlzeiten
        if ($tblPrepareStudent) {
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
            $Content['P' . $personId]['Input']['Missing'] = $excusedDays;
            $Content['P' . $personId]['Input']['Bad']['Missing'] = $unexcusedDays;
            $Content['P' . $personId]['Input']['Total']['Missing'] = $excusedDays + $unexcusedDays;
        }

        // Zeugnisdatum
        if ($tblPrepare) {
            $Content['P' . $personId]['Input']['Date'] = $tblPrepare->getDate();
        }

        if ($tblPrepareStudent) {
            if ($tblCertificate && $tblCertificate->getName() == 'Bildungsempfehlung') {
                // Notendurchschnitt der angegebenen Fächer für Bildungsempfehlung
                $average = $this->calcSubjectGradesAverage($tblPrepareStudent);
                if ($average) {
                    $Content['P' . $personId]['Grade']['Data']['Average'] = number_format($average, 1, ',', '.');
                    //str_replace('.', ',', $average);
                }

                // Notendurchschnitt aller anderen Fächer für Bildungsempfehlung
                $average = $this->calcSubjectGradesAverageOthers($tblPrepareStudent);
                if ($average) {
                    $Content['P' . $personId]['Grade']['Data']['AverageOthers'] = number_format($average, 1, ',', '.');
                }
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
                    $Content['P' . $personId]['Student']['Advanced'][$tblSubjectAdvanced->getAcronym()]['Name'] = $tblSubjectAdvanced->getName();
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
                    $Content['P' . $personId]['Student']['Orientation'][
                        $tblSubjectOrientation->getAcronym()]['Name'] = $tblSubjectOrientation->getName();
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
                        $Content['P' . $personId]['Student']['ForeignLanguage'][
                            $tblSubjectForeignLanguage->getAcronym()]['Name'] = $tblSubjectForeignLanguage->getName();
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
                    $Content['P' . $personId]['Student']['Profile'][$tblSubjectProfile->getAcronym()]['Name']
                        = str_replace('Profil', '', $tblSubjectProfile->getName());

                    // für Herrnhut EZSH Anpassung des Profilnamens
                    $profile = $tblSubjectProfile->getName();
                    $profile = str_replace('gesellschaftswissenschaftliches Profil /', '', $profile);
                    $profile = str_replace('naturwissenschaftlich-mathematisches Profil /', '', $profile);
                    $profile = trim(str_replace('"', '', $profile));
                    $Content['P' . $personId]['Student']['ProfileEZSH'][$tblSubjectProfile->getAcronym()]['Name']
                        = $profile;
                }
            }
        }

        // Abgangszeugnisse
        if ($tblLeaveStudent) {
            if (($tblLeaveGradeList = $this->getLeaveGradeAllByLeaveStudent($tblLeaveStudent))) {
                foreach ($tblLeaveGradeList as $tblLeaveGrade) {
                    if (($tblSubject = $tblLeaveGrade->getServiceTblSubject())) {
                        if (($tblSetting = ConsumerSetting::useService()->getSetting(
                                'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnLeave'))
                            && $tblSetting->getValue()
                        ) {
                            $grade = $this->getVerbalGrade($tblLeaveGrade->getGrade());
                            $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                        } else {
                            // bei Zeugnistext als Note Schriftgröße verkleinern
                            if (Gradebook::useService()->getGradeTextByName($tblLeaveGrade->getGrade())
                                && $tblLeaveGrade->getGrade() != 'befreit'
                            ) {
                                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblLeaveGrade->getServiceTblSubject()->getAcronym()] = true;
                            }
                            $grade = $tblLeaveGrade->getGrade();
                        }

                        $Content['P' . $personId]['Grade']['Data'][$tblSubject->getAcronym()] = $grade;
                    }
                }
            }

            // Gleichgestellter Schulabschluss - GymAbgSekI, MsAbg
            if (($tblLeaveInformationEqualGraduation = $this->getLeaveInformationBy($tblLeaveStudent, 'EqualGraduation'))) {
                if ($tblLeaveInformationEqualGraduation->getValue() == GymAbgSekI::COURSE_RS) {
                    $Content['P' . $personId]['Input']['EqualGraduation']['RS'] = true;
                } elseif ($tblLeaveInformationEqualGraduation->getValue() == GymAbgSekI::COURSE_HS) {
                    $Content['P' . $personId]['Input']['EqualGraduation']['HS'] = true;
                } elseif ($tblLeaveInformationEqualGraduation->getValue() == GymAbgSekI::COURSE_HSQ) {
                    $Content['P' . $personId]['Input']['EqualGraduation']['HSQ'] = true;
                }
            }

            // Bemerkungen
            $remark = '---';
            if (($tblLeaveInformationRemark = $this->getLeaveInformationBy($tblLeaveStudent, 'Remark'))) {
                $remark = $tblLeaveInformationRemark->getValue() ? $tblLeaveInformationRemark->getValue() : $remark;
            }
            $Content['P' . $personId]['Input']['Remark'] = $remark;

            // Inklusive Unterrichtung
            $support = '---';
            if (($tblLeaveInformationSupport = $this->getLeaveInformationBy($tblLeaveStudent, 'Support'))) {
                $support = $tblLeaveInformationSupport->getValue() ? $tblLeaveInformationSupport->getValue() : $remark;
            }
            $Content['P' . $personId]['Input']['Support'] = $support;

            $remarkWithoutTeam = '---';
            if (($tblLeaveInformationRemarkWithoutTeam = $this->getLeaveInformationBy($tblLeaveStudent, 'RemarkWithoutTeam'))) {
                $remarkWithoutTeam = $tblLeaveInformationRemarkWithoutTeam->getValue() ? $tblLeaveInformationRemarkWithoutTeam->getValue() : $remarkWithoutTeam;
            }
            $Content['P' . $personId]['Input']['RemarkWithoutTeam'] = $remarkWithoutTeam;

            $arrangement = '---';
            if (($tblLeaveInformationArrangement = $this->getLeaveInformationBy($tblLeaveStudent, 'Arrangement'))) {
                $arrangement = $tblLeaveInformationArrangement->getValue() ? $tblLeaveInformationArrangement->getValue() : $arrangement;
            }
            $Content['P' . $personId]['Input']['Arrangement'] = $arrangement;

            // Zeugnisdatum
            if (($tblLeaveInformationCertificateDate = $this->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                $Content['P' . $personId]['Input']['Date'] = $tblLeaveInformationCertificateDate->getValue();
            }

            // Headmaster
            if (($tblLeaveInformationHeadmasterName = $this->getLeaveInformationBy($tblLeaveStudent, 'HeadmasterName'))) {
                $Content['P' . $personId]['Headmaster']['Name'] = $tblLeaveInformationHeadmasterName->getValue();
            }
            if (($tblLeaveInformationHeadmasterGender = $this->getLeaveInformationBy($tblLeaveStudent, 'HeadmasterGender'))) {
                if (($tblCommonGender = Common::useService()->getCommonGenderById($tblLeaveInformationHeadmasterGender->getValue()))) {
                    if ($tblCommonGender->getName() == 'Männlich') {
                        $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiter';
                    } elseif ($tblCommonGender->getName() == 'Weiblich') {
                        $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiterin';
                    }
                }
            }
        }

        return $Content;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGroup|null $tblGroup
     *
     * @return array
     */
    public function getCertificateMultiContent(TblPrepareCertificate $tblPrepare, TblGroup $tblGroup = null)
    {

        $Content = array();

        $tblPrepareList = false;

        $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
        if ($tblGroup) {
            if (($tblGenerateCertificate)) {
                $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
            }
        } else {
            if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                $tblPrepareList = array(0 => $tblPrepare);
            }
        }

        if ($tblPrepareList) {
            foreach ($tblPrepareList as $tblPrepareItem) {
                if (($tblDivision = $tblPrepareItem->getServiceTblDivision())
                    && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
                ) {
                    foreach ($tblStudentList as $tblPerson) {
                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson))) {
                                $Content = $this->createCertificateContent($tblPrepareItem, null, $tblPerson, $Content);
                            }
                        }
                    }
                }
            }
        }

        return $Content;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function getCertificateMultiLeaveContent(TblDivision $tblDivision) {
        $Content = array();

        if (($tblLeaveStudentList = $this->getLeaveStudentAllByDivision($tblDivision))) {
            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())) {
                    $Content = $this->createCertificateContent(null, $tblLeaveStudent, $tblPerson, $Content);
                }
            }
        }

        return $Content;
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
            $Content = $this->createCertificateContent($tblPrepare, null, $tblPerson, $Content);
        }

        return $Content;
    }

    public function getLeaveCertificateContent(TblLeaveStudent $tblLeaveStudent) {
        $Content = array();
        if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())) {
            $Content = $this->createCertificateContent(null, $tblLeaveStudent, $tblPerson, $Content);
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
                        $tblPrepareGrade = false;
                        if ($tblPrepareStudent->isApproved()) {
                            // kopierte Zenur
                            $tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject(
                                $tblPrepare, $tblPerson, $tblDivision, $tblSubject,
                                Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
                            );
                        } elseif ($tblPrepare->getServiceTblAppointedDateTask()) {
                            // Zensur aus dem Stichtagsnotenauftrag
                            $tblTestList = Evaluation::useService()->getTestListBy($tblDivision, $tblSubject,
                                $tblPrepare->getServiceTblAppointedDateTask());
                            if ($tblTestList) {
                                foreach ($tblTestList as $tblTest) {
                                    $tblPrepareGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                        $tblPerson);
                                    if ($tblPrepareGrade) {
                                        break;
                                    }
                                }
                            }
                        }
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

            $gradeList = array();

            if ($tblPrepareStudent->isApproved()) {
                // kopierte Zenuren
                $tblPrepareGradeList = $this->getPrepareGradeAllByPerson(
                    $tblPrepare,
                    $tblPerson,
                    Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
                );
                if ($tblPrepareGradeList) {
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
                }
            } elseif ($tblPrepare->getServiceTblAppointedDateTask()) {
                // Zensur aus dem Stichtagsnotenauftrag
                $tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblAppointedDateTask(),
                    $tblDivision);
                if ($tblTestList) {
                    foreach ($tblTestList as $tblTest) {
                        if (($tblSubject = $tblTest->getServiceTblSubject())) {
                            $tblCertificateSubject = Generator::useService()->getCertificateSubjectBySubject(
                                $tblCertificate,
                                $tblSubject
                            );
                            $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                            if (!$tblCertificateSubject
                                && $tblGrade && $tblGrade->getGrade() != ''
                            ) {
                                $grade = str_replace('+', '', $tblGrade->getGrade());
                                $grade = str_replace('-', '', $grade);
                                if (is_numeric($grade)) {
                                    $gradeList[] = $grade;
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($gradeList)) {
                return round(floatval(array_sum($gradeList) / count($gradeList)), 1);
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
                $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
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
     * @param $Data
     */
    public function createPrepareStudentSetBulkTemplates(
        $Data
    ) {

        (new Data($this->getBinding()))->createPrepareStudentSetBulkTemplates($Data);
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

        $personId = $tblPerson->getId();

        if (isset($Content['P' . $personId]['Input']) && is_array($Content['P' . $personId]['Input'])) {
            foreach ($Content['P' . $personId]['Input'] as $field => $value) {
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
     * @param TblGroup|null $tblGroup
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
        TblGroup $tblGroup = null,
        TblGradeType $tblGradeType,
        TblGradeType $tblNextGradeType = null,
        $Route,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Stage;
        }

        $error = false;

        foreach ($Data as $gradeTypeId => $value) {
            if (trim($value) !== '') {
                if (!preg_match('!^([1-5]{1}|[1-4]{1}[+-]{1})$!is', trim($value))) {
                    $error = true;
                    break;
                }
            }
        }

        if ($error) {
            $Stage->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1-5).
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $Stage;
        } else {
            if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'))) {
                foreach ($Data as $prepareStudentId => $value) {
                    if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                        && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                        && ($tblDivision = $tblPrepareItem->getServiceTblDivision())
                        && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                    ) {

                        $this->setSignerFromSignedInPerson($tblPrepareStudent);

                        if ($value != -1) {
                            if (trim($value) === '') {
                                // keine leere Kopfnoten anlegen, nur falls eine Kopfnote vorhanden ist
                                // direktes löschen ist ungünstig, da beim nächsten Speichern wieder der Durchschnitt eingetragen würde
                                if (($tblPrepareGrade = $this->getPrepareGradeByGradeType(
                                    $tblPrepareItem, $tblPerson, $tblDivision, $tblTestType, $tblGradeType
                                ))) {
                                    Prepare::useService()->updatePrepareGradeForBehavior(
                                        $tblPrepareItem, $tblPerson, $tblDivision, $tblTestType, $tblGradeType,
                                        trim($value)
                                    );
                                }
                            } else {
                                Prepare::useService()->updatePrepareGradeForBehavior(
                                    $tblPrepareItem, $tblPerson, $tblDivision, $tblTestType, $tblGradeType,
                                    trim($value)
                                );
                            }
                        }
                    }
                }

                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnoten wurden gespeichert.')
                    . new Redirect('/Education/Certificate/Prepare/Prepare/Setting',
                        Redirect::TIMEOUT_SUCCESS,
                        $tblNextGradeType ? array(
                            'PrepareId' => $tblPrepare->getId(),
                            'GroupId' => $tblGroup ? $tblGroup : null,
                            'Route' => $Route,
                            'GradeTypeId' => $tblNextGradeType->getId()
                        )
                            : array(
                            'PrepareId' => $tblPrepare->getId(),
                            'GroupId' => $tblGroup ? $tblGroup : null,
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
     * @param TblPrepareStudent $tblPrepareStudent
     */
    private function setSignerFromSignedInPerson(TblPrepareStudent $tblPrepareStudent)
    {

        if (!$tblPrepareStudent->getServiceTblPersonSigner()
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
            && $tblGenerateCertificate->isDivisionTeacherAvailable()
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
                (new Data($this->getBinding()))->updatePrepareStudent(
                    $tblPrepareStudent,
                    $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : null,
                    $tblPrepareStudent->isApproved(),
                    $tblPrepareStudent->isPrinted(),
                    $tblPrepareStudent->getExcusedDays(),
                    $tblPrepareStudent->getUnexcusedDays(),
                    $tblPerson
                );
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
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && ($tblCertificateType =$tblGenerateCertificate->getServiceTblCertificateType())
                && $tblCertificateType->getIdentifier() == 'DIPLOMA'
            ) {
                $isDiploma = true;
            } else {
                $isDiploma = false;
            }

//            if (!$tblGenerateCertificate->isLocked()) {
//
//                Generate::useService()->lockGenerateCertificate($tblGenerateCertificate, true);
//            }

            return (new Data($this->getBinding()))->copySubjectGradesByPrepare($tblPrepare, $isDiploma);
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

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function isPreparePrinted(TblPrepareCertificate $tblPrepareCertificate)
    {

        return (new Data($this->getBinding()))->isPreparePrinted($tblPrepareCertificate);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllByPrepare(TblPrepareCertificate $tblPrepareCertificate)
    {

        return (new Data($this->getBinding()))->getPrepareStudentAllByPrepare($tblPrepareCertificate);
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->isGradeTypeUsed($tblGradeType);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return null|string
     * male -> M | female -> F | nothing -> false
     */
    private function getGenderByPerson(TblPerson $tblPerson)
    {

        $return = false;
        if (($tblCommonTeacher = $tblPerson->getCommon())) {
            if (($tblCommonBirthDates = $tblCommonTeacher->getTblCommonBirthDates())) {
                if (($tblCommonGenderTeacher = $tblCommonBirthDates->getTblCommonGender())) {
                    if ($tblCommonGenderTeacher->getName() == 'Männlich') {
                        $return = 'M';
                    } else {
                        $return = 'F';
                    }
                }
            }
        }
        if ($return == false) {
            if ($tblPerson->getSalutation() == 'Herr') {
                $return = 'M';
            } elseif ($tblPerson->getSalutation() == 'Frau') {
                $return = 'F';
            }
        }

        return $return;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param $ranking
     * @param $grade
     * @param bool $isSelected
     * @param bool $isLocked
     *
     * @return TblPrepareAdditionalGrade
     */
    public function createPrepareAdditionalGrade(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking,
        $grade,
        $isSelected = false,
        $isLocked = false
    ) {

        return (new Data($this->getBinding()))->createPrepareAdditionalGrade($tblPrepareCertificate, $tblPerson,
            $tblSubject, $tblPrepareAdditionalGradeType, $ranking, $grade, $isSelected, $isLocked);
    }

    /**
     * @param IFormInterface $Form
     * @param $Data
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblGroup|null $tblGroup
     * @param TblPerson $tblPerson
     * @param $Route
     *
     * @return IFormInterface|string
     */
    public function createPrepareAdditionalGradeForm(
        IFormInterface $Form,
        $Data,
        TblPrepareCertificate $tblPrepareCertificate,
        TblGroup $tblGroup = null,
        TblPerson $tblPerson,
        $Route
    ) {

        /**
         * Service
         */
        if ($Data === null) {
            return $Form;
        }

        $Error = false;
        $tblSubject = false;

        if (!isset($Data['Subject']) || !(($tblSubject = Subject::useService()->getSubjectById($Data['Subject'])))) {
            $Form->setError('Data[Subject]', 'Bitte wählen Sie ein Fach aus');
            $Error = true;
        }
        if (!isset($Data['Grade']) || empty($Data['Grade'])) {
            // Todo Zenuren Wertebereich
            $Form->setError('Data[Grade]', 'Bitte geben Sie eine Zensur ein');
            $Error = true;
        }

        if ($Error) {
            return $Form . new Notify(
                    'Fach konnte nicht angelegt werden',
                    'Bitte füllen Sie die benötigten Felder korrekt aus',
                    Notify::TYPE_WARNING,
                    5000
                );
        } else {

            if ($tblSubject
                && ($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            ) {

                if ($this->createPrepareAdditionalGrade(
                    $tblPrepareCertificate,
                    $tblPerson,
                    $tblSubject,
                    $tblPrepareAdditionalGradeType,
                    $this->getMaxRanking(
                        $tblPrepareCertificate, $tblPerson
                    ),
                    $Data['Grade'])
                ) {
                    return new Success('Das Fach wurde erfolgreich angelegt',
                            new \SPHERE\Common\Frontend\Icon\Repository\Success())
                        . new Redirect('/Education/Certificate/Prepare/DroppedSubjects', Redirect::TIMEOUT_SUCCESS,
                            array(
                                'PrepareId' => $tblPrepareCertificate->getId(),
                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                'PersonId' => $tblPerson->getId(),
                                'Route' => $Route
                            ));
                }
            }
        }

        return new Danger('Das Fach konnte nicht angelegt werden');
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblPrepareAdditionalGradeType|null $tblPrepareAdditionalGradeType
     *
     * @return false|TblPrepareAdditionalGrade[]
     */
    public function getPrepareAdditionalGradeListBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType = null
    ) {

        return (new Data($this->getBinding()))->getPrepareAdditionalGradeListBy($tblPrepareCertificate,
            $tblPerson, $tblPrepareAdditionalGradeType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function setAutoDroppedSubjects(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {

        $gradeString = '';
        $tblLastDivision = false;
        $tblCurrentDivision = $tblPrepare->getServiceTblDivision();
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && (!$tblLevel->getIsChecked())
                    && ($tblLevel->getName() == '9' || $tblLevel->getName() == '09')
                ) {
                    $tblLastDivision = $tblDivision;
                    break;
                }
            }
        }

        if ($tblLastDivision
            && $tblCurrentDivision
            && ($tblLastYear = $tblLastDivision->getServiceTblYear())
            && ($tblCurrentYear = $tblCurrentDivision->getServiceTblYear())
            && ($tblLastDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson,
                $tblLastYear))
            && ($tblCurrentDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson,
                $tblCurrentYear))
        ) {
            $tblLastSubjectList = array();
            foreach ($tblLastDivisionSubjectList as $tblLastDivisionSubject) {
                if (($tblSubject = $tblLastDivisionSubject->getServiceTblSubject())) {
                    $tblLastSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }

            $tblCurrentSubjectList = array();
            foreach ($tblCurrentDivisionSubjectList as $tblCurrentDivisionSubject) {
                if (($tblSubject = $tblCurrentDivisionSubject->getServiceTblSubject())) {
                    $tblCurrentSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }

            $diffList = array();
            foreach ($tblLastSubjectList as $tblLastSubject) {
                if (!isset($tblCurrentSubjectList[$tblLastSubject->getId()])) {
                    $diffList[$tblLastSubject->getAcronym()] = $tblLastSubject;
                }
            }

            $tblLastPrepare = false;
            if (($tblLastPrepareList = Prepare::useService()->getPrepareAllByDivision($tblLastDivision))) {
                foreach ($tblLastPrepareList as $tblPrepareCertificate) {
                    if (($tblGenerateCertificate = $tblPrepareCertificate->getServiceTblGenerateCertificate())
                        && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                        && $tblCertificateType->getIdentifier() == 'YEAR'
                    ) {
                        $tblLastPrepare = $tblPrepareCertificate;
                    }
                }
            }

            if (empty($diffList)) {
                return false;
            } else {
                /** @var TblSubject $item */
                $count = 1;
                ksort($diffList);
                if ($tblLastPrepare) {
                    foreach ($diffList as $item) {
                        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
                        $tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject($tblLastPrepare, $tblPerson,
                            $tblLastDivision, $item, $tblTestType);
                        if ($tblTestType
                            && $tblPrepareGrade
                            && ($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                        ) {
                            $tblPrepareAdditionalGrade = Prepare::useService()->createPrepareAdditionalGrade(
                                $tblPrepare,
                                $tblPerson,
                                $item,
                                $tblPrepareAdditionalGradeType,
                                $count++,
                                $tblPrepareGrade->getGrade()
                            );

                            $gradeString .= $item->getAcronym() . ':' . $tblPrepareAdditionalGrade->getGrade() . ' ';
                        }
                    }
                }
            }
        }

        return $gradeString;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblCurrentDivision
     *
     * @return string[]|false
     */
    public function getAutoDroppedSubjects(TblPerson $tblPerson, TblDivision $tblCurrentDivision)
    {

        $subjectList = array();
        $tblLastDivision = false;
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && (!$tblLevel->getIsChecked())
                    && ($tblLevel->getName() == '9' || $tblLevel->getName() == '09')
                ) {
                    $tblLastDivision = $tblDivision;
                    break;
                }
            }
        }

        if ($tblLastDivision
            && ($tblLastYear = $tblLastDivision->getServiceTblYear())
            && ($tblCurrentYear = $tblCurrentDivision->getServiceTblYear())
            && ($tblLastDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson,
                $tblLastYear))
            && ($tblCurrentDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson,
                $tblCurrentYear))
        ) {
            $tblLastSubjectList = array();
            foreach ($tblLastDivisionSubjectList as $tblLastDivisionSubject) {
                if (($tblSubject = $tblLastDivisionSubject->getServiceTblSubject())) {
                    $tblLastSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }

            $tblCurrentSubjectList = array();
            foreach ($tblCurrentDivisionSubjectList as $tblCurrentDivisionSubject) {
                if (($tblSubject = $tblCurrentDivisionSubject->getServiceTblSubject())) {
                    $tblCurrentSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }

            $diffList = array();
            foreach ($tblLastSubjectList as $tblLastSubject) {
                if (!isset($tblCurrentSubjectList[$tblLastSubject->getId()])) {
                    $diffList[$tblLastSubject->getAcronym()] = $tblLastSubject;
                }
            }

            $tblLastPrepare = false;
            if (($tblLastPrepareList = Prepare::useService()->getPrepareAllByDivision($tblLastDivision))) {
                foreach ($tblLastPrepareList as $tblPrepareCertificate) {
                    if (($tblGenerateCertificate = $tblPrepareCertificate->getServiceTblGenerateCertificate())
                        && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                        && $tblCertificateType->getIdentifier() == 'YEAR'
                    ) {
                        $tblLastPrepare = $tblPrepareCertificate;
                    }
                }
            }

            if (empty($diffList)) {
                return false;
            } else {
                /** @var TblSubject $item */
                $count = 1;
                ksort($diffList);
                if ($tblLastPrepare) {
                    foreach ($diffList as $item) {
                        if (!Subject::useService()->isOrientation($item) && $item->getName() != 'Neigungskurs') {
                            $subjectList[$item->getId()] = $item->getName();
                        }
                    }
                }
            }
        }

        return empty($subjectList) ? false : $subjectList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return int
     */
    private function getMaxRanking(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson
    ) {

        if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            && $list = (new Data($this->getBinding()))->getPrepareAdditionalGradeListBy($tblPrepareCertificate,
                $tblPerson, $tblPrepareAdditionalGradeType)
        ) {

            $item = end($list);

            return $item->getRanking() + 1;
        }

        return 1;
    }

    /**
     * @param $Id
     *
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeById($Id)
    {

        return (new Data($this->getBinding()))->getPrepareAdditionalGradeById($Id);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     *
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
    ) {

        return (new Data($this->getBinding()))->getPrepareAdditionalGradeBy(
            $tblPrepareCertificate,
            $tblPerson,
            $tblSubject,
            $tblPrepareAdditionalGradeType
        );
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     *
     * @return bool
     */
    public function destroyPrepareAdditionalGrade(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade)
    {

        return (new Data($this->getBinding()))->destroyPrepareAdditionalGrade($tblPrepareAdditionalGrade);
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $Ranking
     *
     * @return bool
     */
    public function updatePrepareAdditionalGradeRanking(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade, $Ranking)
    {

        return (new Data($this->getBinding()))->updatePrepareAdditionalGradeRanking($tblPrepareAdditionalGrade,
            $Ranking);
    }

//    /**
//     * @param TblPrepareCertificate $tblPrepare
//     */
//    public function hasDiplomaCertificate(TblPrepareCertificate $tblPrepare)
//    {
//
//        if (($tblDivision = $tblPrepare->getServiceTblDivision())
//            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
//        ) {
//            foreach ($tblPersonList as $tblPerson) {
//                if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))
//                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
//                    && ($tblCertificateType = $tblCertificate->getTblCertificateType())
//                    && $tblCertificateType->getIdentifier() == 'DIPLOMA'
//                ) {
//
//                }
//            }
//        }
//    }

    /**
     * @param $Identifier
     *
     * @return bool|TblPrepareAdditionalGradeType
     */
    public function getPrepareAdditionalGradeTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getPrepareAdditionalGradeTypeByIdentifier($Identifier);
    }

    /**
     * @param $Id
     *
     * @return bool|TblPrepareAdditionalGradeType
     */
    public function getPrepareAdditionalGradeTypeById($Id)
    {

        return (new Data($this->getBinding()))->getPrepareAdditionalGradeTypeById($Id);
    }

    /**
     * @param IFormInterface|null $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblSubject $tblCurrentSubject
     * @param TblSubject|null $tblNextSubject
     * @param null|bool $IsFinalGrade
     * @param $Route
     * @param $Data
     * @param TblGroup|null $tblGroup
     *
     * @return IFormInterface|string
     */
    public function updatePrepareExamGrades(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        TblSubject $tblCurrentSubject,
        TblSubject $tblNextSubject = null,
        $IsFinalGrade = null,
        $Route,
        $Data,
        TblGroup $tblGroup = null
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        $error = false;

        if ($Data != null) {
            foreach ($Data as $personGrades) {
                if (is_array($personGrades)) {
                    foreach ($personGrades as $identifier => $value) {
                        if (trim($value) !== '' && $identifier !== 'Text') {
                            if (!preg_match('!^[1-6]{1}$!is', trim($value))) {
                                $error = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($error) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1-6).
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $form;
        } else {
            if ($Data != null) {
                foreach ($Data as $prepareStudentId => $personGrades) {
                    if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                        && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                        && ($tblDivision = $tblPrepareItem->getServiceTblDivision())
                        && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                        && is_array($personGrades)
                    ) {
                        $this->setSignerFromSignedInPerson($tblPrepareStudent);

                        $hasGradeText = false;
                        $gradeText = '';
                        if ((isset($personGrades['Text']))
                            && ($tblGradeText = Gradebook::useService()->getGradeTextById($personGrades['Text']))
                        ) {
                            $hasGradeText = true;
                            $gradeText = $tblGradeText->getName();
                        }

                        foreach ($personGrades as $identifier => $value) {
                            // GradeText als Endnote speichern
                            if ($identifier == 'EN' && $hasGradeText) {
                                $value = $gradeText;
                            }

                            if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($identifier))) {
                                if ($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy(
                                    $tblPrepareItem, $tblPerson, $tblCurrentSubject, $tblPrepareAdditionalGradeType
                                )
                                ) {
                                    (new Data($this->getBinding()))->updatePrepareAdditionalGrade($tblPrepareAdditionalGrade,
                                        trim($value), false);
                                } elseif (trim($value) != '') {
                                    (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                                        $tblPrepareItem, $tblPerson, $tblCurrentSubject, $tblPrepareAdditionalGradeType,
                                        0, trim($value), false, false
                                    );
                                }
                            }
                        }
                    }
                }
            }
            if ($tblNextSubject) {
                if ($IsFinalGrade) {
                    $parameters = array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'SubjectId' => $tblNextSubject->getId()
                    );
                } else {
                    $parameters = array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'SubjectId' => $tblCurrentSubject->getId(),
                        'IsFinalGrade' => true
                    );
                }
            } else {
                if ($IsFinalGrade) {
                    $parameters = array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'IsNotSubject' => true
                    );
                } else {
                    $parameters = array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'SubjectId' => $tblCurrentSubject->getId(),
                        'IsFinalGrade' => true
                    );
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Noten wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Setting',
                    Redirect::TIMEOUT_SUCCESS,
                    $parameters
                );
        }
    }

    /**
     * @param $grade
     *
     * @return string
     */
    private function getVerbalGrade($grade)
    {
        switch ($grade) {
            case 1 : return 'sehr gut'; break;
            case 2 : return 'gut'; break;
            case 3 : return 'befriedigend'; break;
            case 4 : return 'ausreichend'; break;
            case 5 : return 'mangelhaft'; break;
            case 6 : return 'ungenügend'; break;
        }

        return $grade;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function isCourseMainDiploma(TblPrepareCertificate $tblPrepare)
    {

        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblLevel = $tblDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
            && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
        ) {
            if ($tblLevel->getName() == '9' || $tblLevel->getName() == '09') {
                return true;
            }
        }

        return false;
    }

    /**
     * soft remove
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function destroyPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate)
    {

        return (new Data($this->getBinding()))->destroyPrepareCertificate($tblPrepareCertificate);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param array $certificateNameList
     * @param bool $hasMissingLanguage
     *
     * @return array
     */
    public function checkCertificateSubjectsForDivision(TblPrepareCertificate $tblPrepare, $certificateNameList, &$hasMissingLanguage)
    {

        if (($tblSetting = ConsumerSetting::useService()->getSetting('Api', 'Education', 'Certificate', 'ProfileAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblProfileSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblProfileSubject  = false;
        }
        if (($tblSetting = ConsumerSetting::useService()->getSetting('Api', 'Education', 'Certificate', 'OrientationAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblOrientationSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblOrientationSubject  = false;
        }

        $subjectList = array();
        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivision))
        ) {

            if (($tblSubjectProfileAll = Subject::useService()->getSubjectProfileAll())){
                $hasProfiles = true;
            } else {
                $hasProfiles = false;
            }
            if (($tblSubjectOrientationAll = Subject::useService()->getSubjectOrientationAll())){
                $hasOrientations = true;
            } else {
                $hasOrientations = false;
            }
            if (($tblForeignLanguagesAll = Subject::useService()->getSubjectForeignLanguageAll())) {
                $hasForeignLanguages = true;
            } else {
                $hasForeignLanguages = false;
            }
            foreach ($tblTestList as $tblTest) {
                if (($tblSubject = $tblTest->getServiceTblSubject())) {
                    // Profile ignorieren
                    if ($tblProfileSubject) {
                        if ($tblProfileSubject->getId() == $tblSubject->getId()) {
                            continue;
                        }
                    } elseif ($hasProfiles && isset($tblSubjectProfileAll[$tblSubject->getId()])) {
                        continue;
                    }

                    // Neigungskurse orientieren
                    if ($tblOrientationSubject) {
                        if ($tblOrientationSubject->getId() == $tblSubject->getId()) {
                            continue;
                        }
                    } elseif ($hasOrientations && isset($tblSubjectOrientationAll[$tblSubject->getId()])) {
                        continue;
                    }

                    // bei Fremdsprache I-Icon mit ToolTip
                    if ($hasForeignLanguages && isset($tblForeignLanguagesAll[$tblSubject->getId()])) {
//                        $isForeignLanguage = true;
                        $hasMissingLanguage = true;
                    } /** @noinspection PhpStatementHasEmptyBodyInspection */ else {
//                        $isForeignLanguage = false;
                    }

                    foreach ($certificateNameList as $certificateId => $name) {
                        if (($tblCertificate = Setting::useService()->getCertificateById($certificateId))
                            && !Setting::useService()->getCertificateSubjectBySubject($tblCertificate, $tblSubject)
                        ) {
                            $subjectList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
//                                .($isForeignLanguage
//                                    ? ' ' . new ToolTip(new \SPHERE\Common\Frontend\Icon\Repository\Info(),
//                                        'Bei Fremdsprachen kann die Warnung unter Umständen ignoriert werden,
//                                         bitte prüfen Sie die Detailansicht unter Bearbeiten.') : '');
                        }
                    }
                }
            }

        }

        return $subjectList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return array
     */
    public function checkCertificateSubjectsForStudents(TblPrepareCertificate $tblPrepare)
    {

        $subjectList = array();
        $resultList = array();
        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivision))
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            foreach ($tblTestList as $tblTest) {
                if (($tblSubject = $tblTest->getServiceTblSubject())) {
                    if (($tblSubjectGroup = $tblTest->getServiceTblSubjectGroup())) {
                        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                $tblDivision, $tblSubject, $tblSubjectGroup))
                            && ($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject))
                        ) {
                            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                if (($tblPerson = $tblSubjectStudent->getServiceTblPerson())) {
                                    $subjectList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblSubject;
                                }
                            }
                        }
                    } else {
                        foreach ($tblPersonList as $tblPerson) {
                            $subjectList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblSubject;
                        }
                    }
                }
            }
        }

        if (($tblSetting = ConsumerSetting::useService()->getSetting('Api', 'Education', 'Certificate', 'ProfileAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblProfileSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblProfileSubject  = false;
        }
        if (($tblSetting = ConsumerSetting::useService()->getSetting('Api', 'Education', 'Certificate', 'OrientationAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblOrientationSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblOrientationSubject  = false;
        }

        foreach ($subjectList as $personId => $subjects) {
            if (($tblPerson = Person::useService()->getPersonById($personId))) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                ) {
                    ksort($subjects);
                    /** @var TblSubject $tblSubject */
                    foreach ($subjects as $tblSubject) {
                        // Profile überspringen --> stehen extra im Wahlpflichtbereich
                        if ($tblProfileSubject) {
                            if ($tblProfileSubject->getId() == $tblSubject->getId()) {
                                continue;
                            }
                        } elseif (($tblStudent = $tblPerson->getStudent())
                            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
                            && ($tblProfileList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            $isIgnore = false;
                            foreach ($tblProfileList as $tblProfile) {
                                if ($tblProfile->getServiceTblSubject() && $tblProfile->getServiceTblSubject()->getId() == $tblSubject->getId()) {
                                    $isIgnore = true;
                                }
                            }
                            if ($isIgnore) {
                                continue;
                            }
                        }

                        // Neigungskurs überspringen --> stehen extra im Wahlpflichtbereich
                        if ($tblOrientationSubject) {
                            if ($tblOrientationSubject->getId() == $tblSubject->getId()) {
                                continue;
                            }
                        } elseif (($tblStudent = $tblPerson->getStudent())
                            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                            && ($tblOrientationList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            $isIgnore = false;
                            foreach ($tblOrientationList as $tblOrientation) {
                                if ($tblOrientation->getServiceTblSubject() && $tblOrientation->getServiceTblSubject()->getId() == $tblSubject->getId()) {
                                    $isIgnore = true;
                                }
                            }
                            if ($isIgnore) {
                                continue;
                            }
                        }

                        // ab 2. Fremdsprache ignorieren
                        if (($tblStudent = $tblPerson->getStudent())
                            // nicht für alle Zeugnisse sinnvoll, z.B. Kurshalbjahreszeugnis
                            && $tblCertificate->getName() !== 'Gymnasium Kurshalbjahreszeugnis'
                            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                            && ($tblForeignLanguageList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            $isIgnore = false;
                            foreach ($tblForeignLanguageList as $tblForeignLanguage) {
                                if ($tblForeignLanguage->getServiceTblSubject()
                                    && $tblForeignLanguage->getTblStudentSubjectRanking()
                                    && $tblForeignLanguage->getTblStudentSubjectRanking()->getName() != '1'
                                    && $tblForeignLanguage->getServiceTblSubject()->getId() == $tblSubject->getId()
                                ) {
                                    $isIgnore = true;
                                }
                            }
                            if ($isIgnore) {
                                continue;
                            }
                        }


                        if (!Setting::useService()->getCertificateSubjectBySubject($tblCertificate, $tblSubject)) {
                            $resultList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblSubject->getAcronym();
                        }
                    }
                }
            }
        }

        return $resultList;
    }

    /**
     * @param $Route
     * @param $IsAllYears
     * @param $IsGroup
     * @param $YearId
     * @param $tblYear
     * @param bool $HasAllYears
     *
     * @return array
     */
    public function setYearGroupButtonList($Route, $IsAllYears, $IsGroup, $YearId, &$tblYear, $HasAllYears = true)
    {

        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && !$IsGroup && $tblYearList) {
            $tblYear = end($tblYearList);
        }

        $buttonList = array();
        if ($tblYearList) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $buttonList[] = (new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        $Route, new Edit(), array('YearId' => $tblYearItem->getId())));
                } else {
                    $buttonList[] = (new Standard($tblYearItem->getDisplayName(), $Route,
                        null, array('YearId' => $tblYearItem->getId())));
                }
            }

            // Fachlehrer sollen nur Zugriff auf Leistungsüberprüfungen aller aktuellen Schuljahre haben
            // #SSW-1169 Anlegen von Leistungsüberprüfung von noch nicht erreichten Schuljahren verhindern
            if ($HasAllYears) {
                if ($IsAllYears) {
                    $buttonList[] = (new Standard(new Info(new Bold('Alle Schuljahre')),
                        $Route, new Edit(), array('IsAllYears' => true)));
                }  else {
                    $buttonList[] = (new Standard('Alle Schuljahre', $Route, null,
                        array('IsAllYears' => true)));
                }
            }

            if ($IsGroup) {
                $buttonList[] = (new Standard(new Info(new Bold('Gruppen')),
                    $Route, new Edit(), array('IsGroup' => true)));
            }  else {
                $buttonList[] = (new Standard('Gruppen', $Route, null,
                    array('IsGroup' => true)));
            }

            // Abstandszeile
            $buttonList[] = new Container('&nbsp;');
        }

        return $buttonList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     *
     * @return false|TblLeaveStudent
     */
    public  function getLeaveStudentBy(TblPerson $tblPerson, TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getLeaveStudentBy($tblPerson, $tblDivision);
    }

    /**
     * @param $Id
     *
     * @return false|TblLeaveStudent
     */
    public function getLeaveStudentById($Id)
    {

        return (new Data($this->getBinding()))->getLeaveStudentById($Id);
    }

    /**
     * @return false|TblLeaveStudent[]
     */
    public function  getLeaveStudentAll()
    {

        return (new Data($this->getBinding()))->getLeaveStudentAll();
    }

    /**
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return false|TblLeaveStudent[]
     */
    public function getLeaveStudentAllBy($IsApproved = false, $IsPrinted = false)
    {

        return (new Data($this->getBinding()))->getLeaveStudentAllBy($IsApproved, $IsPrinted);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblLeaveStudent[]
     */
    public function  getLeaveStudentAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getLeaveStudentAllByDivision($tblDivision);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     *
     * @return false|TblLeaveGrade
     */
    public function  getLeaveGradeBy(TblLeaveStudent $tblLeaveStudent, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->getLeaveGradeBy($tblLeaveStudent, $tblSubject);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return false|TblLeaveGrade[]
     */
    public function getLeaveGradeAllByLeaveStudent(TblLeaveStudent $tblLeaveStudent)
    {

        return (new Data($this->getBinding()))->getLeaveGradeAllByLeaveStudent($tblLeaveStudent);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $Field
     *
     * @return false|TblLeaveInformation
     */
    public function getLeaveInformationBy(TblLeaveStudent $tblLeaveStudent, $Field)
    {

        return (new Data($this->getBinding()))->getLeaveInformationBy($tblLeaveStudent, $Field);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return false|TblLeaveInformation[]
     */
    public function getLeaveInformationAllByLeaveStudent(TblLeaveStudent $tblLeaveStudent)
    {

        return (new Data($this->getBinding()))->getLeaveInformationAllByLeaveStudent($tblLeaveStudent);
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblCertificate $tblCertificate
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateLeaveContent(
        IFormInterface $Form = null,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblCertificate $tblCertificate,
        $Data
    ) {

        if ($Data === null) {
            return $Form;
        }

        $error = false;
        if (isset($Data['InformationList']['CertificateDate']) && empty($Data['InformationList']['CertificateDate'])) {
            $Form->setError('Data[InformationList][CertificateDate]', new Exclamation() . ' Bitte geben Sie ein Datum ein.');

            $error = true;
        }

        if ($error) {
            $Form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Es wurden nicht alle Pflichtfelder befüllt. Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $Form;
        }

        if ((!$tblLeaveStudent = $this->getLeaveStudentBy($tblPerson, $tblDivision))) {
            $tblLeaveStudent = (new Data($this->getBinding()))->createLeaveStudent($tblPerson, $tblDivision, $tblCertificate);
        }
        if ($tblLeaveStudent) {
            if (isset($Data['Grades'])) {
                foreach ($Data['Grades'] as $subjectId => $array){
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                        if (isset($array['Grade']) && isset($array['GradeText'])){
                            if (($tblGradeText = Gradebook::useService()->getGradeTextById($array['GradeText']))) {
                                $value = $tblGradeText->getName();
                            } else {
                                $value = $array['Grade'];
                            }

                            if (($tblLeaveGrade = $this->getLeaveGradeBy($tblLeaveStudent, $tblSubject))) {
                                (new Data($this->getBinding()))->updateLeaveGrade($tblLeaveGrade, $value);
                            } else {
                                (new Data($this->getBinding()))->createLeaveGrade($tblLeaveStudent, $tblSubject, $value);
                            }
                        }
                    }
                }
            }

            if (isset($Data['InformationList'])) {
                foreach ($Data['InformationList'] as $field => $value) {
                    if (($tblLeaveInformation = $this->getLeaveInformationBy($tblLeaveStudent, $field))) {
                        (new Data($this->getBinding()))->updateLeaveInformation($tblLeaveInformation, $value);
                    } else {
                        (new Data($this->getBinding()))->createLeaveInformation($tblLeaveStudent, $field, $value);
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                'PersonId' => $tblPerson->getId(),
                'DivisionId' => $tblDivision->getId()
            ));
    }

    /**
     * @param IFormInterface|null $form
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createLeaveStudentFromForm(
        IFormInterface $form = null,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        $Data
    ) {

        if ($Data === null) {
            return $form;
        }

        if (!($tblCertificate = Generator::useService()->getCertificateById($Data['Certificate']))) {
            $form->setError('Data[Certificate]', new Exclamation() . ' Bitte wählen Sie eine Zeugnisvorlage aus.');

            return $form;
        }

        if (($tblLeaveStudent = $this->getLeaveStudentBy($tblPerson, $tblDivision))) {
            (new Data($this->getBinding()))->updateLeaveStudentCertificate($tblLeaveStudent, $tblCertificate);
        } else {
            $tblLeaveStudent = (new Data($this->getBinding()))->createLeaveStudent($tblPerson, $tblDivision, $tblCertificate);
        }

        if ($tblLeaveStudent) {
            return new Success('Die Daten wurden gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                    'PersonId' => $tblPerson->getId(),
                    'DivisionId' => $tblDivision->getId()
                ));
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden.', new Exclamation())
                . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_ERROR, array(
                    'PersonId' => $tblPerson->getId(),
                    'DivisionId' => $tblDivision->getId()
                ));
        }
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return bool
     */
    public function updateLeaveStudent(
        TblLeaveStudent $tblLeaveStudent,
        $IsApproved = false,
        $IsPrinted = false
    ) {

        return (new Data($this->getBinding()))->updateLeaveStudent($tblLeaveStudent, $IsApproved, $IsPrinted);
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate $tblPrepare
     * @param null $GroupId
     * @param null|BlockIView $View
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function updateAbiturPreliminaryGrades(
        IFormInterface $Form = null,
        TblPerson $tblPerson,
        TblPrepareCertificate $tblPrepare,
        $GroupId = null,
        $View = null,
        $Data = null
    ) {

        if ($Data === null) {
            return $Form;
        }

        if ($View == BlockIView::EDIT_GRADES) {
            // check Wertebereich
            $error = false;
            foreach ($Data as $midTerm => $subjects) {
                if (is_array($subjects)
                    && (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm)))
                ) {
                    foreach ($subjects as $subjectId => $value) {
                        if (trim($value) !== '') {
                            if (!preg_match('!^([0-9]{1}|1[0-5]{1})$!is', trim($value))) {
                                $error = true;
                                break;
                            }
                        }
                    }
                }
            }

            if ($error) {
                $Form->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (0 - 15 Punkte).
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                    ))));

                return $Form;
            }

            foreach ($Data as $midTerm => $subjects) {
                if (is_array($subjects)
                    && (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm)))
                ) {
                    foreach ($subjects as $subjectId => $grade) {
                        $grade = trim($grade);
                        if (($tblSubject = Subject::useService()->getSubjectById($subjectId))
                            && $grade !== null && $grade !== ''
                        ) {
                            // todo Prüfung ob bulkSave erforderlich
                            if (($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy(
                                $tblPrepare,
                                $tblPerson,
                                $tblSubject,
                                $tblPrepareAdditionalGradeType
                            ))) {
                                (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                    $tblPrepareAdditionalGrade, $grade, $tblPrepareAdditionalGrade->isSelected());
                            } else {
                                (new Data($this->getBinding()))->createPrepareAdditionalGrade($tblPrepare,
                                    $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType, 0, $grade, false, false);
                            }
                        }
                    }
                }
            }
        } elseif ($View == BlockIView::CHOOSE_COURSES) {
            for ($level = 11; $level < 13; $level++) {
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy(
                            $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                            if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                                if (isset($Data[$midTerm][$tblSubject->getId()])) {
                                    if (!$tblPrepareAdditionalGrade->isSelected()) {
                                        (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                            $tblPrepareAdditionalGrade, $tblPrepareAdditionalGrade->getGrade(), true);
                                    }
                                } else {
                                    if ($tblPrepareAdditionalGrade->isSelected()) {
                                        (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                            $tblPrepareAdditionalGrade, $tblPrepareAdditionalGrade->getGrade(), false);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockI', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'PersonId' => $tblPerson->getId(),
                'GroupId' => $GroupId,
                'Route' => 'Diploma'
            ));
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param TblPrepareCertificate $tblPrepareCertificate
     */
    public function copyAbiturPreliminaryGradesFromCertificates(
        TblPrepareStudent $tblPrepareStudent,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        TblPrepareCertificate $tblPrepareCertificate
    ) {
        // Zensuren von Zeugnissen
        if (($tblPreviousPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))
            && ($tblPrepareGradeList = Prepare::useService()->getPrepareGradeAllByPerson(
                $tblPreviousPrepare,
                $tblPerson,
                $tblTestType))
        ) {
            foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                if (($tblSubject = $tblPrepareGrade->getServiceTblSubject())
                ) {
                    if ($tblSubject->getAcronym() == 'EN2') {
                        $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                    }

                    if (($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                        $tblPrepareCertificate,
                        $tblPerson,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    ))) {
                        if (($tblPrepareGrade->getGrade() !== $tblPrepareAdditionalGrade->getGrade())) {
                            (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                $tblPrepareAdditionalGrade,
                                $tblPrepareGrade->getGrade(),
                                $tblPrepareAdditionalGrade->isSelected()
                            );
                        }
                    } else {
                        (new Data($this->getBinding()))->createPrepareAdditionalGrade($tblPrepareCertificate,
                            $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType, 0, $tblPrepareGrade->getGrade(), false, true);
                    }
                }
            }
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param TblTestType $tblTestType
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function copyAbiturPreliminaryGradesFromAppointedDateTask(
        TblDivision $tblDivision,
        TblPerson $tblPerson,
        TblPrepareCertificate $tblPrepareCertificate,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        TblTestType $tblTestType
    ) {

        if (($tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType))) {
            foreach ($tblTaskList as $tblTask) {
                if (($tblPeriod = $tblTask->getServiceTblPeriodByDivision($tblDivision))
                    && strpos($tblPeriod->getName(), '2.') !== false
                ) {
                    if (($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask))) {
                        foreach ($tblTestList as $tblTest) {
                            if (($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson))
                                && ($tblSubject = $tblGrade->getServiceTblSubject())
                            ) {
                                if ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '') {
                                    if ($tblSubject->getAcronym() == 'EN2') {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                                    }

                                    if (($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy(
                                        $tblPrepareCertificate, $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType))
                                    ) {
                                        if (($tblGrade->getGrade() !== $tblPrepareAdditionalGrade->getGrade())) {
                                            (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                                $tblPrepareAdditionalGrade,
                                                $tblGrade->getGrade(),
                                                $tblPrepareAdditionalGrade->isSelected()
                                            );
                                        }
                                    } else {
                                        (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                                            $tblPrepareCertificate,
                                            $tblPerson,
                                            $tblSubject,
                                            $tblPrepareAdditionalGradeType,
                                            0,
                                            $tblGrade->getGrade(),
                                            false,
                                            true);
                                    }
                                }
                            }
                        }
                    }

                    break;
                }
            }
        }
    }

    public function copyAbiturLeaveGradesFromCertificates(
        TblPrepareStudent $tblPrepareStudent,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        TblLeaveStudent $tblLeaveStudent
    ) {
        // Zensuren von Zeugnissen
        if (($tblPreviousPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))
            && ($tblPrepareGradeList = Prepare::useService()->getPrepareGradeAllByPerson(
                $tblPreviousPrepare,
                $tblPerson,
                $tblTestType))
        ) {
            foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                if (($tblSubject = $tblPrepareGrade->getServiceTblSubject())
                ) {
                    if (($tblLeaveAdditionalGrade = Prepare::useService()->getLeaveAdditionalGradeBy(
                        $tblLeaveStudent,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    ))) {
                        if (($tblPrepareGrade->getGrade() !== $tblLeaveAdditionalGrade->getGrade())) {
                            (new Data($this->getBinding()))->updateLeaveAdditionalGrade(
                                $tblLeaveAdditionalGrade,
                                $tblPrepareGrade->getGrade()
                            );
                        }
                    } else {
                        (new Data($this->getBinding()))->createLeaveAdditionalGrade(
                            $tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType,
                            $tblPrepareGrade->getGrade(),
                            true
                        );
                    }
                }
            }
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param $ranking
     *
     * @return false|TblPrepareAdditionalGrade
     * @throws \Exception
     */
    public function getPrepareAdditionalGradeByRanking(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking
    ) {

        return (new Data($this->getBinding()))->getPrepareAdditionalGradeByRanking(
            $tblPrepareCertificate,
            $tblPerson,
            $tblPrepareAdditionalGradeType,
            $ranking
        );
    }

    /**
     * @param IFormInterface $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Data
     * @param $GroupId
     * @param $firstAdvancedCourse
     * @param $secondAdvancedCourse
     *
     * @return IFormInterface|string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    public function updateAbiturExamGrades(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Data,
        $GroupId,
        $firstAdvancedCourse,
        $secondAdvancedCourse
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        // check Wertebereich && is subject selected
        $errorGrades = false;
        $errorSubject = false;
        $errorRanking = 0;
        foreach ($Data as $ranking => $items) {
            if (isset($items['Grades'])) {
                foreach ($items['Grades'] as $key => $value) {
                    if (trim($value) !== '') {
                        if (!preg_match('!^([0-9]{1}|1[0-5]{1})$!is', trim($value))) {
                            $errorGrades = true;
                            break;
                        }
                    }
                }
            }
            if ($ranking > 2) {
                if (isset($items['Subject']) && !Subject::useService()->getSubjectById($items['Subject'])) {
                    if (isset($items['Grades'])) {
                        foreach ($items['Grades'] as $key => $value) {
                            if (trim($value) !== '') {
                                $errorSubject = true;
                                $errorRanking = $ranking;
                                break;
                            }
                        }
                    }
                }
            }

        }

        if ($errorGrades) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (0 - 15 Punkte).
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));
        }
        if ($errorSubject) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Beim ' . $errorRanking . '. Prüfungsfach wurde kein Fach ausgewählt. Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));
        }
        if ($errorGrades || $errorSubject) {
            return $form;
        }

        foreach ($Data as $ranking => $items) {
            $tblSubject = false;
            if ($ranking === 1) {
                if ($firstAdvancedCourse) {
                    $tblSubject = $firstAdvancedCourse;
                }
            } elseif ($ranking === 2) {
                if ($secondAdvancedCourse) {
                    $tblSubject = $secondAdvancedCourse;
                }
            } elseif (isset($items['Subject'])) {
                $tblSubject = Subject::useService()->getSubjectById($items['Subject']);
            }

            if ($tblSubject) {
                if (isset($items['Grades'])) {
                    foreach ($items['Grades'] as $key => $value) {
                        if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($key))) {
                            if (($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeByRanking(
                                $tblPrepare,
                                $tblPerson,
                                $tblPrepareAdditionalGradeType,
                                $ranking
                            ))) {
                                (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                    $tblPrepareAdditionalGrade,
                                    $value
                                );
                            } else {
                                (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                                    $tblPrepare,
                                    $tblPerson,
                                    $tblSubject,
                                    $tblPrepareAdditionalGradeType,
                                    $ranking,
                                    $value
                                );
                            }
                        }
                    }
                }
            }
        }

        if (isset($Data['BellSubject'])) {
            if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'BellSubject'))) {
                (new Data($this->getBinding()))->updatePrepareInformation(
                    $tblPrepareInformation,
                    'BellSubject',
                    $Data['BellSubject']
                );
            } else {
                (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'BellSubject', $Data['BellSubject']);
            }
        }
        if (isset($Data['BellPoints'])) {
            if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'BellPoints'))) {
                (new Data($this->getBinding()))->updatePrepareInformation(
                    $tblPrepareInformation,
                    'BellPoints',
                    $Data['BellPoints']
                );
            } else {
                (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'BellPoints', $Data['BellPoints']);
            }
        }

        $isBellUsed = isset($Data['IsBellUsed']);
        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'IsBellUsed'))) {
            (new Data($this->getBinding()))->updatePrepareInformation(
                $tblPrepareInformation,
                'IsBellUsed',
                $isBellUsed
            );
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'IsBellUsed', $isBellUsed);
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockII', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'PersonId' => $tblPerson->getId(),
                'GroupId' => $GroupId,
                'Route' => 'Diploma'
            ));
    }

    /**
     * @param TblPrepareAdditionalGrade $tblWrittenExamGrade
     * @param TblPrepareAdditionalGrade|null $tblExtraVerbalGrade
     *
     * @return float|int|string
     */
    public function calcAbiturExamGradesTotalForWrittenExam(
        TblPrepareAdditionalGrade $tblWrittenExamGrade,
        TblPrepareAdditionalGrade $tblExtraVerbalGrade = null
    ) {

        $writtenExamGradeValue = $tblWrittenExamGrade->getGrade();
        if ($tblExtraVerbalGrade) {
            $extraVerbalExamGradeValue = $tblExtraVerbalGrade->getGrade();
            if ($extraVerbalExamGradeValue !== '' && $extraVerbalExamGradeValue !== null) {
                $total = 4 * (floatval($writtenExamGradeValue) * (2 / 3) + floatval($extraVerbalExamGradeValue) * (1 / 3));
            } else {
                $total = floatval($writtenExamGradeValue) * 4;
            }
        } else {
            $total = floatval($writtenExamGradeValue) * 4;
        }
        $total = str_pad(round($total), 2, 0, STR_PAD_LEFT);

        return $total;
    }

    /**
     * @param TblPrepareAdditionalGrade $tblVerbalExamGrade
     * @param TblPrepareAdditionalGrade|null $tblExtraVerbalGrade
     *
     * @return float|int|string
     */
    public function calcAbiturExamGradesTotalForVerbalExam(
        TblPrepareAdditionalGrade $tblVerbalExamGrade,
        TblPrepareAdditionalGrade $tblExtraVerbalGrade = null
    ) {

        $verbalExamGradeValue = $tblVerbalExamGrade->getGrade();
        if ($tblExtraVerbalGrade) {
            $extraVerbalExamGradeValue = $tblExtraVerbalGrade->getGrade();
            if ($extraVerbalExamGradeValue !== '' && $extraVerbalExamGradeValue !== null) {
                $total = 4 * (floatval($verbalExamGradeValue) * (2 / 3) + floatval($extraVerbalExamGradeValue) * (1 / 3));
                $total = str_pad(round($total), 2, 0, STR_PAD_LEFT);
            } else {
                $total = floatval($verbalExamGradeValue) * 4;
            }
        } else {
            $total = floatval($verbalExamGradeValue) * 4;
        }
        $total = str_pad(round($total), 2, 0, STR_PAD_LEFT);

        return $total;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getCoursesForStudent(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $advancedCourses = array();
        $basicCourses = array();
        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                if (($tblSubjectGroup = $tblDivisionSubjectItem->getTblSubjectGroup())) {

                    if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject(
                        $tblDivisionSubjectItem))
                    ) {
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            if (($tblSubject = $tblDivisionSubjectItem->getServiceTblSubject())
                                && ($tblPersonStudent = $tblSubjectStudent->getServiceTblPerson())
                                && $tblPerson->getId() == $tblPersonStudent->getId()
                            ) {
                                if ($tblSubject->getAcronym() == 'EN2') {
                                    $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                                }

                                if ($tblSubject) {
                                    if ($tblSubjectGroup->isAdvancedCourse()) {
                                        $advancedCourses[$tblSubject->getId()] = $tblSubject;
                                    } else {
                                        $basicCourses[$tblSubject->getId()] = $tblSubject;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return array($advancedCourses, $basicCourses);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getResultForAbiturBlockI(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson
    ) {
        $countCourses = 0;
        $countCoursesTotal = 0;
        $resultBlockI = 0;
        if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
            $tblPrepare,
            $tblPerson
        ))) {

            if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                /** @noinspection PhpUnusedLocalVariableInspection */
                list($advancedCourses, $basicCourses) = Prepare::useService()->getCoursesForStudent(
                    $tblDivision,
                    $tblPerson
                );
            } else {
                $advancedCourses = array();
            }

            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                $identifier = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType()->getIdentifier();
                if ($identifier == '11-1' || $identifier == '11-2' || $identifier == '12-1' || $identifier == '12-2') {
                    if (($tblPrepareAdditionalGrade->isSelected())) {

                        $countCourses++;

                        // Leistungskurse zählen doppelt
                        if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                            && isset($advancedCourses[$tblSubject->getId()])
                        ) {
                            $countCoursesTotal += 2;
                            $resultBlockI += 2 * floatval($tblPrepareAdditionalGrade->getGrade());
                        } else {
                            $countCoursesTotal++;
                            $resultBlockI += floatval($tblPrepareAdditionalGrade->getGrade());
                        }
                    }
                }
            }

            if ($countCoursesTotal > 0) {
                $resultBlockI = round(($resultBlockI / $countCoursesTotal) * 40);
            }
        }

        return array($countCourses, $resultBlockI);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return int
     * @throws \Exception
     */
    public function getResultForAbiturBlockII(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson
    ) {

        $result = 0;
        for ($i = 1; $i < 6; $i++) {
            $total = 0;
            if ($i < 4) {
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('WRITTEN_EXAM'))
                    && ($writtenExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                        $tblPrepareCertificate,
                        $tblPerson,
                        $tblPrepareAdditionalGradeType,
                        $i))
                ) {
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EXTRA_VERBAL_EXAM'))
                        && ($extraVerbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                            $tblPrepareCertificate,
                            $tblPerson,
                            $tblPrepareAdditionalGradeType,
                            $i))
                    ) {

                    } else {
                        $extraVerbalExamGrade = false;
                    }

                    $total = Prepare::useService()->calcAbiturExamGradesTotalForWrittenExam(
                        $writtenExamGrade,
                        $extraVerbalExamGrade ? $extraVerbalExamGrade : null
                    );
                }
            } else {
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('VERBAL_EXAM'))
                    && ($verbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                        $tblPrepareCertificate,
                        $tblPerson,
                        $tblPrepareAdditionalGradeType,
                        $i))
                ) {
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EXTRA_VERBAL_EXAM'))
                        && ($extraVerbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                            $tblPrepareCertificate,
                            $tblPerson,
                            $tblPrepareAdditionalGradeType,
                            $i))
                    ) {

                    } else {
                        $extraVerbalExamGrade = false;
                    }

                    $total = Prepare::useService()->calcAbiturExamGradesTotalForVerbalExam(
                        $verbalExamGrade,
                        $extraVerbalExamGrade ? $extraVerbalExamGrade : null
                    );
                }
            }

            // die Bell ersetzt das 5. Prüfungsfach
            if ($i == 5) {
                if (($tblPrepareInformationIsBellUsed = Prepare::useService()->getPrepareInformationBy(
                        $tblPrepareCertificate, $tblPerson, 'IsBellUsed'))
                    && $tblPrepareInformationIsBellUsed->getValue()
                ) {
                    $total = 0;
                    if (($tblPrepareInformationBellPoints = Prepare::useService()->getPrepareInformationBy(
                        $tblPrepareCertificate, $tblPerson, 'BellPoints'))
                    ) {
                        $total = floatval($tblPrepareInformationBellPoints->getValue());
                    }
                }
            }

            $result += floatval($total);
        }

        return $result;
    }

    /**
     * @param $totalPoints
     *
     * @return string
     */
    public function  getResultForAbiturAverageGrade(
        $totalPoints
    ) {

        // ist Formel korrekt?
//        return str_replace('.',',', round((17/3) - ($totalPoints/180),1));
        if ($totalPoints <= 900 && $totalPoints > 822) {
            return '1,0';
        } elseif ($totalPoints > 804) {
            return '1,1';
        } elseif ($totalPoints > 786) {
            return '1,2';
        } elseif ($totalPoints > 768) {
            return '1,3';
        } elseif ($totalPoints > 750) {
            return '1,4';
        } elseif ($totalPoints > 732) {
            return '1,5';
        } elseif ($totalPoints > 714) {
            return '1,6';
        } elseif ($totalPoints > 696) {
            return '1,7';
        } elseif ($totalPoints > 678) {
            return '1,8';
        } elseif ($totalPoints > 660) {
            return '1,9';
        } elseif ($totalPoints > 642) {
            return '2,0';
        } elseif ($totalPoints > 624) {
            return '2,1';
        } elseif ($totalPoints > 606) {
            return '2,2';
        } elseif ($totalPoints > 588) {
            return '2,3';
        } elseif ($totalPoints > 570) {
            return '2,4';
        } elseif ($totalPoints > 552) {
            return '2,5';
        } elseif ($totalPoints > 534) {
            return '2,6';
        } elseif ($totalPoints > 516) {
            return '2,7';
        } elseif ($totalPoints > 498) {
            return '2,8';
        } elseif ($totalPoints > 480) {
            return '2,9';
        } elseif ($totalPoints > 462) {
            return '3,0';
        } elseif ($totalPoints > 444) {
            return '3,1';
        } elseif ($totalPoints > 426) {
            return '3,2';
        } elseif ($totalPoints > 408) {
            return '3,3';
        } elseif ($totalPoints > 390) {
            return '3,4';
        } elseif ($totalPoints > 372) {
            return '3,5';
        } elseif ($totalPoints > 354) {
            return '3,6';
        } elseif ($totalPoints > 336) {
            return '3,7';
        } elseif ($totalPoints > 318) {
            return '3,8';
        } elseif ($totalPoints > 300) {
            return '3,9';
        } elseif ($totalPoints == 300) {
            return '4,0';
        } else {
            return '&nbsp;';
        }
    }

    /**
     * @param IFormInterface $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Data
     * @param $GroupId
     *
     * @return IFormInterface|string
     */
    public function updateAbiturPrepareInformation(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Data,
        $GroupId
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        if (isset($Data['Remark'])) {
            if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'Remark'))) {
                (new Data($this->getBinding()))->updatePrepareInformation(
                    $tblPrepareInformation,
                    'Remark',
                    $Data['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'Remark', $Data['Remark']);
            }
        }

        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'Latinums'))) {
            (new Data($this->getBinding()))->updatePrepareInformation(
                $tblPrepareInformation,
                'Latinums',
                isset($Data['Latinums'])
            );
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'Latinums', isset($Data['Latinums']));
        }
        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'Graecums'))) {
            (new Data($this->getBinding()))->updatePrepareInformation(
                $tblPrepareInformation,
                'Graecums',
                isset($Data['Graecums'])
            );
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'Graecums', isset($Data['Graecums']));
        }
        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'Hebraicums'))) {
            (new Data($this->getBinding()))->updatePrepareInformation(
                $tblPrepareInformation,
                'Hebraicums',
                isset($Data['Hebraicums'])
            );
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'Hebraicums', isset($Data['Hebraicums']));
        }

        if (isset($Data['ForeignLanguages'])) {
            foreach ($Data['ForeignLanguages'] as $ranking => $value) {
                $identifier = 'ForeignLanguages' . $ranking;
                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, $identifier))) {
                    (new Data($this->getBinding()))->updatePrepareInformation(
                        $tblPrepareInformation,
                        $identifier,
                        $value
                    );
                } else {
                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, $identifier, $value);
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'GroupId' => $GroupId,
                'Route' => 'Diploma'
            ));
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $grade
     * @param bool $isSelected
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function updatePrepareAdditionalGrade(
        TblPrepareAdditionalGrade $tblPrepareAdditionalGrade,
        $grade,
        $isSelected = false
    ) {

        (new Data($this->getBinding()))->updatePrepareAdditionalGrade($tblPrepareAdditionalGrade, $grade, $isSelected);

        return false;
    }

    /**
     * @param IFormInterface $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Data
     * @param $GroupId
     *
     * @return IFormInterface|string
     * @throws \Exception
     */
    public function updateAbiturLevelTenGrades(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Data,
        $GroupId
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        // check Wertebereich
        $errorGrades = false;
        if (isset($Data['Grades'])) {
            foreach ($Data['Grades'] as $key => $value) {
                if (trim($value) !== '') {
                    if (!preg_match('!^[1-6]{1}$!is', trim($value))) {
                        $errorGrades = true;
                        break;
                    }
                }
            }
        }

        if ($errorGrades) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1 - 6).
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $form;
        }

        if (isset($Data['Grades'])
            && ($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-10'))
        ) {
            $ranking = 1;
            foreach ($Data['Grades'] as $subjectId => $value) {
                $value = trim($value);
                if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                    if (($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy(
                        $tblPrepare,
                        $tblPerson,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    ))) {
                        (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                            $tblPrepareAdditionalGrade,
                            $value
                        );
                    } else {
                        if ($value !== '') {
                            (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                                $tblPrepare,
                                $tblPerson,
                                $tblSubject,
                                $tblPrepareAdditionalGradeType,
                                $ranking,
                                $value
                            );
                        }
                    }
                }

                $ranking++;
            }
        }

        $levelTenGradesAreNotShown = isset($Data['LevelTenGradesAreNotShown']);
        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'LevelTenGradesAreNotShown'))) {
            (new Data($this->getBinding()))->updatePrepareInformation(
                $tblPrepareInformation,
                'LevelTenGradesAreNotShown',
                $levelTenGradesAreNotShown
            );
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'LevelTenGradesAreNotShown', $levelTenGradesAreNotShown);
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'GroupId' => $GroupId,
                'Route' => 'Diploma'
            ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @return array|bool
     * @throws \Exception
     */
    public function checkAbiturExams(TblPrepareCertificate $tblPrepareCertificate, TblPerson $tblPerson)
    {

        $warnings = false;
        $exams = array();
        $hasGerman = false;
        $hasMathematics = false;
        for ($i = 1; $i <6; $i++) {
            $tblSubject = false;
            $grade = false;
            if ($i < 4) {
                $tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('WRITTEN_EXAM');
            } else {
                $tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('VERBAL_EXAM');
            }

            if (($examGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                $tblPrepareCertificate,
                $tblPerson,
                $tblPrepareAdditionalGradeType,
                $i))
            ) {
                $tblSubject = $examGrade->getServiceTblSubject();
                if ($tblSubject) {
                    if ($tblSubject->getName() == 'Deutsch'){
                        $hasGerman = true;
                    }
                    if ($tblSubject->getName() == 'Mathematik') {
                        $hasMathematics = true;
                    }
                }
                $grade = $examGrade->getGrade();
            }

            $exams[$i] = array(
                'Subject' => $tblSubject,
                'Grade' => $grade
            );
        }

        if (!$hasMathematics) {
            $warnings[] = new Warning('Das Fach Mathematik muss sich unter den Prüfungsfächern befinden!', new Exclamation());
        }
        if (!$hasGerman) {
            $warnings[] = new Warning('Das Fach Deutsch muss sich unter den Prüfungsfächern befinden!', new Exclamation());
        }

        return $warnings;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param bool $isForced
     * @return false|TblLeaveAdditionalGrade
     * @throws \Exception
     */
    public function getLeaveAdditionalGradeBy(
        TblLeaveStudent $tblLeaveStudent,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $isForced = false
    ) {

        return (new Data($this->getBinding()))->getLeaveAdditionalGradeBy($tblLeaveStudent, $tblSubject, $tblPrepareAdditionalGradeType, $isForced);
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateLeaveStudentAbiturPoints(
        IFormInterface $Form = null,
        TblLeaveStudent $tblLeaveStudent,
        $Data
    ) {

        if ($Data === null) {
            return $Form;
        }

        $error = false;

        foreach ($Data as $midTerm => $subjects) {
            if (is_array($subjects)
                && (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm)))
            ) {
                foreach ($subjects as $subjectId => $value) {
                    if (trim($value) !== '') {
                        if (!preg_match('!^([0-9]{1}|1[0-5]{1})$!is', trim($value))) {
                            $error = true;
                            break;
                        }
                    }
                }
            }
        }

        if ($error) {
            $Form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (0 - 15 Punkte).
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $Form;
        }

        foreach ($Data as $midTerm => $subjects) {
            if (is_array($subjects)
                && (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm)))
            ) {
                foreach ($subjects as $subjectId => $grade) {
                    $grade = trim($grade);
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                        if (($tblLeaveAdditionalGrade = $this->getLeaveAdditionalGradeBy(
                            $tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType
                        ))) {
                            (new Data($this->getBinding()))->updateLeaveAdditionalGrade(
                                $tblLeaveAdditionalGrade, $grade
                            );
                        } else {
                            if ($grade !== null && $grade !== '') {
                                (new Data($this->getBinding()))->createLeaveAdditionalGrade(
                                    $tblLeaveStudent,
                                    $tblSubject,
                                    $tblPrepareAdditionalGradeType,
                                    $grade,
                                    false
                                );
                            }
                        }
                    }
                }
            }
        }

        $tblPerson =  $tblLeaveStudent->getServiceTblPerson();
        $tblDivision = $tblLeaveStudent->getServiceTblDivision();

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                'PersonId' => $tblPerson ? $tblPerson->getId() : 0,
                'DivisionId' => $tblDivision ? $tblDivision->getId() : 0,
            ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblCertificate $tblCertificate
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return null|TblLeaveStudent
     */
    public function createLeaveStudent(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblCertificate $tblCertificate,
        $IsApproved = false,
        $IsPrinted = false
    ) {

        return (new Data($this->getBinding()))->createLeaveStudent(
            $tblPerson,
            $tblDivision,
            $tblCertificate,
            $IsApproved,
            $IsPrinted
        );
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     * @return string
     * @throws \Exception
     */
    public function calcAbiturLeaveGradePointsBySubject(TblLeaveStudent $tblLeaveStudent, TblSubject $tblSubject)
    {

        $sum = 0;
        $count = 0;
        for ($level = 11; $level < 13; $level++) {
            for ($term = 1; $term < 3; $term++) {
                $midTerm = $level . '-' . $term;
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                    && ($tblLeaveAdditionalGrade = $this->getLeaveAdditionalGradeBy($tblLeaveStudent, $tblSubject, $tblPrepareAdditionalGradeType))
                ) {
                    $grade = $tblLeaveAdditionalGrade->getGrade();
                    if ($grade !== null && $grade !== '') {
                        $sum += floatval($grade);
                        $count++;
                    }
                }
            }
        }

        if ($count > 0) {
            $result = ceil($sum/$count);

            return str_pad($result, 2, 0, STR_PAD_LEFT);
        } else {

            return '&ndash;';
        }
    }

    /**
     * @param $points
     *
     * @return string
     */
    public function getAbiturLeaveGradeBySubject($points)
    {

        if ($points === '15') {
            return '1+';
        } elseif ($points === '14') {
            return '1';
        } elseif ($points === '13') {
            return '1-';
        } elseif ($points === '12') {
            return '2+';
        } elseif ($points === '11') {
            return '2';
        } elseif ($points === '10') {
            return '2-';
        } elseif ($points === '09') {
            return '3+';
        } elseif ($points === '08') {
            return '3';
        } elseif ($points === '07') {
            return '3-';
        } elseif ($points === '06') {
            return '4+';
        } elseif ($points === '05') {
            return '4';
        } elseif ($points === '04') {
            return '4-';
        } elseif ($points === '03') {
            return '5+';
        } elseif ($points === '02') {
            return '5';
        } elseif ($points === '01') {
            return '5-';
        } elseif ($points === '00') {
            return '6';
        } else {
            return '&ndash;';
        }
    }

    /**
     * @param IFormInterface|null $form
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateAbiturLeaveInformation(
        IFormInterface $form = null,
        TblLeaveStudent $tblLeaveStudent,
        $Data
    ) {

        if ($Data === null) {
            return $form;
        }

        $error = false;
        if (isset($Data['CertificateDate']) && empty($Data['CertificateDate'])) {
            $form->setError('Data[InformationList][CertificateDate]', new Exclamation() . ' Bitte geben Sie ein Datum ein.');
            $error = true;
        }

        if ($error) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Es wurden nicht alle Pflichtfelder befüllt. Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $form;
        }

        $leaveTerms = GymAbgSekII::getLeaveTerms();
        $midTerms = GymAbgSekII::getMidTerms();

        foreach ($Data as $field => $value) {
            if ($field == 'LeaveTerm' && isset($leaveTerms[$value])) {
                $saveValue = $leaveTerms[$value];
            } elseif ($field == 'MidTerm' && isset($midTerms[$value])) {
                $saveValue = $midTerms[$value];
            } else {
                $saveValue = $value;
            }

            if (($tblLeaveInformation = $this->getLeaveInformationBy($tblLeaveStudent, $field))) {
                (new Data($this->getBinding()))->updateLeaveInformation($tblLeaveInformation, $saveValue);
            } else {
                (new Data($this->getBinding()))->createLeaveInformation($tblLeaveStudent, $field, $saveValue);
            }
        }

        $tblPerson = $tblLeaveStudent->getServiceTblPerson();
        $tblDivision = $tblLeaveStudent->getServiceTblDivision();

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                'PersonId' => $tblPerson ? $tblPerson->getId() : 0,
                'DivisionId' => $tblDivision ? $tblDivision->getId() : 0
            ));
    }

    /**
     * @param $tblPrepareList
     * @param $tblGroup
     *
     * @return array
     */
    public function getCertificateInformationPages($tblPrepareList, $tblGroup)
    {
        $tblCertificateList = $this->getCertificateListByPrepareList($tblPrepareList, $tblGroup);

        $informationPageList = array();
        $pageList = array();
        /** @var TblCertificate $tblCertificate */
        foreach ($tblCertificateList as $tblCertificate) {
            if (($tblCertificateInformationList = Generator::useService()->getCertificateInformationListByCertificate($tblCertificate))) {
                foreach ($tblCertificateInformationList as $tblCertificateInformation) {
                    $page = $tblCertificateInformation->getPage();
                    if ($page > 1) {
                        $informationPageList[$tblCertificate->getId()][$page][$tblCertificateInformation->getFieldName()] = $tblCertificateInformation->getFieldName();
                        $pageList[$page] = $page;
                    }
                }
            }
        }

        return array($informationPageList, $pageList);
    }

    /**
     * @param $tblPrepareList
     * @param $tblGroup
     *
     * @return TblCertificate[]
     */
    private function getCertificateListByPrepareList(
        $tblPrepareList,
        $tblGroup
    ) {
        $tblCertificateList = array();
        /** @var TblPrepareCertificate $tblPrepare */
        foreach ($tblPrepareList as $tblPrepare) {
            if (($tblDivisionItem = $tblPrepare->getServiceTblDivision())
                && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
            ) {
                foreach ($tblStudentList as $tblPerson) {
                    if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                        ) {
                            $tblCertificateList[$tblCertificate->getId()] = $tblCertificate;
                        }
                    }
                }
            }
        }

        return $tblCertificateList;
    }
}