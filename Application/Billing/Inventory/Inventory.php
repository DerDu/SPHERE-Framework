<?php

namespace SPHERE\Application\Billing\Inventory;

use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Inventory
 * @package SPHERE\Application\Billing\Inventory
 */
class Inventory implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Commodity::registerModule();
        Item::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Inventar'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Item'), new Link\Name('Artikel'),
                new Link\Icon(new CommodityItem()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Commodity'), new Link\Name('Leistungen'),
                new Link\Icon(new \SPHERE\Common\Frontend\Icon\Repository\Commodity()))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Inventory', __CLASS__.'::frontendWelcome'
        ));

    }

}
