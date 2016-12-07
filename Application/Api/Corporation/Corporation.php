<?php
namespace SPHERE\Application\Api\Corporation;

use SPHERE\Application\IApplicationInterface;

/**
 * Class Corporation
 *
 * @package SPHERE\Application\Api\Corporation
 */
class Corporation implements IApplicationInterface
{

    public static function registerApplication()
    {
        ContactPerson::registerApi();
    }
}