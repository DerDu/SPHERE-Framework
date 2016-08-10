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
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/View',
                __NAMESPACE__.'\Frontend::frontendBankingView'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Change',
                __NAMESPACE__.'\Frontend::frontendChangeBanking'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/Banking/Reference/Change',
                __NAMESPACE__.'\Frontend::frontendChangeBankReference'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/Banking/Reference/Remove',
                __NAMESPACE__.'\Frontend::frontendRemoveBankReference'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection/Payment/Selection',
                __NAMESPACE__.'\Frontend::frontendPaymentSelection'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection/Payment/Choose',
                __NAMESPACE__.'\Frontend::frontendPaymentChoose'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection',
                __NAMESPACE__.'\Frontend::frontendDebtorSelection'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection/PaymentSelection',
                __NAMESPACE__.'\Frontend::frontendDebtorPaymentSelection'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection/PaymentChoose',
                __NAMESPACE__.'\Frontend::frontendDebtorPaymentChoose'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection/Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyDebtorSelection'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Accounting/DebtorSelection/Person/Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyDebtorSelectionByPerson'
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
