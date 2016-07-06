<?php

namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
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
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Group;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Header;
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
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Frontend extends Extension implements IFrontendInterface
{

    const SCORE_RULE = 0;
    const SCORE_CONDITION = 1;
    const GRADE_GROUP = 2;

    /**
     * @param null $GradeType
     *
     * @return Stage
     */
    public function frontendGradeType($GradeType = null)
    {

        $Stage = new Stage('Zensuren-Typ', 'Übersicht');
        $Stage->setMessage('Hier werden die Zensuren-Typen verwaltet. Bei den Zensuren-Typen wird zwischen den beiden
            Kategorien: Kopfnote (z.B. Betragen, Mitarbeit, Fleiß usw.) und Leistungsüberprüfung
            (z.B. Klassenarbeit, Leistungskontrolle usw.) unterschieden.');

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        if (!$tblTestType || !($tblGradeTypeAllTest = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType))) {
            $tblGradeTypeAllTest = array();
        }

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        if (!$tblTestType || !($tblGradeTypeAllBehavior = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType))) {
            $tblGradeTypeAllBehavior = array();
        }
        $tblGradeTypeAll = array_merge($tblGradeTypeAllTest, $tblGradeTypeAllBehavior);

        $TableContent = array();
        if (!empty($tblGradeTypeAll)) {
            array_walk($tblGradeTypeAll, function (TblGradeType $tblGradeType) use (&$TableContent) {

                if ($tblGradeType->isHighlighted()) {
                    $Item = array(
                        'DisplayName' => new Bold($tblGradeType->getName()),
                        'DisplayCode' => new Bold($tblGradeType->getCode()),
                        'Category' => new Bold($tblGradeType->getServiceTblTestType() ? $tblGradeType->getServiceTblTestType()->getName() : ''),
                    );
                } else {
                    $Item = array(
                        'DisplayName' => $tblGradeType->getName(),
                        'DisplayCode' => $tblGradeType->getCode(),
                        'Category' => $tblGradeType->getServiceTblTestType() ? $tblGradeType->getServiceTblTestType()->getName() : '',
                    );
                }
                $Item['Description'] = $tblGradeType->getDescription();
                $Item['Option'] = (new Standard('', '/Education/Graduation/Gradebook/GradeType/Edit', new Edit(), array(
                    'Id' => $tblGradeType->getId()
                ), 'Zensuren-Typ bearbeiten'));
                // löschen erstmal deaktiviert, kann zu Problemen führen
//                    . (new Standard('', '/Education/Graduation/Gradebook/GradeType/Destroy', new Remove(),
//                        array('Id' => $tblGradeType->getId()), 'Löschen'));

                array_push($TableContent, $Item);
            });
        }

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null, array(
                                'Category' => 'Kategorie',
                                'DisplayName' => 'Name',
                                'DisplayCode' => 'Abk&uuml;rzung',
                                'Description' => 'Beschreibung',
                                'Option' => ''
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Gradebook::useService()->createGradeType($Form, $GradeType))
                        )
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formGradeType()
    {

        $type = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $typeList[$type->getId()] = $type->getName();
        $type = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        $typeList[$type->getId()] = $type->getName();

        $typeList = Evaluation::useService()->getTestTypesForGradeTypes();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('GradeType[Type]', 'Kategorie', array('Name' => $typeList)), 3
                ),
                new FormColumn(
                    new TextField('GradeType[Code]', 'LK', 'Abk&uuml;rzung'), 3
                ),
                new FormColumn(
                    new TextField('GradeType[Name]', 'Leistungskontrolle', 'Name'), 6
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('GradeType[Description]', '', 'Beschreibung'), 12
                ),
                new FormColumn(
                    new CheckBox('GradeType[IsHighlighted]', 'Fett markiert', 1), 2
                )
            )),
        )));
    }

    /**
     * @param null $Id
     * @param      $GradeType
     *
     * @return Stage
     */
    public function frontendEditGradeType($Id = null, $GradeType = null)
    {

        $Stage = new Stage('Zensuren-Typ', 'Bearbeiten');

        $tblGradeType = false;

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblGradeType = Gradebook::useService()->getGradeTypeById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Zensuren-Typ nicht gefunden', new Ban())
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/GradeType', new ChevronLeft())
        );

        $Global = $this->getGlobal();
        if (!$Global->POST) {
            if ($tblGradeType->getServiceTblTestType()) {
                $Global->POST['GradeType']['Type'] = $tblGradeType->getServiceTblTestType()->getId();
            }
            $Global->POST['GradeType']['Name'] = $tblGradeType->getName();
            $Global->POST['GradeType']['Code'] = $tblGradeType->getCode();
            $Global->POST['GradeType']['IsHighlighted'] = $tblGradeType->isHighlighted();
            $Global->POST['GradeType']['Description'] = $tblGradeType->getDescription();

            $Global->savePost();
        }

        $Form = $this->formGradeType()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Zensuren-Typ',
                                $tblGradeType->getName() . ' (' . $tblGradeType->getCode() . ')' .
                                ($tblGradeType->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblGradeType->getDescription()))) : ''),
                                Panel::PANEL_TYPE_INFO
                            )
                        ),
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Gradebook::useService()->updateGradeType($Form, $Id, $GradeType))
                        ),
                    ))
                ), new Title(new Edit() . ' Bearbeiten'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendGradeBook()
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherGradebook();
            } else {
                return $this->frontendHeadmasterGradeBook();
            }
        } else {
            return $this->frontendTeacherGradebook();
        }
    }

    /**
     * @return Stage
     */
    public function frontendTeacherGradebook()
    {

        $Stage = new Stage('Notenbuch', 'Auswahl');
        $Stage->setMessage(
            'Auswahl der Notenbücher, wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                '/Education/Graduation/Gradebook/Gradebook/Teacher', new Edit()));
            $Stage->addButton(new Standard('Ansicht: Leitung', '/Education/Graduation/Gradebook/Gradebook/Headmaster'));
        }

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        if ($tblPerson) {
            // Fachlehrer
            $tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson);
            if ($tblSubjectTeacherAllByTeacher) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    $tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject();
                    if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                            [$tblDivisionSubject->getServiceTblSubject()->getId()]
                            [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                = $tblDivisionSubject->getId();
                        } else {
                            $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                $tblDivisionSubject->getTblDivision(),
                                $tblSubjectTeacher->getTblDivisionSubject()->getServiceTblSubject()
                            );
                            if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                    $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                    [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                    [$item->getTblSubjectGroup()->getId()]
                                        = $item->getId();
                                }
                            } else {
                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                [$tblSubjectTeacher->getTblDivisionSubject()->getServiceTblSubject()->getId()]
                                    = $tblSubjectTeacher->getTblDivisionSubject()->getId();
                            }
                        }
                    }
                }
            }

            // Klassenlehrer
            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            if ($tblDivisionTeacherAllByTeacher) {
                foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                    if ($tblDivisionTeacher->getTblDivision()) {
                        $tblDivisionSubjectAllByDivision
                            = Division::useService()->getDivisionSubjectByDivision($tblDivisionTeacher->getTblDivision());
                        if ($tblDivisionSubjectAllByDivision) {
                            foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                                if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                            = $tblDivisionSubject->getId();
                                    } else {
                                        $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                            = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                            $tblDivisionSubject->getTblDivision(),
                                            $tblDivisionSubject->getServiceTblSubject()
                                        );
                                        if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                            /** @var TblDivisionSubject $item */
                                            foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                                [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                                [$item->getTblSubjectGroup()->getId()]
                                                    = $item->getId();
                                            }
                                        } else {
                                            $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                            [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                                = $tblDivisionSubject->getId();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($divisionSubjectList)) {
            foreach ($divisionSubjectList as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    foreach ($subjectList as $subjectId => $value) {
                        $tblSubject = Subject::useService()->getSubjectById($subjectId);
                        if ($tblSubject) {
                            if (is_array($value)) {
                                foreach ($value as $subjectGroupId => $subValue) {
                                    /** @var TblSubjectGroup $item */
                                    $item = Division::useService()->getSubjectGroupById($subjectGroupId);
                                    $divisionSubjectTable[] = array(
                                        'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                        'Type' => $tblDivision->getTypeName(),
                                        'Division' => $tblDivision->getDisplayName(),
                                        'Subject' => $tblSubject->getName(),
                                        'SubjectGroup' => $item->getName(),
                                        'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                            $tblDivision, $tblSubject, $item
                                        ),
                                        'Option' => new Standard(
                                            '', '/Education/Graduation/Gradebook/Gradebook/Teacher/Selected',
                                            new Select(),
                                            array(
                                                'DivisionSubjectId' => $subValue
                                            ),
                                            'Auswählen'
                                        )
                                    );
                                }
                            } else {
                                $divisionSubjectTable[] = array(
                                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                    'Type' => $tblDivision->getTypeName(),
                                    'Division' => $tblDivision->getDisplayName(),
                                    'Subject' => $tblSubject->getName(),
                                    'SubjectGroup' => '',
                                    'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                        $tblDivision, $tblSubject
                                    ),
                                    'Option' => new Standard(
                                        '', '/Education/Graduation/Gradebook/Gradebook/Teacher/Selected', new Select(),
                                        array(
                                            'DivisionSubjectId' => $value
                                        ),
                                        'Auswählen'
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'SubjectTeachers' => 'Fachlehrer',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                    array('4', 'asc')
                                )
                            ))
                        ))
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendHeadmasterGradeBook()
    {

        $Stage = new Stage('Notenbuch', 'Auswahl');
        $Stage->setMessage(
            'Auswahl aller Notenbücher.'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Graduation/Gradebook/Gradebook/Teacher'));
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                '/Education/Graduation/Gradebook/Gradebook/Headmaster', new Edit()));
        }

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as $tblDivision) {
                $tblDivisionSubjectAllByDivision = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                if ($tblDivisionSubjectAllByDivision) {
                    foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                        if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                            if ($tblDivisionSubject->getTblSubjectGroup()) {
                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                    = $tblDivisionSubject->getId();
                            } else {
                                $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
                                    = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                    $tblDivisionSubject->getTblDivision(),
                                    $tblDivisionSubject->getServiceTblSubject()
                                );
                                if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
                                    foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
                                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        [$item->getTblSubjectGroup()->getId()]
                                            = $item->getId();
                                    }
                                } else {
                                    $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                    [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        = $tblDivisionSubject->getId();
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($divisionSubjectList)) {
            foreach ($divisionSubjectList as $divisionId => $subjectList) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    foreach ($subjectList as $subjectId => $value) {
                        $tblSubject = Subject::useService()->getSubjectById($subjectId);
                        if ($tblSubject) {
                            if (is_array($value)) {
                                foreach ($value as $subjectGroupId => $subValue) {
                                    $item = Division::useService()->getSubjectGroupById($subjectGroupId);

                                    $divisionSubjectTable[] = array(
                                        'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                        'Type' => $tblDivision->getTypeName(),
                                        'Division' => $tblDivision->getDisplayName(),
                                        'Subject' => $tblSubject->getName(),
                                        'SubjectGroup' => $item->getName(),
                                        'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                            $tblDivision, $tblSubject, $item
                                        ),
                                        'Option' => new Standard(
                                            '', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Selected',
                                            new Select(),
                                            array(
                                                'DivisionSubjectId' => $subValue
                                            ),
                                            'Auswählen'
                                        )
                                    );
                                }
                            } else {
                                $divisionSubjectTable[] = array(
                                    'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                                    'Type' => $tblDivision->getTypeName(),
                                    'Division' => $tblDivision->getDisplayName(),
                                    'Subject' => $tblSubject->getName(),
                                    'SubjectGroup' => '',
                                    'SubjectTeachers' => Division::useService()->getSubjectTeacherNameList(
                                        $tblDivision, $tblSubject
                                    ),
                                    'Option' => new Standard(
                                        '', '/Education/Graduation/Gradebook/Gradebook/Headmaster/Selected',
                                        new Select(),
                                        array(
                                            'DivisionSubjectId' => $value
                                        ),
                                        'Auswählen'
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'SubjectTeachers' => 'Fachlehrer',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                    array('4', 'asc')
                                )
                            ))
                        ))
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionSubjectId
     *
     * @return Stage|string
     */
    public function frontendTeacherSelectedGradebook($DivisionSubjectId = null)
    {

        $Stage = new Stage('Notenbuch', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Danger(new Ban() . ' Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Gradebook/Teacher',
                Redirect::TIMEOUT_ERROR);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject,
            '/Education/Graduation/Gradebook/Gradebook/Teacher');

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param TblDivisionSubject $tblDivisionSubject
     * @param $BasicRoute
     * @return Stage
     */
    private function contentSelectedGradeBook(
        Stage $Stage,
        TblDivisionSubject $tblDivisionSubject,
        $BasicRoute
    ) {

        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft()));

        $tblDivision = $tblDivisionSubject->getTblDivision();
        $tblSubject = $tblDivisionSubject->getServiceTblSubject();

        // Berechnungsvorschrift und Berechnungssystem der ausgewählten Fach-Klasse ermitteln
        $tblScoreRule = false;
        $scoreRuleText = array();
        if ($tblDivision && $tblSubject) {

            $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                $tblDivision,
                $tblSubject,
                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
            );
            if ($tblScoreRule) {
                $scoreRuleText[] = new Bold($tblScoreRule->getName());
                $tblScoreConditionsByRule = Gradebook::useService()->getScoreConditionsByRule($tblScoreRule);
                if ($tblScoreConditionsByRule) {

                } else {
                    $scoreRuleText[] = new Bold(new \SPHERE\Common\Frontend\Text\Repository\Warning(
                        new Ban() . ' Keine Berechnungsvariante hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                    ));
                }
            }
        }

        $errorRowList = array();

        $tblYear = $tblDivision->getServiceTblYear();
        if ($tblYear) {
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
        } else {
            $tblPeriodList = false;
        }
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        $dataList = array();
        $columnDefinition = array();
        $periodListCount = array();
        $columnDefinition['Number'] = 'Nr.';
        $columnDefinition['Student'] = "Schüler";
        // Tabellenkopf mit Test-Code und Datum erstellen
        if ($tblPeriodList) {
            foreach ($tblPeriodList as $tblPeriod) {
                if ($tblDivisionSubject->getServiceTblSubject()) {
                    $count = 0;
                    $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                        $tblDivision,
                        $tblDivisionSubject->getServiceTblSubject(),
                        $tblTestType,
                        $tblPeriod,
                        $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                    );
                    if ($tblTestList) {

                        // Sortierung der Tests nach Datum
                        $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date', new DateTimeSorter());

                        /** @var TblTest $tblTest */
                        foreach ($tblTestList as $tblTest) {
                            if ($tblTest->getServiceTblGradeType()) {
                                $count++;
                                $date = $tblTest->getDate();
                                if (strlen($date) > 6) {
                                    $date = substr($date, 0, 6);
                                }
                                $columnDefinition['Test' . $tblTest->getId()] = new Small(new Muted($date)) . '<br>'
                                    . ($tblTest->getServiceTblGradeType()->isHighlighted()
                                        ? $tblTest->getServiceTblGradeType()->getCode()
                                        : new Muted($tblTest->getServiceTblGradeType()->getCode()));
                            }
                        }
                        $columnDefinition['PeriodAverage' . $tblPeriod->getId()] = '&#216;';
                        $count++;
                        $periodListCount[$tblPeriod->getId()] = $count;
                    } else {
                        $periodListCount[$tblPeriod->getId()] = 1;
                        $columnDefinition['Period' . $tblPeriod->getId()] = "";
                    }
                }
            }
            $columnDefinition['YearAverage'] = '&#216;';
        }

        // Tabellen-Inhalt erstellen
        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $tblStudentList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
        } else {
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
        }

        if ($tblStudentList) {

            // Sortierung der Schüler nach Nachname --> Vorname
            foreach ($tblStudentList as $key => $row) {
                $name[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstSecondName());
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $tblStudentList);

            $count = 1;
            // Ermittlung der Zensuren zu den Schülern
            foreach ($tblStudentList as $tblPerson) {
                $data = array();
                $data['Number'] = $count % 5 == 0 ? new Bold($count) : $count;
                $count++;
                $data['Student'] = $tblPerson->getLastFirstName();

                // Zenur des Schülers zum Test zuordnen und Durchschnitte berechnen
                if (!empty($columnDefinition)) {
                    foreach ($columnDefinition as $column => $value) {
                        if (strpos($column, 'Test') !== false) {
                            $testId = substr($column, strlen('Test'));
                            $tblTest = Evaluation::useService()->getTestById($testId);
                            if ($tblTest) {
                                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                                if ($tblGrade) {
                                    $data[$column] = $tblTest->getServiceTblGradeType()
                                        ? ($tblTest->getServiceTblGradeType()->isHighlighted()
                                            ? new Bold($tblGrade->getDisplayGrade()) : $tblGrade->getDisplayGrade())
                                        : $tblGrade->getDisplayGrade();
                                } else {
                                    $data[$column] = '';
                                }
                            }
                        } elseif (strpos($column, 'PeriodAverage') !== false) {
                            $periodId = substr($column, strlen('PeriodAverage'));
                            $tblPeriod = Term::useService()->getPeriodById($periodId);
                            if ($tblPeriod) {
                                /*
                                * Calc Average
                                */
                                $average = Gradebook::useService()->calcStudentGrade(
                                    $tblPerson,
                                    $tblDivision,
                                    $tblDivisionSubject->getServiceTblSubject(),
                                    $tblTestType,
                                    $tblScoreRule ? $tblScoreRule : null,
                                    $tblPeriod,
                                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                );
//                                $priority = '';
                                if (is_array($average)) {
                                    $errorRowList = $average;
                                    $average = '';
                                } else {
                                    $posStart = strpos($average, '(');
                                    if ($posStart !== false) {
                                        $posEnd = strpos($average, ')');
                                        if ($posEnd !== false) {
//                                            $priority = substr($average, $posStart + 1, $posEnd - ($posStart + 1));
                                        }
                                        $average = substr($average, 0, $posStart);
                                    }
                                }
                                $data[$column] = new Bold($average);
                            }
                        } elseif (strpos($column, 'YearAverage') !== false) {

                            /*
                            * Calc Average
                            */
                            $average = Gradebook::useService()->calcStudentGrade(
                                $tblPerson,
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                $tblTestType,
                                $tblScoreRule ? $tblScoreRule : null,
                                null,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            if (is_array($average)) {
                                $errorRowList = $average;
                                $average = '';
                            } else {
                                $posStart = strpos($average, '(');
                                if ($posStart !== false) {
                                    $average = substr($average, 0, $posStart);
                                }
                            }
                            $data[$column] = new Bold($average);
                        } elseif (strpos($column, 'Period') !== false) {
                            // keine Tests in der Periode vorhanden
                            $data[$column] = '';
                        }
                    }
                }

                $dataList[] = $data;
            }

            // Fach-Klassendurchschnitt pro Test
            $data = array();
            if (!empty($columnDefinition)) {
                foreach ($columnDefinition as $column => $value) {
                    if (strpos($column, 'Test') !== false) {
                        $testId = substr($column, strlen('Test'));
                        $tblTest = Evaluation::useService()->getTestById($testId);
                        if ($tblTest) {
                            $average = Gradebook::useService()->getAverageByTest($tblTest);
                            $data[$column] = new Muted($average ? $average : '');
                        }
                    } elseif (strpos($column, 'Number') !== false) {
                        $data[$column] = new Muted('&#216;');
                    } elseif (strpos($column, 'Student') !== false) {
                        $data[$column] = new Muted('Fach-Klasse');
                    } else {
                        $data[$column] = '';
                    }
                }
            }
            $dataList[] = $data;
        }

        $tableData = new TableData(
            $dataList, null, $columnDefinition, array('pageLength' => -1)
        );

        // oberste Tabellen-Kopf-Zeile erstellen
        $headTableColumnList = array();
        $headTableColumnList[] = new TableColumn('', 2, '20%');
        if (!empty($periodListCount)) {
            foreach ($periodListCount as $periodId => $count) {
                $tblPeriod = Term::useService()->getPeriodById($periodId);
                if ($tblPeriod) {
                    $headTableColumnList[] = new TableColumn($tblPeriod->getDisplayName(), $count);
                }
            }
            $headTableColumnList[] = new TableColumn('Gesamt');
        }
        $tableData->prependHead(
            new TableHead(
                new TableRow(
                    $headTableColumnList
                )
            )
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                        new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Fach-Klasse',
                                        array(
                                            'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                                            ($tblDivisionSubject->getServiceTblSubject() ? $tblDivisionSubject->getServiceTblSubject()->getName() : '') .
                                            ($tblDivisionSubject->getTblSubjectGroup() ? new Small(
                                                ' (Gruppe: ' . $tblDivisionSubject->getTblSubjectGroup()->getName() . ')') : ''),
                                            'Fachlehrer: ' . Division::useService()->getSubjectTeacherNameList(
                                                $tblDivision, $tblSubject, $tblDivisionSubject->getTblSubjectGroup()
                                                ? $tblDivisionSubject->getTblSubjectGroup() : null
                                            )
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ),
                                    6
                                ),
                                new LayoutColumn(new Panel(
                                    'Berechnungsvorschrift',
                                    $tblScoreRule ? $scoreRuleText : new Bold(new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                        new Ban() . ' Keine Berechnungsvorschrift hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                                    )),
                                    Panel::PANEL_TYPE_INFO
                                ), 6),
                                new LayoutColumn(
                                    $tableData
                                )
                            )
                        ),
                    )
                ),
                (!empty($errorRowList) ? new LayoutGroup($errorRowList) : null)
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionSubjectId
     *
     * @return Stage|string
     */
    public function frontendHeadmasterSelectedGradeBook(
        $DivisionSubjectId = null
    ) {

        $Stage = new Stage('Notenbuch', 'Anzeigen');

        if ($DivisionSubjectId === null || !($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return $Stage . new Danger(new Ban() . ' Notenbuch nicht gefunden.') . new Redirect('/Education/Graduation/Gradebook/Gradebook/Headmaster',
                Redirect::TIMEOUT_ERROR);
        }

        $this->contentSelectedGradeBook($Stage, $tblDivisionSubject,
            '/Education/Graduation/Gradebook/Gradebook/Headmaster');

        return $Stage;
    }

    /**
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendStudentGradebook($YearId = null)
    {

        $Stage = new Stage('Notenübersicht', 'Schüler/Eltern');
        $Stage->setMessage(
            'Anzeige der Zensuren für die Schüler und Eltern. <br>
            Der angemeldete Schüler sieht nur seine eigenen Zensuren. <br>
            Der angemeldete Sorgeberechtigte sieht nur die Zensuren seiner Schützlinge. <br>'
        );

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $rowList = array();
        $tblDisplayYearList = array();
        $data = array();

        $tblPersonList = $this->getPersonListForStudent();

        // Jahre ermitteln, in denen Schüler in einer Klasse ist
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                if ($tblDivisionStudentList) {

                    /** @var TblDivisionStudent $tblDivisionStudent */
                    foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                        $tblDivision = $tblDivisionStudent->getTblDivision();
                        if ($tblDivision && ($tblYear = $tblDivision->getServiceTblYear())) {
                            $tblDisplayYearList[$tblYear->getId()] = $tblYear;
                            $data[$tblYear->getId()][$tblPerson->getId()][$tblDivision->getId()] = $tblDivision;
                        }
                    }
                }
            }
        }

        if (!empty($tblDisplayYearList)) {
            $tblDisplayYearList = $this->getSorter($tblDisplayYearList)->sortObjectBy('DisplayName');
            $lastYear = end($tblDisplayYearList);
            /** @var TblYear $year */
            foreach ($tblDisplayYearList as $year) {
                $Stage->addButton(
                    new Standard(
                        ($YearId === null && $year->getId() == $lastYear->getId()) ? new Info(new Bold($year->getDisplayName())) : $year->getDisplayName(),
                        '/Education/Graduation/Gradebook/Student/Gradebook',
                        null,
                        array(
                            'YearId' => $year->getId()
                        )
                    )
                );
            }

            if ($YearId === null) {
                $YearId = $lastYear->getId();
            }
        }

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $tableHeaderList = array();
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
            if ($tblPeriodList) {
                $tableHeaderList['Subject'] = 'Fach';
                foreach ($tblPeriodList as $tblPeriod) {
                    $tableHeaderList['Period' . $tblPeriod->getId()] = new Bold($tblPeriod->getDisplayName());
                }
            }

            if (!empty($data)) {
                if (isset($data[$tblYear->getId()])) {
                    foreach ($data[$tblYear->getId()] as $personId => $divisionList) {
                        $tblPerson = Person::useService()->getPersonById($personId);
                        if ($tblPerson && is_array($divisionList)) {
                            /** @var TblDivision $tblDivision */
                            foreach ($divisionList as $tblDivision) {
                                if ($tblDivision && $tblDivision->getServiceTblYear()) {
                                    // alle Klassen zum aktuellen Jahr
                                    if ($tblDivision->getServiceTblYear()->getId() == $tblYear->getId()) {
                                        $rowList[] = new LayoutRow(new LayoutColumn(new Title($tblPerson->getLastFirstName()
                                            . new Small(new Muted(' Klasse ' . $tblDivision->getDisplayName()))),
                                            12));
                                        $tableDataList = array();
                                        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                                        if ($tblDivisionSubjectList) {
                                            foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                                if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                                                    if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                                        $hasStudentSubject = false;
                                                        $tblDivisionSubjectWhereGroup =
                                                            Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                                                $tblDivision,
                                                                $tblDivisionSubject->getServiceTblSubject()
                                                            );
                                                        if ($tblDivisionSubjectWhereGroup) {
                                                            foreach ($tblDivisionSubjectWhereGroup as $tblDivisionSubjectGroup) {

                                                                if (Division::useService()->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubjectGroup,
                                                                    $tblPerson)
                                                                ) {
                                                                    $hasStudentSubject = true;
                                                                }
                                                            }
                                                        } else {
                                                            $hasStudentSubject = true;
                                                        }
                                                        if ($hasStudentSubject) {
                                                            $tableDataList[$tblDivisionSubject->getServiceTblSubject()->getId()]['Subject'] = $tblDivisionSubject->getServiceTblSubject()->getName();

                                                            if ($tblPeriodList) {
                                                                foreach ($tblPeriodList as $tblPeriod) {
                                                                    $tblGradeList = Gradebook::useService()->getGradesByStudent(
                                                                        $tblPerson,
                                                                        $tblDivision,
                                                                        $tblDivisionSubject->getServiceTblSubject(),
                                                                        $tblTestType,
                                                                        $tblPeriod
                                                                    );

                                                                    $subTableHeaderList = array();
                                                                    $subTableDataList = array();

                                                                    if ($tblGradeList) {
                                                                        foreach ($tblGradeList as $tblGrade) {
                                                                            $tblTest = $tblGrade->getServiceTblTest();
                                                                            if ($tblTest) {
                                                                                if ($tblTest->getServiceTblGradeType() && $tblTest->getReturnDate()) {
                                                                                    $testReturnDate = (new \DateTime($tblTest->getReturnDate()))->format("Y-m-d");
                                                                                    $now = (new \DateTime('now'))->format("Y-m-d");
                                                                                    if ($testReturnDate < $now) {

                                                                                        // Test anzeigen
                                                                                        $date = $tblTest->getDate();
                                                                                        if (strlen($date) > 6) {
                                                                                            $date = substr($date, 0, 6);
                                                                                        }
                                                                                        $subTableHeaderList['Test' . $tblTest->getId()] = new Small(new Muted($date)) . '<br>'
                                                                                            . ($tblTest->getServiceTblGradeType()->isHighlighted()
                                                                                                ? $tblTest->getServiceTblGradeType()->getCode()
                                                                                                : new Muted($tblTest->getServiceTblGradeType()->getCode()));

                                                                                        $gradeValue = $tblGrade->getGrade();
                                                                                        if ($gradeValue) {
                                                                                            $trend = $tblGrade->getTrend();
                                                                                            if (TblGrade::VALUE_TREND_PLUS === $trend) {
                                                                                                $gradeValue .= '+';
                                                                                            } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                                                                                                $gradeValue .= '-';
                                                                                            }
                                                                                        }

                                                                                        $subTableDataList[0]['Test' . $tblTest->getId()] = $gradeValue ? $gradeValue : '';
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if (!empty($subTableHeaderList)) {
                                                                        $tableDataList[$tblDivisionSubject->getServiceTblSubject()->getId()]['Period' . $tblPeriod->getId()] = new TableData(
                                                                            $subTableDataList, null,
                                                                            $subTableHeaderList,
                                                                            false
                                                                        );
                                                                    } else {
                                                                        $tableDataList[$tblDivisionSubject->getServiceTblSubject()->getId()]['Period' . $tblPeriod->getId()] = '';
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $rowList[] = new LayoutRow(new LayoutColumn(
                                            !empty($tableDataList)
                                                ? new TableData(
                                                $tableDataList, null, $tableHeaderList, null
                                            )
                                                : new Warning('Keine Fächer vorhanden.', new Exclamation())
                                        ));
                                        $rowList[] = new LayoutRow(new LayoutColumn(new Header('&nbsp;'), 12));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        ($YearId !== null ?
                            new Panel('Schuljahr', $tblYear->getDisplayName(), Panel::PANEL_TYPE_INFO)
                            : '')
                    )
                ))),
                ($YearId !== null ? new LayoutGroup($rowList) : null)
            ))
        );
        return $Stage;
    }

    /**
     * @return false|TblPerson[]
     */
    private function getPersonListForStudent()
    {
        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $tblPersonList = array();
        if ($tblPerson) {
            $tblPersonList[] = $tblPerson;

            $tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
            if ($tblPersonRelationshipList) {
                foreach ($tblPersonRelationshipList as $relationship) {
                    if ($relationship->getTblType()->getName() == 'Sorgeberechtigt' && $relationship->getServiceTblPersonTo()) {
                        $tblPersonList[] = $relationship->getServiceTblPersonTo();
                    }
                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }

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
            'Hier werden die Berechnungsvorschriften, für die automatische Durchschnittsberechnung der Zensuren, verwaltet. <br>
            Die Berechnungsvorschrift bildet die 1. Ebene und setzt sich aus einer oder mehrerer Berechnungsvarianten
            zusammen.'
        );

        $this->setScoreStageMenuButtons($Stage, self::SCORE_RULE);

        $tblScoreRuleAll = Gradebook::useService()->getScoreRuleAll();
        if ($tblScoreRuleAll) {
            foreach ($tblScoreRuleAll as &$tblScoreRule) {

                $structure = array();
                if ($tblScoreRule->getDescription() != '') {
                    $structure[] = 'Beschreibung: ' . $tblScoreRule->getDescription() . '<br>';
                }

                $tblScoreConditions = Gradebook::useService()->getScoreConditionsByRule($tblScoreRule);
                if ($tblScoreConditions) {
                    $tblScoreConditions = $this->getSorter($tblScoreConditions)->sortObjectList('Priority');

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
                                    . $tblScoreConditionGroupList->getTblScoreGroup()->getDisplayMultiplier();

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

                if (empty($structure)) {
                    $tblScoreRule->Structure = '';
                } else {
                    $tblScoreRule->Structure = implode('<br>', $structure);
                }

                $tblScoreRule->Option =
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Edit', new Edit(),
                        array('Id' => $tblScoreRule->getId()), 'Bearbeiten')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Select', new Listing(),
                        array('Id' => $tblScoreRule->getId()), 'Berechnungsvarianten auswählen')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Division', new Equalizer(),
                        array('Id' => $tblScoreRule->getId()), 'Fach-Klassen zuordnen')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/SubjectGroup', new Group(),
                        array('Id' => $tblScoreRule->getId()), 'Fachgruppen zuordnen'));
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
                            new TableData($tblScoreRuleAll, null, array(
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
            'Hier werden die Berechnungsvarianten verwaltet. <br>
            Die Berechnungsvariante bildet die 2. Ebene der Berechnungsvorschriften und setzt sich aus einer Priorität
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

        $tblScoreConditionAll = Gradebook::useService()->getScoreConditionAll();
        if ($tblScoreConditionAll) {
            foreach ($tblScoreConditionAll as &$tblScoreCondition) {
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

                $tblScoreCondition->ScoreGroups = $scoreGroups;
                $tblScoreCondition->GradeTypes = $gradeTypes;
                $tblScoreCondition->Option =
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Edit', new Edit(),
                        array('Id' => $tblScoreCondition->getId()), 'Bearbeiten')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/Group/Select', new Listing(),
                        array('Id' => $tblScoreCondition->getId()), 'Zensuren-Gruppen auswählen')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Condition/GradeType/Select',
                        new Equalizer(),
                        array('Id' => $tblScoreCondition->getId()), 'Zensuren-Typen (Bedingungen) auswählen'));
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
                            new TableData($tblScoreConditionAll, null, array(
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
            'Hier werden die Zensuren-Gruppen verwaltet. <br>
            Die Zensuren-Gruppe bildet die 3. Ebene der Berechnungsvorschriften und setzt sich aus einem Faktor
            und Zensuren-Typen zusammen. <br>
            Der Faktor gibt an, wie die Zensuren-Gruppe als ganzes zu anderen Zensuren-Gruppen gewichtet wird. <br>'
        );
        $this->setScoreStageMenuButtons($Stage, self::GRADE_GROUP);

        $tblScoreGroupAll = Gradebook::useService()->getScoreGroupAll();
        if ($tblScoreGroupAll) {
            foreach ($tblScoreGroupAll as &$tblScoreGroup) {
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
                $tblScoreGroup->DisplayMultiplier = $tblScoreGroup->getDisplayMultiplier();
                $tblScoreGroup->GradeTypes = $gradeTypes;
                $tblScoreGroup->Option =
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Group/Edit', new Edit(),
                        array('Id' => $tblScoreGroup->getId()), 'Bearbeiten')) .
                    (new Standard('', '/Education/Graduation/Gradebook/Score/Group/GradeType/Select', new Listing(),
                        array('Id' => $tblScoreGroup->getId()), 'Zensuren-Typen auswählen'));
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
                            new TableData($tblScoreGroupAll, null, array(
                                'Name' => 'Name',
                                'DisplayMultiplier' => 'Faktor',
                                'GradeTypes' => 'Zensuren-Typen',
//                                'Round' => 'Runden',
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
//                new FormColumn(
//                    new TextField('ScoreGroup[Round]', '', 'Rundung'), 2
//                ),
                new FormColumn(
                    new TextField('ScoreGroup[Multiplier]', '', 'Faktor'), 2
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

                if ($tblScoreGroupGradeTypeListByGroup) {
                    foreach ($tblScoreGroupGradeTypeListByGroup as &$tblScoreGroupGradeTypeList) {
                        if ($tblScoreGroupGradeTypeList->getTblGradeType()) {
                            $tblScoreGroupGradeTypeList->Name = $tblScoreGroupGradeTypeList->getTblGradeType()->getName();
                            $tblScoreGroupGradeTypeList->DisplayMultiplier = $tblScoreGroupGradeTypeList->getDisplayMultiplier();
                            $tblScoreGroupGradeTypeList->Option =
                                (new \SPHERE\Common\Frontend\Link\Repository\Primary(
                                    'Entfernen', '/Education/Graduation/Gradebook/Score/Group/GradeType/Remove',
                                    new Minus(), array(
                                    'Id' => $tblScoreGroupGradeTypeList->getId()
                                )))->__toString();
                        } else {
                            $tblScoreGroupGradeTypeList = false;
                        }
                    }
                    $tblScoreGroupGradeTypeListByGroup = array_filter($tblScoreGroupGradeTypeListByGroup);
                }

                if ($tblGradeTypeAll) {
                    foreach ($tblGradeTypeAll as $tblGradeType) {
                        $tblGradeType->Option =
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
                            ))->__toString();
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
                                    new TableData($tblScoreGroupGradeTypeListByGroup, null,
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
                                    new TableData($tblGradeTypeAll, null,
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
     * @return Stage
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
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreConditionGroupList->getTblScoreGroup()->getDisplayMultiplier() . ')'));
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
                            . new Small(new Muted(' (' . 'Faktor: ' . $tblScoreGroup->getDisplayMultiplier() . ')'));
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
    public function frontendScoreConditionGradeTypeRemove(
        $Id = null
    ) {

        $Stage = new Stage('Berechnungsvariante (Bedingungen)', 'Zensuren-Typ von einer Berechnungsvariante entfernen');

        $tblScoreConditionGradeTypeList = Gradebook::useService()->getScoreConditionGradeTypeListById($Id);
        if ($tblScoreConditionGradeTypeList) {
            $Stage->setContent(Gradebook::useService()->removeScoreConditionGradeTypeList($tblScoreConditionGradeTypeList));
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
                                    $name = new \SPHERE\Common\Frontend\Text\Repository\Italic((
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
                                    new \SPHERE\Common\Frontend\Text\Repository\Italic('Alle  verfügbaren Fächer'),
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

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyGradeType(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Zensuren-Type', 'Löschen');

        $tblGradeType = Gradebook::useService()->getGradeTypeById($Id);
        if ($tblGradeType) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Gradebook/GradeType', new ChevronLeft())
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                                new Panel(
                                    'Zensuren-Typ',
                                    $tblGradeType->getName()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblGradeType->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Panel(new Question() . ' Diesen Zensuren-Typ wirklich löschen?',
                                    array(
                                        $tblGradeType->getName(),
                                        $tblGradeType->getDescription() ? $tblGradeType->getDescription() : null
                                    ),
                                    Panel::PANEL_TYPE_DANGER,
                                    new Standard(
                                        'Ja', '/Education/Graduation/Gradebook/GradeType/Destroy', new Ok(),
                                        array('Id' => $Id, 'Confirm' => true)
                                    )
                                    . new Standard(
                                        'Nein', '/Education/Graduation/Gradebook/GradeType', new Disable())
                                )
                            )
                        )
                    )))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Gradebook::useService()->destroyGradeType($tblGradeType)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Der Zensuren-Typ wurde gelöscht')
                                : new Danger(new Ban() . ' Der Zensuren-Typ konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Zensuren-Typ nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Gradebook/GradeType', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendScoreType()
    {

        $Stage = new Stage('Bewertungssystem', 'Übersicht');
        $Stage->setMessage(
            'Hier werden alle verfügbaren Bewertungssysteme angezeigt. Nach der Auswahl eines Bewertungssystems können dem
            Bewertungssystem die entsprechenden Fach-Klassen zugeordnet werden.'
        );

        $tblScoreTypeAll = Gradebook::useService()->getScoreTypeAll();
        if ($tblScoreTypeAll) {
            foreach ($tblScoreTypeAll as &$tblScoreType) {
                $tblScoreType->Option =
                    (new Standard('', '/Education/Graduation/Gradebook/Type/Select',
                        new Equalizer(),
                        array('Id' => $tblScoreType->getId()), 'Fach-Klassen zuordnen'));
            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData(
                                $tblScoreTypeAll, null, array(
                                    'Name' => 'Name',
                                    'Option' => ''
                                )
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendScoreTypeSelect(
        $Id = null,
        $YearId = null,
        $Data = null
    ) {

        $Stage = new Stage('Bewertungssystem', 'Fach-Klassen einem Bewertungssystem zuordnen');
        $Stage->setMessage('Hier können dem ausgewählten Bewertungssystem Fach-Klassen zugeordnet werden. <br>
        ' . new Bold(new Exclamation() . ' Hinweis:') . ' Sobald Zensuren für eine Fach-Klasse vergeben wurden,
        kann das Bewertungssystem dieser Fach-Klasse nicht mehr geändert werden. Außerdem kann die Fach-Klasse immer nur ein Bewertungssystem besitzen.');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Gradebook/Type', new ChevronLeft()));

        $tblScoreType = Gradebook::useService()->getScoreTypeById($Id);
        if ($tblScoreType) {


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
                            ? new Info(new Edit() . ' ' . $tblYear->getDisplayName())
                            : $tblYear->getDisplayName(),
                        '/Education/Graduation/Gradebook/Type/Select',
                        null,
                        array(
                            'Id' => $tblScoreType->getId(),
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
                                        if ($tblScoreRuleDivisionSubject->getTblScoreType()
                                            && $tblScoreRuleDivisionSubject->getTblScoreType()->getId() == $tblScoreType->getId()
                                        ) {
                                            $Global->POST['Data'][$tblDivision->getId()][$subject->getId()] = 1;
                                        }
                                    }
                                }
                                $Global->savePost();
                            }

//                            $tblNewSubject = new TblSubject();
//                            $tblNewSubject->setId(-1);
//                            $tblNewSubject->setName('Alle wählbaren Fächer');
//                            array_unshift($subjectList, $tblNewSubject);

                            $countSubject = 0;
                            $subjectList = $this->getSorter($subjectList)->sortObjectBy('Acronym');

                            /** @var TblSubject $tblSubject */
                            foreach ($subjectList as &$tblSubject) {
                                $isDisabled = false;
                                if ($tblSubject->getId() === -1) {
                                    $name = new \SPHERE\Common\Frontend\Text\Repository\Italic((
                                        $tblSubject->getAcronym() ? new Bold($tblSubject->getAcronym() . ' ') : '') . $tblSubject->getName()
                                    );
                                } else {
                                    $name = ($tblSubject->getAcronym() ? new Bold($tblSubject->getAcronym() . ' ') : '') . $tblSubject->getName();
                                }

                                $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                                    $tblDivision, $tblSubject
                                );
                                if ($tblScoreRuleDivisionSubject) {
                                    if ($tblScoreRuleDivisionSubject->getTblScoreType()
                                        && $tblScoreRuleDivisionSubject->getTblScoreType()->getId() != $tblScoreType->getId()
                                    ) {
                                        $isDisabled = true;
                                        $name .= ' ' . new Label($tblScoreRuleDivisionSubject->getTblScoreType()->getName(),
                                                Label::LABEL_TYPE_PRIMARY);
                                    }
                                }

                                // Bewertungssystem nicht mehr bearbeitbar, nachdem Zensuren vergeben wurden
                                if (Gradebook::useService()->existsGrades($tblDivision, $tblSubject)) {
                                    $isDisabled = true;
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
                                $tblNewSubject = new CheckBox(
                                    'Data[' . $tblDivision->getId() . '][-1]',
                                    new \SPHERE\Common\Frontend\Text\Repository\Italic('Alle  verfügbaren Fächer'),
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
                                    'Bewertungssystem',
                                    new Bold($tblScoreType->getName()),
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
                                    Gradebook::useService()->updateScoreTypeDivisionSubject(
                                        (new Form(
                                            $formGroupList
                                        ))->appendFormButton(new Primary('Speichern', new Save())), $tblScoreType,
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
