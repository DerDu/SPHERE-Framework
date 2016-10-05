<?php
namespace SPHERE\Application\Education\Certificate;

use SPHERE\Application\Education\Certificate\Approve\Approve;
use SPHERE\Application\Education\Certificate\GradeInformation\GradeInformation;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\PrintCertificate\PrintCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Certificate
 *
 * @package SPHERE\Application\Education\Certificate
 */
class Certificate implements IApplicationInterface
{

    public static function registerApplication()
    {

        Setting::registerModule();
        Prepare::registerModule();
        Approve::registerModule();
        PrintCertificate::registerModule();
        GradeInformation::registerModule();

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
