<?php
namespace SPHERE\Application\Platform\Gatekeeper\Saml;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

/**
 * Class Saml
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Saml
 */
class Saml implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MetaData', __NAMESPACE__.'/Frontend::XMLMetaData'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Login/EVSSN', __NAMESPACE__.'/Frontend::frontendLoginEVSSN'
        ));

//        // EKM -> Beispiel kann für zukünftige IDP's verwendet werden
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Login/EKM', __NAMESPACE__.'/Frontend::frontendLoginEKM'
//        ));
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }
}
