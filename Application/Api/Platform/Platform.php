<?php
namespace SPHERE\Application\Api\Platform;

use SPHERE\Application\Api\Platform\Database\Database;
use SPHERE\Application\Api\Platform\Gatekeeper\ApiUserGroup;
use SPHERE\Application\Api\Platform\Gatekeeper\Gatekeeper;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Platform
 *
 * @package SPHERE\Application\Api\Platform
 */
class Platform implements IApplicationInterface
{

    public static function registerApplication()
    {

        Database::registerModule();
        Gatekeeper::registerModule();
//        ApiUserGroup::registerApi();
    }
}
