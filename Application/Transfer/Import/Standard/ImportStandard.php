<?php
namespace SPHERE\Application\Transfer\Import\Standard;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element\Ruler;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;

class ImportStandard implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Interested', __NAMESPACE__.'\Frontend::frontendInterestedImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Stuff', __NAMESPACE__.'\Frontend::frontendStuffImport'
        ));
    }

    /**
     * @return LayoutGroup
     */
    public static function getStandardLink()
    {
        $ColumnList = array();
        $ColumnList[] = new LayoutColumn(
            new Panel(new Center('Schüler').new Ruler().new Center(new Standard('', __NAMESPACE__.'/Student'
                    , new Upload())), '', Panel::PANEL_TYPE_PRIMARY)
            , 3);
        $ColumnList[] = new LayoutColumn(
            new Panel(new Center('Interessenten').new Ruler().new Center(new Standard('', __NAMESPACE__.'/Interested'
                    , new Upload())), '', Panel::PANEL_TYPE_PRIMARY)
            , 3);
        $ColumnList[] = new LayoutColumn(
            new Panel(new Center('Mitarbeiter/Lehrer').new Ruler().new Center(new Standard('', __NAMESPACE__.'/Stuff'
                    , new Upload())), '', Panel::PANEL_TYPE_PRIMARY)
            , 3);

        return new LayoutGroup(
            new LayoutRow(
                $ColumnList
            )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}