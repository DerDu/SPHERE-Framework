<?php
namespace SPHERE\Application\People\Group;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person as PeoplePerson;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null|array $Group
     *
     * @return Stage
     */
    public function frontendGroup($Group = null)
    {

        $Stage = new Stage('Gruppen', 'Übersicht');
        $Stage->addButton(new Backward());

        $tblGroupAll = Group::useService()->getGroupAll();
        if ($tblGroupAll) {
            array_walk($tblGroupAll, function (TblGroup &$tblGroup) {

                $Content = array(
                    ($tblGroup->getDescription() ? new Small(new Muted($tblGroup->getDescription())) : false),
                    ($tblGroup->getRemark() ? nl2br($tblGroup->getRemark()) : false),
                );
                $Content = array_filter($Content);
                $Type = ($tblGroup->isLocked() ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_DEFAULT);
                $Footer = new PullLeft(
                    new Standard('', '/People/Group/Edit', new Edit(),
                        array('Id' => $tblGroup->getId()), 'Daten ändern'
                    )
                    . ($tblGroup->getMetaTable() !== 'COMMON'
                        ? new Standard('', '/People/Group/Person/Add', new PersonGroup(),
                            array('Id' => $tblGroup->getId()), 'Personen zuweisen'
                        )
                        : ''
                    )
                    . ($tblGroup->isLocked()
                        ? ''
                        : new Standard('', '/People/Group/Destroy', new Remove(),
                            array('Id' => $tblGroup->getId()), 'Gruppe löschen'
                        )
                    )
                );
                $Footer .= new PullRight(
                    new Label(PeoplePerson::useService()->countPersonAllByGroup($tblGroup) . ' Personen',
                        Label::LABEL_TYPE_INFO)
                );
                $tblGroup = new LayoutColumn(
                    new Panel($tblGroup->getName(), $Content, $Type, new PullClear($Footer))
                    , 4);
            });

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblGroup
             */
            foreach ($tblGroupAll as $tblGroup) {
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblGroup);
                $LayoutRowCount++;
            }
        } else {
            $LayoutRowList = new LayoutRow(
                new LayoutColumn(
                    new Warning('Keine Gruppen vorhanden')
                )
            );
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    $LayoutRowList
                    , new Title(new ListingTable() . ' Übersicht')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Group::useService()->createGroup(
                                    $this->formGroup()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Die neue Gruppe wurde noch nicht gespeichert')
                                    , $Group
                                )
                            ))
                    ), new Title(new PlusSign() . ' Hinzufügen')
                ),
            ))
        );
        return $Stage;
    }

    /**
     * @return Form
     */
    private function formGroup()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Gruppe', array(
                            new TextField('Group[Name]', 'Name', 'Name'),
                            new TextField('Group[Description]', 'Beschreibung', 'Beschreibung')
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                            new TextArea('Group[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                        ), Panel::PANEL_TYPE_INFO)
                        , 8),
                ))
            )
        );
    }

    /**
     * @param int $Id
     * @param null|array $Group
     *
     * @return Stage
     */
    public function frontendEditGroup($Id, $Group = null)
    {

        $Stage = new Stage('Gruppe', 'Bearbeiten');
        $Stage->addButton(new Backward(true));

        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Group']['Name'] = $tblGroup->getName();
                $Global->POST['Group']['Description'] = $tblGroup->getDescription();
                $Global->POST['Group']['Remark'] = $tblGroup->getRemark();
                $Global->savePost();
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel('Gruppe', new Bold($tblGroup->getName()) .
                                    ($tblGroup->getDescription() !== '' ? '&nbsp;&nbsp;'
                                        . new Muted(new Small(new Small($tblGroup->getDescription()))) : ''),
                                    Panel::PANEL_TYPE_INFO),
                                12
                            )
                        )
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    Group::useService()->updateGroup(
                                        $this->formGroup()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Die Änderungen wurden noch nicht gespeichert')
                                        , $tblGroup, $Group
                                    )
                                )
                            )
                        ), new Title(new Edit() . ' Bearbeiten')
                    ),
                ))
            );
        } else {
            // TODO: Error-Message
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Danger(
                                    'Die Gruppe konnte nicht gefunden werden'
                                )
                            )
                        ), new Title('Gruppe ändern')
                    )
                )
            );
        }
        return $Stage;
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyGroup($Id, $Confirm = false)
    {

        $Stage = new Stage('Gruppe', 'Löschen');
        $Stage->addButton(new Backward(true));

        if ($Id) {
            $tblGroup = Group::useService()->getGroupById($Id);
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question() . ' Diese Gruppe wirklich löschen?', array(
                            $tblGroup->getName() . ' ' . $tblGroup->getDescription(),
                            new Muted(new Small($tblGroup->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/People/Group/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/People/Group', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Remove Group-Member
                $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);
                if ($tblPersonAll) {
                    array_walk($tblPersonAll, function (TblPerson $tblPerson) use ($tblGroup) {

                        Group::useService()->removeGroupPerson($tblGroup, $tblPerson);
                    });
                }

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Group::useService()->destroyGroup($tblGroup)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Gruppe wurde gelöscht')
                                . new Redirect('/People/Group', Redirect::TIMEOUT_SUCCESS)
                                : new Danger(new Ban() . ' Die Gruppe konnte nicht gelöscht werden')
                                . new Redirect('/People/Group', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Gruppe konnte nicht gefunden werden'),
                        new Redirect('/People/Group', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $DataAddPerson
     * @param null $DataRemovePerson
     * @param null $Filter
     * @param null $FilterGroupId
     * @param null $FilterDivisionId
     *
     * @return Stage
     */
    public function frontendGroupPersonAdd(
        $Id = null,
        $DataAddPerson = null,
        $DataRemovePerson = null,
        $Filter = null,
        $FilterGroupId = null,
        $FilterDivisionId = null
    ) {

        $Stage = new Stage('Gruppe', 'Personen zuweisen');
        $Stage->addButton(new Standard('Zurück', '/People/Group', new ChevronLeft()));

        if (($tblGroup = Group::useService()->getGroupById($Id))) {

            $tblFilterGroup = Group::useService()->getGroupById($FilterGroupId);
            $tblFilterDivision = Division::useService()->getDivisionById($FilterDivisionId);

            // Set Filter Post
            if ($Filter == null && ($tblFilterGroup || $tblFilterDivision)) {
                $GLOBAL = $this->getGlobal();
                $GLOBAL->POST['Filter']['Group'] = $tblFilterGroup ? $tblFilterGroup->getId() : 0;
                $GLOBAL->POST['Filter']['Division'] = $tblFilterDivision ? $tblFilterDivision->getId() : 0;

                $GLOBAL->savePost();
            }

            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            $tblPersonAll = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('COMMON'));

            // filter
            if ($tblFilterGroup || $tblFilterDivision) {
                $tblPersonAll = Group::useService()->filterPersonListByGroupAndDivision(
                    $tblPersonAll,
                    $tblFilterGroup ? $tblFilterGroup : null,
                    $tblFilterDivision ? $tblFilterDivision : null
                );
            }

            if ($tblPersonList && $tblPersonAll) {
                $tblPersonAll = array_udiff($tblPersonAll, $tblPersonList,
                    function (TblPerson $tblPersonA, TblPerson $tblPersonB) {

                        return $tblPersonA->getId() - $tblPersonB->getId();
                    }
                );
            }

            if ($tblPersonList) {
                $tempList = array();
                foreach ($tblPersonList as $personListPerson) {
                    $tempList[] = $this->setPersonData($personListPerson, 'DataRemovePerson');
                }
                $tblPersonList = $tempList;
            }

            if (is_array($tblPersonAll)) {
                $tempList = array();
                foreach ($tblPersonAll as $personAllPerson) {
                    $tempList[] = $this->setPersonData($personAllPerson, 'DataAddPerson');
                }
                $tblPersonAll = $tempList;
            }

            if (!$tblFilterGroup && !$tblFilterDivision){
                $displayAvailablePersons = new Warning(
                    'Zum Hinzufügen von Personen zur Gruppe: ' . $tblGroup->getName() . ' schränken Sie bitte den Personenkreis über die Suche (Gruppe und/oder Klasse) ein.',
                    new Exclamation()
                );
            } elseif ($tblPersonAll) {

                $displayAvailablePersons = new TableData(
                    $tblPersonAll,
                    new \SPHERE\Common\Frontend\Table\Repository\Title('Weitere Personen', 'hinzufügen'),
                    array(
                        'Check'       => new Center(new Small('Hinzufügen ').new Enable()),
                        'DisplayName' => 'Name',
                        'Address'     => 'Adresse',
                        'Groups'      => 'Gruppen/Klasse '
                    ),
                    array(
                        "columnDefs"     => array(
                            array(
                                "orderable" => false,
                                "width"     => "35px",
                                "targets"   => 0
                            ),
                            array(
                                "width"   => "20%",
                                "targets" => 1
                            ),
                            array(
                                "width"   => "40%",
                                "targets" => 2
                            )
                        ),
                        'order'          => array(
                            array('1', 'asc')
                        ),
                        "paging"         => false, // Deaktivieren Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                        "searching"      => false, // Deaktivieren Suchen
                        "info"           => false  // Deaktivieren Such-Info
                    )
                );
            } else {
                $displayAvailablePersons = new Warning('Keine weiteren Personen verfügbar.', new Exclamation());
            }

            $form = new Form(array(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(array(
                            ($tblPersonList
                                ? new TableData(
                                    $tblPersonList,
                                    new \SPHERE\Common\Frontend\Table\Repository\Title('Mitglieder der Gruppe "'.$tblGroup->getName().'"',
                                        'entfernen'),
                                    array(
                                        'Check'       => new Center(new Small('Entfernen ').new Disable()),
                                        'DisplayName' => 'Name',
                                        'Address'     => 'Adresse',
                                        'Groups'      => 'Gruppen/Klasse'
                                    ),
                                    array(
                                        "columnDefs"     => array(
                                            array(
                                                "orderable" => false,
                                                "width"     => "35px",
                                                "targets"   => 0
                                            ),
                                            array(
                                                "width"   => "20%",
                                                "targets" => 1
                                            ),
                                            array(
                                                "width"   => "40%",
                                                "targets" => 2
                                            )
                                        ),
                                        'order'          => array(
                                            array('1', 'asc')
                                        ),
                                        "paging"         => false, // Deaktivieren Blättern
                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                                        "searching"      => false, // Deaktivieren Suchen
                                        "info"           => false  // Deaktivieren Such-Info
                                    )
                                )
                                : new Warning('Keine Personen zugewiesen.', new Exclamation())
                            )
                        ), 6),
                        new FormColumn(array(
                            $displayAvailablePersons
                        ), 6),
                    ))
                ),
            ));

            $form->appendFormButton(new Primary('Speichern', new Save()));
            $form->setConfirm('Die Zuweisung der Personen wurde noch nicht gespeichert.');

            $Stage->setContent(new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Gruppe',
                                $tblGroup->getName() . ' ' . new Small(new Muted($tblGroup->getDescription())),
                                Panel::PANEL_TYPE_INFO
                            ), 12
                        ),
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Well(
                                Group::useService()->getFilter(
                                    $this->formFilter(), $tblGroup, $Filter
                                )
                            ), 12
                        )
                    ))
                ), new Title('Personensuche')),
                ($Filter == null ?
                    new LayoutGroup(array(
                        // TODO: Describe possible Action
//                        new LayoutRow(array(
//                            new LayoutColumn(
//                                new Info('Links können neue Personsn... rechts ...')
//                            )
//                        )),
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Well(
                                    Group::useService()->addPersonsToGroup(
                                        $form,
                                        $tblGroup,
                                        $DataAddPerson,
                                        $DataRemovePerson,
                                        $tblFilterGroup ? $tblFilterGroup : null,
                                        $tblFilterDivision ? $tblFilterDivision : null
                                    )
                                )
                            ))
                        ))
                    ), new Title('Zusammensetzung', 'der Gruppe')) : null )
            )));

        } else {
            return $Stage
            . new Danger('Gruppe nicht gefunden.', new Ban())
            . new Redirect('/People/Group', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $DataName
     *
     * @return array
     */
    private function setPersonData(TblPerson $tblPerson, $DataName)
    {
        $result = array();
        $result['Check'] = new CheckBox(
            $DataName . '[' . $tblPerson->getId() . ']',
            ' ',
            1
        );
        $result['DisplayName'] = $tblPerson->getLastFirstName();
        $tblAddress = $tblPerson->fetchMainAddress();
        $result['Address'] = $tblAddress ? $tblAddress->getGuiString() : '';
        $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
        $groups = array();
        if ($tblGroupList) {
            foreach ($tblGroupList as $item) {
                if ($item->getMetaTable() !== 'COMMON') {
                    $groups[] = $item->getName();
                }
            }
        }

        // current Divisions
        $displayDivisionList = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson);

        $result['Groups'] = (!empty($groups) ? implode(', ', $groups) . ($displayDivisionList ? ', ' . $displayDivisionList : '') : '');

        return $result;
    }

    private function formFilter()
    {

        $tblGroupAll = Group::useService()->getGroupAllSorted();
        $tblDivisionList = array();
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblYear);
                if ($tblDivisionAllByYear) {
                    foreach ($tblDivisionAllByYear as $tblDivision) {
                        $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }
        }

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Filter[Group]', 'Gruppe', array('Name' => $tblGroupAll)), 6
                    ),
                    new FormColumn(
                        new SelectBox('Filter[Division]', 'Klasse', array('DisplayName' => $tblDivisionList)), 6
                    ),
                    new FormColumn(
                        new Primary('Suchen', new Filter())
                    ),
                ))
            )
        );
    }
}
