<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:41
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Prepare
 *
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Prepare implements IModuleInterface
{

    public static function registerModule()
    {

        /*
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse vorbereiten'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __NAMESPACE__ . '\Frontend::frontendSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Teacher', __NAMESPACE__ . '\Frontend::frontendTeacherSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Headmaster', __NAMESPACE__ . '\Frontend::frontendHeadmasterSelectDivision')
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare' , __NAMESPACE__ . '\Frontend::frontendPrepare')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Setting' , __NAMESPACE__ . '\Frontend::frontendPrepareSetting')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Preview', __NAMESPACE__ . '\Frontend::frontendPreparePreview')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Prepare\Preview\SubjectGrades', __NAMESPACE__ . '\Frontend::frontendPrepareShowSubjectGrades')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Certificate\Show', __NAMESPACE__ . '\Frontend::frontendShowCertificate')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Signer', __NAMESPACE__ . '\Frontend::frontendSigner')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
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