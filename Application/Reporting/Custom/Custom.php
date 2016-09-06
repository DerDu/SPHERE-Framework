<?php
namespace SPHERE\Application\Reporting\Custom;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person as ChemnitzPerson;
use SPHERE\Application\Reporting\Custom\Hormersdorf\Person\Person as HormersdorfPerson;
use SPHERE\Application\Reporting\Custom\Herrnhut\Person\Person as HerrnhutPerson;
use SPHERE\Application\Reporting\Custom\Coswig\Person\Person as CoswigPerson;
use SPHERE\Application\Reporting\Custom\Schneeberg\Person\Person as SchneebergPerson;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Reporting\Custom
 */
class Custom implements IApplicationInterface
{

    public static function registerApplication()
    {

        $consumerAcronym = ( Consumer::useService()->getConsumerBySession() ? Consumer::useService()->getConsumerBySession()->getAcronym() : '' );
        // Chemitz
        if ($consumerAcronym === 'ESZC' || $consumerAcronym === 'DEMO') {
            ChemnitzPerson::registerModule();
        }
        // Hormersdorf
        if ($consumerAcronym === 'FEGH' || $consumerAcronym === 'FESH' || $consumerAcronym === 'DEMO') {
            HormersdorfPerson::registerModule();
        }
        // Herrnhut
        if ($consumerAcronym === 'EZGH' || $consumerAcronym === 'DEMO') {
            HerrnhutPerson::registerModule();
        }
        // Coswig
        if ($consumerAcronym === 'EVSC' || $consumerAcronym === 'DEMO') {
            CoswigPerson::registerModule();
        }
        // Schneeberg
        if ($consumerAcronym === 'ESS' || $consumerAcronym === 'DEMO') {
            SchneebergPerson::registerModule();
        }

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Individual'))
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

        $Stage = new Stage('Individual', 'Dashboard');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Auswertung'));

        return $Stage;
    }
}
