<?php
namespace SPHERE\Application\Education\Graduation\Evaluation;

use DateTime;
use SPHERE\Application\Api\Education\Graduation\Evaluation\ApiEvaluation;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradebook;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Quote;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing as ListingLayout;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Graduation\Evaluation
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendTest()
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTestTeacher();
            } else {
                return $this->frontendHeadmasterTest();
            }
        } else {
            return $this->frontendTestTeacher();
        }
    }

    /**
     * @param bool $IsAllYears
     * @param null|integer $YearId
     *
     * @return Stage
     */
    public function frontendTestTeacher($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Leistungsüberprüfung', 'Auswahl');
        $Stage->setMessage(
            'Verwaltung der Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten),
            wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                '/Education/Graduation/Evaluation/Test/Teacher',
                new Edit()));
            $Stage->addButton(new Standard('Ansicht: Leitung', '/Education/Graduation/Evaluation/Test/Headmaster'));
        }

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $buttonList = $this->setYearButtonList('/Education/Graduation/Evaluation/Test/Teacher', $IsAllYears, $YearId, $tblYear, false);

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        $isDivisionTeacher = false;

        if ($tblPerson) {
            // Fachlehrer
            $tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson);
            if ($tblSubjectTeacherAllByTeacher) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    $tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject();
                    // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                    /** @var TblYear $tblYear */
                    if ($tblYear && $tblDivisionSubject && $tblDivisionSubject->getTblDivision()
                        && $tblDivisionSubject->getTblDivision()->getServiceTblYear()
                        && $tblDivisionSubject->getTblDivision()->getServiceTblYear()->getId() != $tblYear->getId()
                    ) {
                        continue;
                    }
                    if ($tblDivisionSubject && $tblDivisionSubject->getTblDivision()
                        && ($tblDivisionSubject->getHasGrading() || (($tblSetting = Consumer::useService()->getSetting(
                                    'Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading'
                                ))
                                && $tblSetting->getValue()))
                    ) {
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            if ($tblDivisionSubject->getServiceTblSubject()) {
                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                    = $tblDivisionSubject->getId();
                            }
                        } else {
                            if ($tblSubjectTeacher->getTblDivisionSubject()->getServiceTblSubject()
                                && $tblDivisionSubject->getServiceTblSubject()
                            ) {
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
            }

            if (($tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson))) {
                $isDivisionTeacher = true;
            }

            // Klassenlehrer für Kopfnotenvorschlag
            if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation',
                'ShowProposalBehaviorGrade'))
                && $tblSetting->getValue()
            ) {
                if (($tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson))) {
                    foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                        if (($tblDivisionItem = $tblDivisionTeacher->getTblDivision())
                            && ($tblYearItem = $tblDivisionItem->getServiceTblYear())
                        ) {
                            // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                            /** @var TblYear $tblYear */
                            if ($tblYear
                                && $tblYearItem->getId() != $tblYear->getId()
                            ) {
                                continue;
                            }

                            $divisionSubjectTable[] = array(
                                'Year' => $tblYearItem->getDisplayName(),
                                'Type' => $tblDivisionItem->getTypeName(),
                                'Division' => $tblDivisionItem->getDisplayName(),
                                'Subject' => '',
                                'SubjectGroup' => '',
                                'Option' => new Standard(
                                    '', '/Education/Graduation/Evaluation/Test/Teacher/Proposal/Selected', new Select(),
                                    array(
                                        'DivisionId' => $tblDivisionItem->getId()
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }
            }

            // Klassenlehrer
//            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
//            if ($tblDivisionTeacherAllByTeacher) {
//                foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
//                    if ($tblDivisionTeacher->getTblDivision()) {
//                        $tblDivisionSubjectAllByDivision
//                            = Division::useService()->getDivisionSubjectByDivision($tblDivisionTeacher->getTblDivision());
//                        if ($tblDivisionSubjectAllByDivision) {
//                            foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
//                                if ($tblDivisionSubject->getTblSubjectGroup()) {
//                                    if ($tblDivisionSubject->getServiceTblSubject()) {
//                                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
//                                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
//                                        [$tblDivisionSubject->getTblSubjectGroup()->getId()]
//                                            = $tblDivisionSubject->getId();
//                                    }
//                                } else {
//                                    if ($tblDivisionSubject->getServiceTblSubject()) {
//                                        $tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject
//                                            = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
//                                            $tblDivisionSubject->getTblDivision(),
//                                            $tblDivisionSubject->getServiceTblSubject()
//                                        );
//                                        if ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject) {
//                                            foreach ($tblDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject as $item) {
//                                                $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
//                                                [$tblDivisionSubject->getServiceTblSubject()->getId()]
//                                                [$item->getTblSubjectGroup()->getId()]
//                                                    = $item->getId();
//                                            }
//                                        } else {
//                                            $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
//                                            [$tblDivisionSubject->getServiceTblSubject()->getId()]
//                                                = $tblDivisionSubject->getId();
//                                        }
//                                    }
//                                }
//                            }
//                        }
//
//                    }
//                }
//            }
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
                                        'Option' => new Standard(
                                            '', '/Education/Graduation/Evaluation/Test/Teacher/Selected', new Select(),
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
                                    'Option' => new Standard(
                                        '', '/Education/Graduation/Evaluation/Test/Teacher/Selected', new Select(),
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
                // Klassenlehrer
                $isDivisionTeacher
                    ? new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(new Standard(
                            'Planungsübersicht',
                            '/Education/Graduation/Evaluation/Test/Teacher/HighlightedTestsOverview',
                            null,
                            array('PersonId' => $tblPerson ? $tblPerson->getId() : 0)
                        ))
                    ))
                ))
                    : null,
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'Option' => ''
                            ), array(
                                'order'      => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                    array('4', 'asc')
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2)
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
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterTest($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Leistungsüberprüfung', 'Auswahl');
        $Stage->setMessage(
            'Verwaltung aller Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten).'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Graduation/Evaluation/Test/Teacher'));
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                '/Education/Graduation/Evaluation/Test/Headmaster', new Edit()));
        }

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        $buttonList = $this->setYearButtonList('/Education/Graduation/Evaluation/Test/Headmaster', $IsAllYears,
            $YearId, $tblYear);

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as $tblDivision) {
                // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                /** @var TblYear $tblYear */
                if ($tblYear && $tblDivision && $tblDivision->getServiceTblYear()
                    && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                ) {
                    continue;
                }

                $tblDivisionSubjectAllByDivision = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                if ($tblDivisionSubjectAllByDivision) {
                    /** @var TblDivisionSubject $tblDivisionSubject */
                    foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                        if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject()
                            && $tblDivisionSubject->getTblDivision()
                            && ($tblDivisionSubject->getHasGrading() || (($tblSetting = Consumer::useService()->getSetting(
                                        'Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading'
                                    ))
                                    && $tblSetting->getValue()))
                        ) {
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
                                        'Option' => new Standard(
                                            '', '/Education/Graduation/Evaluation/Test/Headmaster/Selected',
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
                                    'Option' => new Standard(
                                        '', '/Education/Graduation/Evaluation/Test/Headmaster/Selected', new Select(),
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
                        new LayoutColumn(new Standard(
                            'Planungsübersicht',
                            '/Education/Graduation/Evaluation/Test/Headmaster/HighlightedTestsOverview'
                        ))
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
                                'Option' => ''
                            ), array(
                                'order'      => array(
                                    array('0', 'desc'),
                                    array('2', 'asc'),
                                    array('3', 'asc'),
                                    array('4', 'asc')
                                ),
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 2),
                                    array('orderable' => false, 'targets' => -1),
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
    public function frontendTask()
    {

        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendDivisionTeacherTask();
            } else {
                return $this->frontendHeadmasterTask();
            }
        } else {
            return $this->frontendDivisionTeacherTask();
        }
    }

    /**
     * @param $IsAllYears
     * @param $YearId
     *
     * @return Stage
     */
    public function frontendDivisionTeacherTask($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Notenaufträge', 'Übersicht');
        $Stage->setMessage(
            'Anzeige der Kopfnoten- und Stichtagsnotenaufträge (inklusive vergebener Zensuren),
            wo der angemeldete Lehrer als Klassenlehrer hinterlegt ist.'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                '/Education/Graduation/Evaluation/Task/Teacher',
                new Edit()));
            $Stage->addButton(new Standard('Ansicht: Leitung', '/Education/Graduation/Evaluation/Task/Headmaster'));
        }

        $buttonList = $this->setYearButtonList('/Education/Graduation/Evaluation/Task/Teacher', $IsAllYears, $YearId, $tblYear, true);

        $taskList = array();

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }
        if ($tblPerson) {
            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            if ($tblDivisionTeacherAllByTeacher) {
                foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                    if ($tblDivisionTeacher->getTblDivision()) {
                        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
                        $tblTestList = Evaluation::useService()->getTestAllByTestTypeAndDivision(
                            $tblTestType,
                            $tblDivisionTeacher->getTblDivision()
                        );
                        if ($tblTestList) {
                            foreach ($tblTestList as $tblTest) {
                                $taskList[$tblTest->getTblTask()->getId()] = $tblTest->getTblTask();
                            }
                        }
                        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
                        $tblTestList = Evaluation::useService()->getTestAllByTestTypeAndDivision(
                            $tblTestType,
                            $tblDivisionTeacher->getTblDivision()
                        );
                        if ($tblTestList) {
                            foreach ($tblTestList as $tblTest) {
                                $taskList[$tblTest->getTblTask()->getId()] = $tblTest->getTblTask();
                            }
                        }
                    }
                }
            }
        }

        $contentTable = array();
        if (!empty($taskList)) {
            /** @var TblTask $tblTask */
            /** @var TblYear $tblYear */
            foreach ($taskList as $tblTask) {
                if ($IsAllYears || ($tblYear && $tblTask->getServiceTblYear() && $tblTask->getServiceTblYear()->getId() == $tblYear->getId())) {
                    $contentTable[] = array(
                        'Date' => $tblTask->getDate(),
                        'Type' => $tblTask->getTblTestType()->getName(),
                        'Name' => $tblTask->getName(),
                        'Period' => $tblTask->getServiceTblPeriod()
                            ? $tblTask->getServiceTblPeriod()->getDisplayName() : 'Gesamtes Schuljahr',
                        'EditPeriod' => $tblTask->getFromDate() . ' - ' . $tblTask->getToDate(),
                        'Option' =>
                            (new Standard('',
                                '/Education/Graduation/Evaluation/Task/Teacher/Grades',
                                new Equalizer(),
                                array('Id' => $tblTask->getId(), 'IsAllYears' => $IsAllYears),
                                'Zensurenübersicht')
                            )
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList)
                    )),
                    new LayoutRow(array(
                            new LayoutColumn(
                                new TableData(
                                    $contentTable, null, array(
                                    'Date' => 'Stichtag',
                                    'Type' => 'Kategorie',
                                    'Name' => 'Name',
                                    'Period' => 'Noten-Zeitraum',
                                    'EditPeriod' => 'Bearbeitungszeitraum',
                                    'Option' => '',
                                ), array(
                                        'order' => array(
                                            array(0, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 0)
                                        )
                                    )
                                )
                            )
                        )
                    )
                ), new Title(new ListingTable() . ' Übersicht')),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Task
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterTask($Task = null, $IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Notenaufträge', 'Übersicht');
        $Stage->setMessage(
            'Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebenen Zensuren).'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Graduation/Evaluation/Task/Teacher'));
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                '/Education/Graduation/Evaluation/Task/Headmaster', new Edit()));
        }

        $buttonList = $this->setYearButtonList('/Education/Graduation/Evaluation/Task/Headmaster', $IsAllYears, $YearId, $tblYear, true);

        $tblTaskAll = Evaluation::useService()->getTaskAll();

        $contentTable = array();
        if ($tblTaskAll) {
            foreach ($tblTaskAll as $tblTask) {
                /** @var TblYear $tblYear */
                if ($IsAllYears || ($tblYear && $tblTask->getServiceTblYear() && $tblTask->getServiceTblYear()->getId() == $tblYear->getId())) {
                    $hasEdit = true;

                    $contentTable[] = array(
                        'Date' => $tblTask->getDate(),
                        'Type' => $tblTask->getTblTestType()->getName(),
                        'Name' => $tblTask->getName(),
                        'Period' => $tblTask->getServiceTblPeriod()
                            ? $tblTask->getServiceTblPeriod()->getDisplayName() : 'Gesamtes Schuljahr',
                        'EditPeriod' => $tblTask->getFromDate() . ' - ' . $tblTask->getToDate(),
                        'Option' => ($hasEdit ? (new Standard('',
                                '/Education/Graduation/Evaluation/Task/Headmaster/Edit',
                                new Edit(),
                                array('Id' => $tblTask->getId(), 'IsAllYears' => $IsAllYears),
                                'Bearbeiten')) : '')
                            . ($tblTask->isLocked() ? null : new Standard('',
                                '/Education/Graduation/Evaluation/Task/Headmaster/Destroy', new Remove(),
                                array('Id' => $tblTask->getId(), 'IsAllYears' => $IsAllYears),
                                'Löschen'))
                            . (new Standard('',
                                '/Education/Graduation/Evaluation/Task/Headmaster/Division',
                                new Listing(),
                                array('Id' => $tblTask->getId(), 'IsAllYears' => $IsAllYears),
                                'Klassen zuordnen')
                            )
                            . (new Standard('',
                                '/Education/Graduation/Evaluation/Task/Headmaster/Grades',
                                new Equalizer(),
                                array('Id' => $tblTask->getId(), 'IsAllYears' => $IsAllYears),
                                'Zensurenübersicht')
                            ),
                    );
                }
            }
        }

        if ($tblYear) {
            $Global = $this->getGlobal();
            $Global->POST['Select']['Year'] = $tblYear->getId();
            $Global->savePost();
        }
        $Form = $this->formTask();
        $Form
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList)
                    )),
                    new LayoutRow(array(
                            new LayoutColumn(
                                new TableData(
                                    $contentTable, null, array(
                                    'Date' => 'Stichtag',
                                    'Type' => 'Kategorie',
                                    'Name' => 'Name',
                                    'Period' => 'Noten-Zeitraum',
                                    'EditPeriod' => 'Bearbeitungszeitraum',
                                    'Option' => '',
                                ), array(
                                        'order' => array(
                                            array(0, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 0),
                                            array('orderable' => false, 'targets' => -1),
                                        ),
                                    )
                                )
                            )
                        )
                    )
                ), new Title(new ListingTable() . ' Übersicht')),
                $IsAllYears
                    ? null
                    : new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $tblYear ? new Well(Evaluation::useService()->createTask($Form, $Task, $tblYear)) : null
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
    private function formTask()
    {

        $tblTestTypeAllWhereTask = Evaluation::useService()->getTestTypeAllWhereTask();

        $periodList[] = new SelectBoxItem(0, '');
        $periodList[] = new SelectBoxItem(TblTask::FIRST_PERIOD_ID, TblTask::FIRST_PERIOD_NAME);
        $periodList[] = new SelectBoxItem(TblTask::SECOND_PERIOD_ID, TblTask::SECOND_PERIOD_NAME);

        $tblScoreTypeAll = Gradebook::useService()->getScoreTypeAll();
        if ($tblScoreTypeAll) {
            array_push($tblScoreTypeAll, new TblScoreType());
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Task[Type]', 'Kategorie', array('Name' => $tblTestTypeAllWhereTask)), 4
                ),
                new FormColumn(
                    new SelectBox('Task[Period]', 'Noten-Zeitraum beschränken', array('Name' => $periodList)), 4
                ),
                new FormColumn(
                    new SelectBox('Task[ScoreType]', 'Bewertungssystem überschreiben',
                        array('Name' => $tblScoreTypeAll)), 4
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Task[Name]', '', 'Name'), 12
                ),

            )),
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Task[Date]', '', 'Stichtag', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Task[FromDate]', '', 'Bearbeitungszeitraum von', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Task[ToDate]', '', 'Bearbeitungszeitraum bis', new Calendar()), 4
                ),
            ))
        )));
    }

    /**
     * @param null $DivisionSubjectId
     * @param null $Test
     *
     * @return Stage|string
     */
    public function frontendTestSelected(
        $DivisionSubjectId = null,
        $Test = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Übersicht');

        $error = false;
        if ($DivisionSubjectId == null) {
            $error = true;
        } elseif (!Division::useService()->getDivisionSubjectById($DivisionSubjectId)) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Fach-Klasse nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        $this->contentTestSelected($DivisionSubjectId, $Test, $Stage, '/Education/Graduation/Evaluation/Test/Teacher');

        return $Stage;
    }

    /**
     * @param $DivisionSubjectId
     * @param $Test
     * @param Stage $Stage
     * @param $BasicRoute
     * @return string
     */
    private function contentTestSelected($DivisionSubjectId, $Test, Stage $Stage, $BasicRoute)
    {

        $Stage->addButton(new Standard('Zurück', $BasicRoute, new ChevronLeft()));

        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);
        $tblDivision = $tblDivisionSubject->getTblDivision();

        if (!$tblDivision) {
            return $Stage . new Danger('Klasse nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        //Integration
        $Accordion = new Accordion();
        $Listing = array();
        $HandyCapCount = 0;
        if(($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))){
            foreach($tblPersonList as $tblPerson){
                // Button's nur anzeigen, wenn Integrationen hinterlegt sind
                if(Student::useService()->getIsSupportByPerson($tblPerson)){
                    $HandyCapCount++;
                    $Listing[] = new Container(new PullClear($tblPerson->getLastFirstName()
                        .new PullRight((new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                            ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId())))));
                }
            }
        }
        if(!empty($Listing)){
            $Listing = new ListingLayout($Listing);
            $Accordion->addItem('Integration '.new Muted('('.$HandyCapCount.')'), $Listing);
        }


        if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject() && $tblDivision) {
            $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                $tblDivision,
                $tblDivisionSubject->getServiceTblSubject(),
                null,
                null,
                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
            );
        } else {
            $tblTestList = array();
        }

        // SSW-872 - Nachträgliche Aktualisierung Notenauftrag -> anlegen der Tests
        if (($tblTestTypeAppointedDateTask = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))
            && ($tblTaskListDivision = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestTypeAppointedDateTask))
        ) {
            foreach($tblTaskListDivision as $tblTaskItem) {
                if (!(Evaluation::useService()->existsTestByTask(
                    $tblTaskItem,
                    $tblDivision,
                    $tblDivisionSubject->getServiceTblSubject(),
                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                ))) {
                    $tblTestList[] = Evaluation::useService()->createTestToAppointedDateTask(
                        $tblTaskItem,
                        $tblDivisionSubject
                    );
                }
            }
        }

        $contentTable = array();
        $nowDateTime = new DateTime('now');
        if (!empty($tblTestList)) {
            if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Evaluation', 'AutoPublicationOfTestsAfterXDays'))
            ) {
                $days = intval($tblSetting->getValue());
            } else {
                $days = false;
            }
            if ($tblTestTypeAppointedDateTask) {
                $tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestTypeAppointedDateTask);
                if ($tblTaskList) {
                    $tblTaskList = $this->getSorter($tblTaskList)->sortObjectBy('Date', new DateTimeSorter(), Sorter::ORDER_DESC);
                }
            } else {
                $tblTaskList = false;
            }
            $behaviorTestList = array();
            foreach ($tblTestList as $tblTest) {
                $tblTask = $tblTest->getTblTask();
                $tblGradeType = $tblTest->getServiceTblGradeType();
                $isBehaviorTask = false;
                if ($tblTask) {
                    // noch nicht erreichte Notenaufträge für Lehrer ausblenden (-7 Tage)
                    if (strpos($BasicRoute, 'Teacher') !== false) {
                        $taskFromDate = new DateTime($tblTask->getFromDate());
                        if (($nowDateTime > $taskFromDate)
                           || ($nowDateTime < $taskFromDate
                                && $nowDateTime > ($taskFromDate->sub(new \DateInterval('P7D'))))
                        ) {

                        } else {
                            continue;
                        }
                    }

                    $stringDate = $tblTask->getDate();

                    if (($tblTask->getTblTestType()->getIdentifier() == 'BEHAVIOR_TASK')
                        && $tblGradeType
                    ) {
                        $isBehaviorTask = true;
                    }
                } else {
                    $stringDate = $tblTest->getDate();
                }

                if ($tblGradeType) {
                    if ($tblTask) {
                        $gradeType = new Bold('Kopfnote: ' . $tblTest->getServiceTblGradeType()->getName());
                    } else {
                        $gradeType = $tblGradeType->getDisplayName();
                    }
                } elseif ($tblTask) {
                    $gradeType = new Bold('Stichtagsnote');
                } else {
                    $gradeType = '';
                }

                $countGrades = 0;
                $countStudents = 0;
                $this->countGradesAndStudentsAll($tblTest, $countGrades, $countStudents);

                $grades = ($countGrades == $countStudents ? new Success($countGrades . ' von ' . $countStudents) :
                    new Warning($countGrades . ' von ' . $countStudents));

                // standard
                $stringReturnDate = $tblTest->getReturnDate();

//                // modify date if normal test where date is reached
                if ($tblTest->getReturnDate()
                    && new \DateTime($tblTest->getReturnDate()) <= new \DateTime()
                    && $tblTest->getTblTestType()->getIdentifier() == 'TEST') {
                    $stringReturnDate = new Success(new Bold($tblTest->getReturnDate()));
                }
                // modify with continues tests
                if ($tblTest->getFinishDate()) {
                    $stringDate = $tblTest->getFinishDate();
                    $stringReturnDate = ''; // $tblTest->getFinishDate();
                }

                // show autoReturnDate
                if ($stringReturnDate == ''
                    && $tblTest->getTblTestType()
                    && $tblTest->getTblTestType()->getIdentifier() == 'TEST'
                ) {
                    $autoReturnDateAppointedTask = false;
                    // durch Stichtagsnotenauftrag
                    $appointedDateTask = false;
                    if ($tblTaskList) {
                        /** @var TblTask $tblTaskItem */
                        foreach ($tblTaskList as $tblTaskItem) {
                            if (($date = $tblTaskItem->getDate())
                                && ($tblPeriod = $tblTest->getServiceTblPeriod())
                                && ($toDatePeriod = $tblPeriod->getToDate())
                                && ($dateTimeTask = new \DateTime($date))
                                && ($toDateTimePeriod = new \DateTime($toDatePeriod))
                                && $dateTimeTask < $toDateTimePeriod
                            ) {
                                $appointedDateTask = $tblTaskItem;
                                break;
                            }
                        }

                        if ($appointedDateTask) {
                            if ($tblTest->getDate()
                                && ($testDate = (new \DateTime($tblTest->getDate())))
                                && ($toDateTimeTask = new \DateTime($appointedDateTask->getToDate()))
                                && ($nowDateTime = (new \DateTime('now')))
                                && $testDate <= $toDateTimeTask
                                && $toDateTimeTask < $nowDateTime
                            ) {
                                $autoReturnDateAppointedTask = $toDateTimeTask->add(new \DateInterval('P1D'));
                            }
                        }
                    }

                    // nach X Tagen
                    $autoReturnDateDays = false;
                    if ($days
                        && $tblTest->getDate()
                    ) {
                        $testDate = (new \DateTime($tblTest->getDate()));
                        $autoReturnDateDays = $testDate->add(
                            new \DateInterval('P' . $days . 'D')
                        );
                    }

                    if ($autoReturnDateAppointedTask && $autoReturnDateDays) {
                        if ($autoReturnDateAppointedTask < $autoReturnDateDays) {
                            $autoReturnDate = $autoReturnDateAppointedTask;
                        } else {
                            $autoReturnDate = $autoReturnDateDays;
                        }
                    } elseif ($autoReturnDateAppointedTask) {
                        $autoReturnDate = $autoReturnDateAppointedTask;
                    } else {
                        $autoReturnDate = $autoReturnDateDays;
                    }

                    if ($autoReturnDate) {
                        $stringReturnDate = $autoReturnDate->format('d.m.Y');
                        $autoReturnDate = $autoReturnDate->format("Y-m-d");
                        $now = (new \DateTime('now'))->format("Y-m-d");
                        if ($autoReturnDate <= $now) {
                            $stringReturnDate = new Success(new Bold($stringReturnDate));
                        }
                    }
                }

                if ($isBehaviorTask) {
                    // SSW-420 für das Zusammenfassen der einzelnen Kopfnoten
                    $behaviorTestList[$tblTask->getId()][$tblGradeType->getName()] = array(
                        'Date' => $stringDate,
                        'Division' => $tblTest->getServiceTblDivision()
                            ? $tblTest->getServiceTblDivision()->getDisplayName() : '',
                        'Subject' => $tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getName() : '',
                        'DisplayPeriod' => $tblTask->getFromDate() . ' - ' . $tblTask->getToDate(),
                        'DisplayDescription' => $tblTask->getName(),
                        'TestId' => $tblTest->getId(),
                        'CountGrades' => $countGrades,
                        'CountStudents' => $countStudents
                    );
                } else {
                    $contentTable[] = array(
                        'Date' => $stringDate,
                        'Division' => $tblTest->getServiceTblDivision()
                            ? $tblTest->getServiceTblDivision()->getDisplayName() : '',
                        'Subject' => $tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getName() : '',
                        'DisplayPeriod' => $tblTask
                            ? $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()
                            : ($tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getDisplayName() : ''),
                        'GradeType' => $gradeType,
                        'DisplayDescription' => $tblTask ? $tblTask->getName() : $tblTest->getDescription(),
                        'CorrectionDate' => $tblTest->getCorrectionDate(),
                        'ReturnDate' => $stringReturnDate,
                        'Grades' => new Bold($grades),
                        'Option' => ($tblTest->getTblTestType()->getId() == Evaluation::useService()->getTestTypeByIdentifier('TEST')->getId()
                                ? (new Standard('', $BasicRoute . '/Edit', new Edit(),
                                    array('Id' => $tblTest->getId()), 'Bearbeiten'))
                                . (new Standard('', $BasicRoute . '/Destroy', new Remove(),
                                    array('Id' => $tblTest->getId()), 'Löschen'))
                                : '')
                            . (new Standard('', $BasicRoute . '/Grade/Edit', new Listing(),
                                array('Id' => $tblTest->getId()), 'Zensuren eintragen'))
                    );
                }
            }

            foreach ($behaviorTestList as $taskId => $behaviorTests) {
                ksort($behaviorTests);
                $countGrades = 0;
                $countStudents = 0;
                foreach ($behaviorTests as $item) {
                    $countGrades += $item['CountGrades'];
                    $countStudents += $item['CountStudents'];
                }

                $firstItem = reset($behaviorTests);
                if ($firstItem) {
                    $contentTable[] = array(
                        'Date' => $firstItem['Date'],
                        'Division' => $firstItem['Division'],
                        'Subject' => $firstItem['Subject'],
                        'DisplayPeriod' => $firstItem['DisplayPeriod'],
                        'GradeType' => new Bold('Kopfnote'),
                        'DisplayDescription' => $firstItem['DisplayDescription'],
                        'CorrectionDate' => '',
                        'ReturnDate' => '',
                        'Grades' => ($countGrades < $countStudents
                            ? new Warning($countGrades . ' von ' . $countStudents)
                            :new Success($countGrades . ' von ' . $countStudents)
                        ),
                        'Option' =>
                            new Standard('', $BasicRoute . '/Grade/Edit', new Listing(),
                                array('Id' => $firstItem['TestId']), 'Zensuren eintragen')
                    );
                }
            }
        }

        if ($tblDivision->getServiceTblYear()) {
            $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                $tblDivision,
                $tblDivisionSubject->getServiceTblSubject(),
                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
            );
            $isTeacher = (strpos($BasicRoute, 'Teacher') !== false);
            $Form = $this->formTest(
                $tblDivision,
                $tblDivision->getServiceTblYear(),
                $tblScoreRule ? $tblScoreRule : null,
                $isTeacher ? $tblDivisionSubject : null
            )
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->addButton(new Standard('Leistungsüberprüfung anlegen', '', new PlusSign(), array(),
                false, $Form->getHash()));
        } else {
            $Form = false;
        }

        // Vorschau Test
        $testArray = array();
        $testArrayTemp = array();
        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
            && ($tblTestAllByDivision = $tblTestAllByDivision = Evaluation::useService()->getTestAllByTestTypeAndDivision($tblTestType,
            $tblDivision))
        ) {
            $linkedDivisions = array();
            /** @var TblTest $testItem */
            foreach ($tblTestAllByDivision as $testItem) {
                $testArrayTemp[$testItem->getId()] = $testItem;
                if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                    && ($tblSubjectLinked = $testItem->getServiceTblSubject())
                    && $tblSubject->getId() == $tblSubjectLinked->getId()
                    && ($linkedTestList = $testItem->getLinkedTestAll())
                ) {
                    foreach ($linkedTestList as $linkedTest) {
                        if (($linkedDivision = $linkedTest->getServiceTblDivision())
                            && $linkedDivision->getId() != $tblDivision->getId()
                        ) {
                            if (!isset($linkedDivisions[$linkedDivision->getId()])) {
                                $linkedDivisions[$linkedDivision->getId()] = $linkedDivision;
                            }
                        }
                    }
                }
            }
            foreach ($linkedDivisions as $linkedDivisionItem) {
                if (($linkedTestListByLinkedDivision = Evaluation::useService()->getTestAllByTestTypeAndDivision($tblTestType, $linkedDivisionItem))) {
                    foreach ($linkedTestListByLinkedDivision as $tblTestItem) {
                        $testArrayTemp[$tblTestItem->getId()] = $tblTestItem;
                    }
                }
            }

            $testArrayTemp = $this->getSorter($testArrayTemp)->sortObjectBy('Date', new DateTimeSorter());

            $nowWeek = date('W');
            $nowYear = (new \DateTime('now'))->format('Y');
            /** @var TblTest $item */
            foreach ($testArrayTemp as $item) {
                if ($item->getDate()) {
                    $dateWeek = date('W', strtotime($item->getDate()));
                    $dateYear = (new \DateTime($item->getDate()))->format('Y');
                    if ($dateWeek !== false && (($dateYear == $nowYear && $dateWeek >= $nowWeek) || $dateYear > $nowYear)) {
                        $testArray[$dateWeek][$item->getId()] = $item;
                    }
                }
            }
        }

        $preview = Evaluation::useService()->getLayoutRowsForTestPlanning($testArray);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Fach-Klasse',
                                'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                                ($tblDivisionSubject->getServiceTblSubject() ? $tblDivisionSubject->getServiceTblSubject()->getName() : '') .
                                ($tblDivisionSubject->getTblSubjectGroup() ? new Small(
                                    ' (Gruppe: ' . $tblDivisionSubject->getTblSubjectGroup()->getName() . ')') : ''),
                                Panel::PANEL_TYPE_INFO
                            )
                        ))
                    ))
                )),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ApiSupportReadOnly::receiverOverViewModal()
                            .$Accordion
                        , 4)
                    )
                ),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($contentTable, null, array(
                                'Date'               => 'Datum',
                                'Division'           => 'Klasse',
                                'Subject'            => 'Fach',
                                'DisplayPeriod'      => 'Zeitraum',
                                'GradeType'          => 'Zensuren-Typ',
                                'DisplayDescription' => 'Thema',
                                'CorrectionDate'     => 'Korrekturdatum',
                                'ReturnDate'         => 'Bekanntgabedatum',
                                'Grades'             => 'Noten eingetragen',
                                'Option'             => ''
                            ), array(
                                'order' => array(
                                    array(0, 'desc')
                                ),
                                'columnDefs' => array(
                                    array('type' => 'de_date', 'targets' => 0),
                                    array('type' => 'de_date', 'targets' => 6),
                                    // Sortierung funktioniert bei Text-Style (farblicher Änderung, Fettmarkierung usw.) Benutzung nicht
                                    array('orderable' => false, 'targets' => 7),
                                    array('orderable' => false, 'targets' => 8),
                                    array('orderable' => false, 'targets' => 9),
                                ),
                                'responsive' => false,
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $tblDivisionSubject->getHasGrading()
                                ? $Form
                                    ? new Well(Evaluation::useService()->createTest($Form, $tblDivisionSubject->getId(),
                                    $Test, $BasicRoute))
                                    : new Danger('Schuljahr nicht gefunden', new Ban())
                                : new \SPHERE\Common\Frontend\Message\Repository\Warning('Es können keine Leistungsüberprüfungen angelegt werden, da für dieses 
                                Fach: wird nicht benotet, eingestellt ist.', new Exclamation())
                        ))
                    ))
                ), new Title(new PlusSign() . ' Leistungsüberprüfung anlegen')),
                $tblDivisionSubject->getHasGrading()
                    ? new LayoutGroup($preview, new Title(new Clock() . ' Planung'))
                    : null
            ))
        );

        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblYear $tblYear
     * @param TblScoreRule $tblScoreRule
     * @param TblDivisionSubject $tblDivisionSubjectSelected
     *
     * @return Form
     */
    private function formTest(
        TblDivision $tblDivision,
        TblYear $tblYear,
        TblScoreRule $tblScoreRule = null,
        TblDivisionSubject $tblDivisionSubjectSelected = null
    ) {

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        // nur Zensuren-Typen, welche bei der hinterlegten Berechnungsvorschrift hinterlegt sind
        if ($tblScoreRule) {
            // SSW-747 keine Zensuren-Typen bei dieser Berechnungsvorschrift verfügbar
            if (!($tblGradeTypeList = $tblScoreRule->getGradeTypesAll())) {
                $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
            }
        } else {
            $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
        }

        $tblLevel = $tblDivision->getTblLevel();
        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $tblLevel && $tblLevel->getName() == '12');

        // select current period
        $Global = $this->getGlobal();
        if (!$Global->POST && $tblPeriodList) {
            /** @var TblPeriod $tblPeriod */
            foreach ($tblPeriodList as $tblPeriod) {
                if ($tblPeriod->getFromDate() && $tblPeriod->getToDate()) {
                    $fromDate = (new \DateTime($tblPeriod->getFromDate()))->format("Y-m-d");
                    $toDate = (new \DateTime($tblPeriod->getToDate()))->format("Y-m-d");
                    $now = (new \DateTime('now'))->format("Y-m-d");
                    if ($fromDate <= $now && $now <= $toDate) {
                        $Global->POST['Test']['Period'] = $tblPeriod->getId();
                    }
                }
            }
            $Global->savePost();
        }

        $arrayFormRows = array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Test[Period]', 'Zeitraum', array('DisplayName' => $tblPeriodList)), 6
                ),
                new FormColumn(
                    new SelectBox('Test[GradeType]', 'Zensuren-Typ', array('DisplayName' => $tblGradeTypeList)), 6
                )
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Test[Description]', '', 'Thema'), 12
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new CheckBox('Test[IsContinues]', new Bold('fortlaufendes Datum '.
                        new ToolTip(new InfoIcon(), "Bei Tests mit 'fortlaufendes Datum' 
                        erfolgt die Freigabe für die Notenübersicht (Eltern, Schüler) automatisch, sobald das Datum der 
                        jeweiligen Note (Prio1) oder das optionale Enddatum (Prio2) erreicht ist.")
                        .'(z.B. für Mündliche Noten)'
                    ), 1,
                        array(
                            'Test[FinishDate]',
                            'Test[Date]',
                            'Test[CorrectionDate]',
                            'Test[ReturnDate]'
                        ))
                ),
                new FormColumn(
                    (new DatePicker('Test[FinishDate]', '', 'Enddatum (optional für Notendatum)', new Calendar()))->setDisabled(), 3
                ),
                new FormColumn(
                    new DatePicker('Test[Date]', '', 'Datum', new Calendar()), 3
                ),
                new FormColumn(
                    new DatePicker('Test[CorrectionDate]', '', 'Korrekturdatum', new Calendar()), 3
                ),
                new FormColumn(
                    new DatePicker('Test[ReturnDate]', '', 'Bekanntgabedatum für Notenübersicht (Eltern, Schüler)',
                        new Calendar()), 3
                ),
            ))
        );

        // Tests verknüpfen
        if ($tblDivisionSubjectSelected && ($panel = Evaluation::useService()->getTestLinkPanel($tblYear, $tblDivisionSubjectSelected))) {
            $arrayFormRows[] = new FormRow(new FormColumn(
                $panel
                ));
        }

        return new Form(new FormGroup($arrayFormRows));
    }

    /**
     * @param null $DivisionSubjectId
     * @param null $Test
     *
     * @return Stage|string
     */
    public function frontendHeadmasterTestSelected(
        $DivisionSubjectId = null,
        $Test = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Übersicht');

        $error = false;
        if ($DivisionSubjectId == null) {
            $error = true;
        } elseif (!Division::useService()->getDivisionSubjectById($DivisionSubjectId)) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Fach-Klasse nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Headmaster', Redirect::TIMEOUT_ERROR);
        }

        $this->contentTestSelected($DivisionSubjectId, $Test, $Stage,
            '/Education/Graduation/Evaluation/Test/Headmaster');

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Test
     *
     * @return Stage|string
     */
    public function frontendEditTest(
        $Id = null,
        $Test = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Bearbeiten');

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!Evaluation::useService()->getTestById($Id)) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Test nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        return $this->contentEditTest($Stage, $Id, $Test, '/Education/Graduation/Evaluation/Test/Teacher');
    }

    /**
     * @param Stage $Stage
     * @param        $Id
     * @param        $Test
     * @param string $BasicRoute
     *
     * @return string
     */
    private function contentEditTest(Stage $Stage, $Id, $Test, $BasicRoute)
    {

        $tblTest = Evaluation::useService()->getTestById($Id);
        if ($tblTest) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Test']['GradeType'] = ($tblGradeType = $tblTest->getServiceTblGradeType())
                    ? $tblGradeType->getId() : 0;
                $Global->POST['Test']['Description'] = $tblTest->getDescription();
                $Global->POST['Test']['Date'] = $tblTest->getDate();
                $Global->POST['Test']['CorrectionDate'] = $tblTest->getCorrectionDate();
                $Global->POST['Test']['ReturnDate'] = $tblTest->getReturnDate();
                $Global->POST['Test']['FinishDate'] = $tblTest->getFinishDate();
                $Global->savePost();
            }

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

            $Stage->addButton(
                new Standard('Zur&uuml;ck', $BasicRoute . '/Selected', new ChevronLeft()
                    , array('DivisionSubjectId' => $tblDivisionSubject->getId()))
            );

            // nur Zensuren-Typen, welche bei der hinterlegten Berechnungsvorschrift hinterlegt sind
            $tblScoreRule = false;
            $tblGradeTypeList = array();
            if (($tblDivision = $tblTest->getServiceTblDivision())) {
                $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                    $tblDivision,
                    $tblDivisionSubject->getServiceTblSubject(),
                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                );
            }
            if ($tblScoreRule) {
                $tblGradeTypeList = $tblScoreRule->getGradeTypesAll();
            } else {
                if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))) {
                    $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
                }
            }

            $Form = new Form(new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Test[GradeType]', 'Zensuren-Typ', array('DisplayName' => $tblGradeTypeList)), 12
                    ),
                    new FormColumn(
                        new TextField('Test[Description]', '', 'Thema'), 12
                    ),
                )),
                $tblTest->isContinues()
                    ? new FormRow(array(
                        new FormColumn(
                            new DatePicker('Test[FinishDate]', '', 'Enddatum (optional für Notendatum)', new Calendar()), 4
                        )
                    ))
                    : new FormRow(array(
                    new FormColumn(
                        new DatePicker('Test[Date]', '', 'Datum', new Calendar()), 4
                    ),
                    new FormColumn(
                        new DatePicker('Test[CorrectionDate]', '', 'Korrekturdatum', new Calendar()), 4
                    ),
                    new FormColumn(
                        new DatePicker('Test[ReturnDate]', '', 'Bekanntgabedatum für Notenübersicht (Eltern, Schüler)', new Calendar()), 4
                    ),
                ))
            )));
            $Form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $panel = false;
            if (($tblTestLinkList = $tblTest->getLinkedTestAll())) {
                $panelContent = array();
                $panelContent[] = 'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                    ($tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getAcronym() . ' '
                        . $tblTest->getServiceTblSubject()->getName() : '') .
                    ($tblTest->getServiceTblSubjectGroup() ? new Small(
                        ' (Gruppe: ' . $tblTest->getServiceTblSubjectGroup()->getName() . ')') : '');
                foreach ($tblTestLinkList as $tblTestItem) {
                    $division = $tblTestItem->getServiceTblDivision();
                    $subject = $tblTestItem->getServiceTblSubject();
                    $group = $tblTestItem->getServiceTblSubjectGroup();
                    if ($division && $subject) {
                        $panelContent[] = 'Klasse ' . $division->getDisplayName()
                            . ' - ' . $subject->getAcronym() . ' ' . $subject->getName()
                            . ($group ? new Small(
                                ' (Gruppe: ' . $group->getName() . ')') : '');
                    }
                }
                if (!empty($panelContent)) {
                    sort($panelContent);
                    $panel = new Panel(
                        'Verknüpfte Leistungsüberprüfungen',
                        $panelContent,
                        Panel::PANEL_TYPE_INFO
                    );
                }
            }
            if ($panel) {
                $Stage->setMessage(new WarningText(new Exclamation()
                    . ' Verknüpfte Leistungsüberprüfungen werden mit bearbeitet.'));
            }

            $Stage->setContent(
                new Layout (array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Fach-Klasse',
                                    'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
                                    ($tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getName() : '') .
                                    ($tblTest->getServiceTblSubjectGroup() ? new Small(
                                        ' (Gruppe: ' . $tblTest->getServiceTblSubjectGroup()->getName() . ')') : ''),
                                    Panel::PANEL_TYPE_INFO
                                ), 6
                            ),
                            new LayoutColumn(
                                new Panel($tblTest->getTblTask() ? 'Bearbeitungszeitraum:' : 'Zeitraum:',
                                    $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO), 3
                            ),
                            new LayoutColumn(
                                new Panel('Zensuren-Typ:',
                                    $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '',
                                    Panel::PANEL_TYPE_INFO), 3
                            )
                        )),
                        $panel
                            ? new LayoutRow(array(
                            new LayoutColumn(
                                $panel
                            ),
                        ))
                            : null,
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Evaluation::useService()->updateTest($Form, $tblTest->getId(), $Test,
                                    $BasicRoute))
                            )
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {

            return new Danger(new Ban() . ' Test nicht gefunden')
            . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     * @param $Test
     *
     * @return Stage|string
     */
    public function frontendHeadmasterEditTest(
        $Id = null,
        $Test = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Bearbeiten');

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblTest = Evaluation::useService()->getTestById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Test nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Headmaster', Redirect::TIMEOUT_ERROR);
        }

        return $this->contentEditTest($Stage, $Id, $Test, '/Education/Graduation/Evaluation/Test/Headmaster');
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyTest(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Löschen');

        if (!Evaluation::useService()->getTestById($Id)) {
            return $Stage . new Danger('Test nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        return $this->contentDestroyTest($Stage, $Id, $Confirm, '/Education/Graduation/Evaluation/Test/Teacher');
    }

    public function contentDestroyTest(Stage $Stage, $Id, $Confirm, $BasicRoute)
    {

        $tblTest = Evaluation::useService()->getTestById($Id);
        if ($tblTest) {
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

            $Stage->addButton(
                new Standard('Zur&uuml;ck', $BasicRoute . '/Selected', new ChevronLeft(),
                    array('DivisionSubjectId' => $tblDivisionSubject->getId()))
            );

            if (!$Confirm) {
                $panel = false;
                if (($tblTestLinkList = $tblTest->getLinkedTestAll())) {
                    $panelContent = array();
                    foreach ($tblTestLinkList as $tblTestItem) {
                        $division = $tblTestItem->getServiceTblDivision();
                        $subject = $tblTestItem->getServiceTblSubject();
                        $group = $tblTestItem->getServiceTblSubjectGroup();
                        if ($division && $subject) {
                            $panelContent[] = $division->getDisplayName() . ' - ' . $subject->getAcronym()
                                . ($group ? ' - ' . $group->getName() : '');
                        }
                    }
                    if (!empty($panelContent)) {
                        sort($panelContent);
                        $panel = new Panel(
                            new Exclamation() . ' Diese verknüpften Leistungsüberprüfungen werden ebenfalls gelöscht',
                            $panelContent,
                            Panel::PANEL_TYPE_DANGER
                        );
                    }
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Test', ($tblTest->getDescription() !== '' ? '&nbsp;&nbsp;'
                                . new Muted(new Small(new Small($tblTest->getDescription()))) : ''),
                                Panel::PANEL_TYPE_INFO),
                            $panel ? $panel : null,
                            new Panel(new Question() . ' Diesen Test wirklich löschen?', array(
                                $tblTest->getDescription() ? $tblTest->getDescription() : null
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', $BasicRoute . '/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                . new Standard(
                                    'Nein', $BasicRoute . '/Selected', new Disable(),
                                    array('DivisionSubjectId' => $tblDivisionSubject->getId())
                                )
                            )
                        )
                    ))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Evaluation::useService()->destroyTest($tblTest)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Der Test wurde gelöscht')
                                : new Danger(new Ban() . ' Der Test konnte nicht gelöscht werden')
                            ),
                            new Redirect($BasicRoute . '/Selected', Redirect::TIMEOUT_SUCCESS,
                                array('DivisionSubjectId' => $tblDivisionSubject->getId()))
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Test nicht gefunden.', new Ban())
            . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendHeadmasterDestroyTest(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Löschen');

        if (!Evaluation::useService()->getTestById($Id)) {
            return $Stage . new Danger('Test nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Headmaster', Redirect::TIMEOUT_ERROR);
        }

        return $this->contentDestroyTest($Stage, $Id, $Confirm, '/Education/Graduation/Evaluation/Test/Headmaster');
    }

    /**
     * @param $Id
     * @param $Grade
     *
     * @return Stage|string
     */
    public function frontendEditTestGrade(
        $Id = null,
        $Grade = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Zensuren eintragen');

        $tblTest = false;

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblTest = Evaluation::useService()->getTestById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Test nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        if (!$tblTest->getServiceTblDivision()) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
            . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        $isEdit = false;
        $Stage->setMessage(new Warning(new Exclamation() . ' Bei einer Notenänderung muss für diese ein Grund angegeben werden.'));

        $this->contentEditTestGrade($Stage, $tblTest, $Grade, '/Education/Graduation/Evaluation/Test/Teacher', $isEdit);

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param TblTest $tblTest
     * @param $Grade
     * @param $BasicRoute
     * @param bool|false $IsEdit
     *
     * @return string
     */
    private function contentEditTestGrade(Stage $Stage, TblTest $tblTest, $Grade, $BasicRoute, $IsEdit = false)
    {

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

        $Stage->addButton(
            new Standard('Zur&uuml;ck', $BasicRoute . '/Selected', new ChevronLeft()
                , array('DivisionSubjectId' => $tblDivisionSubject->getId()))
        );

        $buttonList = array();
        $tblNextTest = null;

        $isTestAppointedDateTask = ($tblTest->getTblTestType()->getId()
            == Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')->getId());
        $tblDivision = $tblTest->getServiceTblDivision();
        $tblSubject = $tblTest->getServiceTblSubject();

        $tblScoreRule = false;
        $scoreRuleText = array();
        $tblScoreType = false;
        $showCourse = false;
        if ($tblDivision && $tblSubject) {
            if ($tblTest->getTblTask() && $tblTest->getTblTask()->getServiceTblScoreType()) {
                $tblScoreType = $tblTest->getTblTask()->getServiceTblScoreType();
            } else {
                $tblScoreType = Gradebook::useService()->getScoreTypeByDivisionAndSubject(
                    $tblDivision, $tblSubject
                );
            }

            $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                $tblDivision,
                $tblSubject,
                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
            );
            if ($tblScoreRule) {
                $scoreRuleText[] = $tblScoreRule->getName();
                $tblScoreConditionsByRule = Gradebook::useService()->getScoreConditionsByRule($tblScoreRule);
                if ($tblScoreConditionsByRule) {

                } else {
                    $scoreRuleText[] = new Bold(new Warning(
                        new Ban() . ' Keine Berechnungsvariante hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                    ));
                }
            }

            if (($tblLevel = $tblDivision->getTblLevel())
                && ($tblType = $tblLevel->getServiceTblType())
                && $tblType->getName() == 'Mittelschule / Oberschule'
                && intval($tblLevel->getName()) > 6
            ) {
                $showCourse = true;
            }
        }

        $gradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
        if (($tblTestLinkedList = $tblTest->getLinkedTestAll())) {
            foreach ($tblTestLinkedList as $testItem) {
                $gradeListByTest = Gradebook::useService()->getGradeAllByTest($testItem);
                if ($gradeList && $gradeListByTest) {
                    $gradeList = array_merge($gradeList, $gradeListByTest);
                } elseif ($gradeListByTest) {
                    $gradeList = $gradeListByTest;
                }
            }
        }

        /*
         * set post
         */
        if ($gradeList && empty($Grade)) {
            $Global = $this->getGlobal();
            /** @var TblGrade $tblGrade */
            foreach ($gradeList as $tblGrade) {
                if ($tblGrade->getServiceTblPerson()) {
                    if ($tblGrade->getGrade() === null) {
                        $Global->POST['Grade'][$tblGrade->getServiceTblPerson()->getId()]['Attendance'] = 1;
                    } else {

                        $gradeValue = str_replace('.', ',', $tblGrade->getGrade());
                        $trend = $tblGrade->getTrend();
                        if ($trend !== null) {
                            if ($trend == TblGrade::VALUE_TREND_PLUS) {
                                $gradeValue .= '+';
                            } elseif ($trend == TblGrade::VALUE_TREND_MINUS) {
                                $gradeValue .= '-';
                            }
                        }
                        $Global->POST['Grade'][$tblGrade->getServiceTblPerson()->getId()]['Grade'] = $gradeValue;
                    }
                    $Global->POST['Grade'][$tblGrade->getServiceTblPerson()->getId()]['Comment'] = $tblGrade->getComment();
                    $Global->POST['Grade'][$tblGrade->getServiceTblPerson()->getId()]['PublicComment'] = $tblGrade->getPublicComment();
                    if ($tblTest->isContinues()) {
                        $Global->POST['Grade'][$tblGrade->getServiceTblPerson()->getId()]['Date'] = $tblGrade->getDate();
                    }
                    if ($tblGrade->getTblGradeText()) {
                        $Global->POST['Grade'][$tblGrade->getServiceTblPerson()->getId()]['Text']
                            = $tblGrade->getTblGradeText()->getId();
                    }
                }
            }
            $Global->savePost();
        }

        /*
         * set grade mirror
         */
        $gradeMirror = $this->setGradeMirror($tblScoreType ? $tblScoreType : null, $gradeList, $Grade);

        $studentList = array();
        $studentTestList = array();
        $errorRowList = array();
        $columnDefinition = array();

        $tblTask = $tblTest->getTblTask();
        $IsTaskAndInPeriod = true;
        if ($tblTask && !$tblTask->isInEditPeriod()) {
            $IsTaskAndInPeriod = false;
        }

        $hasPreviewGrades = false;

        $displayDivisionSubjectList = array();
        $displayDivisionSubjectList[] = 'Klasse ' . $tblDivision->getDisplayName() . ' - ' .
            ($tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getAcronym() . ' '
                . $tblTest->getServiceTblSubject()->getName() : '') .
            ($tblTest->getServiceTblSubjectGroup() ? new Small(
                ' (Gruppe: ' . $tblTest->getServiceTblSubjectGroup()->getName() . ')') : '');
        if ($tblTestLinkedList) {
            $studentList = $this->setStudentList($tblDivisionSubject, $tblTest, $studentList, $studentTestList, true);
            foreach ($tblTestLinkedList as $tblTestLinked) {
                if ($tblTestLinked->getServiceTblDivision()
                    && $tblTestLinked->getServiceTblSubject()
                    && ($testDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                        $tblTestLinked->getServiceTblDivision(),
                        $tblTestLinked->getServiceTblSubject(),
                        $tblTestLinked->getServiceTblSubjectGroup() ? $tblTestLinked->getServiceTblSubjectGroup() : null
                    ))
                ) {
                    $studentList = $this->setStudentList($testDivisionSubject, $tblTestLinked, $studentList,
                        $studentTestList, true);
                    $displayDivisionSubjectList[$tblTestLinked->getServiceTblDivision()->getDisplayName()
                    . $tblTestLinked->getServiceTblSubject()->getAcronym()]
                        = 'Klasse ' . $tblTestLinked->getServiceTblDivision()->getDisplayName()
                        . ' - ' . $tblTestLinked->getServiceTblSubject()->getAcronym() . ' '
                        . $tblTestLinked->getServiceTblSubject()->getName()
                        . ($tblTestLinked->getServiceTblSubjectGroup()
                            ? new Small(' (Gruppe: ' . $tblTestLinked->getServiceTblSubjectGroup()->getName() . ')')
                            : '');
                }
            }
            sort($displayDivisionSubjectList);
        } else {
            $studentList = $this->setStudentList($tblDivisionSubject, $tblTest, $studentList, $studentTestList);
        }

        if ($tblTask) {
            $period = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
            $tableColumns = array(
                'Number' => '#',
                'Name'   => 'Schüler',
                'Integration' => 'Integration'
            );
            if ($showCourse) {
                $tableColumns['Course'] = 'Bildungsgang';
            }

            // Stichtagsnotenauftrag
            if ($isTestAppointedDateTask) {
                $tblPeriodByDivision = $tblTask->getServiceTblPeriodByDivision($tblDivision);
                $gradeType = 'Stichtagsnote' . ($tblPeriodByDivision
                        ? new Small(new Muted(' ' . $tblPeriodByDivision->getDisplayName()))
                        : new Small(new Muted(' Gesamtes Schuljahr')));

                $dataList = array();
                $periodListCount = array();
                $columnDefinition['Number'] = '#';
                $columnDefinition['Student'] = "Schüler";
                $columnDefinition['Integration'] = "Integration";
                if ($showCourse) {
                    $columnDefinition['Course'] = 'Bildungsgang';
                }

                $tblPeriodList = false;
                $tblLevel = $tblDivision->getTblLevel();
                // ist Stichtagsnotenauftrag auf eine Periode beschränkt oder wird das gesamte Schuljahr genutzt
                if ($tblPeriodByDivision) {
                    $tblPeriodList[] = $tblPeriodByDivision;
                } elseif ($tblTask->getServiceTblYear()) {
                    $tblPeriodList = Term::useService()->getPeriodAllByYear($tblTask->getServiceTblYear(), $tblLevel && $tblLevel->getName() == '12');
                } else {
                    // alte Daten wo noch kein Schuljahr ausgewählt werde musste bei der Erstellung des Stichtagsnotenauftrags
                    $tblYearAll = Term::useService()->getYearAllByDate(DateTime::createFromFormat('d.m.Y',
                        $tblTask->getDate()));
                    if ($tblYearAll) {
                        foreach ($tblYearAll as $tblYear) {
                            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                        }
                    }
                }

                // Vornoten für Schüler die in einer anderen Klasse deaktiviert sind
                if ($tblDivisionSubject->getTblSubjectGroup()) {
                    $tblStudentList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
                } else {
                    $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                }
                if ($tblStudentList) {
                    $gradeListFromAnotherDivision = Gradebook::useService()->getGradesFromAnotherDivision($tblDivision,
                        $tblSubject, $tblStudentList);
                } else {
                    $gradeListFromAnotherDivision = false;
                }

                // Tabellenkopf mit Test-Code und Datum erstellen
                if ($tblPeriodList) {
                    /** @var TblPeriod $tblPeriod */
                    foreach ($tblPeriodList as $tblPeriod) {
                        $count = 0;
                        if ($gradeListFromAnotherDivision && isset($gradeListFromAnotherDivision[$tblPeriod->getId()])) {
                            $count++;
                            $columnDefinition['ExtraGrades' . $tblPeriod->getId()] = 'Vornoten';
                        }

                        if ($tblDivisionSubject->getServiceTblSubject()) {
                            $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                $tblPeriod,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            if ($tblTestList) {

                                $tblTestList = Evaluation::useService()->sortTestList($tblTestList);

                                /** @var TblTest $tblTestTemp */
                                foreach ($tblTestList as $tblTestTemp) {
                                    if ($tblTestTemp->getServiceTblGradeType()) {
                                        if ($tblTask->getDate() && $tblTestTemp->getDate()) {
                                            $taskDate = new \DateTime($tblTask->getDate());
                                            $testDate = new \DateTime($tblTestTemp->getDate());
                                            // Tests nur vom vor dem Stichtag
                                            if ($taskDate->format('Y-m-d') >= $testDate->format('Y-m-d')) {
                                                $count++;
                                                $date = $tblTestTemp->getDate();
                                                if (strlen($date) > 6) {
                                                    $date = substr($date, 0, 6);
                                                }
                                                $text = new Small(new Muted($date)) . '<br>'
                                                    . ($tblTestTemp->getServiceTblGradeType()->isHighlighted()
                                                        ? $tblTestTemp->getServiceTblGradeType()->getCode()
                                                        : new Muted($tblTestTemp->getServiceTblGradeType()->getCode()));
                                                $columnDefinition['Test' . $tblTestTemp->getId()]
                                                    = $tblTestTemp->getDescription()
                                                        ? new ToolTip($text, $tblTestTemp->getDescription())
                                                        : $text;
                                            }
                                        } elseif ($tblTestTemp->isContinues()) {
                                            $count++;
                                            $text = new Small('&nbsp;') . '<br>'
                                                . ($tblTestTemp->getServiceTblGradeType()->isHighlighted()
                                                    ? $tblTestTemp->getServiceTblGradeType()->getCode()
                                                    : new Muted($tblTestTemp->getServiceTblGradeType()->getCode()));
                                            $columnDefinition['Test' . $tblTestTemp->getId()]
                                                = $tblTestTemp->getDescription()
                                                    ? new ToolTip($text, $tblTestTemp->getDescription())
                                                    : $text;
                                        }
                                    }
                                }
                                // SSW-259
                                if ($count == 0) {
                                    $periodListCount[$tblPeriod->getId()] = 1;
                                    $columnDefinition['Period' . $tblPeriod->getId()] = "";
                                }
                                $periodListCount[$tblPeriod->getId()] = $count;
                            } else {
                                $count++;
                                $periodListCount[$tblPeriod->getId()] = $count;
                                $columnDefinition['Period' . $tblPeriod->getId()] = "";
                            }
                        }
                    }
                    $columnDefinition['YearAverage'] = '&#216;';
                }

                // Tabellen-Inhalt erstellen
                if ($tblStudentList) {

                    $count = 1;
                    // Ermittlung der Zensuren zu den Schülern
                    foreach ($tblStudentList as $tblPerson) {
                        $data = array();
                        $data['Number'] = $count % 5 == 0 ? new Bold($count) : $count;
                        $count++;
                        $data['Student'] = $tblPerson->getLastFirstName();

                        if(Student::useService()->getIsSupportByPerson($tblPerson)) {
                            $Integration = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                                ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                        } else {
                            $Integration = '';
                        }
                        $data['Integration'] = $Integration;

                        $data['Course'] = '';
                        $tblCourse = Student::useService()->getCourseByPerson($tblPerson);
                        if ($tblCourse) {
                            $data['Course'] = $tblCourse->getName();
                        }

                        // Zensur des Schülers zum Test zuordnen und Durchschnitte berechnen
                        if (!empty($columnDefinition)) {
                            foreach ($columnDefinition as $column => $value) {
                                if (strpos($column, 'Test') !== false) {
                                    $testId = substr($column, strlen('Test'));
                                    $tblTestTemp = Evaluation::useService()->getTestById($testId);
                                    if ($tblTestTemp) {
                                        $data[$column] = '';
                                        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTestTemp,
                                            $tblPerson);
                                        if ($tblGrade) {
                                            if (!$tblTestTemp->isContinues()    // Testdatum
                                                || ($tblTestTemp->isContinues()   // Notendatum
                                                    && $tblGrade->getDate() && $tblTask->getDate()
                                                    && ($taskDate = new \DateTime($tblTask->getDate()))
                                                    && ($gradeDate = new \DateTime($tblGrade->getDate()))
                                                    && ($taskDate->format('Y-m-d') >= $gradeDate->format('Y-m-d')))
                                                || ($tblTestTemp->isContinues() && !$tblGrade->getDate() // Test-Enddatum
                                                    && $tblTestTemp->getFinishDate() && $tblTask->getDate()
                                                    && ($taskDate = new \DateTime($tblTask->getDate()))
                                                    && ($finishDate = new \DateTime($tblTestTemp->getFinishDate()))
                                                    && ($taskDate->format('Y-m-d') >= $finishDate->format('Y-m-d')))
                                            ) {
                                                if ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '') {
                                                    $value = ' ' . $tblGrade->getDisplayGrade();
                                                } else {
                                                    $value = '&nbsp;';
                                                }
                                                $data[$column] = $tblTestTemp->getServiceTblGradeType()
                                                    ? ($tblTestTemp->getServiceTblGradeType()->isHighlighted()
                                                        ? new Bold($value) : $value)
                                                    : $value;
                                            }
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
                                            $tblPerson, $tblDivision, $tblDivisionSubject->getServiceTblSubject(),
                                            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                            $tblScoreRule ? $tblScoreRule : null, $tblPeriod,
                                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                            $tblTask->getDate() ? $tblTask->getDate() : false,
                                            $gradeListFromAnotherDivision
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
                                    }
                                } elseif (strpos($column, 'YearAverage') !== false) {

                                    /*
                                    * Calc Average
                                    */
                                    $average = Gradebook::useService()->calcStudentGrade(
                                        $tblPerson, $tblDivision, $tblDivisionSubject->getServiceTblSubject(),
                                        Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                        $tblScoreRule ? $tblScoreRule : null,
                                        ($tblTaskPeriod = $tblTask->getServiceTblPeriodByDivision($tblDivision)) ? $tblTaskPeriod : null,
                                        $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                        $tblTask->getDate() ? $tblTask->getDate() : false, $gradeListFromAnotherDivision
                                    );
                                    if (is_array($average)) {
                                        $errorRowList = $average;
                                        $average = '';
                                    } else {
                                        $posStart = strpos($average, '(');
                                        if ($posStart !== false) {
                                            $average = substr($average, 0, $posStart);
                                        }

                                        // Zensuren voreintragen bei Stichtagsnotenauftrag, wenn noch keine vergeben ist
                                        if (($average || $average === (float) 0) && !Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                                $tblPerson)
                                        ) {
                                            $hasPreviewGrades = true;
                                            $Global = $this->getGlobal();
                                            $Global->POST['Grade'][$tblPerson->getId()]['Grade'] =
                                                str_replace('.', ',', round($average, 0));
                                            $Global->savePost();
                                        }
                                    }
                                    $data[$column] = new Bold($average);
                                } elseif (strpos($column, 'Period') !== false) {
                                    // keine Tests in der Periode vorhanden
                                    $data[$column] = '';
                                    // Vornoten
                                } elseif (strpos($column, 'ExtraGrades') !== false) {
                                    $periodId = str_replace('ExtraGrades', '', $column);
                                    if ($gradeListFromAnotherDivision && isset($gradeListFromAnotherDivision[$periodId][$tblPerson->getId()])) {
                                        $data[$column] = implode(', ', $gradeListFromAnotherDivision[$periodId][$tblPerson->getId()])
                                            . '&nbsp;'
                                            . ApiGradebook::receiverModal()
                                            . (new Standard('', '#', new EyeOpen()))->ajaxPipelineOnClick(ApiGradebook::pipelineOpenExtraGradesModal(
                                                $tblDivision->getId(), $tblSubject->getId(), $periodId, $tblPerson->getId()
                                            ));
                                    }
                                }
                            }
                        }

                        $dataList[$tblPerson->getId()] = $data;
                    }
                }

                $studentList = $dataList;

                $columnDefinition['Grade'] = 'Zensur';
                $columnDefinition['Text'] = 'oder Zeugnistext'
                    . ApiEvaluation::receiverModal()
                    . new PullRight(
                        (new Standard('Alle bearbeiten', ApiEvaluation::getEndpoint()))
                            ->ajaxPipelineOnClick(ApiEvaluation::pipelineOpenGradeTextModal($tblTest->getId()))
                        );
                $columnDefinition['Comment'] = 'Vermerk Notenänderung';
            } else {
                // Kopfnotenauftrag
                $gradeType = $this->setContentForBehaviorTask(
                    $tblTest,
                    $tblTask ? $tblTask : null,
                    $tblDivision,
                    $tblSubject,
                    $tblNextTest,
                    $BasicRoute,
                    $studentList,
                    $tableColumns,
                    $buttonList);
            }
        } else {
            $period = $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getDisplayName() : '';
            $gradeType = ($tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '')
                . ($tblTest->getFinishDate() ? ' (Enddatum: ' . $tblTest->getFinishDate() . ')': '');

            $tableColumns = array();
            $tableColumns['Number'] = '#';
            $tableColumns['Name'] = 'Schüler';
            $tableColumns['Integration'] = 'Integration';
            if ($showCourse) {
                $tableColumns['Course'] = 'Bildungsgang';
            }

            $tableColumns['Grade'] = 'Zensur';
            if ($tblTest->isContinues()) {
                $tableColumns['Date'] = 'Datum' . ($tblTest->getFinishDate() ? ' (' . $tblTest->getFinishDate() . ')' : '');
            }
            $tableColumns['Comment'] = 'Vermerk Notenänderung';
            $tableColumns['Attendance'] = 'Nicht teilgenommen';
            $tableColumns['PublicComment'] = 'Öffentlicher Kommentar';
        }

        if ($studentList) {
            $tabIndex = 1;
            $selectList = array();
            if ($tblScoreType) {
                $selectList[-1] = '';
                if ($tblScoreType->getIdentifier() == 'GRADES') {
                    for ($i = 1; $i < 6; $i++) {
                        $selectList[$i . '+'] = (string)($i . '+');
                        $selectList[$i] = (string)($i);
                        $selectList[$i . '-'] = (string)($i . '-');
                    }
                    $selectList[6] = 6;
                } elseif ($tblScoreType->getIdentifier() == 'GRADES_BEHAVIOR_TASK') {
                    for ($i = 1; $i < 5; $i++) {
                        $selectList[$i . '+'] = (string)($i . '+');
                        $selectList[$i] = (string)($i);
                        $selectList[$i . '-'] = (string)($i . '-');
                    }
                    $selectList[5] = 5;
                } elseif ($tblScoreType->getIdentifier() == 'POINTS') {
                    for ($i = 0; $i < 16; $i++) {
                        $selectList[$i] = (string)$i;
                    }
                }
            }
            foreach ($studentList as $personId => $value) {
                $tblPerson = Person::useService()->getPersonById($personId);
                if ($studentTestList && isset($studentTestList[$personId])) {
                    $tblTestOfPerson = $studentTestList[$personId];
                } else {
                    $tblTestOfPerson = $tblTest;
                }
                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTestOfPerson,
                    $tblPerson);
                $studentList = $this->contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $studentList,
                    $tabIndex, $selectList, $IsTaskAndInPeriod, $tblScoreType ? $tblScoreType : null, $tblTest
                );
            }
        }

        if ($isTestAppointedDateTask) {
            $tableData = new TableData(
                $studentList, null, $columnDefinition,
                array(
                    "paging"         => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                    "searching"      => false, // Deaktivieren Suchen
                    "info"           => false,  // Deaktivieren Such-Info
                    "responsive"   => false,
                    'order'      => array(
                        array('0', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('orderable' => false, 'targets' => '_all'),
                    ),
                )
            );

            // oberste Tabellen-Kopf-Zeile erstellen
            $headTableColumnList = array();
            $headTableColumnList[] = new TableColumn('', $showCourse ? 4 : 3, '20%');
            $countHeaderColumns = 2;
            if (!empty($periodListCount)) {
                foreach ($periodListCount as $periodId => $count) {
                    $tblPeriod = Term::useService()->getPeriodById($periodId);
                    if ($tblPeriod) {
                        $headTableColumnList[] = new TableColumn($tblPeriod->getDisplayName(), $count);
                        $countHeaderColumns += $count;
                    }
                }
                $countLastTab = count($columnDefinition) - $countHeaderColumns;
                $headTableColumnList[] = new TableColumn('', $countLastTab > 0 ? $countLastTab : 1);
            }
            $tableData->prependHead(
                new TableHead(
                    new TableRow(
                        $headTableColumnList
                    )
                )
            );
        } else {
            $tableData = new TableData($studentList, null, $tableColumns, array(
                "paging"         => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching"      => false, // Deaktivieren Suchen
                "info"           => false,  // Deaktivieren Such-Info
                "sorting"        => false,  // Deaktivieren Such-Info
                "responsive"   => false,
                'order'      => array(
                    array('0', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0),
                    array('orderable' => false, 'targets' => '_all'),
                ),
            ));
        }

        /*
         * Content
         */
        $serviceForm = Gradebook::useService()->updateGradeToTest(
            new Form(
                new FormGroup(array(
                    new FormRow(
                        new FormColumn(
                            $tableData
                        )
                    ),
                ))
                , new Primary('Speichern', new Save()))
            , $tblTest->getId(), $Grade, $BasicRoute, $tblScoreType ? $tblScoreType : null,
            empty($studentTestList) ? null : $studentTestList,
            $tblNextTest ? $tblNextTest : null
        );
        $warningNoScoreType = new WarningMessage('Kein Bewertungssystem hinterlegt.
                                Zensuren können erst vergeben werden nachdem für diese Fach-Klasse ein Bewertungssystem
                                hinterlegt wurde.', new Ban());
        if ($tblTask) {
            if ($tblTask->isBeforeEditPeriod()) {
                $content = new WarningMessage(
                        'Zensuren können erst ab erreichen des Bearbeitungszeitraums vergeben werden.',
                        new Exclamation()
                    )
                    . ($tblScoreType ? '' : $warningNoScoreType);
            } elseif ($tblTask->isAfterEditPeriod()) {
                if ($IsEdit) {
                    $content = $tblScoreType ? $serviceForm : $warningNoScoreType;
                } else {
                    $content = new WarningMessage(
                            'Zensuren können von Ihnen nur innerhalb des Bearbeitungszeitraums vergeben werden. Zur Nachträglichen Bearbeitung der Zensuren
                             wenden Sie sich bitte an die Schulleitung.',
                            new Exclamation()
                        )
                        . ($tblScoreType ? '' : $warningNoScoreType)
                        . $tableData;
                }
            } else {
                $content = $tblScoreType ? $serviceForm : $warningNoScoreType;
            }

        } else {
            $content = $tblScoreType ? $serviceForm : $warningNoScoreType;
        }

        $Stage->setContent(
            ApiSupportReadOnly::receiverOverViewModal()
            .new Layout (array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Fach-Klasse',
                                $displayDivisionSubjectList,
                                Panel::PANEL_TYPE_INFO
                            ), 3
                        ),
                        new LayoutColumn(
                            new Panel($tblTest->getTblTask() ? 'Bearbeitungszeitraum' : 'Zeitraum',
                                $period,
                                Panel::PANEL_TYPE_INFO), 3
                        ),
                        new LayoutColumn(
                            new Panel('Zensuren-Typ',
                                $gradeType,
                                Panel::PANEL_TYPE_INFO), 3
                        ),
                        new LayoutColumn(new Panel(
                            'Berechnungsvorschrift',
                            $tblScoreRule ? $scoreRuleText : new Bold(new Warning(
                                new Ban() . ' Keine Berechnungsvorschrift hinterlegt. Alle Zensuren-Typen sind gleichwertig.'
                            )),
                            Panel::PANEL_TYPE_INFO
                        ), 3),
                        new LayoutColumn(
                            new Panel(
                                'Beschreibung',
                                $tblTest->getTblTask() ? $tblTest->getTblTask()->getName() : $tblTest->getDescription(),
                                Panel::PANEL_TYPE_INFO
                            ), 12
                        ),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Notenspiegel',
                                $gradeMirror,
                                Panel::PANEL_TYPE_PRIMARY
                            )
                        )
                    )),
                    ($hasPreviewGrades
                        ? new LayoutRow(new LayoutColumn(new WarningMessage(
                            'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                        )))
                        : null
                    )
                )),
                (!empty($errorRowList) ? new LayoutGroup($errorRowList) : null),
                (!empty($buttonList) && !$tblTask->isBeforeEditPeriod() ? new LayoutGroup(new LayoutRow(new LayoutColumn($buttonList))) : null),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $content
                        )
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $tblGrade
     * @param $IsEdit
     * @param $student
     * @param $tabIndex
     * @param $selectList
     * @param bool $IsTaskAndInPeriod
     * @param TblScoreType|null $tblScoreType
     * @param TblTest $tblTest
     * @return array
     */
    private function contentEditTestGradeTableRow(
        TblPerson $tblPerson,
        $tblGrade,
        $IsEdit,
        $student,
        &$tabIndex,
        $selectList,
        $IsTaskAndInPeriod = false,
        TblScoreType $tblScoreType = null,
        TblTest $tblTest = null
    ) {

        if ($tblScoreType === null) {
            $tblScoreType = false;
        }

        /** @var TblGrade $tblGrade */
        if (!$IsEdit && !$IsTaskAndInPeriod) {
            /** @var TblGrade $tblGrade */
            $student[$tblPerson->getId()]['Grade'] = $tblGrade ? $tblGrade->getDisplayGrade() : '';
            $student[$tblPerson->getId()]['Comment'] = $tblGrade ? $tblGrade->getComment() : '';
            $student[$tblPerson->getId()]['Text'] = ($tblGrade ? ($tblGrade->getTblGradeText() ? $tblGrade->getTblGradeText()->getName() : '') : '');
        } else {
            if ($tblScoreType) {
                if ($tblScoreType->getIdentifier() == 'VERBAL') {
                    $student[$tblPerson->getId()]['Grade']
                        = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '', '',
                        new Quote()))->setTabIndex($tabIndex++);
                } elseif ($tblScoreType->getIdentifier() == 'GRADES_V1'
                    || $tblScoreType->getIdentifier() == 'GRADES_COMMA'
                ) {
                    $student[$tblPerson->getId()]['Grade']
                        = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '',
                        ''))->setTabIndex($tabIndex++);
                } else {
//                    $selectBox = (new SelectBox('Grade[' . $tblPerson->getId() . '][Grade]', '', $selectList));
//                    $selectBox->setTabIndex($tabIndex++);
//                    $selectBox->configureLibrary( SelectBox::LIBRARY_SELECT2 );
                    $selectComplete = (new SelectCompleter('Grade[' . $tblPerson->getId() . '][Grade]', '', '', $selectList))
                        ->setTabIndex($tabIndex++);

                    $student[$tblPerson->getId()]['Grade']
                        = $selectComplete;
                }
            } else {
                $student[$tblPerson->getId()]['Grade']
                    = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '', '',
                    new Quote()))->setTabIndex($tabIndex++);
            }

            if ($tblTest && $tblTest->isContinues()) {
                $student[$tblPerson->getId()]['Date']
                    = (new DatePicker('Grade[' . $tblPerson->getId() . '][Date]', '', '', null, array('widgetPositioning' => array('vertical' => 'bottom')) ))->setTabIndex($tabIndex++);
            }

            $student[$tblPerson->getId()]['Comment']
                = (new TextField('Grade[' . $tblPerson->getId() . '][Comment]', '', '',
                new Comment()))->setTabIndex(1000 + $tabIndex)->setPrefixValue($tblGrade ? $tblGrade->getDisplayTeacher() : '');
            $student[$tblPerson->getId()]['Attendance'] =
                (new CheckBox('Grade[' . $tblPerson->getId() . '][Attendance]', ' ', 1))->setTabIndex(2000 + $tabIndex);

            // Zeugnistext
            if (($tblTask = $tblTest->getTblTask()) && $tblTask->getTblTestType()
                && $tblTask->getTblTestType()->getIdentifier() == 'APPOINTED_DATE_TASK'
            ) {
                $gradeTextId = 0;
                if ($tblGrade && ($tblGradeText = $tblGrade->getTblGradeText())) {
                    $gradeTextId = $tblGradeText->getId();
                }
                $student[$tblPerson->getId()]['Text'] = ApiEvaluation::receiverContent(
                    $this->getGradeTextSelectBox($tblPerson->getId(), $gradeTextId), 'ChangeGradeText_' . $tblPerson->getId()
                );
            }

            // öffentlicher Kommentar für die Elternansicht
            if (!$tblTask) {
                $student[$tblPerson->getId()]['PublicComment'] = (new TextField('Grade[' . $tblPerson->getId() . '][PublicComment]',
                    'z.B.: für Betrugsversuch', '', new Comment()))->setTabIndex(1000 + $tabIndex);
            }
        }

        return $student;
    }

    /**
     * @param $personId
     * @param $gradeTextId
     *
     * @return SelectBox
     */
    public function getGradeTextSelectBox($personId, $gradeTextId)
    {
        $global = $this->getGlobal();
        $global->POST['Grade'][$personId]['Text'] = $gradeTextId;
        $global->savePost();

        return new SelectBox(
            'Grade[' . $personId . '][Text]', '', array(TblGradeText::ATTR_NAME => Gradebook::useService()->getGradeTextAll())
        );
    }

    /**
     * @param $Id
     * @param $Grade
     *
     * @return Stage|string
     */
    public function frontendHeadmasterEditTestGrade(
        $Id = null,
        $Grade = null
    ) {

        $Stage = new Stage('Leistungsüberprüfung', 'Zensuren eintragen');

        $tblTest = false;

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblTest = Evaluation::useService()->getTestById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Test nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Test/Headmaster', Redirect::TIMEOUT_ERROR);
        }

        $this->contentEditTestGrade($Stage, $tblTest, $Grade, '/Education/Graduation/Evaluation/Test/Headmaster',
            true);

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Task
     * @param null $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendHeadmasterTaskEdit($Id = null, $Task = null, $IsAllYears = null)
    {

        $Stage = new Stage('Notenauftrag', 'Bearbeiten');

        $tblTask = false;

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblTask = Evaluation::useService()->getTaskById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Notenauftrag nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Task/Headmaster', new ChevronLeft(), array(
                'IsAllYears' => $IsAllYears
            ))
        );

        $Global = $this->getGlobal();
        if (!$Global->POST) {
            $Global->POST['Task']['Type'] = $tblTask->getTblTestType()->getId();
            $Global->POST['Task']['Name'] = $tblTask->getName();
            $Global->POST['Task']['Date'] = $tblTask->getDate();
            $Global->POST['Task']['FromDate'] = $tblTask->getFromDate();
            $Global->POST['Task']['ToDate'] = $tblTask->getToDate();
            $Global->POST['Task']['Period'] = $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : 0;
            $Global->POST['Task']['ScoreType'] = $tblTask->getServiceTblScoreType() ? $tblTask->getServiceTblScoreType() : 0;
            $Global->savePost();
        }

        $Form = $this->formTask();
        $Form
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                $tblTask->getTblTestType()->getName(),
                                $tblTask->getName() . ' ' . $tblTask->getDate()
                                . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                    $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                Panel::PANEL_TYPE_INFO
                            )
                        )
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(Evaluation::useService()->updateTask($Form, $tblTask->getId(), $Task))
                        )
                    ))
                ), new Title(new Edit() . ' Bearbeiten')),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     * @param null $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendHeadmasterTaskDivision($Id = null, $Data = null, $IsAllYears = null)
    {

        $Stage = new Stage('Notenauftrag', 'Klassen zuordnen');
        $Stage->setMessage(new Bold(new Exclamation() . ' Hinweis: ') . 'Bei der Auswahl vieler Klassen kann das Speichern einige Zeit dauern.');

        $tblTask = false;

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblTask = Evaluation::useService()->getTaskById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Notenauftrag nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_ERROR, array(
                    'IsAllYears' => $IsAllYears
                ));
        }

        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Task/Headmaster', new ChevronLeft(), array(
                'IsAllYears' => $IsAllYears
            ))
        );


        if ($tblTask->getTblTestType()->getIdentifier() == 'BEHAVIOR_TASK') {
            $isBehaviorTask = true;
        } else {
            $isBehaviorTask = false;
        }

        $hasEdit = false;
        $nowDate = (new \DateTime('now'))->format("Y-m-d");
        $toDate = $tblTask->getToDate();
        if ($toDate) {
            $toDate = new \DateTime($toDate);
            $toDate = $toDate->format('Y-m-d');
        }
        if ($nowDate && $toDate) {
            if ($nowDate < $toDate) {
                $hasEdit = true;
            }
        }

        $tblTestAllByTest = Evaluation::useService()->getTestAllByTask($tblTask);
        $tblGradeTypeList = $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR')
        );
        if ($tblTestAllByTest) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                foreach ($tblTestAllByTest as $tblTest) {
                    $tblDivision = $tblTest->getServiceTblDivision();
                    if ($tblDivision) {
                        $Global->POST['Data']['Division'][$tblDivision->getId()] = 1;
                    }
                    if ($isBehaviorTask) {
                        $tblGradeType = $tblTest->getServiceTblGradeType();
                        if ($tblGradeType) {
                            $Global->POST['Data']['GradeType'][$tblGradeType->getId()] = 1;
                        }
                    }
                }
                $Global->savePost();
            }
        } elseif ($tblGradeTypeList) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                foreach ($tblGradeTypeList as $tblGradeTypeItem) {
                    $Global->POST['Data']['GradeType'][$tblGradeTypeItem->getId()] = 1;
                }
                $Global->savePost();
            }
        }

        $schoolTypeList = array();
        if ($tblTask->getServiceTblYear()) {
            $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblTask->getServiceTblYear());
            if ($tblDivisionAllByYear) {
                foreach ($tblDivisionAllByYear as $tblDivision) {
                    $type = $tblDivision->getTblLevel()->getServiceTblType();
                    $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                    if ($type && $tblDivisionSubjectList) {
                        $schoolTypeList[$type->getId()][$tblDivision->getId()] = $tblDivision->getDisplayName();
                    }
                }
            }
        } else {
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblYear);
                    if ($tblDivisionAllByYear) {
                        foreach ($tblDivisionAllByYear as $tblDivision) {
                            $type = $tblDivision->getTblLevel()->getServiceTblType();
                            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                            if ($type && $tblDivisionSubjectList) {
                                $schoolTypeList[$type->getId()][$tblDivision->getId()] = $tblDivision->getDisplayName();
                            }
                        }
                    }
                }
            }
        }

        $isLocked = $tblTask->isLocked();

        $gradeTypeColumnList = array();
        if ($isBehaviorTask) {
            if ($tblGradeTypeList) {
                foreach ($tblGradeTypeList as $tblGradeType) {
                    if ($hasEdit && !$isLocked) {
                        $gradeTypeColumnList[] = new FormColumn(
                            new CheckBox('Data[GradeType][' . $tblGradeType->getId() . ']', $tblGradeType->getName(),
                                1), 1
                        );
                    } else {
                        $gradeTypeColumnList[] = new FormColumn(
                            (new CheckBox('Data[GradeType][' . $tblGradeType->getId() . ']', $tblGradeType->getName(),
                                1))->setDisabled(), 1
                        );
                    }
                }
            }
        }

        $columnList = array();
        if (!empty($schoolTypeList)) {
            foreach ($schoolTypeList as $typeId => $divisionList) {
                $type = Type::useService()->getTypeById($typeId);
                if ($type && is_array($divisionList)) {

                    asort($divisionList, SORT_NATURAL);

                    $checkBoxList = array();
                    foreach ($divisionList as $key => $value) {
                        if ($hasEdit && !$isLocked) {
                            $checkBoxList[] = new CheckBox('Data[Division][' . $key . ']', $value, 1);
                        } else {
                            $checkBoxList[] = (new CheckBox('Data[Division][' . $key . ']', $value, 1))->setDisabled();
                        }
                    }

                    $panel = new Panel($type->getName(), $checkBoxList, Panel::PANEL_TYPE_DEFAULT);
                    $columnList[] = new FormColumn($panel, 3);
                }
            }
        }
        $columnList[] = new FormColumn(new HiddenField('Data[IsSubmit]'));

        $form = new Form(array(
            $isBehaviorTask
                ? new FormGroup(array(
                new FormRow(
                    $gradeTypeColumnList
                )
            ),
                new \SPHERE\Common\Frontend\Form\Repository\Title('Kopfnoten')
            )
                : null,
            new FormGroup(
                new FormRow(
                    $columnList
                )
                , new \SPHERE\Common\Frontend\Form\Repository\Title('<br> Klassen'))
        ));
        if ($hasEdit && !$isLocked) {
            $form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                $tblTask->getTblTestType()->getName(),
                                $tblTask->getName() . ' ' . $tblTask->getDate()
                                . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                    $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                Panel::PANEL_TYPE_INFO
                            )
                        ),
                        ($isLocked ?
                            new LayoutColumn(
                                new WarningMessage('Es wurden bereits Zensuren zum Notenauftrag vergeben. Klassen können nicht mehr zu diesem Notenauftrag
                                hinzugefügt oder von diesem Notenauftrag entfernt werden.', new Exclamation())
                            ) : null),
                        (!$hasEdit ?
                            new LayoutColumn(
                                new WarningMessage('Der Bearbeitungszeitraum ist abgelaufen. Klassen können nicht mehr zu diesem Notenauftrag
                                hinzugefügt oder von diesem Notenauftrag entfernt werden.', new Exclamation())
                            ) : null),
                    ))
                )),
            ))
            . new Well(Evaluation::useService()->updateDivisionTasks($form, $tblTask->getId(), $Data))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     * @param null $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendHeadmasterTaskGrades($Id = null, $DivisionId = null, $IsAllYears = null)
    {
        $Stage = new Stage('Notenauftrag', 'Zensurenübersicht');

        $tblTask = false;

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblTask = Evaluation::useService()->getTaskById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Notenauftrag nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_ERROR, array(
                    'IsAllYears' => $IsAllYears
                ));
        }

        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Task/Headmaster',
                new ChevronLeft(), array('IsAllYears' => $IsAllYears)
            )
        );

        $tblCurrentDivision = Division::useService()->getDivisionById($DivisionId);

        $tblDivisionAllByTask = Evaluation::useService()->getDivisionAllByTask($tblTask);
        $buttonList = array();
        if ($tblDivisionAllByTask) {
            $tblDivisionAllByTask = $this->getSorter($tblDivisionAllByTask)->sortObjectBy('DisplayName');

            if (!$tblCurrentDivision) {
                $tblCurrentDivision = current($tblDivisionAllByTask);
            }

            if (count($tblDivisionAllByTask) > 1) {
                /** @var TblDivision $tblDivision */
                foreach ($tblDivisionAllByTask as $tblDivision) {
                    if ($tblCurrentDivision && $tblCurrentDivision->getId() == $tblDivision->getId()) {
                        $buttonList[] = new Standard(
                            new Info(new Bold('Klasse ' . $tblDivision->getDisplayName())),
                            '/Education/Graduation/Evaluation/Task/Headmaster/Grades',
                            new Edit(),
                            array(
                                'Id' => $Id,
                                'DivisionId' => $tblDivision->getId(),
                                'IsAllYears' => $IsAllYears
                            )
                        );
                    } else {
                        $buttonList[] = new Standard(
                            'Klasse ' . $tblDivision->getDisplayName(),
                            '/Education/Graduation/Evaluation/Task/Headmaster/Grades',
                            null,
                            array(
                                'Id' => $Id,
                                'DivisionId' => $tblDivision->getId(),
                                'IsAllYears' => $IsAllYears
                            )
                        );
                    }
                }
            }
        }

        $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask,
            $tblCurrentDivision ? $tblCurrentDivision : null);

        $divisionList = array();
        if ($tblTestAllByTask) {
            foreach ($tblTestAllByTask as $tblTest) {
                $tblDivision = $tblTest->getServiceTblDivision();
                if ($tblDivision) {
                    $divisionList[$tblDivision->getId()][$tblTest->getId()] = $tblTest;
                }
            }
        }

        $tableList = array();
        $studentList = array();
        $tableHeaderList = array();
        if (!empty($divisionList)) {

            $tableList = $this->setGradeOverviewForTask($tblTask, $divisionList, $tableHeaderList, $studentList,
                $tableList);
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                $tblTask->getTblTestType()->getName(),
                                $tblTask->getName() . ' ' . $tblTask->getDate()
                                . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                    'Bearbeitungszeitraum '.$tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                Panel::PANEL_TYPE_INFO
                            ),
                            $tblDivisionAllByTask ? null : new WarningMessage(
                                'Es sind keine Klassen zu diesem Notenauftrag zugeordnet.', new Exclamation()
                            )
                        )),
                        new LayoutColumn(
                            empty($buttonList) ? null : $buttonList
                        )
                    ))
                )),
            ))
            . new Layout($tableList)
        );

        return $Stage;
    }

    /**
     * @param TblTask $tblTask
     * @param         $divisionList
     * @param         $tableHeaderList
     * @param         $studentList
     * @param         $tableList
     * @return LayoutGroup[]
     */
    private function setGradeOverviewForTask(
        TblTask $tblTask,
        $divisionList,
        $tableHeaderList,
        $studentList,
        $tableList
    ) {

        $gradeList = array();
        $taskDate = new DateTime($tblTask->getDate());
        foreach ($divisionList as $divisionId => $testList) {
            $tblDivision = Division::useService()->getDivisionById($divisionId);
            if ($tblDivision) {
                if (($tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision, true))) {
                    $count = 1;
                    foreach ($tblDivisionStudentAll as $tblPerson) {
                        if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                            continue;
                        }

                        $studentList[$tblDivision->getId()][$tblPerson->getId()]['Number'] = $count++;
                        $studentList[$tblDivision->getId()][$tblPerson->getId()]['Name'] =
                            $tblPerson->getLastFirstName();
                        $studentList[$tblDivision->getId()][$tblPerson->getId()]['Average'] = '';
                    }
                }

                // Stichtagsnote
                if ($tblTask->getTblTestType()->getId() == Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')) {
                    if (!empty($testList)) {
                        /** @var TblTest $tblTest */
                        foreach ($testList as $tblTest) {
                            $tblSubject = $tblTest->getServiceTblSubject();
                            if ($tblSubject && $tblTest->getServiceTblDivision()) {
                                $tableHeaderList[$tblDivision->getId()]['Subject' . $tblSubject->getId()] = $tblSubject->getAcronym();

                                $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                    $tblTest->getServiceTblDivision(),
                                    $tblSubject,
                                    $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                                );

                                if ($tblDivisionSubject && $tblDivisionSubject->getTblSubjectGroup()) {
                                    $tblSubjectStudentAllByDivisionSubject =
                                        Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                    if ($tblSubjectStudentAllByDivisionSubject) {
                                        foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                            $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                            if ($tblPerson) {
                                                if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                                                    continue;
                                                }

                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                    $tblTest, $tblSubject, $tblPerson, $studentList,
                                                    $tblDivisionSubject->getTblSubjectGroup()
                                                        ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                    $gradeList
                                                );
                                            }
                                        }
                                    }
                                } else {
                                    if ($tblDivisionStudentAll) {
                                        foreach ($tblDivisionStudentAll as $tblPerson) {
                                            if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                                                continue;
                                            }

                                            $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                $tblTest, $tblSubject, $tblPerson, $studentList, null, $gradeList);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Sortierung nach Fächer-Acroynm
                    if (!empty($tableHeaderList[$tblDivision->getId()])) {
                        asort($tableHeaderList[$tblDivision->getId()]);
                    }
                    $prependTableHeaderList[$tblDivision->getId()]['Number'] = '#';
                    $prependTableHeaderList[$tblDivision->getId()]['Name'] = 'Schüler';
                    $tableHeaderList[$tblDivision->getId()] = $prependTableHeaderList[$tblDivision->getId()]
                        + $tableHeaderList[$tblDivision->getId()];

                    // Bug Schüler ist nicht in der Gruppe, wenn nicht alle Schüler in einer Gruppe sind, z.B. bei Ethik
                    if (!empty($studentList)) {
                        $count = 1;
                        foreach ($studentList as $divisionListId => $students) {
                            if (is_array($students)) {
                                foreach ($students as $studentId => $student) {
                                    foreach ($tableHeaderList[$divisionListId] as $key => $value) {
                                        if ($key == 'Number') {
                                            $studentList[$divisionId][$studentId][$key] = $count++;
                                        } elseif (!isset($student[$key])) {
                                            $studentList[$divisionId][$studentId][$key] = "";
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Gesamtdurchschnitt
                    $tableHeaderList[$tblDivision->getId()]['Average'] = '&#216;';
                    if (!empty($gradeList)) {
                        foreach ($gradeList as $personId => $gradeArray) {
                            $sum = 0;
                            foreach ($gradeArray as $grade) {
                                $sum += $grade;
                            }
                            $count = count($gradeArray);
                            $studentList[$divisionId][$personId]['Average'] = $count  > 0
                                ? round($sum / $count, 2 ) : '';
                        }
                    }

                } else {

                    if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation',
                        'ShowProposalBehaviorGrade'))
                    ) {
                        $showProposalBehaviorGrade = $tblSetting->getValue();
                    } else {
                        $showProposalBehaviorGrade = false;
                    }

                    // Kopfnoten
                    $tableHeaderList[$tblDivision->getId()]['Number'] = '#';
                    $tableHeaderList[$tblDivision->getId()]['Name'] = 'Schüler';
                    $grades = array();

                    if (!empty($testList)) {
                        /** @var TblTest $tblTest */
                        foreach ($testList as $tblTest) {
                            $tblGradeType = $tblTest->getServiceTblGradeType();
                            if ($tblGradeType && $tblTest->getServiceTblDivision() && $tblTest->getServiceTblSubject()) {

                                $tableHeaderList[$tblDivision->getId()]['Type' . $tblGradeType->getId()]
                                    = $tblGradeType->getCode() . ' (' . $tblGradeType->getName() . ')';

                                $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                    $tblTest->getServiceTblDivision(),
                                    $tblTest->getServiceTblSubject(),
                                    $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                                );

                                if ($tblDivisionSubject) {
                                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                                        $tblSubjectStudentAllByDivisionSubject =
                                            Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                        if ($tblSubjectStudentAllByDivisionSubject) {
                                            foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                                $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                                if ($tblPerson) {
                                                    if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                                                        continue;
                                                    }

                                                    list($studentList, $grades) = $this->setTableContentForBehaviourTask($tblDivision,
                                                        $tblTest, $tblPerson, $studentList, $grades);
                                                }
                                            }
                                        }
                                    } else {
                                        if ($tblDivisionStudentAll) {
                                            foreach ($tblDivisionStudentAll as $tblPerson) {
                                                if ($this->checkIsPersonInActive($tblDivision, $tblPerson, $taskDate)) {
                                                    continue;
                                                }

                                                list($studentList, $grades) = $this->setTableContentForBehaviourTask($tblDivision,
                                                    $tblTest, $tblPerson, $studentList, $grades);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // calc Average
                        if (isset($studentList[$tblDivision->getId()])) {
                            foreach ($studentList[$tblDivision->getId()] as $personId => $studentListByDivision) {
                                $tblPerson = Person::useService()->getPersonById($personId);
                                $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
                                $tblGradeTypeAllWhereBehavior = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
                                if ($tblPerson && $tblGradeTypeAllWhereBehavior) {
                                    foreach ($tblGradeTypeAllWhereBehavior as $tblGradeType) {
                                        $gradeTypeId = $tblGradeType->getId();
                                        // Kopfnotenvorschlag KL
                                        if ($showProposalBehaviorGrade) {
                                            $proposalGrade = new Warning(new Bold('f'));
                                            if (($tblProposalBehaviorGrade = Gradebook::useService()->getProposalBehaviorGrade(
                                                $tblDivision, $tblTask, $tblGradeType, $tblPerson
                                                )) && $tblProposalBehaviorGrade->getDisplayGrade() !== ''
                                            ) {
                                                $proposalGrade = new Bold($tblProposalBehaviorGrade->getDisplayGrade());
                                            }

                                            if (isset($studentListByDivision['Type' . $gradeTypeId])) {
                                                $studentListByDivision['Type' . $gradeTypeId] .= new Small(' | (KL-Vorschlag: ' . $proposalGrade . ')');
                                            }
                                        }
                                        if (isset($grades[$personId][$gradeTypeId]) && $grades[$personId][$gradeTypeId]['Count'] > 0) {
                                            $studentList[$tblDivision->getId()][$personId]['Type' . $gradeTypeId] =
                                                new Bold('&#216; ' .
                                                    round(floatval($grades[$personId][$gradeTypeId]['Sum']) / floatval($grades[$personId][$gradeTypeId]['Count']),
                                                        2) . ' | ') . $studentListByDivision['Type' . $gradeTypeId];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($tableHeaderList)) {
            foreach ($tableHeaderList as $divisionId => $tableHeader) {
                $tblDivision = Division::useService()->getDivisionById($divisionId);
                if ($tblDivision) {
                    $tableList[] =
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(array(
                                    new Title('Klasse', $tblDivision->getDisplayName()),
                                    new TableData(
                                        isset($studentList[$tblDivision->getId()]) ? $studentList[$tblDivision->getId()] : array(),
                                        null,
                                        $tableHeader,
                                        array(
                                            "paging"         => false, // Deaktivieren Blättern
                                            "iDisplayLength" => -1,    // Alle Einträge zeigen
                                            "searching"      => false, // Deaktivieren Suchen
                                            "info"           => false,  // Deaktivieren Such-Info
                                            "responsive"   => false
                                        ),
                                        array(
                                            'columnDefs' => array(
                                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            ),
                                        )
                                    )
                                ))
                            )
                        );
                }
            }
            return $tableList;
        }
        return $tableList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblSubject $tblSubject
     * @param TblPerson $tblPerson
     * @param $studentList
     * @param TblSubjectGroup $tblSubjectGroup
     * @param array $gradeList
     *
     * @return  $studentList
     */
    private function setTableContentForAppointedDateTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        $studentList,
        TblSubjectGroup $tblSubjectGroup = null,
        &$gradeList = array()
    ) {

        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        $tblTask = $tblTest->getTblTask();

        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
            $tblDivision,
            $tblSubject,
            $tblSubjectGroup
        );

        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson, $tblDivision, $tblSubject, Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblScoreRule ? $tblScoreRule : null,
            ($tblTaskPeriod = $tblTask->getServiceTblPeriodByDivision($tblDivision)) ? $tblTaskPeriod : null, null,
            $tblTask->getDate() ? $tblTask->getDate() : false, true
        );
        if (is_array($average)) {
//            $errorRowList = $average;
            $average = ' ';
        } else {
            $posStart = strpos($average, '(');
            if ($posStart !== false) {
                $average = substr($average, 0, $posStart);
            }
        }

        if ($tblGrade) {
            // Zeugnistext
            if (($tblGradeText = $tblGrade->getTblGradeText())) {
                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                ['Subject' . $tblSubject->getId()] = $tblGradeText->getName();

                return $studentList;
            }

            $gradeValue = $tblGrade->getGrade();
            $trend = $tblGrade->getTrend();

            if ($gradeValue !== null && $gradeValue !== '') {
                $gradeList[$tblPerson->getId()][] = $gradeValue;
            }

            $isGradeInRange = true;
            if ($average !== '' && $average !== null && $gradeValue !== null) {
                if (is_numeric($gradeValue)) {
                    $gradeFloat = floatval($gradeValue);
                    if (($gradeFloat - 0.5) <= $average && ($gradeFloat + 0.5) >= $average) {
                        $isGradeInRange = true;
                    } else {
                        $isGradeInRange = false;
                    }
                }
            }

            if (TblGrade::VALUE_TREND_PLUS === $trend) {
                $gradeValue .= '+';
            } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                $gradeValue .= '-';
            }

            if ($isGradeInRange) {
                $gradeValue = new Success($gradeValue);
            } else {
                $gradeValue = new \SPHERE\Common\Frontend\Text\Repository\Danger($gradeValue);
            }

            $gradeValue = new Bold($gradeValue);

            $studentList[$tblDivision->getId()][$tblPerson->getId()]
            ['Subject' . $tblSubject->getId()] = ($tblGrade->getGrade() !== null ?
                    $gradeValue : '') . (($average || $average === (float)0) ? new Muted(new Small('&nbsp;&nbsp; &#216;' . $average)) : '');
            return $studentList;
        } else {
            $studentList[$tblDivision->getId()][$tblPerson->getId()]
            ['Subject' . $tblSubject->getId()] =
                new Warning(new Bold('fehlt'))
                . (($average || $average === (float)0) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param $studentList
     * @param $grades
     *
     * @return array
     */
    private function setTableContentForBehaviourTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblPerson $tblPerson,
        $studentList,
        $grades
    ) {

        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        if ($tblTest->getServiceTblGradeType() && $tblTest->getServiceTblSubject()) {
            $gradeTypeId = $tblTest->getServiceTblGradeType()->getId();
            $tblSubject = $tblTest->getServiceTblSubject();
            if ($tblGrade) {
                $gradeText = $tblSubject->getAcronym() . ': ' . ($tblGrade->getGrade() !== null ?
                        $tblGrade->getDisplayGrade() : '');
                if (is_numeric($tblGrade->getGrade())) {
                    if (isset($grades[$tblPerson->getId()][$gradeTypeId]['Count'])) {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Count']++;
                    } else {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Count'] = 1;
                    }
                    if (isset($grades[$tblPerson->getId()][$gradeTypeId]['Sum'])) {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Sum']
                            = floatval($grades[$tblPerson->getId()][$gradeTypeId]['Sum']) + floatval($tblGrade->getGrade());
                    } else {
                        $grades[$tblPerson->getId()][$gradeTypeId]['Sum'] = floatval($tblGrade->getGrade());
                    }
                }
            } else {
                $gradeText = $tblSubject->getAcronym() . ': ' .
                    new Warning(new Bold('f'));
            }

            if (!isset($studentList[$tblDivision->getId()][$tblPerson->getId()]['Type' . $gradeTypeId])) {
                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                ['Type' . $gradeTypeId] = new Small(new Small($gradeText));
                return array($studentList, $grades);
            } else {
                $studentList[$tblDivision->getId()][$tblPerson->getId()]
                ['Type' . $gradeTypeId] .= new Small(new Small(' | ' . $gradeText));
                return array($studentList, $grades);
            }
        }
        return array($studentList, $grades);
    }

    /**
     * @param null $Id
     * @param null $DivisionId
     * @param null $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendDivisionTeacherTaskGrades($Id = null, $DivisionId = null, $IsAllYears = null)
    {

        $Stage = new Stage('Notenauftrag', 'Zensurenübersicht');

        $tblTask = false;

        $error = false;
        if ($Id == null) {
            $error = true;
        } elseif (!($tblTask = Evaluation::useService()->getTaskById($Id))) {
            $error = true;
        }
        if ($error) {
            return $Stage . new Danger('Notenauftrag nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Task/Teacher', Redirect::TIMEOUT_ERROR, array(
                    'IsAllYears' => $IsAllYears
                ));
        }

        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Task/Teacher',
                new ChevronLeft(), array('IsAllYears' => $IsAllYears)
            )
        );

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }
        $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);

        $tblCurrentDivision = Division::useService()->getDivisionById($DivisionId);

        $tblDivisionAllByTask = Evaluation::useService()->getDivisionAllByTask($tblTask);
        $buttonList = array();
        if ($tblDivisionAllByTask) {
            $tblDivisionAllByTask = $this->getSorter($tblDivisionAllByTask)->sortObjectBy('DisplayName');

            $tempList = array();
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionAllByTask as $tblDivision) {
                if (Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson)) {
                    $tempList[] = $tblDivision;
                }
            }
            $tblDivisionAllByTask = empty($tempList) ? false : $tempList;

            if (!$tblCurrentDivision && $tblDivisionAllByTask) {
                $tblCurrentDivision = current($tblDivisionAllByTask);
            }

            if (count($tblDivisionAllByTask) > 1) {
                /** @var TblDivision $tblDivision */
                foreach ($tblDivisionAllByTask as $tblDivision) {
                    if ($tblCurrentDivision && $tblCurrentDivision->getId() == $tblDivision->getId()) {
                        $buttonList[] = new Standard(
                            new Info(new Bold('Klasse ' . $tblDivision->getDisplayName())),
                            '/Education/Graduation/Evaluation/Task/Teacher/Grades',
                            new Edit(),
                            array(
                                'Id' => $Id,
                                'DivisionId' => $tblDivision->getId()
                            )
                        );
                    } else {
                        $buttonList[] = new Standard(
                            'Klasse ' . $tblDivision->getDisplayName(),
                            '/Education/Graduation/Evaluation/Task/Teacher/Grades',
                            null,
                            array(
                                'Id' => $Id,
                                'DivisionId' => $tblDivision->getId()
                            )
                        );
                    }
                }
            }
        }

        $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask,
            $tblCurrentDivision ? $tblCurrentDivision : null);

        $divisionList = array();
        if ($tblTestAllByTask) {
            foreach ($tblTestAllByTask as $tblTest) {
                $tblDivision = $tblTest->getServiceTblDivision();
                if ($tblDivision) {
                    if ($tblDivisionTeacherAllByTeacher) {
                        foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                            if ($tblDivisionTeacher->getTblDivision() && $tblDivision->getId() == $tblDivisionTeacher->getTblDivision()->getId()) {
                                $divisionList[$tblDivision->getId()][$tblTest->getId()] = $tblTest;
                            }
                        }
                    }
                }
            }
        }

        $tableList = array();
        $studentList = array();
        $tableHeaderList = array();
        if (!empty($divisionList)) {

            $tableList = $this->setGradeOverviewForTask($tblTask, $divisionList, $tableHeaderList, $studentList,
                $tableList);
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                $tblTask->getTblTestType()->getName(),
                                $tblTask->getName() . ' ' . $tblTask->getDate()
                                . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                    'Bearbeitungszeitraum '.$tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                Panel::PANEL_TYPE_INFO
                            )
                        ),
                        new LayoutColumn(
                            empty($buttonList) ? null : $buttonList
                        )
                    ))
                )),
            ))
            . new Layout($tableList)
        );

        return $Stage;
    }


    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblTest $tblTest
     * @param array $studentList
     * @param array $studentTestList
     * @param bool $isDivisionSubjectNamed
     * @return array
     */
    private function setStudentList(
        TblDivisionSubject $tblDivisionSubject,
        TblTest $tblTest,
        $studentList,
        &$studentTestList,
        $isDivisionSubjectNamed = false
    ) {
        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $tblStudentAll = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject, true);
        } else {
            $tblStudentAll = Division::useService()->getStudentAllByDivision($tblDivisionSubject->getTblDivision(), true);
        }
        if ($tblStudentAll
            && ($tblDivision = $tblDivisionSubject->getTblDivision())
        ) {
            if ($tblTest->getDate()) {
                $testDate = new DateTime($tblTest->getDate());
            } elseif ($tblTest->getFinishDate()) {
                $testDate = new DateTime($tblTest->getFinishDate());
            } else {
                $testDate = false;
            }

            /** @var TblPerson $tblPerson */
            foreach ($tblStudentAll as $tblPerson) {
                $isInActive = false;
                // inaktive Schüler abhängig vom Austrittsdatum ignorieren
                if (($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson(
                        $tblDivision, $tblPerson
                    ))
                    && ($leaveDate = $tblDivisionStudent->getLeaveDateTime()) !== null
                ) {
                    $isInActive = $tblDivisionStudent->isInActive();
                    if ($testDate && $testDate > $leaveDate) {
                        continue;
                    }
                }

                $studentTestList[$tblPerson->getId()] = $tblTest;
                $count = count($studentList) + 1;
                $name = $tblPerson->getLastFirstName() . ($isDivisionSubjectNamed
                        ? new Muted(' (' . $tblDivisionSubject->getTblDivision()->getDisplayName()
                            . ' - ' . $tblDivisionSubject->getServiceTblSubject()->getAcronym()
                            . ($tblDivisionSubject->getTblSubjectGroup() ? ' - ' . $tblDivisionSubject->getTblSubjectGroup()->getName() : '')
                            . ')')
                        : ''
                    );
                $studentList[$tblPerson->getId()]['Number'] = $count;
                $studentList[$tblPerson->getId()]['Name'] = $isInActive ? new Strikethrough($name) : $name;
                if(Student::useService()->getIsSupportByPerson($tblPerson)) {
                    $Integration = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                        ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                } else {
                    $Integration = '';
                }
                $studentList[$tblPerson->getId()]['Integration'] = $Integration;
                if (($tblCourse = Student::useService()->getCourseByPerson($tblPerson))) {
                    $course = $tblCourse->getName();
                } else {
                    $course = '';
                }
                $studentList[$tblPerson->getId()]['Course'] = $isInActive ? new Strikethrough($course) : $course;
            }
        }

        if (($tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest))) {
            foreach ($tblGradeList as $tblGrade) {
                if (($tblPerson = $tblGrade->getServiceTblPerson()) && !isset($studentList[$tblPerson->getId()])) {
                    $studentTestList[$tblPerson->getId()] = $tblTest;
                    $studentList[$tblPerson->getId()]['Number'] = count($studentList) + 1;
                    $studentList[$tblPerson->getId()]['Name'] = new Muted($tblPerson->getLastFirstName()) . ($isDivisionSubjectNamed
                            ? new Muted(' (' . $tblDivisionSubject->getTblDivision()->getDisplayName()
                                . ' - ' . $tblDivisionSubject->getServiceTblSubject()->getAcronym()
                                . ($tblDivisionSubject->getTblSubjectGroup() ? ' - ' . $tblDivisionSubject->getTblSubjectGroup()->getName() : '')
                                . ')')
                            : ''
                        );
                }
            }
        }

        return $studentList;
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     * @param null $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendHeadmasterTaskDestroy(
        $Id = null,
        $Confirm = false,
        $IsAllYears = null
    ) {

        $Stage = new Stage('Notenauftrag', 'Löschen');

        if (!Evaluation::useService()->getTaskById($Id)) {
            return $Stage . new Danger('Notenauftrag nicht gefunden nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_ERROR, array(
                    'IsAllYears' => $IsAllYears
                ));
        }

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Evaluation/Task/Headmaster', new ChevronLeft(), array(
                    'IsAllYears' => $IsAllYears
                ))
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                                new Panel(
                                    $tblTask->getTblTestType()->getName(),
                                    $tblTask->getName() . ' ' . $tblTask->getDate()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Panel(new Question() . ' Diesen Notenauftrag wirklich löschen?', null,
                                    Panel::PANEL_TYPE_DANGER,
                                    new Standard(
                                        'Ja', '/Education/Graduation/Evaluation/Task/Headmaster/Destroy', new Ok(),
                                        array('Id' => $Id, 'Confirm' => true, 'IsAllYears' => $IsAllYears)
                                    )
                                    . new Standard(
                                        'Nein', '/Education/Graduation/Evaluation/Task/Headmaster', new Disable(), array(
                                        'IsAllYears' => $IsAllYears
                                    ))
                                )
                            )
                        )
                    )))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Evaluation::useService()->destroyTask($tblTask)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' Der Notenauftrag wurde gelöscht')
                                : new Danger(new Ban() . ' Der Notenauftrag konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_SUCCESS, array(
                                'IsAllYears' => $IsAllYears
                            ))
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Notenauftrag nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_ERROR, array(
                    'IsAllYears' => $IsAllYears
                ));
        }

        return $Stage;
    }

    /**
     * @param TblScoreType|null $tblScoreType
     * @param $gradeList
     * @param $Grade
     *
     * @return array|Bold
     */
    private function setGradeMirror(TblScoreType $tblScoreType = null, $gradeList, $Grade)
    {
        $minRange = null;
        $maxRange = null;
        if ($tblScoreType) {
            $gradeMirror = array();
            $gradeMirror[] = new Bold('Bewertungssystem: ' . $tblScoreType->getName());
            if ($tblScoreType->getIdentifier() == 'VERBAL') {
                $gradeMirror[] = new Bold(new Warning(new Exclamation() . ' Für die verbale Bewertung ist kein Notenspiegel verfügbar.'));
            } else {
                $mirror = array();
                $count = 0;
                $sum = 0;

                $description = '';
                if ($tblScoreType->getIdentifier() == 'GRADES' || $tblScoreType->getIdentifier() == 'GRADES_COMMA') {
                    $minRange = 1;
                    $maxRange = 6;
                    $description = 'Note ';
                } elseif ($tblScoreType->getIdentifier() == 'POINTS') {
                    $minRange = 0;
                    $maxRange = 15;
                    $description = 'Punkte ';
                } elseif ($tblScoreType->getIdentifier() == 'GRADES_V1'
                    || $tblScoreType->getIdentifier() == 'GRADES_BEHAVIOR_TASK') {
                    $minRange = 1;
                    $maxRange = 5;
                    $description = 'Note ';
                }

                for ($i = $minRange; $i <= $maxRange; $i++) {
                    $mirror[$i] = 0;
                }

                if ($gradeList) {
                    /** @var TblGrade $tblGrade */
                    foreach ($gradeList as $tblGrade) {
                        if (empty($Grade)) {
                            if (is_numeric($tblGrade->getGrade())) {
                                $gradeValue = intval(round(floatval($tblGrade->getGrade()), 0));
                                if ($gradeValue >= $minRange && $gradeValue <= $maxRange) {
                                    $mirror[$gradeValue]++;
                                    $sum += $gradeValue;
                                    $count++;
                                }
                            }
                        }
                    }
                }

                for ($i = $minRange; $i <= $maxRange; $i++) {
                    if (isset($mirror[$i])) {
                        $gradeMirror[] = $description . new Bold($i) . ': ' . $mirror[$i] .
                            ($count > 0 ? ' (' . (round(($mirror[$i] / $count) * 100, 0)) . '%)' : '');
                    }
                }

                if ($count > 0) {
                    $average = $sum / $count;
                } else {
                    $average = '';
                }
                $gradeMirror[] = new Bold('Fach-Klassen &#216;: ' . ($average ? round($average, 2) : $average));
            }
        } else {
            $gradeMirror = new Bold(new Warning(
                new Ban() . ' Kein Bewertungssystem hinterlegt.'
            ));
        }

        return $gradeMirror;
    }

    /**
     * Zählung mit verknüpften Tests
     *
     * @param TblTest $tblTest
     * @param $countGrades
     * @param $countStudents
     */
    private function countGradesAndStudentsAll(TblTest $tblTest, &$countGrades, &$countStudents)
    {

        $this->countGradesAndStudentsByTest($tblTest, $countGrades, $countStudents);
        if (($tblTestLinkedList = $tblTest->getLinkedTestAll())) {
            foreach ($tblTestLinkedList as $testItem) {
                $this->countGradesAndStudentsByTest($testItem, $countGrades, $countStudents);
            }
        }
    }

    /**
     * Zählung ohne verknüpfte Tests
     *
     * @param TblTest $tblTest
     * @param $countGrades
     * @param $countStudents
     */
    private function countGradesAndStudentsByTest(TblTest $tblTest, &$countGrades, &$countStudents)
    {
        $tblDivision = $tblTest->getServiceTblDivision();
        $tblSubject = $tblTest->getServiceTblSubject();
        $tblSubjectGroup = $tblTest->getServiceTblSubjectGroup();
        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
            $tblDivision, $tblSubject, $tblSubjectGroup ? $tblSubjectGroup : null
        );

        $tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
        if ($tblGradeList && $tblDivision && $tblSubject && $tblDivisionSubject) {
            foreach ($tblGradeList as $tblGrade) {
                if (($tblPerson = $tblGrade->getServiceTblPerson())
                ) {
                    $countGrades++;

                    if (($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson(
                            $tblDivision, $tblPerson
                        )) && $tblDivisionStudent->isInActiveByDateTime($tblGrade->getDateForSorter())
                    ) {
                        $countStudents++;
                    } else {
                        if ($tblSubjectGroup) {
                            if (!Division::useService()->exitsSubjectStudent($tblDivisionSubject, $tblPerson)) {
                                $countStudents++;
                            }
                        } else {
                            if (!Division::useService()->existsDivisionStudent($tblDivision, $tblPerson)) {
                                $countStudents++;
                            }
                        }
                    }
                }
            }
        }

        if ($tblDivisionSubject && $tblSubjectGroup) {
            $tblSubjectStudentAll = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
            if ($tblSubjectStudentAll) {
                $count = 0;
                foreach ($tblSubjectStudentAll as $tblSubjectStudent) {
                    if (($tblPerson = $tblSubjectStudent->getServiceTblPerson())
                        && ($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson(
                            $tblDivision, $tblPerson
                        ))
                    ) {
                        if ($tblTest->isContinues()) {
                            if (!$tblDivisionStudent->isInActive()) {
                                $count++;
                            }
                        } elseif (!$tblDivisionStudent->isInActiveByDateTime(new DateTime($tblTest->getDate()))) {
                            $count++;
                        }
                    }
                }
                $countStudents += $count;
            }
        } else {
            $tblDivisionStudentAll = Division::useService()->getDivisionStudentAllByDivision($tblDivision, true);
            if ($tblDivisionStudentAll) {
                $count = 0;
                foreach ($tblDivisionStudentAll as $divisionStudent) {
                    if ($tblTest->isContinues()) {
                        if (!$divisionStudent->isInActive()) {
                            $count++;
                        }
                    } elseif (!$divisionStudent->isInActiveByDateTime(new DateTime($tblTest->getDate()))) {
                        $count++;
                    }
                }
                $countStudents += $count;
            }
        }

        if ($countStudents < $countGrades) {
            $countStudents = $countGrades;
        }
    }

    /**
     * @param $Route
     * @param $IsAllYears
     * @param $YearId
     * @param $tblYear
     * @param bool $HasAllYears
     *
     * @return array
     */
    public function setYearButtonList($Route, $IsAllYears, $YearId, &$tblYear, $HasAllYears = true)
    {

        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && $tblYearList) {
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
        }

        // Fachlehrer sollen nur Zugriff auf Leistungsüberprüfungen aller aktuellen Schuljahre haben
        // #SSW-1169 Anlegen von Leistungsüberprüfung von noch nicht erreichten Schuljahren verhindern
        if ($HasAllYears) {
            if ($IsAllYears) {
                $buttonList[] = (new Standard(new Info(new Bold('Alle Schuljahre')),
                    $Route, new Edit(), array('IsAllYears' => true)));
            } else {
                $buttonList[] = (new Standard('Alle Schuljahre', $Route, null,
                    array('IsAllYears' => true)));
            }
        }

        // Abstandszeile
        $buttonList[] = new Container('&nbsp;');

        return $buttonList;
    }

    /**
     * @param TblTest $tblTest
     * @param TblTask|null $tblTask
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTest|null $tblNextTest
     * @param $BasicRoute
     * @param $studentList
     * @param $tableColumns
     * @param $buttonList
     *
     * @return string
     */
    private function setContentForBehaviorTask(
        TblTest $tblTest,
        TblTask $tblTask = null,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTest &$tblNextTest = null,
        $BasicRoute,
        &$studentList,
        &$tableColumns,
        &$buttonList
    ) {
        $gradeType = 'Kopfnote: ' . ($tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '')
            . (($tblTaskPeriod = $tblTask->getServiceTblPeriodByDivision($tblDivision))
                ? new Small(new Muted(' ' . $tblTaskPeriod->getDisplayName()))
                : new Small(new Muted(' Gesamtes Schuljahr')));

        if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation',
            'ShowProposalBehaviorGrade'))
        ) {
            $showProposalBehaviorGrade = $tblSetting->getValue();
        } else {
            $showProposalBehaviorGrade = false;
        }

        // Navigation zwischen den Kopfnotentypen
        if ($tblTask
            && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask,
            $tblDivision))) {

            $tblCurrentGradeType = false;
            $tblNextGradeType = false;
//            $tblNextTest = false;
            $tblGradeTypeList = array();
            foreach ($tblTestList as $tblTestItem) {
                if ($tblSubject && $tblTestItem->getServiceTblSubject()
                    && $tblSubject->getId() == $tblTestItem->getServiceTblSubject()->getId()
                    && (!$tblTest->getServiceTblSubjectGroup()
                        || ($tblTest->getServiceTblSubjectGroup() && $tblTestItem->getServiceTblSubjectGroup()
                            && $tblTest->getServiceTblSubjectGroup()->getId() == $tblTestItem->getServiceTblSubjectGroup()->getId())
                    )
                    && ($tblGradeTypeItem = $tblTestItem->getServiceTblGradeType())) {
                    if (!isset($tblGradeTypeList[$tblTestItem->getId()])) {
                        $tblGradeTypeList[$tblTestItem->getId()] = $tblGradeTypeItem;
                        if ($tblCurrentGradeType && !$tblNextGradeType) {
                            $tblNextGradeType = $tblGradeTypeItem;
                            $tblNextTest = $tblTestItem;
                        }
                        if ($tblTest->getServiceTblGradeType()
                            && $tblTest->getServiceTblGradeType()->getId() == $tblGradeTypeItem->getId()) {
                            $tblCurrentGradeType = $tblGradeTypeItem;
                        }
                    }
                }
            }
            /** @var TblGradeType $tblGradeType */
            foreach ($tblGradeTypeList as $testId => $tblGradeType) {
                if ($tblCurrentGradeType->getId() == $tblGradeType->getId()) {
                    $name = new Info(new Bold($tblGradeType->getName()));
                    $icon = new Edit();
                } else {
                    $name = $tblGradeType->getName();
                    $icon = null;
                }

                // letzte Kopfnote --> zur ersten springen
                if (!$tblNextTest){
                    $tblNextTest = Evaluation::useService()->getTestById($testId);
                }

                $buttonList[] = new Standard($name,
                    $BasicRoute . '/Grade/Edit', $icon, array(
                        'Id' => $testId,
                    )
                );
            }
        }

        foreach ($studentList as $personId => $student) {
            $tblPerson = Person::useService()->getPersonById($personId);
            if ($tblPerson) {
                $tblGradeList = Gradebook::useService()->getGradesByGradeType($tblPerson, $tblDivision, $tblSubject,
                    $tblTest->getServiceTblGradeType());

                $previewsGrade = '';
                if ($tblGradeList) {
                    $count = count($tblGradeList);
                    for ($i = 0; $i < $count; $i++) {
                        /** @var TblGrade $tblGrade */
                        $tblGrade = array_pop($tblGradeList);

                        $taskDate = new DateTime($tblTask->getDate());
                        if ($taskDate > $tblGrade->getEntityCreate()
                            && ($tblTestGrade = $tblGrade->getServiceTblTest())
                            && ($tblTaskGrade = $tblTestGrade->getTblTask())
                            && $tblTaskGrade->getId() != $tblTask->getId()
                        ) {
                            $previewsGrade = $tblGrade->getDisplayGrade();
                            break;
                        }
                    }
                }

                $studentList[$tblPerson->getId()]['PreviewsGrade'] = $previewsGrade;

                // Kopfnotenvorschlag des Klassenlehrers
//                $proposalGrades = array();
//                if ($showProposalBehaviorGrade
//                    && ($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))
//                ) {
//                    foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
//                        if (($tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson())
//                            && ($tblGradeTeacherList = Gradebook::useService()->getGradesByDivisionAndTeacher(
//                                $tblDivision, $tblPersonTeacher, $tblPerson, $tblTest->getServiceTblGradeType()
//                            ))
//                        ) {
//                            foreach ($tblGradeTeacherList as $item) {
//                                if (($displayGrade = $item->getDisplayGrade())
//                                    && ($tblSubjectItem = $item->getServiceTblSubject())
//                                ) {
//                                    $proposalGrades[$item->getId()] = new ToolTip(
//                                        $tblSubjectItem->getAcronym() . ': ' . $displayGrade,
//                                        $tblPersonTeacher->getFullName()
//                                    );
//                                }
//                            }
//                        }
//                    }
//                }
//                $studentList[$tblPerson->getId()]['ProposalGrade'] = implode(', ', $proposalGrades);
                if (($tblProposalBehaviorGrade = Gradebook::useService()->getProposalBehaviorGrade(
                    $tblDivision, $tblTask, $tblTest->getServiceTblGradeType(), $tblPerson
                ))) {
                    $studentList[$tblPerson->getId()]['ProposalGrade'] = $tblProposalBehaviorGrade->getDisplayGrade();
                }
            }
        }

        $tableColumns['PreviewsGrade'] = 'Letzte Zensur';
        if ($showProposalBehaviorGrade) {
            $tableColumns['ProposalGrade'] = 'Kopfnotenvorschlag KL';
        }
        $tableColumns['Grade'] = 'Zensur';
        $tableColumns['Comment'] = 'Vermerk Notenänderung';

        return $gradeType;
    }

    /**
     * @param null $Data
     * @param null $PersonId
     *
     * @return Stage
     */
    public function frontendDivisionTeacherHighlightedTestsOverview($Data = null, $PersonId = null)
    {

        return $this->setHighlightedTestsOverview($Data, true, $PersonId);
    }


    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendHeadmasterHighlightedTestsOverview($Data = null)
    {

        return $this->setHighlightedTestsOverview($Data, false);
    }

    /**
     * @param $Data
     * @param bool $IsDivisionTeacher
     * @param null $PersonId
     *
     * @return Stage
     */
    private function setHighlightedTestsOverview($Data, $IsDivisionTeacher, $PersonId = null)
    {
        $stage = new Stage('Leistungsüberprüfung', 'Planungsübersicht');
        $stage->addButton(new Standard(
            'Zurück',
            $IsDivisionTeacher ? '/Education/Graduation/Evaluation/Test/Teacher' : '/Education/Graduation/Evaluation/Test/Headmaster',
            new ChevronLeft()
        ));

        if ($Data == null && ($tblYearList = Term::useService()->getYearByNow())) {
            $global = $this->getGlobal();

            $tblYear = reset($tblYearList);
            $global->POST['Data']['Year'] = $tblYear->getId();
            $global->POST['Data']['GradeType'] = -SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED;
            $global->POST['Data']['Option'] = 2;

            $global->savePost();
        }

        $receiverContent = ApiEvaluation::receiverContent(
            (new ApiEvaluation())->loadTestPlanning($Data, $IsDivisionTeacher, $PersonId)
        );

        $yearSelectBox = (new SelectBox('Data[Year]', 'Schuljahr',
            array('DisplayName' => Term::useService()->getYearAll())))->setRequired();
        $typeSelectBox = new SelectBox('Data[Type]', 'Schulart', array('Name' => Type::useService()->getTypeAll()));
        $divisionTextField = new TextField('Data[DivisionName]', '', 'Klasse');

        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))) {
            $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
        } else {
            $tblGradeTypeList = array();
        }
        $tblGradeTypeList[] = new SelectBoxItem(-SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED,
            'Nur große Zensuren-Typen (Fett marktiert)');
        $gradeTypeSelectBox = (new SelectBox('Data[GradeType]', 'Zensuren-Typ',
            array('{{ Code }} - {{ Name }}' => $tblGradeTypeList)))
            ->setRequired();

        $optionList[] = new SelectBoxItem(1, 'komplettes Schuljahr');
        $optionList[] = new SelectBoxItem(2, 'ab der aktuellen Woche');
        $option = (new SelectBox('Data[Option]', 'Option', array('Name' => $optionList)))
            ->setRequired();

        $button = (new \SPHERE\Common\Frontend\Link\Repository\Primary('Filtern', '', new Filter()))
            ->ajaxPipelineOnClick(ApiEvaluation::pipelineCreateTestPlanningContent($receiverContent, $Data, $IsDivisionTeacher, $PersonId));

        $form = (new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new Panel(
                    'Filter',
                    new Layout (new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            $yearSelectBox, 4
                        ),
                        new LayoutColumn(
                            $typeSelectBox, 4
                        ),
                        new LayoutColumn(
                            $divisionTextField, 4
                        ),
                        new LayoutColumn(
                            $gradeTypeSelectBox, 4
                        ),
                        new LayoutColumn(
                            $option, 4
                        ),
                        new LayoutColumn(
                            $button
                        ),
                    )))),
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

    /**
     * @param null $DivisionId
     *
     * @return Stage|string
     */
    public function frontendProposalTestSelected($DivisionId = null)
    {
        $stage = new Stage('Kopfnotenvorschlag des Klassenlehrers', 'Übersicht');

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $stage->addButton(new Standard('Zurück', '/Education/Graduation/Evaluation/Test/Teacher', new ChevronLeft()));

            if (($tblDivisionStudentAll = Division::useService()->getDivisionStudentAllByDivision($tblDivision))) {
                $countStudents = 4 * count($tblDivisionStudentAll);
            } else {
                $countStudents = 0;
            }

            if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'))
                && ($tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType))
            ) {
                $dataList = array();
                foreach ($tblTaskList as $tblTask) {
                    $countGrades = 0;
                    if (($tblGradeList = Gradebook::useService()->getProposalBehaviorGradeAllBy($tblDivision, $tblTask))) {
                        foreach ($tblGradeList as $tblProposalBehaviorGrade) {
                            if ($tblProposalBehaviorGrade->getDisplayGrade() !== '') {
                                $countGrades++;
                            }
                        }
                    }

                    $dataList[] = array(
                        'Date' => $tblTask->getDate(),
                        'DisplayPeriod' => $tblTask->getFromDate() . ' - ' . $tblTask->getToDate(),
                        'Name' => $tblTask->getName(),
                        'Grades' => ($countGrades >= $countStudents ? new Success($countGrades . ' von ' . $countStudents) :
                            new Warning($countGrades . ' von ' . $countStudents)),
                        'Option' => new Standard('', '/Education/Graduation/Evaluation/Test/Teacher/Proposal/Grade/Edit', new Listing(),
                            array('DivisionId' => $tblDivision->getId(), 'TaskId' => $tblTask->getId()), 'Zensuren eintragen')
                    );
                }

                $columns = array(
                    'Date' => 'Datum',
                    'DisplayPeriod' => 'Bearbeitungszeitraum',
                    'Name' => 'Name',
                    'Grades' => 'Noten eingetragen',
                    'Option' => ''
                );

                $content = new TableData(
                    $dataList,
                    null,
                    $columns,
                    array(
                        'order' => array(
                            array(0, 'desc')
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 0)
                        )
                    )
                );
            } else {
                $content = new WarningMessage('Es sind aktuell keine Kopfnotenaufträge verfügbar.' , new Ban());
            }

            $stage->setContent(
                new Panel(
                    'Klasse',
                    $tblDivision->getDisplayName(),
                    Panel::PANEL_TYPE_INFO
                )
                . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    $content
                )), new Title(new ListingTable() . ' Übersicht')))
            );
        } else {
            return $stage . new Danger('Klasse nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param null $DivisionId
     * @param null $TaskId
     * @param null $GradeTypeId
     * @param null $Grade
     *
     * @return Stage|string
     */
    public function frontendEditProposalTestGrade($DivisionId = null, $TaskId = null, $GradeTypeId = null, $Grade = null)
    {
        $stage = new Stage('Kopfnotenvorschlag des Klassenlehrers', 'Zensuren eintragen');

        if (!($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            return $stage . new Danger('Klasse nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }
        if (!($tblTask = Evaluation::useService()->getTaskById($TaskId))) {
            return $stage . new Danger('Notenauftrag nicht gefunden.', new Ban())
                . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        $stage->addButton(new Standard('Zurück', '/Education/Graduation/Evaluation/Test/Teacher/Proposal/Selected',
                new ChevronLeft(), array('DivisionId' => $DivisionId))
        );

        $showCourse = false;
        if (($tblLevel = $tblDivision->getTblLevel())
            && ($tblType = $tblLevel->getServiceTblType())
            && $tblType->getName() == 'Mittelschule / Oberschule'
            && intval($tblLevel->getName()) > 6
        ) {
            $showCourse = true;
        }

        $period = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
        $tableColumns = array(
            'Number' => '#',
            'Name' => 'Schüler',
            'Integration' => 'Integration'
        );
        if ($showCourse) {
            $tableColumns['Course'] = 'Bildungsgang';
        }
        $tableColumns['PreviewsGrade'] = 'Letzter Kopfnotenvorschlag';
        $tableColumns['Grade'] = 'Zensur';
        $tableColumns['Comment'] = 'Vermerk Notenänderung';

        $buttonList = array();
        $tblCurrentGradeType = Gradebook::useService()->getGradeTypeById($GradeTypeId);
        $tblNextGradeType = false;
        if (($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivision))) {
            $tblGradeTypeList = array();
            $isCurrent = false;
            foreach ($tblTestList as $tblTest) {
                if (($tblGradeTypeItem = $tblTest->getServiceTblGradeType())) {
                    if (!isset($tblGradeTypeList[$tblGradeTypeItem->getId()])) {
                        $tblGradeTypeList[$tblGradeTypeItem->getId()] = $tblGradeTypeItem;

                        if (!$tblCurrentGradeType) {
                            $tblCurrentGradeType = $tblGradeTypeItem;
                        }

                        if ($tblCurrentGradeType && $isCurrent && !$tblNextGradeType) {
                            $tblNextGradeType = $tblGradeTypeItem;
                        }

                        if (!$isCurrent && $tblCurrentGradeType
                            && $tblCurrentGradeType->getId() == $tblGradeTypeItem->getId()
                        ) {
                            $isCurrent = true;
                        }
                    }
                }
            }

            if ($tblCurrentGradeType) {
                /** @var TblGradeType $tblGradeType */
                foreach ($tblGradeTypeList as $tblGradeType) {
                    if ($tblCurrentGradeType->getId() == $tblGradeType->getId()) {
                        $name = new Info(new Bold($tblGradeType->getName()));
                        $icon = new Edit();
                    } else {
                        $name = $tblGradeType->getName();
                        $icon = null;
                    }

                    $buttonList[] = new Standard($name,
                        '/Education/Graduation/Evaluation/Test/Teacher/Proposal/Grade/Edit',
                        $icon,
                        array(
                            'DivisionId' => $DivisionId,
                            'TaskId' => $TaskId,
                            'GradeTypeId' => $tblGradeType->getId()
                        )
                    );
                }
            }
        }

        $isInPeriod = $tblTask->isInEditPeriod();

        // lastTask
        $taskDate = new DateTime($tblTask->getDate());
        $tblLastTask = false;
        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'))
            && ($tblTaskList = Evaluation::useService()->getTaskAllByDivision($tblDivision, $tblTestType))
        ) {
            $tblTaskList = $this->getSorter($tblTaskList)->sortObjectBy('Date', new DateTimeSorter(), Sorter::ORDER_DESC);
            /** @var TblTask $tblTaskItem */
            foreach ($tblTaskList as $tblTaskItem){
                $date = new DateTime($tblTaskItem->getDate());
                if (!$tblLastTask
                    && $date < $taskDate
                ) {
                    $tblLastTask = $tblTaskItem;
                    break;
                }
            }
        }

        $studentList = array();
        if (($tblScoreType = $tblTask->getServiceTblScoreType())) {

        } else {
            $tblScoreType = Gradebook::useService()->getScoreTypeByIdentifier('GRADES_BEHAVIOR_TASK');
        }
        if (($tblStudentAll = Division::useService()->getStudentAllByDivision($tblDivision, true))) {
            if ($tblScoreType) {
                $selectList[-1] = '';
                if ($tblScoreType->getIdentifier() == 'GRADES') {
                    for ($i = 1; $i < 6; $i++) {
                        $selectList[$i . '+'] = (string)($i . '+');
                        $selectList[$i] = (string)($i);
                        $selectList[$i . '-'] = (string)($i . '-');
                    }
                    $selectList[6] = 6;
                } elseif ($tblScoreType->getIdentifier() == 'GRADES_BEHAVIOR_TASK') {
                    for ($i = 1; $i < 5; $i++) {
                        $selectList[$i . '+'] = (string)($i . '+');
                        $selectList[$i] = (string)($i);
                        $selectList[$i . '-'] = (string)($i . '-');
                    }
                    $selectList[5] = 5;
                } elseif ($tblScoreType->getIdentifier() == 'POINTS') {
                    for ($i = 0; $i < 16; $i++) {
                        $selectList[$i] = (string)$i;
                    }
                }
            } else {
                $selectList = array();
            }

            // Post setzen
            if (($tblProposalBehaviorGradeList = Gradebook::useService()->getProposalBehaviorGradeAllBy(
                $tblDivision,
                $tblTask,
                $tblCurrentGradeType
            ))) {
                $global = $this->getGlobal();
                foreach ($tblProposalBehaviorGradeList as $tblProposalBehaviorGrade) {
                    if (($tblPersonItem = $tblProposalBehaviorGrade->getServiceTblPerson())) {
                        $global->POST['Grade'][$tblPersonItem->getId()]['Grade'] = $tblProposalBehaviorGrade->getDisplayGrade();
                        $global->POST['Grade'][$tblPersonItem->getId()]['Comment'] = $tblProposalBehaviorGrade->getComment();
                    }
                }
                $global->savePost();
            }

            $tabIndex = 1;
            foreach ($tblStudentAll as $tblPerson) {
                $isInActive = false;
                // inaktive Schüler abhängig vom Austrittsdatum ignorieren
                if (($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson(
                        $tblDivision, $tblPerson
                    ))
                    && ($leaveDate = $tblDivisionStudent->getLeaveDateTime()) !== null
                ) {
                    $isInActive = $tblDivisionStudent->isInActive();
                    if ($taskDate > $leaveDate) {
                        continue;
                    }
                }

                $studentTestList[$tblPerson->getId()] = $tblTest;
                $count = count($studentList) + 1;
                $name = $tblPerson->getLastFirstName();
                $studentList[$tblPerson->getId()]['Number'] = $count;
                $studentList[$tblPerson->getId()]['Name'] = $isInActive ? new Strikethrough($name) : $name;
                if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                    $Integration = (new Standard('', ApiSupportReadOnly::getEndpoint(), new EyeOpen()))
                        ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                } else {
                    $Integration = '';
                }
                $studentList[$tblPerson->getId()]['Integration'] = $Integration;
                if (($tblCourse = Student::useService()->getCourseByPerson($tblPerson))) {
                    $course = $tblCourse->getName();
                } else {
                    $course = '';
                }
                $studentList[$tblPerson->getId()]['Course'] = $isInActive ? new Strikethrough($course) : $course;

                $previewsGrade = '';
                if ($tblLastTask
                    && ($tblLastProposalBehaviorGrade = Gradebook::useService()->getProposalBehaviorGrade($tblDivision,
                        $tblLastTask, $tblCurrentGradeType, $tblPerson))
                ) {
                    $previewsGrade = $tblLastProposalBehaviorGrade->getDisplayGrade();
                }
                $studentList[$tblPerson->getId()]['PreviewsGrade'] = $previewsGrade;

                $tblGrade = Gradebook::useService()->getProposalBehaviorGrade($tblDivision, $tblTask, $tblCurrentGradeType, $tblPerson);
                if (!$isInPeriod) {
                    $studentList[$tblPerson->getId()]['Grade'] = $tblGrade ? $tblGrade->getDisplayGrade() : '';
                    $studentList[$tblPerson->getId()]['Comment'] = $tblGrade ? $tblGrade->getComment() : '';
                } else {
                    if ($tblScoreType) {
                        if ($tblScoreType->getIdentifier() == 'VERBAL') {
                            $studentList[$tblPerson->getId()]['Grade']
                                = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '', '',
                                new Quote()))->setTabIndex($tabIndex++);
                        } elseif ($tblScoreType->getIdentifier() == 'GRADES_V1'
                            || $tblScoreType->getIdentifier() == 'GRADES_COMMA'
                        ) {
                            $studentList[$tblPerson->getId()]['Grade']
                                = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '',
                                ''))->setTabIndex($tabIndex++);
                        } else {
                            $selectComplete = (new SelectCompleter('Grade[' . $tblPerson->getId() . '][Grade]', '', '',
                                $selectList))
                                ->setTabIndex($tabIndex++);

                            $studentList[$tblPerson->getId()]['Grade']
                                = $selectComplete;
                        }
                    } else {
                        $studentList[$tblPerson->getId()]['Grade']
                            = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '', '',
                            new Quote()))->setTabIndex($tabIndex++);
                    }

                    $studentList[$tblPerson->getId()]['Comment']
                        = (new TextField('Grade[' . $tblPerson->getId() . '][Comment]', '', '',
                        new Comment()))->setTabIndex(1000 + $tabIndex)->setPrefixValue($tblGrade ? $tblGrade->getDisplayTeacher() : '');
                }
            }
        }

        $gradeType = $tblCurrentGradeType ? $tblCurrentGradeType->getDisplayName() : '';
        $tableData = new TableData($studentList, null, $tableColumns, array(
            "paging" => false, // Deaktivieren Blättern
            "iDisplayLength" => -1,    // Alle Einträge zeigen
            "searching" => false, // Deaktivieren Suchen
            "info" => false,  // Deaktivieren Such-Info
            "sorting" => false,  // Deaktivieren Such-Info
            "responsive" => false,
            'order' => array(
                array('0', 'asc'),
            ),
            'columnDefs' => array(
                array('type' => 'natural', 'targets' => 0),
                array('orderable' => false, 'targets' => '_all'),
            ),
        ));

        /*
         * Content
         */
        $serviceForm = Gradebook::useService()->updateProposalBehaviorGrade(
            new Form(
                new FormGroup(array(
                    new FormRow(
                        new FormColumn(
                            $tableData
                        )
                    ),
                ))
                , new Primary('Speichern', new Save())),
            $tblDivision,
            $tblTask,
            $tblCurrentGradeType,
            $Grade,
            $tblScoreType ? $tblScoreType : null,
            $tblNextGradeType ? $tblNextGradeType : null
        );

        if ($tblTask->isBeforeEditPeriod()) {
            $content = new WarningMessage(
                'Zensuren können erst ab erreichen des Bearbeitungszeitraums vergeben werden.',
                new Exclamation()
            );
        } elseif ($tblTask->isAfterEditPeriod()) {
            $content = new WarningMessage(
                    'Zensuren können von Ihnen nur innerhalb des Bearbeitungszeitraums vergeben werden. Zur Nachträglichen Bearbeitung der Zensuren
                         wenden Sie sich bitte an die Schulleitung.',
                    new Exclamation()
                )
                . $tableData;
        } else {
            $content = $serviceForm;
        }

        $stage->setContent(
            ApiSupportReadOnly::receiverOverViewModal()
            .new Layout (array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Klasse',
                                $tblDivision->getDisplayName(),
                                Panel::PANEL_TYPE_INFO
                            ), 3
                        ),
                        new LayoutColumn(
                            new Panel('Bearbeitungszeitraum',
                                $period,
                                Panel::PANEL_TYPE_INFO), 3
                        ),
                        new LayoutColumn(
                            new Panel('Zensuren-Typ',
                                $gradeType,
                                Panel::PANEL_TYPE_INFO), 3
                        ),
                        new LayoutColumn(
                            new Panel(
                                'Name',
                                $tblTask->getName(),
                                Panel::PANEL_TYPE_INFO
                            ), 3
                        ),
                    )),
                )),
                (!empty($buttonList) ? new LayoutGroup(new LayoutRow(new LayoutColumn($buttonList))) : null),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $content
                        )
                    ))
                ))
            ))
        );

        return $stage;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     * @param DateTime $taskDate
     * @param DateTime $leaveDate
     *
     * @return bool
     */
    private function checkIsPersonInActive(TblDivision $tblDivision, TblPerson $tblPerson, DateTime $taskDate)
    {
         // inaktive Schüler abhängig vom Austrittsdatum ignorieren
        if (($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson(
                $tblDivision, $tblPerson
            ))
            && ($leaveDate = $tblDivisionStudent->getLeaveDateTime()) !== null
        ) {
            if ($taskDate > $leaveDate) {
                return true;
            }
        }

        return false;
    }
}
