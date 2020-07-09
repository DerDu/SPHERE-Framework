<?php
namespace SPHERE\Application\Setting;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Setting\Agb\Agb;
use SPHERE\Application\Setting\Authorization\Authorization;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\Application\Setting\Univention\Univention;
use SPHERE\Application\Setting\User\User;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Setting
 *
 * @package SPHERE\Application\Setting
 */
class Setting implements IClusterInterface
{

    public static function registerCluster()
    {

        MyAccount::registerApplication();
        Consumer::registerApplication();
        Authorization::registerApplication();
        User::registerApplication();
        if(($tblAccount = Account::useService()->getAccountBySession())){
            if(($tblConsumer = $tblAccount->getServiceTblConsumer())){
                if(($tblConsumerLogin = ConsumerGatekeeper::useService()->getConsumerLoginByConsumer($tblConsumer))){
                    if($tblConsumerLogin->getSystemName() == TblConsumerLogin::VALUE_SYSTEM_UCS){
                        // ToDO remove for productive
                        // only SystemAdmins allowed
                        if(($tblIdentification = $tblAccount->getServiceTblIdentification())){
                            if(($tblIdentification->getName() == TblIdentification::NAME_SYSTEM)){
                                Univention::registerApplication();
                            }
                        }
//                        Univention::registerApplication();
                    }
                }
            }
        }
        Agb::registerApplication();

        Main::getDisplay()->addServiceNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Einstellungen'), new Link\Icon(new Cog()))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __CLASS__.'::frontendDashboard')
        );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Einstellungen');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Setting'));

        return $Stage;
    }
}
