<?php

namespace SPHERE\Application\Billing\Bookkeeping;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Bookkeeping
 * @package SPHERE\Application\Billing\Bookkeeping
 */
class Bookkeeping implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Basket::registerModule();
        Invoice::registerModule();
        Balance::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Buchungen'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Basket'), new Link\Name('Warenkorb'),
                new Link\Icon(new \SPHERE\Common\Frontend\Icon\Repository\Basket()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Export'), new Link\Name('Export'),
                new Link\Icon(new Download()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Balance'), new Link\Name('Offene Posten'),
                new Link\Icon(new Document()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Invoice'), new Link\Name('Rechnungen'),
                new Link\Icon(new MoreItems()))
        );

    }
}
