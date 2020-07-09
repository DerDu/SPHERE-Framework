<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.03.2019
 * Time: 14:55
 */

namespace SPHERE\Application\Billing\Inventory\Document;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Document
 *
 * @package SPHERE\Application\Billing\Inventory\Document
 */
class Document implements IModuleInterface
{
    public static function registerModule()
    {
        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Bescheinigung'),
                new Link\Icon(new \SPHERE\Common\Frontend\Icon\Repository\Document()))
        );

        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendDocument'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\EditInformation',
                __NAMESPACE__.'\Frontend::frontendEditDocumentInformation'
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
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}