<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Token;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Token
 *
 * @package SPHERE\Application\System\Gatekeeper\Token
 */
class Token implements IModuleInterface
{

    public static function registerModule()
    {

        Database::registerService(__CLASS__);

//        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__),
//            new Link\Name('Hardware-Schlüssel')),
//            new Link\Route('/Platform/Gatekeeper/Authorization')
//        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendYubiKey'
        )
            ->setParameterDefault('CredentialKey', null)
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

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Token'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }


}
