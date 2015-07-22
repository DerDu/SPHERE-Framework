<?php
namespace SPHERE\Application\System\Gatekeeper;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\System\Gatekeeper\Account\Account;
use SPHERE\Application\System\Gatekeeper\Authentication\Authentication;
use SPHERE\Application\System\Gatekeeper\Authorization\Authorization;
use SPHERE\Application\System\Gatekeeper\Consumer\Consumer;
use SPHERE\Application\System\Gatekeeper\Token\Token;

/**
 * Class Gatekeeper
 *
 * @package SPHERE\Application\System\Gatekeeper
 */
class Gatekeeper implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Consumer::registerModule();
        Token::registerModule();
        Account::registerModule();
        Authorization::registerModule();
        Authentication::registerModule();
    }
}
