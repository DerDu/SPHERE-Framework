<?php
namespace SPHERE\Application\People\Search\Group;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Handler\DataCacheHandler;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Search\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool|false|int $Id
     *
     * @return Stage
     */
    public function frontendSearch($Id = false)
    {

        $Stage = new Stage('Suche', 'nach Gruppe');
        $Stage->addButton( new Backward() );

        $tblGroupAll = Group::useService()->getGroupAllSorted();
        if (!empty($tblGroupAll)) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupAll, function (TblGroup &$tblGroup) use ($Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblGroup->getName().'&nbsp;&nbsp;'.new Label(Group::useService()->countMemberAllByGroup($tblGroup)),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Id' => $tblGroup->getId()
                        ), $tblGroup->getDescription())
                );
            });
        }

        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {

            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);

            // Consumer + Group Cache
            $Acronym = Consumer::useService()->getConsumerBySession()->getAcronym();

            $Cache = new DataCacheHandler($Acronym.':'.$Id.':'.$tblGroup->getMetaTable(), array(
                new TblPerson(),
                new TblMember(),
                new TblAddress(),
                new TblToPerson(),
                new TblProspect(),
                new TblStudent(),
                new TblDivision(),
                new TblDivisionStudent(),
            ));
            if (null === ( $Result = $Cache->getData() )) {

                if ($tblGroup->getMetaTable() == 'STUDENT') {
                    $tblYearList = Term::useService()->getYearByNow();
                } else {
                    $tblYearList = false;
                }

                $Result = array();
                if ($tblPersonAll) {
                    array_walk($tblPersonAll,
                        function (TblPerson &$tblPerson) use ($tblGroup, &$Result, $Acronym, $tblYearList) {

                            // Division && Identification
                            $displayDivisionList = false;
                            $identification = '';
                            if ($tblGroup->getMetaTable() == 'STUDENT') {
                                $displayDivisionList = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson);

                                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                if ($tblStudent) {
                                    $identification = $tblStudent->getIdentifier();
                                }

                            }

                            // Prospect
                            $level = false;
                            $year = false;
                            $option = false;
                            if ($tblGroup->getMetaTable() == 'PROSPECT') {
                                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                                if ($tblProspect) {
                                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                    if ($tblProspectReservation) {
                                        $level = $tblProspectReservation->getReservationDivision();
                                        $year = $tblProspectReservation->getReservationYear();
                                        $optionA = $tblProspectReservation->getServiceTblTypeOptionA();
                                        $optionB = $tblProspectReservation->getServiceTblTypeOptionB();
                                        if ($optionA && $optionB) {
                                            $option = $optionA->getName() . ', ' . $optionB->getName();
                                        } elseif ($optionA) {
                                            $option = $optionA->getName();
                                        } elseif ($optionB) {
                                            $option = $optionB->getName();
                                        }
                                    }
                                }
                            }

                            array_push($Result, array(
                                'FullName'       => $tblPerson->getLastFirstName(),
                                'Address'        => ( $tblPerson->fetchMainAddress()
                                    ? $tblPerson->fetchMainAddress()->getGuiString()
                                    : new Warning('Keine Adresse hinterlegt')
                                ),
                                'Option'         => (new Standard('', '/People/Person', new Edit(), array(
                                        'Id' => $tblPerson->getId(),
                                        'Group' => $tblGroup->getId()
                                    ), 'Bearbeiten'))
                                    . (new Standard('',
                                        '/People/Person/Destroy', new Remove(),
                                        array('Id' => $tblPerson->getId(), 'Group' => $tblGroup->getId()),
                                        'Person löschen')),
                                'Remark'         => (
                                $Acronym == 'ESZC' && $tblGroup->getMetaTable() == 'CUSTODY'
                                    ? (($Common = Common::useService()->getCommonByPerson($tblPerson)) ? $Common->getRemark() : '')
                                    : ''
                                ),
                                'Division'       => ($displayDivisionList ? $displayDivisionList : ''),
                                'Identification' => $identification,
                                'Year'           => ($year ? $year : ''),
                                'Level'          => ($level ? $level : ''),
                                'SchoolOption'   => ($option ? $option : '')
                            ));
                        });
                }
                $Cache->setData($Result);
            }

            if ($Acronym == 'ESZC' && $tblGroup->getMetaTable() == 'CUSTODY') {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Remark' => 'Bemerkung',
                    'Option' => '',
                );
            } elseif ($tblGroup->getMetaTable() == 'STUDENT') {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Division' => 'Klasse',
                    'Identification' => 'Schülernummer',
                    'Option' => '',
                );
            } elseif ($tblGroup->getMetaTable() == 'PROSPECT') {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Year' => 'Schuljahr',
                    'Level' => 'Klassenstufe',
                    'SchoolOption' => 'Schulart',
                    'Option' => '',
                );
            } else {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Option' => '',
                );
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(
                        new Panel(new PersonGroup() . ' Gruppe', array(
                            new Bold($tblGroup->getName()),
                            ($tblGroup->getDescription() ? new Small($tblGroup->getDescription()) : ''),
                            ($tblGroup->getRemark() ? new Danger(new Italic(nl2br($tblGroup->getRemark()))) : '')
                        ), Panel::PANEL_TYPE_INFO
                        )
                    )),
                    new LayoutRow(new LayoutColumn(array(
                        new Headline('Verfügbare Personen', 'in dieser Gruppe'),
                        new TableData($Result, null, $ColumnArray)
                    )))
                )))
            );
        } else {
            $Stage->setMessage('Bitte wählen Sie eine Gruppe');
        }

        return $Stage;
    }
}
