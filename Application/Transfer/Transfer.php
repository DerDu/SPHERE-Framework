<?php
namespace SPHERE\Application\Transfer;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Transfer\Export\Export;
use SPHERE\Application\Transfer\Import\Import;
use SPHERE\Common\Frontend\Icon\Repository\Transfer as TransferIcon;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Transfer
 *
 * @package SPHERE\Application\Transfer
 */
class Transfer implements IClusterInterface
{

    public static function registerCluster()
    {

        Import::registerApplication();
        Export::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Datentransfer'), new Link\Icon(new TransferIcon()))
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

        $Stage = new Stage('Dashboard', 'Datentransfer');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Transfer'));

        return $Stage;
    }
}
