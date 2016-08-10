<?php
namespace SPHERE\Application\Transfer\Export\Invoice;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Invoice
 * @package SPHERE\Application\Transfer\Export\Invoice
 */
class Invoice implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Module
         */
//        Error::registerModule();
        /**
         * Register Navigation
         */
//        Main::getDisplay()->addApplicationNavigation(
//            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Export' ), new Link\Icon( new Download() ) )
//        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('\Billing\Bookkeeping\Export',
                __NAMESPACE__.'\Frontend::frontendExport'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('\Billing\Bookkeeping\Export'.'/Prepare',
                __NAMESPACE__.'\Frontend::frontendPrepare'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('\Billing\Bookkeeping\Export'.'/Prepare/View',
                __NAMESPACE__.'\Frontend::frontendPrepareView'
            )
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
