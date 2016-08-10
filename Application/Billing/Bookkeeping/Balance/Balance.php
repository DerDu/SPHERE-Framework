<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Balance
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Balance implements IModuleInterface
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
//            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Offene Posten' ), new Link\Icon( new Info() ) )
//        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendBalance'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/View',
                __NAMESPACE__.'\Frontend::frontendBalanceView'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Paid',
                __NAMESPACE__.'\Frontend::frontendBalancePaid'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Reversal',
                __NAMESPACE__.'\Frontend::frontendBalanceReversal'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Payment/Edit',
                __NAMESPACE__.'\Frontend::frontendPaymentEdit'
            )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Bookkeeping', 'Balance', null,
            Consumer::useService()->getConsumerBySession()),
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
