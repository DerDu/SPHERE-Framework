<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.09.2016
 * Time: 08:37
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount;

use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $MinimumGradeCount
     *
     * @return Stage
     */
    public function frontendMinimumGradeCount($MinimumGradeCount = null)
    {

        if(!isset($_POST['MinimumGradeCount']['Period'])){
            $_POST['MinimumGradeCount']['Period'] = SelectBoxItem::PERIOD_FULL_YEAR;
        }

        $Stage = new Stage('Mindestnotenanzahl', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Mindestnotenanzahlen verwaltet.' . '<br>' .
            'Es muss mindestens eine Klassenstufe vergeben werden für diese dann die Mindestnotenanzahl berücksichtigt wird.' . '<br>' .
            'Optional kann die Mindestnotenanzahl auf ein Fach und/oder ein Zensuren-Typ beschränkt werden.'
        );

        $TableContent = array();
        $list = array();
        if (($tblMinimumGradeCountAll = Gradebook::useService()->getMinimumGradeCountAll())) {
            foreach ($tblMinimumGradeCountAll as $tblMinimumGradeCount) {
                $tblGradeType = $tblMinimumGradeCount->getTblGradeType();
                $tblSubject = $tblMinimumGradeCount->getServiceTblSubject();
                if (($tblLevel = $tblMinimumGradeCount->getServiceTblLevel())
                    && ($tblType = $tblLevel->getServiceTblType())
                ) {
                    $typeName = $tblType->getName();
                    if ($typeName == 'Grundschule') {
                        $typeName = 'GS';
                    } elseif ($typeName == 'Mittelschule / Oberschule') {
                        $typeName = 'OS';
                    } elseif ($typeName == 'Gymnasium') {
                        $typeName = 'GYM';
                    }
                    $levelName = $tblLevel->getName() . ' (' . $typeName . ')';

                    if (isset($list['H' . $tblMinimumGradeCount->getHighlighted()
                        . 'G' . ($tblGradeType ? $tblGradeType->getId() : 0)
                        . 'P' . $tblMinimumGradeCount->getPeriod()
                        . 'C' . $tblMinimumGradeCount->getCourse()
                        . 'N' . $tblMinimumGradeCount->getCount()])
                    ) {
                        $list['H' . $tblMinimumGradeCount->getHighlighted()
                        . 'G' . ($tblGradeType ? $tblGradeType->getId() : 0)
                        . 'P' . $tblMinimumGradeCount->getPeriod()
                        . 'C' . $tblMinimumGradeCount->getCourse()
                        . 'N' . $tblMinimumGradeCount->getCount()]
                        ['Levels'][$tblLevel->getId()] = $levelName;

                        if ($tblSubject) {
                            $list['H' . $tblMinimumGradeCount->getHighlighted()
                            . 'G' . ($tblGradeType ? $tblGradeType->getId() : 0)
                            . 'P' . $tblMinimumGradeCount->getPeriod()
                            . 'C' . $tblMinimumGradeCount->getCourse()
                            . 'N' . $tblMinimumGradeCount->getCount()]
                            ['Subjects'][$tblSubject->getId()] = $tblSubject->getAcronym();

                            $list['H' . $tblMinimumGradeCount->getHighlighted()
                            . 'G' . ($tblGradeType ? $tblGradeType->getId() : 0)
                            . 'P' . $tblMinimumGradeCount->getPeriod()
                            . 'C' . $tblMinimumGradeCount->getCourse()
                            . 'N' . $tblMinimumGradeCount->getCount()]
                            ['SubjectsLevelVerify'][$tblSubject->getId()][$tblLevel->getId()] = $levelName;
                        }

                    } else {
                        $subjects = $tblSubject ? array($tblSubject->getId() => $tblSubject->getAcronym()) : array();
                        $list['H' . $tblMinimumGradeCount->getHighlighted()
                        . 'G' . ($tblGradeType ? $tblGradeType->getId() : 0)
                        . 'P' . $tblMinimumGradeCount->getPeriod()
                        . 'C' . $tblMinimumGradeCount->getCourse()
                        . 'N' . $tblMinimumGradeCount->getCount()] = array(
                            'Id' => $tblMinimumGradeCount->getId(),
                            'GradeType' => $tblMinimumGradeCount->getGradeTypeDisplayName(),
                            'Period' => $tblMinimumGradeCount->getPeriodDisplayName(),
                            'Course' => $tblMinimumGradeCount->getCourseDisplayName(),
                            'Count' => $tblMinimumGradeCount->getCount(),
                            'Levels' => array($tblLevel->getId() => $levelName),
                            'Subjects' => $subjects,
                            'SubjectsLevelVerify' => array($tblSubject ? $tblSubject->getId() : 0 => array($tblLevel->getId() => $levelName))
                        );
                    }
                }
            }
        }

        foreach ($list as $item) {
            sort($item['Levels'], SORT_NATURAL);
            asort($item['Subjects']);

            // Fächer Suchen welche nicht in weniger Klassenstufen verwendet werden
            if (isset($item['SubjectsLevelVerify'])) {
                $maxCount = 0;
                foreach ($item['SubjectsLevelVerify'] as $subjectId => $levels) {
                    if (($count = count($levels)) > $maxCount) {
                        $maxCount = $count;
                    }
                }

                foreach ($item['SubjectsLevelVerify'] as $subjectTempId => $levelsTemp) {
                    if (count($levelsTemp) < $maxCount
                        && isset($item['Subjects'][$subjectTempId])
                    ) {
                        sort($levelsTemp, SORT_NATURAL);
                        $item['Subjects'][$subjectTempId] = new ToolTip('(' . $item['Subjects'][$subjectTempId] . ')', implode(', ', $levelsTemp));
                    }
                }
            }

            $TableContent[] = array(
                'GradeType' => $item['GradeType'],
                'Period' => $item['Period'],
                'Course' => $item['Course'],
                'Count' => $item['Count'],
                'Levels' => implode(', ', $item['Levels']),
                'Subjects' => implode(', ', $item['Subjects']),
                'Option' => (new Standard('', '/Education/Graduation/Gradebook/MinimumGradeCount/Edit',
                        new Edit(), array(
                            'Id' => $item['Id']
                        ), 'Bearbeiten'))
                    . (new Standard('', '/Education/Graduation/Gradebook/MinimumGradeCount/Destroy',
                        new Remove(),
                        array('Id' => $item['Id']), 'Löschen'))
            );
        }

        $Form = $this->formMinimumGradeCount()
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null,
                                array(
                                    'GradeType' => 'Zensuren-Typ',
                                    'Period' => 'Zeitraum',
                                    'Course' => 'SEKII - Kurs',
                                    'Count' => 'Anzahl',
                                    'Levels' => 'Klassenstufen',
                                    'Subjects' => 'Fächer',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array('0', 'asc'),
                                        array('1', 'asc'),
                                        array('2', 'asc'),
                                        array('3', 'asc')
                                    ),
                                    'columnDefs' => array(
                                        array('orderable' => false, 'targets' => -1),
                                    ),
                                )
                            )
                        ))
                    )),
                ), new Title(new Listing() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Gradebook::useService()->updateMinimumGradeCount($Form,
                                $MinimumGradeCount)
                            ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formMinimumGradeCount()
    {

        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))) {
            $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
        } else {
            $tblGradeTypeList = array();
        }

        $tblGradeTypeList[] = new SelectBoxItem(-SelectBoxItem::HIGHLIGHTED_ALL, 'Alle Zensuren-Typen');
        $tblGradeTypeList[] = new SelectBoxItem(-SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED, 'Nur große Zensuren-Typen (Fett marktiert)');
        $tblGradeTypeList[] = new SelectBoxItem(-SelectBoxItem::HIGHLIGHTED_IS_NOT_HIGHLIGHTED, 'Nur kleine Zensuren-Typen (nicht Fett markiert)');

        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_FULL_YEAR, '-Gesamtes Schuljahr-');
        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_FIRST_PERIOD, '1. Halbjahr');
        $periodList[] = new SelectBoxItem(SelectBoxItem::PERIOD_SECOND_PERIOD, '2. Halbjahr');

        $courseList[] = new SelectBoxItem(SelectBoxItem::COURSE_NONE, '-[ nicht ausgewählt ]-');
        $courseList[] = new SelectBoxItem(SelectBoxItem::COURSE_ADVANCED, 'Leistungskurs');
        $courseList[] = new SelectBoxItem(SelectBoxItem::COURSE_BASIC, 'Grundkurs');

        $schoolTypeList = array();
        if ($tblLevelAll = Division::useService()->getLevelAll()) {
            foreach ($tblLevelAll as $tblLevel) {
                if (($tblType = $tblLevel->getServiceTblType())) {
                    $schoolTypeList[$tblType->getId()][$tblLevel->getName()] =
                        new CheckBox('MinimumGradeCount[Levels][' . $tblLevel->getId() . ']', $tblLevel->getName(), 1);
                }
            }
        }

        $levelColumns = array();
        foreach ($schoolTypeList as $typeId => $levels) {
            if (($tblTypeItem = Type::useService()->getTypeById($typeId))) {
                ksort($levels);
                // für Sortierung
                if ($tblTypeItem->getName() == 'Grundschule') {
                    $key = 1;
                } elseif ($tblTypeItem->getName() == 'Mittelschule / Oberschule') {
                    $key = 2;
                } elseif ($tblTypeItem->getName() == 'Gymnasium') {
                    $key = 3;
                } else {
                    $key = 10  + $typeId;
                }

                $levelColumns[$key] = new LayoutColumn(
                    new Panel($tblTypeItem->getName(), $levels), 3
                );
            }
        }
        ksort($levelColumns);

        if (($tblSubjectAll = Subject::useService()->getSubjectAll())) {
            $tblSubjectAll = $this->getSorter($tblSubjectAll)->sortObjectBy('Name');
            $layoutColumns = array();
            /** @var TblSubject $tblSubject */
            foreach ($tblSubjectAll as $tblSubject) {
                $layoutColumns[] = new LayoutColumn(
                    new CheckBox('MinimumGradeCount[Subjects][' . $tblSubject->getId() . ']', $tblSubject->getDisplayName(), 1), 3
                );
            }

            $layoutSubjects = new Layout(new LayoutGroup(new LayoutRow($layoutColumns)));
        } else {
            $layoutSubjects = new Warning('Es sind keine Fächer vorhanden') . new Exclamation();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel(
                        'Fächer',
                        $layoutSubjects,
                        Panel::PANEL_TYPE_INFO
                    )
                ))
            )),
            new FormRow(array(
                new FormColumn(array(
                    new Panel(
                        'Klassenstufen'  . new DangerText('*'),
                        new Layout(new LayoutGroup(new LayoutRow($levelColumns))),
                        Panel::PANEL_TYPE_INFO
                    )
                ))
            )),
            new FormRow(array(
                new FormColumn(
                    new SelectBox('MinimumGradeCount[GradeType]', 'Zensuren-Typ', array('{{ Code }} - {{ Name }}' => $tblGradeTypeList)), 3
                ),
                new FormColumn(
                    new SelectBox('MinimumGradeCount[Period]', 'Zeitraum', array('{{ Name }}' => $periodList)), 3
                ),
                new FormColumn(
                    new SelectBox('MinimumGradeCount[Course]', 'SEKII - Kurs', array('{{ Name }}' => $courseList)), 3
                ),
                new FormColumn(
                    (new NumberField('MinimumGradeCount[Count]', '', 'Anzahl ', new Quantity()))->setRequired(), 3
                ),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new DangerText(new Primary('Speichern', new Save()) . ' * Pflichtfeld')
                )),
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $MinimumGradeCount
     *
     * @return Stage|string
     */
    public function frontendEditMinimumGradeCount($Id = null, $MinimumGradeCount = null)
    {

        $Stage = new Stage('Mindestnotenanzahl', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Gradebook/MinimumGradeCount', new ChevronLeft())
        );

        $tblMinimumGradeCount = Gradebook::useService()->getMinimumGradeCountById($Id);
        if ($tblMinimumGradeCount) {
            $highlighted = $tblMinimumGradeCount->getHighlighted();
            $tblGradeType = $tblMinimumGradeCount->getTblGradeType();
            $period = $tblMinimumGradeCount->getPeriod();
            $course = $tblMinimumGradeCount->getCourse();
            $count = $tblMinimumGradeCount->getCount();

            if (($tblMinimumGradeCountList = Gradebook::useService()->getMinimumGradeCountAllBy(
                $highlighted,
                $tblGradeType ? $tblGradeType : null,
                $period,
                $course,
                $count))
            ) {
                $global = $this->getGlobal();
                if (!$global->POST) {
                    $global->POST['MinimumGradeCount']['GradeType'] = $tblGradeType ? $tblGradeType : -$highlighted;
                    $global->POST['MinimumGradeCount']['Period'] = $period;
                    $global->POST['MinimumGradeCount']['Course'] = $course;
                    $global->POST['MinimumGradeCount']['Count'] = $count;

                    foreach ($tblMinimumGradeCountList as $item) {
                        if (($tblLevel = $item->getServiceTblLevel())) {
                            $global->POST['MinimumGradeCount']['Levels'][$tblLevel->getId()] = 1;
                        }
                        if (($tblSubject = $item->getServiceTblSubject())) {
                            $global->POST['MinimumGradeCount']['Subjects'][$tblSubject->getId()] = 1;
                        }
                    }

                    $global->savePost();
                }
            }



            $Form = $this->formMinimumGradeCount()
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateMinimumGradeCount($Form, $MinimumGradeCount, $tblMinimumGradeCount))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Mindestnotenanzahl nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyMinimumGradeCount(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Mindesnotenanzahl', 'Löschen');

        $tblMinimumGradeCount = Gradebook::useService()->getMinimumGradeCountById($Id);
        if ($tblMinimumGradeCount) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/MinimumGradeCount', new ChevronLeft())
            );

            $tblMinimumGradeCountList = Gradebook::useService()->getMinimumGradeCountAllBy(
                $tblMinimumGradeCount->getHighlighted(),
                $tblMinimumGradeCount->getTblGradeType() ? $tblMinimumGradeCount->getTblGradeType() : null,
                $tblMinimumGradeCount->getPeriod(),
                $tblMinimumGradeCount->getCourse(),
                $tblMinimumGradeCount->getCount()
            );

            if ($tblMinimumGradeCountList) {
                $levels = array();
                $subjects = array();
                foreach ($tblMinimumGradeCountList as $item) {
                    if (($tblLevel = $item->getServiceTblLevel())
                        && ($tblType = $tblLevel->getServiceTblType())
                    ) {
                        $typeName = $tblType->getName();
                        if ($typeName == 'Grundschule') {
                            $typeName = 'GS';
                        } elseif ($typeName == 'Mittelschule / Oberschule') {
                            $typeName = 'OS';
                        } elseif ($typeName == 'Gymnasium') {
                            $typeName = 'GYM';
                        }
                        $levelName = $tblLevel->getName() . ' (' . $typeName . ')';
                        $levels[$tblLevel->getId()] = $levelName;
                    }

                    if (($tblSubject = $item->getServiceTblSubject())) {
                        $subjects[$tblSubject->getId()] = $tblSubject->getAcronym();
                    }
                }
                sort($levels);
                sort($subjects);

                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel(new Question() . ' Diese Mindestnotenanzahl wirklich löschen?',
                                array(
                                    'Zensuren-Typ: ' . $tblMinimumGradeCount->getGradeTypeDisplayName(),
                                    'Zeitraum: ' . $tblMinimumGradeCount->getPeriodDisplayName(),
                                    'SEKII - Kurs: ' . $tblMinimumGradeCount->getCourseDisplayName(),
                                    'Anzahl: ' . $tblMinimumGradeCount->getCount(),
                                    'Klassenstufen: ' . implode(', ', $levels),
                                    'Fächer: ' . implode(', ', $subjects)
                                ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Education/Graduation/Gradebook/MinimumGradeCount/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                . new Standard(
                                    'Nein', '/Education/Graduation/Gradebook/MinimumGradeCount', new Disable())
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (Gradebook::useService()->destroyBulkMinimumGradeCountList($tblMinimumGradeCountList)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                        . ' Die Mindestnotenanzahl wurde gelöscht')
                                    : new Danger(new Ban() . ' Die Mindestnotenanzahl konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount',
                                    Redirect::TIMEOUT_SUCCESS)
                            )))
                        )))
                    );
                }
            }

            return $Stage;
        }

        return $Stage . new Danger('Mindestnotenanzahl nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Gradebook/MinimumGradeCount', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param null $Data
     * @param null $PersonId
     *
     * @return Stage
     */
    public function frontendTeacherMinimumGradeCountReporting($Data = null, $PersonId = null)
    {

        return $this->setMinimumGradeCountReporting($Data, true, $PersonId);
    }

    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendHeadmasterMinimumGradeCountReporting($Data = null)
    {

        return $this->setMinimumGradeCountReporting($Data, false);
    }

    /**
     * @param $Data
     * @param bool $IsDivisionTeacher
     * @param null $PersonId
     *
     * @return Stage
     */
    private function setMinimumGradeCountReporting($Data, $IsDivisionTeacher, $PersonId = null)
    {
        $stage = new Stage('Mindestnotenanzahl', 'Auswertung');
        $stage->addButton(new Standard(
            'Zurück',
            $IsDivisionTeacher ? '/Education/Graduation/Gradebook/Gradebook/Teacher' : '/Education/Graduation/Gradebook/Gradebook/Headmaster',
            new ChevronLeft()
        ));

        if ($Data == null && ($tblYearList = Term::useService()->getYearByNow())) {
            $global = $this->getGlobal();

            $tblYear = reset($tblYearList);
            $global->POST['Data']['Year'] = $tblYear->getId();

            $global->savePost();
        }

        $receiverContent = ApiMinimumGradeCount::receiverContent(
            (new ApiMinimumGradeCount())->loadMinimumGradeCountReporting($Data, $IsDivisionTeacher, $PersonId)
        );

        $yearSelectBox = (new SelectBox('Data[Year]', 'Schuljahr',
            array('DisplayName' => Term::useService()->getYearAll())))->setRequired();
        $typeSelectBox = new SelectBox('Data[Type]', 'Schulart', array('Name' => Type::useService()->getTypeAll()));
        if (!$IsDivisionTeacher) {
            $typeSelectBox->setRequired();
        }
        $divisionTextField = new TextField('Data[DivisionName]', '', 'Klasse');

        $button = (new \SPHERE\Common\Frontend\Link\Repository\Primary('Filtern', '', new Filter()))
            ->ajaxPipelineOnClick(ApiMinimumGradeCount::pipelineCreateMinimumGradeCountReportingContent($receiverContent, $Data, $IsDivisionTeacher, $PersonId));

        $form = (new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new Panel(
                    'Filter',
                    new Layout (new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                $yearSelectBox, 4
                            ),
                            new LayoutColumn(
                                $typeSelectBox, 4
                            ),
                            new LayoutColumn(
                                $divisionTextField, 4
                            ),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                $button
                            ),
                        )),
                    ))),
                    Panel::PANEL_TYPE_INFO
                )
            )
        )))))->disableSubmitAction();

        $stage->setContent(
           $form
            . $receiverContent
        );

        return $stage;
    }
}