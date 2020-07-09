<?php
namespace SPHERE\Application\Platform\Gatekeeper;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authentication\Authentication;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Authorization;
use SPHERE\Application\Platform\Gatekeeper\Saml\Saml;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Gatekeeper
 *
 * @package SPHERE\Application\System\Gatekeeper
 */
class Gatekeeper implements IApplicationInterface
{

    public static function registerApplication()
    {

        Authorization::registerModule();
        Authentication::registerModule();
        Saml::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Authorization'), new Link\Name('Berechtigungen'),
                new Link\Icon(new PersonKey()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Authorization', __CLASS__.'::frontendWelcome'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage('Berechtigungen');
        return $Stage;
    }
}
