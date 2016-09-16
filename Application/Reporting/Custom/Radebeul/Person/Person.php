<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.09.2016
 * Time: 16:04
 */

namespace SPHERE\Application\Reporting\Custom\Radebeul\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 *
 * @package SPHERE\Application\Reporting\Custom\Radebeul\Person
 */
class Person extends AbstractModule implements IModuleInterface
{

    public static function registerModule()
    {

        /*
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/ParentTeacherConferenceList'), new Link\Name('Anwesenheitsliste für Elternabende'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/DenominationList'), new Link\Name('Religionszugehörigkeit'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/PhoneList'), new Link\Name('Telefonliste'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/KindergartenList'), new Link\Name('Kinderhausliste'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/RegularSchoolList'), new Link\Name('Stammschulenliste'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/DiseaseList'), new Link\Name('Allergieliste'))
        );

        /*
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendPerson'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/ParentTeacherConferenceList',
            __NAMESPACE__.'\Frontend::frontendParentTeacherConferenceList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DenominationList',
            __NAMESPACE__.'\Frontend::frontendDenominationList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/PhoneList',
            __NAMESPACE__.'\Frontend::frontendPhoneList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/KindergartenList',
            __NAMESPACE__.'\Frontend::frontendKindergartenList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/RegularSchoolList',
            __NAMESPACE__.'\Frontend::frontendRegularSchoolList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DiseaseList',
            __NAMESPACE__.'\Frontend::frontendDiseaseList'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}