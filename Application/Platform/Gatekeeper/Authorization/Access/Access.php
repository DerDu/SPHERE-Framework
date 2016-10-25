<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Access
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access
 */
class Access implements IModuleInterface
{

    public static function registerModule()
    {

        Database::registerService(__CLASS__);

        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__),
            new Link\Name('Rechteverwaltung')),
            new Link\Route('/Platform/Gatekeeper/Authorization')
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendWelcome'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Role', __NAMESPACE__.'\Frontend::frontendRole'
        )
            ->setParameterDefault('Name', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/RoleGrantLevel', __NAMESPACE__.'\Frontend::frontendRoleGrantLevel'
        )
            ->setParameterDefault('tblLevel', null)
            ->setParameterDefault('Remove', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Level', __NAMESPACE__.'\Frontend::frontendLevel'
        )
            ->setParameterDefault('Name', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/LevelGrantPrivilege', __NAMESPACE__.'\Frontend::frontendLevelGrantPrivilege'
        )
            ->setParameterDefault('tblPrivilege', null)
            ->setParameterDefault('Remove', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Privilege', __NAMESPACE__.'\Frontend::frontendPrivilege'
        )
            ->setParameterDefault('Name', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/PrivilegeGrantRight', __NAMESPACE__.'\Frontend::frontendPrivilegeGrantRight'
        )
            ->setParameterDefault('tblRight', null)
            ->setParameterDefault('Remove', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Right', __NAMESPACE__.'\Frontend::frontendRight'
        )
            ->setParameterDefault('Name', null)
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Access'),
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
