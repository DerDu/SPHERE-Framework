<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Invoice
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
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
//            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Rechnungen' ), new Link\Icon( new Info() ) )
//        );
//        Main::getDisplay()->addModuleNavigation(
//            new Link( new Link\Route( __NAMESPACE__.'/Order' ), new Link\Name( 'Freigeben' ), new Link\Icon( new Info() ) )
//        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendInvoiceList'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/View',
                __NAMESPACE__.'\Frontend::frontendInvoiceView'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/View/Remove/Paid',
                __NAMESPACE__.'\Frontend::frontendRemovePaid'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/View/Remove/Storno',
                __NAMESPACE__.'\Frontend::frontendRemoveStorno'
            )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Bookkeeping', 'Invoice', null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}
