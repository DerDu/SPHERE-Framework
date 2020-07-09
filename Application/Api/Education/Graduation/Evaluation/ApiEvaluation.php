<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 30.11.2018
 * Time: 08:41
 */

namespace SPHERE\Application\Api\Education\Graduation\Evaluation;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Frontend;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class ApiEvaluation
 *
 * @package SPHERE\Application\Api\Education\Graduation\Evaluation
 */
class ApiEvaluation extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('loadTestPlanning');

        $Dispatcher->registerMethod('openGradeTextModal');
        $Dispatcher->registerMethod('setGradeText');
        $Dispatcher->registerMethod('changeGradeText');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverContent($Content = '', $Identifier = '')
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param AbstractReceiver $Receiver
     * @param $Data
     * @param $IsDivisionTeacher
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateTestPlanningContent(AbstractReceiver $Receiver, $Data, $IsDivisionTeacher, $PersonId)
    {

        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter($Receiver, self::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTestPlanning'
        ));
        $FieldEmitter->setPostPayload(array(
            'Data' => $Data,
            'IsDivisionTeacher' => $IsDivisionTeacher,
            'PersonId' => $PersonId
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Leistungsüberprüfungen werden aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param $TestId
     *
     * @return Pipeline
     */
    public static function pipelineOpenGradeTextModal($TestId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openGradeTextModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'TestId' => $TestId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $TestId
     *
     * @return Pipeline
     */
    public static function pipelineSetGradeText($TestId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'setGradeText',
        ));
        $emitter->setPostPayload(array(
            'TestId' => $TestId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $gradeTextId
     * @param $personId
     *
     * @return Pipeline
     */
    public static function pipelineChangeGradeText($gradeTextId, $personId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverContent('', 'ChangeGradeText_' . $personId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeGradeText',
        ));
        $ModalEmitter->setPostPayload(array(
            'GradeTextId' => $gradeTextId,
            'PersonId' => $personId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     * @param null $IsDivisionTeacher
     * @param null $PersonId
     *
     * @return Layout|string
     */
    public function loadTestPlanning($Data = null, $IsDivisionTeacher = null, $PersonId = null)
    {
        $IsDivisionTeacher = $IsDivisionTeacher === 'true';
        if ($Data === null) {
            return '';
        }

        if (!($tblYear = Term::useService()->getYearById($Data['Year']))) {
            return new Warning('Bitte wählen Sie ein Schuljahr aus!', new Exclamation());
        }

        $tblGradeType = false;
        $isHighlighted = false;
        if ($Data['GradeType'] < 0) {
            if ($Data['GradeType'] == -SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED) {
                $isHighlighted = true;
            }
        } elseif (($tblGradeType = Gradebook::useService()->getGradeTypeById($Data['GradeType']))) {

        } else {
            return new Warning('Bitte wählen Sie einen Zensuren-Typ aus!', new Exclamation());
        }

        if ($Data['Option']  == 2) {
            $dateTime = new \DateTime('now');
        } elseif ($Data['Option']  == 1) {
            $dateTime = null;
        } else {
            return new Warning('Bitte wählen Sie eine Option aus!', new Exclamation());
        }
        $nowWeek = date('W');
        $nowYear = (new \DateTime('now'))->format('Y');

        $tblType = Type::useService()->getTypeById($Data['Type']);

        if (isset($Data['DivisionName']) && ($divisionName = $Data['DivisionName']) && $divisionName != '') {
            $tblDivisionList = Division::useService()->getDivisionAllByName($divisionName, $tblYear, $tblType ? $tblType : null);
            if (empty($tblDivisionList)) {
                return new Warning('Klasse nicht gefunden', new Exclamation());
            }
        } else {
            if ($tblType) {
                $tblDivisionList = Division::useService()->getDivisionAllByYearAndType($tblYear, $tblType);
            } else {
                $tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear);
            }
        }

        // Klassenlehrer können nur ihre eigenen Klassen sehen
        if ($IsDivisionTeacher && $tblDivisionList) {
            $tempDivisionList = array();
            if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson)) {
                        $tempDivisionList[] = $tblDivision;
                    }
                }
            }

            $tblDivisionList = empty($tempDivisionList) ? false : $tempDivisionList;
        }

        if ($tblDivisionList
            && ($tblTestList = Evaluation::useService()->getTestListForPlanning($tblDivisionList, $tblGradeType ? $tblGradeType : null, $isHighlighted))
        ) {
            $testArray = array();

            $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date', new DateTimeSorter());
            /** @var TblTest $tblTest */
            foreach($tblTestList as $tblTest) {
                if (($date = $tblTest->getDate()) || ($date = $tblTest->getFinishDate())) {
                    // Tests ab der aktuellen Woche
                    if ($dateTime) {
                        $dateWeek = date('W', strtotime($date));
                        $dateYear = (new \DateTime($date))->format('Y');
                        if ($dateWeek !== false && (($dateYear == $nowYear && $dateWeek >= $nowWeek) || $dateYear > $nowYear)) {
                            $testArray[$dateWeek][$tblTest->getId()] = $tblTest;
                        }
                    // alle Tests
                    } else {
                        $dateWeek = date('W', strtotime($date));
                        $testArray[$dateWeek][$tblTest->getId()] = $tblTest;
                    }
                }
            }

            $preview = Evaluation::useService()->getLayoutRowsForTestPlanning($testArray);
            if (!empty($preview)) {
                return new Layout(new LayoutGroup($preview));
            }
        }

        return new Warning('Keine Leistungsüberprüfungen gefunden', new Exclamation());
    }

    /**
     * @param $TestId
     *
     * @return String
     */
    public function openGradeTextModal($TestId)
    {
        $panel = '';
        if (($tblTest = Evaluation::useService()->getTestById($TestId))
            && ($tblDivision = $tblTest->getServiceTblDivision())
            && ($tblSubject = $tblTest->getServiceTblSubject())
        ) {
            $panel = new Panel(
                'Fach-Klasse',
                'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                $tblSubject->getName() .
                ($tblTest->getServiceTblSubjectGroup()
                    ? new Small(' (Gruppe: ' . $tblTest->getServiceTblSubjectGroup()->getName() . ')')
                    : ''
                ),
                Panel::PANEL_TYPE_INFO
            );
        }

        return
            new Title('Stichtagsnote - Zeugnistext der gesamten Fach-Klasse auswählen')
            . $panel
            . '<br>'
            . new Warning(
                'Es werden alle Zeugnistexte auf den gewählten Wert vorausgefüllt. Die Daten müssen anschließend noch gespeichert werden.',
                new Exclamation()
            )
            . new Well(new Form(new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new SelectBox(
                            'GradeText',
                            '&nbsp;&nbsp;Zeugnistext&nbsp;&nbsp;',
                            array(TblGradeText::ATTR_NAME => Gradebook::useService()->getGradeTextAll())
                        )
                    )
                ),
                new FormRow(
                    new FormColumn(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn('<br>'))))
                    )
                ),
                new FormRow(
                    new FormColumn(
                        (new Primary('Übernehmen', self::getEndpoint()))->ajaxPipelineOnClick(self::pipelineSetGradeText($TestId))
                    )
                )
            ))));
    }

    /**
     * @param $TestId
     *
     * @return Danger|string
     */
    public function setGradeText($TestId)
    {
        if (!($tblTest = Evaluation::useService()->getTestById($TestId))) {
            return new Danger('Leistungsüberprüfung nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $gradeTextId = $Global->POST['GradeText'];

        if (($tblDivision = $tblTest->getServiceTblDivision())
            && ($tblSubject = $tblTest->getServiceTblSubject())
            && ($tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                $tblDivision,
                $tblSubject,
                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
            ))
        ) {
            if ($tblDivisionSubject->getTblSubjectGroup()) {
                $tblStudentAll = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject, true);
            } else {
                $tblStudentAll = Division::useService()->getStudentAllByDivision($tblDivisionSubject->getTblDivision(), true);
            }

            $result = '';
            if ($tblStudentAll) {
                foreach ($tblStudentAll as $tblPerson) {
                   $result .= self::pipelineChangeGradeText($gradeTextId, $tblPerson->getId());
                }
            }

            return $result . self::pipelineClose();
        }

        return self::pipelineClose();
    }

    /**
     * @param $GradeTextId
     * @param $PersonId
     *
     * @return SelectBox
     */
    public function changeGradeText($GradeTextId, $PersonId)
    {
        return (new Frontend())->getGradeTextSelectBox($PersonId, $GradeTextId);
    }
}