<?php
namespace SPHERE\Application\Platform\System\Archive;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * @deprecated
 * Class Archive
 *
 * @package SPHERE\Application\Platform\System\Archive
 */
class Archive extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Archivierung'), new Link\Icon(new Listing()))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, 'Frontend::frontendArchive')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'System', 'Protocol'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}
