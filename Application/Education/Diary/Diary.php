<?php

namespace SPHERE\Application\Education\Diary;

use SPHERE\Application\Education\ClassRegister\Diary\Frontend;
use SPHERE\Application\Education\ClassRegister\Diary\Service;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Diary
 *
 * @package SPHERE\Application\Education\Diary
 */
class Diary implements IApplicationInterface, IModuleInterface
{
    const LOCATION = 'SPHERE\Application\Education\ClassRegister\Diary';

    public static function registerApplication()
    {
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('pädagogisches Tagebuch'))
        );

        self::registerModule();
    }

    public static function registerModule()
    {
        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , self::LOCATION . '\Frontend::frontendSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Teacher', self::LOCATION . '\Frontend::frontendTeacherSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Headmaster', self::LOCATION . '\Frontend::frontendHeadmasterSelectDivision')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Selected', self::LOCATION . '\Frontend::frontendDiary')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Education', 'ClassRegister', null, null,
            Consumer::useService()->getConsumerBySession()),
            self::LOCATION . '/Service/Entity', self::LOCATION . '\Service\Entity'
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