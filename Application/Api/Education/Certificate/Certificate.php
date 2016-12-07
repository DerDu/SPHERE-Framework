<?php
namespace SPHERE\Application\Api\Education\Certificate;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class Certificate extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Generator/Create', __NAMESPACE__.'\Generator\Creator::createPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/Preview', __NAMESPACE__ . '\Generator\Creator::previewPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/Download', __NAMESPACE__ . '\Generator\Creator::downloadPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Generator/PreviewZip', __NAMESPACE__ . '\Generator\Creator::previewZip'
        ));

    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }
}
