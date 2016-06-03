<?php
namespace SPHERE\Application\Education\Graduation\Evaluation;

use DateTime;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
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
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Quote;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
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
     * @return Stage
     */
    public function frontendTestTeacher()
    {

        $Stage = new Stage('Leistungsüberprüfung', 'Auswahl');
        $Stage->setMessage(
            'Verwaltung der Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten),
            wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Bold('Ansicht: Lehrer'), '/Education/Graduation/Evaluation/Test/Teacher',
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

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        if ($tblPerson) {
            // Fachlehrer
            $tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson);
            if ($tblSubjectTeacherAllByTeacher) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    $tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject();
                    if ($tblDivisionSubject && $tblDivisionSubject->getTblDivision()) {
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

            // Klassenlehrer
            $tblDivisionTeacherAllByTeacher = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson);
            if ($tblDivisionTeacherAllByTeacher) {
                foreach ($tblDivisionTeacherAllByTeacher as $tblDivisionTeacher) {
                    if ($tblDivisionTeacher->getTblDivision()) {
                        $tblDivisionSubjectAllByDivision
                            = Division::useService()->getDivisionSubjectByDivision($tblDivisionTeacher->getTblDivision());
                        if ($tblDivisionSubjectAllByDivision) {
                            foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                                if ($tblDivisionSubject->getTblSubjectGroup()) {
                                    if ($tblDivisionSubject->getServiceTblSubject()) {
                                        $divisionSubjectList[$tblDivisionSubject->getTblDivision()->getId()]
                                        [$tblDivisionSubject->getServiceTblSubject()->getId()]
                                        [$tblDivisionSubject->getTblSubjectGroup()->getId()]
                                            = $tblDivisionSubject->getId();
                                    }
                                } else {
                                    if ($tblDivisionSubject->getServiceTblSubject()) {
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
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
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
    public function frontendHeadmasterTest()
    {

        $Stage = new Stage('Leistungsüberprüfung', 'Auswahl');
        $Stage->setMessage(
            'Verwaltung aller Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten).'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Graduation/Evaluation/Test/Teacher'));
            $Stage->addButton(new Standard(new Bold('Ansicht: Leitung'),
                '/Education/Graduation/Evaluation/Test/Headmaster', new Edit()));
        }

        $divisionSubjectTable = array();
        $divisionSubjectList = array();

        $tblDivisionAll = Division::useService()->getDivisionAll();
        if ($tblDivisionAll) {
            foreach ($tblDivisionAll as $tblDivision) {
                $tblDivisionSubjectAllByDivision = Division::useService()->getDivisionSubjectByDivision($tblDivision);
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
                        new LayoutColumn(array(
                            new TableData($divisionSubjectTable, null, array(
                                'Year' => 'Schuljahr',
                                'Type' => 'Schulart',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'SubjectGroup' => 'Gruppe',
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
     * @return Stage
     */
    public function frontendDivisionTeacherTask()
    {

        $Stage = new Stage('Notenaufträge', 'Übersicht');
        $Stage->setMessage(
            'Anzeige der Kopfnoten- und Stichtagsnotenaufträge (inklusive vergebener Zensuren),
            wo der angemeldete Lehrer als Klassenlehrer hinterlegt ist.'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Bold('Ansicht: Lehrer'), '/Education/Graduation/Evaluation/Task/Teacher',
                new Edit()));
            $Stage->addButton(new Standard('Ansicht: Leitung', '/Education/Graduation/Evaluation/Task/Headmaster'));
        }

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

        if (!empty($taskList)) {
            /** @var TblTask $tblTask */
            foreach ($taskList as $tblTask) {
                $tblTask->Type = $tblTask->getTblTestType()->getName();
                $tblTask->EditPeriod = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
                $tblTask->Period = $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod()->getDisplayName() : 'Gesamtes Schuljahr';
                $tblTask->Option =
                    (new Standard('',
                        '/Education/Graduation/Evaluation/Task/Teacher/Grades',
                        new Equalizer(),
                        array('Id' => $tblTask->getId()),
                        'Zensuren ansehen')
                    );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new TableData(
                                    $taskList, null, array(
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
     * @param null $Select
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterTask($Task = null, $Select = null, $YearId = null)
    {

        $Stage = new Stage('Notenaufträge', 'Übersicht');
        $Stage->setMessage(
            'Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebener Zensuren).'
        );
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Headmaster');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Graduation/Evaluation/Task/Teacher'));
            $Stage->addButton(new Standard(new Bold('Ansicht: Leitung'),
                '/Education/Graduation/Evaluation/Task/Headmaster', new Edit()));
        }

        $tblTaskAll = Evaluation::useService()->getTaskAll();

        if ($tblTaskAll) {
            foreach ($tblTaskAll as $tblTask) {
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

                $tblTask->Type = $tblTask->getTblTestType()->getName();
                $tblTask->EditPeriod = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
                $tblTask->Period = $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod()->getDisplayName() : 'Gesamtes Schuljahr';
                $tblTask->Option =
                    ($hasEdit ? (new Standard('',
                        '/Education/Graduation/Evaluation/Task/Headmaster/Edit',
                        new Edit(),
                        array('Id' => $tblTask->getId()),
                        'Bearbeiten')) : '')
                    . (new Standard('',
                        '/Education/Graduation/Evaluation/Task/Headmaster/Destroy', new Remove(),
                        array('Id' => $tblTask->getId()),
                        'Löschen'))
                    . (new Standard('',
                        '/Education/Graduation/Evaluation/Task/Headmaster/Division',
                        new Listing(),
                        array('Id' => $tblTask->getId()),
                        'Klassen auswählen')
                    )
                    . (new Standard('',
                        '/Education/Graduation/Evaluation/Task/Headmaster/Grades',
                        new Equalizer(),
                        array('Id' => $tblTask->getId()),
                        'Zensuren ansehen')
                    );

            }
        }

        $tblYear = false;
        if ($YearId === null) {
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                $tblYear = reset($tblYearList);
            }
        } else {
            $tblYear = Term::useService()->getYearById($YearId);
        }

        if ($tblYear) {
            $Global = $this->getGlobal();
            $Global->POST['Select']['Year'] = $tblYear->getId();
            $Global->savePost();
        }

        $Form = ($this->formTask($tblYear ? $tblYear : null));
        $Form
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new TableData(
                                    $tblTaskAll, null, array(
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
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(
//                            new Well(Evaluation::useService()->getYear(
//                                new Form(
//                                    new FormGroup(
//                                        new FormRow(
//                                            new FormColumn(
//                                                new SelectBox(
//                                                    'Select[Year]', 'Schuljahr', array('Name' => Term::useService()->getYearAll()), new Calendar()
//                                                )
//                                            )
//                                        )
//                                    )
//                                , new Primary('Auswählen', new Select()))
//                                , $Select))
//                        )
//                    ))
//                ), new Title(new Select() . ' Schuljahr auswählen')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(Evaluation::useService()->getYear(
                                new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new SelectBox(
                                                    'Select[Year]', 'Schuljahr',
                                                    array('{{Name}} {{Description}}' => Term::useService()->getYearAll()),
                                                    new Calendar()
                                                )
                                            )
                                        )
                                    )
                                    , new Primary('Auswählen', new Select()))
                                , $Select)),
                            $tblYear ? new Well(Evaluation::useService()->createTask($Form, $Task, $tblYear)) : null
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return Form
     */
    private function formTask(TblYear $tblYear = null)
    {

        $tblTestTypeAllWhereTask = Evaluation::useService()->getTestTypeAllWhereTask();
        $tblYearAllByNow = Term::useService()->getYearByNow();
        $periodSelect[] = '';
        if ($tblYear === null) {
            if ($tblYearAllByNow) {
                foreach ($tblYearAllByNow as $tblYear) {
                    $tblPeriodAllByYear = Term::useService()->getPeriodAllByYear($tblYear);
                    if ($tblPeriodAllByYear) {
                        foreach ($tblPeriodAllByYear as $tblPeriod) {
                            $periodSelect[$tblPeriod->getId()] = $tblPeriod->getDisplayName();
                        }
                    }
                }
            }
        } else {
            $tblPeriodAllByYear = Term::useService()->getPeriodAllByYear($tblYear);
            if ($tblPeriodAllByYear) {
                foreach ($tblPeriodAllByYear as $tblPeriod) {
                    $periodSelect[$tblPeriod->getId()] = $tblPeriod->getDisplayName();
                }
            }
        }

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
                    new SelectBox('Task[Period]', 'Noten-Zeitraum beschränken', $periodSelect), 4
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
     * @return Stage
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

        if ($tblDivisionSubject && $tblDivisionSubject->getServiceTblSubject() && $tblDivision) {
            $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                $tblDivision,
                $tblDivisionSubject->getServiceTblSubject(),
                null,
                null,
                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
            );
        } else {
            $tblTestList = false;
        }
        if ($tblTestList) {
            array_walk($tblTestList, function (TblTest &$tblTest) use (&$BasicRoute) {

                $tblDivision = $tblTest->getServiceTblDivision();
                if ($tblDivision) {
                    $tblTest->Division = $tblDivision->getDisplayName();
                } else {
                    $tblTest->Division = '';
                }
                $tblTask = $tblTest->getTblTask();
                $tblTest->Subject = $tblTest->getServiceTblSubject() ? $tblTest->getServiceTblSubject()->getName() : '';
                $tblTest->Period = $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getDisplayName() : '';
                if ($tblTest->getServiceTblGradeType()) {
                    if ($tblTask) {
                        $tblTest->GradeType = new Bold('Kopfnote: ' . $tblTest->getServiceTblGradeType()->getName());
                    } else {
                        $tblTest->GradeType = $tblTest->getServiceTblGradeType()->getName();
                    }
                } elseif ($tblTask) {
                    $tblTest->GradeType = new Bold('Stichtagsnote');
                } else {
                    $tblTest->GradeType = '';
                }
                if ($tblTask) {
                    $tblTest->DisplayDescription = $tblTask->getName();
                    $tblTest->DisplayPeriod = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
                } else {
                    $tblTest->DisplayDescription = $tblTest->getDescription();
                    $tblTest->DisplayPeriod = $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getDisplayName() : '';
                }

                $tblTest->Option =
                    ($tblTest->getTblTestType()->getId() == Evaluation::useService()->getTestTypeByIdentifier('TEST')->getId()
                        ? (new Standard('', $BasicRoute . '/Edit', new Edit(),
                            array('Id' => $tblTest->getId()), 'Bearbeiten'))
                        . (new Standard('', $BasicRoute . '/Destroy', new Remove(),
                            array('Id' => $tblTest->getId()), 'Löschen'))
                        : '')
                    . (new Standard('', $BasicRoute . '/Grade/Edit', new Listing(),
                        array('Id' => $tblTest->getId()), 'Zensuren bearbeiten'));

                $tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
                if ($tblGradeList) {
                    $countGrades = count($tblGradeList);
                } else {
                    $countGrades = 0;
                }

                if ($tblTest->getServiceTblDivision() && $tblTest->getServiceTblSubject()) {
                    if ($tblTest->getServiceTblSubjectGroup()) {
                        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                            $tblTest->getServiceTblDivision(),
                            $tblTest->getServiceTblSubject(),
                            $tblTest->getServiceTblSubjectGroup()
                        );
                        $countStudent = Division::useService()->countSubjectStudentByDivisionSubject($tblDivisionSubject);
                    } else {
                        $countStudent = Division::useService()->countDivisionStudentAllByDivision($tblTest->getServiceTblDivision());
                    }
                    $tblTest->Grades = $countGrades == $countStudent ? new Success($countGrades . ' von ' . $countStudent) :
                        new Warning($countGrades . ' von ' . $countStudent);
                }
            });
        } else {
            $tblTestList = array();
        }

        if ($tblDivision->getServiceTblYear()) {
            $Form = $this->formTest($tblDivision->getServiceTblYear())
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        } else {
            $Form = false;
        }

        // Vorschau Test
        $tblTestAllByDivision = Evaluation::useService()->getTestAllByTestTypeAndDivision(Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblDivision);
        $testArray = array();
        if ($tblTestAllByDivision) {
            $tblTestAllByDivision = $this->getSorter($tblTestAllByDivision)->sortObjectBy('Date', new DateTimeSorter());

            $nowWeek = date('W');
            $nowYear = (new \DateTime('now'))->format('Y');
            /** @var TblTest $item */
            foreach ($tblTestAllByDivision as $item) {
                if ($item->getDate()) {
                    $dateWeek = date('W', strtotime($item->getDate()));
                    $dateYear = (new \DateTime($item->getDate()))->format('Y');
                    if ($dateWeek !== false && (($dateYear == $nowYear && $dateWeek >= $nowWeek) || $dateYear > $nowYear)) {
                        $testArray[$dateWeek][$item->getId()] = $item;
                    }
                }
            }
        }

        $trans = array(
            'Mon' => 'Mo',
            'Tue' => 'Di',
            'Wed' => 'Mi',
            'Thu' => 'Do',
            'Fri' => 'Fr',
            'Sat' => 'Sa',
            'Sun' => 'So',
        );

        $preview = array();
        if (!empty($testArray)) {
            $columnCount = 0;
            $row = array();
            foreach ($testArray as $calendarWeek => $testList) {
                $panelData = array();
                $date = new \DateTime();
                if (!empty($testList)) {
                    foreach ($testList as $item) {
                        if ($item->getServiceTblSubject() && $item->getServiceTblGradeType()) {
                            $content = $item->getServiceTblSubject()->getAcronym() . ' '
                                . $item->getServiceTblGradeType()->getCode() . ' '
                                . $item->getDescription() . ' ('
                                . strtr(date('D', strtotime($item->getDate())), $trans) . ' ' . date('d.m.y',
                                    strtotime($item->getDate())) . ')';
                            $panelData[] = $item->getServiceTblGradeType()->isHighlighted()
                                ? new Bold($content) : $content;
                            $date = new \DateTime($item->getDate());
                        }
                    }
                }

                $year = $date->format('Y');
                $week = $date->format('W');
                $monday = date('d.m.y', strtotime("$year-W{$week}"));
                $friday = date('d.m.y', strtotime("$year-W{$week}-5"));;

                $panel = new Panel(
                    new Bold('KW: ' . $calendarWeek) . new Muted(' &nbsp;&nbsp;&nbsp;(' . $monday . ' - ' . $friday . ')'),
                    $panelData,
                    $calendarWeek == date('W') ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_DEFAULT
                );
                $columnCount++;
                if ($columnCount > 4) {
                    $preview[] = new LayoutRow($row);
                    $row = array();
                    $columnCount = 1;
                }
                $row[] = new LayoutColumn($panel, 3);
            }
            if (!empty($row)) {
                $preview[] = new LayoutRow($row);
            }
        }

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
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblTestList, null, array(
                                'Date' => 'Datum',
                                'Division' => 'Klasse',
                                'Subject' => 'Fach',
                                'DisplayPeriod' => 'Zeitraum',
                                'GradeType' => 'Zensuren-Typ',
                                'DisplayDescription' => 'Beschreibung',
                                'CorrectionDate' => 'Korrekturdatum',
                                'ReturnDate' => 'R&uuml;ckgabedatum',
                                'Grades' => 'Noten eingetragen',
                                'Option' => ''
                            ), array(
                                'order' => array(
                                    array(0, 'desc')
                                ),
                                'columnDefs' => array(
                                    array('type' => 'de_date', 'targets' => 0)
                                )
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $Form
                                ? new Well(Evaluation::useService()->createTest($Form, $tblDivisionSubject->getId(),
                                $Test, $BasicRoute))
                                : new Danger('Schuljahr nicht gefunden', new Ban())
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen')),
                new LayoutGroup(
                    $preview
                    , new Title(new Clock() . ' Planung'))
            ))
        );

        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     * @return Form
     */
    private function formTest(TblYear $tblYear)
    {

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);
        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);

        // select current period
        $Global = $this->getGlobal();
        if (!$Global->POST && $tblPeriodList) {
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

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Test[Period]', 'Zeitraum', array('DisplayName' => $tblPeriodList)), 6
                ),
                new FormColumn(
                    new SelectBox('Test[GradeType]', 'Zensuren-Typ', array('Name' => $tblGradeTypeList)), 6
                )
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Test[Description]', '1. Klassenarbeit', 'Beschreibung'), 12
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Test[Date]', '', 'Datum', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Test[CorrectionDate]', '', 'Korrekturdatum', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Test[ReturnDate]', '', 'Bekanntgabedatum für Notenübersicht (Eltern, Schüler)',
                        new Calendar()), 4
                ),
            ))
        )));
    }

    /**
     * @param null $DivisionSubjectId
     * @param null $Test
     *
     * @return Stage
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
                $Global->POST['Test']['Description'] = $tblTest->getDescription();
                $Global->POST['Test']['Date'] = $tblTest->getDate();
                $Global->POST['Test']['CorrectionDate'] = $tblTest->getCorrectionDate();
                $Global->POST['Test']['ReturnDate'] = $tblTest->getReturnDate();
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

            $Form = new Form(new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Test[Description]', '1. Klassenarbeit', 'Beschreibung'), 12
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new DatePicker('Test[Date]', '', 'Datum', new Calendar()), 4
                    ),
                    new FormColumn(
                        new DatePicker('Test[CorrectionDate]', '', 'Korrekturdatum', new Calendar()), 4
                    ),
                    new FormColumn(
                        new DatePicker('Test[ReturnDate]', '', 'R&uuml;ckgabedatum', new Calendar()), 4
                    ),
                ))
            )));
            $Form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $tblDivision = $tblTest->getServiceTblDivision();

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
                                new Panel('Zeitraum:',
                                    $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO), 3
                            ),
                            new LayoutColumn(
                                new Panel('Zensuren-Typ:',
                                    $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '',
                                    Panel::PANEL_TYPE_INFO), 3
                            )
                        )),
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
     * @return Stage
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

            $tblDivisionSubject = Division::useService()->getDivisionSubject(
                $tblTest->getServiceTblDivision(),
                $tblTest->getServiceTblSubject(),
                $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
            );

            $Stage->addButton(
                new Standard('Zur&uuml;ck', $BasicRoute . '/Selected', new ChevronLeft(),
                    array('DivisionSubjectId' => $tblDivisionSubject->getId()))
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Test', ($tblTest->getDescription() !== '' ? '&nbsp;&nbsp;'
                                . new Muted(new Small(new Small($tblTest->getDescription()))) : ''),
                                Panel::PANEL_TYPE_INFO),
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
     * @return Stage
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

        $Stage = new Stage('Leistungsüberprüfung', 'Zensuren bearbeiten');

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

        // Klassenlehrer darf ohne Grund Noten editieren
        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        if (!$tblTest->getServiceTblDivision()) {
            return new Danger(new Ban() . ' Klasse nicht gefunden')
            . new Redirect('/Education/Graduation/Evaluation/Test/Teacher', Redirect::TIMEOUT_ERROR);
        }

        if (Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblTest->getServiceTblDivision(),
            $tblPerson)
        ) {
            $isEdit = true;
        } else {
            $isEdit = false;
            $Stage->setMessage(new Warning(new Exclamation() . ' Bei einer Notenänderung muss für diese ein Grund angegeben werden.'));
        }

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

        $isTestAppointedDateTask = ($tblTest->getTblTestType()->getId()
            == Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')->getId());
        $tblDivision = $tblTest->getServiceTblDivision();
        $tblSubject = $tblTest->getServiceTblSubject();

        $tblScoreRule = false;
        $scoreRuleText = array();
        $tblScoreType = false;
        if ($tblDivision && $tblSubject) {
            $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject(
                $tblDivision,
                $tblSubject
            );
            if ($tblScoreRuleDivisionSubject) {

                if ($tblTest->getTblTask() && $tblTest->getTblTask()->getServiceTblScoreType()) {
                    $tblScoreType = $tblTest->getTblTask()->getServiceTblScoreType();
                } else {
                    $tblScoreType = $tblScoreRuleDivisionSubject->getTblScoreType();
                }

                if ($tblScoreRuleDivisionSubject->getTblScoreRule()) {
                    $tblScoreRule = $tblScoreRuleDivisionSubject->getTblScoreRule();
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
                }
            }
        }

        $gradeList = Gradebook::useService()->getGradeAllByTest($tblTest);

        /*
         * set post
         */
        if ($gradeList) {
            $Global = $this->getGlobal();
            /** @var TblGrade $grade */
            foreach ($gradeList as $grade) {
                if (empty($Grade)) {
                    if ($grade->getServiceTblPerson()) {
                        if ($grade->getGrade() === null) {
                            $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Attendance'] = 1;
                        } else {
                            if ($IsEdit) {
                                $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Grade'] =
                                    str_replace('.', ',', $grade->getGrade());

                                $trend = $grade->getTrend();
                                if ($trend !== null) {
                                    $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Trend'] = $trend;
                                }
                            } else {
                                $trend = $grade->getTrend();
                                if ($trend !== null) {
                                    if ($trend == TblGrade::VALUE_TREND_PLUS) {
                                        $trend = '+';
                                    } elseif ($trend == TblGrade::VALUE_TREND_MINUS) {
                                        $trend = '-';
                                    } else {
                                        $trend = '';
                                    }
                                } else {
                                    $trend = '';
                                }
                                $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Grade'] =
                                    str_replace('.', ',', $grade->getGrade()) . $trend;
                            }

                        }
                        $Global->POST['Grade'][$grade->getServiceTblPerson()->getId()]['Comment'] = $grade->getComment();
                    }
                }
            }
            $Global->savePost();
        }

        /*
         * set grade mirror
         */
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

                $description = '';
                if ($tblScoreType->getIdentifier() == 'GRADES') {
                    $minRange = 1;
                    $maxRange = 6;
                    $description = 'Note ';
                } elseif ($tblScoreType->getIdentifier() == 'POINTS') {
                    $minRange = 0;
                    $maxRange = 15;
                    $description = 'Punkte ';
                } elseif ($tblScoreType->getIdentifier() == 'GRADES_V1') {
                    $minRange = 1;
                    $maxRange = 5;
                    $description = 'Note ';
                }

                for ($i = $minRange; $i <= $maxRange; $i++) {
                    $mirror[$i] = 0;
                }

                if ($gradeList) {
                    /** @var TblGrade $grade */
                    foreach ($gradeList as $grade) {
                        if (empty($Grade)) {

                            if (is_numeric($grade->getGrade())) {
                                $gradeValue = intval(round(floatval($grade->getGrade()), 0));
                                if ($gradeValue >= $minRange && $gradeValue <= $maxRange) {
                                    $mirror[$gradeValue]++;
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
            }
        } else {
            $gradeMirror = new Bold(new Warning(
                new Ban() . ' Kein Bewertungssystem hinterlegt.'
            ));
        }

        $studentList = array();
        $errorRowList = array();
        $columnDefinition = array();

        $tblTask = $tblTest->getTblTask();
        $IsTaskAndInPeriod = true;
        if ($tblTask && !$tblTask->isInEditPeriod()) {
            $IsTaskAndInPeriod = false;
        }

        $hasPreviewGrades = false;

        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $tblSubjectStudentAllByDivisionSubject = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
            if ($tblSubjectStudentAllByDivisionSubject) {
                foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                    $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                    if ($tblPerson) {

                        $average = false;
                        $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName()
                            . ($average ? new Bold('&nbsp;&nbsp;&#216; ' . $average) : '');
                    }
                }
            }
        } else {
            $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentAll) {
                foreach ($tblDivisionStudentAll as $tblPerson) {

                    $average = false;
                    $studentList[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName()
                        . ($average ? new Bold('&nbsp;&nbsp;&#216; ' . $average) : '');
                }
            }
        }
        if ($tblTask) {
            $period = $tblTask->getFromDate() . ' - ' . $tblTask->getToDate();
            $tableColumns = array(
                'Name' => 'Schüler',
            );

            // Stichtagsnotenauftrag
            if ($isTestAppointedDateTask) {

                $gradeType = 'Stichtagsnote' . ($tblTask->getServiceTblPeriod()
                        ? new Small(new Muted(' ' . $tblTask->getServiceTblPeriod()->getDisplayName()))
                        : new Small(new Muted(' Gesamtes Schuljahr')));

                $dataList = array();
                $periodListCount = array();
                $columnDefinition['Number'] = 'Nr.';
                $columnDefinition['Student'] = "Schüler";

                $tblPeriodList = false;
                // ist Stichtagsnotenauftrag auf eine Periode beschränkt oder wird das gesamte Schuljahr genutzt
                if ($tblTask->getServiceTblPeriod()) {
                    $tblPeriodList[] = $tblTask->getServiceTblPeriod();
                } elseif ($tblTask->getServiceTblYear()) {
                    $tblPeriodList = Term::useService()->getPeriodAllByYear($tblTask->getServiceTblYear());
                } else {
                    // alte Daten wo noch kein Schuljahr ausgewählt werde musste bei der Erstellung des Stichtagsnotenauftrags
                    $tblYearAll = Term::useService()->getYearAllByDate(DateTime::createFromFormat('d.m.Y',
                        $tblTask->getDate()));
                    if ($tblYearAll) {
                        foreach ($tblYearAll as $tblYear) {
                            if ($tblTask->getServiceTblPeriod()) {
                                $tblPeriodList = array($tblTask->getServiceTblPeriod());
                            } else {
                                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                            }
                        }
                    }
                }

                // Tabellenkopf mit Test-Code und Datum erstellen
                if ($tblPeriodList) {
                    foreach ($tblPeriodList as $tblPeriod) {
                        if ($tblDivisionSubject->getServiceTblSubject()) {
                            $count = 0;
                            $tblTestList = Evaluation::useService()->getTestAllByTypeAndDivisionAndSubjectAndPeriodAndSubjectGroup(
                                $tblDivision,
                                $tblDivisionSubject->getServiceTblSubject(),
                                Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                $tblPeriod,
                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                            );
                            if ($tblTestList) {

                                // Sortierung der Tests nach Datum
                                $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date',
                                    new DateTimeSorter());

                                /** @var TblTest $tblTestTemp */
                                foreach ($tblTestList as $tblTestTemp) {
                                    if ($tblTestTemp->getServiceTblGradeType()) {
                                        $count++;
                                        $date = $tblTestTemp->getDate();
                                        if (strlen($date) > 6) {
                                            $date = substr($date, 0, 6);
                                        }
                                        $columnDefinition['Test' . $tblTestTemp->getId()] = new Small(new Muted($date)) . '<br>'
                                            . ($tblTestTemp->getServiceTblGradeType()->isHighlighted()
                                                ? $tblTestTemp->getServiceTblGradeType()->getCode()
                                                : new Muted($tblTestTemp->getServiceTblGradeType()->getCode()));
                                    }
                                }
//                                $columnDefinition['PeriodAverage' . $tblPeriod->getId()] = '&#216;';
//                                $count++;
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
                                    $tblTestTemp = Evaluation::useService()->getTestById($testId);
                                    if ($tblTestTemp) {
                                        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTestTemp,
                                            $tblPerson);
                                        if ($tblGrade) {
                                            $data[$column] = $tblTestTemp->getServiceTblGradeType()
                                                ? ($tblTestTemp->getServiceTblGradeType()->isHighlighted()
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
                                            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                            $tblScoreRule ? $tblScoreRule : null,
                                            $tblPeriod,
                                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                        );

                                        if (is_array($average)) {
                                            $errorRowList = $average;
                                            $average = '';
                                        } else {
                                            $posStart = strpos($average, '(');
                                            if ($posStart !== false) {
//                                                $posEnd = strpos($average, ')');
//                                                if ($posEnd !== false) {
//                                                  $priority = substr($average, $posStart + 1, $posEnd - ($posStart + 1));
//                                                }
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
                                        Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                        $tblScoreRule ? $tblScoreRule : null,
                                        $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null,
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

                                        // Zensuren voreintragen bei Stichtagsnotenauftrag, wenn noch keine vergeben ist
                                        if ($average && !Gradebook::useService()->getGradeByTestAndStudent($tblTest,
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
                                }
                            }
                        }

                        $dataList[$tblPerson->getId()] = $data;
                    }
                }

                $studentList = $dataList;

                $columnDefinition['Grade'] = 'Zensur';
                if ($tblScoreType && $tblScoreType->getIdentifier() == 'GRADES') {
                    $columnDefinition['Trend'] = 'Tendenz';
                }
                $columnDefinition['Comment'] = 'Vermerk Notenänderung';
            } else {
                // Kopfnote
                $gradeType = 'Kopfnote: ' . ($tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '')
                    . ($tblTask->getServiceTblPeriod()
                        ? new Small(new Muted(' ' . $tblTask->getServiceTblPeriod()->getDisplayName()))
                        : new Small(new Muted(' Gesamtes Schuljahr')));

                foreach ($studentList as $personId => $student) {
                    $tblPerson = Person::useService()->getPersonById($personId);
                    if ($tblPerson) {
                        $tblGradeList = Gradebook::useService()->getGradesByGradeType($tblPerson, $tblSubject,
                            $tblTest->getServiceTblGradeType());

                        $previewsGrade = '';
                        if ($tblGradeList) {
                            $count = count($tblGradeList);
                            for ($i = 0; $i < $count; $i++) {
                                /** @var TblGrade $tblGrade */
                                $tblGrade = array_pop($tblGradeList);
                                if ($tblTask->getEntityCreate() > $tblGrade->getEntityCreate()) {
                                    $previewsGrade = $tblGrade->getDisplayGrade();
                                    break;
                                }
                            }
                        }

                        $studentList[$tblPerson->getId()]['PreviewsGrade'] = $previewsGrade;
                    }
                }

                $tableColumns['PreviewsGrade'] = 'Letzte Zensur';
                $tableColumns['Grade'] = 'Zensur';
                if ($tblScoreType && $tblScoreType->getIdentifier() == 'GRADES') {
                    $tableColumns['Trend'] = 'Tendenz';
                }
                $tableColumns['Comment'] = 'Vermerk Notenänderung';
            }
        } else {
            $period = $tblTest->getServiceTblPeriod() ? $tblTest->getServiceTblPeriod()->getDisplayName() : '';
            $gradeType = $tblTest->getServiceTblGradeType() ? $tblTest->getServiceTblGradeType()->getName() : '';

            if ($tblScoreType && $tblScoreType->getIdentifier() == 'GRADES') {
                $tableColumns = array(
                    'Name' => 'Schüler',
                    'Grade' => 'Zensur',
                    'Trend' => 'Tendenz',
                    'Comment' => 'Vermerk Notenänderung',
                    'Attendance' => 'Nicht teilgenommen'
                );
            } else {
                $tableColumns = array(
                    'Name' => 'Schüler',
                    'Grade' => 'Zensur',
                    'Comment' => 'Vermerk Notenänderung',
                    'Attendance' => 'Nicht teilgenommen'
                );
            }
        }

        if ($studentList) {
            foreach ($studentList as $personId => $value) {
                $tblPerson = Person::useService()->getPersonById($personId);
                $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                    $tblPerson);
                $studentList = $this->contentEditTestGradeTableRow($tblPerson, $tblGrade, $IsEdit, $studentList,
                    $IsTaskAndInPeriod, $tblScoreType ? $tblScoreType : null
                );
            }
        }

        if ($isTestAppointedDateTask) {
            $tableData = new TableData(
                $studentList, null, $columnDefinition, null
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
                $headTableColumnList[] = new TableColumn('', 3);
            }
            $tableData->prependHead(
                new TableHead(
                    new TableRow(
                        $headTableColumnList
                    )
                )
            );
        } else {
            if ($tblTask && !$IsEdit && $tblTask->isAfterEditPeriod())
            if (isset($tableColumns['Trend'])){
                unset($tableColumns['Trend']);
            }
            $tableData = new TableData($studentList, null, $tableColumns, null);
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
            , $tblTest->getId(), $Grade, $BasicRoute, $minRange, $maxRange
        );
        $warningNoScoreType = new \SPHERE\Common\Frontend\Message\Repository\Warning('Kein Bewertungssystem hinterlegt.
                                Zensuren können erst vergeben werden nachdem für diese Fach-Klasse ein Bewertungssystem
                                hinterlegt wurde.', new Ban());
        if ($tblTask) {
            if ($tblTask->isBeforeEditPeriod()) {
                $content = new \SPHERE\Common\Frontend\Message\Repository\Warning(
                        'Zensuren können erst ab erreichen des Bearbeitungszeitraums vergeben werden.',
                        new Exclamation()
                    )
                    . ($tblScoreType ? '' : $warningNoScoreType);
            } elseif ($tblTask->isAfterEditPeriod()) {
                if ($IsEdit) {
                    $content = $tblScoreType ? $serviceForm : $warningNoScoreType;
                } else {
                    $content = new \SPHERE\Common\Frontend\Message\Repository\Warning(
                            'Zensuren können von Ihnen nur innerhalb des Bearbeitungszeitraums vergeben werden. Zur Nachträglichen Bearbeitung der Zensuren
                             wenden Sie sich bitte an den Klassenlehrer oder die Schulleitung.',
                            new Exclamation()
                        )
                        . ($tblScoreType ? '' : $warningNoScoreType)
                        . $tableData
                    ;
                }
            } else {
                $content = $tblScoreType ? $serviceForm : $warningNoScoreType;
            }

        } else {
            $content = $tblScoreType ? $serviceForm : $warningNoScoreType;
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
                            ), 3
                        ),
                        new LayoutColumn(
                            new Panel('Zeitraum:',
                                $period,
                                Panel::PANEL_TYPE_INFO), 3
                        ),
                        new LayoutColumn(
                            new Panel('Zensuren-Typ:',
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
                    (($tblTask && !$IsEdit && !$tblTask->isInEditPeriod())
                        ? new LayoutRow(new LayoutColumn(new \SPHERE\Common\Frontend\Message\Repository\Warning(
                                'Sie befinden sich nicht mehr im Bearbeitungszeitraum.
                            Zensuren können von Ihnen nicht mehr eingetragen werden.', new Exclamation())
                        ))
                        : null
                    ),
                    ($hasPreviewGrades
                        ? new LayoutRow(new LayoutColumn(new \SPHERE\Common\Frontend\Message\Repository\Warning(
                            'Es wurden noch nicht alle Notenvorschläge gespeichert.', new Exclamation()
                        )))
                        : null
                    )
                )),
                (!empty($errorRowList) ? new LayoutGroup($errorRowList) : null),
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
     * @param TblScoreType|null $tblScoreType
     * @param bool $IsTaskAndInPeriod
     *
     * @return array
     */
    private function contentEditTestGradeTableRow(
        TblPerson $tblPerson,
        $tblGrade,
        $IsEdit,
        $student,
        $IsTaskAndInPeriod = false,
        TblScoreType $tblScoreType = null
    ) {

        if ($tblScoreType === null) {
            $tblScoreType = false;
        }

        if ($tblGrade) {
            $labelComment = new Warning('Bei Notenänderung bitte einen Grund angeben');
        } else {
            $labelComment = '';
        }

        if (!$IsEdit && !$IsTaskAndInPeriod) {
            /** @var TblGrade $tblGrade */
            $student[$tblPerson->getId()]['Grade'] = $tblGrade ? $tblGrade->getDisplayGrade(): '';
            $student[$tblPerson->getId()]['Comment'] = $tblGrade ? $tblGrade->getComment(): '';
        } else {
            if ($tblScoreType) {
                if ($tblScoreType->getIdentifier() == 'VERBAL') {
                    $student[$tblPerson->getId()]['Grade']
                        = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '', '',
                        new Quote()))->setTabIndex(1);
                } elseif ($tblScoreType->getIdentifier() == 'GRADES_V1') {
                    $student[$tblPerson->getId()]['Grade']
                        = (new TextField('Grade[' . $tblPerson->getId() . '][Grade]', '', ''))->setTabIndex(1);
                } elseif ($tblScoreType->getIdentifier() == 'POINTS') {
                    $student[$tblPerson->getId()]['Grade']
                        = (new NumberField('Grade[' . $tblPerson->getId() . '][Grade]', '', ''))->setTabIndex(1);
                } else {
                    $student = $this->setFieldsForGradesWithTrend($student, $tblPerson);
                }
            } else {
                $student = $this->setFieldsForGradesWithTrend($student, $tblPerson);
            }
            $student[$tblPerson->getId()]['Comment']
                = (new TextField('Grade[' . $tblPerson->getId() . '][Comment]', '', $labelComment,
                new Comment()))->setTabIndex(3);
            $student[$tblPerson->getId()]['Attendance'] =
                (new CheckBox('Grade[' . $tblPerson->getId() . '][Attendance]', ' ', 1))->setTabIndex(4);
        }

        return $student;
    }

    /**
     * @param $student
     * @param TblPerson $tblPerson
     * @return array
     */
    private function setFieldsForGradesWithTrend($student, TblPerson $tblPerson)
    {

        $selectBoxContent = array(
            TblGrade::VALUE_TREND_NULL => '',
            TblGrade::VALUE_TREND_PLUS => 'Plus',
            TblGrade::VALUE_TREND_MINUS => 'Minus'
        );

        $student[$tblPerson->getId()]['Grade']
            = (new NumberField('Grade[' . $tblPerson->getId() . '][Grade]', '', ''))->setTabIndex(1);
        $student[$tblPerson->getId()]['Trend']
            = (new SelectBox('Grade[' . $tblPerson->getId() . '][Trend]', '', $selectBoxContent,
            new ResizeVertical()))->setTabIndex(2);

        return $student;
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

        $Stage = new Stage('Leistungsüberprüfung', 'Zensuren bearbeiten');

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
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskEdit($Id = null, $Task = null)
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
            new Standard('Zurück', '/Education/Graduation/Evaluation/Task/Headmaster',
                new ChevronLeft())
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

        $Form = ($this->formTask($tblTask->getServiceTblYear() ? $tblTask->getServiceTblYear() : null));
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
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskDivision($Id = null, $Data = null)
    {

        $Stage = new Stage('Notenauftrag', 'Klassen zuordnen');

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
            new Standard('Zurück', '/Education/Graduation/Evaluation/Task/Headmaster',
                new ChevronLeft())
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

        $gradeTypeColumnList = array();
        if ($isBehaviorTask) {
            $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType(
                Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR')
            );
            if ($tblGradeTypeList) {
                foreach ($tblGradeTypeList as $tblGradeType) {
                    if ($hasEdit) {
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
                        if ($hasEdit) {
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
        if ($hasEdit) {
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
                        (!$hasEdit ?
                            new LayoutColumn(
                                new \SPHERE\Common\Frontend\Message\Repository\Warning('Der Bearbeitungszeitraum ist abgelaufen. Klassen können nicht mehr zu diesem Notenauftrag
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
     * @return Stage|string
     */
    public function frontendHeadmasterTaskGrades($Id = null)
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
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_ERROR);
        }


        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Task/Headmaster',
                new ChevronLeft())
        );

        $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);

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

        foreach ($divisionList as $divisionId => $testList) {
            $tblDivision = Division::useService()->getDivisionById($divisionId);
            if ($tblDivision) {
                // Stichtagsnote
                if ($tblTask->getTblTestType()->getId() == Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')) {
                    if (!empty($testList)) {
                        /** @var TblTest $tblTest */
                        foreach ($testList as $tblTest) {
                            $tblSubject = $tblTest->getServiceTblSubject();
                            if ($tblSubject && $tblTest->getServiceTblDivision()) {
                                $tableHeaderList[$tblDivision->getId()]['Name'] = 'Schüler';
                                $tableHeaderList[$tblDivision->getId()]['Subject' . $tblSubject->getId()] = $tblSubject->getAcronym();

                                $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                    $tblTest->getServiceTblDivision(),
                                    $tblSubject,
                                    $tblTest->getServiceTblSubjectGroup() ? $tblTest->getServiceTblSubjectGroup() : null
                                );

                                if ($tblDivisionSubject->getTblSubjectGroup()) {
                                    $tblSubjectStudentAllByDivisionSubject =
                                        Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                    if ($tblSubjectStudentAllByDivisionSubject) {
                                        foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                            $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                            if ($tblPerson) {
                                                $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                    $tblTest, $tblSubject, $tblPerson, $studentList);
                                            }
                                        }
                                    }
                                } else {
                                    $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
                                    if ($tblDivisionStudentAll) {
                                        foreach ($tblDivisionStudentAll as $tblPerson) {

                                            $studentList = $this->setTableContentForAppointedDateTask($tblDivision,
                                                $tblTest, $tblSubject, $tblPerson, $studentList);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Bug Schüler ist nicht in der Gruppe, wenn nicht alle Schüler in einer Gruppe sind, z.B. bei Ethik
                    if (!empty($studentList)) {
                        foreach ($studentList as $divisionListId => $students) {
                            if (is_array($students)) {
                                foreach ($students as $studentId => $student) {
                                    foreach ($tableHeaderList[$divisionListId] as $key => $value) {
                                        if (!isset($student[$key])) {
                                            $studentList[$divisionId][$studentId][$key] = "";
                                        }
                                    }
                                }
                            }
                        }
                    }

                } else {

                    // Kopfnoten
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

                                if ($tblDivisionSubject->getTblSubjectGroup()) {
                                    $tblSubjectStudentAllByDivisionSubject =
                                        Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                                    if ($tblSubjectStudentAllByDivisionSubject) {
                                        foreach ($tblSubjectStudentAllByDivisionSubject as $tblSubjectStudent) {

                                            $tblPerson = $tblSubjectStudent->getServiceTblPerson();
                                            if ($tblPerson) {
                                                list($studentList, $grades) = $this->setTableContentForBehaviourTask($tblDivision,
                                                    $tblTest, $tblPerson, $studentList, $grades);
                                            }
                                        }
                                    }
                                } else {
                                    $tblDivisionStudentAll = Division::useService()->getStudentAllByDivision($tblDivision);
                                    if ($tblDivisionStudentAll) {
                                        foreach ($tblDivisionStudentAll as $tblPerson) {

                                            list($studentList, $grades) = $this->setTableContentForBehaviourTask($tblDivision,
                                                $tblTest, $tblPerson, $studentList, $grades);
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
                                        null
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
     * @return $studentList
     */
    private function setTableContentForAppointedDateTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblSubject $tblSubject,
        TblPerson $tblPerson,
        $studentList
    ) {
        $studentList[$tblDivision->getId()][$tblPerson->getId()]['Name'] =
            $tblPerson->getLastFirstName();
        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        $tblTask = $tblTest->getTblTask();
        $tblScoreRuleDivisionSubject = Gradebook::useService()->getScoreRuleDivisionSubjectByDivisionAndSubject($tblDivision,
            $tblSubject);
        if ($tblScoreRuleDivisionSubject) {
            $tblScoreRule = $tblScoreRuleDivisionSubject->getTblScoreRule();
        } else {
            $tblScoreRule = false;
        }
        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson,
            $tblDivision,
            $tblSubject,
            Evaluation::useService()->getTestTypeByIdentifier('TEST'),
            $tblScoreRule ? $tblScoreRule : null,
            $tblTask->getServiceTblPeriod() ? $tblTask->getServiceTblPeriod() : null
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
            $gradeValue = $tblGrade->getGrade();
            $trend = $tblGrade->getTrend();

            $isGradeInRange = true;
            if ($average !== ' ' && $average && $gradeValue !== null) {
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

            $studentList[$tblDivision->getId()][$tblPerson->getId()]
            ['Subject' . $tblSubject->getId()] = ($tblGrade->getGrade() !== null ?
                    $gradeValue : '') . (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        } else {
            $studentList[$tblDivision->getId()][$tblPerson->getId()]
            ['Subject' . $tblSubject->getId()] =
                new Warning('fehlt')
                . (($average !== ' ' && $average) ? new Muted('&nbsp;&nbsp; &#216;' . $average) : '');
            return $studentList;
        }
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param $studentList
     * @param $grades
     * @return array
     */
    private function setTableContentForBehaviourTask(
        TblDivision $tblDivision,
        TblTest $tblTest,
        TblPerson $tblPerson,
        $studentList,
        $grades
    ) {
        $studentList[$tblDivision->getId()][$tblPerson->getId()]['Name'] =
            $tblPerson->getLastFirstName();
        $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
            $tblPerson);

        if ($tblTest->getServiceTblGradeType() && $tblTest->getServiceTblSubject()) {
            $gradeTypeId = $tblTest->getServiceTblGradeType()->getId();
            $tblSubject = $tblTest->getServiceTblSubject();
            if ($tblGrade) {
                $gradeText = $tblSubject->getAcronym() . ': ' . ($tblGrade->getGrade() !== null ?
                        $tblGrade->getGrade() : '');
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
                    new Warning('f');
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
     * @return Stage|string
     */
    public function frontendDivisionTeacherTaskGrades($Id = null)
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
            . new Redirect('/Education/Graduation/Evaluation/Task/Teacher', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zurück', '/Education/Graduation/Evaluation/Task/Teacher',
                new ChevronLeft())
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
        $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblTask);

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
                                    $tblTask->getFromDate() . ' - ' . $tblTask->getToDate()))),
                                Panel::PANEL_TYPE_INFO
                            )
                        )
                    ))
                )),
            ))
            . new Layout($tableList)
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendHeadmasterTaskDestroy(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Notenauftrag', 'Löschen');

        if (!Evaluation::useService()->getTaskById($Id)) {
            return $Stage . new Danger('Notenauftrag nicht gefunden nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_ERROR);
        }

        $tblTask = Evaluation::useService()->getTaskById($Id);
        if ($tblTask) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Graduation/Evaluation/Task/Headmaster', new ChevronLeft())
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
                                        array('Id' => $Id, 'Confirm' => true)
                                    )
                                    . new Standard(
                                        'Nein', '/Education/Graduation/Evaluation/Task/Headmaster', new Disable())
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
                            new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Notenauftrag nicht gefunden.', new Ban())
            . new Redirect('/Education/Graduation/Evaluation/Task/Headmaster', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

}
