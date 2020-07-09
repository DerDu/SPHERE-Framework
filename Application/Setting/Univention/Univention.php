<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Publicly;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Univention
 * @package SPHERE\Application\Setting\Univention
 */
class Univention implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation(new Link(new Link\Route(__NAMESPACE__),
            new Link\Name('Univention'), new Link\Icon(new Publicly())
        ));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/Csv'),
            new Link\Name('Univention über CSV'), new Link\Icon(new Publicly())
        ));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/Api'),
            new Link\Name('Univention über API'), new Link\Icon(new Publicly())
        ));


        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'/Frontend::frontendUnivention'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Csv', __NAMESPACE__.'/Frontend::frontendUnivCSV'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Api', __NAMESPACE__.'/Frontend::frontendUnivAPI'
        ));

    }

    public static function useService()
    {

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Token'),
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