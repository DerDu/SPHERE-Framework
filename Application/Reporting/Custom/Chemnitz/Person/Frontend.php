<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
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
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Chemnitz\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $Stage = new Stage();
        $Stage->setTitle('ESZC Auswertung');
        $Stage->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $Stage;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendClassList($DivisionId = null)
    {

        $Stage = new Stage('ESZC Auswertung', 'Klassenliste');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Chemnitz/Person/ClassList', new ChevronLeft()));
        }
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $PersonList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Chemnitz/Common/ClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Chemnitz/Person/ClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => ''
                                    ), array(
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc'),
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number' => '#',
                                        'Salutation' => 'Anrede',
                                        'Father' => 'Vorname Sorgeberechtigter 1',
                                        'Mother' => 'Vorname Sorgeberechtigter 2',
                                        'LastName' => 'Name',
                                        'Denomination' => 'Konfession',
                                        'Address' => 'Adresse',
                                        'FirstName' => 'Schüler',
                                        'Birthday' => 'Geburtsdatum',
                                        'Birthplace' => 'Geburtsort',
                                    ),
                                    array(
                                        'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 4),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 7),
                                    ),
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: ' . count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendStaffList()
    {

        $Stage = new Stage('ESZC Auswertung', 'Liste der Mitarbeiter');

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));
        $PersonList = Person::useService()->createStaffList();
        if ($PersonList) {
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Custom/Chemnitz/Common/StaffList/Download', new Download())
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($PersonList, null,
                                array(
                                    'Salutation'   => 'Anrede',
                                    'Title'        => 'Titel',
                                    'FirstName'    => 'Vorname',
                                    'LastName'     => 'Name',
                                    'Birthday'     => 'Geburtsdatum',
                                    'Division'     => 'Unterbereich',
                                    'Address'      => 'Adresse',
//                                    'StreetName'   => 'Straße',
//                                    'StreetNumber' => 'Hausnr.',
//                                    'Code'         => 'PLZ',
//                                    'City'         => 'Ort',
                                    'Phone1'       => 'Telefon 1',
                                    'Phone2'       => 'Telefon 2',
                                    'Mail'         => 'Mail',
                                ),
                                array(
                                    'order' => array(
                                        array(2, 'asc'),
                                        array(1, 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                                    ),
                                    "pageLength" => -1,
                                    "responsive" => false
                                )
                            )
                        )
                    )
                ),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Weiblich', array(
                                'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Männlich', array(
                                'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Gesamt', array(
                                'Anzahl: ' . count($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                null)
                        )
                    )
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendSchoolFeeList()
    {

        $Stage = new Stage('ESZC Auswertung', 'Schulgeldliste');

        $Stage->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Custom/Chemnitz/Common/SchoolFeeList/Download', new Download())
        );
        $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));
        $PersonList = Person::useService()->createSchoolFeeList();

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($PersonList, null,
                                array(
                                    'DebtorNumber' => 'Deb.-Nr.',
                                    'Reply' => 'Bescheid geschickt',
                                    'Father' => 'Sorgeberechtigter 1',
//                                    'FatherSalutation'     => 'Anrede V',
//                                    'FatherLastName'       => 'Name V',
//                                    'FatherFirstName'      => 'Vorname V',
                                    'Mother' => 'Sorgeberechtigter 2',
//                                    'MotherSalutation'     => 'Anrede M',
//                                    'MotherLastName'       => 'Name M',
//                                    'MotherFirstName'      => 'Vorname M',
                                    'Records' => 'Unterlagen eingereicht',
                                    'LastSchoolFee' => 'SG Vorjahr',
                                    'Remarks' => 'Bemerkungen',
                                    'Address' => 'Adresse',
//                                    'StreetName'           => 'Straße',
//                                    'StreetNumber'         => 'Hausnummer',
//                                    'Code'                 => 'PLZ',
//                                    'City'                 => 'Ort',
                                ),
                                array(
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                                    ),
                                    "pageLength" => -1,
                                    "responsive" => false
                                )
                            )
                        )
                    )
                ),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Weiblich', array(
                                'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Männlich', array(
                                'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Gesamt', array(
                                'Anzahl: ' . count($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                null)
                        )
                    )
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendMedicList($DivisionId = null)
    {

        $Stage = new Stage('ESZC Auswertung', 'Arztliste');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Chemnitz/Person/MedicList', new ChevronLeft()));
        }

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $PersonList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createMedicList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Chemnitz/Common/MedicList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Chemnitz/Person/MedicList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc'),
                                        ),
                                    )
                                )
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number' => '#',
                                        'LastName' => 'Name',
                                        'FirstName' => 'Vorname',
                                        'Birthday' => 'Geburtsdatum',
                                        'Address' => 'Adresse',
                                    ),
                                    array(
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                        ),
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: ' . count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendInterestedPersonList()
    {

        $Stage = new Stage('ESZC Auswertung', 'Neuanmeldungen/Interessenten');
        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Interessent'));
        $PersonList = Person::useService()->createInterestedPersonList();
        if ($PersonList) {
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Custom/Chemnitz/Common/InterestedPersonList/Download', new Download())
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($PersonList, null,
                                array(
                                    'RegistrationDate' => 'Anmeldedatum',
                                    'FirstName'        => 'Vorname',
                                    'LastName'         => 'Name',
                                    'SchoolYear'       => 'Schuljahr',
                                    'DivisionLevel'    => 'Klassenstufe',
                                    'TypeOptionA'      => 'Schulart 1',
                                    'TypeOptionB'      => 'Schulart 2',
                                    'Address'          => 'Adresse',
                                    'Birthday'         => 'Geburtsdatum',
                                    'Birthplace'       => 'Geburtsort',
                                    'Nationality'      => 'Staatsangeh.',
                                    'Denomination'     => 'Bekenntnis',
                                    'Siblings'         => 'Geschwister',
                                    'Hoard'            => 'Hort',
                                    'Father'           => 'Sorgeberechtigter 1',
                                    'Mother'           => 'Sorgeberechtigter 2',
                                    'Phone'            => 'Telefon Interessent',
                                    'PhoneGuardian'    => 'Telefon Sorgeb.',
                                ),
                                array(
                                    'order' => array(
                                        array(2, 'asc'),
                                        array(1, 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 12),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 14),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 15),
                                    ),
                                    "pageLength" => -1,
                                    "responsive" => false
                                )
                            )
                        )
                    )
                ),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Weiblich', array(
                                'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Männlich', array(
                                'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Gesamt', array(
                                'Anzahl: ' . count($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                null)
                        )
                    )
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendParentTeacherConferenceList($DivisionId = null)
    {

        $Stage = new Stage('ESZC Auswertung', 'Liste für Elternabende');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Chemnitz/Person/ParentTeacherConferenceList',
                new ChevronLeft()));
        }

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $PersonList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createParentTeacherConferenceList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Chemnitz/Common/ParentTeacherConferenceList/Download',
                            new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Chemnitz/Person/ParentTeacherConferenceList',
                    new EyeOpen(), array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc')
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number' => '#',
                                        'LastName' => 'Name',
                                        'FirstName' => 'Vorname',
                                        'Attendance' => 'Anwesenheit',
                                    ),
                                    array(
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                        ),
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: ' . count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendClubMemberList()
    {

        $Stage = new Stage('ESZC Auswertung', 'Liste der Vereinsmitglieder');
        $tblPersonList = array();
        $clubGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB);
        if ($clubGroup) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($clubGroup);
        }
        $PersonList = Person::useService()->createClubMemberList();
        if ($PersonList) {
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Custom/Chemnitz/Common/ClubMemberList/Download', new Download())
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($PersonList, null,
                                array(
                                    'Salutation'   => 'Anrede',
                                    'Title'        => 'Titel',
                                    'FirstName'    => 'Vorname',
                                    'LastName'     => 'Name',
                                    'Address'      => 'Adresse',
//                                    'StreetName'   => 'Straße',
//                                    'StreetNumber' => 'Hausnr.',
//                                    'Code'         => 'PLZ',
//                                    'City'         => 'Ort',
                                    'Phone'        => 'Telefon',
                                    'Mail'         => 'Mail',
                                    'Directorate'  => 'Vorstand'
                                ),
                                array(
                                    'order' => array(
                                        array(2, 'asc'),
                                        array(1, 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                                    ),
                                    "pageLength" => -1,
                                    "responsive" => false
                                )
                            )
                        )
                    )
                ),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Weiblich', array(
                                'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Männlich', array(
                                'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Gesamt', array(
                                'Anzahl: ' . count($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                null)
                        )
                    )
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendPrintClassList($DivisionId = null)
    {

        $Stage = new Stage('ESZC Auswertung', 'Klassenliste zum Ausdrucken');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Chemnitz/Person/PrintClassList',
                new ChevronLeft()));
        }

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $PersonList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createPrintClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Chemnitz/Common/PrintClassList/Download',
                            new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Chemnitz/Person/PrintClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc')
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $tblCustodyList = Division::useService()->getCustodyAllByDivision($tblDivision);
            $CustodyString = '';
            /** @var TblPerson $tblCustody */
            if (!empty($tblCustodyList)) {
                foreach ($tblCustodyList as $tblCustody) {
                    if (!empty($CustodyString)) {
                        $CustodyString .= ', ' . $tblCustody->getFullName();
                    } else {
                        $CustodyString .= $tblCustody->getFullName();
                    }
                }
            }

            $IntegrationStudent = false;
            $CounterDivisionGroup1 = 0;
            $CounterDivisionGroup2 = 0;
            $OrientationList = array();
            $FrenchCounter = 0;
            $EducationList = array();
            if ($PersonList) {
                foreach ($PersonList as $PersonData) {
                    if (isset($PersonData['Integration']) && $PersonData['Integration'] == 1) {
                        $IntegrationStudent = true;
                    }
                    if (!empty($PersonData['Group'])) {
                        if ($PersonData['Group1']) {
                            $CounterDivisionGroup1++;
                        }
                        if ($PersonData['Group2']) {
                            $CounterDivisionGroup2++;
                        }
//                        Debugger::screenDump($PersonData['Group1']);
//                        Debugger::screenDump($PersonData['Group2']);
                    }
                    if (!empty($PersonData['Orientation'])) {
                        if(isset($OrientationList[$PersonData['Orientation']])){
                            $OrientationList[$PersonData['Orientation']] += 1;
                        } else {
                            $OrientationList[$PersonData['Orientation']] = 1;
                        }
                    }
                    if (!empty($PersonData['French'])) {
                        $FrenchCounter++;
                    }
                    if (!empty($PersonData['Education'])) {
                        if(isset($EducationList[$PersonData['Education']])){
                            $EducationList[$PersonData['Education']] += 1;
                        } else {
                            $EducationList[$PersonData['Education']] = 1;
                        }
                    }
                }
            }



            $GroupCountString = '';
            if (!empty($CounterDivisionGroup1)) {
                $GroupCountString .= 'Anzahl Gruppe 1: ' . $CounterDivisionGroup1;
            }
            if (!empty($CounterDivisionGroup2)) {
                if (!empty($GroupCountString)) {
                    $GroupCountString .= '<br/>Anzahl Gruppe 2: ' . $CounterDivisionGroup2;
                } else {
                    $GroupCountString .= 'Anzahl Gruppe 2: ' . $CounterDivisionGroup2;
                }
            }

            $FrenchCountString = '';
            if (!empty($FrenchCounter)) {
                $FrenchCountString = 'Anzahl: ' . $FrenchCounter;
            }

            $EducationCountString = '';
            if (!empty($EducationList)) {
                foreach ($EducationList as $Education => $Count) {
                    if (!empty($EducationCountString)) {
                        $EducationCountString .= '<br/>Anzahl ' . $Education . ': ' . $Count;
                    } else {
                        $EducationCountString .= 'Anzahl ' . $Education . ': ' . $Count;
                    }
                }
            }

            $OrientationCountString = '';
            if (!empty($OrientationList)) {
                foreach ($OrientationList as $Orientation => $Count) {
                    if (!empty($OrientationCountString)) {
                        $OrientationCountString .= '<br/>Anzahl ' . $Orientation . ': ' . $Count;
                    } else {
                        $OrientationCountString .= 'Anzahl ' . $Orientation . ': ' . $Count;
                    }
                }
            }



            $LayoutColumnCounterList = array();
            $LayoutColumnCounterList[] = new LayoutColumn(
                new Panel('Geschlecht', array(
                    'Anzahl Weiblich: ' . Person::countFemaleGenderByPersonList($tblPersonList) .
                    new Container('Anzahl Männlich: ' . Person::countMaleGenderByPersonList($tblPersonList)) .
                    new Container( 'Anzahl Gesamt: ' . count($tblPersonList)),
                ), Panel::PANEL_TYPE_INFO)
                , 3);
            if (!empty($EducationCountString)) {
                $LayoutColumnCounterList[] = new LayoutColumn(new Panel('Bildungsgänge', $EducationCountString, Panel::PANEL_TYPE_INFO),3);
            }
            if (!empty($OrientationCountString)) {
                $LayoutColumnCounterList[] = new LayoutColumn(new Panel('Wahlbereiche', $OrientationCountString, Panel::PANEL_TYPE_INFO),3);
            }
            if (!empty($GroupCountString)) {
                $LayoutColumnCounterList[] = new LayoutColumn(new Panel('Gruppen', $GroupCountString, Panel::PANEL_TYPE_INFO), 3);
            }
            if (!empty($FrenchCountString)) {
                $LayoutColumnCounterList[] = new LayoutColumn(new Panel('Französisch', $FrenchCountString, Panel::PANEL_TYPE_INFO), 3);
            }

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblRelationship
             */
            foreach ($LayoutColumnCounterList as $Column) {
                if ($LayoutRowCount % 4 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($Column);
                $LayoutRowCount++;
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 3
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 3
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 3
                                ) : ''),
                            new LayoutColumn(new Panel('Elternsprecher',
                                (!empty($CustodyString) ? $CustodyString : '&nbsp;'),
                                    Panel::PANEL_TYPE_SUCCESS), 3
                            )
                        )),
                        new LayoutRow(
                            new LayoutColumn(($IntegrationStudent ? new Warning(new Child().' Schriftart-Fett für Kinder mit Förderbedarf') .'<br/>' : ''))
                        )
                    )),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number' => '#',
                                        'DisplayName' => 'Name',
                                        'Birthday' => 'Geb.-Datum',
                                        'Address' => 'Adresse',
                                        'PhoneNumbers' => 'Telefonnummer',
                                        'Group'        => 'Schüler&shy;gruppe',
                                        'OrientationAndFrench'  => 'WB/Profil/FR',
                                        'Education'    => 'Bildungsgang',
                                        'Elective'     => 'Wahlfach',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup($LayoutRowList),
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }

        return $Stage;
    }
}
