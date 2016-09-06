<?php
namespace SPHERE\Application\Document\Explorer;

use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\IApplicationInterface;

/**
 * @deprecated
 * Class Explorer
 *
 * @package SPHERE\Application\Document\Explorer
 */
class Explorer implements IApplicationInterface
{

    /**
     * @deprecated
     */
    public static function registerApplication()
    {

        Storage::registerModule();
    }
}
