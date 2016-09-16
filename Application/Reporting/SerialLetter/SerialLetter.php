<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.04.2016
 * Time: 08:10
 */

namespace SPHERE\Application\Reporting\SerialLetter;


use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class SerialLetter implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Adresslisten für Serienbriefe'))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendSerialLetter')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Address', __NAMESPACE__.'\Frontend::frontendPersonAddress')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Address/Edit', __NAMESPACE__.'\Frontend::frontendPersonAddressEdit')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person/Select', __NAMESPACE__.'\Frontend::frontendSerialLetterPersonSelected')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '/Edit', __NAMESPACE__ . '\Frontend::frontendSerialLetterEdit')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '/Destroy', __NAMESPACE__ . '\Frontend::frontendSerialLetterDestroy')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '/Export', __NAMESPACE__ . '\Frontend::frontendSerialLetterExport')
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
            new Identifier('Reporting', 'SerialLetter', null, null, Consumer::useService()->getConsumerBySession()),
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