<?php
namespace SPHERE\Application\Transfer\Import\Indiware;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Indiware
 *
 * @package SPHERE\Application\Transfer\Import\Indiware
 */
class Indiware implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Lectureship'), new Link\Name('Indiware - Lehraufträge'),
                new Link\Icon(new Education()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship', __NAMESPACE__.'\Frontend::frontendLectureship'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Year', __NAMESPACE__.'\Frontend::frontendSelectYear'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Prepare', __NAMESPACE__.'\Frontend::frontendLectureshipPrepare'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Import', __NAMESPACE__.'\Frontend::frontendLectureshipImport'
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
