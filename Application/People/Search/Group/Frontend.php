<?php
namespace SPHERE\Application\People\Search\Group;

use SPHERE\Application\Api\Education\Division\ValidationFilter;
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
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
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
        $Stage->addButton(new Standard('Zurück', '/People', new ChevronLeft()));
        Group::useFrontend()->addGroupSearchStageButton($Stage);

        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {

            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);

            // Consumer + Group Cache
            $Acronym = Consumer::useService()->getConsumerBySession()->getAcronym();

            $Cache = new DataCacheHandler($Acronym . ':' . $Id . ':' . $tblGroup->getMetaTable(), array(
                new TblPerson(),
                new TblMember(),
                new TblAddress(),
                new TblToPerson(),
                new TblProspect(),
                new TblStudent(),
                new TblDivision(),
                new TblDivisionStudent(),
            ));

            $filterWarning = false;
            if ($tblGroup->getMetaTable() == 'STUDENT') {
                $tblYearList = Term::useService()->getYearByNow();

                $filterWarning = ValidationFilter::receiverUsed(ValidationFilter::getContent());
            } else {
                $tblYearList = false;
            }

            if (null === ($Result = $Cache->getData())) {
                $Result = array();
                if ($tblPersonAll) {
                    array_walk($tblPersonAll,
                        function (TblPerson &$tblPerson) use ($tblGroup, &$Result, $Acronym, $tblYearList, &$validationTable) {

                            // Division && Identification
                            $displayDivisionList = false;
                            $identification = '';
                            if ($tblGroup->getMetaTable() == 'STUDENT') {
                                $displayDivisionList = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson);

                                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                if ($tblStudent) {
                                    $identification = $tblStudent->getIdentifierComplete();
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

                            // Custody
                            $childrenList = array();
                            if ($tblGroup->getMetaTable() == 'CUSTODY') {
                                if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                                    foreach ($tblRelationshipList as $tblToPerson) {
                                        if ($tblToPerson->getTblType()->getName() == "Sorgeberechtigt"
                                            && ($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom())
                                            && ($tblPersonTo = $tblToPerson->getServiceTblPersonTo())
                                            && $tblPersonFrom->getId() == $tblPerson->getId()
                                        ) {
                                            $childrenList[$tblPersonTo->getId()]
                                                = new Standard('', '/People/Person', new Person(),
                                                    array(
                                                        'Id' => $tblPersonTo->getId(),
                                                        'Group' => $tblGroup->getId()
                                                    ),
                                                    'zur Person wechseln'
                                                )
                                                . ($tblPerson->getLastName() != $tblPersonTo->getLastName()
                                                    ? $tblPersonTo->getLastFirstName() : $tblPersonTo->getFirstSecondName());
                                        }
                                    }
                                }
                            }

                            array_push($Result, array(
                                'FullName' => $tblPerson->getLastFirstName(),
                                'Address' => ($tblPerson->fetchMainAddress()
                                    ? $tblPerson->fetchMainAddress()->getGuiString()
                                    : new Warning('Keine Adresse hinterlegt')
                                ),
                                'Option' => (new Standard('', '/People/Person', new Edit(), array(
                                        'Id' => $tblPerson->getId(),
                                        'Group' => $tblGroup->getId()
                                    ), 'Bearbeiten'))
                                    . (new Standard('',
                                        '/People/Person/Destroy', new Remove(),
                                        array('Id' => $tblPerson->getId(), 'Group' => $tblGroup->getId()),
                                        'Person löschen')),
                                'Remark' => (
                                $Acronym == 'ESZC' && $tblGroup->getMetaTable() == 'CUSTODY'
                                    ? (($Common = Common::useService()->getCommonByPerson($tblPerson)) ? $Common->getRemark() : '')
                                    : ''
                                ),
                                'Division' => ($displayDivisionList ? $displayDivisionList : ''),
                                'Identification' => $identification,
                                'Year' => ($year ? $year : ''),
                                'Level' => ($level ? $level : ''),
                                'SchoolOption' => ($option ? $option : ''),
                                'Custody' => (empty($childrenList) ? '' : (string)new Listing($childrenList))
                            ));
                        });
                }
                $Cache->setData($Result);
            }

            $YearNow = '';
            if (($YearList = Term::useService()->getYearByNow())) {
                 $YearNow = current($YearList)->getYear();
            }

            if ($tblGroup->getMetaTable() == 'CUSTODY') {
                if ($Acronym == 'ESZC') {
                    $ColumnArray = array(
                        'FullName' => 'Name',
                        'Address' => 'Adresse',
                        'Custody' => 'Sorgeberechtigt für',
                        'Remark' => 'Bemerkung',
                        'Option' => '',
                    );
                } else {
                    $ColumnArray = array(
                        'FullName' => 'Name',
                        'Address' => 'Adresse',
                        'Custody' => 'Sorgeberechtigt für',
                        'Option' => '',
                    );
                }

            } elseif ($tblGroup->getMetaTable() == 'STUDENT') {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Division' => 'Klasse (SJ ' . $YearNow . ')',
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
                ($filterWarning
                    ? $filterWarning
                    : '')
                . new Layout(new LayoutGroup(array(
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
                        new TableData($Result, null, $ColumnArray, array(
                            'order' => array(
                                array(0, 'asc')
                            ),
                            'columnDefs' => array(
                                array('type' => ConsumerSetting::useService()->getGermanSortBySetting(), 'targets' => 0),
                                array('orderable' => false, 'targets' => -1),
                            )
                        ))
                    )))
                )))
            );
        } else {
            $Stage->setMessage('Bitte wählen Sie eine Gruppe');
        }

        return $Stage;
    }

    /**
     * @param Stage $Stage
     */
    public function addGroupSearchStageButton(Stage $Stage)
    {

        $tblGroupAll = Group::useService()->getGroupAllSorted();
        if (!empty($tblGroupAll)) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupAll, function (TblGroup &$tblGroup) use ($Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblGroup->getName() . '&nbsp;&nbsp;' . new Label(Group::useService()->countMemberByGroup($tblGroup)),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Id' => $tblGroup->getId()
                        ), $tblGroup->getDescription())
                );
            });
        }
    }
}
