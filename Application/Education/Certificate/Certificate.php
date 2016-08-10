<?php
namespace SPHERE\Application\Education\Certificate;

use SPHERE\Application\Education\Certificate\Approve\Approve;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\PrintCertificate\PrintCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class Certificate implements IApplicationInterface
{

    public static function registerApplication()
    {

        Generator::registerModule();
        Setting::registerModule();
        Prepare::registerModule();
        Approve::registerModule();
        PrintCertificate::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Zeugnisse');

        return $Stage;
    }
}
