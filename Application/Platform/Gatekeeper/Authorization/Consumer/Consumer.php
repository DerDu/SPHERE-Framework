<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Consumer
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Consumer
 */
class Consumer implements IModuleInterface
{

    public static function registerModule()
    {

        Database::registerService(__CLASS__);

        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__),
            new Link\Name('Mandanten')),
            new Link\Route('/Platform/Gatekeeper/Authorization')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendConsumer'
        )
            ->setParameterDefault('ConsumerAcronym', null)
            ->setParameterDefault('ConsumerName', null)
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
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Consumer'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }


}
