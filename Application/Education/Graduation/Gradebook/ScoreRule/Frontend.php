<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 21.09.2016
 * Time: 11:54
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\ScoreRule;

use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\Frontend as FrontendMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Group;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\ScoreRule
 */
class Frontend extends FrontendMinimumGradeCount
{

    const SCORE_RULE = 0;
    const SCORE_CONDITION = 1;
    const GRADE_GROUP = 2;

    /**
     * @param null $ScoreRule
     *
     * @return Stage
     */
    public function frontendScore(
        $ScoreRule = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Berechnungsvorschriften, für die automatische Durchschnittsberechnung der Zensuren, verwaltet.' . '<br>' .
            'Die Berechnungsvorschrift bildet die 1. Ebene und setzt sich aus einer oder mehrerer Berechnungsvarianten
            zusammen.'
        );

        $this->setScoreStageMenuButtons($Stage, self::SCORE_RULE);

        $contentTable = array();
        $tblScoreRuleAll = Gradebook::useService()->getScoreRuleAll();
        if ($tblScoreRuleAll) {
            foreach ($tblScoreRuleAll as $tblScoreRule) {

                $structure = array();
                if ($tblScoreRule->getDescription() != '') {
                    $structure[] = 'Beschreibung: ' . $tblScoreRule->getDescription() . '<br>';
                }

                $tblScoreConditions = Gradebook::useService()->getScoreConditionsByRule($tblScoreRule);
                if ($tblScoreConditions) {
                    $tblScoreConditions = $this->getSorter($tblScoreConditions)->sortObjectBy('Priority');

                    $count = 1;
                    /** @var TblScoreCondition $tblScoreCondition */
                    foreach ($tblScoreConditions as $tblScoreCondition) {
                        $structure[] = $count++ . '. Berechnungsvariante: ' . $tblScoreCondition->getName()
                            . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Priorität: '
                            . $tblScoreCondition->getPriority();

                        $tblScoreConditionGradeTypeListByCondition = Gradebook::useService()->getScoreConditionGradeTypeListByCondition(
                            $tblScoreCondition
                        );
                        if ($tblScoreConditionGradeTypeListByCondition) {
                            $list = array();
                            foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeTypeList) {
                                if ($tblScoreConditionGradeTypeList->getTblGradeType()) {
                                    $list[] = $tblScoreConditionGradeTypeList->getTblGradeType()->getName();
                                }
                            }

                            $structure[] = '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;' . 'Bedingungen: ' . implode(', ',
                                    $list);
                        }

                        $tblScoreConditionGroupListByCondition = Gradebook::useService()->getScoreConditionGroupListByCondition(
                            $tblScoreCondition
                        );
                        if ($tblScoreConditionGroupListByCondition) {
                            foreach ($tblScoreConditionGroupListByCondition as $tblScoreConditionGroupList) {
                                $structure[] = '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;' . 'Zensuren-Gruppe: '
                                    . $tblScoreConditionGroupList->getTblScoreGroup()->getName()
                                    . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Faktor: '
                                    . $tblScoreConditionGroupList->getTblScoreGroup()->getDisplayMultiplier()
                                    . ($tblScoreConditionGroupList->getTblScoreGroup()->isEveryGradeASingleGroup()
                                        ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Noten einzeln ' : '');

                                $tblGradeTypeList = Gradebook::useService()->getScoreGroupGradeTypeListByGroup(
                                    $tblScoreConditionGroupList->getTblScoreGroup()
                                );
                                if ($tblGradeTypeList) {
                                    foreach ($tblGradeTypeList as $tblGradeType) {
                                        $structure[] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9702;&nbsp;&nbsp;'
                                            . 'Zensuren-Typ: '
                                            . ($tblGradeType->getTblGradeType() ? $tblGradeType->getTblGradeType()->getName() : '')
                                            . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Faktor: '
                                            . $tblGradeType->getDisplayMultiplier();
                                    }
                                } else {
                                    $structure[] = new Warning('Kein Zenuren-Typ hinterlegt.', new Ban());
                                }
                            }
                        } else {
                            $structure[] = new Warning('Keine Zenuren-Gruppe hinterlegt.', new Ban());
                        }
                        $structure[] = ' ';
                    }
                } else {
                    $structure[] = new Warning('Keine Berechnungsvariante hinterlegt.', new Ban());
                }

                $contentTable[] = array(
                    'Name' => $tblScoreRule->getName(),
                    'Structure' => empty($structure) ? '' : implode('<br>', $structure),
                    'Option' =>
                        (new Standard('', '/Education/Graduation/Gradebook/Score/Edit', new Edit(),
                            array('Id' => $tblScoreRule->getId()), 'Bearbeiten')) .
                        (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Select', new Listing(),
                            array('Id' => $tblScoreRule->getId()), 'Berechnungsvarianten auswählen')) .
                        (new Standard('', '/Education/Graduation/Gradebook/Score/Division', new Equalizer(),
                            array('Id' => $tblScoreRule->getId()), 'Fach-Klassen zuordnen')) .
                        (new Standard('', '/Education/Graduation/Gradebook/Score/SubjectGroup', new Group(),
                            array('Id' => $tblScoreRule->getId()), 'Fachgruppen zuordnen'))
                );
            }
        }

        $Form = $this->formScoreRule()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($contentTable, null, array(
                                'Name' => 'Name',
                                'Structure' => '',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->createScoreRule($Form, $ScoreRule))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param $view
     */
    private function setScoreStageMenuButtons(Stage $Stage, $view)
    {

        $text = ' Berechnungsvorschriften';
        $Stage->addButton(
            new Standard($view == self::SCORE_RULE ? new Edit() . new Info ($text) : $text,
                '/Education/Graduation/Gradebook/Score', null, null,
                'Erstellen/Berarbeiten')
        );

        $text = ' Berechnungsvarianten';
        $Stage->addButton(
            new Standard($view == self::SCORE_CONDITION ? new Edit() . new Info ($text) : $text,
                '/Education/Graduation/Gradebook/Score/Condition', null,
                null,
                'Erstellen/Berarbeiten')
        );

        $text = ' Zensuren-Gruppen';
        $Stage->addButton(
            new Standard($view == self::GRADE_GROUP ? new Edit() . new Info ($text) : $text,
                '/Education/Graduation/Gradebook/Score/Group', null, null,
                'Erstellen/Berarbeiten')
        );
    }

    /**
     * @return Form
     */
    private function formScoreRule()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreRule[Name]', '', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('ScoreRule[Description]', '', 'Beschreibung'), 8
                ),
            ))
        )));
    }

    /**
     * @param null $ScoreCondition
     *
     * @return Stage
     */
    public function frontendScoreCondition(
        $ScoreCondition = null
    ) {

        $Stage = new Stage('Berechnungsvariante', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Berechnungsvarianten verwaltet.' . '<br>' .
            'Die Berechnungsvariante bildet die 2. Ebene der Berechnungsvorschriften und setzt sich aus einer Priorität
            , Zensuren-Gruppen und Bedingungen (Zensuren-Typen) zusammen. <br>
            Die Priorität gibt an, in welcher Reihenfolge die Berechnungsvarianten
            (falls eine Berechnungsvorschrift mehrere Berechnungsvarianten enthält) berücksichtigt werden.
            Dabei hat die Berechnungsvariante mit der niedrigsten Zahl die höchste Priorität. <br>
            Die Bedingungen (Zensuren-Typen) geben an, ob für die Durchschnittsberechnung die Berechnungsvariante gewählt wird.
            Ist keine Bedingung hinterlegt, wird diese Berechnungsvariante immer gewählt. Hingegen wenn eine oder mehrere
            Bedingung(en) hinterlegt sind, wird diese Berechnungsvariante nur gewählt, wenn alle Zensuren-Typen bei den Zensuren
            des Schülers vorhanden sind.'
        );
        $this->setScoreStageMenuButtons($Stage, self::SCORE_CONDITION);

        $contentTable = array();
        $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();
        if ($tblScoreConditionAll) {
            foreach ($tblScoreConditionAll as $tblScoreCondition) {
                $scoreGroups = '';
                $tblScoreGroups = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                if ($tblScoreGroups) {
                    foreach ($tblScoreGroups as $tblScoreGroup) {
                        $scoreGroups .= $tblScoreGroup->getTblScoreGroup()->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getTblScoreGroup()->getDisplayMultiplier() . ')')) . ', ';
                    }
                }
                if (($length = strlen($scoreGroups)) > 2) {
                    $scoreGroups = substr($scoreGroups, 0, $length - 2);
                }

                $gradeTypes = array();
                $tblGradeTypes = Gradebook::useService()->getScoreConditionGradeTypeListByCondition($tblScoreCondition);
                if ($tblGradeTypes) {
                    foreach ($tblGradeTypes as $tblGradeType) {
                        if ($tblGradeType->getTblGradeType()) {
                            $gradeTypes[] = $tblGradeType->getTblGradeType()->getName();
                        }
                    }
                }
                if (empty($gradeTypes)) {
                    $gradeTypes = '';
                } else {
                    $gradeTypes = implode(', ', $gradeTypes);
                }

                $contentTable[] = array(
                    'Name' => $tblScoreCondition->getName(),
                    'ScoreGroups' => $scoreGroups,
                    'GradeTypes' => $gradeTypes,
                    'Priority' => $tblScoreCondition->getPriority(),
                    'Option' =>
                        (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Edit', new Edit(),
                            array('Id' => $tblScoreCondition->getId()), 'Bearbeiten')) .
                        (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Group/Select', new Listing(),
                            array('Id' => $tblScoreCondition->getId()), 'Zensuren-Gruppen auswählen')) .
                        (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/GradeType/Select',
                            new Equalizer(),
                            array('Id' => $tblScoreCondition->getId()), 'Zensuren-Typen (Bedingungen) auswählen'))
                );
            }
        }

        $Form = $this->formScoreCondition()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($contentTable, null, array(
                                'Name' => 'Name',
                                'ScoreGroups' => 'Zensuren-Gruppen',
                                'GradeTypes' => 'Zensuren-Typen (Bedingungen)',
                                'Priority' => 'Priorität',
//                                'Round' => 'Runden',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->createScoreCondition($Form, $ScoreCondition))
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
    private function formScoreCondition()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreCondition[Name]', 'Klassenarbeit 60% : Rest 40%', 'Name'), 10
                ),
//                new FormColumn(
//                    new TextField('ScoreCondition[Round]', '', 'Rundung'), 2
//                ),
                new FormColumn(
                    new NumberField('ScoreCondition[Priority]', '1', 'Priorität'), 2
                )
            ))
        )));
    }

    /**
     * @param null $ScoreGroup
     *
     * @return Stage
     */
    public function frontendScoreGroup(
        $ScoreGroup = null
    ) {

        $Stage = new Stage('Zensuren-Gruppe', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Zensuren-Gruppen verwaltet.' . '<br>' .
            'Die Zensuren-Gruppe bildet die 3. Ebene der Berechnungsvorschriften und setzt sich aus einem Faktor
            und Zensuren-Typen zusammen.' . '<br>' .
            'Der Faktor gibt an, wie die Zensuren-Gruppe als ganzes zu anderen Zensuren-Gruppen gewichtet wird.' . '<br>' .
            'Über die Option ' . new Italic('Noten einzeln')
            . ' werden alle Noten dieser Zensurengruppe nicht zu einem Durchschnitt zusammen gerechnet, sondern alle Noten dieser Gruppe einzeln gewertet.'
        );
        $this->setScoreStageMenuButtons($Stage, self::GRADE_GROUP);

        $contentTable = array();
        $tblScoreGroupAll = Gradebook::useService()->getScoreGroupAll();
        if ($tblScoreGroupAll) {
            foreach ($tblScoreGroupAll as $tblScoreGroup) {
                $gradeTypes = '';
                $tblScoreGroupGradeTypes = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                if ($tblScoreGroupGradeTypes) {
                    foreach ($tblScoreGroupGradeTypes as $tblScoreGroupGradeType) {
                        if ($tblScoreGroupGradeType->getTblGradeType()) {

                            $gradeTypes .= $tblScoreGroupGradeType->getTblGradeType()->getName()
                                . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroupGradeType->getDisplayMultiplier() . ')')) . ', ';
                        }
                    }
                }
                if (($length = strlen($gradeTypes)) > 2) {
                    $gradeTypes = substr($gradeTypes, 0, $length - 2);
                }

                $contentTable[] = array(
                    'Name' => $tblScoreGroup->getName(),
                    'Multiplier' => $tblScoreGroup->getMultiplier(),
                    'GradeTypes' => $gradeTypes,
                    'IsEveryGradeASingleGroup' => $tblScoreGroup->isEveryGradeASingleGroup() ? 'Ja' : new Muted('Nein'),
                    'Option' =>
                        (new Standard('', '/Education/Graduation/Gradebook/Score/Group/Edit', new Edit(),
                            array('Id' => $tblScoreGroup->getId()), 'Bearbeiten'))
                        . (new Standard('', '/Education/Graduation/Gradebook/Score/Group/GradeType/Select',
                            new Listing(),
                            array('Id' => $tblScoreGroup->getId()), 'Zensuren-Typen auswählen'))
                );
            }
        }

        $Form = $this->formScoreGroup()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($contentTable, null, array(
                                'Name' => 'Name',
                                'Multiplier' => 'Faktor',
                                'GradeTypes' => 'Zensuren-Typen',
                                'IsEveryGradeASingleGroup' => 'Noten einzeln',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Gradebook::useService()->createScoreGroup($Form, $ScoreGroup))
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
    private function formScoreGroup()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreGroup[Name]', '', 'Name'), 10
                ),
                new FormColumn(
                    new TextField('ScoreGroup[Multiplier]', '', 'Faktor'), 2
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new CheckBox('ScoreGroup[IsEveryGradeASingleGroup]',
                        'Noten einzeln (Noten dieser Gruppe als eigene Gruppe betrachten)', 1)
                )
            ))
        )));
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeSelect(
        $Id = null
    ) {

        $Stage = new Stage('Zensuren-Gruppe', 'Zensuren-Typen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score/Group', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreGroup = Gradebook::useService()->getScoreGroupById($Id);
            if (empty($tblScoreGroup)) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreGroupGradeTypeListByGroup = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
                $tblGradeTypeAll = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
                $tblGradeTypeAllByGroup = array();
                if ($tblScoreGroupGradeTypeListByGroup) {
                    /** @var TblScoreGroupGradeTypeList $tblScoreGroupGradeType */
                    foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeType) {
                        if ($tblScoreGroupGradeType->getTblGradeType()) {
                            $tblGradeTypeAllByGroup[] = $tblScoreGroupGradeType->getTblGradeType();
                        }
                    }
                }

                if (!empty($tblGradeTypeAllByGroup) && $tblGradeTypeAll) {
                    $tblGradeTypeAll = array_udiff($tblGradeTypeAll, $tblGradeTypeAllByGroup,
                        function (TblGradeType $ObjectA, TblGradeType $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                $contentSelectedTable = array();
                if ($tblScoreGroupGradeTypeListByGroup) {
                    /** @var TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList */
                    foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeTypeList) {

                        if ($tblScoreGroupGradeTypeList->getTblGradeType()) {
                            $contentSelectedTable[] = array(
                                'Name' => $tblScoreGroupGradeTypeList->getTblGradeType()->getName(),
                                'DisplayMultiplier' => $tblScoreGroupGradeTypeList->getDisplayMultiplier(),
                                'Option' =>
                                    (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                        'Entfernen', '/Education/Graduation/Gradebook/Score/Group/GradeType/Remove',
                                        new Minus(), array(
                                        'Id' => $tblScoreGroupGradeTypeList->getId()
                                    )))->__toString()
                            );
                        }
                    }
                }

                $contentAvailableTable = array();
                if ($tblGradeTypeAll) {
                    foreach ($tblGradeTypeAll as $tblGradeType) {
                        $contentAvailableTable[] = array(
                            'Name' => $tblGradeType->getName(),
                            'Option' =>
                                (new Form(
                                    new FormGroup(
                                        new FormRow(array(
                                            new FormColumn(
                                                new TextField('GradeType[Multiplier]', 'Faktor', '', new Quantity()
                                                )
                                                , 7),
                                            new FormColumn(
                                                new Primary('Hinzufügen',
                                                    new Plus())
                                                , 5)
                                        ))
                                    ), null,
                                    '/Education/Graduation/Gradebook/Score/Group/GradeType/Add', array(
                                        'tblScoreGroupId' => $tblScoreGroup->getId(),
                                        'tblGradeTypeId' => $tblGradeType->getId()
                                    )
                                ))->__toString()
                        );
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Zensuren-Gruppe',
                                        $tblScoreGroup->getName()
                                        . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getDisplayMultiplier() . ')')),
                                        Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Typen'),
                                    new TableData($contentSelectedTable, null,
                                        array(
                                            'Name' => 'Name',
                                            'DisplayMultiplier' => 'Faktor',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Typen'),
                                    new TableData($contentAvailableTable, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => 'Faktor'
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreGroupId
     * @param null $tblGradeTypeId
     * @param null $GradeType
     *
     * @return Stage|string
     */
    public function frontendScoreGroupGradeTypeAdd(
        $tblScoreGroupId = null,
        $tblGradeTypeId = null,
        $GradeType = null
    ) {

        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ einer Zenuseren-Gruppe hinzufügen');

        if ($tblScoreGroupId === null || $tblGradeTypeId === null) {
            return $Stage;
        }

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($tblScoreGroupId);
        $tblGradeType = Gradebook::useService()->getGradeTypeById($tblGradeTypeId);

        if (isset($GradeType['Multiplier']) && $GradeType['Multiplier'] == '') {
            $multiplier = 1;
        } elseif (isset($GradeType['Multiplier']) && !preg_match(Service::PREG_MATCH_DECIMAL_NUMBER,
                $GradeType['Multiplier'])
        ) {
            return $Stage
            . new Warning('Bitte geben Sie als Faktor eine Zahl an. Der Zensuren-Type wurde nicht hinzugefügt.',
                new Exclamation())
            . new Redirect('/Education/Graduation/Gradebook/Score/Group/GradeType/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblScoreGroup->getId()));
        } else {
            $multiplier = $GradeType['Multiplier'];
        }

        if ($tblScoreGroup && $tblGradeType) {
            $Stage->setContent(Gradebook::useService()->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup,
                $multiplier));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupGradeTypeRemove(
        $Id = null
    ) {

        $Stage = new Stage('Zensuren-Berechnung', 'Zensuren-Typ von einer Zenuseren-Gruppe entfernen');

        $tblScoreGroupGradeTypeList = Gradebook::useService()->getScoreGroupGradeTypeListById($Id);
        if ($tblScoreGroupGradeTypeList) {
            $Stage->setContent(Gradebook::useService()->removeScoreGroupGradeTypeList($tblScoreGroupGradeTypeList));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupSelect(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score/Condition', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {

            $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Id);
            if (!$tblScoreCondition) {
                $Stage->setContent(new Warning('Die Zensuren-Gruppe konnte nicht abgerufen werden'));
            } else {
                $tblScoreConditionGroupListByCondition = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                $tblScoreGroupAll = Gradebook::useService()->getScoreGroupAll();
                $tblScoreGroupAllByCondition = array();
                if ($tblScoreConditionGroupListByCondition) {
                    /** @var TblScoreConditionGroupList $tblScoreConditionGroup */
                    foreach ($tblScoreConditionGroupListByCondition as $tblScoreConditionGroup) {
                        $tblScoreGroupAllByCondition[] = $tblScoreConditionGroup->getTblScoreGroup();
                    }
                }

                if (!empty($tblScoreGroupAllByCondition) && $tblScoreGroupAll) {
                    $tblScoreGroupAll = array_udiff($tblScoreGroupAll, $tblScoreGroupAllByCondition,
                        function (TblScoreGroup $ObjectA, TblScoreGroup $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreConditionGroupListByCondition) {
                    foreach ($tblScoreConditionGroupListByCondition as &$tblScoreConditionGroupList) {
                        $tblScoreConditionGroupList->Name = $tblScoreConditionGroupList->getTblScoreGroup()->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreConditionGroupList->getTblScoreGroup()->getDisplayMultiplier()
                                .  ($tblScoreConditionGroupList->getTblScoreGroup()->isEveryGradeASingleGroup()
                                    ? ', Noten einzeln' : '') . ')'));
                        $tblScoreConditionGroupList->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Entfernen', '/Education/Graduation/Gradebook/Score/Condition/Group/Remove',
                                new Minus(), array(
                                'Id' => $tblScoreConditionGroupList->getId()
                            )))->__toString();
                    }
                }

                if ($tblScoreGroupAll) {
                    foreach ($tblScoreGroupAll as $tblScoreGroup) {
                        $tblScoreGroup->DisplayName = $tblScoreGroup->getName()
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getDisplayMultiplier()
                                . ($tblScoreGroup->isEveryGradeASingleGroup()
                                    ? ', Noten einzeln' : '') . ')'));
                        $tblScoreGroup->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Hinzufügen',
                                '/Education/Graduation/Gradebook/Score/Condition/Group/Add',
                                new Plus(),
                                array(
                                    'tblScoreGroupId' => $tblScoreGroup->getId(),
                                    'tblScoreConditionId' => $tblScoreCondition->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvariante', $tblScoreCondition->getName(),
                                        Panel::PANEL_TYPE_INFO), 12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Gruppen'),
                                    new TableData($tblScoreConditionGroupListByCondition, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Gruppen'),
                                    new TableData($tblScoreGroupAll, null,
                                        array(
                                            'DisplayName' => 'Name ',
                                            'Option' => ' '
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreGroupId
     * @param null $tblScoreConditionId
     *
     * @return Stage
     */
    public function frontendScoreGroupAdd(
        $tblScoreGroupId = null,
        $tblScoreConditionId = null
    ) {

        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppe einer Berechnungsvariante hinzufügen');

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($tblScoreGroupId);
        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($tblScoreConditionId);

        if ($tblScoreGroup && $tblScoreCondition) {
            $Stage->setContent(Gradebook::useService()->addScoreConditionGroupList($tblScoreCondition,
                $tblScoreGroup));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreGroupRemove(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvariante', 'Zensuren-Gruppe von einer Berechnungsvariante entfernen');

        $tblScoreConditionGroupList = Gradebook::useService()->getScoreConditionGroupListById($Id);
        if ($tblScoreConditionGroupList) {
            $Stage->setContent(Gradebook::useService()->removeScoreConditionGroupList($tblScoreConditionGroupList));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $ScoreRule
     * @return Stage|string
     */
    public function frontendEditScore($Id = null, $ScoreRule = null)
    {

        $Stage = new Stage('Berechnungsvorschrift', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Score', new ChevronLeft())
        );

        $tblScoreRule = Gradebook::useService()->getScoreRuleById($Id);
        if ($tblScoreRule) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreRule']['Name'] = $tblScoreRule->getName();
                $Global->POST['ScoreRule']['Description'] = $tblScoreRule->getDescription();
                $Global->savePost();
            }

            $Form = $this->formScoreRule()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Berechnungsvorschrift',
                                    new Bold($tblScoreRule->getName()) . '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblScoreRule->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateScoreRule($Form, $Id, $ScoreRule))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Berechnungsvorschrift nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param null $ScoreGroup
     * @return Stage|string
     */
    public function frontendEditScoreGroup($Id = null, $ScoreGroup = null)
    {

        $Stage = new Stage('Zensuren-Gruppe', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Score/Group', new ChevronLeft())
        );

        $tblScoreGroup = Gradebook::useService()->getScoreGroupById($Id);
        if ($tblScoreGroup) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreGroup']['Name'] = $tblScoreGroup->getName();
                $Global->POST['ScoreGroup']['Round'] = $tblScoreGroup->getRound();
                $Global->POST['ScoreGroup']['Multiplier'] = $tblScoreGroup->getDisplayMultiplier();
                $Global->POST['ScoreGroup']['IsEveryGradeASingleGroup'] = $tblScoreGroup->isEveryGradeASingleGroup();

                $Global->savePost();
            }

            $Form = $this->formScoreGroup()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Zensuren-Gruppe',
                                    $tblScoreGroup->getName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateScoreGroup($Form, $Id, $ScoreGroup))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Zensuren-Gruppe nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Group', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $Id
     * @param null $ScoreCondition
     * @return Stage|string
     */
    public function frontendEditScoreCondition($Id = null, $ScoreCondition = null)
    {

        $Stage = new Stage('Berechnungsvariante', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/Score/Condition', new ChevronLeft())
        );

        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Id);
        if ($tblScoreCondition) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['ScoreCondition']['Name'] = $tblScoreCondition->getName();
                $Global->POST['ScoreCondition']['Round'] = $tblScoreCondition->getRound();
                $Global->POST['ScoreCondition']['Priority'] = $tblScoreCondition->getPriority();

                $Global->savePost();
            }

            $Form = $this->formScoreCondition()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Berechnungsvariante',
                                    $tblScoreCondition->getName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Gradebook::useService()->updateScoreCondition($Form, $Id, $ScoreCondition))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Berechnungsvariante nicht gefunden')
            . new Redirect('/Education/Graduation/Gradebook/Score/Condition', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreConditionGradeTypeSelect(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvariante (Bedingungen)', 'Zensuren-Typen auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score/Condition', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreCondition = Gradebook::useService()->getScoreConditionById($Id);
            if (empty($tblScoreCondition)) {
                $Stage->setContent(new Warning('Die Berechnungsvariante konnte nicht abgerufen werden'));
            } else {
                $tblScoreConditionGradeTypeListByCondition = Gradebook::useService()->getScoreConditionGradeTypeListByCondition($tblScoreCondition);
                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
                $tblGradeTypeAll = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
                $tblGradeTypeAllByCondition = array();
                if ($tblScoreConditionGradeTypeListByCondition) {
                    /** @var TblScoreConditionGradeTypeList $tblScoreConditionGradeType */
                    foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeType) {
                        if ($tblScoreConditionGradeType->getTblGradeType()) {
                            $tblGradeTypeAllByCondition[] = $tblScoreConditionGradeType->getTblGradeType();
                        }
                    }
                }

                if (!empty($tblGradeTypeAllByCondition) && $tblGradeTypeAll) {
                    $tblGradeTypeAll = array_udiff($tblGradeTypeAll, $tblGradeTypeAllByCondition,
                        function (TblGradeType $ObjectA, TblGradeType $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreConditionGradeTypeListByCondition) {
                    foreach ($tblScoreConditionGradeTypeListByCondition as &$tblScoreConditionGradeTypeList) {
                        if ($tblScoreConditionGradeTypeList->getTblGradeType()) {
                            $tblScoreConditionGradeTypeList->Name = $tblScoreConditionGradeTypeList->getTblGradeType()->getName();
                            $tblScoreConditionGradeTypeList->Option =
                                (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                    'Entfernen', '/Education/Graduation/Gradebook/Score/Condition/GradeType/Remove',
                                    new Minus(), array(
                                    'Id' => $tblScoreConditionGradeTypeList->getId()
                                )))->__toString();
                        } else {
                            $tblScoreConditionGradeTypeList = false;
                        }
                    }
                    $tblScoreConditionGradeTypeListByCondition = array_filter($tblScoreConditionGradeTypeListByCondition);
                }

                if ($tblGradeTypeAll) {
                    foreach ($tblGradeTypeAll as $tblGradeType) {
                        $tblGradeType->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Hinzufügen',
                                '/Education/Graduation/Gradebook/Score/Condition/GradeType/Add',
                                new Plus(),
                                array(
                                    'tblScoreConditionId' => $tblScoreCondition->getId(),
                                    'tblGradeTypeId' => $tblGradeType->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvariante', $tblScoreCondition->getName(),
                                        Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Zensuren-Typen'),
                                    new TableData($tblScoreConditionGradeTypeListByCondition, null,
                                        array(
                                            'Name' => 'Name',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Zensuren-Typen'),
                                    new TableData($tblGradeTypeAll, null,
                                        array(
                                            'Name' => 'Name ',
                                            'Option' => 'Faktor'
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreConditionId
     * @param null $tblGradeTypeId
     *
     * @return Stage
     */
    public function frontendScoreConditionGradeTypeAdd(
        $tblScoreConditionId = null,
        $tblGradeTypeId = null
    ) {

        $Stage = new Stage('Berechnungsvariante (Bedingungen)', 'Zensuren-Typ einer Berechnungsvariante hinzufügen');

        if ($tblScoreConditionId === null || $tblGradeTypeId === null) {
            return $Stage;
        }

        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($tblScoreConditionId);
        $tblGradeType = Gradebook::useService()->getGradeTypeById($tblGradeTypeId);

        if ($tblScoreCondition && $tblGradeType) {
            $Stage->setContent(Gradebook::useService()->addScoreConditionGradeTypeList($tblGradeType,
                $tblScoreCondition));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionSelect(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvarianten auswählen');

        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblScoreRule = Gradebook::useService()->getScoreRuleById($Id);
            if (empty($tblScoreRule)) {
                $Stage->setContent(new Warning('Die Berechnungsvorschrift konnte nicht abgerufen werden'));
            } else {
                $tblScoreRuleConditionListByRule = Gradebook::useService()->getScoreRuleConditionListByRule($tblScoreRule);
                $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();
                $tblScoreConditionAllByRule = array();
                if ($tblScoreRuleConditionListByRule) {
                    /** @var TblScoreRuleConditionList $tblScoreRuleConditionList */
                    foreach ($tblScoreRuleConditionListByRule as $tblScoreRuleConditionList) {
                        $tblScoreConditionAllByRule[] = $tblScoreRuleConditionList->getTblScoreCondition();
                    }
                }

                if (!empty($tblScoreConditionAllByRule) && $tblScoreConditionAll) {
                    $tblScoreConditionAll = array_udiff($tblScoreConditionAll, $tblScoreConditionAllByRule,
                        function (TblScoreCondition $ObjectA, TblScoreCondition $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if ($tblScoreRuleConditionListByRule) {
                    foreach ($tblScoreRuleConditionListByRule as &$tblScoreRuleCondition) {
                        $tblScoreRuleCondition->Name = $tblScoreRuleCondition->getTblScoreCondition()->getName();
                        $tblScoreRuleCondition->Priority = $tblScoreRuleCondition->getTblScoreCondition()->getPriority();
                        $tblScoreRuleCondition->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Entfernen', '/Education/Graduation/Gradebook/Score/Condition/Remove',
                                new Minus(), array(
                                'Id' => $tblScoreRuleCondition->getId()
                            )))->__toString();
                    }
                }

                if ($tblScoreConditionAll) {
                    foreach ($tblScoreConditionAll as $tblScoreCondition) {
                        $tblScoreCondition->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                'Hinzufügen',
                                '/Education/Graduation/Gradebook/Score/Condition/Add',
                                new Plus(),
                                array(
                                    'tblScoreRuleId' => $tblScoreRule->getId(),
                                    'tblScoreConditionId' => $tblScoreCondition->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Berechnungsvariante', $tblScoreRule->getName(), Panel::PANEL_TYPE_INFO),
                                    12
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Berechnungsvarianten'),
                                    new TableData($tblScoreRuleConditionListByRule, null,
                                        array(
                                            'Name' => 'Name',
                                            'Priority' => 'Priorität',
                                            'Option' => ''
                                        )
                                    )
                                ), 6
                                ),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Berechnungsvarianten'),
                                    new TableData($tblScoreConditionAll, null,
                                        array(
                                            'Name' => 'Name ',
                                            'Priority' => 'Priorität ',
                                            'Option' => ' '
                                        )
                                    )
                                ), 6
                                )
                            )),
                        )),
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $tblScoreRuleId
     * @param null $tblScoreConditionId
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionAdd(
        $tblScoreRuleId = null,
        $tblScoreConditionId = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvariante einer Berechnungsvorschrift hinzufügen');

        $tblScoreRule = Gradebook::useService()->getScoreRuleById($tblScoreRuleId);
        $tblScoreCondition = Gradebook::useService()->getScoreConditionById($tblScoreConditionId);

        if ($tblScoreRule && $tblScoreCondition) {
            $Stage->setContent(Gradebook::useService()->addScoreRuleConditionList($tblScoreRule, $tblScoreCondition));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendScoreRuleConditionRemove(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Berechnungsvariante von einer Berechnungsvorschrift entfernen');

        $tblScoreRuleCondition = Gradebook::useService()->getScoreRuleConditionListById($Id);
        if ($tblScoreRuleCondition) {
            $Stage->setContent(Gradebook::useService()->removeScoreRuleConditionList($tblScoreRuleCondition));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendScoreDivision(
        $Id = null,
        $YearId = null,
        $Data = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Fach-Klassen einer Berechnungsvorschrift zuordnen');
        $Stage->setMessage('Hier können der ausgewählten Berechnungsvorschrift Fach-Klassen zugeordnet werden. <br>
        ' . new Bold(new Exclamation() . ' Hinweis:') . ' Eine Fach-Klasse kann immer nur ein Bewertungssystem besitzen.');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score', new ChevronLeft()));

        $tblScoreRule = Gradebook::useService()->getScoreRuleById($Id);
        if ($tblScoreRule) {


            if ($YearId && ($tblSelectedYear = Term::useService()->getYearById($YearId))) {
            } else {
                if (($tblYearAllByNow = Term::useService()->getYearByNow())) {
                    $tblSelectedYear = current($tblYearAllByNow);
                } else {
                    $tblSelectedYear = false;
                }
            }

            $yearButtonList = array();
            $tblYearList = Term::useService()->getYearAllSinceYears(3);
            if ($tblYearList) {
                $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
                /** @var TblYear $tblYear */
                foreach ($tblYearList as $tblYear) {
                    $yearButtonList[] = new Standard(
                        ($tblSelectedYear && $tblYear->getId() == $tblSelectedYear->getId())
                            ? new Info($tblYear->getDisplayName())
                            : $tblYear->getDisplayName(),
                        '/Education/Graduation/Gradebook/Score/Division',
                        null,
                        array(
                            'Id' => $tblScoreRule->getId(),
                            'YearId' => $tblYear->getId()
                        )
                    );
                }
            }

            $formGroupList = array();
            $rowList = array();
            $columnList = array();
            if ($tblSelectedYear) {
                $tblDivisionList = Division::useService()->getDivisionByYear($tblSelectedYear);
                if ($tblDivisionList) {
                    $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('DisplayName');
                    /** @var TblDivision $tblDivision */
                    foreach ($tblDivisionList as $tblDivision) {
                        $subjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
                        if ($subjectList) {

                            // set Post
                            if ($Data == null) {
                                $Global = $this->getGlobal();
                                foreach ($subjectList as $subject) {
                                    $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                        $tblDivision, $subject
                                    );
                                    if ($tblScoreRuleDivisionSubject) {
                                        if ($tblScoreRuleDivisionSubject->getTblScoreRule()
                                            && $tblScoreRuleDivisionSubject->getTblScoreRule()->getId() == $tblScoreRule->getId()
                                        ) {
                                            $Global->POST['Data'][$tblDivision->getId()][$subject->getId()] = 1;
                                        }
                                    }
                                }
                                $Global->savePost();
                            }

                            $countSubject = 0;
                            $subjectList = $this->getSorter($subjectList)->sortObjectBy('Acronym');
                            /** @var TblSubject $tblSubject */
                            foreach ($subjectList as &$tblSubject) {
                                $isDisabled = false;
                                if ($tblSubject->getId() === -1) {
                                    $name = new Italic((
                                        $tblSubject->getAcronym() ? new Bold($tblSubject->getAcronym() . ' ') : '') . $tblSubject->getName()
                                    );
                                } else {
                                    $name = ($tblSubject->getAcronym() ? new Bold($tblSubject->getAcronym() . ' ') : '') . $tblSubject->getName();
                                }
                                $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                    $tblDivision, $tblSubject
                                );
                                if ($tblScoreRuleDivisionSubject) {
                                    if ($tblScoreRuleDivisionSubject->getTblScoreRule()
                                        && $tblScoreRuleDivisionSubject->getTblScoreRule()->getId() != $tblScoreRule->getId()
                                    ) {
                                        $isDisabled = true;
                                        $name .= ' ' . new Label($tblScoreRuleDivisionSubject->getTblScoreRule()->getName(),
                                                Label::LABEL_TYPE_PRIMARY);
                                    }
                                }

                                $checkBox = new CheckBox(
                                    'Data[' . $tblDivision->getId() . '][' . $tblSubject->getId() . ']',
                                    $name,
                                    1
                                );
                                $tblSubject = $isDisabled ? $checkBox->setDisabled() : $checkBox;
                                if (!$isDisabled) {
                                    $countSubject++;
                                }
                            }

                            if ($countSubject > 0) {
//                                $tblNewSubject = new TblSubject();
//                                $tblNewSubject->setId(-1);
//                                $tblNewSubject->setName('Alle verfügbaren Fächer');
                                $tblNewSubject = new CheckBox(
                                    'Data[' . $tblDivision->getId() . '][-1]',
                                    new Italic('Alle  verfügbaren Fächer'),
                                    1
                                );

                                array_unshift($subjectList, $tblNewSubject);
                            }

                            $panel = new Panel(
                                new Bold('Klasse ' . $tblDivision->getDisplayName()),
                                $subjectList,
                                Panel::PANEL_TYPE_INFO
                            );

                            if ($tblDivision->getTblLevel()) {
                                $schoolTypeId = $tblDivision->getTblLevel()->getServiceTblType()->getId();
                            } else {
                                $schoolTypeId = 0;
                            }
                            $columnList[$schoolTypeId][] = new FormColumn($panel, 3);
                            if (count($columnList[$schoolTypeId]) == 4) {
                                $rowList[$schoolTypeId][] = new FormRow($columnList[$schoolTypeId]);
                                $columnList[$schoolTypeId] = array();
                            }
                        }
                    }

                    foreach ($columnList as $schoolTypeId => $list) {
                        if (!empty($list)) {
                            $rowList[$schoolTypeId][] = new FormRow($list);
                        }
                    }

                    foreach ($rowList as $schoolTypeId => $list) {
                        $tblSchoolType = Type::useService()->getTypeById($schoolTypeId);
                        $formGroupList[] = new FormGroup($list,
                            new \SPHERE\Common\Frontend\Form\Repository\Title($tblSchoolType
                                ? new Building() . ' ' . $tblSchoolType->getName()
                                : 'Keine Schulart'));
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Berechnungsvorschrift',
                                    new Bold($tblScoreRule->getName()) . '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblScoreRule->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                            new LayoutColumn($yearButtonList),
                            new LayoutColumn('<br>')
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                empty($formGroupList)
                                    ? new Warning('Keine Klassen vorhanden.', new Exclamation())
                                    : new Well(
                                    Gradebook::useService()->updateScoreRuleDivisionSubject(
                                        (new Form(
                                            $formGroupList
                                        ))->appendFormButton(new Primary('Speichern', new Save())), $tblScoreRule,
                                        $tblSelectedYear ? $tblSelectedYear : null, $Data
                                    )
                                )
                            )
                        )
                    )),
                ))
            );
        } else {
            $Stage->setContent(new Danger('Berechnungsvorschrift nicht gefunden.', new Exclamation()));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendScoreSubjectGroup(
        $Id = null,
        $YearId = null,
        $Data = null
    ) {

        $Stage = new Stage('Berechnungsvorschrift', 'Fachgruppe einer Berechnungsvorschrift zuordnen');
        $Stage->setMessage('Hier können der ausgewählten Berechnungsvorschrift Fachgruppen zugeordnet werden, dabei
            wird die Berechnungsvorschrift der Fach-Klasse für diese Gruppe überschrieben. <br>
            ' . new Bold(new Exclamation() . ' Hinweis:') . ' Eine Fachgruppe kann immer nur ein Bewertungssystem besitzen.');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Score', new ChevronLeft()));

        $tblScoreRule = Gradebook::useService()->getScoreRuleById($Id);
        if ($tblScoreRule) {

            if ($YearId && ($tblSelectedYear = Term::useService()->getYearById($YearId))) {
            } else {
                if (($tblYearAllByNow = Term::useService()->getYearByNow())) {
                    $tblSelectedYear = current($tblYearAllByNow);
                } else {
                    $tblSelectedYear = false;
                }
            }

            $yearButtonList = array();
            $tblYearList = Term::useService()->getYearAllSinceYears(3);
            if ($tblYearList) {
                $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
                /** @var TblYear $tblYear */
                foreach ($tblYearList as $tblYear) {
                    $yearButtonList[] = new Standard(
                        ($tblSelectedYear && $tblYear->getId() == $tblSelectedYear->getId())
                            ? new Info($tblYear->getDisplayName())
                            : $tblYear->getDisplayName(),
                        '/Education/Graduation/Gradebook/Score/SubjectGroup',
                        null,
                        array(
                            'Id' => $tblScoreRule->getId(),
                            'YearId' => $tblYear->getId()
                        )
                    );
                }
            }

            $formGroupList = array();
            $rowList = array();
            $columnList = array();
            if ($tblSelectedYear) {
                $tblDivisionList = Division::useService()->getDivisionByYear($tblSelectedYear);
                if ($tblDivisionList) {
                    $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('DisplayName');
                    /** @var TblDivision $tblDivision */
                    foreach ($tblDivisionList as $tblDivision) {
                        $subjectList = Division::useService()->getSubjectAllByDivision($tblDivision);
                        if ($subjectList) {
                            $subjectGroupList = array();
                            $subjectList = $this->getSorter($subjectList)->sortObjectBy('Acronym');
                            /** @var TblSubject $tblSubject */
                            foreach ($subjectList as $tblSubject) {
                                $tblDivisionSubjectWhereGroup = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                    $tblDivision, $tblSubject
                                );
                                if ($tblDivisionSubjectWhereGroup) {
                                    /** @var TblDivisionSubject $tblDivisionSubject */
                                    foreach ($tblDivisionSubjectWhereGroup as $tblDivisionSubject) {
                                        $isDisabled = false;
                                        $name = ($tblSubject->getAcronym() ? new Bold($tblSubject->getAcronym() . ' ') : '')
                                            . $tblSubject->getName() . ' - ' . $tblDivisionSubject->getTblSubjectGroup()->getName();

                                        // set Post
                                        if ($Data == null) {
                                            $Global = $this->getGlobal();
                                            /** @var TblSubject $subject */
                                            foreach ($subjectList as $subject) {
                                                $tblScoreRuleSubjectGroup = Gradebook::useService()->getScoreRuleSubjectGroupByDivisionAndSubjectAndGroup(
                                                    $tblDivision,
                                                    $subject,
                                                    $tblDivisionSubject->getTblSubjectGroup()
                                                );
                                                if ($tblScoreRuleSubjectGroup) {
                                                    if ($tblScoreRuleSubjectGroup->getTblScoreRule()
                                                    ) {
                                                        if ($tblScoreRuleSubjectGroup->getTblScoreRule()->getId() == $tblScoreRule->getId()) {
                                                            $Global->POST['Data'][$tblDivision->getId()][$subject->getId()]
                                                            [$tblDivisionSubject->getTblSubjectGroup()->getId()] = 1;
                                                        } else {
                                                            $isDisabled = true;
                                                            $name .= ' ' . new Label($tblScoreRuleSubjectGroup->getTblScoreRule()->getName(),
                                                                    Label::LABEL_TYPE_PRIMARY);
                                                        }
                                                    }
                                                }
                                            }
                                            $Global->savePost();
                                        }

                                        $checkBox = new CheckBox(
                                            'Data[' . $tblDivision->getId() . '][' . $tblSubject->getId() . ']['
                                            . $tblDivisionSubject->getTblSubjectGroup()->getId() . ']',
                                            $name,
                                            1
                                        );
                                        $subjectGroupList[] = $isDisabled ? $checkBox->setDisabled() : $checkBox;
                                    }
                                }
                            }

                            if (!empty($subjectGroupList)) {
                                $panel = new Panel(
                                    new Bold('Klasse ' . $tblDivision->getDisplayName()),
                                    $subjectGroupList,
                                    Panel::PANEL_TYPE_INFO
                                );

                                if ($tblDivision->getTblLevel()) {
                                    $schoolTypeId = $tblDivision->getTblLevel()->getServiceTblType()->getId();
                                } else {
                                    $schoolTypeId = 0;
                                }
                                $columnList[$schoolTypeId][] = new FormColumn($panel, 3);
                                if (count($columnList[$schoolTypeId]) == 4) {
                                    $rowList[$schoolTypeId][] = new FormRow($columnList[$schoolTypeId]);
                                    $columnList[$schoolTypeId] = array();
                                }
                            }
                        }
                    }

                    foreach ($columnList as $schoolTypeId => $list) {
                        if (!empty($list)) {
                            $rowList[$schoolTypeId][] = new FormRow($list);
                        }
                    }

                    if (!empty($rowList)) {
                        foreach ($rowList as $schoolTypeId => $list) {
                            $tblSchoolType = Type::useService()->getTypeById($schoolTypeId);
                            $formGroupList[] = new FormGroup($list,
                                new \SPHERE\Common\Frontend\Form\Repository\Title($tblSchoolType
                                    ? new Building() . ' ' . $tblSchoolType->getName()
                                    : 'Keine Schulart'));
                        }
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Berechnungsvorschrift',
                                    new Bold($tblScoreRule->getName()) . '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblScoreRule->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                            new LayoutColumn($yearButtonList),
                            new LayoutColumn('<br>')
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                empty($formGroupList)
                                    ? new Warning('Keine Fachgruppen vorhanden.', new Exclamation())
                                    : new Well(
                                    Gradebook::useService()->updateScoreRuleSubjectGroup(
                                        (new Form(
                                            $formGroupList
                                        ))->appendFormButton(new Primary('Speichern', new Save())), $tblScoreRule,
                                        $tblSelectedYear ? $tblSelectedYear : null, $Data
                                    )
                                )
                            )
                        )
                    )),
                ))
            );
        } else {
            $Stage->setContent(new Danger('Berechnungsvorschrift nicht gefunden.', new Exclamation()));
        }

        return $Stage;
    }
}