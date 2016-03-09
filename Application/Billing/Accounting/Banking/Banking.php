<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;


/**
 * Class Banking
 * @package SPHERE\Application\Billing\Accounting\Banking
 */
class Banking implements IModuleInterface
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
//                Main::getDisplay()->addApplicationNavigation(
//                    new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Debitoren' ), new Link\Icon( new Money() ) )
//                );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendBanking'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/BankReference',
                __NAMESPACE__.'\Frontend::frontendBankReference'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/View',
                __NAMESPACE__.'\Frontend::frontendBankingView'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Add',
                __NAMESPACE__.'\Frontend::frontendAddBanking'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Change',
                __NAMESPACE__.'\Frontend::frontendChangeBanking'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/BankReference/Add',
                __NAMESPACE__.'\Frontend::frontendAddBankReference'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/BankReference/View',
                __NAMESPACE__.'\Frontend::frontendBankReferenceView'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/BankReference/Change',
                __NAMESPACE__.'\Frontend::frontendChangeBankReference'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/BankReference/Remove',
                __NAMESPACE__.'\Frontend::frontendRemoveBankReference'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/Pay/Selection',
                __NAMESPACE__.'\Frontend::frontendPaySelection'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/Pay/Choose',
                __NAMESPACE__.'\Frontend::frontendPayChoose'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection',
                __NAMESPACE__.'\Frontend::frontendDebtorSelection'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection/PaySelection',
                __NAMESPACE__.'\Frontend::frontendDebtorPaySelection'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection/PayChoose',
                __NAMESPACE__.'\Frontend::frontendDebtorPayChoose'
            ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Accounting', 'Banking', null,
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
