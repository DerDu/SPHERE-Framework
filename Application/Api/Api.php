<?php
namespace SPHERE\Application\Api;

use SPHERE\Application\Api\Billing\Billing;
use SPHERE\Application\Api\Corporation\Corporation;
use SPHERE\Application\Api\Document\Document;
use SPHERE\Application\Api\Education\Education;
use SPHERE\Application\Api\Platform\Platform;
use SPHERE\Application\Api\Reporting\Reporting;
use SPHERE\Application\Api\Test\Test;
use SPHERE\Application\IClusterInterface;

/**
 * Class Api
 *
 * @package SPHERE\Application\Api
 */
class Api implements IClusterInterface
{

    public static function registerCluster()
    {

        Billing::registerApplication();
        Test::registerApplication();
        Reporting::registerApplication();
        Platform::registerApplication();
        Education::registerApplication();
        Document::registerApplication();
        Corporation::registerApplication();
    }
}
