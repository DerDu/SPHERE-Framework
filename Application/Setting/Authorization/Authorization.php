<?php
namespace SPHERE\Application\Setting\Authorization;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Authorization\Token\Token;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Authorization
 *
 * @package SPHERE\Application\Setting\Authorization
 */
class Authorization implements IApplicationInterface
{

    public static function registerApplication()
    {

        Token::registerModule();
        Account::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Benutzer'), new Link\Icon(new Person()))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, 'Authorization::frontendDashboard')
        );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Benutzer');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Authorization'));

        return $Stage;
    }
}
