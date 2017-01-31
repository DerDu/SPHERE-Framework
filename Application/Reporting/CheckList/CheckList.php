<?php

namespace SPHERE\Application\Reporting\CheckList;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class CheckList
 *
 * @package SPHERE\Application\Reporting\CheckList
 */
class CheckList implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Check-Listen'))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__.'\Frontend::frontendList')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Edit',
                __NAMESPACE__.'\Frontend::frontendListEdit')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Element/Select',
                __NAMESPACE__.'\Frontend::frontendListElementSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Element/Edit',
                __NAMESPACE__.'\Frontend::frontendListElementEdit')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Element/Remove',
                __NAMESPACE__.'\Frontend::frontendListElementRemove')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Object/Select',
                __NAMESPACE__.'\Frontend::frontendListObjectSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Object/Add',
                __NAMESPACE__.'\Frontend::frontendListObjectAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Object/Remove',
                __NAMESPACE__.'\Frontend::frontendListObjectRemove')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Object/Element/Show',
                __NAMESPACE__.'\Frontend::frontendListObjectElementShow')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Object/Element/Edit',
                __NAMESPACE__.'\Frontend::frontendListObjectElementEdit')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy', __NAMESPACE__.'\Frontend::frontendDestroyList')
        );
    }

    public static function registerModule()
    {

    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Reporting', 'CheckList', null, null, Consumer::useService()->getConsumerBySession()),
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
