<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Level
     * @param null $Division
     * @param null $Year
     *
     * @return Stage
     */
    public function frontendCreateLevelDivision($Level = null, $Division = null, $Year = null)
    {

        $Stage = new Stage('Klassen', 'Aktuelle Übersicht');
        new Backward();

        $DivisionList = array();
        if (isset( $Year ) && $Year !== '0') {
            $tblYear = Term::useService()->getYearById($Year);
            $TempList = Division::useService()->getDivisionByYear($tblYear);
            if ($TempList) {
                foreach ($TempList as $Temp) {
                    $DivisionList[] = $Temp;
                }
            }
        } else {
            $tblYearList = Term::useService()->getYearByNow();
            if (!empty( $tblYearList )) {
                foreach ($tblYearList as $tblYear) {
                    $TempList = Division::useService()->getDivisionByYear($tblYear);
                    if ($TempList) {
                        foreach ($TempList as $Temp) {
                            $DivisionList[] = $Temp;
                        }
                    }
                }
            }
        }
        if (isset( $Year ) && $Year !== '0') {
            $tblYear = Term::useService()->getYearById($Year);
            if ($tblYear) {
                $Stage->setDescription('Übersicht '.new \SPHERE\Common\Frontend\Text\Repository\Info(new Bold($tblYear->getDisplayName())));
            }
        }

        $Stage->addButton(
            new Standard('Aktuelle Übersicht',
                new Route(__NAMESPACE__), new PersonGroup())
        );

        $YearAll = Term::useService()->getYearAllSinceYears(1);
        if (!empty( $YearAll )) {
            foreach ($YearAll as $key => $row) {
                $name[$key] = strtoupper($row->getDisplayName());
            }
            array_multisort($name, SORT_ASC, $YearAll);

            /** @noinspection PhpUnusedParameterInspection */
            array_walk($YearAll, function (TblYear &$tblYear) use ($Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblYear->getDisplayName(),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Year' => $tblYear->getId()
                        ), $tblYear->getDescription())
                );
            });
        }

        $tblDivisionAll = $DivisionList;

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Temp['Year'] = $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '';
                $Temp['SchoolType'] = $tblDivision->getTypeName();
                $Temp['ClassGroup'] = $tblDivision->getDisplayName();

                if ($tblDivision->getServiceTblYear()) {
                    $tblPeriodAll = $tblDivision->getServiceTblYear()->getTblPeriodAll();
                } else {
                    $tblPeriodAll = false;
                }
                $Period = array();
                if ($tblPeriodAll) {
                    foreach ($tblPeriodAll as $tblPeriod) {
                        $Period[] = $tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
                    }
                    $Temp['Period'] = new Listing($Period);
                } else {
                    $Temp['Period'] = 'fehlt';
                }

                $SubjectUsedCount = Division::useService()->countDivisionSubjectForSubjectTeacherByDivision($tblDivision);
                $GroupTeacherCount = Division::useService()->countDivisionSubjectGroupTeacherByDivision($tblDivision);
                $Temp['Description'] = $tblDivision->getDescription();
                $Temp['StudentList'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
//                $Temp['TeacherList'] = Division::useService()->countDivisionTeacherAllByDivision($tblDivision);
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    $NameList = array();
                    foreach ($tblTeacherList as $tblPerson) {
                        $NameList[] = $tblPerson->getLastName();
                    }
//                    $Temp['TeacherList'] = new Listing($NameList);
                    $Temp['TeacherList'] = implode('<br/>', $NameList);
                } else {
                    $Temp['TeacherList'] = '';
                }
//                $Custody = Division::useService()->countDivisionCustodyAllByDivision($tblDivision);
                $SubjectCount = Division::useService()->countDivisionSubjectAllByDivision($tblDivision);

                if ($SubjectUsedCount > 1) {
                    $Temp['SubjectList'] = $SubjectCount
                        .new PullRight(new Small(new Small(new Muted('('.new Danger($SubjectUsedCount).') Fachlehrer fehlen'))));
                } elseif ($SubjectUsedCount == 1) {
                    $Temp['SubjectList'] = $SubjectCount
                        .new PullRight(new Small(new Small(new Muted('('.new Danger($SubjectUsedCount).') Fachlehrer fehlt'))));
                } else {
                    $Temp['SubjectList'] = $SubjectCount;
                }
                if ($GroupTeacherCount > 1) {
                    $Temp['SubjectList'] .= '<br/>'.new PullRight(new Small(new Small(new Muted('('.new Danger($GroupTeacherCount).') Gruppenlehrer fehlen'))));
                } elseif ($GroupTeacherCount == 1) {
                    $Temp['SubjectList'] .= '<br/>'.new PullRight(new Small(new Small(new Muted('('.new Danger($GroupTeacherCount).') Gruppenlehrer fehlt'))));
                }
                $Temp['Option'] = new Standard('&nbsp;Klassenansicht', '/Education/Lesson/Division/Show',
                        new EyeOpen(), array('Id' => $tblDivision->getId()), 'Klasse einsehen')
                    .new Standard('', '/Education/Lesson/Division/Change', new Pencil(),
                        array('Id' => $tblDivision->getId()), 'Beschreibung bearbeiten')
                    .new Standard('', '/Education/Lesson/Division/Copy', new MoreItems(),
                        array('Id' => $tblDivision->getId()), 'Klasse kopieren')
                    .(new Standard('', '/Education/Lesson/Division/Destroy', new Remove(),
                        array('Id' => $tblDivision->getId()), 'Löschen'));;

                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Year'        => 'Schuljahr',
                                    'Period'      => 'Zeitraum',
                                    'SchoolType'  => 'Schultyp',
                                    'ClassGroup'  => 'Schulklasse',
                                    'Description' => 'Beschreibung',
                                    'StudentList' => 'Schüler',
                                    'TeacherList' => 'Klassenlehrer',
                                    'SubjectList' => 'Fächer',
                                    'Option'      => '',
                                )
                                , array(
                                    'order'      => array(array(3, 'asc')),
                                    'columnDefs' => array(
                                        array('orderable' => false, 'width' => '20px', 'targets' => 0),
                                        array('type' => 'de_date', 'targets' => 1),
                                        array('type' => 'natural', 'targets' => 3),
                                        array('type' => 'natural', 'targets' => 5),
                                        array('type' => 'natural', 'targets' => 7),
                                    )
                                )
                            )
                        )
                    )
                    , new Title(new ListingTable().' Übersicht')),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Division::useService()->createLevelDivision(
                                    $this->formLevelDivision()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Level, $Division
                                )
                            ))
                    ), new Title(new PlusSign().' Hinzufügen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblLevel|null    $tblLevel
     * @param TblDivision|null $tblDivision
     * @param bool             $future
     *
     * @return Form
     */
    public function formLevelDivision(TblLevel $tblLevel = null, TblDivision $tblDivision = null, $future = false)
    {

        $tblLevelAll = Division::useService()->getLevelAll();
        $acNameAll = array();
        if ($tblLevelAll) {
            array_walk($tblLevelAll, function (TblLevel $tblLevel) use (&$acNameAll) {

                if (!in_array($tblLevel->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblLevel->getName());
                }
            });
        }

        $Global = $this->getGlobal();

        if (!isset( $Global->POST['Level'] ) && $tblLevel) {
            $Global->POST['Level']['Type'] = ( $tblLevel->getServiceTblType() ? $tblLevel->getServiceTblType()->getId() : 0 );
            $Global->POST['Level']['Name'] = $tblLevel->getName();
            $Global->POST['Division']['Year'] = ( $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getId() : 0 );
            $Global->POST['Division']['Name'] = $tblDivision->getName();
            $Global->POST['Division']['Description'] = $tblDivision->getDescription();

            if (!$tblLevel) {
                $Global->POST['Level']['Check'] = true;
            } else {
                if ($future && $tblLevel->getIsChecked()) {
                    $Global->POST['Level']['Check'] = true;
                }
            }

            $Global->savePost();
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();

//        if ($future) {
//            $tblYearAll = Term::useService()->getYearAllFutureYears(1);
//        } else {
            $tblYearAll = Term::useService()->getYearAllSinceYears(0);
//        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Klassenstufe',
                            array(
                                new SelectBox('Level[Type]', 'Schulart', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                                ), new Education()),
                                new CheckBox('Level[Check]', 'jahrgangübergreifende Klasse anlegen', 1, array(
                                    'Level[Name]'
                                )),
                                new AutoCompleter('Level[Name]', 'Klassenstufe (Nummer)', 'z.B: 5', $acNameAll,
                                    new Pencil()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 4),
                    new FormColumn(
                        new Panel('Klassengruppe',
                            array(
                                new SelectBox('Division[Year]', 'Schuljahr', array(
                                    '{{ Year }} {{ Description }}' => $tblYearAll
                                ), new Education()),
                                '&nbsp;',
                                new AutoCompleter('Division[Name]', 'Klassengruppe (Name)', 'z.B: Alpha', $acNameAll,
                                    new Pencil())
                            ), Panel::PANEL_TYPE_INFO
                        ), 4),
                    new FormColumn(
                        new Panel('Klassengruppe',
                            array(
                                new TextField('Division[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                                    new Pencil())
                            ), Panel::PANEL_TYPE_INFO
                        ), 4),
                )),
            ))
        );
    }

    /**
     * @param null $Id
     * @param null $StudentId
     * @param null $Remove
     *
     * @return Stage
     */
    public function frontendStudentAdd($Id = null, $StudentId = null, $Remove = null)
    {

        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            $Stage = new Stage('Schüler', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }

        $Title = 'der Klasse '.new Bold($tblDivision->getDisplayName());

        $Stage = new Stage('Schüler', $Title);
//            $Stage->addButton(new Backward());
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $tblDivision->getId())));

        if (!$tblDivision->getTblLevel()) {
            $Stage->setMessage('Liste aller Schüler im Schuljahr '.( $tblDivision->getServiceTblYear()
                    ? $tblDivision->getServiceTblYear()->getDisplayName() : '' ).'.');

            if ($tblDivision && null !== $StudentId && ( $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($StudentId) )) {
                if ($Remove) {
                    Division::useService()->removeStudentToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Success('Schüler erfolgreich entfernt')
                        .new Redirect('/Education/Lesson/Division/Student/Add', Redirect::TIMEOUT_SUCCESS,
                            array('Id' => $Id))
                    );
                    return $Stage;
                } else {
                    Division::useService()->addStudentToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Success('Schüler erfolgreich hinzugefügt')
                        .new Redirect('/Education/Lesson/Division/Student/Add', Redirect::TIMEOUT_SUCCESS,
                            array('Id' => $Id))
                    );
                    return $Stage;
                }
            }
            $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $tblDivisionStudentAll = false;
            if ($tblGroup) {

                $tblStudentList = Group::useService()->getPersonAllByGroup($tblGroup);  // Alle Schüler
                if ($tblDivision->getTblLevel()) {
                    if ($tblDivision->getServiceTblYear()) {
                        $tblDivisionList = Division::useService()->getDivisionByYear($tblDivision->getServiceTblYear());
                        if ($tblStudentList) {
                            if ($tblDivisionList) {
                                foreach ($tblDivisionList as $tblSingleDivision) {
                                    $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblSingleDivision);
                                    if ($tblDivision->getTblLevel() && $tblDivisionStudentList) {
                                        $tblStudentList = array_udiff($tblStudentList, $tblDivisionStudentList,
                                            function (TblPerson $tblPersonA, TblPerson $tblPersonB) {

                                                return $tblPersonA->getId() - $tblPersonB->getId();
                                            });
                                    }
                                }
                                if (is_array($tblStudentList)) {
                                    $tblDivisionStudentAll = $tblStudentList;
                                }
                            }
                        }
                    }
                } else {
                    $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                    if ($tblDivisionStudentList) {
                        $tblStudentList = array_udiff($tblStudentList, $tblDivisionStudentList,
                            function (TblPerson $invoiceA, TblPerson $invoiceB) {

                                return $invoiceA->getId() - $invoiceB->getId();
                            });
                    }
                    if (is_array($tblStudentList)) {
                        $tblDivisionStudentAll = $tblStudentList;
                    }
                }

            }
        } else {
            if ($tblDivision->getTblLevel()->getIsChecked()) {
                $Stage->setMessage('Liste aller Schüler die im Schuljahr '.( $tblDivision->getServiceTblYear()
                        ? $tblDivision->getServiceTblYear()->getDisplayName() : '' )
                    .' noch keiner Klasse zugeordnet sind.');
            } else {
                $Stage->setMessage('Liste aller Schüler im Schuljahr '.( $tblDivision->getServiceTblYear()
                        ? $tblDivision->getServiceTblYear()->getDisplayName() : '' ).'.');
            }

            if ($tblDivision && null !== $StudentId && ( $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($StudentId) )) {
                if ($Remove) {
                    Division::useService()->removeStudentToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Success('Schüler erfolgreich entfernt')
                        .new Redirect('/Education/Lesson/Division/Student/Add', Redirect::TIMEOUT_SUCCESS,
                            array('Id' => $Id))
                    );
                    return $Stage;
                } else {
                    Division::useService()->addStudentToDivision($tblDivision, $tblPerson);
                    $Stage->setContent(
                        new Success('Schüler erfolgreich hinzugefügt')
                        .new Redirect('/Education/Lesson/Division/Student/Add', Redirect::TIMEOUT_SUCCESS,
                            array('Id' => $Id))
                    );
                    return $Stage;
                }
            }
            $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $tblDivisionStudentAll = false;
            if ($tblGroup) {

                $tblStudentList = Group::useService()->getPersonAllByGroup($tblGroup);  // Alle Schüler
                $IsChecked = $tblDivision->getTblLevel()->getIsChecked();
                if (!$IsChecked) {
                    if ($tblDivision->getServiceTblYear()) {
                        $tblYear = $tblDivision->getServiceTblYear();
                        $tblYearAll = Term::useService()->getYearAll();
                        $tblDivisionList = array();
                        if ($tblYearAll) {
                            foreach ($tblYearAll as $Year) {
                                if ($tblYear->getYear() == $Year->getYear()) {
                                    $DivisionList = Division::useService()->getDivisionByYear($Year);
                                    if (is_array($DivisionList)) {
                                        $tblDivisionList = array_merge($tblDivisionList, $DivisionList);
                                    }
                                }
                            }
                        }
//                            $tblDivisionList = Division::useService()->getDivisionByYear($tblDivision->getServiceTblYear());
                        if ($tblStudentList) {

                            if ($tblDivisionList) {
                                /** @var TblDivision $tblSingleDivision */
                                foreach ($tblDivisionList as $tblSingleDivision) {
                                    if ($tblSingleLevel = $tblSingleDivision->getTblLevel()) {
                                        $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblSingleDivision);
                                        if (!$tblSingleLevel->getIsChecked() && $tblDivisionStudentList) {
                                            $tblStudentList = array_udiff($tblStudentList, $tblDivisionStudentList,
                                                function (TblPerson $invoiceA, TblPerson $invoiceB) {

                                                    return $invoiceA->getId() - $invoiceB->getId();
                                                });
                                        }
                                    }
                                }
                                if (is_array($tblStudentList)) {
                                    $tblDivisionStudentAll = $tblStudentList;
                                }
                            }
                        }
                    }
                } else {
                    $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                    if ($tblDivisionStudentList) {
                        $tblStudentList = array_udiff($tblStudentList, $tblDivisionStudentList,
                            function (TblPerson $tblPersonA, TblPerson $tblPersonB) {

                                return $tblPersonA->getId() - $tblPersonB->getId();
                            });
                    }
                    if (is_array($tblStudentList)) {
                        $tblDivisionStudentAll = $tblStudentList;
                    }
                }

            }
        }

        $tblDivisionStudentActive = Division::useService()->getStudentAllByDivision($tblDivision);

        if (is_array($tblDivisionStudentActive) && is_array($tblDivisionStudentAll)) {
            $tblStudentAvailable = array_udiff($tblDivisionStudentAll, $tblDivisionStudentActive,
                function (TblPerson $ObjectA, TblPerson $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        } else {
            $tblStudentAvailable = $tblDivisionStudentAll;
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (is_array($tblDivisionStudentActive)) {
            $count = 1;
            array_walk($tblDivisionStudentActive, function (TblPerson &$Entity) use (&$Id, &$count) {
                $Entity->Number = $count++;
                $Entity->Name = $Entity->getLastFirstName();
                $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($Entity);
                $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                if (!empty( $tblAddressAll )) {
                    $tblAddress = current($tblAddressAll)->getGuiString();
                } else {
                    $tblAddress = false;
                }
                if (isset( $tblAddress ) && $tblAddress) {
                    $Entity->Address = $tblAddress;
                } else {
                    $Entity->Address = new WarningText('Keine Adresse hinterlegt');
                }

                $tblCourse = Student::useService()->getCourseByPerson($Entity);
                $Entity->Course = $tblCourse ? $tblCourse->getName() : '';

                /** @noinspection PhpUndefinedFieldInspection */
                $Entity->Option = new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                        '/Education/Lesson/Division/Student/Add', new Minus(),
                        array(
                            'Id'        => $Id,
                            'StudentId' => $Entity->getId(),
                            'Remove'    => true
                        ))
                );
            });
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (isset( $tblDivisionStudentAll ) && !empty( $tblDivisionStudentAll )) {
            array_walk($tblDivisionStudentAll, function (TblPerson &$Entity) use ($Id) {

                $Entity->Name = $Entity->getLastFirstName();
                $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($Entity);
                $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                if (!empty( $tblAddressAll )) {
                    $tblAddress = current($tblAddressAll)->getGuiString();
                } else {
                    $tblAddress = false;
                }
                if (isset( $tblAddress ) && $tblAddress) {
                    $Entity->Address = $tblAddress;
                } else {
                    $Entity->Address = new WarningText('Keine Adresse hinterlegt');
                }

                $tblCourse = Student::useService()->getCourseByPerson($Entity);
                $Entity->Course = $tblCourse ? $tblCourse->getName() : '';

                /** @noinspection PhpUndefinedFieldInspection */
                $Entity->Option = new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen',
                        '/Education/Lesson/Division/Student/Add', new Plus(),
                        array(
                            'Id'        => $Id,
                            'StudentId' => $Entity->getId()
                        ))
                );
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Ausgewählt', 'Schüler'),
                            ( empty( $tblDivisionStudentActive )
                                ? new Warning('Keine Schüler zugewiesen')
                                : new TableData($tblDivisionStudentActive, null,
                                    array(
                                        'Number' => '#',
                                        'Name'    => 'Schüler',
                                        'Address' => 'Adresse',
                                        'Course' => 'Bildungsgang',
                                        'Option'  => ''
                                    ))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title('Verfügbar', 'Schüler'),
                            ( empty( $tblStudentAvailable )
                                ? new Info('Keine weiteren Schüler verfügbar')
                                : new TableData($tblStudentAvailable, null,
                                    array(
                                        'Name'    => 'Schüler',
                                        'Address' => 'Adresse',
                                        'Course' => 'Bildungsgang',
                                        'Option'  => ' '
                                    ))
                            )
                        ), 6)
                    ))
                )
            )
        );
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $TeacherId
     * @param null $Remove
     * @param null $Description
     *
     * @return Stage
     */
    public function frontendTeacherAdd($Id = null, $TeacherId = null, $Remove = null, $Description = null)
    {

        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            $Stage = new Stage('Klassenlehrer', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }

        $Title = 'der Klasse '.new Bold($tblDivision->getDisplayName());

        $Stage = new Stage('Klassenlehrer', $Title);
        $Stage->setMessage('');
//            $Stage->addButton(new Backward());
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $tblDivision->getId())));

        if ($tblDivision && null !== $TeacherId && ( $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($TeacherId) )) {
            if ($Remove) {
                Division::useService()->removeTeacherToDivision($tblDivision, $tblPerson);
                $Stage->setContent(
                    new Success('Klassenlehrer erfolgreich entfernt')
                    .new Redirect('/Education/Lesson/Division/Teacher/Add', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $Id))
                );
                return $Stage;
            } else {
                Division::useService()->addDivisionTeacher($tblDivision, $tblPerson, $Description);
                $Stage->setContent(
                    new Success('Klassenlehrer erfolgreich hinzugefügt')
                    .new Redirect('/Education/Lesson/Division/Teacher/Add', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $Id))
                );
                return $Stage;
            }
        }
        $tblGroup = Group::useService()->getGroupByMetaTable('TEACHER');
        if ($tblGroup) {
            $tblDivisionTeacherAll = Group::useService()->getPersonAllByGroup($tblGroup);
        } else {
            $tblDivisionTeacherAll = false;
        }
        $tblDivisionTeacherActive = Division::useService()->getTeacherAllByDivision($tblDivision);

        if (is_array($tblDivisionTeacherActive) && is_array($tblDivisionTeacherAll)) {
            $tblTeacherAvailable = array_udiff($tblDivisionTeacherAll, $tblDivisionTeacherActive,
                function (TblPerson $ObjectA, TblPerson $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        } else {
            $tblTeacherAvailable = $tblDivisionTeacherAll;
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (is_array($tblDivisionTeacherActive)) {
            array_walk($tblDivisionTeacherActive, function (TblPerson &$Entity) use ($Id, $tblDivision) {

                /** @noinspection PhpUndefinedFieldInspection */
                $Entity->DisplayName = $Entity->getLastFirstName();
                $Entity->Address = $Entity->fetchMainAddress() ? $Entity->fetchMainAddress()->getGuiString() : '';
                $Entity->Option = new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                        '/Education/Lesson/Division/Teacher/Add', new Minus(),
                        array(
                            'Id'        => $Id,
                            'TeacherId' => $Entity->getId(),
                            'Remove'    => true
                        ))
                );
                $Entity->Description = Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision,
                    $Entity)->getDescription();
            });
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (isset( $tblDivisionTeacherAll ) && !empty( $tblDivisionTeacherAll )) {
            array_walk($tblDivisionTeacherAll, function (TblPerson &$Entity) use ($Id) {

                $Entity->DisplayName = $Entity->getLastFirstName();
                $Entity->Address = $Entity->fetchMainAddress() ? $Entity->fetchMainAddress()->getGuiString() : '';
                $Entity->Options = (new Form(
                    new FormGroup(
                        new FormRow(array(
                            new FormColumn(
                                new TextField('Description', 'z.B.: Stellvertreter', '', new Person()
                                )
                                , 7),
                            new FormColumn(
                                new Primary('Hinzufügen',
                                    new Plus())
                                , 5)
                        ))
                    ), null,
                    '/Education/Lesson/Division/Teacher/Add',
                    array(
                        'Id'        => $Id,
                        'TeacherId' => $Entity->getId()
                    )
                ))->__toString();
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Ausgewählt', 'Lehrer'),
                            ( empty( $tblDivisionTeacherActive )
                                ? new Warning('Keine Lehrer zugewiesen')
                                : new TableData($tblDivisionTeacherActive, null,
                                    array(
                                        'DisplayName'   => 'Name',
                                        'Address'    => 'Adresse',
                                        'Description' => 'Beschreibung',
                                        'Option'      => ''
                                    ))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title('Verfügbar', 'Lehrer'),
                            ( empty( $tblTeacherAvailable )
                                ? new Info('Keine weiteren Lehrer verfügbar')
                                : new TableData($tblTeacherAvailable, null,
                                    array(
                                        'DisplayName'   => 'Name',
                                        'Address'    => 'Adresse',
                                        'Options'   => 'Beschreibung'
                                    ))
                            )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;

    }

    /**
     * @param null $Id
     * @param null $PersonId
     * @param null $Remove
     * @param null $Description
     *
     * @return Stage
     */
    public function frontendCustodyAdd($Id = null, $PersonId = null, $Remove = null, $Description = null)
    {

        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            $Stage = new Stage('Elternvertreter', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }

        $Title = 'der Klasse '.new Bold($tblDivision->getDisplayName());

        $Stage = new Stage('Elternvertreter', $Title);
        $Stage->setMessage('');
//            $Stage->addButton(new Backward());
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $tblDivision->getId())));

        if ($tblDivision && null !== $PersonId && ( $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($PersonId) )) {
            if ($Remove) {
                Division::useService()->removePersonToDivision($tblDivision, $tblPerson);
                $Stage->setContent(
                    new Success('Elternvertreter erfolgreich entfernt')
                    .new Redirect('/Education/Lesson/Division/Custody/Add', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $Id))
                );
                return $Stage;
            } else {
                Division::useService()->addDivisionCustody($tblDivision, $tblPerson, $Description);
                $Stage->setContent(
                    new Success('Elternvertreter erfolgreich hinzugefügt')
                    .new Redirect('/Education/Lesson/Division/Custody/Add', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $Id))
                );
                return $Stage;
            }
        }
        $tblGroup = Group::useService()->getGroupByMetaTable('CUSTODY');
        if ($tblGroup) {
            $tblDivisionGuardianAll = Group::useService()->getPersonAllByGroup($tblGroup);
        } else {
            $tblDivisionGuardianAll = false;
        }
        $tblDivisionGuardianActive = Division::useService()->getCustodyAllByDivision($tblDivision);

        if (is_array($tblDivisionGuardianActive) && is_array($tblDivisionGuardianAll)) {
            $tblGuardianAvailable = array_udiff($tblDivisionGuardianAll, $tblDivisionGuardianActive,
                function (TblPerson $ObjectA, TblPerson $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        } else {
            $tblGuardianAvailable = $tblDivisionGuardianAll;
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (is_array($tblDivisionGuardianActive)) {
            array_walk($tblDivisionGuardianActive, function (TblPerson &$Entity) use ($Id, $tblDivision) {

                /** @noinspection PhpUndefinedFieldInspection */
                $Entity->DisplayName = $Entity->getLastFirstName();
                $Entity->Address = $Entity->fetchMainAddress() ? $Entity->fetchMainAddress()->getGuiString() : '';
                $Entity->Option = new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                        '/Education/Lesson/Division/Custody/Add', new Minus(),
                        array(
                            'Id'       => $Id,
                            'PersonId' => $Entity->getId(),
                            'Remove'   => true
                        ))
                );
                $Entity->Description = Division::useService()->getDivisionCustodyByDivisionAndPerson($tblDivision,
                    $Entity)->getDescription();
            });
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (isset( $tblDivisionGuardianAll ) && !empty( $tblDivisionGuardianAll )) {
            array_walk($tblDivisionGuardianAll, function (TblPerson &$Entity) use ($Id) {

                $Entity->DisplayName = $Entity->getLastFirstName();
                $Entity->Address = $Entity->fetchMainAddress() ? $Entity->fetchMainAddress()->getGuiString() : '';
                $Entity->Options = (new Form(
                    new FormGroup(
                        new FormRow(array(
                            new FormColumn(
                                new TextField('Description', 'z.B.: Stellvertreter', '', new Person()
                                )
                                , 7),
                            new FormColumn(
                                new Primary('Hinzufügen',
                                    new Plus())
                                , 5)
                        ))
                    ), null,
                    '/Education/Lesson/Division/Custody/Add',
                    array(
                        'Id'       => $Id,
                        'PersonId' => $Entity->getId()
                    )
                ))->__toString();
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Ausgewählt', 'Elternvertreter'),
                            ( empty( $tblDivisionGuardianActive )
                                ? new Warning('Keine Elternvertreter zugewiesen')
                                : new TableData($tblDivisionGuardianActive, null,
                                    array(
                                        'DisplayName'   => 'Name',
                                        'Address'    => 'Adresse',
                                        'Description' => 'Beschreibung',
                                        'Option'      => ''
                                    ))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title('Verfügbar', 'Elternvertreter'),
                            ( empty( $tblGuardianAvailable )
                                ? new Info('Keine weiteren Elternvertreter verfügbar')
                                : new TableData($tblGuardianAvailable, null,
                                    array(
                                        'DisplayName'   => 'Name',
                                        'Address'    => 'Adresse',
                                        'Options'   => 'Beschreibung'
                                    ))
                            )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Subject
     * @param null $Remove
     *
     * @return Stage
     */
    public function frontendSubjectAdd($Id = null, $Subject = null, $Remove = null)
    {

        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            $Stage = new Stage('Fächer', 'hinzufügen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        $Title = 'der Klasse '.new Bold($tblDivision->getDisplayName());
        $Stage = new Stage('Fächer', $Title);
        $Stage->setMessage('');
//            $Stage->addButton(new Backward());
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $tblDivision->getId())));

        if ($tblDivision && null !== $Subject && ( $tblSubject = Subject::useService()->getSubjectById($Subject) )) {
            if ($Remove) {
                Division::useService()->removeSubjectToDivision($tblDivision, $tblSubject);
                $Stage->setContent(
                    new Success('Fach erfolgreich entfernt')
                    .new Redirect('/Education/Lesson/Division/Subject/Add', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $Id))
                );
                return $Stage;
            } else {
                Division::useService()->addSubjectToDivision($tblDivision, $tblSubject);
                $Stage->setContent(
                    new Success('Fach erfolgreich hinzugefügt')
                    .new Redirect('/Education/Lesson/Division/Subject/Add', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $Id))
                );
                return $Stage;
            }
        }

        $tblSubjectUsedList = Division::useService()->getSubjectAllByDivision($tblDivision);
        $tblSubjectAll = Subject::useService()->getSubjectAll();

        if (is_array($tblSubjectUsedList)) {
            $tblSubjectAvailable = array_udiff($tblSubjectAll, $tblSubjectUsedList,
                function (TblSubject $ObjectA, TblSubject $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        } else {
            $tblSubjectAvailable = $tblSubjectAll;
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (is_array($tblSubjectUsedList)) {
            array_walk($tblSubjectUsedList, function (TblSubject &$Entity) use ($Id) {

                /** @noinspection PhpUndefinedFieldInspection */
                $Entity->Option = new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                        '/Education/Lesson/Division/Subject/Add', new Minus(),
                        array(
                            'Id'      => $Id,
                            'Subject' => $Entity->getId(),
                            'Remove'  => true
                        ))
                );
            }, $Id);
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (isset( $tblSubjectAvailable )) {
            array_walk($tblSubjectAvailable, function (TblSubject &$Entity) use ($Id) {

                /** @noinspection PhpUndefinedFieldInspection */
                $Entity->Option = new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen',
                        '/Education/Lesson/Division/Subject/Add', new Plus(),
                        array(
                            'Id'      => $Id,
                            'Subject' => $Entity->getId()
                        ))
                );
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Ausgewählt', 'Fach'),
                            ( empty( $tblSubjectUsedList )
                                ? new Warning('Keine Fächer zugewiesen')
                                : new TableData($tblSubjectUsedList, null,
                                    array(
                                        'Acronym'     => 'Kürzel',
                                        'Name'        => 'Name',
                                        'Description' => 'Beschreibung',
                                        'Option'      => ''
                                    ))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title('Verfügbar', 'Fach'),
                            ( empty( $tblSubjectAvailable )
                                ? new Info('Keine weiteren Fächer verfügbar')
                                : new TableData($tblSubjectAvailable, null,
                                    array(
                                        'Acronym'     => 'Kürzel',
                                        'Name'        => 'Name',
                                        'Description' => 'Beschreibung',
                                        'Option'      => ' '
                                    ))
                            )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $DivisionSubjectId
     * @param null $Student
     *
     * @return Stage
     */
    public function frontendSubjectStudentAdd($Id = null, $DivisionSubjectId = null, $Student = null)
    {

        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            $Stage = new Stage('Schüler', 'auswählen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        $tblDivisionSubject = $DivisionSubjectId === null ? false : Division::useService()->getDivisionSubjectById($DivisionSubjectId);
        if (!$tblDivisionSubject) {
            $Stage = new Stage('Schüler', 'auswählen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Fach nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblDivision->getId()));
        }
        $Stage = new Stage('Schüler', 'Klasse '.new Bold($tblDivision->getDisplayName()));
//                $Stage->addButton(new Backward());
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $Id)));
        $Stage->setMessage(new WarningText('"Schüler in Gelb"')
            .' sind bereits in einer anderen Gruppe in diesem Fach angelegt.');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Fach - Gruppe', array(
                                'Fach: '.new Bold($tblDivisionSubject->getServiceTblSubject()
                                    ? $tblDivisionSubject->getServiceTblSubject()->getName() : ''),
                                'Gruppe: '.new Bold($tblDivisionSubject->getTblSubjectGroup()->getName())
                            ), Panel::PANEL_TYPE_INFO)
                        )
                    )
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Division::useService()->addSubjectStudent(
                                    $this->formSubjectStudentAdd($tblDivisionSubject)
                                        ->appendFormButton(new Primary('Speichern', new Save))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $DivisionSubjectId, $Student, $Id
                                )
                            )
                        )
                    ), new Title(new Check().' Zuordnen')
                )
            ));

        return $Stage;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return Form
     */
    public function formSubjectStudentAdd(TblDivisionSubject $tblDivisionSubject)
    {

        $tblSubjectStudentAllSelected = $tblDivisionSubject === null ? false : Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
        if ($tblSubjectStudentAllSelected) {
            $Global = $this->getGlobal();
            array_walk($tblSubjectStudentAllSelected, function (TblSubjectStudent &$tblSubjectStudent) use (&$Global) {

                if ($tblSubjectStudent->getServiceTblPerson()) {
                    $Global->POST['Student'][$tblSubjectStudent->getServiceTblPerson()->getId()] = $tblSubjectStudent->getServiceTblPerson()->getId();
                }
            });
            $Global->savePost();
        }

        if ($tblDivisionSubject->getTblDivision()) {
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionSubject->getTblDivision());  // Alle Schüler der Klasse
        } else {
            $tblStudentList = false;
        }

        if ($tblStudentList) {
            if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                $tblDivisionSubjectControlList = Division::useService()->
                getDivisionSubjectBySubjectAndDivision($tblDivisionSubject->getServiceTblSubject(),
                    $tblDivisionSubject->getTblDivision());
                if ($tblDivisionSubjectControlList) {
                    /** @var TblDivisionSubject $tblDivisionSubjectControl */
                    $PersonId = array();
                    foreach ($tblDivisionSubjectControlList as $tblDivisionSubjectControl) {
                        if ($tblDivisionSubjectControl->getId() !== $tblDivisionSubject->getId()) {
                            $tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectControl);
                            if ($tblSubjectStudentList) {
                                foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                    if ($tblSubjectStudent->getServiceTblPerson()) {
                                        $PersonId[] = $tblSubjectStudent->getServiceTblPerson()->getId();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            foreach ($tblStudentList as &$tblPerson) {
                $trigger = false;
                if (isset( $PersonId )) {
                    foreach ($PersonId as $Person) {

                        if ($Person === $tblPerson->getId()) {
                            $trigger = true;
                        }
                    }
                }
                $tblCourse = Student::useService()->getCourseByPerson($tblPerson);
                $display = $tblPerson->getLastFirstName() . ($tblCourse ? new Small(' (' . $tblCourse->getName() . ')') : '');

                $tblPerson = new CheckBox(
                    'Student['.$tblPerson->getId().']',
                    ( ( $trigger ) ? new WarningText($display)
                        : $display )
                    ,
                    $tblPerson->getId()
                );
            }
        } else {
            $tblStudentList = new Warning('Es sind noch keine Schüler für die Klasse hinterlegt');
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Schüler' . new Small(' (Bildungsgang)') , $tblStudentList, Panel::PANEL_TYPE_INFO)
                        , 12),
                )),
            ))
        );
    }

    /**
     * @param null $Id
     * @param null $DivisionSubjectId
     * @param null $SubjectTeacherId
     * @param null $PersonId
     *
     * @return Stage
     */
    public function frontendSubjectTeacherAdd($Id = null, $DivisionSubjectId = null, $SubjectTeacherId = null, $PersonId = null)
    {

        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            $Stage = new Stage('Fachlehrer', 'auswählen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        $tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId);
        if (!$tblDivisionSubject) {
            $Stage = new Stage('Fachlehrer', 'auswählen');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Fach in der Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblDivision->getId()));
        }

        if ($tblDivisionSubject->getTblSubjectGroup()) {
            $Subject = new Bold($tblDivisionSubject->getServiceTblSubject() ? $tblDivisionSubject->getServiceTblSubject()->getName() : '')
                .' und die Gruppe '.new Bold($tblDivisionSubject->getTblSubjectGroup()->getName());
        } else {
            $Subject = new Bold($tblDivisionSubject->getServiceTblSubject() ? $tblDivisionSubject->getServiceTblSubject()->getName() : '');
        }

        $Stage = new Stage('Fachlehrer ', 'Klasse '.new Bold($tblDivision->getDisplayName()));
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $tblDivision->getId())));

        if ($tblDivision && $tblDivisionSubject){
            if ($SubjectTeacherId !== null
                && ($tblSubjectTeacher = Division::useService()->getSubjectTeacherById($SubjectTeacherId))
            ){
                Division::useService()->removeSubjectTeacher($tblSubjectTeacher);
                $Stage->setContent(
                    new Success('Fachlehrer erfolgreich entfernt')
                    . new Redirect('/Education/Lesson/Division/SubjectTeacher/Add', Redirect::TIMEOUT_SUCCESS,
                        array(
                            'Id' => $tblDivision->getId(),
                            'DivisionSubjectId' => $tblDivisionSubject->getId()
                        )
                    )
                );

                return $Stage;
            } elseif ($PersonId !== null
                && ($tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($PersonId))
            ){
                Division::useService()->addSubjectTeacher($tblDivisionSubject, $tblPerson);
                $Stage->setContent(
                    new Success('Fachlehrer erfolgreich hinzugefügt')
                    . new Redirect('/Education/Lesson/Division/SubjectTeacher/Add', Redirect::TIMEOUT_SUCCESS,
                        array(
                            'Id' => $tblDivision->getId(),
                            'DivisionSubjectId' => $tblDivisionSubject->getId()
                        )
                    )
                );

                return $Stage;
            }
        }

        $tblSubjectTeacherAllSelected = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
        $tblTeacherAllList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER'));

        $tblTeacherSelectedList = array();
        if ($tblSubjectTeacherAllSelected){
            foreach ($tblSubjectTeacherAllSelected as $tblSubjectTeacher){
                if ($tblSubjectTeacher->getServiceTblPerson()){
                    $tblTeacherSelectedList[] = $tblSubjectTeacher->getServiceTblPerson();
                }
            }
        }

        if (!empty($tblTeacherSelectedList) && $tblTeacherAllList) {
            $tblTeacherAllList = array_udiff($tblTeacherAllList, $tblTeacherSelectedList,
                function (TblPerson $ObjectA, TblPerson $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        }

        if ($tblSubjectTeacherAllSelected) {
            $tblTeacherSelectedList = array();
            foreach ($tblSubjectTeacherAllSelected as $tblSubjectTeacher) {

                $tblPerson = $tblSubjectTeacher->getServiceTblPerson();
                if ($tblPerson) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->DisplayName = $tblPerson->getLastFirstName();
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->Address = $tblPerson->fetchMainAddress() ? $tblPerson->fetchMainAddress()->getGuiString() : '';
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->Option = new PullRight(
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                            '/Education/Lesson/Division/SubjectTeacher/Add', new Minus(),
                            array(
                                'Id' => $tblDivision->getId(),
                                'DivisionSubjectId' => $tblDivisionSubject->getId(),
                                'SubjectTeacherId' => $tblSubjectTeacher->getId()
                            ))
                    );

                    $tblTeacherSelectedList[] = $tblPerson;
                }
            }
        }

        if ($tblTeacherAllList) {
             foreach($tblTeacherAllList as $tblPerson){

                 /** @noinspection PhpUndefinedFieldInspection */
                 $tblPerson->DisplayName = $tblPerson->getLastFirstName();
                 /** @noinspection PhpUndefinedFieldInspection */
                 $tblPerson->Address = $tblPerson->fetchMainAddress() ? $tblPerson->fetchMainAddress()->getGuiString() : '';
                 /** @noinspection PhpUndefinedFieldInspection */
                 $tblPerson->Options = new PullRight(
                     new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen',
                         '/Education/Lesson/Division/SubjectTeacher/Add', new Plus(),
                         array(
                             'Id'        => $tblDivision->getId(),
                             'DivisionSubjectId' => $tblDivisionSubject->getId(),
                             'PersonId' => $tblPerson->getId()
                         ))
                 );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                'Fach',
                                $Subject,
                                Panel::PANEL_TYPE_INFO
                            )
                        )
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Ausgewählt', 'Lehrer'),
                            ( empty( $tblTeacherSelectedList )
                                ? new Warning('Kein Lehrer zugewiesen')
                                : new TableData($tblTeacherSelectedList, null,
                                    array(
                                        'DisplayName'   => 'Name',
                                        'Address'    => 'Adresse',
                                        'Option'      => ''
                                    ))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title('Verfügbar', 'Lehrer'),
                            ( empty( $tblTeacherAllList )
                                ? new Info('Keine weiteren Lehrer verfügbar')
                                : new TableData($tblTeacherAllList, null,
                                    array(
                                        'DisplayName'   => 'Name',
                                        'Address'    => 'Adresse',
                                        'Options'   => ' '
                                    ))
                            )
                        ), 6)
                    ))
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null       $Id
     * @param null       $DivisionSubjectId
     * @param null|array $Group
     *
     * @return Stage
     */
    public function frontendSubjectGroupAdd($Id = null, $DivisionSubjectId = null, $Group = null)
    {

        $Stage = new Stage('FachGruppe', 'Übersicht');

        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
//            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        $tblSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getServiceTblSubject();
        if (!$tblSubject) {
//            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
//                array('Id' => $tblDivision->getId())));
            $Stage->setContent(new Warning('Fach nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblDivision->getId()));
        }
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $tblDivision->getId())));
        $Stage->setDescription('Klasse '.new Bold($tblDivision->getDisplayName()));
        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject,
            $tblDivision);
        $TableContent = array();
        if (!empty( $tblDivisionSubjectList )) {
            array_walk($tblDivisionSubjectList,
                function (TblDivisionSubject $tblDivisionSubject) use (&$TableContent, $tblDivision, $tblSubject) {

                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                        $Temp['Name'] = $tblDivisionSubject->getServiceTblSubject() ? $tblDivisionSubject->getServiceTblSubject()->getName() : '';
                        $Temp['Description'] = $tblDivisionSubject->getTblSubjectGroup()->getDescription();
                        if ($tblDivisionSubject->getTblSubjectGroup()) {
                            $Temp['GroupName'] = $tblDivisionSubject->getTblSubjectGroup()->getName();
                        } else {
                            $Temp['GroupName'] = '';
                        }
                        $Temp['Option'] = new Standard('Bearbeiten',
                                '/Education/Lesson/Division/SubjectGroup/Change', new Pencil(),
                                array(
                                    'Id'                => $tblDivisionSubject->getTblSubjectGroup()->getId(),
                                    'DivisionId'        => $tblDivision->getId(),
                                    'SubjectId'         => $tblSubject->getId(),
                                    'DivisionSubjectId' => $tblDivisionSubject->getId()
                                ))
                            .new Standard('Löschen', '/Education/Lesson/Division/SubjectGroup/Remove',
                                new Remove(),
                                array(
                                    'Id'                => $tblDivision->getId(),
                                    'DivisionSubjectId' => $tblDivisionSubject->getId(),
                                    'SubjectGroupId'    => $tblDivisionSubject->getTblSubjectGroup()->getId()
                                ));
                        array_push($TableContent, $Temp);
                    }
                });
            $tblDivisionSubjectList = array_filter($tblDivisionSubjectList);
        }

        $Stage->setContent(
            ( ( !empty( $tblDivisionSubjectList ) ) ?
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Name'        => 'Fach',
                                        'GroupName'   => 'Gruppe',
                                        'Description' => 'Beschreibung',
                                        'Option'      => '',
                                    ), false)
                            )
                        ), new Title(new ListingTable().' Übersicht')
                    )
                ) : null )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Division::useService()->addSubjectToDivisionWithGroup(
                                    $this->formSubjectGroupAdd()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblDivision, $tblSubject, $Group, $DivisionSubjectId)
                            )
                        )
                    ), new Title(new PlusSign().' Hinzufügen einer '.$tblSubject->getName().'-Gruppe')
                )
            )
        );
        return $Stage;
    }

    /**
     * @return Form
     */
    public function formSubjectGroupAdd()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                        new FormColumn(
                            new Panel('Gruppe',
                                new TextField('Group[Name]', '', 'Gruppenname'),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                        new FormColumn(
                            new Panel('Sonstiges',
                                new TextField('Group[Description]', '', 'Beschreibung'),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                    )
                )
            )
        );
    }

    /**
     * @param null $Id
     * @param null $SubjectId
     * @param null $DivisionId
     * @param null $DivisionSubjectId
     * @param null $Group
     *
     * @return Stage
     */
    public function frontendSubjectGroupChange($Id = null, $SubjectId = null, $DivisionId = null, $DivisionSubjectId = null, $Group = null)
    {

        if ($Id === null || $SubjectId === null || $DivisionId === null || $DivisionSubjectId === null) {
            $Stage = new Stage('Gruppe', 'Bearbeiten');
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }

        $Stage = new Stage('Gruppe', 'Bearbeiten');
//        $Stage->addButton(new Backward());
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/SubjectGroup/Add', new ChevronLeft(),
            array(
                'Id'                => $DivisionId,
                'DivisionSubjectId' => $DivisionSubjectId
            )));

        $tblSubjectGroup = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup();
        if ($tblSubjectGroup) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Id'] ) && $DivisionSubjectId) {
                $Global->POST['Group']['Name'] = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup()->getName();
                $Global->POST['Group']['Description'] = Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup()->getDescription();
                $Global->savePost();
            }

            $tblSubject = Subject::useService()->getSubjectById($SubjectId);
            if (!$tblSubject) {
                $Stage->setContent(new Warning('Kein Fach gefunden'));
                return $Stage;
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel('Fach - Gruppe', $tblSubject->getName()
                                    .' - '.Division::useService()->getDivisionSubjectById($DivisionSubjectId)->getTblSubjectGroup()->getName()
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                )
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    Division::useService()->changeSubjectGroup(
                                        $this->formSubjectGroupAdd()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $Group, $Id, $DivisionId, $DivisionSubjectId)
                                )
                            )
                        ), new Title(new Edit().' Bearbeiten')
                    )
                )
            );
        } else {
            $Stage->setContent(new Warning('Keine Gruppe gefunden'));
        }

        return $Stage;
    }

    /**
     * @param $Id
     * @param $DivisionSubjectId
     * @param $SubjectGroupId
     *
     * @return Stage
     */
    public function frontendSubjectGroupRemove($Id = null, $DivisionSubjectId = null, $SubjectGroupId = null)
    {

        $Stage = new Stage('Gruppe', 'entfernen');

        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        $tblDivisionSubject = $DivisionSubjectId === null ? false : Division::useService()->getDivisionSubjectById($DivisionSubjectId);
        if (!$tblDivisionSubject) {
            $Stage->setContent(new Warning('Fach in der Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        $tblSubjectGroup = $SubjectGroupId === null ? false : Division::useService()->getSubjectGroupById($SubjectGroupId);
        if (!$tblSubjectGroup) {
            $Stage->setContent(new Warning('Gruppe in der Klasse nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
            array('Id' => $Id)));
        if (Division::useService()->removeSubjectGroup($tblSubjectGroup, $tblDivisionSubject)) {
            Division::useService()->removeDivisionSubject($tblDivisionSubject);
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Success('Gruppe erfolgreich entfernt')
                .new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $Id)));
        } else {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Danger('Gruppe konnte nicht entfernt werden')
                .new Redirect('/Education/Lesson/Division/Show', Redirect::TIMEOUT_ERROR,
                    array('Id' => $Id)));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Division
     *
     * @return Stage
     */
    public function frontendDivisionChange($Id = null, $Division = null)
    {

        $Stage = new Stage('Klasse', 'Bearbeiten');
        $Stage->addButton(new Backward());
//        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
        $tblDivision = Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            return $Stage.new Danger('Klasse nicht gefunden.', new Ban())
            .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Id'] ) && $tblDivision) {
            $Global->POST['Division']['Name'] = $tblDivision->getName();
            $Global->POST['Division']['Description'] = $tblDivision->getDescription();
            $Global->savePost();
        }

        if (!$tblDivision->getTblLevel()) {
            $PanelShow = new Panel('Beschreibung für', array(
                ( $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '' )
                .' - '.$tblDivision->getDisplayName(),
                $tblDivision->getDescription()
            ), Panel::PANEL_TYPE_SUCCESS);

        } elseif ($tblDivision->getTblLevel()->getName() == '') {
            $PanelShow = new Panel('Beschreibung für', array(
                ( $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '' )
                .' - '.( $tblDivision->getTblLevel()->getServiceTblType() ? $tblDivision->getTblLevel()->getServiceTblType()->getName() : '' )
                .' - '.$tblDivision->getDisplayName(),
                $tblDivision->getDescription()
            ), Panel::PANEL_TYPE_SUCCESS);
        } else {
            $PanelShow = new Panel('Beschreibung für', array(
                ( $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '' )
                .' - '.( $tblDivision->getTblLevel()->getServiceTblType() ? $tblDivision->getTblLevel()->getServiceTblType()->getName() : '' )
                .' - '.$tblDivision->getDisplayName(),
                $tblDivision->getDescription()
            ), Panel::PANEL_TYPE_SUCCESS);
        }

        if ($tblDivision) {
            $Info = new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $PanelShow
                        )
                    )
                )
            );
        } else {
            $Info = null;
        }
        $Stage->setContent($Info.
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Division::useService()->changeDivision(
                                    $this->formDivision()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Division, $Id))
                        )
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formDivision()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                        new FormColumn(new Panel('Gruppe',
                            array(
                                new TextField('Division[Name]', 'zb: Alpha', 'Gruppenname',
                                    new Pencil())
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                        new FormColumn(new Panel('Sonstiges',
                            array(
                                new TextField('Division[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                                    new Pencil())
                            ), Panel::PANEL_TYPE_INFO
                        ), 6)
                    )
                )
            )
        );
    }

    /**
     * @param int $Id
     *
     * @return Stage
     */
    public function frontendDivisionShow($Id = null)
    {

        $Stage = new Stage('Klassenansicht', 'Übersicht');
        $Stage->addButton(new Backward());
//        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->setDescription('Übersicht '.new Bold($tblDivision->getDisplayName()));

            $Stage->setMessage($tblDivision->getDescription());
            $Stage->addButton(new Standard('Fächer', '/Education/Lesson/Division/Subject/Add',
                new Book(), array('Id' => $tblDivision->getId()), 'Auswählen'));
            $Stage->addButton(new Standard('Klassenlehrer', '/Education/Lesson/Division/Teacher/Add',
                new Person(), array('Id' => $tblDivision->getId()), 'Auswählen'));
            $Stage->addButton(new Standard('Elternvertreter', '/Education/Lesson/Division/Custody/Add',
                new Person(), array('Id' => $tblDivision->getId()), 'Auswählen'));
            $Stage->addButton(new Standard('Schüler', '/Education/Lesson/Division/Student/Add',
                new \SPHERE\Common\Frontend\Icon\Repository\Group(), array('Id' => $tblDivision->getId()),
                'Auswählen'));
            $StudentTableCount = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
            $tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentList) {
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    $tblDivisionStudent->FullName = $tblDivisionStudent->getLastFirstName();
                    $tblCommon = Common::useService()->getCommonByPerson($tblDivisionStudent);
                    if ($tblCommon) {
                        $tblDivisionStudent->Birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                    } else {
                        $tblDivisionStudent->Birthday = 'Nicht hinterlegt';
                    }

                    $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($tblDivisionStudent);
                    $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                    if (!empty( $tblAddressAll )) {
                        $tblAddress = current($tblAddressAll)->getGuiString();
                    } else {
                        $tblAddress = false;
                    }
                    if (isset( $tblAddress ) && $tblAddress) {
                        $tblDivisionStudent->Address = $tblAddress;
                    } else {
                        $tblDivisionStudent->Address = new WarningText('Keine Adresse hinterlegt');
                    }

                    $tblCourse = Student::useService()->getCourseByPerson($tblDivisionStudent);
                    $tblDivisionStudent->Course = $tblCourse ? $tblCourse ->getName() : '';
                }
            } else {
                $tblDivisionStudentList = array();
            }
            $tblPersonList = Division::useService()->getTeacherAllByDivision($tblDivision);
            if ($tblPersonList) {
                $TeacherList = array();
                foreach ($tblPersonList as &$tblPerson) {
                    $Description = Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision,
                        $tblPerson)->getDescription();
                    $TeacherList[] = $tblPerson->getFullName().' '.new Muted($Description);
                }
                $tblPersonList = new Panel('Klassenlehrer', $TeacherList, Panel::PANEL_TYPE_INFO);
            } else {
                $tblPersonList = new Warning('Kein Klassenlehrer festgelegt');
            }
            $tblCostodyList = Division::useService()->getCustodyAllByDivision($tblDivision);
            if ($tblCostodyList) {
                $CostodyList = array();
                /** @var TblPerson $tblPerson */
                foreach ($tblCostodyList as &$tblPerson) {
                    $Description = Division::useService()->getDivisionCustodyByDivisionAndPerson($tblDivision,
                        $tblPerson)->getDescription();
                    $CostodyList[] = $tblPerson->getFullName().' '.new Muted($Description);
                }
                $tblCostodyList = new Panel('Elternvertreter', $CostodyList, Panel::PANEL_TYPE_INFO);
            } else {
                $tblCostodyList = new Warning('Kein Elternvertreter festgelegt');
            }
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);

            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as $Index => $tblDivisionSubject) {
                    if ($tblDivisionSubject->getTblSubjectGroup()) {
                        $tblDivisionSubjectList[$Index] = false;
                    }
                }
                $tblDivisionSubjectList = array_filter($tblDivisionSubjectList);

                /** @var TblDivisionSubject $row */
                foreach ($tblDivisionSubjectList as $key => $row) {
                    $name[$key] = strtoupper($row->getServiceTblSubject() ? $row->getServiceTblSubject()->getName() : '');
                    $Acronym[$key] = strtoupper($row->getServiceTblSubject() ? $row->getServiceTblSubject()->getAcronym() : '');
                }
                array_multisort($name, SORT_ASC, $Acronym, SORT_ASC, $tblDivisionSubjectList);

                /** @var TblDivisionSubject $tblDivisionSubject */
                foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {

                    $tblDivisionSubject->GroupTeacher = '';
//                    $tblDivisionSubject->Student = new Panel('Alle Schüler','aus der Klasse',Panel::PANEL_TYPE_INFO);
                    $tblDivisionSubject->Student = '';

                    $tblDivisionSubject->Subject = $tblDivisionSubject->getServiceTblSubject() ? new Panel($tblDivisionSubject->getServiceTblSubject()
                        ? $tblDivisionSubject->getServiceTblSubject()->getAcronym()
                        . ' - ' . $tblDivisionSubject->getServiceTblSubject()->getName()
                        . ($tblDivisionSubject->getServiceTblSubject()->getDescription()
                            ? ' - ' . new Small($tblDivisionSubject->getServiceTblSubject()->getDescription())
                            : '')
                        : '',
                        $StudentTableCount.' / '.$StudentTableCount.' Schüler aus der Klasse',
                        Panel::PANEL_TYPE_INFO) : '';

                    $tblDivisionTeachersList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
                    $TeacherArray = array();
                    if ($tblDivisionTeachersList) {
                        foreach ($tblDivisionTeachersList as $tblDivisionTeachers) {
                            if ($tblDivisionTeachers->getServiceTblPerson()) {
                                $TeacherArray[] = $tblDivisionTeachers->getServiceTblPerson()->getFullName();
                            }
                        }
                    }
                    $SubjectTeacherPanel = new Panel('Fachlehrer', $TeacherArray, Panel::PANEL_TYPE_INFO,
                        new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add', new Pencil(),
                            array(
                                'Id'                => $tblDivision->getId(),
                                'DivisionSubjectId' => $tblDivisionSubject->getId()
                            ), 'Fachlehrer festlegen'));

                    if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                        $tblDivisionSubjectTestList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblDivisionSubject->getServiceTblSubject(),
                            $tblDivisionSubject->getTblDivision());
                    } else {
                        $tblDivisionSubjectTestList = false;
                    }

                    if (count($tblDivisionSubjectTestList) > 1) {
                        $Grouparray = array();
                        $TeacherPanelArray = '';
                        $TeacherGroupList = array(new Bold('Gruppenlehrer:'));
                        $StudentsGroupCount = 0;
                        $StudentPanel = '';
                        /** @var TblDivisionSubject $tblDivisionSubjectTest */
                        foreach ($tblDivisionSubjectTestList as $tblDivisionSubjectTest) {
                            if ($tblDivisionSubjectTest->getTblSubjectGroup()) {
                                $TeachersArray = array();
                                $StudentArray = array();
                                $tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectTest);
                                if ($tblSubjectTeacherList) {
                                    foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                        if ($tblSubjectTeacher->getServiceTblPerson()) {
                                            $TeachersArray[] = $tblSubjectTeacher->getServiceTblPerson()->getFullName();
                                        }
                                    }
                                }
                                if ($TeachersArray) {
                                    foreach ($TeachersArray as $Teachers) {
                                        $TeacherGroupList[] = $Teachers;
                                    }
                                }
                                $TeacherPanelArray .= New Panel($tblDivisionSubjectTest->getTblSubjectGroup()->getName(),
                                    $TeachersArray, Panel::PANEL_TYPE_INFO,
                                    new Standard('Lehrer', '/Education/Lesson/Division/SubjectTeacher/Add',
                                        new Pencil(),
                                        array(
                                            'Id'                => $tblDivision->getId(),
                                            'DivisionSubjectId' => $tblDivisionSubjectTest->getId()
                                        ), 'Gruppenlehrer festlegen'));
                                $Grouparray[] = $tblDivisionSubjectTest->getTblSubjectGroup()->getName();

                                $tblSubjectStudentsList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectTest);
                                if ($tblSubjectStudentsList) {
                                    /** @var TblSubjectStudent $tblSubjectStudents */
                                    foreach ($tblSubjectStudentsList as $tblSubjectStudents) {
                                        if ($tblSubjectStudents->getServiceTblPerson()) {
                                            $StudentArray[] = $tblSubjectStudents->getServiceTblPerson()->getLastFirstName();
                                            $StudentsGroupCount = $StudentsGroupCount + 1;
                                        }
                                    }
                                }
                                $StudentPanel .= new Panel($tblDivisionSubjectTest->getTblSubjectGroup()->getName(),
                                    $StudentArray, Panel::PANEL_TYPE_INFO,
                                    new Standard('Schüler', '/Education/Lesson/Division/SubjectStudent/Add',
                                        new Pencil(),
                                        array(
                                            'Id'                => $tblDivision->getId(),
                                            'DivisionSubjectId' => $tblDivisionSubjectTest->getId()
                                        ), 'Schüler zuordnen'));
                            }
                        }

                        if ($StudentTableCount > $StudentsGroupCount && $tblDivisionSubject->getServiceTblSubject()) {
                            $tblDivisionSubject->Subject = new Panel($tblDivisionSubject->getServiceTblSubject()
                                ? $tblDivisionSubject->getServiceTblSubject()->getAcronym()
                                . ' - ' . $tblDivisionSubject->getServiceTblSubject()->getName()
                                . ($tblDivisionSubject->getServiceTblSubject()->getDescription()
                                    ? ' - ' . new Small($tblDivisionSubject->getServiceTblSubject()->getDescription())
                                    : '')
                                :'',
                                new WarningText($StudentsGroupCount.' / '.$StudentTableCount.' Schüler aus der Klasse'),
                                Panel::PANEL_TYPE_INFO);
                        }

                        $tblDivisionSubject->Group = new Panel('Gruppen', $Grouparray, Panel::PANEL_TYPE_INFO,
                            new Standard('Gruppen', '/Education/Lesson/Division/SubjectGroup/Add', new Pencil(),
                                array(
                                    'Id'                => $tblDivision->getId(),
                                    'DivisionSubjectId' => $tblDivisionSubject->getId()
                                ), 'Gruppen bearbeiten'));

                        $tblDivisionSubject->GroupTeacher = $TeacherPanelArray;
                        $tblDivisionSubject->SubjectTeacher = $SubjectTeacherPanel;
                        $tblDivisionSubject->Student = (new Accordion())
                            ->addItem('Enthaltene Schüler', $StudentPanel, false);
                    } else {
                        $tblDivisionSubject->Group = new Panel('Gruppen', '', Panel::PANEL_TYPE_INFO,
                            new Standard('Gruppe', '/Education/Lesson/Division/SubjectGroup/Add', new Plus(),
                                array(
                                    'Id'                => $tblDivision->getId(),
                                    'DivisionSubjectId' => $tblDivisionSubject->getId()
                                ), 'Gruppe erstellen'));

                        $tblDivisionSubject->SubjectTeacher = $SubjectTeacherPanel;
                    }
                }

            } else {
                $tblDivisionSubjectList = array();
            }
            $TitleClass = new \SPHERE\Common\Frontend\Icon\Repository\Group().' Schüler in der Klasse '.$tblDivision->getDisplayName();

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                ( ( !empty( $tblDivisionStudentList ) ) ?
                                    new TableData($tblDivisionStudentList, null
                                        , array(
//                                            'LastName'  => 'Nachname',
//                                            'FirstName' => 'Vorname',
                                            'FullName' => 'Schüler',
                                            'Address'  => 'Adresse',
                                            'Birthday' => 'Geburtsdatum',
                                            'Course' => 'Bildungsgang',
                                        ), false)
                                    : new Warning('Keine Schüer der Klasse zugewiesen') )
                            ,
                            ), 6),
                            new LayoutColumn($tblPersonList, 5),
                            new LayoutColumn($tblCostodyList, 5)
                        )), new Title($TitleClass)
                    )
                ).
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                ( ( !empty( $tblDivisionSubjectList ) ) ?
                                    new TableData($tblDivisionSubjectList, null,
                                        array(
                                            'Subject'        => 'Fach',
                                            'SubjectTeacher' => 'Fachlehrer',
                                            'Group'          => 'Gruppen',
                                            'GroupTeacher'   => 'Gruppenlehrer',
                                            'Student'        => 'Gruppen Schüler',
                                        ), array("bPaginate" => false))
                                    :
                                    new Warning('Keine Fächer der Klasse zugewiesen') )
                            )
                        ), new Title(new Book().' Fächer')
                    )
                )
            );
        } else {
            $Stage->setContent(new Warning('Klasse nicht gefunden'));
        }

        return $Stage;
    }

    /**
     * @param int $Id
     *
     * @return Stage
     */
    public function frontendSubjectTeacherShow($Id)
    {

        $Stage = new Stage('Lehrer', 'Auswahl');
        $tblDivision = Division::useService()->getDivisionById($Id);
        if ($tblDivision) {
            $Stage->addButton(new Backward());
//            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division/Show', new ChevronLeft(),
//                array('Id' => $tblDivision->getId())));
            $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
            if ($tblDivisionSubjectList) {
                foreach ($tblDivisionSubjectList as &$tblDivisionSubject) {

                    $tblDivisionSubject->Name = $tblDivisionSubject->getServiceTblSubject()
                        ? $tblDivisionSubject->getServiceTblSubject()->getName() : '';
                    $tblDivisionSubject->Acronym = $tblDivisionSubject->getServiceTblSubject()
                        ? $tblDivisionSubject->getServiceTblSubject()->getAcronym() : '';
                    $tblDivisionSubject->Option = new Standard('', '/Education/Lesson/Division/SubjectTeacher/Add',
                        new Plus(), array(
                            'Id'                => $Id,
                            'DivisionTeacherId' => $tblDivisionSubject->getId()
                        ));

                    $tblTeacherList = Division::useService()->getTeacherAllByDivisionSubject($tblDivisionSubject);
                    $teacherString = new Danger('leer');
                    $teacherArray = array();
                    if ($tblTeacherList) {
                        /** @var TblPerson $Teacher */
                        foreach ($tblTeacherList as $Teacher) {
                            $teacherArray[] = $Teacher->getLastFirstName();
                        }
                        $teacherString = implode(', ', $teacherArray);
                    }
                    $tblDivisionSubject->Teacher = $teacherString;
                }
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($tblDivisionSubjectList, null, array(
                                        'Acronym' => 'Kürzel',
                                        'Name'    => 'Name',
                                        'Teacher' => 'Lehrer',
                                        'Option'  => 'Lehrer Zuweisung'
                                    )
                                )
                            )
                        )
                    )
                )
            );
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Warning('Klasse nicht gefunden')
                            )
                        )
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param int        $Id
     * @param bool|false $Confirm
     *
     * @return Stage|string
     */
    public function frontendDivisionDestroy($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Klasse', 'Löschen');
        if ($Id && ( $tblDivision = Division::useService()->getDivisionById($Id) )) {
            if (!$Confirm) {

                $tblLevel = $tblDivision->getTblLevel();
                $StudentInt = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                $TeacherInt = Division::useService()->countDivisionTeacherAllByDivision($tblDivision);
                $CustodyInt = Division::useService()->countDivisionCustodyAllByDivision($tblDivision);
                if ($StudentInt > 0) {
                    $StudentInt = new Danger($StudentInt);
                }
                if ($TeacherInt > 0) {
                    $TeacherInt = new Danger($TeacherInt);
                }
                if ($CustodyInt > 0) {
                    $CustodyInt = new Danger($CustodyInt);
                }

                $Content[] = 'Jahr: '.new Bold($tblDivision->getServiceTblYear()
                        ? $tblDivision->getServiceTblYear()->getDisplayName() : '');
                $Content[] = 'Typ: '.new Bold($tblLevel->getServiceTblType()
                        ? $tblLevel->getServiceTblType()->getName() : '');
                $Content[] = 'Stufe: '.new Bold($tblLevel->getName());
                $Content[] = 'Gruppe: '.new Bold($tblDivision->getName());
                $Content[] = 'Klassenbezeichnung: '.new Bold($tblDivision->getDisplayName());
                $Content[] = 'Beschreibung: '.new Bold($tblDivision->getDescription());
                $Content2[] = 'Schüler: '.new Bold($StudentInt);
                $Content2[] = 'Klassenlehrer: '.new Bold($TeacherInt);
                $Content2[] = 'Elternvertreter: '.new Bold($CustodyInt);
                $Content2[] = 'Fächer: '.new Bold(Division::useService()->countDivisionSubjectAllByDivision($tblDivision));

                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel(new Question().' Diese Klasse wirklich löschen?',
                                        $Content, Panel::PANEL_TYPE_DANGER,
                                        new Standard(
                                            'Ja', '/Education/Lesson/Division/Destroy', new Ok(),
                                            array('Id' => $Id, 'Confirm' => true))
                                        .new Standard('Nein', '/Education/Lesson/Division', new Disable())
                                    )
                                    , 6),
                                new LayoutColumn(
                                    new Panel(new \SPHERE\Common\Frontend\Icon\Repository\Info().' Beinhaltet:',
                                        $Content2
                                        ,
                                        Panel::PANEL_TYPE_DANGER
                                    )
                                    , 6),
                            ))
                        )
                    )
                );
            } else {
                // Destroy Division
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Division::useService()->destroyDivision($tblDivision)
                                ? new Success('Die Klasse wurde gelöscht',
                                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                                .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_SUCCESS)
                                : new \SPHERE\Common\Frontend\Message\Repository\Danger('Die Klasse konnte nicht gelöscht werden',
                                    new Ban())
                                .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            return $Stage.new Warning('Klasse nicht gefunden!', new Ban())
            .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Division
     * @param null $Level
     *
     * @return Stage
     */
    public function frontendCopyDivision($Id = null, $Division = null, $Level = null)
    {

        $Stage = new Stage('Klasse', 'Kopieren');
        $Stage->addButton(new Backward());
//        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Division', new ChevronLeft()));
        $tblDivision = $Id === null ? false : Division::useService()->getDivisionById($Id);
        if (!$tblDivision) {
            return $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Danger('Klasse nicht gefunden.',
                new Ban()))
            .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }
        $tblLevel = $tblDivision->getTblLevel();
        if (!$tblLevel) {
            return $Stage->setContent(new Warning('zugehörige Schulart / Klassenstufe fehlt'))
            .new Redirect('/Education/Lesson/Division', Redirect::TIMEOUT_ERROR);
        }

        $Content[] = 'Typ: '.new Bold($tblLevel->getServiceTblType()
                ? $tblLevel->getServiceTblType()->getName() : '');
//        $Content[] = 'Stufe: ' . new Bold($tblLevel->getName());
        $Content[] = 'Klassenbezeichnung: '.new Bold($tblDivision->getDisplayName());
        $Content1[] = 'Jahr: '.new Bold($tblDivision->getServiceTblYear()
                ? $tblDivision->getServiceTblYear()->getDisplayName() : '');
//        $Content1[] = 'Gruppe: ' . new Bold($tblDivision->getName());
        $Content1[] = 'Beschreibung: '.new Bold($tblDivision->getDescription());
        $Content2[] = 'Schüler: '.new Bold(Division::useService()->countDivisionStudentAllByDivision($tblDivision));
        $Content2[] = 'Klassenlehrer: '.new Bold(Division::useService()->countDivisionTeacherAllByDivision($tblDivision));
        $Content2[] = 'Elternvertreter: '.new Bold(Division::useService()->countDivisionCustodyAllByDivision($tblDivision));
        $Content2[] = 'Fächer: '.new Bold(Division::useService()->countDivisionSubjectAllByDivision($tblDivision));

        if (is_numeric($tblLevel->getName())) {
            $length = strlen($tblLevel->getName());
            if ($Zahl = (int)( $tblLevel->getName() )) {
                $Summary = $Zahl + 1;
                $Summary = str_pad($Summary, $length, 0, STR_PAD_LEFT);
                $tblLevel->setName($Summary);
            }
        } else {
            $str = $tblLevel->getName();
            $letterList = preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
            $Number = array();
            if (!empty( $letterList )) {
                foreach ($letterList as $key => $letter) {
                    if (is_numeric($letter)) {
                        $Replace[] = true;
                        $Number[] = $letter;
                    } else {
                        $Replace[] = false;
                    }
                }
                $Number = implode('', $Number);

                $length = strlen($Number);
                if ($Zahl = (int)( $Number )) {
                    $Number = $Zahl + 1;
                    $Number = str_pad($Number, $length, 0, STR_PAD_LEFT);
                }
                if ($letterList && !empty( $Replace )) {
                    $i = 0;
                    foreach ($letterList as $Key => &$singleLetter) {
                        if ($Replace[$Key]) {
                            $singleLetter = $Number[$i];
                            $i++;
                        }
                    }
                    $str = implode('', $letterList);
                    $tblLevel->setName($str);
                }
            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
//                            new Well(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                            new LayoutColumn(
                                                new Panel('Zu kopierende Klassenstufe:',
                                                    $Content, Panel::PANEL_TYPE_INFO)
                                                , 6),
                                            new LayoutColumn(
                                                new Panel('Zu kopierende Klassengruppe:',
                                                    $Content1, Panel::PANEL_TYPE_INFO)
                                                , 6),
                                        )
                                    )
                                )
                            )
//                            )
                            , 8),
                        new LayoutColumn(
                            new Panel('Anzahl Personen und Fächer:',
                                $Content2, Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                    ))
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Division::useService()->copyDivision(
                                $this->formLevelDivision($tblLevel, $tblDivision, true)
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblDivision, $Level, $Division
                            )
                        ))
                    ), new Title(new MoreItems().' Kopie erstellen')
                )
            )
        );

        return $Stage;
    }
}
