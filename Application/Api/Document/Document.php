<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:09
 */

namespace SPHERE\Application\Api\Document;

use SPHERE\Application\Api\Document\Custom\Custom;
use SPHERE\Application\Api\Document\Standard\Standard;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Document
 *
 * @package SPHERE\Application\Api\Document
 */
class Document implements IApplicationInterface
{

    public static function registerApplication()
    {

        Standard::registerModule();
        Custom::registerModule();
    }
}