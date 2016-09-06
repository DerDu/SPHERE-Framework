<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
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
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
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
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getPrepareAllByDivision($tblDivision);
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
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function existsPrepareStudentWhereIsApproved(TblPrepareCertificate $tblPrepare)
    {

        return (new Data($this->getBinding()))->existsPrepareStudentWhereIsApproved($tblPrepare);
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
     * @param TblDivision $tblDivision
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createPrepare(IFormInterface $Stage = null, TblDivision $tblDivision, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $Stage->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Stage->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createPrepare(
                $tblDivision,
                $Data['Date'],
                $Data['Name']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zeugnisvorbereitung ist erfasst worden.')
            . new Redirect('/Education/Certificate/Prepare/Prepare', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblDivision->getId()
            ));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepare(IFormInterface $Stage = null, TblPrepareCertificate $tblPrepare, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $Stage->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Stage->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updatePrepare(
                $tblPrepare,
                $Data['Date'],
                $Data['Name'],
                $tblPrepare->getServiceTblAppointedDateTask() ? $tblPrepare->getServiceTblAppointedDateTask() : null,
                $tblPrepare->getServiceTblBehaviorTask() ? $tblPrepare->getServiceTblBehaviorTask() : null,
                $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : null
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zeugnisvorbereitung ist geändert worden.')
            . new Redirect('/Education/Certificate/Prepare/Prepare', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblPrepare->getServiceTblDivision() ? $tblPrepare->getServiceTblDivision()->getId() : null
            ));
        }

        return $Stage;
    }


    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblTask $tblTask
     *
     * @return string
     */
    public function updatePrepareSetAppointedDateTask(
        TblPrepareCertificate $tblPrepare,
        TblTask $tblTask
    ) {

        $this->updatePrepareSubjectGrades($tblPrepare, $tblTask);

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Stichtagsnotenauftrag wurde ausgewählt.')
        . new Redirect('/Education/Certificate/Prepare/Division', Redirect::TIMEOUT_SUCCESS, array(
            'PrepareId' => $tblPrepare->getId()
        ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return string
     */
    public function updatePrepareUpdateAppointedDateTask(
        TblPrepareCertificate $tblPrepare
    ) {

        $Stage = new Stage('Stichtagsnotenauftrag', 'Aktualisieren');
        if ($tblPrepare->getServiceTblAppointedDateTask()) {
            $this->updatePrepareSubjectGrades($tblPrepare, $tblPrepare->getServiceTblAppointedDateTask());

            return $Stage
            . new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Stichtagsnotenauftrag wurde ausgewählt.')
            . new Redirect('/Education/Certificate/Prepare/Division', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId()
            ));
        } else {
            return $Stage
            . new Danger('Kein Stichtagsnotenauftrag ausgewählt.', new Exclamation())
            . new Redirect('/Education/Certificate/Prepare/Division', Redirect::TIMEOUT_SUCCESS,
                array(
                    'PrepareId' => $tblPrepare->getId()
                ));
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblTask $tblTask
     *
     * @return string
     */
    public function updatePrepareSetBehaviorTask(
        TblPrepareCertificate $tblPrepare,
        TblTask $tblTask
    ) {

        // Löschen der vorhandenen Zensuren
        if ($tblPrepare->getServiceTblBehaviorTask()
            && $tblPrepare->getServiceTblBehaviorTask()->getId() !== $tblTask->getId()
        ) {
            (new Data($this->getBinding()))->destroyPrepareGrades($tblPrepare, $tblTask->getTblTestType());
        }

        (new Data($this->getBinding()))->updatePrepare(
            $tblPrepare,
            $tblPrepare->getDate(),
            $tblPrepare->getName(),
            $tblPrepare->getServiceTblAppointedDateTask() ? $tblPrepare->getServiceTblAppointedDateTask() : null,
            $tblTask,
            $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : null
        );

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnotenauftrag wurde ausgewählt.')
        . new Redirect('/Education/Certificate/Prepare/Division', Redirect::TIMEOUT_SUCCESS, array(
            'PrepareId' => $tblPrepare->getId()
        ));

    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblScoreType|null $tblScoreType
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepareGradeForBehaviorTask(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblScoreType $tblScoreType = null,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        if ($tblScoreType === null) {
            $tblScoreType = Gradebook::useService()->getScoreTypeByIdentifier('GRADES');
        }
        $error = false;
        if (is_array($Data)) {
            foreach ($Data as $gradeTypeId => $value) {
                if (trim($value) !== '' && $tblScoreType) {
                    if (!preg_match('!' . $tblScoreType->getPattern() . '!is', trim($value))) {
                        $error = true;
                        break;
                    }
                }
            }

            if ($error) {
                $Stage->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Nicht alle eingebenen Zensuren befinden sich im Wertebereich.
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                    ))));

                return $Stage;
            } else {
                if (($tblTask = $tblPrepare->getServiceTblBehaviorTask())
                    && ($tblTestType = $tblTask->getTblTestType())
                    && ($tblDivision = $tblPrepare->getServiceTblDivision())
                ) {
                    foreach ($Data as $gradeTypeId => $value) {
                        if (trim($value) && trim($value) !== ''
                            && ($tblGradeType = Gradebook::useService()->getGradeTypeById($gradeTypeId))
                        ) {
                            (new Data($this->getBinding()))->updatePrepareGradeForBehavior(
                                $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblGradeType, trim($value)
                            );
                        }
                    }

                    return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnoten wurden gespeichert.')
                    . new Redirect('/Education/Certificate/Prepare/BehaviorGrades', Redirect::TIMEOUT_SUCCESS, array(
                        'PrepareId' => $tblPrepare->getId(),
                        'PersonId' => $tblPerson->getId(),
                    ));

                }
            }
        }

        return $Stage;
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
            . new Redirect('/Education/Certificate/Prepare/Division', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId()
            ));
        }

        return $Stage;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblCertificate $tblCertificate
     *
     * @return string
     */
    public function updatePrepareStudentSetCertificate(
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
        } else {
            (new Data($this->getBinding()))->createPrepareStudent(
                $tblPrepare,
                $tblPerson,
                $tblCertificate
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
            return (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate,
                true,
                $tblPrepareStudent->isPrinted(),
                Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision,
                    new \DateTime($tblPrepare->getDate())),
                Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblDivision,
                    new \DateTime($tblPrepare->getDate()))
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
                Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision,
                    new \DateTime($tblPrepare->getDate())),
                Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblDivision,
                    new \DateTime($tblPrepare->getDate()))
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
                $tblPrepareStudent->isPrinted(),
                null,
                null
            );
        } else {
            return false;
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Content
     * @param Certificate $Certificate
     *
     * @return IFormInterface|string
     */
    public function updatePrepareInformationList(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Content,
        Certificate $Certificate = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Content) {
            return $Stage;
        }

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
                }

                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, $field))) {
                    (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, $field, $value);
                } else {
                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, $field, $value);
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
        . new Redirect('/Education/Certificate/Prepare/Certificate', Redirect::TIMEOUT_SUCCESS, array(
            'PrepareId' => $tblPrepare->getId(),
            'PersonId' => $tblPerson->getId()
        ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $tblTask
     */
    private function updatePrepareSubjectGrades(TblPrepareCertificate $tblPrepare, TblTask $tblTask)
    {
        // Löschen der vorhandenen Zensuren
        (new Data($this->getBinding()))->destroyPrepareGrades($tblPrepare, $tblTask->getTblTestType());

        // Zensuren zum Stichtagsnotenauftrag ermitteln
        $tblDivision = $tblPrepare->getServiceTblDivision();
        $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
        $gradeList = array();
        if ($tblDivision) {
            $tblStudentListByDivision = Division::useService()->getStudentAllByDivision($tblDivision);
            $tblYear = $tblDivision->getServiceTblYear();
            $isApprovedArray = array();
            if ($tblStudentListByDivision && $tblYear && $tblTestAllByTask) {
                foreach ($tblTestAllByTask as $tblTest) {
                    foreach ($tblStudentListByDivision as $tblPerson) {
                        if (!isset($isApprovedArray[$tblPerson->getId()])) {
                            if (($tblPersonStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                                $isApprovedArray[$tblPerson->getId()] = $tblPersonStudent->isApproved();
                            } else {
                                $isApprovedArray[$tblPerson->getId()] = false;
                            }
                        }

                        if (!$isApprovedArray[$tblPerson->getId()]) {
                            $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                            if ($tblGrade) {
                                $gradeList[$tblPerson->getId()][$tblGrade->getId()] = $tblGrade;
                            }
                        }
                    }
                }
            }
        }

        // Speichern der Zensuren aus dem Stichtagsnotenauftrag
        if (!empty($gradeList)) {
            (new Data($this->getBinding()))->createPrepareGrades(
                $tblPrepare,
                $tblTask->getTblTestType(),
                $gradeList
            );
        }

        (new Data($this->getBinding()))->updatePrepare(
            $tblPrepare,
            $tblPrepare->getDate(),
            $tblPrepare->getName(),
            $tblTask,
            $tblPrepare->getServiceTblBehaviorTask() ? $tblPrepare->getServiceTblBehaviorTask() : null,
            $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : null
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function isAppointedDateTaskUpdated(TblPrepareCertificate $tblPrepareCertificate)
    {

        $tblDivision = $tblPrepareCertificate->getServiceTblDivision();
        $tblTask = $tblPrepareCertificate->getServiceTblAppointedDateTask();
        if ($tblDivision && $tblTask) {
            $tblStudentListByDivision = Division::useService()->getStudentAllByDivision($tblDivision);
            $tblYear = $tblDivision->getServiceTblYear();
            $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
            if ($tblStudentListByDivision && $tblYear && $tblTestAllByTask) {
                foreach ($tblTestAllByTask as $tblTest) {
                    foreach ($tblStudentListByDivision as $tblPerson) {
                        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                        if ($tblGrade && $tblGrade->getGrade()) {
                            $tblPrepareGrade = $this->getPrepareGradeBySubject(
                                $tblPrepareCertificate,
                                $tblPerson,
                                $tblGrade->getServiceTblDivision(),
                                $tblGrade->getServiceTblSubject(),
                                $tblTask->getTblTestType()
                            );
                            if ($tblPrepareGrade) {
                                if ($tblPrepareGrade->getGrade() != $tblGrade->getDisplayGrade()) {
                                    return true;
                                }
                            } else {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
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
                }
            }
            if ($tblCompany) {
                $Content['Company']['Id'] = $tblCompany->getId();
            }

            // Arbeitsgemeinschaften
            if ($tblStudent
                && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                    $tblStudent, $tblSubjectType
                ))
            ) {
                $tempList = array();
                foreach ($tblSubjectList as $tblStudentSubject) {
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
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                    $tblStudent, $tblSubjectType
                ))
            ) {
                if ($tblSubjectList) {
                    foreach ($tblSubjectList as $tblStudentSubject) {
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
                foreach ($tblPrepareInformationList as $tblPrepareInformation) {
                    $Content['Input'][$tblPrepareInformation->getField()] = $tblPrepareInformation->getValue();
                }
            }

            // Klassenlehrer
            if ($tblPrepare->getServiceTblPersonSigner()) {
                $Content['DivisionTeacher']['Name'] = $tblPrepare->getServiceTblPersonSigner()->getFullName();
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

            // Fachnoten
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
}