<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Company
 *
 * @package SPHERE\Application\Corporation\Company
 */
class Company extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        if (0 === strpos(self::getRequest()->getPathInfo(), (new Link\Route(__NAMESPACE__))->getValue())) {
            $Parameter = self::getRequest()->getParameterArray();
            if (isset( $Parameter['Id'] )) {
                $Name = 'Firma bearbeiten';
            } else {
                $Name = 'Firma anlegen';
            }
        } else {
            $Name = 'Firma anlegen';
        }

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name($Name),
                new Link\Icon(new Building())
            )
        );
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendCompany'
        ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy', __NAMESPACE__.'\Frontend::frontendDestroyCompany')
        );

        // Contact: Address
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Address/Create', 'SPHERE\Application\Contact\Address\Frontend::frontendCreateToCompany'
        )
            ->setParameterDefault('Street', null)
            ->setParameterDefault('City', null)
            ->setParameterDefault('State', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Address/Edit', 'SPHERE\Application\Contact\Address\Frontend::frontendUpdateToCompany'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Street', null)
            ->setParameterDefault('City', null)
            ->setParameterDefault('State', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Address/Destroy', 'SPHERE\Application\Contact\Address\Frontend::frontendDestroyToCompany'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Confirm', false)
        );
        // Contact: Mail
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Mail/Create', 'SPHERE\Application\Contact\Mail\Frontend::frontendCreateToCompany'
        )
            ->setParameterDefault('Address', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Mail/Edit', 'SPHERE\Application\Contact\Mail\Frontend::frontendUpdateToCompany'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Address', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Mail/Destroy', 'SPHERE\Application\Contact\Mail\Frontend::frontendDestroyToCompany'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Confirm', false)
        );
        // Contact: Phone
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Phone/Create', 'SPHERE\Application\Contact\Phone\Frontend::frontendCreateToCompany'
        )
            ->setParameterDefault('Number', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Phone/Edit', 'SPHERE\Application\Contact\Phone\Frontend::frontendUpdateToCompany'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Number', null)
            ->setParameterDefault('Type', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Phone/Destroy', 'SPHERE\Application\Contact\Phone\Frontend::frontendDestroyToCompany'
        )
            ->setParameterDefault('Id', null)
            ->setParameterDefault('Confirm', false)
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Contact/Create', __NAMESPACE__.'\Frontend::frontendContact'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Corporation', 'Company', null, null, Consumer::useService()->getConsumerBySession()),
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
