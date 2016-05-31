<?php
namespace SPHERE\Application\Education\Graduation\Evaluation;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Evaluation
 *
 * @package SPHERE\Application\Education\Graduation\Evaluation
 */
class Evaluation implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Test'),
                new Link\Name('Leistungsüberprüfung'),
                new Link\Icon(new Document()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Task'),
                new Link\Name('Notenaufträge'),
                new Link\Icon(new ClipBoard()))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test',
                __NAMESPACE__.'\Frontend::frontendTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task',
                __NAMESPACE__.'\Frontend::frontendTask')
        );

        /**
         * Teacher
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Teacher',
                __NAMESPACE__.'\Frontend::frontendTestTeacher')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Teacher\Selected',
                __NAMESPACE__.'\Frontend::frontendTestSelected')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Teacher\Edit',
                __NAMESPACE__.'\Frontend::frontendEditTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Teacher\Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Teacher\Grade\Edit',
                __NAMESPACE__.'\Frontend::frontendEditTestGrade')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task\Teacher',
                __NAMESPACE__.'\Frontend::frontendDivisionTeacherTask')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task\Teacher\Grades',
                __NAMESPACE__.'\Frontend::frontendDivisionTeacherTaskGrades')
        );

        /*
         * Headmaster
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Headmaster',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Headmaster\Selected',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTestSelected')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Headmaster\Edit',
                __NAMESPACE__.'\Frontend::frontendHeadmasterEditTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Headmaster\Destroy',
                __NAMESPACE__.'\Frontend::frontendHeadmasterDestroyTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Headmaster\Grade\Edit',
                __NAMESPACE__.'\Frontend::frontendHeadmasterEditTestGrade')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task\Headmaster',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTask')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task\Headmaster\Edit',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTaskEdit')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task\Headmaster\Destroy',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTaskDestroy')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task\Headmaster\Division',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTaskDivision')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task\Headmaster\Grades',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTaskGrades')
        );

    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Education', 'Graduation', 'Evaluation', null,
            Consumer::useService()->getConsumerBySession()),
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
