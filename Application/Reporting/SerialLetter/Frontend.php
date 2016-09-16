<?php

namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYearPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\ViewSchoolType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
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
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

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
     *
     * @return Stage|string
     */
    public function frontendSerialLetterPersonSelected($Id = null, $FilterGroup = null, $FilterStudent = null, $FilterPerson = null, $FilterYear = null, $FilterType = null)
    {

        $Stage = new Stage('Personen für Serienbriefe', 'Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = ( $Id == null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $Filter = false;
        // No Filter Detected
        if (
            $FilterGroup === null
            && $FilterStudent === null
            && $FilterPerson === null
            && $FilterYear === null
            && $FilterType === null
        ) {
            $FilterGroup['TblGroup_Name'] = 'Schüler';
            $Global = $this->getGlobal();
            // set Group Student
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

            $Pile = new Pile();
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(), null, 'TblMember_serviceTblPerson');
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(), ViewPerson::TBL_PERSON_ID, null);
            // Group->Person
        }
        // Database Join with foreign Key
        if ($FilterStudent) {
            $Filter = $FilterStudent;

            $Pile = new Pile();
            $Pile->addPile(( new ViewYearPeriod() )->getViewService(), new ViewYearPeriod(), null, 'TblYear_Id');
            $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(), 'TblDivision_serviceTblYear', 'TblDivisionStudent_serviceTblPerson');
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(), ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID);
            $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(), 'TblDivisionStudent_serviceTblPerson', 'TblLevel_serviceTblType');
            $Pile->addPile(( new ViewSchoolType() )->getViewService(), new ViewSchoolType(), 'TblType_Id', null);
            // Term->Division->Person->Division->SchoolType
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
                    0 => $FilterYear,
                    1 => $Filter,
                    2 => $FilterPerson,
                    4 => $FilterType
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
            } else {
                /** @var array $DataPerson */
                $DataPerson = $Row[2]->__toArray();
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Exchange'] = (string)new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                'Id'       => $Id,
                'PersonId' => $DataPerson['TblPerson_Id']
            ));
            $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Name'] = $tblPerson->getLastFirstName();
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
            if ($tblAddress) {
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = $tblAddress->getGuiString();
            }

            // ignore duplicated Person
            if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
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
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Address = new WarningMessage('Keine Adresse hinterlegt!');
                if ($tblAddress) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->Address = $tblAddress->getGuiString();
                }

            });
        }

        $FormGroup = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(array(
                        new AutoCompleter('FilterGroup[TblGroup_Name]', 'Gruppe: Name', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll())),
                    ), 6),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Person: Vorname', 'Person: Vorname'),
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Person: Nachname', 'Person: Nachname')
                    ), 6)
                ))
            )
            , new Primary('in Gruppen suchen'));

        $FormStudent = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new AutoCompleter('FilterYear[TblYear_Name]', 'Bildung: Schuljahr', 'Bildung: Schuljahr', array('Name' => Term::useService()->getYearAll())),
                    ), 6),
                    new FormColumn(array(
                        new AutoCompleter('FilterType[TblType_Name]', 'Bildung: Schulart', 'Bildung: Schulart', array('Name' => Type::useService()->getTypeAll())),
                    ), 6),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new TextField('FilterStudent[TblLevel_Name]', 'Klasse: Stufe', 'Klasse: Stufe'),
                        new TextField('FilterStudent[TblDivision_Name]', 'Klasse: Gruppe', 'Klasse: Gruppe'),
                    ), 6),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Person: Vorname', 'Person: Vorname'),
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Person: Nachname', 'Person: Nachname')
                    ), 6)
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
            .( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                array('Id' => $tblSerialLetter->getId()), 'Addressen auswählen') )
            .( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                array('Id' => $tblSerialLetter->getId()),
                'Addressliste für Serienbriefe anzeigen und herunterladen') )
        );

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
                                            new TableData($tblPersonList, null,
                                                array('Exchange' => '',
                                                      'Name'     => 'Name',
                                                      'Address'  => 'Adresse'),
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
                                                        ),
                                                        'Connect' => array(
                                                            'From' => 'TableCurrent',
                                                            'To'   => 'TableAvailable',
                                                        )
                                                    )
                                                )
                                            )
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
                                        new LayoutColumn(
                                            new Panel(new Search() . ' Personen-Suche nach ' . new Bold('Personengruppe'), array(
                                                $FormGroup
                                            ), Panel::PANEL_TYPE_INFO)
                                            , 6
                                        ),
                                        new LayoutColumn(
                                            new Panel(new Search() . ' Personen-Suche nach ' . new Bold('Klasse / Schüler'), array(
                                                $FormStudent
                                            ), Panel::PANEL_TYPE_INFO)
                                            , 6
                                        ),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            ($Timeout === true
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

                                                new TableData($tblPersonSearch, null,
                                                array('Exchange' => ' ',
                                                      'Name'     => 'Name',
                                                      'Address'  => 'Adresse'), array(
                                                        'order'            => array(array(1, 'asc')),
                                                    'columnDefs' => array(
                                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                                    ),
                                                    'ExtensionRowExchange' => array(
                                                        'Enabled' => true,
                                                        'Url'     => '/Api/Reporting/SerialLetter/Exchange',
                                                        'Handler' => array(
                                                            'From' => 'glyphicon-plus-sign',
                                                            'To'   => 'glyphicon-minus-sign'
                                                        ),
                                                        'Connect' => array(
                                                            'From' => 'TableAvailable',
                                                            'To'   => 'TableCurrent',
                                                        ),
                                                    )
                                                )
                                            )
                                        ), 12)
                                    ))
                                ))
                            )
                        ), 6)
                    )),
                ))
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
        $tblPersonList = SerialLetter::useService()->getPersonBySerialLetter($tblSerialLetter);
        if (!$tblPersonList) {
            return $Stage.new Danger('Es sind keine Personen dem Serienbrief zugeordnet', new Exclamation());
        }

        $TableContent = array();
        array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $Id, $tblSerialLetter) {
            $Item['Name'] = $tblPerson->getLastFirstName();
            $Item['Address'] = array();
            $Item['Option'] = new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                array('Id'       => $Id,
                      'PersonId' => $tblPerson->getId()));

            $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
            if ($tblAddressPersonList) {
                $Data = array();
                foreach ($tblAddressPersonList as $tblAddressPerson) {

                    if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                        if ($tblAddressPerson->getServiceTblSalutation()) {
                            if ($tblPersonTo = $tblToPerson->getServiceTblPerson()) {
                                $Data[] = $tblAddressPerson->getServiceTblSalutation()->getSalutation().' '.$tblPersonTo->getLastFirstName();
                            }
                        } else {
                            if ($tblPersonTo = $tblToPerson->getServiceTblPerson()) {
                                $Data[] = $tblPersonTo->getLastFirstName();
                            }
                        }
                        if (( $tblAddress = $tblToPerson->getTblAddress() )) {
                            $Data[] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                $Data[] = $tblCity->getCode().' '.$tblCity->getDisplayName();
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
            .( new Standard('', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen') )
            .( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                array('Id' => $tblSerialLetter->getId()),
                'Addressliste für Serienbriefe anzeigen und herunterladen') )
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
                                'Name'    => 'Name',
                                'Address' => 'Serienbrief Adresse',
                                'Option'  => '',
                            ), array(
                                'columnDefs' => array(
                                    array('orderable' => false, 'width' => '1%', 'targets' => -1),
                                    array('width' => '15%', 'targets' => 0)
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
     * @param null $Id
     * @param null $PersonId
     * @param null $Check
     *
     * @return Stage|string
     */
    public function frontendPersonAddressEdit($Id = null, $PersonId = null, $Check = null)
    {

        $Stage = new Stage('Adresse(n)', 'Auswählen');
        $tblSerialLetter = ( $Id === null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id)));
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
            'Person'     => 'Person',
//            'Relationship' => 'Beziehung',
            'Address'    => 'Adressen',
            'Salutation' => 'Anrede'
        );

        $personCount = 0;
        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
        if ($tblAddressToPersonList) {
            foreach ($tblAddressToPersonList as $tblToPerson) {

                $dataList[$tblPerson->getId()]['Number'] = ++$personCount;
                $dataList[$tblPerson->getId()]['Person'] = $tblPerson->getLastFirstName();
                $subDataList[] = array(
                    'Person'     => $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getFullName() : '',
//                    'Relationship' => '',
                    'Address'    => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Address]',
                        '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1),
                    'Salutation' => new SelectBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Salutation]',
                        '', array('Salutation' => $tblSalutationAll))
                );
            }
        }

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        $PersonToPersonId = array();
        if ($tblRelationshipAll) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                    if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonFrom());
                    } else {
                        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonTo());
                    }
                    if ($tblAddressToPersonList) {
                        foreach ($tblAddressToPersonList as $tblToPerson) {

                            $PersonIdAddressIdNow = ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getId() : '' ).'.'.
                                ( $tblToPerson->getTblAddress() ? $tblToPerson->getTblAddress()->getId() : '' );
                            // ignore duplicated Person by Relationship
                            if (!array_key_exists($PersonIdAddressIdNow, $PersonToPersonId)) {
                                $subDataList[] = array(
                                    'Person'     => $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getFullName() : '',
//                                    'Relationship' => $tblRelationship->getTblType()->getName(),
                                    'Address'    => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Address]',
                                        '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1),
                                    'Salutation' => new SelectBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Salutation]',
                                        '', array('Salutation' => $tblSalutationAll))
                                );
                                $PersonToPersonId[$PersonIdAddressIdNow] = $PersonIdAddressIdNow;
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
                            new TableData($subDataList, null, $columnList, null),
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
            .( new Standard('', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen') )
            .( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen') )
            .( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                array('Id' => $tblSerialLetter->getId()),
                'Addressliste für Serienbriefe anzeigen und herunterladen') )
        );

        $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
        $PanelPerson = new Panel(
            $tblPerson->getFullName(),
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
                                    $Form, $tblSerialLetter, $Check
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

            $dataList = array();
            $columnList = array(
                'Number'          => 'Nr.',
                'Person'          => 'Person',
                'Salutation'      => 'Anrede',
                'PersonToAddress' => 'Adressat',
                'Address'         => 'Adresse',
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
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
                /** @var TblPerson $tblPerson */
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                        $tblPerson);
                    if ($tblAddressPersonAllByPerson) {
                        foreach ($tblAddressPersonAllByPerson as $tblAddressPerson) {
                            if ($tblAddressPerson->getServiceTblToPerson()
                                && $tblAddressPerson->getServiceTblToPerson()->getTblAddress()
                            ) {
                                $countAddresses++;
                            }
                            $dataList[] = array(
                                'Number'          => ++$count,
                                'Person'          => ( $tblAddressPerson->getServiceTblPerson()
                                    ? $tblAddressPerson->getServiceTblPerson()->getLastFirstName()
                                    : new Warning(new Exclamation().' Person nicht gefunden.') ),
                                'PersonToAddress' => ( $tblAddressPerson->getServiceTblPersonToAddress()
                                    ? $tblAddressPerson->getServiceTblPersonToAddress()->getLastFirstName()
                                    : new Warning(new Exclamation().' Person nicht gefunden.') ),
                                'Address'         => ( $tblAddressPerson->getServiceTblToPerson()
                                    ? $tblAddressPerson->getServiceTblToPerson()->getTblAddress()->getGuiString()
                                    : new Warning(new Exclamation().' Adresse nicht gefunden.') ),
                                'Salutation'      => $tblAddressPerson->getServiceTblSalutation()
                                    ? $tblAddressPerson->getServiceTblSalutation()->getSalutation()
                                    : new Warning(new Exclamation().' Keine Anrede hinterlegt.')
                            );
                        }
                    } else {
                        $dataList[] = array(
                            'Number'          => ++$count,
                            'Person'          => $tblPerson->getLastFirstName(),
                            'PersonToAddress' => new Warning(new Exclamation().' Keine Person mit Adresse hinterlegt.'),
                            'Address'         => '',
                            'Salutation'      => ''
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
                .( new Standard('', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                    array('Id' => $tblSerialLetter->getId()), 'Personen auswählen') )
                .( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                    array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen') )
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
     * @param $Id
     * @param bool|false $Confirm
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
                            new Danger(new Ban() . ' Die Adressliste für Serienbriefe konnte nicht gefunden werden.'),
                            new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Adressliste für Serienbriefe', new Bold($tblSerialLetter->getName()) .
                                ($tblSerialLetter->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblSerialLetter->getDescription()))) : ''),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(new Question() . ' Diese Adressliste für Serienbriefe wirklich löschen?', array(
                                $tblSerialLetter->getName() . ' ' . $tblSerialLetter->getDescription()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Reporting/SerialLetter/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                . new Standard(
                                    'Nein', '/Reporting/SerialLetter', new Disable()
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (SerialLetter::useService()->destroySerialLetter($tblSerialLetter)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Adressliste für Serienbriefe wurde gelöscht')
                                    : new Danger(new Ban() . ' Die Adressliste für Serienbriefe konnte nicht gelöscht werden')
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
                        new Danger(new Ban() . ' Daten nicht abrufbar.'),
                        new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
