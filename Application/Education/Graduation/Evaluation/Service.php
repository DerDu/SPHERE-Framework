<?php
namespace SPHERE\Application\Education\Graduation\Evaluation;

use SPHERE\Application\Education\Graduation\Evaluation\Service\Data;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Setup;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Graduation\Evaluation
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
     * @return bool|TblTestType[]
     */
    public function getTestTypesForGradeTypes()
    {

        return (new Data($this->getBinding()))->getTestTypesForGradeTypes();
    }

    /**
     * @return bool|TblTestType[]
     */
    public function getTestTypeAllWhereTask()
    {

        return (new Data($this->getBinding()))->getTestTypeAllWhereTask();
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param null|TblPeriod $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|TblTest[]
     */
    public function getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType = null,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
            $tblDivision, $tblSubject, $tblTestType, $tblPeriod, $tblSubjectGroup
        );
    }

    /**
     * @param TblTestType $tblTestType
     *
     * @return bool|TblTest[]
     */
    public function getTestAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTestAllByTestType($tblTestType);
    }

    /**
     * @param TblTestType $tblTestType
     * @param TblDivision $tblDivision
     * @return bool|TblTest[]
     */
    public function getTestAllByTestTypeAndDivision(TblTestType $tblTestType, TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTestAllByTestTypeAndDivision($tblTestType, $tblDivision);
    }

    /**
     * @return bool|TblTask[]
     */
    public function getTaskAll()
    {

        return (new Data($this->getBinding()))->getTaskAll();
    }

    /**
     * @param TblTestType $tblTestType
     *
     * @return bool|TblTask[]
     */
    public function getTaskAllByTestType(TblTestType $tblTestType)
    {

        return (new Data($this->getBinding()))->getTaskAllByTestType($tblTestType);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $DivisionSubjectId
     * @param null $Test
     * @param string $BasicRoute
     *
     * @return IFormInterface|string
     */
    public function createTest(IFormInterface $Stage = null, $DivisionSubjectId = null, $Test = null, $BasicRoute)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Test || $DivisionSubjectId === null) {
            return $Stage;
        }

        $Error = false;
        if (!($tblPeriod = Term::useService()->getPeriodById($Test['Period']))) {
            $Stage->setError('Test[Period]', 'Bitte wählen Sie einen Zeitraum aus');
            $Error = true;
        }
        if (!($tblGradeType = Gradebook::useService()->getGradeTypeById($Test['GradeType']))) {
            $Stage->setError('Test[GradeType]', 'Bitte wählen Sie einen Zensuren-Typ aus');
            $Error = true;
        }
        if (isset($Test['Date']) && empty($Test['Date'])) {
            $Stage->setError('Test[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if ($Error) {
            return $Stage;
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);

        if (!$tblDivisionSubject) {
            return new Danger(new Ban() . ' Fach-Klasse nicht gefunden')
            . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        if (!$tblDivisionSubject->getTblDivision()) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
            . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        if (!$tblDivisionSubject->getServiceTblSubject()) {
            return new Danger(new Ban() . ' Fach nicht gefunden')
            . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        if (!$tblGradeType) {
            return new Danger(new Ban() . ' Zensuren-Typ nicht gefunden')
            . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_ERROR,
                array('DivisionSubjectId' => $tblDivisionSubject->getId()));
        }

        (new Data($this->getBinding()))->createTest(
            $tblDivisionSubject->getTblDivision(),
            $tblDivisionSubject->getServiceTblSubject(),
            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
            $tblPeriod,
            $tblGradeType,
            $this->getTestTypeByIdentifier('TEST'),
            null,
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        return new Success('Die Leistungsüberprüfung ist angelegt worden',
            new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));

    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblTestType
     */
    public function getTestTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getTestTypeByIdentifier($Identifier);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $Id
     * @param                     $Test
     * @param string $BasicRoute
     *
     * @return IFormInterface|Redirect
     */
    public function updateTest(IFormInterface $Stage = null, $Id, $Test, $BasicRoute)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Test) {
            return $Stage;
        }

        $tblTest = $this->getTestById($Id);
        (new Data($this->getBinding()))->updateTest(
            $tblTest,
            $Test['Description'],
            $Test['Date'],
            $Test['CorrectionDate'],
            $Test['ReturnDate']
        );

        if (!$tblTest->getServiceTblDivision()) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
            . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }
        if (!$tblTest->getServiceTblSubject()) {
            return new Danger(new Ban() . ' Fach nicht gefunden')
            . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblTest->getServiceTblDivision(),
            $tblTest->getServiceTblSubject(),
            $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
        );

        return new Success('Test erfolgreich geändert.', new \SPHERE\Common\Frontend\Icon\Repository\Success()) .
        new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS,
            array('DivisionSubjectId' => $tblDivisionSubject->getId()));
    }

    /**
     * @param $Id
     *
     * @return bool|TblTest
     */
    public function getTestById($Id)
    {

        return (new Data($this->getBinding()))->getTestById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Task
     * @param TblYear $tblYear
     *
     * @return IFormInterface|string
     */
    public function createTask(IFormInterface $Stage = null, $Task, TblYear $tblYear = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Task) {
            return $Stage;
        }

        if (null === $tblYear) {
            return new Danger('Kein Schuljahr ausgewählt', new Exclamation());
        }

        $Error = false;
        if (!($tblTestType = Evaluation::useService()->getTestTypeById($Task['Type']))) {
            $Stage->setError('Task[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }
        if (isset($Task['Name']) && empty($Task['Name'])) {
            $Stage->setError('Task[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($Task['Date']) && empty($Task['Date'])) {
            $Stage->setError('Task[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['FromDate']) && empty($Task['FromDate'])) {
            $Stage->setError('Task[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['ToDate']) && empty($Task['ToDate'])) {
            $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        } else {
            $nowDate = (new \DateTime('now'))->format("Y-m-d");
            $toDate = new \DateTime($Task['ToDate']);
            $toDate = $toDate->format('Y-m-d');
            if ($nowDate && $toDate) {
                if ($nowDate < $toDate) {

                } else {
                    $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum in der Zukunft an');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            $tblPeriod = Term::useService()->getPeriodById($Task['Period']);
            $tblScoreType = Gradebook::useService()->getScoreTypeById($Task['ScoreType']);
            (new Data($this->getBinding()))->createTask(
                $tblTestType, $Task['Name'], $Task['Date'], $Task['FromDate'], $Task['ToDate'],
                $tblPeriod ? $tblPeriod : null, $tblScoreType ? $tblScoreType : null, $tblYear ? $tblYear : null
            );
            $Stage .= new Success('Notenauftrag erfolgreich angelegt',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTestType
     */
    public function getTestTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTestTypeById($Id);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $Id
     * @param                     $Task
     *
     * @return IFormInterface|Redirect
     */
    public function updateTask(IFormInterface $Stage = null, $Id, $Task)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Task) {
            return $Stage;
        }
        $Error = false;
        if (!($tblTestType = Evaluation::useService()->getTestTypeById($Task['Type']))) {
            $Stage->setError('Task[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $Error = true;
        }
        if (isset($Task['Name']) && empty($Task['Name'])) {
            $Stage->setError('Task[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($Task['Date']) && empty($Task['Date'])) {
            $Stage->setError('Task[Date]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['FromDate']) && empty($Task['FromDate'])) {
            $Stage->setError('Task[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Task['ToDate']) && empty($Task['ToDate'])) {
            $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        } else {
            $nowDate = (new \DateTime('now'))->format("Y-m-d");
            $toDate = new \DateTime($Task['ToDate']);
            $toDate = $toDate->format('Y-m-d');
            if ($nowDate && $toDate) {
                if ($nowDate < $toDate) {

                } else {
                    $Stage->setError('Task[ToDate]', 'Bitte geben Sie ein Datum in der Zukunft an');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            $tblTask = $this->getTaskById($Id);
            $tblPeriod = Term::useService()->getPeriodById($Task['Period']);
            $tblScoreType = Gradebook::useService()->getScoreTypeById($Task['ScoreType']);
            (new Data($this->getBinding()))->updateTask(
                $tblTask,
                $this->getTestTypeById($Task['Type']),
                $Task['Name'],
                $Task['Date'],
                $Task['FromDate'],
                $Task['ToDate'],
                $tblPeriod ? $tblPeriod : null,
                $tblScoreType ? $tblScoreType : null
            );

            $Stage .= new Success('Notenauftrag erfolgreich geändert',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_SUCCESS);

        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTask
     */
    public function getTaskById($Id)
    {

        return (new Data($this->getBinding()))->getTaskById($Id);
    }

    public function updateDivisionTasks(IFormInterface $Stage = null, $Id, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Stage;
        }

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {
            if ($tblTask->getTblTestType()->getIdentifier() == 'BEHAVIOR_TASK') {
                $isBehaviorTask = true;
            } else {
                $isBehaviorTask = false;
            }

            if ($isBehaviorTask) {
                // add
                if ($Data && isset($Data['GradeType'])) {
                    foreach ($Data['GradeType'] as $gradeTypeId => $value) {
                        $tblGradeType = Gradebook::useService()->getGradeTypeById($gradeTypeId);
                        if ($tblGradeType) {
                            if ($Data && isset($Data['Division'])) {
                                foreach ($Data['Division'] as $divisionId => $divisionValue) {
                                    $tblDivision = Division::useService()->getDivisionById($divisionId);
                                    if ($tblDivision) {
                                        $this->addBehaviorGradeTypeToDivisionAndTask(
                                            $tblTask, $tblDivision, $tblGradeType
                                        );
                                    }
                                }
                            }
                        }
                    }
                }

                // remove
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblDivision = $tblTest->getServiceTblDivision();
                        if ($tblDivision) {
                            if (!isset($Data['Division'][$tblDivision->getId()])) {
                                // delete all
                                $this->removeDivisionFromTask($tblTask, $tblDivision);
                            } elseif ($tblTest->getServiceTblGradeType()
                                && !isset($Data['GradeType'][$tblTest->getServiceTblGradeType()->getId()])
                            ) {
                                // delete single
                                (new Data($this->getBinding()))->destroyTest($tblTest);
                            }
                        }
                    }
                }

            } else {
                $tblDivisionList = array();
                $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);
                if ($tblTestAllByTask) {
                    foreach ($tblTestAllByTask as $tblTest) {
                        $tblDivision = $tblTest->getServiceTblDivision();
                        if ($tblDivision) {
                            $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                        }
                    }
                }

                // remove
                if (!empty($tblDivisionList)) {
                    /** @var TblDivision $tblDivision */
                    foreach ($tblDivisionList as $tblDivision) {
                        if (!isset($Data['Division'][$tblDivision->getId()])) {
                            $this->removeDivisionFromTask($tblTask, $tblDivision);
                        }
                    }
                }

                // add
                if ($Data && isset($Data['Division'])) {
                    foreach ($Data['Division'] as $divisionId => $value) {
                        $tblDivision = Division::useService()->getDivisionById($divisionId);
                        if ($tblDivision) {
                            $this->addDivisionToAppointedDateTask($tblTask, $tblDivision);
                        }
                    }
                }
            }
        }

        return $Stage;
    }

    public function addBehaviorGradeTypeToDivisionAndTask(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblGradeType $tblGradeType
    ) {

        $tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision(
            $tblDivision
        );

        if ($tblDivisionSubjectAll) {
            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                if ($tblDivisionSubject->getTblSubjectGroup()) {
                    if (!$this->existsTestByTaskAndGradeType(
                        $tblTask, $tblDivision, $tblDivisionSubject->getServiceTblSubject(), $tblGradeType,
                        $tblDivisionSubject->getTblSubjectGroup()
                    )
                    ) {
                        (new Data($this->getBinding()))->createTest(
                            $tblDivision,
                            $tblDivisionSubject->getServiceTblSubject(),
                            $tblDivisionSubject->getTblSubjectGroup(),
                            null,
                            $tblGradeType,
                            $tblTask->getTblTestType(),
                            $tblTask,
                            '',
                            $tblTask->getDate()
                        );
                    }
                } else {
                    if (!Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                        $tblDivision, $tblDivisionSubject->getServiceTblSubject()
                    )
                    ) {
                        if (!$this->existsTestByTaskAndGradeType(
                            $tblTask, $tblDivision, $tblDivisionSubject->getServiceTblSubject(), $tblGradeType)
                        ) {
                            (new Data($this->getBinding()))->createTest(
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                null,
                                null,
                                $tblGradeType,
                                $tblTask->getTblTestType(),
                                $tblTask,
                                '',
                                $tblTask->getDate()
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblTask              $tblTask
     * @param TblDivision          $tblDivision
     * @param TblSubject           $tblSubject
     * @param TblGradeType         $tblGradeType
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool
     */
    public function existsTestByTaskAndGradeType(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblGradeType $tblGradeType,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->existsTestByTaskAndGradeType($tblTask, $tblDivision, $tblSubject,
            $tblGradeType, $tblSubjectGroup);
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision|null $tblDivision
     *
     * @return bool|Service\Entity\TblTest[]
     */
    public function getTestAllByTask(TblTask $tblTask, TblDivision $tblDivision = null)
    {

        return (new Data($this->getBinding()))->getTestAllByTask($tblTask, $tblDivision);
    }

    /**
     * @param TblTask     $tblTask
     * @param TblDivision $tblDivision
     */
    public function removeDivisionFromTask(
        TblTask $tblTask,
        TblDivision $tblDivision
    ) {

        $tblTestAllByTask = $this->getTestAllByTask($tblTask, $tblDivision);
        if ($tblTestAllByTask) {
            foreach ($tblTestAllByTask as $tblTest) {
                (new Data($this->getBinding()))->destroyTest($tblTest);
            }
        }
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivision $tblDivision
     */
    public function addDivisionToAppointedDateTask(
        TblTask $tblTask,
        TblDivision $tblDivision
    ) {

        $tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision(
            $tblDivision
        );

        if ($tblDivisionSubjectAll) {
            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                if ($tblDivisionSubject->getServiceTblSubject()) {
                    if ($tblTask->getTblTestType()->getId() == $this->getTestTypeByIdentifier('APPOINTED_DATE_TASK')) {
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            if (!$this->existsTestByTask($tblTask, $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(), $tblDivisionSubject->getTblSubjectGroup())
                            ) {
                                (new Data($this->getBinding()))->createTest(
                                    $tblDivision,
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblDivisionSubject->getTblSubjectGroup(),
                                    null,
                                    null,
                                    $tblTask->getTblTestType(),
                                    $tblTask,
                                    '',
                                    $tblTask->getDate()
                                );
                            }
                        } else {
                            if (!Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                $tblDivision, $tblDivisionSubject->getServiceTblSubject()
                            )
                            ) {
                                if (!$this->existsTestByTask($tblTask, $tblDivision,
                                    $tblDivisionSubject->getServiceTblSubject())
                                ) {
                                    (new Data($this->getBinding()))->createTest(
                                        $tblDivision,
                                        $tblDivisionSubject->getServiceTblSubject(),
                                        null,
                                        null,
                                        null,
                                        $tblTask->getTblTestType(),
                                        $tblTask,
                                        '',
                                        $tblTask->getDate()
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblTask         $tblTask
     * @param TblDivision     $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function existsTestByTask(
        TblTask $tblTask,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        return (new Data($this->getBinding()))->existsTestByTask($tblTask, $tblDivision, $tblSubject, $tblSubjectGroup);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDivision[]
     */
    public function getTestAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getTestAllByDivision($tblDivision);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return bool
     */
    public function destroyTest(TblTest $tblTest)
    {

        return (new Data($this->getBinding()))->destroyTest($tblTest);
    }

    /**
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function destroyTask(TblTask $tblTask)
    {

        return (new Data($this->getBinding()))->destroyTask($tblTask);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     *
     * @return IFormInterface|Redirect
     */
    public function getYear(IFormInterface $Stage = null, $Select = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $tblYear = Term::useService()->getYearById($Select['Year']);
        if (!$tblYear) {
            $Stage->setError('Select[Year]', new Exclamation() . ' Bitte wählen Sie ein Schuljahr aus');
            return $Stage;
        }

        return new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_SUCCESS, array(
            'YearId' => $tblYear->getId(),
        ));
    }

}
