<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use SPHERE\Application\Api\Education\ClassRegister\ApiDiary;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\ClassRegister\Diary
 */
class Frontend extends Extension implements IFrontendInterface
{
    const BASE_ROUTE = '/Education/Diary';

    /**
     * @return Stage
     */
    public function frontendSelectDivision()
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
        } else {
            return $this->frontendTeacherSelectDivision();
        }
    }

    /**
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherSelectDivision($IsAllYears = false, $IsGroup = false, $YearId = null)
    {

        $Stage = new Stage('pädagogisches Tagebuch', 'Klasse auswählen');
        $this->setHeaderButtonList($Stage, View::TEACHER);

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $buttonList = Prepare::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Teacher',
            $IsAllYears, $IsGroup, $YearId, $tblYear, false);

        $table = false;
        $divisionTable = array();
        if ($tblPerson) {
            if ($IsGroup) {
                if (($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                    && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
                ) {
                    if (($tblGroupAll = Group::useService()->getTudorGroupAll($tblPerson))) {
                        foreach ($tblGroupAll as $tblGroup) {
                            $divisionTable[] = array(
                                'Group' => $tblGroup->getName(),
                                'Option' => new Standard(
                                    '', self::BASE_ROUTE . '/Selected', new Select(),
                                    array(
                                        'GroupId' => $tblGroup->getId(),
                                        'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }

                $table = new TableData($divisionTable, null, array(
                    'Group' => 'Gruppe',
                    'Option' => ''
                ), array(
                    'order' => array(
                        array('0', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 0)
                    ),
                ));
            } else {
                if (($tblDivisionList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson))) {
                    foreach ($tblDivisionList as $tblDivisionTeacher) {
                        $tblDivision = $tblDivisionTeacher->getTblDivision();

                        // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                        /** @var TblYear $tblYear */
                        if ($tblYear && $tblDivision && $tblDivision->getServiceTblYear()
                            && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                        ) {
                            continue;
                        }

                        $divisionTable[] = array(
                            'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Option' => new Standard(
                                '',  self::BASE_ROUTE . '/Selected', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                                ),
                                'Auswählen'
                            )
                        );
                    }
                }

                $table = new TableData($divisionTable, null, array(
                    'Year' => 'Schuljahr',
                    'Type' => 'Schulart',
                    'Division' => 'Klasse',
                    'Option' => ''
                ), array(
                    'order' => array(
                        array('0', 'desc'),
                        array('1', 'asc'),
                        array('2', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 2)
                    ),
                ));
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        $table
                            ? new LayoutColumn(array($table))
                            : null
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision($IsAllYears = false, $IsGroup = false, $YearId = null)
    {

        $Stage = new Stage('pädagogisches Tagebuch', 'Klasse auswählen');
        $this->setHeaderButtonList($Stage, View::HEADMASTER);

        $tblDivisionList = Division::useService()->getDivisionAll();

        $buttonList = Prepare::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Headmaster',
            $IsAllYears, $IsGroup, $YearId, $tblYear);

        $divisionTable = array();
        if ($IsGroup) {
            // tudorGroups
            if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroup) {
                    $divisionTable[] = array(
                        'Group' => $tblGroup->getName(),
                        'Option' => new Standard(
                            '', self::BASE_ROUTE . '/Selected', new Select(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                                'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
                            ),
                            'Auswählen'
                        )
                    );
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Group' => 'Gruppe',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0)
                ),
            ));
        } else {
            if ($tblDivisionList) {
                foreach ($tblDivisionList as $tblDivision) {
                    // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                    /** @var TblYear $tblYear */
                    if ($tblYear && $tblDivision && $tblDivision->getServiceTblYear()
                        && $tblDivision->getServiceTblYear()->getId() != $tblYear->getId()
                    ) {
                        continue;
                    }

                    if ($tblDivision) {
                        $divisionTable[] = array(
                            'Year' => $tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Option' => new Standard(
                                '', self::BASE_ROUTE . '/Selected', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
                                ),
                                'Auswählen'
                            )
                        );
                    }
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Year' => 'Schuljahr',
                'Type' => 'Schulart',
                'Division' => 'Klasse',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                    array('2', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 2)
                ),
            ));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn($table)
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param int $view
     */
    private function setHeaderButtonList(Stage $Stage, $view)
    {
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Diary/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Diary/Headmaster');

        $countRights = 0;
        if ($hasTeacherRight) {
            $countRights++;
        }
        if ($hasHeadmasterRight) {
            $countRights++;
        }

        if ($countRights > 1) {
            if ($hasTeacherRight) {
                if ($view == View::TEACHER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                        self::BASE_ROUTE . '/Teacher', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Lehrer',
                        self::BASE_ROUTE . '/Teacher'));
                }
            }
            if ($hasHeadmasterRight) {
                if ($view == View::HEADMASTER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                        self::BASE_ROUTE . '/Headmaster', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Leitung',
                        self::BASE_ROUTE . '/Headmaster'));
                }
            }
        }
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendDiary(
        $DivisionId = null,
        $GroupId = null,
        $BasicRoute = '/Education/Diary/Teacher'
    ) {
        $stage = new Stage('Klassenbuch', 'pädagogisches Tagebuch');

        $tblPerson = false;
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount))
        ) {
            $tblPerson = $tblPersonAllByAccount[0];
        }

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            ));

            $tblYear = $tblDivision->getServiceTblYear();

            $receiver = ApiDiary::receiverBlock($this->loadDiaryTable($tblDivision, null), 'DiaryContent');

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 6),
                            new LayoutColumn(
                                new Panel(
                                    'Schuljahr',
                                    $tblYear ? $tblYear->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 6),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                $tblPerson
                                    ? ApiDiary::receiverModal()
                                    . (new Primary(
                                        new Plus() . ' Eintrag hinzufügen',
                                        ApiDiary::getEndpoint()
                                    ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenCreateDiaryModal($tblDivision->getId(),
                                        null))
                                    : new Warning('Für Ihren Account ist keine Person ausgewählt. 
                                    Sie können keine neuen Einträge zum pädagogischen Tagebuch hinzufügen',
                                    new Exclamation())
                            ),
                            new LayoutColumn(
                                $receiver
                            )
                        ))
                    ))
                ))
            );
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            ));

            if (($tblYearList = Term::useService()->getYearByNow())) {
                $tblYear = reset($tblYearList);
            } else {
                $tblYear = false;
            }

            $receiver = ApiDiary::receiverBlock($this->loadDiaryTable(null, $tblGroup), 'DiaryContent');

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Gruppe',
                                    $tblGroup->getName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 6),
                            new LayoutColumn(
                                new Panel(
                                    'Schuljahr',
                                    $tblYear ? $tblYear->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 6),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                ApiDiary::receiverModal()
                                . (new Primary(
                                    new Plus() . ' Eintrag hinzufügen',
                                    ApiDiary::getEndpoint()
                                ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenCreateDiaryModal(null, $tblGroup->getId()))
                            ),
                            new LayoutColumn(
                                $receiver
                            )
                        ))
                    ))
                ))
            );

        } else {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            ));

            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param null $DiaryId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formDiary(TblDivision $tblDivision = null, TblGroup $tblGroup = null, $DiaryId = null, $setPost = false)
    {
        $setStudents = array();
        if ($DiaryId && ($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Date'] = $tblDiary->getDate();
                // im Chrome automatisches autocomplete von Ländern (welche im Browser eingetragen wurden, Formulardaten)
                $Global->POST['Data']['Place'] = $tblDiary->getLocation();
                $Global->POST['Data']['Subject'] = $tblDiary->getSubject();
                $Global->POST['Data']['Content'] = $tblDiary->getContent();

                if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
                    foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                        if (($tblPersonItem = $tblDiaryStudent->getServiceTblPerson())) {
                            $Global->POST['Data']['Students'][$tblPersonItem->getId()] = 1;
                            $setStudents[$tblPersonItem->getId()] = $tblPersonItem;
                        }
                    }
                }

                $Global->savePost();
            }
        }

        if ($DiaryId) {
            $saveButton = (new Primary('Speichern', ApiDiary::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDiary::pipelineEditDiarySave($DiaryId));
        } else {
            $saveButton = (new Primary('Speichern', ApiDiary::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDiary::pipelineCreateDiarySave(
                    $tblDivision ? $tblDivision->getId() : null,
                    $tblGroup ? $tblGroup->getId() : null
                ));
        }

        $columns = array();
        if ($tblDivision) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        } elseif ($tblGroup) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            }

        } else {
            $tblPersonList = false;
        }
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $columns[$tblPerson->getId()] = new FormColumn(new CheckBox('Data[Students][' . $tblPerson->getId() . ']',
                    $tblPerson->getLastFirstName(), 1), 4);
            }
        }
        // deaktivierte ausgewählte Schüler hinzufügen
        if (!empty($setStudents)) {
            foreach ($setStudents as $personId => $tblStudent) {
                if (!isset($columns[$personId])) {
                    $columns[$tblStudent->getId()] = new FormColumn(new CheckBox('Data[Students][' . $tblStudent->getId() . ']',
                        new Strikethrough($tblStudent->getLastFirstName()), 1), 4);
                }
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                    , 6),
                    new FormColumn(
                        new TextField('Data[Place]', '', 'Ort')
                    , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Subject]', 'Titel', 'Titel', new Calendar())
                    ),
                )),
                new FormRow(
                    $columns
                ),
                new FormRow(array(
                    new FormColumn(
                        new TextArea('Data[Content]', 'Bemerkungen', 'Bemerkungen', new Edit())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return TableData
     */
    public function loadDiaryTable(TblDivision $tblDivision = null, TblGroup $tblGroup = null)
    {
        $dataList = array();
        $diaryList = array();

        if ($tblDivision) {
            // Klasseneinträge inklusive der Einträge der verkünften Vorgänger-Klassen
            if (($tblDiaryList = Diary::useService()->getDiaryAllByDivision($tblDivision, true))) {
                foreach ($tblDiaryList as $tblDiary) {
                    $diaryList[$tblDiary->getId()] = $tblDiary;
                }
            }
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        } elseif ($tblGroup) {
            // Gruppeneinträge
            if (($tblDiaryList = Diary::useService()->getDiaryAllByGroup($tblGroup))) {
                foreach ($tblDiaryList as $tblDiary) {
                    $diaryList[$tblDiary->getId()] = $tblDiary;
                }
            }
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        } else {
            $tblPersonList = false;
        }

        // zusätzliche Schülereintrage (z.B. vom Klassenwechsel)
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblDiaryListByStudent = Diary::useService()->getDiaryAllByStudent($tblPerson))) {
                    foreach ($tblDiaryListByStudent as $item) {
                        if (!isset($diaryList[$item->getId()])) {
                            $diaryList[$item->getId()] = $item;
                        }
                    }
                }
            }
        }

        // sortieren nach Datum
        $diaryList = $this->getSorter($diaryList)->sortObjectBy('Date', new DateTimeSorter(), Sorter::ORDER_DESC);
        $count = 0;
        /** @var TblDiary $tblDiaryItem */
        foreach ($diaryList as $tblDiaryItem) {
            $count++;
            $dataList[] = $this->setDiaryItem($tblDiaryItem, $count);
        }

        return new TableData(
            $dataList,
            null,
            array(
                'Number' => '#',
                'Information' => 'Information',
                'PersonList' => 'Schüler',
                'Content' => 'Inhalt',
                'Options' => ' '
            ),
            array(
                'order' => array(
                    array(0, 'asc')
                ),
                'responsive' => false
            )
        );
    }

    /**
     * @param TblDiary $tblDiary
     * @param int $count
     *
     * @return array
     */
    private function setDiaryItem(TblDiary $tblDiary, &$count)
    {
        $displayPerson = '';
        if (($tblPerson = $tblDiary->getServiceTblPerson())) {
            if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
                && ($acronym = $tblTeacher->getAcronym())
            ) {
                $displayPerson = $acronym;
            } else {
                $displayPerson = $tblPerson->getLastName();
            }
        }

        $personList = array();
        if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
            foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                if (($tblPersonItem = $tblDiaryStudent->getServiceTblPerson())) {
                    $personList[] = $tblPersonItem->getLastFirstName();
                }
            }
        }

        return array(
            'Number' => $count,
            'Information' => $tblDiary->getDate()
                . (($tblDivision = $tblDiary->getServiceTblDivision()) ? '<br>' . $tblDivision->getDisplayName() : '')
                . (($tblGroup = $tblDiary->getServiceTblGroup()) ? '<br>' . $tblGroup->getName() : '')
                . (($tblYear = $tblDiary->getServiceTblYear()) ? ' (' . $tblYear->getName() . ')' : '')
                . (($location = $tblDiary->getLocation()) ? '<br>' . $location : '')
                . '<br>' . $displayPerson,
            'PersonList' => empty($personList) ? '' : implode('<br>', $personList),
            'Content' => new Bold($tblDiary->getSubject())
                . '<br><br>'
                // Zeilenumbrüche berücksichtigen
                . str_replace("\n", '<br>', $tblDiary->getContent()),
            'Options' =>
                (new Standard(
                    '',
                    ApiDiary::getEndpoint(),
                    new Edit(),
                    array(),
                    'Bearbeiten'
                ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenEditDiaryModal($tblDiary->getId()))
                . (new Standard(
                    '',
                    ApiDiary::getEndpoint(),
                    new Remove(),
                    array(),
                    'Löschen'
                ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenDeleteDiaryModal($tblDiary->getId()))
        );
    }
}