<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.07.2016
 * Time: 13:32
 */

namespace SPHERE\Application\Education\Certificate\PrintCertificate;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class PrintCertificate implements IModuleInterface
{

    public static function registerModule()
    {

        /*
       * Navigation
       */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse drucken'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __NAMESPACE__ . '\Frontend::frontendPrintCertificate')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Confirm' , __NAMESPACE__ . '\Frontend::frontendConfirmPrintCertificate')
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}