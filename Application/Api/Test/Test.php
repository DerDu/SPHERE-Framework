<?php
namespace SPHERE\Application\Api\Test;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

/**
 * Class Test
 *
 * @package SPHERE\Application\Api\Test
 */
class Test extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/ShowImage',
                __NAMESPACE__.'\Frontend::showImage'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/ShowContent',
                __NAMESPACE__.'\Frontend::showContent'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/ShowThumbnail',
                __NAMESPACE__.'\Frontend::showThumbnail'
            )
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/AjaxTest',
                __NAMESPACE__.'\Frontend::frontendAjaxTest'
            )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
