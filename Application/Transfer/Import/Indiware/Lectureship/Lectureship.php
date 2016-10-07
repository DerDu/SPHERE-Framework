<?php
namespace SPHERE\Application\Transfer\Import\Indiware\Lectureship;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Lectureship
 *
 * @package SPHERE\Application\Transfer\Import\Indiware\Lectureship
 */
class Lectureship implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ ), new Link\Name('Indiware - Lehraufträge'),
                new Link\Icon(new Education()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __NAMESPACE__ . '\Frontend::frontendLectureship'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Year', __NAMESPACE__ . '\Frontend::frontendSelectYear'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Prepare', __NAMESPACE__ . '\Frontend::frontendLectureshipPrepare'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Import', __NAMESPACE__ . '\Frontend::frontendLectureshipImport'
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