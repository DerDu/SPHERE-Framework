<?php
namespace SPHERE\Application\People\Search;

use SPHERE\Application\IApplicationInterface;

/**
 * Class Search
 *
 * @package SPHERE\Application\People\Search
 */
class Search implements IApplicationInterface
{

    public static function registerApplication()
    {

        Group\Group::registerModule();
//        Filter\Filter::registerModule();
    }

}
