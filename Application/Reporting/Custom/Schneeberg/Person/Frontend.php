<?php
/**
 * Created by PhpStorm.
 * User: kauschke
 * Date: 16.08.2016
 * Time: 13:52
 */

namespace SPHERE\Application\Reporting\Custom\Schneeberg\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Schneeberg\Person
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
    public function frontendClassList($DivisionId = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenliste');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Schneeberg/Person/ClassList',
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
                $PersonList = Person::useService()->createClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Schneeberg/Person/ClassList/Download',
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Schneeberg/Person/ClassList', new EyeOpen(),
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
                                    ))
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
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
                                        'Street' => 'Straße',
                                        'ZipCode' => 'PLZ',
                                        'City' => 'Ort',
                                        'Parents' => 'Eltern',
                                        'Phone' => 'Telefon privat',
                                        'PhoneMother' => 'Mutter',
                                        'PhoneFather' => 'Vater',
                                        'Photo' => 'FOTO',
                                    ),
                                    array(
                                        'pageLength' => -1,
                                        'responsive' => false,
                                        'order' => array(
                                            array(0, 'asc'),
                                        )
                                    )
                                )
                            )
                        )
                    ),
                ))
            );
        }

        return $Stage;
    }
}