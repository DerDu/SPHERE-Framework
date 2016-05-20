<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.05.2016
 * Time: 08:14
 */

namespace SPHERE\Application\People\Meta\Teacher;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Link\Identifier;

class Teacher implements IModuleInterface
{

    public static function registerModule()
    {
        // Implement registerModule() method.
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}