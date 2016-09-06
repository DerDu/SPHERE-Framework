<?php

namespace SPHERE\Application\Reporting\Custom\Herrnhut\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
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
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Herrnhut\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $Stage = new Stage();
        $Stage->setTitle('EZGH Auswertung');
        $Stage->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendProfileList($DivisionId = null)
    {
        $Stage = new Stage('EZGH Auswertung', 'Klassenliste Profile');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Herrnhut/Person/ProfileList', new ChevronLeft()));
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
                $PersonList = Person::useService()->createProfileList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Herrnhut/Common/ProfileList/Download', new Download(),
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Herrnhut/Person/ProfileList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
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
                                        'Year'     => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type'     => 'Schulart',
                                        'Count'    => 'Schüler',
                                        'Option'   => ''
                                    ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc')
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ( $tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ( $tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'        => '#',
                                        'LastName'  => 'Name',
                                        'FirstName' => 'Vorname',
                                        'Profile'   => 'Profil',
                                    ),
                                    array(
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
                                    'Anzahl: '.Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: '.Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: '.count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null )
                            )
                        )
                    ))
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendSignList($DivisionId = null)
    {
        $Stage = new Stage('EZGH Auswertung', 'Unterschriften Liste');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Herrnhut/Person/SignList', new ChevronLeft()));
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
                $PersonList = Person::useService()->createSignList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Herrnhut/Common/SignList/Download', new Download(),
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Herrnhut/Person/SignList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
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
                                        'Year'     => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type'     => 'Schulart',
                                        'Count'    => 'Schüler',
                                        'Option'   => ''
                                    ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc')
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ( $tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ( $tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'        => '#',
                                        'LastName'  => 'Name',
                                        'FirstName' => 'Vorname',
                                        'Empty'     => 'Unterschrift',
                                    ),
                                    array(
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
                                    'Anzahl: '.Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: '.Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: '.count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null )
                            )
                        )
                    ))
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendLanguageList($DivisionId = null)
    {
        $Stage = new Stage('EZGH Auswertung', 'Klassenliste Fremdsprachen');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Herrnhut/Person/LanguageList', new ChevronLeft()));
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
                $PersonList = Person::useService()->createLanguageList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Herrnhut/Common/LanguageList/Download', new Download(),
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Herrnhut/Person/LanguageList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
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
                                        'Year'     => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type'     => 'Schulart',
                                        'Count'    => 'Schüler',
                                        'Option'   => ''
                                    ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc')
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ( $tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ( $tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'        => '#',
                                        'LastName'     => 'Name',
                                        'FirstName'    => 'Vorname',
                                        'StreetName'   => 'Straße',
                                        'StreetNumber' => 'Nummer',
                                        'Code'         => 'PLZ',
                                        'City'         => 'Ort',
                                        'Birthday'     => 'Geb.-datum',
                                        'Birthplace'   => 'Geburtsort',
                                        'FS1'          => 'FS 1',
                                        'FS2'          => 'FS 2',
                                        'FS3'          => 'FS 3',
                                        'FS4'          => 'FS 4',
                                    ),
                                    array(
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
                                    'Anzahl: '.Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: '.Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: '.count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null )
                            )
                        )
                    ))
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendClassList($DivisionId = null)
    {

        $Stage = new Stage('EZGH Auswertung', 'Klassenliste');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Herrnhut/Person/ClassList', new ChevronLeft()));
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
                            '/Api/Reporting/Custom/Herrnhut/Common/ClassList/Download', new Download(),
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Herrnhut/Person/ClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
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
                                        'Year'     => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type'     => 'Schulart',
                                        'Count'    => 'Schüler',
                                        'Option'   => ''
                                    ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc')
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ( $tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ( $tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'        => '#',
                                        'LastName'     => 'Name',
                                        'FirstName'    => 'Vorname',
                                        'Birthday'     => 'Geb.-datum',
                                        'Birthplace'   => 'Geburtsort',
                                        'StreetName'   => 'Straße',
                                        'StreetNumber' => 'Nr.',
                                        'Code'         => 'PLZ',
                                        'City'         => 'Ort',
                                    ),
                                    array(
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
                                    'Anzahl: '.Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: '.Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: '.count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null )
                            )
                        )
                    ))
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendExtendedClassList($DivisionId = null)
    {

        $Stage = new Stage('EZGH Auswertung', 'Erweiterte Klassenliste');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Herrnhut/Person/ExtendedClassList', new ChevronLeft()));
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
                $PersonList = Person::useService()->createExtendedClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Herrnhut/Common/ExtendedClassList/Download', new Download(),
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Herrnhut/Person/ExtendedClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
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
                                        'Year'     => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type'     => 'Schulart',
                                        'Count'    => 'Schüler',
                                        'Option'   => ''
                                    ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc')
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ( $tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ( $tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'        => '#',
                                        'LastName'     => 'Name',
                                        'FirstName'    => 'Vorname',
                                        'PhoneNumbers' => 'Telefon-Nr.',
                                        'Email'        => 'E-Mail',
                                        'Parents'      => 'Erziehungsberechtigte',
                                        'Entrance'     => 'Aufnahme',
                                        'Leaving'      => 'Abgang',
                                    ),
                                    array(
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
                                    'Anzahl: '.Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: '.Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: '.count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null )
                            )
                        )
                    ))
                ))
            );
        }
        return $Stage;
    }
}
