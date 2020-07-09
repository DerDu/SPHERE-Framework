<?php
namespace SPHERE\Application\Manual\Support;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Support
 *
 * @package SPHERE\Application\Manual\Support
 */
class Support implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Feedback & Support'), new Link\Icon(new Comment()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendSupport'
        ));

//        Main::getDisplay()->addApplicationNavigation(
//            new Link(new Link\Route('\Manual\Request'), new Link\Name('Anfrage'), new Link\Icon(new Comment()))
//        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            '\Manual\Request', __NAMESPACE__.'/Frontend::frontendRequest'
        ));
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
