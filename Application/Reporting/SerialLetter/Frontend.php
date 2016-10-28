<?php

namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
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
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\View;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

/**
 * Class Frontend
 * @package SPHERE\Application\Reporting\SerialLetter
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $SerialLetter
     *
     * @return Stage
     */
    public function frontendSerialLetter($SerialLetter = null)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Übersicht');

        $tblSerialLetterAll = SerialLetter::useService()->getSerialLetterAll();

        $TableContent = array();
        if ($tblSerialLetterAll) {
            array_walk($tblSerialLetterAll, function (TblSerialLetter $tblSerialLetter) use (&$TableContent) {
                $Item['Name'] = $tblSerialLetter->getName();
                $Item['Description'] = $tblSerialLetter->getDescription();
                $Item['Option'] =
                    ( new Standard('', '/Reporting/SerialLetter/Edit', new Edit(),
                        array('Id' => $tblSerialLetter->getId()), 'Bearbeiten') )
                    .( new Standard('', '/Reporting/SerialLetter/Destroy', new Remove(),
                        array('Id' => $tblSerialLetter->getId()), 'Löschen') )
                    .( new Standard('', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                        array('Id' => $tblSerialLetter->getId()), 'Personen auswählen') )
                    .( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                        array('Id' => $tblSerialLetter->getId()), 'Addressen auswählen') )
                    .( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                        array('Id' => $tblSerialLetter->getId()),
                        'Addressliste für Serienbriefe anzeigen und herunterladen') );
                array_push($TableContent, $Item);
            });
        }

        $Form = $this->formSerialLetter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null, array(
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Option'      => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable().' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(SerialLetter::useService()->createSerialLetter($Form, $SerialLetter))
                        ))
                    ))
                ), new Title(new PlusSign().' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSerialLetter()
    {

        $tblGroupAll = Group::useService()->getGroupAll();
        // Gruppe "Alle" aus der Auswahl entfernen
        if ($tblGroupAll) {
            /** @var TblGroup $tblGroup */
            $tblGroup = current($tblGroupAll);
            if ($tblGroup->getMetaTable() == 'COMMON') {
                array_shift($tblGroupAll);
            }
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('SerialLetter[Name]', 'Name', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('SerialLetter[Description]', 'Beschreibung', 'Beschreibung'), 8
                )
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $SerialLetter
     *
     * @return Stage|string
     */
    public function frontendSerialLetterEdit($Id = null, $SerialLetter = null)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Bearbeiten');

        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));

        if (( $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id) )) {
            if ($SerialLetter == null) {
                $Global = $this->getGlobal();
                $Global->POST['SerialLetter']['Name'] = $tblSerialLetter->getName();
                $Global->POST['SerialLetter']['Description'] = $tblSerialLetter->getDescription();
                $Global->savePost();
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Name', $tblSerialLetter->getName().' '
                            .new Small(new Muted($tblSerialLetter->getDescription())), Panel::PANEL_TYPE_INFO), 8
                    ),
                    new LayoutColumn(array(
                        new Title(new Edit().' Bearbeiten'),
                        new Well(
                            SerialLetter::useService()->updateSerialLetter(
                                $this->formSerialLetter()->appendFormButton(new Primary('Speichern', new Save())),
                                $tblSerialLetter, $SerialLetter
                            )
                        )
                    ))
                ))))
            );

        } else {
            return $Stage
            .new Danger('Serienbrief nicht gefunden', new Exclamation())
            .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param null|int   $Id
     * @param null|array $FilterGroup
     * @param null|array $FilterStudent
     * @param null|array $FilterPerson
     * @param null|array $FilterYear
     * @param null|array $FilterType
     * @param string     $TabActive
     *
     * @return Stage|string
     */
    public function frontendSerialLetterPersonSelected(
        $Id = null,
        $FilterGroup = null,
        $FilterStudent = null,
        $FilterPerson = null,
        $FilterYear = null,
        $FilterType = null,
        $TabActive = 'PERSON'
    ) {

        $Stage = new Stage('Personen für Serienbriefe', 'Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = ( $Id == null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $Stage->addButton(new Standard(new Bold(new Info('Personen Auswahl')),
            '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
            array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Addressliste für Serienbriefe anzeigen und herunterladen'));

        $Filter = false;
        // No Filter Detected
        if (
            $FilterGroup === null
            && $FilterStudent === null
            && $FilterPerson === null
            && $FilterYear === null
            && $FilterType === null
        ) {
            // set Group Student and Execute Search
            $FilterGroup['TblGroup_Name'] = 'Schüler';
            $Global = $this->getGlobal();
            $Global->POST['FilterGroup']['TblGroup_Name'] = 'Schüler';

            // set Year
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $Global->POST['FilterYear']['TblYear_Name'] = $tblYear->getName();
                }
            }
            $Global->savePost();
        };

        // Database Join with foreign Key
        if ($FilterGroup) {
            $Filter = $FilterGroup;

            $Pile = new Pile(Pile::JOIN_TYPE_INNER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(), null, 'TblMember_serviceTblPerson');
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(), ViewPerson::TBL_PERSON_ID, null);
            // Group->Person
        }
        // Database Join with foreign Key
        if ($FilterStudent) {
            $Filter = $FilterStudent;

            $Pile = new Pile(Pile::JOIN_TYPE_INNER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
            );
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
            );
            $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(),
                ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
            );
            $Pile->addPile(( new ViewYear() )->getViewService(), new ViewYear(),
                ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
            );
        }


        $Result = array();
        $Timeout = null;
        if ($Filter && isset( $Pile )) {
            // Preparation Filter
            array_walk($Filter, function (&$Input) {

                if (!empty( $Input )) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Filter = array_filter($Filter);
            // Preparation FilterPerson
            if ($FilterPerson) {
                array_walk($FilterPerson, function (&$Input) {

                    if (!empty( $Input )) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterPerson = array_filter($FilterPerson);
            } else {
                $FilterPerson = array();
            }
            // Preparation $FilterYear
            if ($FilterYear) {
                array_walk($FilterYear, function (&$Input) {

                    if (!empty( $Input )) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterYear = array_filter($FilterYear);
            } else {
                $FilterYear = array();
            }
            // Preparation $FilterType
            if ($FilterType) {
                array_walk($FilterType, function (&$Input) {

                    if (!empty( $Input )) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterType = array_filter($FilterType);
            } else {
                $FilterType = array();
            }
            // Filter ordered by Database Join with foreign Key
            if ($FilterGroup) {
                $Result = $Pile->searchPile(array(
                    0 => $Filter,
                    1 => $FilterPerson
                ));
            }
            // Filter ordered by Database Join with foreign Key
            if ($FilterStudent) {
                $Result = $Pile->searchPile(array(
                    0 => array('TblGroup_Name' => array('Schüler')),
                    1 => $FilterPerson,
                    2 => $Filter,
                    3 => $FilterYear
//                    4 => $FilterType
                ));
            }
            // get Timeout status
            $Timeout = $Pile->isTimeout();
        }

        /**
         * @var int                                                        $Index
         * @var ViewPeopleGroupMember[]|ViewDivisionStudent[]|ViewPerson[] $Row
         */
        $SearchResult = array();
        foreach ($Result as $Index => $Row) {
            if ($FilterGroup) {
                /** @var array $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                $DataPerson['Division'] = new Small(new Muted('-NA-'));

            } else {
                /** @var array $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                $tblDivisionStudent = $Row[2]->getTblDivisionStudent();
                if ($tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container($tblDivision->getDisplayName());
                    } else {
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                    }
                } else {
                    $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                }
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Exchange'] = (string)new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                'Id'       => $Id,
                'PersonId' => $DataPerson['TblPerson_Id']
            ));
            $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Name'] = false;
            if ($tblPerson) {
                $DataPerson['Name'] = $tblPerson->getLastFirstName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            }
            if (!$DataPerson['Name']) {
//                var_dump($DataPerson['TblPerson_Id']);
            }
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
            if (isset( $tblAddress ) && $tblAddress && $DataPerson['Name']) {
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = $tblAddress->getGuiString();
            }
            $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
            if (isset( $tblStudent ) && $tblStudent && $DataPerson['Name']) {
                $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
            }

            // ignore duplicated Person
            if ($DataPerson['Name']) {
                if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                    $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                }
            }

        }

        $tblPersonSearch = $SearchResult;

        $tblPersonList = SerialLetter::useService()->getPersonBySerialLetter($tblSerialLetter);

        if (!empty( $tblPersonList ) && !empty( $tblPersonSearch )) {

            $tblPersonIdList = array();
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$tblPersonIdList) {
                if (!in_array($tblPerson->getId(), $tblPersonIdList)) {
                    array_push($tblPersonIdList, $tblPerson->getId());
                }
            });

            array_filter($tblPersonSearch, function (&$Item) use ($tblPersonIdList) {
                if (in_array($Item['TblPerson_Id'], $tblPersonIdList)) {
                    $Item = false;
                }
            });

            $tblPersonSearch = array_filter($tblPersonSearch);
        }
        if ($tblPersonList) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblPersonList, function (TblPerson &$tblPerson) use ($Id) {

                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Exchange = new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(
                    'Id'       => $Id,
                    'PersonId' => $tblPerson->getId()
                ));
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Name = $tblPerson->getLastFirstName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Address = new WarningMessage('Keine Adresse hinterlegt!');
                if ($tblAddress) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->Address = $tblAddress->getGuiString();
                }
                if ($tblStudent) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->StudentNumber = $tblStudent->getIdentifier();
                } else {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->StudentNumber = new Small(new Muted('-NA-'));
                }


                $VisitedDivision = new Small(new Muted('-NA-'));
                $VisitedDivisionList = array();
                if ($tblPerson !== null) {
                    $tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                    if ($tblDivisionStudentAllByPerson) {
                        foreach ($tblDivisionStudentAllByPerson as &$tblDivisionStudent) {
                            $tblDivision = $tblDivisionStudent->getTblDivision();
                            /** @var TblDivision $tblDivision */
                            if ($tblDivision) {
                                $tblLevel = $tblDivision->getTblLevel();
                                $tblYear = $tblDivision->getServiceTblYear();
                                if ($tblLevel && $tblYear) {
                                    $VisitedDivisionList[] = new Small(new Muted('Aktuelle Klasse:')).new Container($tblDivision->getDisplayName());
                                }
                            }
                        }

                        if (!empty( $VisitedDivisionList )) {
                            rsort($VisitedDivisionList);
                            $VisitedDivision = current($VisitedDivisionList);
                        }
                    }
                }
                $tblPerson->Division = $VisitedDivision;
            });
        }

        $FormGroup = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(array(
                        new AutoCompleter('FilterGroup[TblGroup_Name]', 'Gruppe: Name', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll())),
                    ), 4),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Person: Vorname', 'Person: Vorname')
                    ), 4),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Person: Nachname', 'Person: Nachname')
                    ), 4)
                ))
            )
            , new Primary('in Gruppen suchen'));

        $FormStudent = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new AutoCompleter('FilterYear[TblYear_Name]', 'Bildung: Schuljahr', 'Bildung: Schuljahr', array('Name' => Term::useService()->getYearAll())),
                    ), 2),
//                    new FormColumn(array(
//                        new AutoCompleter('FilterType[TblType_Name]', 'Bildung: Schulart', 'Bildung: Schulart', array('Name' => Type::useService()->getTypeAll())),
//                    ), 6),
                    new FormColumn(array(
                        new TextField('FilterStudent[TblLevel_Name]', 'Klasse: Stufe', 'Klasse: Stufe')
                    ), 2),
                    new FormColumn(array(
                        new TextField('FilterStudent[TblDivision_Name]', 'Klasse: Gruppe', 'Klasse: Gruppe')
                    ), 2),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Person: Vorname', 'Person: Vorname')
                    ), 3),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Person: Nachname', 'Person: Nachname')
                    ), 3)
                )),
                new FormRow(array(

                ))
            ))
            , new Primary('in Klassen suchen'));

        // set Success by filtered Input field in FormGroup
        if ($FilterGroup) {
            foreach ($FilterGroup as $Field => $Value) {
                if ($Value) {
                    $FormGroup->setSuccess('FilterGroup['.$Field.']', '', new Filter());
                }
            }
            if ($FilterPerson) {
                foreach ($FilterPerson as $Field => $Value) {
                    if ($Value) {
                        $FormGroup->setSuccess('FilterPerson['.$Field.']', '', new Filter());
                    }
                }
            }
        }

        // set Success by filtered Input field in FormStudent
        if ($FilterStudent) {
            foreach ($FilterStudent as $Field => $Value) {
                if ($Value) {
                    $FormStudent->setSuccess('FilterStudent['.$Field.']', '', new Filter());
                }
            }
            if ($FilterPerson) {
                foreach ($FilterPerson as $Field => $Value) {
                    if ($Value) {
                        $FormStudent->setSuccess('FilterPerson['.$Field.']', '', new Filter());
                    }
                }
            }
            if ($FilterYear) {
                foreach ($FilterYear as $Field => $Value) {
                    if ($Value) {
                        $FormStudent->setSuccess('FilterYear['.$Field.']', '', new Filter());
                    }
                }
            }
            if ($FilterType) {
                foreach ($FilterType as $Field => $Value) {
                    if ($Value) {
                        $FormStudent->setSuccess('FilterType['.$Field.']', '', new Filter());
                    }
                }
            }
        }

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Adresse(n): '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );


        // Create Tabs
        $LayoutTabs[] = new LayoutTab('Personen in Gruppen suchen', 'PERSON', array('Id' => $tblSerialLetter->getId()));
        $LayoutTabs[] = new LayoutTab('Schüler in Klassen suchen', 'DIVISION', array('Id' => $tblSerialLetter->getId()));
        if (!empty( $LayoutTabs ) && $TabActive === 'PERSON') {
            $LayoutTabs[0]->setActive();
        }

        switch ($TabActive) {
            case 'PERSON':
                $MetaTable = new Panel(new Search().' Personen-Suche nach '.new Bold('Personengruppe')
                    , array($FormGroup), Panel::PANEL_TYPE_INFO);
                break;
            case 'DIVISION':
                $MetaTable = new Panel(new Search().' Schüler-Suche nach '.new Bold('Schuljahr / Klasse / Schüler')
                    , array($FormStudent), Panel::PANEL_TYPE_INFO);
                break;
            default:
                $MetaTable = new Panel(new Search().' Personen-Suche nach '.new Bold('Personengruppe')
                    , array($FormGroup), Panel::PANEL_TYPE_INFO);
        }
//        $MetaTable = new Well($MetaTable);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title(new PersonIcon().' Personen', 'Zugewiesen'),

                            new Layout(
                                new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                                        ), 12),
                                    ))
                                ))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title(new PersonIcon().' Personen', 'Verfügbar'),
                            new Layout(
                                new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new LayoutTabs($LayoutTabs),
                                            $MetaTable
                                        ), 12),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            ( $Timeout === true
                                                ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                                : ''
                                            )
                                        )
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ( empty( $tblPersonSearch )
                                                ? new WarningMessage('Keine Ergebnisse bei aktueller Filterung '.new SuccessText(new Filter()))
                                                : ''
                                            ),

                                        ), 12)
                                    ))
                                ))
                            )
                        ), 6)
                    ))
                ))
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblPersonList, null,
                                array('Exchange'      => '',
                                      'Name'          => 'Name',
                                      'Address'       => 'Adresse',
                                      'Division'      => 'Klasse',
                                      'StudentNumber' => 'Schüler-Nr.'
                                ),
                                array(
                                    'order'                => array(array(1, 'asc')),
                                    'columnDefs'           => array(
                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                    ),
                                    'ExtensionRowExchange' => array(
                                        'Enabled' => true,
                                        'Url'     => '/Api/Reporting/SerialLetter/Exchange',
                                        'Handler' => array(
                                            'From' => 'glyphicon-minus-sign',
                                            'To'   => 'glyphicon-plus-sign',
                                            'All'  => 'TableRemoveAll'
                                        ),
                                        'Connect' => array(
                                            'From' => 'TableCurrent',
                                            'To'   => 'TableAvailable',
                                        )
                                    )
                                )
                            ),
                            new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll')
                        ), 6),
                        new LayoutColumn(array(
                            new TableData($tblPersonSearch, null,
                                array('Exchange'      => ' ',
                                      'Name'          => 'Name',
                                      'Address'       => 'Adresse',
                                      'Division'      => 'Klasse',
                                      'StudentNumber' => 'Schüler-Nr.'
                                ),
                                array(
                                    'order'                => array(array(1, 'asc')),
                                    'columnDefs'           => array(
                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                    ),
                                    'ExtensionRowExchange' => array(
                                        'Enabled' => true,
                                        'Url'     => '/Api/Reporting/SerialLetter/Exchange',
                                        'Handler' => array(
                                            'From' => 'glyphicon-plus-sign',
                                            'To'   => 'glyphicon-minus-sign',
                                            'All'  => 'TableAddAll'
                                        ),
                                        'Connect' => array(
                                            'From' => 'TableAvailable',
                                            'To'   => 'TableCurrent',
                                        ),
                                    )
                                )
                            ),
                            new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll')
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendPersonAddress(
        $Id = null
    ) {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }
        $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
            array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Addressliste für Serienbriefe anzeigen und herunterladen'));

        $tblPersonList = SerialLetter::useService()->getPersonBySerialLetter($tblSerialLetter);
        if (!$tblPersonList) {
            return $Stage.new Danger('Es sind keine Personen dem Serienbrief zugeordnet', new Exclamation());
        }

        $TableContent = array();
        array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $Id, $tblSerialLetter) {
            $Item['Name'] = $tblPerson->getLastFirstName();
            $Item['StudentNumber'] = new Small(new Muted('-NA-'));
            $Item['Address'] = array();
            $Item['Option'] = new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                array('Id'       => $Id,
                      'PersonId' => $tblPerson->getId(),
                      'Route'    => '/Reporting/SerialLetter/Address'));
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                $Item['StudentNumber'] = $tblStudent->getIdentifier();
            }

            $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
            if ($tblAddressPersonList) {
                $Data = array();
                /** @var TblAddressPerson $tblAddressPerson */
                foreach ($tblAddressPersonList as $tblAddressPerson) {

                    if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                        if ($tblAddressPerson->getServiceTblSalutation()) {
                            if (( $tblPersonTo = $tblToPerson->getServiceTblPerson() )) {
                                $Data[] = $tblAddressPerson->getServiceTblSalutation()->getSalutation().' '.$tblPersonTo->getLastFirstName();
                            }
                        } else {
                            if (( $tblPersonTo = $tblToPerson->getServiceTblPerson() )) {
                                $Data[] = $tblPersonTo->getLastFirstName();
                            }
                        }
                        if (( $tblAddress = $tblToPerson->getTblAddress() )) {
                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                if ($tblCity->getDistrict() != '') {
                                    $Data[] = $tblCity->getDistrict();
                                }
                            }

                            $Data[] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                $Data[] = $tblCity->getCode().' '.$tblCity->getName();
                            }
                            if (( $tblState = $tblAddress->getTblState() )) {
                                $Data[] = $tblState->getName();
                            }
                        }
                    }

                    if (!empty( $Data )) {
                        $Item['Address'][] = new LayoutColumn(
                            new Panel('', $Data)
                            , 3);
                    }
                    $Data = array();
                }

                $Item['Address'] = array_filter($Item['Address']);

                $Item['Address'] = (string)new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            $Item['Address']
                        )
                    )
                );
            }

            if (empty( $Item['Address'] )) {
                $Item['Address'] = new WarningMessage('Keine Adressen ausgewählt!');
            }

            array_push($TableContent, $Item);
        });

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Adresse(n): '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title(new Setup().' Adressen', 'Zuweisung'),
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(
                                        new LayoutColumn(
                                            new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter)
                                            , 6)
                                    )
                                )
                            )
                        ), 12),
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'Name'          => 'Name',
                                'StudentNumber' => 'Schüler-Nr.',
                                'Address'       => 'Serienbrief Adresse',
                                'Option'        => '',
                            ), array(
                                'columnDefs' => array(
                                    array('orderable' => false, 'width' => '1%', 'targets' => -1),
                                    array('width' => '15%', 'targets' => 0),
                                    array('width' => '10%', 'targets' => 1)
                                )
                            ))
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null   $Id
     * @param null   $PersonId
     * @param string $Route
     * @param null   $Check
     *
     * @return Stage|string
     */
    public function frontendPersonAddressEdit($Id = null, $PersonId = null, $Route = '/Reporting/SerialLetter/Address', $Check = null)
    {

        $Stage = new Stage('Adresse(n)', 'Auswählen');
        $tblSerialLetter = ( $Id === null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft(), array('Id' => $Id)));
        $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
            array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Addressliste für Serienbriefe anzeigen und herunterladen'));

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            return $Stage.new WarningMessage('Person nicht gefunden', new Exclamation());
        }

        // Set Global Post
        $Global = $this->getGlobal();
        if ($Check === null) {
            $tblAddressAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
            if ($tblAddressAllByPerson) {
                foreach ($tblAddressAllByPerson as $tblAddressPerson) {
                    if ($tblAddressPerson->getServiceTblPerson()
                        && $tblAddressPerson->getServiceTblPersonToAddress()
                        && $tblAddressPerson->getServiceTblToPerson()
                    ) {
                        $Global->POST['Check']
                        [$tblAddressPerson->getServiceTblPerson()->getId()]
                        [$tblAddressPerson->getServiceTblToPerson()->getId()]
                        ['Address'] = 1;

                        $Global->POST['Check']
                        [$tblAddressPerson->getServiceTblPerson()->getId()]
                        [$tblAddressPerson->getServiceTblToPerson()->getId()]
                        ['Salutation'] = $tblAddressPerson->getServiceTblSalutation() ? $tblAddressPerson->getServiceTblSalutation()->getId() : 0;
                    }
                }
            }
        }
        $Global->savePost();

        // Selectbox Field's
        $tblSalutationAll = Person::useService()->getSalutationAll();
        if ($tblSalutationAll) {
            $tblSalutation = new TblSalutation('Familie');
            $tblSalutation->setId(TblAddressPerson::SALUTATION_FAMILY);
            $tblSalutationAll['Family'] = $tblSalutation;
        }

        $dataList = array();
        $columnList = array(
            'Person'       => 'Person',
            'Relationship' => 'Beziehung',
            'Address'      => 'Adressen',
            'Salutation'   => 'Anrede'
        );

        $personCount = 0;
        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
        if ($tblAddressToPersonList) {
            foreach ($tblAddressToPersonList as $tblToPerson) {

                $dataList[$tblPerson->getId()]['Number'] = ++$personCount;
                $dataList[$tblPerson->getId()]['Person'] = $tblPerson->getLastFirstName();
                $subDataList[] = array(
                    'Person'       => $tblToPerson->getServiceTblPerson() ? new Bold($tblToPerson->getServiceTblPerson()->getFullName()) : '',
                    'Relationship' => '',
                    'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Address]',
                        '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1),
                    'Salutation'   => new SelectBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Salutation]',
                        '', array('Salutation' => $tblSalutationAll))
                );
            }
        }

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        $PersonToPersonId = array();
        if ($tblRelationshipAll) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                $tblType = $tblRelationship->getTblType();
                if ($tblType && $tblType->getName() !== 'Arzt') {
                    if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                        if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                            $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonFrom());
                            $direction = $tblRelationship->getServiceTblPersonFrom()->getLastFirstName().' ist '.$tblType->getName()
                                .' für '.new Bold($tblPerson->getLastFirstName());
                        } else {
                            $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonTo());
                            $direction = new Bold($tblPerson->getLastFirstName()).' ist '.$tblType->getName()
                                .' für '.$tblRelationship->getServiceTblPersonTo()->getLastFirstName();
                        }
                        if ($tblAddressToPersonList) {
                            foreach ($tblAddressToPersonList as $tblToPerson) {
                                $PersonIdAddressIdNow = ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getId() : '' ).'.'.
                                    ( $tblToPerson->getTblAddress() ? $tblToPerson->getTblAddress()->getId() : '' );
                                // ignore duplicated Person by Relationship
                                if (!array_key_exists($PersonIdAddressIdNow, $PersonToPersonId)) {
                                    $subDataList[] = array(
                                        'Person'       => $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getFullName() : '',
                                        'Relationship' => $direction,
                                        'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Address]',
                                            '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1),
                                        'Salutation'   => new SelectBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Salutation]',
                                            '', array('Salutation' => $tblSalutationAll))
                                    );
                                    $PersonToPersonId[$PersonIdAddressIdNow] = $PersonIdAddressIdNow;
                                }
                            }
                        } else {
                            /** @var TblToPerson $tblRelationship */
                            if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                                $subDataList[] = array(
                                    'Person'       => $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getFullName() : '',
                                    'Relationship' => $direction,
                                    'Address'      => new Warning(
                                        new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Keine Adresse hinterlegt'),
                                    'Salutation'   => ''
                                );
                            } else {
                                $subDataList[] = array(
                                    'Person'       => $tblRelationship->getServiceTblPersonTo() ? $tblRelationship->getServiceTblPersonTo()->getFullName() : '',
                                    'Relationship' => $direction,
                                    'Address'      => new Warning(
                                        new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Keine Adresse hinterlegt'),
                                    'Salutation'   => ''
                                );
                            }
                        }
                    }
                }
            }
        }

        if (isset( $subDataList )) {
            $Form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(array(
                            new TableData($subDataList, null, $columnList,
                                array(
                                    'order' => array(array(1, 'asc'), array(0, 'asc')),
//                                    'columnDefs' => array(
//                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
//                                    ),
                                )),
                            new Primary('Speichern', new Save())
                        ))
                    )
                )
            );
        }

        $tblPersonList = SerialLetter::useService()->getPersonBySerialLetter($tblSerialLetter);
        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Adresse(n): '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

        $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
        $PanelPerson = new Panel(
            new Bold($tblPerson->getFullName()),
            'Verwendete Adresse(n): '.( $tblAddressPersonList === false ? 0 : count($tblAddressPersonList) ),
            Panel::PANEL_TYPE_SUCCESS);

        if (isset( $Form )) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(array(
                                new Title(new Listing().' Addressen', 'Auswahl')
                            , new Layout(
                                    new LayoutGroup(
                                        new LayoutRow(array(
                                            new LayoutColumn(
                                                new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter
                                                )
                                                , 6),
                                            new LayoutColumn(
                                                $PanelPerson
                                                , 6)
                                        ))
                                    )
                                ),
                                new Well(SerialLetter::useService()->setPersonAddressSelection(
                                    $Form, $tblSerialLetter, $Check, $Route
                                ))
                            ))
                        )
                    )
                )
            );
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Title(new Listing().'  Addressen', 'Auswahl')
                                , 12),
                            new LayoutColumn(
                                new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter
                                )
                                , 6),
                            new LayoutColumn(
                                $PanelPerson
                                , 6),
                            new LayoutColumn(
                                new WarningMessage('Die Person '.$tblPerson->getFullName().' sowie die in Beziehung gesetzten Personen besitzen keine Adresse. Zur Person '
                                    .new Standard('', '/People/Person', new PersonIcon(), array('Id' => $tblPerson->getId())))
                                , 12)
                        ))
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendSerialLetterExport(
        $Id = null
    ) {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen herunterladen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        if (( $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id) )) {

            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
            $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
                array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
            $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
                array('Id' => $tblSerialLetter->getId()),
                'Addressliste für Serienbriefe anzeigen und herunterladen'));

            $dataList = array();
            $columnList = array(
                'Number'          => 'Nr.',
                'Person'          => 'Person',
                'StudentNumber'   => 'Schüler-Nr.',
                'Salutation'      => 'Anrede',
                'PersonToAddress' => 'Adressat',
                'Address'         => 'Adresse',
                'Option'          => ''
            );

            $countAddresses = 0;
            $count = 0;
            $tblPersonList = false;
            $tbSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
            if ($tbSerialPersonList) {
                foreach ($tbSerialPersonList as $tbSerialPerson) {
                    if ($tbSerialPerson->getServiceTblPerson()) {
                        $tblPersonList[] = $tbSerialPerson->getServiceTblPerson();
                    }
                }
            }
            if ($tblPersonList) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                /** @var TblPerson $tblPerson */
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                        $tblPerson);
                    if ($tblAddressPersonAllByPerson) {
                        /** @var TblAddressPerson $tblAddressPerson */
                        foreach ($tblAddressPersonAllByPerson as $tblAddressPerson) {
                            // clean data if missing address
                            if (!$tblAddressPerson->getServiceTblToPerson()) {
                                SerialLetter::useService()->destroySerialAddressPerson($tblAddressPerson);
                            }
                        }
                    }
                    // get fresh list
                    $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                        $tblPerson);
                    if ($tblAddressPersonAllByPerson) {
                        /** @var TblAddressPerson $tblAddressPerson */
                        foreach ($tblAddressPersonAllByPerson as $tblAddressPerson) {

                            $tblAddressPersonFound = $tblAddressPerson->getServiceTblPerson();
                            $tblPersonWithAddress = $tblAddressPerson->getServiceTblPersonToAddress();

                            if ($tblAddressPerson->getServiceTblToPerson()
                                && $tblAddressPerson->getServiceTblToPerson()->getTblAddress()
                            ) {

                                $countAddresses++;
                            }

                            $RelationshipListFrom = array();
                            $RelationshipListTo = array();
                            if ($tblAddressPersonFound) {
                                $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblAddressPersonFound);
                                if ($tblRelationshipList) {
                                    /** @var TblToPerson $tblRelationship */
                                    foreach ($tblRelationshipList as $tblRelationship) {
                                        if ($tblRelationship->getServiceTblPersonFrom() == $tblAddressPersonFound
                                            && $tblRelationship->getServiceTblPersonTo() == $tblPersonWithAddress
                                        ) {
                                            $RelationshipListFrom [] = $tblRelationship->getTblType()->getName();
                                        }
                                        if ($tblRelationship->getServiceTblPersonTo() == $tblAddressPersonFound
                                            && $tblRelationship->getServiceTblPersonFrom() == $tblPersonWithAddress
                                        ) {
                                            $RelationshipListTo [] = $tblRelationship->getTblType()->getName();
                                        }
                                    }
                                }
                            }
                            if (!empty( $RelationshipListFrom )) {
                                $RelationshipListFrom = implode(', ', $RelationshipListFrom);
                                $RelationshipListFrom =
                                    new Small(new Muted('('.$tblPerson->getLastFirstName().' ist '.$RelationshipListFrom.')'));
                            } else {
                                $RelationshipListFrom = '';
                            }
                            if (!empty( $RelationshipListTo )) {
                                $RelationshipListTo = implode(', ', $RelationshipListTo);
                                $RelationshipListTo =
                                    new Small(new Muted('('.$RelationshipListTo.' für '.$tblPerson->getLastFirstName().')'));
                            } else {
                                $RelationshipListTo = '';
                            }

                            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                            if ($tblStudent) {
                                $StudentNumber = $tblStudent->getIdentifier();
                            } else {
                                $StudentNumber = new Small(new Muted('-NA-'));
                            }

                            $dataList[] = array(
                                'Number'          => ++$count,
                                'Person'          => ( $tblAddressPerson->getServiceTblPerson()
                                    ? $tblAddressPerson->getServiceTblPerson()->getLastFirstName()
                                    : new Warning(new Exclamation().' Person nicht gefunden.') ),
                                'StudentNumber'   => $StudentNumber,
                                'PersonToAddress' => ( $tblPersonWithAddress
                                    ? $tblPersonWithAddress->getLastFirstName().' '.$RelationshipListFrom.' '.$RelationshipListTo
                                    : new Warning(new Exclamation().' Person nicht gefunden.') ),
                                'Address'         => ( $tblAddressPerson->getServiceTblToPerson()
                                    ? $tblAddressPerson->getServiceTblToPerson()->getTblAddress()->getGuiString()
                                    : new Warning(new Exclamation().' Adresse nicht gefunden.') ),
                                'Salutation'      => $tblAddressPerson->getServiceTblSalutation()
                                    ? $tblAddressPerson->getServiceTblSalutation()->getSalutation()
                                    : new Warning(new Exclamation().' Keine Anrede hinterlegt.'),
                                'Option'          => new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                    array('Id'       => $Id,
                                          'PersonId' => $tblPerson->getId(),
                                          'Route'    => '/Reporting/SerialLetter/Export'))
                            );
                        }
                    } else {
                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                        if ($tblStudent) {
                            $StudentNumber = $tblStudent->getIdentifier();
                        } else {
                            $StudentNumber = new Small(new Muted('-NA-'));
                        }

                        $dataList[] = array(
                            'Number'          => ++$count,
                            'Person'          => $tblPerson->getLastFirstName(),
                            'StudentNumber'   => $StudentNumber,
                            'PersonToAddress' => new Warning(new Exclamation().' Keine Person mit Adresse hinterlegt.'),
                            'Address'         => '',
                            'Salutation'      => '',
                            'Option'          => new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                array('Id'       => $Id,
                                      'PersonId' => $tblPerson->getId(),
                                      'Route'    => '/Reporting/SerialLetter/Export'))
                        );
                    }
                }
            }

            if ($countAddresses > 0) {
                $Stage->addButton(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                        '/Api/Reporting/SerialLetter/Download', new Download(),
                        array('Id' => $tblSerialLetter->getId()))
                );
            }

            $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
            $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
                'Adresse(n): '.$SerialLetterCount,);
            $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                    .' Person(en)', Label::LABEL_TYPE_INFO)
            );

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            $countAddresses == 0
                                ? new LayoutColumn(
                                new WarningMessage('Keine Adressen ausgewählt.',
                                    new Exclamation())
                            )
                                : null,
                            new LayoutColumn(
                                new Title(new EyeOpen().' Adressen', 'Übersicht')
                            ),
                            new LayoutColumn(
                                new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                                6),
                            new LayoutColumn(
                                new TableData(
                                    $dataList, null, $columnList
                                )
                            )
                        ))
                    )
                )
            );

            return $Stage;
        } else {
            return $Stage.new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendSerialLetterDestroy($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Löschen');
        if ($Id) {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft())
            );
            $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
            if (!$tblSerialLetter) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gefunden werden.'),
                            new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Adressliste für Serienbriefe', new Bold($tblSerialLetter->getName()).
                                ( $tblSerialLetter->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    .new Muted(new Small(new Small($tblSerialLetter->getDescription()))) : '' ),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(new Question().' Diese Adressliste für Serienbriefe wirklich löschen?', array(
                                $tblSerialLetter->getName().' '.$tblSerialLetter->getDescription()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Reporting/SerialLetter/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                .new Standard(
                                    'Nein', '/Reporting/SerialLetter', new Disable()
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( SerialLetter::useService()->destroySerialLetter($tblSerialLetter)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Adressliste für Serienbriefe wurde gelöscht')
                                    : new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_SUCCESS)
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban().' Daten nicht abrufbar.'),
                        new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
