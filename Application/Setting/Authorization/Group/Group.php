<?php
namespace SPHERE\Application\Setting\Authorization\Group;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Setting\Authorization\Account\Frontend;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Group
 *
 * @package SPHERE\Application\Setting\Authorization\Group
 */
class Group extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Benutzergruppen'), new Link\Icon(new PersonGroup()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendUserGroup')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Account'),
            __DIR__ . '/../../../Platform/Gatekeeper/Authorization/Account/Service/Entity',
            '\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity'
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
