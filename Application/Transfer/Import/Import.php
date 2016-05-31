<?php
namespace SPHERE\Application\Transfer\Import;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Transfer\Import\Chemnitz\Chemnitz;
use SPHERE\Application\Transfer\Import\Coswig\Coswig;
use SPHERE\Application\Transfer\Import\FuxMedia\FuxSchool;
use SPHERE\Application\Transfer\Import\Herrnhut\Herrnhut;
use SPHERE\Application\Transfer\Import\Hormersdorf\Hormersdorf;
use SPHERE\Application\Transfer\Import\Indiware\Indiware;
use SPHERE\Application\Transfer\Import\Muldental\Muldental;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Import
 *
 * @package SPHERE\Application\Transfer\Import
 */
class Import implements IApplicationInterface
{

    public static function registerApplication()
    {

        FuxSchool::registerModule();
        Indiware::registerModule();

        $consumerAcronym = ( Consumer::useService()->getConsumerBySession() ? Consumer::useService()->getConsumerBySession()->getAcronym() : '' );
        if ($consumerAcronym == 'ESZC') {
            Chemnitz::registerModule();
        } elseif ($consumerAcronym === 'FEGH' || $consumerAcronym === 'FESH') {
            Hormersdorf::registerModule();
        } elseif ($consumerAcronym === 'EVSC'){
            Coswig::registerModule();
        } elseif ($consumerAcronym === 'EVAMTL'){
            Muldental::registerModule();
        } elseif ($consumerAcronym === 'EZGH'){
            Herrnhut::registerModule();
        }

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'), new Link\Icon(new Upload()))
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

        $Stage = new Stage('Dashboard', 'Import');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Import'));

        return $Stage;
    }
}
