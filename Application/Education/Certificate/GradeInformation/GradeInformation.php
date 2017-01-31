<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2016
 * Time: 13:03
 */

namespace SPHERE\Application\Education\Certificate\GradeInformation;


use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class GradeInformation implements IModuleInterface
{

    public static function registerModule()
    {

        /*
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Noteninformation'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Teacher', __NAMESPACE__ . '\Frontend::frontendTeacherSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Headmaster', __NAMESPACE__ . '\Frontend::frontendHeadmasterSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Create', __NAMESPACE__ . '\Frontend::frontendGradeInformation')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting', __NAMESPACE__ . '\Frontend::frontendSetting')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\Preview', __NAMESPACE__ . '\Frontend::frontendPreview')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\Template\Show', __NAMESPACE__ . '\Frontend::frontendShowTemplate')
        );
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