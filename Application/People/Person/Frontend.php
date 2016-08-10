<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronDown;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\ChevronUp;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool|false|string $TabActive
     *
     * @param null|int          $Id
     * @param null|array        $Person
     * @param null|array        $Meta
     * @param null|int          $Group
     *
     * @return Stage
     */
    public function frontendPerson($TabActive = '#', $Id = null, $Person = null, $Meta = null, $Group = null)
    {

        $Stage = new Stage('Person', 'Datenblatt '.( $Id ? 'bearbeiten' : 'anlegen' ));
        $Stage->addButton( new Backward() );

        if (!$Id) {

            $BasicTable = Person::useService()->createPerson(
                $this->formPerson()
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $Person);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(new LayoutColumn(new Well($BasicTable))),
                        new Title(new PersonParent().' Grunddaten', 'der Person')
                    ),
                ))
            );

        } else {
            $tblPerson = Person::useService()->getPersonById($Id);

            if ($tblPerson) {
                $Global = $this->getGlobal();
                if (!isset($Global->POST['Person'])) {
                    if ($tblPerson->getTblSalutation()) {
                        $Global->POST['Person']['Salutation'] = $tblPerson->getTblSalutation()->getId();
                    }
                    $Global->POST['Person']['Title'] = $tblPerson->getTitle();
                    $Global->POST['Person']['FirstName'] = $tblPerson->getFirstName();
                    $Global->POST['Person']['SecondName'] = $tblPerson->getSecondName();
                    $Global->POST['Person']['LastName'] = $tblPerson->getLastName();
                    $Global->POST['Person']['BirthName'] = $tblPerson->getBirthName();
                    $tblGroupAll = Group::useService()->getGroupAllByPerson($tblPerson);
                    if (!empty($tblGroupAll)) {
                        /** @var TblGroup $tblGroup */
                        foreach ((array)$tblGroupAll as $tblGroup) {
                            $Global->POST['Person']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        }
                    }
                    $Global->savePost();
                }

                $BasicTable = Person::useService()->updatePerson(
                    $this->formPerson()
                        ->appendFormButton(new Primary('Speichern', new Save()))
                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                    $tblPerson, $Person, $Group);

                $MetaTabs = Group::useService()->getGroupAllByPerson($tblPerson);
                // Sort by Name
                usort($MetaTabs, function (TblGroup $ObjectA, TblGroup $ObjectB) {

                    return strnatcmp($ObjectA->getName(), $ObjectB->getName());
                });
                // Create Tabs
                /** @noinspection PhpUnusedParameterInspection */
                array_walk($MetaTabs, function (TblGroup &$tblGroup) use ($Group, $tblPerson) {

                    switch (strtoupper($tblGroup->getMetaTable())) {
                        case 'COMMON':
                            $tblGroup = new LayoutTab('Personendaten', $tblGroup->getMetaTable(),
                                array('Id' => $tblPerson->getId(), 'Group' => $Group)
                            );
                            break;
                        case 'PROSPECT':
                            $tblGroup = new LayoutTab('Interessent', $tblGroup->getMetaTable(),
                                array('Id' => $tblPerson->getId(), 'Group' => $Group)
                            );
                            break;
                        case 'STUDENT':
                            $tblGroup = new LayoutTab('Schülerakte', $tblGroup->getMetaTable(),
                                array('Id' => $tblPerson->getId(), 'Group' => $Group)
                            );
                            break;
                        case 'CUSTODY':
                            $tblGroup = new LayoutTab('Sorgerechtdaten', $tblGroup->getMetaTable(),
                                array('Id' => $tblPerson->getId(), 'Group' => $Group)
                            );
                            break;
                        case 'CLUB':
                            $tblGroup = new LayoutTab('Vereinsmitglied', $tblGroup->getMetaTable(),
                                array('Id' => $tblPerson->getId(), 'Group' => $Group)
                            );
                            break;
                        case 'TEACHER':
                            $tblGroup = new LayoutTab('Lehrer', $tblGroup->getMetaTable(),
                                array('Id' => $tblPerson->getId(), 'Group' => $Group)
                            );
                            break;
                        default:
                            $tblGroup = false;
                    }
                });
                /** @var LayoutTab[] $MetaTabs */
                $MetaTabs = array_filter($MetaTabs);
                // Folded ?
                if (!empty($MetaTabs)) {
                    if (!$TabActive || $TabActive == '#') {
                        array_unshift($MetaTabs, new LayoutTab('&nbsp;' . new ChevronRight() . '&nbsp;', '#',
                            array('Id' => $tblPerson->getId(), 'Group' => $Group)
                        ));
                        $MetaTabs[0]->setActive();
                    } else {
                        if ($TabActive == 'Common') {
                            array_unshift($MetaTabs, new LayoutTab('&nbsp;' . new ChevronUp() . '&nbsp;', '#',
                                array('Id' => $tblPerson->getId(), 'Group' => $Group)
                            ));
                            $MetaTabs[1]->setActive();
                        } else {
                            array_unshift($MetaTabs, new LayoutTab('&nbsp;' . new ChevronUp() . '&nbsp;', '#',
                                array('Id' => $tblPerson->getId(), 'Group' => $Group)
                            ));
                        }
                    }
                }

                switch (strtoupper($TabActive)) {
                    case 'COMMON':
                        $MetaTable = Common::useFrontend()->frontendMeta($tblPerson, $Meta, $Group);
                        break;
                    case 'PROSPECT':
                        $MetaTable = Prospect::useFrontend()->frontendMeta($tblPerson, $Meta, $Group);
                        break;
                    case 'STUDENT':
                        $MetaTable = Student::useFrontend()->frontendMeta($tblPerson, $Meta, $Group);
                        break;
                    case 'CUSTODY':
                        $MetaTable = Custody::useFrontend()->frontendMeta($tblPerson, $Meta, $Group);
                        break;
                    case 'CLUB':
                        $MetaTable = Club::useFrontend()->frontendMeta($tblPerson, $Meta, $Group);
                        break;
                    case 'TEACHER':
                        $MetaTable = Teacher::useFrontend()->frontendMeta($tblPerson, $Meta, $Group);
                        break;
                    default:
                        if (!empty($MetaTabs)) {
                            $MetaTable = new Muted('Bitte wählen Sie eine Rubrik');
                        } else {
                            $MetaTable = new Warning('Keine Informationen verfügbar');
                        }
                }
                $MetaTable = new Well($MetaTable);

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(
                            new LayoutRow(new LayoutColumn(array(
                                new Well($BasicTable)
                            ))),
                            new Title(new PersonParent() . ' Grunddaten', 'der Person')
                        ),
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(new LayoutTabs($MetaTabs))),
                            new LayoutRow(new LayoutColumn($MetaTable)),
                        ), new Title(new Tag() . ' Informationen', 'zur Person')),
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                Address::useFrontend()->frontendLayoutPerson($tblPerson)
                            )),
                        ), (new Title(new TagList() . ' Adressdaten', 'der Person'))
                            ->addButton(
                                new Standard('Adresse hinzufügen', '/People/Person/Address/Create',
                                    new ChevronDown(), array('Id' => $tblPerson->getId())
                                )
                            )
                        ),
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                Phone::useFrontend()->frontendLayoutPerson($tblPerson)
                                . Mail::useFrontend()->frontendLayoutPerson($tblPerson)
                            )),
                        ), (new Title(new TagList() . ' Kontaktdaten', 'der Person'))
                            ->addButton(
                                new Standard('Telefonnummer hinzufügen', '/People/Person/Phone/Create',
                                    new ChevronDown(), array('Id' => $tblPerson->getId())
                                )
                            )
                            ->addButton(
                                new Standard('E-Mail Adresse hinzufügen', '/People/Person/Mail/Create',
                                    new ChevronDown(), array('Id' => $tblPerson->getId())
                                )
                            )
                        ),
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                Relationship::useFrontend()->frontendLayoutPerson($tblPerson),
                                Relationship::useFrontend()->frontendLayoutCompany($tblPerson)
                            ))),
                        ), (new Title(new TagList() . ' Beziehungen', 'zu Personen und Firmen'))
                            ->addButton(
                                new Standard('Personenbeziehung hinzufügen', '/People/Person/Relationship/Create',
                                    new ChevronDown(), array('Id' => $tblPerson->getId())
                                )
                            )
                            ->addButton(
                                new Standard('Firmenbeziehung hinzufügen', '/Corporation/Company/Relationship/Create',
                                    new ChevronDown(), array('Id' => $tblPerson->getId())
                                )
                            )
                        ),
                    ))
                );

            } else {
                return $Stage . new Danger('Person nicht gefunden', new Exclamation());
            }
        }

        return $Stage;
    }

    /**
     * Kompakte Darstellung aller relevanter/Datenschutz unbedenklicher Personendaten (reine Ansicht!)
     *
     * @param null|int $Id TblPerson
     *
     * @return Stage
     */
    public function frontendDossier($Id = null)
    {

        $Stage = new Stage();

        if( !$Id ) {
            // TODO: Error
            return $Stage;
        }

        $tblPerson = Person::useService()->getPersonById($Id);
        if( !$tblPerson ) {
            // TODO: Error
            return $Stage;
        }


        return $Stage;
    }

    /**
     * @return Form
     */
    private function formPerson()
    {

        $tblGroupList = Group::useService()->getGroupAllSorted();
        if ($tblGroupList) {
            // Sort by Name
//            usort($tblGroupList, function (TblGroup $ObjectA, TblGroup $ObjectB) {
//
//                return strnatcmp($ObjectA->getName(), $ObjectB->getName());
//            });

            // Create CheckBoxes
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupList, function (TblGroup &$tblGroup) {

                switch (strtoupper($tblGroup->getMetaTable())) {
                    case 'COMMON':
                        $Global = $this->getGlobal();
                        $Global->POST['Person']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        $Global->savePost();
                        $tblGroup = new RadioBox(
                            'Person[Group]['.$tblGroup->getId().']',
                            $tblGroup->getName().' '.new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                        break;
                    default:
                        $tblGroup = new CheckBox(
                            'Person[Group]['.$tblGroup->getId().']',
                            $tblGroup->getName().' '.new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                }
            });
        } else {
            $tblGroupList = array(new Warning('Keine Gruppen vorhanden'));
        }

        $tblSalutationAll = Person::useService()->getSalutationAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anrede', array(
                            new SelectBox('Person[Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll),
                                new Conversation()),
                            new AutoCompleter('Person[Title]', 'Titel', 'Titel', array('Dipl.- Ing.'),
                                new Conversation()),
                        ), Panel::PANEL_TYPE_INFO), 2),
                    new FormColumn(
                        new Panel('Vorname', array(
                            new TextField('Person[FirstName]', 'Rufname', 'Vorname'),
                            new TextField('Person[SecondName]', 'weitere Vornamen', 'Zweiter Vorname'),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Nachname', array(
                            new TextField('Person[LastName]', 'Nachname', 'Nachname'),
                            new TextField('Person[BirthName]', 'Geburtsname', 'Geburtsname'),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO), 4),
                ))
            ))
        );
    }

    /**
     * @param $Id
     * @param bool|false $Confirm
     * @param null $Group
     * @return Stage
     */
    public function frontendDestroyPerson($Id = null, $Confirm = false, $Group = null)
    {

        $Stage = new Stage('Person', 'Löschen');
        if ($Id) {
            if ($Group) {
                $Stage->addButton(new Standard('Zurück', '/People/Search/Group', new ChevronLeft(), array('Id' => $Group)));
            }
            $tblPerson = Person::useService()->getPersonById($Id);
            if (!$tblPerson){
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger('Die Person konnte nicht gefunden werden.', new Ban()),
                            new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR, array('Id' => $Group))
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Person', new Bold($tblPerson->getLastFirstName()),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(new Question() . ' Diese Person wirklich löschen?', array(
                                $tblPerson->getLastFirstName()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/People/Person/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true, 'Group' => $Group)
                                )
                                . new Standard(
                                    'Nein', '/People/Search/Group', new Disable(), array('Id' => $Group)
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (Person::useService()->destroyPerson($tblPerson)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Person wurde gelöscht.')
                                    : new Danger(new Ban() . ' Die Person konnte nicht gelöscht werden.')
                                ),
                                new Redirect('/People/Search/Group', Redirect::TIMEOUT_SUCCESS, array('Id' => $Group))
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Daten nicht abrufbar.', new Ban()),
                        new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR, array('Id' => $Group))
                    )))
                )))
            );
        }
        return $Stage;
    }
}
