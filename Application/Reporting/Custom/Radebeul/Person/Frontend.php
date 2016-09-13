<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.09.2016
 * Time: 16:04
 */

namespace SPHERE\Application\Reporting\Custom\Radebeul\Person;

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
 * @package SPHERE\Application\Reporting\Custom\Radebeul\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $Stage = new Stage();
        $Stage->setTitle('Auswertung');
        $Stage->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $Stage;
    }


    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendParentTeacherConferenceList($DivisionId = null)
    {

        $Stage = new Stage('Individuelle Auswertung', 'Anwesenheitsliste Elternabende');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Radebeul/Person/ParentTeacherConferenceList',
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
                            '/Api/Reporting/Custom/Radebeul/Person/ParentTeacherConferenceList/Download',
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Radebeul/Person/ParentTeacherConferenceList',
                    new EyeOpen(),
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
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
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
                                        'Number' => 'lfdNr.',
                                        'LastName' => 'Name',
                                        'FirstName' => 'Vorname',
                                        'Attendance' => 'Unterschrift',
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
    public function frontendDenominationList()
    {

        $Stage = new Stage('Individuelle Auswertung', 'Anwesenheitsliste Elternabende');
        $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));

        $countArray = array();
        $PersonList = Person::useService()->createDenominationList($countArray);
        if ($PersonList){
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Custom/Radebeul/Person/DenominationList/Download',
                    new Download()
                )
            );
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($PersonList, null,
                                array(
                                    'Number' => 'lfdNr.',
                                    'LastName' => 'Name',
                                    'FirstName' => 'Vorname',
                                    'Denomination' => 'Religion',
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
                            new Panel('Schüler', array(
                                $countArray['All'],
                            ), Panel::PANEL_TYPE_INFO)
                            , 3),
                        new LayoutColumn(
                            new Panel('Evangelisch', array(
                                $countArray['EV'],
                            ), Panel::PANEL_TYPE_INFO)
                            , 3),
                        new LayoutColumn(
                            new Panel('Katholisch', array(
                                $countArray['RK'],
                            ), Panel::PANEL_TYPE_INFO)
                            , 3),
                        new LayoutColumn(
                            new Panel('Ohne Angabe', array(
                                $countArray['KEINE'],
                            ), Panel::PANEL_TYPE_INFO)
                            , 3),
                    )),
                ))
            ))
        );

        return $Stage;
    }
}