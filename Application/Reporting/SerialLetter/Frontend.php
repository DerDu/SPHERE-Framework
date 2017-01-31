<?php
namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\ViewPeopleMetaProspect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterCategory;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialPerson;
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
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
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
use SPHERE\System\Database\Binding\AbstractView;
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
     * @param null   $SerialLetter
     * @param string $TabActive
     * @param null   $FilterGroup
     * @param null   $FilterPerson
     * @param null   $FilterStudent
     * @param null   $FilterYear
     * @param null   $FilterProspect
     * @param null   $FilterCompany
     * @param null   $FilterRelationship
     *
     * @return Stage
     */
    public function frontendSerialLetter(
        $SerialLetter = null,
        $TabActive = 'STATIC',
        $FilterGroup = null,
        $FilterPerson = null,
        $FilterStudent = null,
        $FilterYear = null,
        $FilterProspect = null,
        $FilterCompany = null,
        $FilterRelationship = null
    ) {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Übersicht');
        $tblSerialLetterAll = SerialLetter::useService()->getSerialLetterAll();

        // create Tabs
        $LayoutTabs[] = new LayoutTab('Statisch', 'STATIC');
        if (!empty($LayoutTabs) && $TabActive === 'PERSON') {
            $LayoutTabs[0]->setActive();
        }
        $LayoutTabs[] = new LayoutTab('Dynamisch (Personengruppe)', 'PERSONGROUP');
        $LayoutTabs[] = new LayoutTab('Dynamisch (Schüler)', 'STUDENT');
        $LayoutTabs[] = new LayoutTab('Dynamisch (Interessenten)', 'PROSPECT');
        $LayoutTabs[] = new LayoutTab('Dynamisch (Firmengruppe)', 'COMPANY');

        $TableContent = array();
        if ($tblSerialLetterAll) {
            array_walk($tblSerialLetterAll, function (TblSerialLetter $tblSerialLetter) use (&$TableContent) {
                $tblFilterCategory = $tblSerialLetter->getFilterCategory();


                $Item['Name'] = $tblSerialLetter->getName();
                $Item['Description'] = $tblSerialLetter->getDescription();
                $Item['Category'] = ( $tblSerialLetter->getFilterCategory()
                    ? new Info('Serienbrief dynamisch ').new Bold($tblSerialLetter->getFilterCategory()->getName())
                    : new Info('Serienbrief statisch') );
                $Item['Option'] =
                    ( $tblFilterCategory
                        ? ( new Standard('', '/Reporting/SerialLetter/Edit', new Edit(),
                            array('Id' => $tblSerialLetter->getId(), 'Control' => true), 'Bearbeiten') )
                        : ( new Standard('', '/Reporting/SerialLetter/Edit', new Edit(),
                            array('Id' => $tblSerialLetter->getId()), 'Bearbeiten') )
                    )
                    .( new Standard('', '/Reporting/SerialLetter/Destroy', new Remove(),
                        array('Id' => $tblSerialLetter->getId()), 'Löschen') )
                    .
                    ( $tblFilterCategory
                        ? ''
                        : new Standard('', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                            array('Id' => $tblSerialLetter->getId()), 'Personen auswählen')
                    )
                    .
                    ( $tblFilterCategory
                        ? ( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                            array('Id' => $tblSerialLetter->getId(), 'Control' => true), 'Addressen auswählen') )
                        : ( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                            array('Id' => $tblSerialLetter->getId()), 'Addressen auswählen') )
                    )
                    .( $tblFilterCategory
                        ? ( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                            array('Id' => $tblSerialLetter->getId(), 'Control' => true),
                            'Addressliste für Serienbriefe anzeigen und herunterladen') )
                        : ( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                            array('Id' => $tblSerialLetter->getId()),
                            'Addressliste für Serienbriefe anzeigen und herunterladen') )
                    );
                array_push($TableContent, $Item);
            });
        }

        $FormSerialLetter = $this->formSerialLetter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Timeout = null;
//        $TableSearch = array();
//        $IsFilter = false;


        switch ($TabActive) {
            case 'STATIC':
                $MetaTable = new Panel(new PlusSign().' Serienbrief anlegen '
                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetter, $SerialLetter))), Panel::PANEL_TYPE_INFO);
                break;
            case 'PERSONGROUP':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName('Personengruppe');

//                // Filter Group
//                if (isset( $FilterGroup['TblGroup_Id'] ) && !empty( $FilterGroup['TblGroup_Id'] )
//                ) {
////                    $Result = SerialLetter::useService()->getGroupFilterResultListBySerialLetter(null, $FilterGroup, $Timeout);
////                    if ($Result) {
////                        $TableSearch = $this->getGroupTableByResult($Result);
////                    }
//                }

//                if (isset( $FilterGroup['TblGroup_Id'] ) && !empty( $FilterGroup['TblGroup_Id'] )) {
//                    if (is_array($FilterGroup['TblGroup_Id'])) {
//                        $FilterGroup['TblGroup_Id'] = implode(' ', $FilterGroup['TblGroup_Id']);
//                    }
//                }

                $FormSerialLetterDynamic = $this->formFilterPersonGroup();
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PlusSign().' Serienbrief anlegen '
                                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic,
                                        $SerialLetter, $FilterGroup, null, null, null, null, null, null, $tblFilterCategory->getId())))
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                );
                break;
            case 'STUDENT':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName('Schüler');

                $FormSerialLetterDynamic = $this->formFilterStudent();
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PlusSign().' Serienbrief anlegen '
                                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic
                                        , $SerialLetter, $FilterGroup, $FilterPerson, $FilterStudent, $FilterYear, null, null, null, $tblFilterCategory->getId())))
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                );
                break;
            case 'PROSPECT':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName('Interessenten');

                $FormSerialLetterDynamic = $this->formFilterProspect();
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PlusSign().' Serienbrief anlegen '
                                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic,
                                        $SerialLetter, $FilterGroup, null, null, null, $FilterProspect, null, null, $tblFilterCategory->getId())))
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                );
                break;
            case 'COMPANY':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName('Firmengruppe');

                $FormSerialLetterDynamic = $this->formFilterCompany();
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PlusSign().' Serienbrief anlegen '
                                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic,
                                        $SerialLetter, $FilterGroup, null, null, null, null, $FilterCompany, $FilterRelationship, $tblFilterCategory->getId())))
                                    , Panel::PANEL_TYPE_INFO)
                            )
                        )
                    )
                );
                break;
            default:
                $MetaTable = new Panel(new PlusSign().' Serienbrief anlegen '
                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetter, $SerialLetter))), Panel::PANEL_TYPE_INFO);
        }
        if (!empty($LayoutTabs) && $TabActive === 'STATIC') {
            $LayoutTabs[0]->setActive();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null, array(
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Category'    => 'Kategorie',
                                'Option'      => '',
                            ), array(
                                'columnDefs' => array(
                                    array('orderable' => false, 'width' => '180px', 'targets' => 3)
                                )
                            ))
                        ))
                    ))
                ), new Title(new ListingTable().' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new LayoutTabs($LayoutTabs),
                            $MetaTable
                        ))
                    ))
                ), new Title(new PlusSign().' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param $Result
     *
     * @return array
     */
    private function getGroupTableByResult($Result)
    {
        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewPerson[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }
        return $TableSearch;
    }

    /**
     * @param $Result
     *
     * @return array
     */
    private function getStudentTableByResult($Result)
    {
        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewDivisionStudent[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();
                $tblDivisionStudent = $Row[2]->getTblDivisionStudent();

                $DataPerson['DivisionYear'] = new Small(new Muted('Gefiltertes Jahr:')).new Container('-NA-');
                $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                /** @var TblDivisionStudent $tblDivisionStudent */
                if ($tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        if (( $tblYear = $tblDivision->getServiceTblYear() )) {
                            $DataPerson['DivisionYear'] = new Small(new Muted('Gefiltertes Jahr:')).new Container($tblYear->getName());
                        }
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container($tblDivision->getDisplayName());
                    }
                }

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }
                $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
                if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                    $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }
        return $TableSearch;
    }

    /**
     * @param $Result
     *
     * @return array
     */
    private function getProspectTableByResult($Result)
    {

        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewPerson[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));
                $DataPerson['ReservationDate'] = '';
                $DataPerson['InterviewDate'] = '';
                $DataPerson['TrialDate'] = '';
                $DataPerson['ReservationYear'] = '';
                $DataPerson['ReservationDivision'] = '';
                $DataPerson['ReservationOptionA'] = '';
                $DataPerson['ReservationOptionB'] = '';

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);

                    $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                    if ($tblProspect) {
                        $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                        if ($tblProspectAppointment) {
                            $DataPerson['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                            $DataPerson['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                            $DataPerson['TrialDate'] = $tblProspectAppointment->getTrialDate();
                        }
                        $tblProspectReservation = $tblProspect->getTblProspectReservation();
                        if ($tblProspectReservation) {
                            $DataPerson['ReservationYear'] = $tblProspectReservation->getReservationYear();
                            $DataPerson['ReservationDivision'] = $tblProspectReservation->getReservationDivision();
                            if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                                $DataPerson['ReservationOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                            }
                            if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                                $DataPerson['ReservationOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                            }
                        }
                    }
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }
        return $TableSearch;
    }

    /**
     * @param $Result
     *
     * @return array
     */
    private function getCompanyTableByResult($Result)
    {

        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewCompany[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataCompany = $Row[1]->__toArray();
                $DataPerson = $Row[3]->__toArray();

                $tblCompany = Company::useService()->getCompanyById($DataCompany['TblCompany_Id']);
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByCompany($tblCompany);
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }

                $DataPerson['CompanyName'] = '';
                $DataPerson['CompanyExtendedName'] = '';
                $DataPerson['Type'] = '';
                if ($tblCompany) {
                    $DataPerson['CompanyName'] = $tblCompany->getName();
                    $DataPerson['CompanyExtendedName'] = $tblCompany->getExtendedName();
                    $tblRelationshipList = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany);
                    if ($tblRelationshipList) {
                        /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToCompany $tblRelationship */
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getServiceTblPerson()->getId() === $tblPerson->getId()) {
                                if ($tblRelationship->getTblType()) {
                                    $DataPerson['Type'] = $tblRelationship->getTblType()->getName();
                                }
                            }
                        }
                    }
                }


                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }
        return $TableSearch;
    }

    private function formSerialLetterStandardGroup()
    {

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('SerialLetter[Name]', 'Name', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('SerialLetter[Description]', 'Beschreibung', 'Beschreibung'), 8
                )
            )),
        ));
    }

    /**
     * @return Form
     */
    private function formSerialLetter()
    {

        return new Form($this->formSerialLetterStandardGroup());
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Form
     */
    private function formFilterPersonGroup(TblSerialLetter $tblSerialLetter = null)
    {
        $FormGroup = array();
        $FormGroup[] = $this->formSerialLetterStandardGroup();

        if ($tblSerialLetter != null) {
            $FormGroup[] = new FormGroup(new FormRow(new FormColumn(
                new InfoMessage('Filter können über das leeren der Gruppe entfernt werden "-[Nicht ausgewählt]-"')
            )));
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                $FilterCount = 1;
                foreach ($tblFilterFieldList as $tblFilterField) {
                    // found Filter
                    $FormGroup[] = new FormGroup(new FormRow(array(
                            new FormColumn(
                                new SelectBox('FilterGroup['.$tblFilterField->getField().']['.$tblFilterField->getFilterNumber().']',
                                    'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                                , 3),
                        ))
//                        , new TitleForm('Aktiver Filter '.$FilterCount)
                    );
                    $FilterCount++;
                }
            }
            // new Filter
            $FormGroup[] = new FormGroup(new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterGroup[TblGroup_Id][]',
                        'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                    , 3),
            )));
        } else {
            // first Filter
            $FormGroup[] = new FormGroup(new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterGroup[TblGroup_Id][0]',
                        'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                    , 3),
            )));
        }

        return new Form(
            $FormGroup
        );
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Form
     */
    private function formFilterStudent(TblSerialLetter $tblSerialLetter = null)
    {

        $GroupList = array();
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        if ($tblGroup) {
            $GroupList[] = $tblGroup;
        }
        $LevelList = array();
        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as $tblLevel) {
                if ($tblLevel->getName() !== '') {
                    $LevelList[] = $tblLevel;
                }
            }
        }

        $FormGroup = array();
        $FormGroup[] = $this->formSerialLetterStandardGroup();
        if ($tblSerialLetter != null) {
            $FormGroup[] = new FormGroup(new FormRow(new FormColumn(
                new InfoMessage('Filter ohne Gruppe werden ignoriert! Filter können über das leeren aller Informationen entfernt werden "-[Nicht ausgewählt]-"')
            )));
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                $FilterArray = array();
                foreach ($tblFilterFieldList as $tblFilterField) {
                    $FilterArray[$tblFilterField->getFilterNumber()][] = $tblFilterField;
                }
                if (!empty($FilterArray)) {
                    foreach ($FilterArray as $FilterNumber => $FilterFieldList) {
                        // found Filter
                        $FormGroup[] = new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    new SelectBox('FilterGroup[TblGroup_Id]['.$FilterNumber.']', 'Gruppe: Name', array('Name' => $GroupList))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('FilterYear[TblYear_Id]['.$FilterNumber.']', 'Bildung: Schuljahr',
                                        array('{{Name}} {{Description}}' => Term::useService()->getYearAll()))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('FilterStudent[TblLevel_Id]['.$FilterNumber.']', 'Klasse: Stufe',
                                        array('{{ Name }} {{ serviceTblType.Name }}' => $LevelList))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterStudent[TblDivision_Name]['.$FilterNumber.']', 'Klasse: Gruppe', '',
                                        array('Name' => Division::useService()->getDivisionAll()))
                                    , 3),
                            ))
                        ));
//                        /** @var TblFilterField $tblFilterField */
//                        foreach($FilterFieldList as $tblFilterField){
//                            if($tblFilterField->getField() === 'TblGroup_Id'){
//                                $Global->POST['FilterGroup'][$FilterNumber][$tblFilterField->getField()] = $tblFilterField->getValue();
//                            }
//                            if($tblFilterField->getField() === 'TblYear_Id'){
//                                $Global->POST['FilterYear'][$FilterNumber][$tblFilterField->getField()] = $tblFilterField->getValue();
//                            }
//                            if($tblFilterField->getField() === 'TblLevel_Id'){
//                                $Global->POST['FilterLevel'][$FilterNumber][$tblFilterField->getField()] = $tblFilterField->getValue();
//                            }
//                            if($tblFilterField->getField() === 'TblDivision_Name'){
//                                $Global->POST['FilterDivision'][$FilterNumber][$tblFilterField->getField()] = $tblFilterField->getValue();
//                            }
//                        }
                    }
                }
            }
            // new Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][]', 'Gruppe: Name', array('Name' => $GroupList))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterYear[TblYear_Id][]', 'Bildung: Schuljahr',
                            array('{{Name}} {{Description}}' => Term::useService()->getYearAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterStudent[TblLevel_Id][]', 'Klasse: Stufe',
                            array('{{ Name }} {{ serviceTblType.Name }}' => $LevelList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterStudent[TblDivision_Name][]', 'Klasse: Gruppe', '',
                            array('Name' => Division::useService()->getDivisionAll()))
                        , 3),
                ))
            ));
        } else {
            // first Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][0]', 'Gruppe: Name', array('Name' => $GroupList))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterYear[TblYear_Id][0]', 'Bildung: Schuljahr',
                            array('{{Name}} {{Description}}' => Term::useService()->getYearAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterStudent[TblLevel_Id][0]', 'Klasse: Stufe',
                            array('{{ Name }} {{ serviceTblType.Name }}' => $LevelList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterStudent[TblDivision_Name][0]', 'Klasse: Gruppe', '',
                            array('Name' => Division::useService()->getDivisionAll()))
                        , 3),
                ))
            ));
            // POST StandardGroup (first Visit)
            $Global = $this->getGlobal();
            if (!isset($Global->POST['FilterGroup']['TblGroup_Id'][0])) {
                if (!isset($Global->POST['FilterGroup']['TblGroup_Id'][0])) {
                    $Global->POST['FilterGroup']['TblGroup_Id'][0] = $tblGroup->getId();
                }
                $Global->savePost();
            }
        }

        return new Form(
            $FormGroup
        );
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Form
     */
    private function formFilterProspect(TblSerialLetter $tblSerialLetter = null)
    {

        $GroupList = array();
        $tblGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
        if ($tblGroup) {
//            $GroupList[] = '';
            $GroupList[] = $tblGroup;
        }

        $FormGroup = array();
        $FormGroup[] = $this->formSerialLetterStandardGroup();
        if ($tblSerialLetter != null) {
            $FormGroup[] = new FormGroup(new FormRow(new FormColumn(
                new InfoMessage('Filter ohne Gruppe werden ignoriert! Filter können über das leeren aller Informationen entfernt werden "-[Nicht ausgewählt]-"')
            )));
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                $FilterArray = array();
                foreach ($tblFilterFieldList as $tblFilterField) {
                    $FilterArray[$tblFilterField->getFilterNumber()][] = $tblFilterField;
                }
                if (!empty($FilterArray)) {
                    foreach ($FilterArray as $FilterNumber => $FilterFieldList) {
                        // found Filter
                        $FormGroup[] = new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    new SelectBox('FilterGroup[TblGroup_Id]['.$FilterNumber.']', 'Gruppe: Name', array('Name' => $GroupList))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterProspect[TblProspectReservation_ReservationYear]['.$FilterNumber.']', 'Interessent: Schuljahr',
                                        '', array('ReservationYear' => Prospect::useService()->getProspectReservationAll()))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterProspect[TblProspectReservation_ReservationDivision]['.$FilterNumber.']', 'Interessent: Stufe',
                                        '', array('ReservationDivision' => Prospect::useService()->getProspectReservationAll()))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('FilterProspect[TblProspectReservation_serviceTblTypeOptionA]['.$FilterNumber.']', 'Schulart:'
                                        , array('Name' => Type::useService()->getTypeAll()))
                                    , 3),
                            ))
                        ));
//                        /** @var TblFilterField $tblFilterField */
//                        foreach($FilterFieldList as $tblFilterField){
//                            if($tblFilterField->getField() === 'TblGroup_Id'){
//                                $Global->POST['FilterGroup'][$FilterNumber][$tblFilterField->getField()] = $tblFilterField->getValue();
//                            } elseif($tblFilterField->getField() === 'TblProspectReservation_ReservationYear'
//                            ||$tblFilterField->getField() === 'TblProspectReservation_ReservationDivision'
//                            ||$tblFilterField->getField() === 'TblProspectReservation_serviceTblTypeOptionA'){
//                                $Global->POST['FilterProspect'][$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
//                            }
//                        }
                    }
                }
            }
            // new Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][]', 'Gruppe: Name', array('Name' => $GroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationYear][]', 'Interessent: Schuljahr',
                            '', array('ReservationYear' => Prospect::useService()->getProspectReservationAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationDivision][]', 'Interessent: Stufe',
                            '', array('ReservationDivision' => Prospect::useService()->getProspectReservationAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterProspect[TblProspectReservation_serviceTblTypeOptionA][]', 'Schulart:'
                            , array('Name' => Type::useService()->getTypeAll()))
                        , 3),
                ))
            ));
        } else {
            // first Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][0]', 'Gruppe: Name', array('Name' => $GroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationYear][0]', 'Interessent: Schuljahr',
                            '', array('ReservationYear' => Prospect::useService()->getProspectReservationAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationDivision][0]', 'Interessent: Stufe',
                            '', array('ReservationDivision' => Prospect::useService()->getProspectReservationAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterProspect[TblProspectReservation_serviceTblTypeOptionA][0]', 'Schulart:'
                            , array('Name' => Type::useService()->getTypeAll()))
                        , 3),
                ))
            ));
            // POST StandardGroup (first Visit)
            $Global = $this->getGlobal();
            if (!isset($Global->POST['FilterGroup']['TblGroup_Id'][0])) {
                $Global->POST['FilterGroup']['TblGroup_Id'][0] = $tblGroup->getId();
                $Global->savePost();
            }
        }

        return new Form(
            $FormGroup
        );
    }

    /**
     * @param null $tblSerialLetter
     *
     * @return Form
     */
    private function formFilterCompany($tblSerialLetter = null)
    {

        $tblGroupList = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupAll();
        $tblGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
        $tblCompanyGroup = Relationship::useService()->getGroupByIdentifier('COMPANY');
        $TypeList = Relationship::useService()->getTypeAllByGroup($tblCompanyGroup);

        $FormGroup = array();
        $FormGroup[] = $this->formSerialLetterStandardGroup();
        if ($tblSerialLetter != null) {
            $FormGroup[] = new FormGroup(new FormRow(new FormColumn(
                new InfoMessage('Filter ohne Gruppe werden ignoriert! Filter können über das leeren aller Informationen entfernt werden "-[Nicht ausgewählt]-"')
            )));
            $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);
            if ($tblFilterFieldList) {
                $FilterArray = array();
                foreach ($tblFilterFieldList as $tblFilterField) {
                    $FilterArray[$tblFilterField->getFilterNumber()][] = $tblFilterField;
                }
                if (!empty($FilterArray)) {
                    foreach ($FilterArray as $FilterNumber => $FilterFieldList) {
                        // found Filter
                        $FormGroup[] = new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    new SelectBox('FilterGroup[TblGroup_Id]['.$FilterNumber.']', 'Gruppe: Name', array('Name' => $tblGroupList))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterCompany[TblCompany_Name]['.$FilterNumber.']', 'Firma: Name', '',
                                        array('Name' => Company::useService()->getCompanyAll()))
                                    , 3),
                                new FormColumn(
                                    new AutoCompleter('FilterCompany[TblCompany_ExtendedName]['.$FilterNumber.']', 'Firma: Zusatz', '',
                                        array('ExtendedName' => Company::useService()->getCompanyAll()))
                                    , 3),
                                new FormColumn(
                                    new SelectBox('FilterRelationship[TblType_Id]['.$FilterNumber.']', 'Beziehung: Typ', array('Name' => $TypeList))
                                    , 3),
                            ))
                        ));
                    }
                }
            }
            // new Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][]', 'Gruppe: Name', array('Name' => $tblGroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterCompany[TblCompany_Name][]', 'Firma: Name', '',
                            array('Name' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterCompany[TblCompany_ExtendedName][]', 'Firma: Zusatz', '',
                            array('ExtendedName' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterRelationship[TblType_Id][]', 'Beziehung: Typ', array('Name' => $TypeList))
                        , 3),
                ))
            ));
        } else {
            // First Filter
            $FormGroup[] = new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('FilterGroup[TblGroup_Id][0]', 'Gruppe: Name', array('Name' => $tblGroupList))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterCompany[TblCompany_Name][0]', 'Firma: Name', '',
                            array('Name' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterCompany[TblCompany_ExtendedName][0]', 'Firma: Zusatz', '',
                            array('ExtendedName' => Company::useService()->getCompanyAll()))
                        , 3),
                    new FormColumn(
                        new SelectBox('FilterRelationship[TblType_Id][0]', 'Beziehung: Typ', array('Name' => $TypeList))
                        , 3),
                ))
            ));
            // POST StandardGroup (first Visit)
            $Global = $this->getGlobal();
            if (!isset($Global->POST['FilterGroup']['TblGroup_Id'][0])) {
                $Global->POST['FilterGroup']['TblGroup_Id'][0] = $tblGroup->getId();
            }
            $Global->savePost();
        }

        return new Form(
            $FormGroup
        );
    }

    /**
     * @param null   $Id
     * @param bool   $Control
     * @param null   $SerialLetter
     * @param string $TabActive
     * @param null   $FilterGroup
     * @param null   $FilterStudent
     * @param null   $FilterYear
     * @param null   $FilterProspect
     * @param null   $FilterCompany
     * @param null   $FilterRelationship
     *
     * @return Stage|string
     */
    public function frontendSerialLetterEdit(
        $Id = null,
        $Control = false,
        $SerialLetter = null,
        $TabActive = null,
        $FilterGroup = null,
        $FilterStudent = null,
        $FilterYear = null,
        $FilterProspect = null,
        $FilterCompany = null,
        $FilterRelationship = null
    ) {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));

        if (!( $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id) )) {

            return $Stage
                .new Danger('Serienbrief nicht gefunden', new Exclamation())
                .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }

        $tblFilterCategory = $tblSerialLetter->getFilterCategory();
        if ($tblFilterCategory) {
            if ($Control) {
                SerialLetter::useService()->updateSerialPerson($tblSerialLetter, $tblFilterCategory);
            }
        }
        if ($TabActive === null) {
            if ($tblFilterCategory) {
                if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
                    $TabActive = 'PERSONGROUP';
                }
                if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
                    $TabActive = 'STUDENT';
                }
                if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                    $TabActive = 'PROSPECT';
                }
                if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                    $TabActive = 'COMPANY';
                }
            }
        }

        $Stage->addButton(new Standard(new Bold(new Info('Bearbeiten')), '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblSerialLetter->getFilterCategory()) {
            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Addressliste für Serienbriefe anzeigen und herunterladen'));

        $FormSerialLetter = $this->formSerialLetter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $tblFilterFieldList = SerialLetter::useService()->getFilterFieldAllBySerialLetter($tblSerialLetter);

        $Global = $this->getGlobal();
        // Post FilterField
        if ($FilterGroup === null && $FilterStudent === null && $FilterYear === null
            && $FilterProspect === null && $FilterCompany === null && $FilterRelationship === null
        ) {
            if ($tblFilterFieldList) {
                foreach ($tblFilterFieldList as $tblFilterField) {
                    $FilterNumber = $tblFilterField->getFilterNumber();
                    if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                        $FilterGroup[$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
                    }
                    // changed array order
                    if (stristr($tblFilterField->getField(), 'TblLevel_')) {
                        $FilterStudent[$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
                    }
                    // changed array order
                    if (stristr($tblFilterField->getField(), 'TblDivision_')) {
                        $FilterStudent[$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
                    }
                    if (stristr($tblFilterField->getField(), 'TblYear_')) {
                        $FilterYear[$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
                    }
                    // changed array order
                    if (stristr($tblFilterField->getField(), 'TblProspectReservation_')) {
                        $FilterProspect[$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
                    }
                    // changed array order
                    if (stristr($tblFilterField->getField(), 'TblCompany_')) {
                        $FilterCompany[$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
                    }
                    if (stristr($tblFilterField->getField(), 'TblType_')) {
                        $FilterRelationship[$tblFilterField->getField()][$FilterNumber] = $tblFilterField->getValue();
                    }
                }
            }
            $Global->POST['FilterGroup'] = $FilterGroup;
            $Global->POST['FilterStudent'] = $FilterStudent;
            $Global->POST['FilterYear'] = $FilterYear;
            $Global->POST['FilterProspect'] = $FilterProspect;
            $Global->POST['FilterCompany'] = $FilterCompany;
            $Global->POST['FilterRelationship'] = $FilterRelationship;
            $Global->savePost();
        }

        // Post SerialLetterName
        if ($SerialLetter == null) {
            $Global->POST['SerialLetter']['Name'] = $tblSerialLetter->getName();
            $Global->POST['SerialLetter']['Description'] = $tblSerialLetter->getDescription();
            $Global->savePost();
        }
        $Timeout = null;
        $SearchTable = array();

        // hide table while POST request
        $IsPost = false;
        if (isset($Global->POST['Button'])) {
            $IsPost = true;
        }

        switch ($TabActive) {
//            case 'STATIC':
//                $MetaTable = new Panel(new Edit().' Serienbrief'
//                    , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetter, $tblSerialLetter
//                        , $SerialLetter))), Panel::PANEL_TYPE_INFO);
//                break;
            case 'PERSONGROUP':

                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName(TblFilterCategory::IDENTIFIER_PERSON_GROUP);
                if ($tblFilterCategory) {
                    $CategoryName = $tblFilterCategory->getName();
                } else {
                    $CategoryName = '';
                }
                if (!$IsPost) {
                    $Result = SerialLetter::useService()->getGroupFilterResultListBySerialLetter($tblSerialLetter, null, $Timeout);
                    if ($Result) {
                        $SearchTable = $this->getGroupTableByResult($Result);
                    }
                }


//                // Filter Group
//                if (isset( $FilterGroup['TblGroup_Id'] ) && !empty( $FilterGroup['TblGroup_Id'] )
//                ) {
//                    $Result = SerialLetter::useService()->getGroupFilterResultListBySerialLetter(null, $FilterGroup, $Timeout);
//                    if ($Result) {
//                        $SearchTable = $this->getGroupTableByResult($Result);
//                    }
//                }

//                if (isset( $FilterGroup['TblGroup_Id'] ) && !empty( $FilterGroup['TblGroup_Id'] )) {
//                    if (is_array($FilterGroup['TblGroup_Id'])) {
//                        $FilterGroup['TblGroup_Id'] = implode(' ', $FilterGroup['TblGroup_Id']);
//                    }
//                }

//                $FormSerialLetterDynamic =
//                    new Form(new FormGroup(array(
//                        new FormRow(array(
//                            new FormColumn(
//                                new TextField('SerialLetter[Name]', 'Name', 'Name'), 4
//                            ),
//                            new FormColumn(
//                                new TextField('SerialLetter[Description]', 'Beschreibung', 'Beschreibung'), 8
//                            )
//                        )),
//                    )), null, new Route(__NAMESPACE__.'/Edit'),
//                        array('Id'          => $tblSerialLetter->getId(),
//                              'TabActive'   => 'PERSONGROUP',
//                              'FilterGroup' => $FilterGroup,
//                        )
//                    );
                $FormSerialLetterDynamic = $this->formFilterPersonGroup($tblSerialLetter);
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(new Edit().' Serienbrief '.new Bold($CategoryName)
                                        , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetterDynamic, $tblSerialLetter
                                            , $SerialLetter, $FilterGroup)))
                                        , Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    ).
                    ( !$IsPost
                        ? new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(
                                    new LayoutColumn(
                                        ( $Timeout === true
                                            ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                            : ''
                                        )
                                    )
                                ),
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($SearchTable, new \SPHERE\Common\Frontend\Table\Repository\Title('Ansicht der gespeicherten Filterung'),
                                            array('Salutation' => 'Anrede',
                                                  'Name'       => 'Name',
                                                  'Address'    => 'Adresse'
                                            ),
                                            array(
                                                'order'      => array(array(1, 'asc')),
                                                'columnDefs' => array(
                                                    array('orderable' => false,
                                                          'width'     => '3%', 'targets' => 0)
                                                )
                                            )
                                        )
                                    )
                                )
                            ))
                        )
                        : '' );
                break;
            case 'STUDENT':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName('Schüler');

                if ($tblFilterCategory) {
                    $CategoryName = $tblFilterCategory->getName();
                } else {
                    $CategoryName = '';
                }
                if (!$IsPost) {
                    $Result = SerialLetter::useService()->getStudentFilterResultListBySerialLetter($tblSerialLetter, null, null, null, $Timeout);
                    if ($Result) {
                        $SearchTable = $this->getStudentTableByResult($Result);
                    }
                }

                $FormSerialLetterDynamic = $this->formFilterStudent($tblSerialLetter);
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(new Edit().' Serienbrief '.new Bold($CategoryName)
                                        , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetterDynamic, $tblSerialLetter
                                            , $SerialLetter, $FilterGroup, $FilterStudent, $FilterYear)))
                                        , Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    )
                    .( !$IsPost
                        ? new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(
                                    new LayoutColumn(
                                        ( $Timeout === true
                                            ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                            : ''
                                        )
                                    )
                                ),
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($SearchTable, new \SPHERE\Common\Frontend\Table\Repository\Title('Ansicht der gespeicherten Filterung'),
                                            array('Salutation'    => 'Anrede',
                                                  'Name'          => 'Name',
                                                  'Address'       => 'Adresse',
                                                  'DivisionYear'  => 'Jahr',
                                                  'Division'      => 'Klasse',
                                                  'StudentNumber' => 'Schüler-Nr.'
                                            ),
                                            array(
                                                'order'      => array(array(1, 'asc')),
                                                'columnDefs' => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0),
                                                    array('type' => 'natural', 'targets' => 4),
                                                    array('type' => 'natural', 'targets' => 5)
                                                )
                                            )
                                        )
                                    )
                                )
                            ))
                        )
                        : '' );
                break;
            case 'PROSPECT':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName('Interessenten');

                if ($tblFilterCategory) {
                    $CategoryName = $tblFilterCategory->getName();
                } else {
                    $CategoryName = '';
                }
                if (!$IsPost) {
                    $Result = SerialLetter::useService()->getProspectFilterResultListBySerialLetter($tblSerialLetter, null, null, $Timeout);
                    if ($Result) {
                        $SearchTable = $this->getProspectTableByResult($Result);
                    }
                }

                $FormSerialLetterDynamic = $this->formFilterProspect($tblSerialLetter);
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(new Edit().' Serienbrief '.new Bold($CategoryName)
                                        , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetterDynamic, $tblSerialLetter
                                            , $SerialLetter, $FilterGroup, null, null, $FilterProspect)))
                                        , Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    )
                    .( !$IsPost
                        ? new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(
                                    new LayoutColumn(
                                        ( $Timeout === true
                                            ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                            : ''
                                        )
                                    )
                                ),
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($SearchTable, new \SPHERE\Common\Frontend\Table\Repository\Title('Ansicht der gespeicherten Filterung'),
                                            array('Salutation'          => 'Anrede',
                                                  'Name'                => 'Name',
                                                  'Address'             => 'Adresse',
                                                  'ReservationDate'     => 'Eingangsdatum',
                                                  'InterviewDate'       => 'Aufnahmegespräch',
                                                  'TrialDate'           => 'Schnuppertag',
                                                  'ReservationYear'     => 'Anmeldung für Jahr',
                                                  'ReservationDivision' => 'Anmeldung für Stufe',
                                                  'ReservationOptionA'  => 'Schulart: Option A',
                                                  'ReservationOptionB'  => 'Schulart: Option B',
                                            ),
                                            array(
                                                'order'      => array(array(1, 'asc')),
                                                'columnDefs' => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0),
                                                    array('type' => 'de_date', 'targets' => 3),
                                                    array('type' => 'de_date', 'targets' => 4),
                                                    array('type' => 'de_date', 'targets' => 5),
                                                    array('type' => 'natural', 'targets' => 7)
                                                )
                                            )
                                        )
                                    )
                                )
                            ))
                        )
                        : '' );
                break;
            case 'COMPANY':
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryByName('Firmengruppe');

                if ($tblFilterCategory) {
                    $CategoryName = $tblFilterCategory->getName();
                } else {
                    $CategoryName = '';
                }
                if (!$IsPost) {
                    $Result = SerialLetter::useService()->getCompanyFilterResultListBySerialLetter($tblSerialLetter, null, null, null, $Timeout);
                    if ($Result) {
                        $SearchTable = $this->getCompanyTableByResult($Result);
                    }
                }

                $FormSerialLetterDynamic = $this->formFilterCompany($tblSerialLetter);
                $FormSerialLetterDynamic
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $MetaTable = new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(new Edit().' Serienbrief '.new Bold($CategoryName)
                                        , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetterDynamic, $tblSerialLetter
                                            , $SerialLetter, $FilterGroup, null, null, null, $FilterCompany, $FilterRelationship)))
                                        , Panel::PANEL_TYPE_INFO)
                                )
                            )
                        )
                    )
                    .( !$IsPost
                        ? new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(
                                    new LayoutColumn(
                                        ( $Timeout === true
                                            ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                            : ''
                                        )
                                    )
                                ),
                                new LayoutRow(
                                    new LayoutColumn(
                                        new TableData($SearchTable, new \SPHERE\Common\Frontend\Table\Repository\Title('Ansicht der gespeicherten Filterung'),
                                            array('Salutation'          => 'Anrede',
                                                  'Name'                => 'Name',
                                                  'Address'             => 'Adresse',
                                                  'CompanyName'         => 'Firmenname',
                                                  'CompanyExtendedName' => 'Zusatz',
                                                  'Type'                => 'Typ'
                                            ),
                                            array(
                                                'order'      => array(array(1, 'asc')),
                                                'columnDefs' => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                                )
                                            )
                                        )
                                    )
                                )
                            ))
                        )
                        : '' );
                break;
            default:
                $MetaTable = new Panel(new Edit().' Serienbrief'
                    , array(new Well(SerialLetter::useService()->updateSerialLetter($FormSerialLetter, $tblSerialLetter
                        , $SerialLetter))), Panel::PANEL_TYPE_INFO);
        }

        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        $MetaTable
                    )
                ),
            )))
        );

        return $Stage;
    }

    /**
     * @param null|int   $Id
     * @param null|array $FilterGroup
     * @param null|array $FilterStudent
     * @param null|array $FilterPerson
     * @param null|array $FilterYear
     * @param null|array $FilterType
     * @param null|array $FilterProspect
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
        $FilterProspect = null,
        $TabActive = 'PERSON'
    ) {

        $Stage = new Stage('Personen für Serienbriefe', 'Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = ( $Id == null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $tblFilterCategory = $tblSerialLetter->getFilterCategory();

        $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblFilterCategory) {
            $Stage->addButton(new Standard(new Bold(new Info('Personen Auswahl')),
                '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
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
            && $FilterProspect === null
        ) {
            // set Group Student and Execute Search
            $Global = $this->getGlobal();
            if ($TabActive == 'PROSPECT') {
                $ProspectGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
                if ($ProspectGroup && $FilterProspect === null && !isset($FilterGroup['TblGroup_Id'])) {
                    $Global->POST['FilterGroup']['TblGroup_Id'] = $ProspectGroup->getId();
                    $Global->savePost();
                    $FilterGroup = null;
                }
            }
            // set Year
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $Global->POST['FilterYear']['TblYear_Id'] = $tblYear->getId();
                }
            }
            $Global->savePost();

        };

        // Database Join with foreign Key
        if ($FilterGroup && isset($FilterGroup['TblGroup_Id']) && $FilterGroup['TblGroup_Id'] !== '0'
            || $FilterPerson && ( isset($FilterPerson['TblPerson_FirstName']) && !empty($FilterPerson['TblPerson_FirstName'])
                || $FilterPerson['TblPerson_LastName'] && !empty($FilterPerson['TblPerson_LastName']) )
            && $TabActive === 'PERSON'
        ) {

            $Filter = $FilterGroup;

            $Pile = new Pile(Pile::JOIN_TYPE_INNER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(), null, 'TblMember_serviceTblPerson');
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(), ViewPerson::TBL_PERSON_ID, null);
            // Group->Person
        }
        // Database Join with foreign Key
        if ($FilterStudent && $TabActive === 'DIVISION') {

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

        if ($FilterProspect && $TabActive === 'PROSPECT') {
            $Filter = $FilterProspect;

            // Database Join with foreign Key
            $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
            );
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
            );
            $Pile->addPile(( new ViewPeopleMetaProspect() )->getViewService(), new ViewPeopleMetaProspect(),
                ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON, ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON
            );
        }

        $Result = array();
        $Timeout = null;
        if ($Filter && isset($Pile)) {
            // Preparation Filter
            array_walk($Filter, function (&$Input) {

                if (!empty($Input)) {
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

                    if (!empty($Input)) {
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

                    if (!empty($Input)) {
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

                    if (!empty($Input)) {
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
            // Preparation $FilterProspect
            if ($FilterProspect) {
                array_walk($FilterProspect, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterProspect = array_filter($FilterProspect);
            } else {
                $FilterProspect = array();
            }
            // Filter ordered by Database Join with foreign Key
            if ($FilterGroup && $TabActive === 'PERSON') {
                $Result = $Pile->searchPile(array(
                    0 => $Filter,
                    1 => $FilterPerson
                ));
            }
            // Filter ordered by Database Join with foreign Key
            if ($FilterStudent && $TabActive === 'DIVISION') {
                $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');

                $Result = $Pile->searchPile(array(
                    0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                    1 => $FilterPerson,
                    2 => $Filter,
                    3 => $FilterYear
//                    4 => $FilterType
                ));
            }

            // Filter ordered by Database Join with foreign Key
            if (( $FilterGroup || $FilterProspect ) && $TabActive === 'PROSPECT') {
                $ProspectGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
                if ($ProspectGroup) {
                    $Result = $Pile->searchPile(array(
                        0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($ProspectGroup->getId())),
                        1 => $FilterPerson,
                        2 => $FilterProspect,
                    ));
                }
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
                if ($TabActive === 'PROSPECT') {
                    /** @var TblPerson $tblPerson */
                    $Person = $Row[1]->__toArray();
                    if (isset($Person['Id'])) {
                        $tblPerson = Person::useService()->getPersonById($Person['Id']);
                        if ($tblPerson) {
                            $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                            if ($tblProspect) {
                                $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                if ($tblProspectReservation) {
                                    $DataPerson['ProspectYear'] = $tblProspectReservation->getReservationYear();
                                    $DataPerson['ProspectDivision'] = $tblProspectReservation->getReservationDivision();
                                }
                            }
                        }
                    }
                }

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
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
            if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = $tblAddress->getGuiString();
            }
            $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
            if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
            }

            if (!isset($DataPerson['ProspectYear'])) {
                $DataPerson['ProspectYear'] = new Small(new Muted('-NA-'));
            }
            if (!isset($DataPerson['ProspectDivision'])) {
                $DataPerson['ProspectDivision'] = new Small(new Muted('-NA-'));
            }

            // ignore duplicated Person
            if ($DataPerson['Name']) {
                if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                    $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                }
            }
        }

        $tblPersonSearch = $SearchResult;
        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);

        if (!empty($tblPersonList) && !empty($tblPersonSearch)) {

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
                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);

                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->ProspectYear = new Small(new Muted('-NA-'));
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->ProspectDivision = new Small(new Muted('-NA-'));

                // only Prospect where no Student exist
                if ($tblProspect && !$tblStudent) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        /** @noinspection PhpUndefinedFieldInspection */
                        $tblPerson->ProspectYear = $tblProspectReservation->getReservationYear();
                        /** @noinspection PhpUndefinedFieldInspection */
                        $tblPerson->ProspectDivision = $tblProspectReservation->getReservationDivision();
                    }
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

                        if (!empty($VisitedDivisionList)) {
                            rsort($VisitedDivisionList);
                            $VisitedDivision = current($VisitedDivisionList);
                        }
                    }
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Division = $VisitedDivision;
            });
        }

        $FormGroup = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(array(
                        new SelectBox('FilterGroup['.ViewPeopleGroupMember::TBL_GROUP_ID.']', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll())),
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
                        new SelectBox('FilterYear[TblYear_Id]', 'Bildung: Schuljahr',
                            array('{{ Name }} {{ Description }}' => Term::useService()->getYearAll())),
                    ), 4),
                    new FormColumn(array(
                        new SelectBox('FilterStudent['.ViewDivisionStudent::TBL_LEVEL_ID.']', 'Klasse: Stufe',
                            array('{{ Name }} {{ serviceTblType.Name }}' => Division::useService()->getLevelAll()))
                    ), 4),
                    new FormColumn(array(
                        new AutoCompleter('FilterStudent['.ViewDivisionStudent::TBL_DIVISION_NAME.']', 'Klasse: Gruppe', 'Klasse: Gruppe',
                            array('Name' => Division::useService()->getDivisionAll()))
                    ), 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Person: Vorname', 'Person: Vorname')
                    ), 4),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Person: Nachname', 'Person: Nachname')
                    ), 4)
                ))
            ))
            , new Primary('in Klassen suchen'));

        $ProspectGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
        $ProspectReservationList = Prospect::useService()->getProspectReservationAll();

        $FormProspect = new Form(
            new FormGroup(array(
                new FormRow(array(
//                    new FormColumn(
//                        ( new RadioBox('FilterGroup[TblGroup_Id]', 'Gruppe: '.$ProspectGroup->getName(),
//                            $ProspectGroup->getId()) )->setDefaultValue($ProspectGroup->getId(), true)
//                        , 4),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationYear]', 'Interessent: Schuljahr', 'Interessent: Schuljahr',
                            array('ReservationYear' => $ProspectReservationList))
                        , 4),
                    new FormColumn(
                        new AutoCompleter('FilterProspect[TblProspectReservation_ReservationDivision]', 'Interessent: Stufe', 'Interessent: Stufe',
                            array('ReservationDivision' => $ProspectReservationList))
                        , 4),
                ))
            ))
            , new Primary('Interessenten suchen'), $this->getRequest()->getPathInfo(), array(
            'TabActive'   => 'PROSPECT',
            'Id'          => $tblSerialLetter->getId(),
            'FilterGroup' => array('TblGroup_Id' => $ProspectGroup->getId())
        ));

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

        // set Success by filtered Input field in FormProspect
        if ($FilterGroup) {
            foreach ($FilterGroup as $Field => $Value) {
                if ($Value) {
                    $FormProspect->setSuccess('FilterGroup['.$Field.']', '', new Filter());
                }
            }
            if ($FilterProspect) {
                foreach ($FilterProspect as $Field => $Value) {
                    if ($Value) {
                        $FormProspect->setSuccess('FilterProspect['.$Field.']', '', new Filter());
                    }
                }
            }
        }

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );


        // create Tabs
        $LayoutTabs[] = new LayoutTab('Personen in Gruppen suchen', 'PERSON', array('Id' => $tblSerialLetter->getId()));
        $LayoutTabs[] = new LayoutTab('Schüler in Klassen suchen', 'DIVISION', array('Id' => $tblSerialLetter->getId()));
        $LayoutTabs[] = new LayoutTab('Interessenten suchen', 'PROSPECT', array('Id' => $tblSerialLetter->getId()));
        if (!empty($LayoutTabs) && $TabActive === 'PERSON') {
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
            case 'PROSPECT':
                $MetaTable = new Panel(new Search().' Interresenten-Suche nach '.new Bold('Jahr / Stufe')
                    , array($FormProspect), Panel::PANEL_TYPE_INFO);
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
                                            new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
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
                                            ( !$FilterStudent && !$FilterGroup && !$FilterProspect
                                                ? new WarningMessage('Benutzen Sie bitte den Filter')
                                                : ( empty($tblPersonSearch)
                                                    ? new WarningMessage('Keine Ergebnisse bei aktueller Filterung '.new SuccessText(new Filter()))
                                                    : ''
                                                )
                                            )
                                        ,

                                        ), 12)
                                    ))
                                ))
                            )
                        ), 6)
                    ))
                ))
            )
            .( $TabActive === 'PROSPECT'
                ?
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($tblPersonList, null,
                                    array('Exchange'         => '',
                                          'Name'             => 'Name',
                                          'Address'          => 'Adresse',
//                                          'Division'         => 'Klasse',
//                                          'StudentNumber'    => 'Schüler-Nr.',
                                          'ProspectYear'     => 'Intr.-Jahr',
                                          'ProspectDivision' => 'Intr.-Stufe'
                                    ),
                                    array(
                                        'order'                => array(array(1, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0),
                                            array('type' => 'natural', 'targets' => 4)
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
                                    array('Exchange'         => ' ',
                                          'Name'             => 'Name',
                                          'Address'          => 'Adresse',
//                                          'Division'         => 'Klasse',
//                                          'StudentNumber'    => 'Schüler-Nr.',
                                          'ProspectYear'     => 'Intr.-Jahr',
                                          'ProspectDivision' => 'Intr.-Stufe'
                                    ),
                                    array(
                                        'order'                => array(array(1, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0),
                                            array('type' => 'natural', 'targets' => 4)
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
                                //                                )
                                ),
                                ( !$FilterStudent && !$FilterGroup
                                    ? ''
                                    : new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll')
                                )
                            ), 6)
                        ))
                    )
                )
                : new Layout(
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
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0),
                                            array('type' => 'natural', 'targets' => 3)
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
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0),
                                            array('type' => 'natural', 'targets' => 3)
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
//                                )
                                ),
                                ( !$FilterStudent && !$FilterGroup
                                    ? ''
                                    : new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll')
                                )
                            ), 6)
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null|int $Id
     * @param bool     $Control
     *
     * @return Stage|string
     */
    public function frontendPersonAddress(
        $Id = null,
        $Control = false
    ) {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }

        $tblFilterCategory = $tblSerialLetter->getFilterCategory();

        $isCompany = false;
        if ($tblSerialLetter->getFilterCategory()
            && $tblSerialLetter->getFilterCategory()->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP
        ) {
            $isCompany = true;
        }

        // update SerialPerson
        if ($tblFilterCategory) {
            if ($Control) {
                SerialLetter::useService()->updateSerialPerson($tblSerialLetter, $tblFilterCategory);
            }
        }

        $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblFilterCategory) {
            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
        $Stage->addButton(new Standard(new Bold(new Info('Adressen Auswahl')), '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Addressliste für Serienbriefe anzeigen und herunterladen'));

        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
        if (!$tblPersonList) {
            return $Stage.new Danger('Es sind keine Personen dem Serienbrief zugeordnet', new Exclamation());
        }

        $TableContent = array();
        array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $Id, $tblSerialLetter, $tblFilterCategory) {
            $Item['Name'] = $tblPerson->getLastFirstName();
            $Item['StudentNumber'] = new Small(new Muted('-NA-'));
            $Item['CompanyRelationship'] = '';
            $Item['Address'] = array();
            $Item['Option'] = new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                array('Id'       => $Id,
                      'PersonId' => $tblPerson->getId(),
                      'Route'    => '/Reporting/SerialLetter/Address'));
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                $Item['StudentNumber'] = $tblStudent->getIdentifier();
            }
            if ($tblFilterCategory && $tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                $tblRelationshipCompanyList = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson);
                if ($tblRelationshipCompanyList) {
                    foreach ($tblRelationshipCompanyList as $tblRelationshipCompany) {
                        if ($tblRelationshipCompany->getServiceTblCompany()) {
                            if ($Item['CompanyRelationship'] === '') {
                                $Item['CompanyRelationship'] = $tblRelationshipCompany->getTblType()->getName();
                            } else {
                                $Item['CompanyRelationship'] .= ', '.$tblRelationshipCompany->getTblType()->getName();
                            }
                        }
                    }
                }
            }

            $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
            if ($tblAddressPersonList) {
                $Data = array();
                $WarningList = array();
                if ($tblFilterCategory && $tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                    /** @var TblAddressPerson $tblAddressPerson */
                    foreach ($tblAddressPersonList as $tblAddressPerson) {
                        $tblToCompany = $tblAddressPerson->getServiceTblToPerson($tblSerialLetter->getFilterCategory());
                        /** @var TblToCompany $tblToCompany */
                        if ($tblToCompany) {
                            $tblAddress = $tblToCompany->getTblAddress();

                            if (!isset($Data[$tblAddress->getId()]['Person'])) {
                                $Data[$tblAddress->getId()]['Person'] =
                                    $tblPerson->getLastName().' '.$tblPerson->getFirstName();
                                if ($tblPerson->getSalutation() === '') {
                                    $WarningList[] = $tblPerson->getLastName().' '.
                                        $tblPerson->getFirstName();
                                }
                            } else {
                                $Data[$tblAddress->getId()]['Person'] =
                                    $Data[$tblAddress->getId()]['Person'].', '.
                                    $tblPerson->getLastName().' '.$tblPerson->getFirstName();
                                if ($tblPerson->getSalutation() === '') {
                                    $WarningList[] = $tblPerson->getLastName().' '.
                                        $tblPerson->getFirstName();
                                }
                            }
                            if (!isset($Data[$tblAddress->getId()]['District'])) {
                                if (( $tblCity = $tblAddress->getTblCity() )) {
                                    $Data[$tblAddress->getId()]['District'] = $tblAddress->getTblCity()->getDistrict();
                                }
                            }
                            if (!isset($Data[$tblAddress->getId()]['Street'])) {
                                $Data[$tblAddress->getId()]['Street'] =
                                    $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                            }
                            if (!isset($Data[$tblAddress->getId()]['City'])) {
                                if (( $tblCity = $tblAddress->getTblCity() )) {
                                    $Data[$tblAddress->getId()]['City'] = $tblCity->getCode().' '.$tblCity->getName();
                                }
                            }
                        }
                    }
                } else {
                    /** @var TblAddressPerson $tblAddressPerson */
                    foreach ($tblAddressPersonList as $tblAddressPerson) {
                        if (( $serviceTblPersonToAddress = $tblAddressPerson->getServiceTblToPerson() )) {
                            if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                                if (( $PersonToAddress = $tblToPerson->getServiceTblPerson() )) {
                                    if (( $tblAddress = $serviceTblPersonToAddress->getTblAddress() )) {
                                        if (!isset($Data[$tblAddress->getId()]['Person'])) {
                                            $Data[$tblAddress->getId()]['Person'] =
                                                $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                            if ($PersonToAddress->getSalutation() === '') {
                                                $WarningList[] = $PersonToAddress->getLastName().' '.
                                                    $PersonToAddress->getFirstName();
                                            }
                                        } else {
                                            $Data[$tblAddress->getId()]['Person'] =
                                                $Data[$tblAddress->getId()]['Person'].', '.
                                                $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                            if ($PersonToAddress->getSalutation() === '') {
                                                $WarningList[] = $PersonToAddress->getLastName().' '.
                                                    $PersonToAddress->getFirstName();
                                            }
                                        }
                                        if (!isset($Data[$tblAddress->getId()]['District'])) {
                                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                                $Data[$tblAddress->getId()]['District'] = $tblAddress->getTblCity()->getDistrict();
                                            }
                                        }
                                        if (!isset($Data[$tblAddress->getId()]['Street'])) {
                                            $Data[$tblAddress->getId()]['Street'] =
                                                $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                                        }
                                        if (!isset($Data[$tblAddress->getId()]['City'])) {
                                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                                $Data[$tblAddress->getId()]['City'] = $tblCity->getCode().' '.$tblCity->getName();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($WarningList)) {
                    $WarningList = array_unique($WarningList);
                    foreach ($WarningList as $Warning) {
                        $Item['Address'][] = new LayoutColumn(
                            new WarningMessage(new Exclamation().' Fehlende Anrede ('.$Warning.')')
                            , 4);
                    }
                }

                $AddressList = array();
                if (!empty($Data)) {
                    foreach ($Data as $AddressPanel) {
                        $AddressList[] = new LayoutColumn(
                            new Panel('', $AddressPanel)
                            , 4
                        );
                    }
                    $Item['Address'][] = new LayoutColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(
                                    $AddressList
                                )
                            )
                        )
                    );
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

            if (empty($Item['Address'])) {
                $Item['Address'] = new WarningMessage('Keine Adressen ausgewählt!');
            }

            array_push($TableContent, $Item);
        });

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter, $isCompany);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

        $Buttons = array();
//        $Buttons[] = new Standard('Löschen', '/Reporting/SerialLetter/Address/Remove', new Remove(), array('Id' => $tblSerialLetter->getId()));
        // Company use other automatic
        if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
            $Buttons[] = new Standard('Firmen mit Personen anschreiben', '/Reporting/SerialLetter/Address/Company', new Edit()
                , array('Id' => $tblSerialLetter->getId()));
        } elseif ($tblFilterCategory || !$tblFilterCategory) {
            $Buttons[] = new Standard('Personen direkt anschreiben', '/Reporting/SerialLetter/Address/Person', new Edit()
                , array('Id' => $tblSerialLetter->getId()));
            $Buttons[] = new Standard('Sorgeberechtigte anschreiben', '/Reporting/SerialLetter/Address/Guardian', new Edit()
                , array('Id' => $tblSerialLetter->getId()));
        }

        $TableShow =
            new TableData($TableContent, null, array(
                    'Name'          => 'Name',
                    'StudentNumber' => 'Schüler-Nr.',
                    'Address'       => 'Serienbrief Adresse',
                    'Option'        => '')
                , array(
                    'order'      => array(array(2, 'asc')
                    , array(0, 'asc')
                    ),
                    'columnDefs' => array(
                        array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        array('width' => '15%', 'targets' => 0),
                        array('width' => '10%', 'targets' => 1)
                    )
                ));
        if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
            $TableShow =
                new TableData($TableContent, null, array(
                        'Name'                => 'Name',
                        'CompanyRelationship' => 'Beziehungstyp',
                        'Address'             => 'Serienbrief Adresse',
                        'Option'              => '')
                    , array(
                        'order'      => array(array(2, 'asc')
                        , array(0, 'asc')
                        ),
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                            array('width' => '15%', 'targets' => 0),
                            array('width' => '10%', 'targets' => 1)
                        )
                    ));
        } elseif ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
            $TableShow =
                new TableData($TableContent, null, array(
                        'Name'    => 'Name',
                        'Address' => 'Serienbrief Adresse',
                        'Option'  => '')
                    , array(
                        'order'      => array(array(1, 'asc')
                        , array(0, 'asc')
                        ),
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                            array('width' => '15%', 'targets' => 0),
                        )
                    ));
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new Title(new Setup().' Adressen', 'Zuweisung'),
                                            new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter)
                                        ), 6),
                                        new LayoutColumn(array(
                                                new Title('Adressauswahl', 'Automatik'),
                                                new Panel('Adressen von untenstehenden Personen', $Buttons
                                                    , Panel::PANEL_TYPE_INFO))
                                            , 6)
                                    ))
                                )
                            )
                        ), 12),
                        new LayoutColumn(
                            $TableShow
                        )
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
    public function frontendSetAddressByPerson($Id = null)
    {

        $Stage = new Stage('Befüllen der Adressen', '');
        $tblSerialLetter = ( !$Id ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage->setContent(new WarningMessage('Kein Serienbrief ausgewählt'))
                .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id)));

        $Stage->setContent(
            SerialLetter::useService()->createAddressPersonSelf($tblSerialLetter)
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendSetAddressByPersonGuardian($Id = null)
    {

        $Stage = new Stage('Befüllen der Adressen', 'aus Sorgeberechtigten');
        $tblSerialLetter = ( !$Id ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage->setContent(new WarningMessage('Kein Serienbrief ausgewählt'))
                .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id)));

        $Stage->setContent(
            SerialLetter::useService()->createAddressPersonGuardian($tblSerialLetter)
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendSetAddressByCompany($Id = null)
    {

        $Stage = new Stage('Befüllen der Adressen', 'aus Firmen');
        $tblSerialLetter = ( !$Id ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage->setContent(new WarningMessage('Kein Serienbrief ausgewählt'))
                .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id)));

        $Stage->setContent(
            SerialLetter::useService()->createAddressPersonCompany($tblSerialLetter)
        );

        return $Stage;
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendAddressRemove($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Löschen');
        if ($Id) {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id))
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
                    $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
                    if ($tblPersonList) {
                        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
                        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
                            'Anzahl Anschreiben: '.$SerialLetterCount,);
                        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                                .' Person(en)', Label::LABEL_TYPE_INFO)
                        );
                    } else {
                        $PanelContent = 'Keine Personen im Warenkorb';
                        $PanelFooter = '';
                    }

                    $Stage->setContent(
                        new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                                ), 6),
                                new LayoutColumn(
                                    new Panel(new Question().' Alle Zuweisungen für Adressen des Serienbriefs wirklich löschen?', array(
                                        $tblSerialLetter->getName().' '.$tblSerialLetter->getDescription()
                                    ),
                                        Panel::PANEL_TYPE_DANGER,
                                        new Standard(
                                            'Ja', '/Reporting/SerialLetter/Address/Remove', new Ok(),
                                            array('Id' => $Id, 'Confirm' => true)
                                        )
                                        .new Standard(
                                            'Nein', '/Reporting/SerialLetter/Address', new Disable(),
                                            array('Id' => $Id)
                                        )
                                    )
                                )
                            ))

                        ))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( SerialLetter::useService()->destroyAddressPersonAllBySerialLetter($tblSerialLetter)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Zuweisungen für Adressen des Serienbriefs wurde gelöscht')
                                    : new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()))
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
        $tblFilterCategory = $tblSerialLetter->getFilterCategory();

        $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
            array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
        if (!$tblFilterCategory) {
            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        }
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
                $FilterCategory = ( $tblFilterCategory ? $tblFilterCategory : null );
                foreach ($tblAddressAllByPerson as $tblAddressPerson) {
                    if ($tblAddressPerson->getServiceTblPerson()
                        && $tblAddressPerson->getServiceTblPersonToAddress()
                        && $tblAddressPerson->getServiceTblToPerson($FilterCategory)
                    ) {
                        $Global->POST['Check']
                        [$tblAddressPerson->getServiceTblPerson()->getId()]
                        [$tblAddressPerson->getServiceTblToPerson($FilterCategory)->getId()]
                        ['Address'] = 1;

//                        $Global->POST['Check']
//                        [$tblAddressPerson->getServiceTblPerson()->getId()]
//                        [$tblAddressPerson->getServiceTblToPerson()->getId()]
//                        ['Salutation'] = $tblAddressPerson->getServiceTblSalutation() ? $tblAddressPerson->getServiceTblSalutation()->getId() : 0;
                    }
                }
            }
        }
        $Global->savePost();

        $dataList = array();
        $columnList = array(
            'Salutation'   => 'Anrede',
            'Person'       => 'Person',
            'Relationship' => 'Beziehung',
            'AddressType'  => 'Adresstyp',
            'Address'      => 'Adressen'
        );
        if ($tblFilterCategory && $tblFilterCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
            $columnList = array(
                'Salutation'   => 'Anrede',
                'Person'       => 'Person',
                'Relationship' => 'Beziehung',
                'Company'      => 'Firma',
                'AddressType'  => 'Adresstyp',
                'Address'      => 'Adressen'
            );
        }

        $personCount = 0;

        if ($tblSerialLetter->getFilterCategory()
            && TblFilterCategory::IDENTIFIER_COMPANY_GROUP == $tblSerialLetter->getFilterCategory()->getName()
        ) {
            $tblAddressToPersonList = array();
            $tblCompanyList = array();
            $tblCompanyRelationshipList = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson);
            if ($tblCompanyRelationshipList) {
                /** @var TblToCompany $tblCompanyRelationship */
                foreach ($tblCompanyRelationshipList as $tblCompanyRelationship) {
                    if ($tblCompanyRelationship->getServiceTblCompany()) {
                        $tblCompanyList[] = $tblCompanyRelationship->getServiceTblCompany();
                    }
                }
            }
            if (!empty($tblCompanyList)) {
                /** @var TblCompany $tblCompany */
                foreach ($tblCompanyList as $tblCompany) {
                    if ($tblCompany->fetchMainAddress()) {
                        $tblAddressToPersonList = array_merge($tblAddressToPersonList, Address::useService()->getAddressAllByCompany($tblCompany));
                    }
                }
            }
        } else {
            $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
        }
        if ($tblAddressToPersonList) {
            foreach ($tblAddressToPersonList as $tblToPerson) {
                if (!$tblToPerson instanceof TblToCompany) {
                    $dataList[$tblPerson->getId()]['Number'] = ++$personCount;
                    $dataList[$tblPerson->getId()]['Person'] = $tblPerson->getLastFirstName();
                    $subDataList[] = array(
                        'Salutation'   => $tblPerson->getSalutation(),
                        'Person'       => $tblToPerson->getServiceTblPerson() ? new Bold($tblToPerson->getServiceTblPerson()->getFullName()) : '',
                        'Relationship' => '',
                        'AddressType'  => $tblToPerson->getTblType()->getName(),
                        'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson->getId().'][Address]',
                            '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1),
                    );
                }
            }
        }

        if ($tblSerialLetter->getFilterCategory()
            && TblFilterCategory::IDENTIFIER_COMPANY_GROUP == $tblSerialLetter->getFilterCategory()->getName()
        ) {
            $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson);
        } else {
            $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        }
        $PersonToPersonId = array();
        if ($tblRelationshipAll) {
            /** @var TblToPerson|TblToCompany $tblRelationship */
            foreach ($tblRelationshipAll as $tblRelationship) {
                $tblGroup = Relationship::useService()->getGroupByIdentifier('COMPANY');
                if ($tblGroup->getId() == $tblRelationship->getTblType()->getTblGroup()->getId()) {
                    $tblType = $tblRelationship->getTblType();
                    if ($tblType) {
                        if ($tblAddressToPersonList) {
                            /** @var TblToCompany $tblToCompany */
                            foreach ($tblAddressToPersonList as $tblToCompany) {
                                if ($tblToCompany->getTblAddress()) {
                                    if ($tblToCompany->getServiceTblCompany() && $tblRelationship->getServiceTblCompany()) {
                                        if ($tblToCompany->getServiceTblCompany()->getId() === $tblRelationship->getServiceTblCompany()->getId()) {
                                            $RelationShip = $tblType->getName();
                                            $subDataList[] = array(
                                                'Salutation'   => $tblPerson->getSalutation(),
                                                'Person'       => $tblPerson->getFullName(),
                                                'Relationship' => $RelationShip,
                                                'Company'      => ( $tblToCompany->getServiceTblCompany()
                                                    ? $tblToCompany->getServiceTblCompany()->getName().' '
                                                    .$tblToCompany->getServiceTblCompany()->getExtendedName()
                                                    : '' ),
                                                'AddressType'  => $tblToCompany->getTblType()->getName(),
                                                'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToCompany->getId().'][Address]',
                                                    '&nbsp; '.$tblToCompany->getTblAddress()->getGuiString(), 1)
                                            );
                                        }
                                    }
                                } else {
                                    if ($tblToCompany->getServiceTblCompany() && $tblRelationship->getServiceTblCompany()) {
                                        if ($tblToCompany->getServiceTblCompany()->getId() === $tblRelationship->getServiceTblCompany()->getId()) {
                                            $RelationShip = $tblType->getName();
                                            /** @var TblToPerson $tblRelationship */
                                            $subDataList[] = array(
                                                'Salutation'   => $tblPerson->getSalutation(),
                                                'Person'       => $tblPerson->getFullName(),
                                                'Relationship' => $RelationShip,
                                                'Company'      => ( $tblToCompany->getServiceTblCompany()
                                                    ? $tblToCompany->getServiceTblCompany()->getName().' '
                                                    .$tblToCompany->getServiceTblCompany()->getExtendedName()
                                                    : '' ),
                                                'AddressType'  => $tblToCompany->getTblType()->getName(),
                                                'Address'      => new Warning(
                                                    new Exclamation().' Keine Adresse hinterlegt')
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
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
                                            'Salutation'   => ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getSalutation() : '' ),
                                            'Person'       => ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getFullName() : '' ),
                                            'Relationship' => $direction,
                                            'AddressType'  => $tblToPerson->getTblType()->getName(),
                                            'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Address]',
                                                '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1)
                                        );
                                        $PersonToPersonId[$PersonIdAddressIdNow] = $PersonIdAddressIdNow;
                                    }
                                }
                            } else {
                                /** @var TblToPerson $tblRelationship */
                                if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                                    $subDataList[] = array(
                                        'Salutation'   => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getSalutation() : '' ),
                                        'Person'       => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getFullName() : '' ),
                                        'Relationship' => $direction,
                                        'AddressType'  => '',
                                        'Address'      => new Warning(
                                            new Exclamation().' Keine Adresse hinterlegt')
                                    );
                                } else {
                                    $subDataList[] = array(
                                        'Salutation'   => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getSalutation() : '' ),
                                        'Person'       => ( $tblRelationship->getServiceTblPersonTo() ? $tblRelationship->getServiceTblPersonTo()->getFullName() : '' ),
                                        'Relationship' => $direction,
                                        'AddressType'  => '',
                                        'Address'      => new Warning(
                                            new Exclamation().' Keine Adresse hinterlegt')
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }


        if (isset($subDataList)) {
            $Form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(array(
                            new TableData($subDataList, null, $columnList,
                                array(
                                    'order' => array(array(2, 'asc'), array(1, 'asc')),
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

        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

        $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
        $PanelPerson = new Panel(
            new Bold($tblPerson->getFullName()),
            'Verwendete Adresse(n): '.( $tblAddressPersonList === false ? 0 : count($tblAddressPersonList) ),
            Panel::PANEL_TYPE_SUCCESS);

        if (isset($Form)) {
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
                                                new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter
                                                )
                                                , 6),
                                            new LayoutColumn(
                                                $PanelPerson
                                                , 6)
                                        ))
                                    )
                                ),
                                new Well(SerialLetter::useService()->setPersonAddressSelection(
                                    $Form, $tblSerialLetter, $tblPerson, $Check, $Route
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
                                new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter
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
     * @param bool $Control
     *
     * @return Stage|string
     */
    public function frontendSerialLetterExport(
        $Id = null,
        $Control = false
    ) {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen herunterladen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        if (( $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id) )) {
            $tblFilterCategory = $tblSerialLetter->getFilterCategory();
            // update SerialPerson
            if ($tblFilterCategory) {
                if ($Control) {
                    SerialLetter::useService()->updateSerialPerson($tblSerialLetter, $tblFilterCategory);
                }
            }

            $Stage->addButton(new Standard('Bearbeiten', '/Reporting/SerialLetter/Edit', new Edit(),
                array('Id' => $tblSerialLetter->getId()), 'Aktuelle Filterung anzeigen'));
            if (!$tblFilterCategory) {
                $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                    array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
            }
            $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
                array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
            $Stage->addButton(new Standard(new Bold(new Info('Addressliste')), '/Reporting/SerialLetter/Export', new View(),
                array('Id' => $tblSerialLetter->getId()),
                'Addressliste für Serienbriefe anzeigen und herunterladen'));

            $dataList = array();
            $columnList = array(
                'Number'          => 'Nr.',
                'Person'          => 'Person',
                'StudentNumber'   => 'Schüler-Nr.',
                'Division'        => 'Aktuelle Klasse(n)',
                'Salutation'      => 'Anrede',
                'PersonToAddress' => 'Adressat',
                'Address'         => 'Adresse',
                'Option'          => ''
            );
            $Interactive = array(
                'order'      => array(array(0, 'asc')),
                'columnDefs' => array(
                    array(
//                        'orderable' => false,
                        'width' => '3%', 'targets' => 0),
                    array('type' => 'natural', 'targets' => 2),
                    array('type' => 'natural', 'targets' => 4)
                )
            );
            if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                $columnList = array(
                    'Number'              => 'Nr.',
                    'Person'              => 'Person',
                    'ReservationDivision' => 'Stufe',
                    'ReservationYear'     => 'Jahr',
                    'ReservationOptionA'  => 'Schulart 1',
                    'ReservationOptionB'  => 'Schulart 2',
                    'Salutation'          => 'Anrede',
                    'PersonToAddress'     => 'Adressat',
                    'Address'             => 'Adresse',
                    'Option'              => ''
                );
                $Interactive = array(
                    'order'      => array(array(0, 'asc')),
                    'columnDefs' => array(
                        array(
//                            'orderable' => false,
                            'width' => '3%', 'targets' => 0),
                    )
                );
            }
            if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                $columnList = array(
                    'Number'     => 'Nr.',
                    'Person'     => 'Person',
                    'Salutation' => 'Anrede',
//                    'PersonToAddress' => 'Adressat',
                    'Company'    => 'Firma',
                    'Address'    => 'Adresse',
                    'Option'     => ''
                );
                $Interactive = array(
                    'order'      => array(array(0, 'asc')),
                    'columnDefs' => array(
                        array(
//                            'orderable' => false,
                            'width' => '3%', 'targets' => 0),
                    )
                );
            }

            $countAddresses = 0;
            $count = 0;
            $tblPersonList = false;
            $tbSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
            if ($tbSerialPersonList) {
                /** @var TblSerialPerson $tbSerialPerson */
                foreach ($tbSerialPersonList as $tbSerialPerson) {
                    if ($tbSerialPerson->getServiceTblPerson()) {
                        $tblPersonList[] = $tbSerialPerson->getServiceTblPerson();
                    }
                }
            }
            $isCompany = false;
            if ($tblSerialLetter->getFilterCategory()
                && $tblSerialLetter->getFilterCategory()->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP
            ) {
                $isCompany = true;
            }

            if ($tblPersonList) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                /** @var TblPerson $tblPerson */
                foreach ($tblPersonList as $tblPerson) {
                    if (!$tblFilterCategory) {
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
                    }

                    // get fresh list
                    $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                        $tblPerson, 'M');   // ToDO choose FirstGender
                    if ($tblAddressPersonAllByPerson) {
                        /** @var TblAddressPerson $tblAddressPerson */
                        $AddressList = array();
                        $tblFilterCategory = $tblSerialLetter->getFilterCategory();
                        array_walk($tblAddressPersonAllByPerson, function (TblAddressPerson $tblAddressPerson) use (&$AddressList, $tblPerson, $Id, $tblSerialLetter, $tblFilterCategory) {
                            if ($tblFilterCategory && TblFilterCategory::IDENTIFIER_COMPANY_GROUP == $tblFilterCategory->getName()) {
                                if (( $serviceTblCompanyToAddress = $tblAddressPerson->getServiceTblToPerson($tblFilterCategory) )) {
//                                        $tblAddress = $serviceTblCompanyToAddress->getTblAddress();
                                    $AddressList[$tblAddressPerson->getId()]['Person'] =
                                        $tblPerson->getLastFirstName();
                                    $AddressList[$tblAddressPerson->getId()]['PersonToAddress'] =
                                        $tblPerson->getLastFirstName();
                                    if (( $tblToCompany = $tblAddressPerson->getServiceTblToPerson($tblFilterCategory) )) {
                                        $AddressList[$tblAddressPerson->getId()]['Address'] = $tblToCompany->getTblAddress()->getGuiString();
                                        $AddressList[$tblAddressPerson->getId()]['Company'] =
                                            ( $tblToCompany->getServiceTblCompany()->getExtendedName() !== ''
                                                ? $tblToCompany->getServiceTblCompany()->getName().'<br/>'.
                                                $tblToCompany->getServiceTblCompany()->getExtendedName()
                                                : $tblToCompany->getServiceTblCompany()->getName() );
                                    }

                                    $AddressList[$tblAddressPerson->getId()]['Salutation'] =
                                        $tblPerson->getSalutation() !== ''
                                            ? $tblPerson->getSalutation()
                                            : new Warning(new Exclamation().' Keine Anrede hinterlegt.');
                                    $AddressList[$tblAddressPerson->getId()]['Option'] =
                                        new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                            array('Id'       => $Id,
                                                  'PersonId' => $tblPerson->getId(),
                                                  'Route'    => '/Reporting/SerialLetter/Export'));
                                }
                            } else {
                                if (( $serviceTblPersonToAddress = $tblAddressPerson->getServiceTblToPerson() )) {
                                    if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                                        if (( $PersonToAddress = $tblToPerson->getServiceTblPerson() )) {
                                            if (( $tblAddress = $serviceTblPersonToAddress->getTblAddress() )) {
                                                if (!isset($AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'])) {
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'] =
                                                        $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                        $PersonToAddress->getSalutation();
                                                    if ($PersonToAddress->getSalutation() === '') {
                                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                            new Warning(new Exclamation().' Fehlt!');
                                                    }
                                                } else {
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'] =
                                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'].', '.
                                                        $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                                    if ($AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !==
                                                        new Exclamation().'Fehlt!'
                                                    ) {
                                                        if ($PersonToAddress->getSalutation() === '') {
                                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                                new Warning(new Exclamation().' Fehlt!');
                                                        } else {
                                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                                ( $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !== ''
                                                                    ? $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'].', '.
                                                                    $PersonToAddress->getSalutation()
                                                                    : $PersonToAddress->getSalutation() );
                                                        }
                                                    }
                                                }
                                            }

                                            if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                                                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                                                if ($tblProspect) {
                                                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['ReservationDivision'] = $tblProspectReservation->getReservationDivision();
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['ReservationYear'] = $tblProspectReservation->getReservationYear();
                                                    if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['ReservationOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                                                    }
                                                    if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['ReservationOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                                                    }
                                                }
                                            } else {
                                                $StudentNumber = new Small(new Muted('-NA-'));
                                                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                                if ($tblStudent) {
                                                    if ($tblStudent->getIdentifier() != '') {
                                                        $StudentNumber = $tblStudent->getIdentifier();
                                                    }
                                                }

                                                $Division = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, '');
                                                if ($Division === '') {
                                                    $Division = new Small(new Muted('-NA-'));
                                                }
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['StudentNumber'] = $StudentNumber;
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['Division'] = $Division;
                                            }

                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Person'] =
                                                ( $tblAddressPerson->getServiceTblPerson()
                                                    ? $tblAddressPerson->getServiceTblPerson()->getLastFirstName()
                                                    : new Warning(new Exclamation().' Person nicht gefunden.') );
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToAddress'] =
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'];
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Address'] =
                                                ( $tblAddressPerson->getServiceTblToPerson()
                                                    ? $tblAddressPerson->getServiceTblToPerson()->getTblAddress()->getGuiString()
                                                    : new Warning(new Exclamation().' Adresse nicht gefunden.') );
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Salutation'] =
                                                isset($AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'])
                                                && $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !== ''
                                                    ? $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList']
                                                    : new Warning(new Exclamation().' Keine Anrede hinterlegt.');
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['Option'] =
                                                new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                                    array('Id'       => $Id,
                                                          'PersonId' => $tblPerson->getId(),
                                                          'Route'    => '/Reporting/SerialLetter/Export'));
                                        }
                                    }
                                }
                            }
                        });
                        if ($AddressList) {
                            foreach ($AddressList as $Address) {
                                $countAddresses++;
                                $dataList[] = array(
                                    'Number'              => ++$count,
                                    'Person'              => ( isset($Address['Person']) ? $Address['Person'] : '' ),
                                    'StudentNumber'       => ( isset($Address['StudentNumber']) ? $Address['StudentNumber'] : '' ),
                                    'Division'            => ( isset($Address['Division']) ? $Address['Division'] : '' ),
                                    'PersonToAddress'     => ( isset($Address['PersonToAddress']) ? $Address['PersonToAddress'] : '' ),
                                    'Address'             => ( isset($Address['Address']) ? $Address['Address'] : '' ),
                                    'Company'             => ( isset($Address['Company']) ? $Address['Company'] : '' ),
                                    'ReservationDivision' => ( isset($Address['ReservationDivision']) ? $Address['ReservationDivision'] : '' ),
                                    'ReservationYear'     => ( isset($Address['ReservationYear']) ? $Address['ReservationYear'] : '' ),
                                    'ReservationOptionA'  => ( isset($Address['ReservationOptionA']) ? $Address['ReservationOptionA'] : '' ),
                                    'ReservationOptionB'  => ( isset($Address['ReservationOptionB']) ? $Address['ReservationOptionB'] : '' ),
                                    'Salutation'          => ( isset($Address['Salutation']) ? $Address['Salutation'] : '' ),
                                    'Option'              => ( isset($Address['Option']) ? $Address['Option'] : '' )
                                );
                            }
                        }
                    } else {
                        if ($tblFilterCategory && $tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                            $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                            if ($tblProspect) {
                                $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                $ReservationDivision = $tblProspectReservation->getReservationDivision();
                                $ReservationYear = $tblProspectReservation->getReservationYear();
                                if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                                    $ReservationOptionA = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                                }
                                if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                                    $ReservationOptionB = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                                }
                            }
                        } else {
                            $StudentNumber = new Small(new Muted('-NA-'));
                            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                            if ($tblStudent) {
                                if ($tblStudent->getIdentifier() != '') {
                                    $StudentNumber = $tblStudent->getIdentifier();
                                }
                            }
                            $DivisionString = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, '');
                            if ($DivisionString === '') {
                                $DivisionString = new Small(new Muted('-NA-'));
                            }
                            $Division = $DivisionString;
                        }

                        $dataList[] = array(
                            'Number'              => ++$count,
                            'Person'              => $tblPerson->getLastFirstName(),
                            'StudentNumber'       => ( isset($StudentNumber) ? $StudentNumber : '' ),
                            'Division'            => ( isset($Division) ? $Division : '' ),
                            'PersonToAddress'     => new Warning(new Exclamation().' Keine Person mit Adresse hinterlegt.'),
                            'Address'             => '',
                            'Salutation'          => '',
                            'Company'             => '',
                            'ReservationDivision' => ( isset($ReservationDivision) ? $ReservationDivision : '' ),
                            'ReservationYear'     => ( isset($ReservationYear) ? $ReservationYear : '' ),
                            'ReservationOptionA'  => ( isset($ReservationOptionA) ? $ReservationOptionA : '' ),
                            'ReservationOptionB'  => ( isset($ReservationOptionB) ? $ReservationOptionB : '' ),
                            'Option'              => new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
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

            $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter, $isCompany);

            $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
                'Anzahl Anschreiben: '.$SerialLetterCount,);
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
                                    $dataList, null, $columnList, $Interactive
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
