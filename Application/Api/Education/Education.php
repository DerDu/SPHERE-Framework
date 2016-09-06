<?php
namespace SPHERE\Application\Api\Education;

use SPHERE\Application\Api\Education\Certificate\Certificate;
use SPHERE\Application\Api\Education\ClassRegister\ClassRegister;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Education
 *
 * @package SPHERE\Application\Api\Education
 */
class Education implements IApplicationInterface
{

    public static function registerApplication()
    {

        Certificate::registerModule();
        ClassRegister::registerModule();
    }
}
