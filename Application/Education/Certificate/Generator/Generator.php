<?php
namespace SPHERE\Application\Education\Certificate\Generator;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Generator
 *
 * @package SPHERE\Application\Education\Certificate\Generator
 */
class Generator implements IModuleInterface
{

    public static function registerModule()
    {

//        Main::getDisplay()->addModuleNavigation(
//            new Link(new Link\Route(__NAMESPACE__.'\Select\Division'), new Link\Name('Zeugnis erstellen'))
//        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Select\Division', __NAMESPACE__.'\Frontend::frontendSelectDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Select\Student', __NAMESPACE__.'\Frontend::frontendSelectStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Select\Certificate', __NAMESPACE__.'\Frontend::frontendSelectCertificate'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Select\Content', __NAMESPACE__.'\Frontend::frontendSelectContent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Create', __NAMESPACE__.'\Frontend::frontendCreate'
        ));

    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
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
