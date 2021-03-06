<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2016
 * Time: 13:03
 */

namespace SPHERE\Application\Education\Certificate\GradeInformation;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
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

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Certificate\GradeInformation
 */
class Service
{

    /**
     * @param IFormInterface|null $Stage
     * @param TblDivision $tblDivision
     * @param string $Route
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createGradeInformation(
        IFormInterface $Stage = null,
        TblDivision $tblDivision,
        $Route = 'Teacher',
        $Data
    ) {

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
            $tblPrepare = Prepare::useService()->createPrepareData(
                $tblDivision,
                $Data['Date'],
                $Data['Name'],
                true
            );

            // letzten Notenaufträge vorselektieren
            if ($tblPrepare) {
                $tblAppointedDateTaskList = Evaluation::useService()->getTaskAllByDivision(
                    $tblDivision, Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
                );
                $tblBehaviorTaskList = Evaluation::useService()->getTaskAllByDivision(
                    $tblDivision, Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK')
                );
                if ($tblAppointedDateTaskList || $tblBehaviorTaskList) {
                    Prepare::useService()->updatePrepareData(
                        $tblPrepare,
                        $tblPrepare->getDate(),
                        $tblPrepare->getName(),
                        $tblAppointedDateTaskList ? current($tblAppointedDateTaskList) : null,
                        $tblBehaviorTaskList ? current($tblBehaviorTaskList) : null
                    );
                }
            }

            // Vorlage (Template) vorselektieren
            if (($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
                && ($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GradeInformation'))
            ) {
                foreach ($tblStudentList as $tblPerson) {
                    Prepare::useService()->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, $tblCertificate);
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Noteninformation ist erfasst worden.')
            . new Redirect('/Education/Certificate/GradeInformation/Create', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblDivision->getId(),
                'Route' => $Route
            ));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param string $Route
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateGradeInformation(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        $Route = 'Teacher',
        $Data
    ) {

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
            Prepare::useService()->updatePrepareData(
                $tblPrepare,
                $Data['Date'],
                $Data['Name'],
                $tblPrepare->getServiceTblAppointedDateTask() ? $tblPrepare->getServiceTblAppointedDateTask() : null,
                $tblPrepare->getServiceTblBehaviorTask() ? $tblPrepare->getServiceTblBehaviorTask() : null,
                $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : null
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Noteninformation ist geändert worden.')
            . new Redirect('/Education/Certificate/GradeInformation/Create', Redirect::TIMEOUT_SUCCESS, array(
                'DivisionId' => $tblPrepare->getServiceTblDivision() ? $tblPrepare->getServiceTblDivision()->getId() : null,
                'Route' => $Route
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

        Prepare::useService()->updatePrepareData(
            $tblPrepare,
            $tblPrepare->getDate(),
            $tblPrepare->getName(),
            $tblTask,
            $tblPrepare->getServiceTblBehaviorTask() ? $tblPrepare->getServiceTblBehaviorTask() : null,
            $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : null
        );

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Stichtagsnotenauftrag wurde ausgewählt.')
        . new Redirect('/Education/Certificate/GradeInformation/Setting', Redirect::TIMEOUT_SUCCESS, array(
            'PrepareId' => $tblPrepare->getId()
        ));
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
//        if ($tblPrepare->getServiceTblBehaviorTask()
//            && $tblPrepare->getServiceTblBehaviorTask()->getId() !== $tblTask->getId()
//        ) {
//            Prepare::useService()->destroyPrepareGrades($tblPrepare, $tblTask->getTblTestType());
//        }

        Prepare::useService()->updatePrepareData(
            $tblPrepare,
            $tblPrepare->getDate(),
            $tblPrepare->getName(),
            $tblPrepare->getServiceTblAppointedDateTask() ? $tblPrepare->getServiceTblAppointedDateTask() : null,
            $tblTask,
            $tblPrepare->getServiceTblPersonSigner() ? $tblPrepare->getServiceTblPersonSigner() : null
        );

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnotenauftrag wurde ausgewählt.')
        . new Redirect('/Education/Certificate/GradeInformation/Setting/Preview', Redirect::TIMEOUT_SUCCESS, array(
            'PrepareId' => $tblPrepare->getId()
        ));

    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param string $Route
     * @param $Grades
     * @param $Remarks
     *
     * @return IFormInterface|string
     */
    public function updatePrepareBehaviorGradesAndRemark(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        $Route = 'Teacher',
        $Grades,
        $Remarks
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Grades && null === $Remarks) {
            return $Stage;
        }

        $error = false;
//            if ($tblScoreType) {
//                foreach ($Data as $gradeTypeId => $value) {
//                    if (trim($value) !== '' && $tblScoreType) {
//                        if (!preg_match('!' . $tblScoreType->getPattern() . '!is', trim($value))) {
//                            $error = true;
//                            break;
//                        }
//                    }
//                }
//            }

        if ($error) {
            $Stage->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich.
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $Stage;
        } else {
            if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'))
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
            ) {
                if ($Grades) {
                    foreach ($Grades as $personId => $personGrades) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))
                            && is_array($personGrades)
                        ) {
                            foreach ($personGrades as $gradeTypeId => $value) {
                                if (trim($value) && trim($value) !== ''
                                    && ($tblGradeType = Gradebook::useService()->getGradeTypeById($gradeTypeId))
                                ) {
                                    Prepare::useService()->updatePrepareGradeForBehavior(
                                        $tblPrepare, $tblPerson, $tblDivision, $tblTestType, $tblGradeType,
                                        trim($value)
                                    );
                                }
                            }
                        }
                    }
                }



                if ($Remarks) {
                    foreach ($Remarks as $personId => $remark) {
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            $Content['P' . $personId]['Input']['Remark'] = $remark;

                            Prepare::useService()->updatePrepareInformationDataList($tblPrepare, $tblPerson,
                                $Content, null);
                        }
                    }
                }

                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
                . new Redirect('/Education/Certificate/GradeInformation/Setting/Preview',
                    Redirect::TIMEOUT_SUCCESS, array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route
                    ));
            }
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
    public function updatePrepareStudentSetTemplate(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblCertificate $tblCertificate
    ) {

        Prepare::useService()->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, $tblCertificate);

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Vorlage wurde ausgewählt.')
        . new Redirect('/Education/Certificate/GradeInformation/Setting/Template', Redirect::TIMEOUT_SUCCESS, array(
            'PrepareId' => $tblPrepare->getId(),
            'PersonId' => $tblPerson->getId()
        ));
    }
}