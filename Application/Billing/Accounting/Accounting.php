<?php

namespace SPHERE\Application\Billing\Accounting;

use SPHERE\Application\Billing\Accounting\Account\Account;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\SchoolAccount\SchoolAccount;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Accounting
 * @package SPHERE\Application\Billing\Accounting
 */
class Accounting implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
//        Account::registerModule();
        Banking::registerModule();
        SchoolAccount::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Buchhaltung'))
        );
//        Main::getDisplay()->addModuleNavigation(
//            new Link(new Link\Route(__NAMESPACE__.'/Account'), new Link\Name('FIBU-Konten'),
//                new Link\Icon(new ClipBoard()))
//        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Banking'), new Link\Name('Debitoren'),
                new Link\Icon(new Person()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/SchoolAccount'), new Link\Name('Kontoeinstellung'),
                new Link\Icon(new Cog()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/DebtorSelection'), new Link\Name('Zahlungseinstellung'),
                new Link\Icon(new CogWheels()))
        );

    }
}
