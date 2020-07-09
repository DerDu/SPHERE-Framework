<?php

namespace SPHERE\Application\People;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IClusterInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Meta;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Search;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class People
 *
 * @package SPHERE\Application\People
 */
class People implements IClusterInterface
{

    public static function registerCluster()
    {

        Search::registerApplication();
        Person::registerApplication();
        Group::registerApplication();
        Meta::registerApplication();
        Relationship::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Personen'), new Link\Icon(new PersonIcon()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));

        Main::getDispatcher()->registerWidget('Personen', array(__CLASS__, 'widgetPersonGroupList'), 4, 6);
    }

    /**
     * @return Layout
     */
    public static function widgetPersonGroupList()
    {

        $tblGroupAll = Group::useService()->getGroupAllSorted();
        $tblGroupLockedList = array();
        $tblGroupCustomList = array();
        if ($tblGroupAll) {
            /** @var TblGroup $tblGroup */
            foreach ((array)$tblGroupAll as $Index => $tblGroup) {

                $countContent = new Muted(new Small(Group::useService()->countMemberByGroup($tblGroup) . '&nbsp;Mitglieder'));
                $content =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                $tblGroup->getName()
                                . new Muted(new Small('<br/>' . $tblGroup->getDescription(true)))
                                , 5),
                            new LayoutColumn(
                                $countContent
                                , 6),
                            new LayoutColumn(
                                new PullRight(
                                    new Standard('', '/People/Search/Group',
                                        new \SPHERE\Common\Frontend\Icon\Repository\Group(),
                                        array('Id' => $tblGroup->getId()))
                                ), 1)
                        )
                    )));

                if ($tblGroup->isLocked()) {
                    $tblGroupLockedList[] = $content;
                    if ($tblGroup->getMetaTable() == 'STUDENT') {

                        $countContent = self::getStudentCountByType();
                        $countContent = new Listing($countContent);

                        $content =
                            new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(
                                        'Schüler / Schuljahr'
                                        . new Muted(new Small('<br/>' . 'Schüler die in Klassen zugeordnet sind'))
                                        , 5),
                                    new LayoutColumn(
                                        $countContent
                                        , 7),
                                )
                            )));
                        $tblGroupLockedList[] = $content;
                    }
                } else {
                    $tblGroupCustomList[] = $content;
                }
            }
        }

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Panel('Personen in festen Gruppen', $tblGroupLockedList), 6
            ),
            !empty($tblGroupCustomList) ?
                new LayoutColumn(
                    new Panel('Personen in individuellen Gruppen', $tblGroupCustomList), 6) : null
        ))));
    }

    /**
     * @return array
     */
    private static function getStudentCountByType()
    {
        $tblYearList = Term::useService()->getYearByNow();
        $StudentCountBySchoolType = array();
        // Schüler nach Schulart zählen
        if (!empty( $tblYearList )) {
            foreach ($tblYearList as $tblYear) {
                $TblDivisionList = Division::useService()->getDivisionByYear($tblYear);
                if ($TblDivisionList) {
                    foreach ($TblDivisionList as $tblDivision) {
                        // SSW-834 jahrgangsübergreifende nicht mitzählen, ansonsten werden Schüler doppelt gezählt
                        if (($tblLevel = $tblDivision->getTblLevel())
                            && ($tblLevel->getIsChecked())
                        ) {
                            continue;
                        }

                        $schoolType = $tblDivision->getTypeName();
                        $personCount = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                        if (isset($StudentCountBySchoolType[$schoolType])) {
                            $StudentCountBySchoolType[$schoolType] += $personCount;
                        } else {
                            $StudentCountBySchoolType[$schoolType] = $personCount;
                        }
                    }
                }
            }
        }

        $tblStudentCounterBySchoolType = array();
        // Alle Schüler im Schuljahr (Summiert)
        if (($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYear) {
                $tblStudentCounterBySchoolType[] = new Muted(new Small(
                    Division::useService()->getStudentCountByYear($tblYear)
                    . ' Mitglieder im Schuljahr ' . $tblYear->getDisplayName() . ' '));
            }
        }
        // Anhängen der Schulartzählung
        if (!empty($StudentCountBySchoolType)) {
            foreach ($StudentCountBySchoolType as $SchoolType => $Counter) {
                $tblStudentCounterBySchoolType[] = new Muted(new Small($SchoolType . ': ' . $Counter));
            }
        }

        return $tblStudentCounterBySchoolType; //(!empty($tblStudentCounterBySchoolType) ? implode(new Container(), $tblStudentCounterBySchoolType) : '');
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Personen');

//        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Personen'));
        $Stage->setContent(self::widgetPersonGroupList());

        return $Stage;
    }
}
