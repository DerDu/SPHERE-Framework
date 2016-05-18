<?php
namespace SPHERE\Application\Transfer\Export;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Export\Datev\Datev;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Export
 *
 * @package SPHERE\Application\Transfer\Export
 */
class Export implements IApplicationInterface
{

    public static function registerApplication()
    {

        Datev::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten exportieren'), new Link\Icon(new Download()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

        Main::getDispatcher()->registerWidget('Export', array(__CLASS__, 'widgetDatev'), 2, 4);
    }

    /**
     * @return Thumbnail
     */
    public static function widgetDatev()
    {

        new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/datev_logo.png'),
            'Datev', 'Rechnungen',
            new Standard('', '/Sphere/Transfer/Export/Datev', new Download(), array(), 'Download')
        );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Export');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Export'));

        return $Stage;
    }
}
