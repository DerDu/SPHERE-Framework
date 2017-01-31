<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.11.2016
 * Time: 08:46
 */

namespace SPHERE\Application\Education\Certificate\Generate;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Generate
 *
 * @package SPHERE\Application\Education\Certificate\Generate
 */
class Generate implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse generieren'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __NAMESPACE__ . '\Frontend::frontendGenerate')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Division\Select' , __NAMESPACE__ . '\Frontend::frontendSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Division' , __NAMESPACE__ . '\Frontend::frontendDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Division\SelectTemplate' , __NAMESPACE__ . '\Frontend::frontendSelectTemplate')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
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