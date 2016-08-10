<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.07.2016
 * Time: 09:05
 */

namespace SPHERE\Application\Education\ClassRegister\Absence;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Absence
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence
 */
class Absence implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendAbsence')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '/Edit' , __NAMESPACE__ . '\Frontend::frontendEditAbsence')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '/Destroy' , __NAMESPACE__ . '\Frontend::frontendDestroyAbsence')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '/Month' , __NAMESPACE__ . '\Frontend::frontendAbsenceMonth')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Education', 'ClassRegister', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
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