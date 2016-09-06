<?php
namespace SPHERE\Application\Document\Storage;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Storage
 *
 * @package SPHERE\Application\Document\Storage
 */
class Storage implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Manager'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendManager'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Document', 'Storage', null, null, Consumer::useService()->getConsumerBySession()),
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

    /**
     * @param string $Extension
     * @param string $Prefix
     * @param bool   $Destruct
     *
     * @return FilePointer
     */
    public static function createFilePointer(
        $Extension = 'document-storage',
        $Prefix = 'SPHERE-Temporary',
        $Destruct = true
    ) {

        return new FilePointer($Extension, $Prefix, $Destruct);
    }
}
