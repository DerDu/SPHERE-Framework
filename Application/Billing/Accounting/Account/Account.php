<?php

namespace SPHERE\Application\Billing\Accounting\Account;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Account
 * @package SPHERE\Application\Billing\Accounting\Account
 */
class Account implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Route
         */
        //ToDO sinnvoll implemenireren
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Account::frontendAccountFibu'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Activate',
                __NAMESPACE__.'\Frontend::frontendActivateAccountFibu'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Deactivate',
                __NAMESPACE__.'\Frontend::frontendDeactivateAccountFibu'
            ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Invoice', null, null,
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

    /**
     * @return Stage
     */
    public function frontendAccountFibu()
    {

        $Stage = new Stage('Fibo', 'ToDO');
        return $Stage;
    }
}
