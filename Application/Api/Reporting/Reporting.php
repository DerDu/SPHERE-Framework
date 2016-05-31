<?php
namespace SPHERE\Application\Api\Reporting;

use SPHERE\Application\Api\Reporting\CheckList\CheckList;
use SPHERE\Application\Api\Reporting\Custom\Custom;
use SPHERE\Application\Api\Reporting\SerialLetter\SerialLetter;
use SPHERE\Application\Api\Reporting\Standard\Standard;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Api\Reporting
 */
class Reporting implements IApplicationInterface
{

    public static function registerApplication()
    {

        Custom::registerModule();
        Standard::registerModule();
        CheckList::registerModule();
        SerialLetter::registerModule();
    }
}
