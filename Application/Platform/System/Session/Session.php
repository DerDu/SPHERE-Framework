<?php
namespace SPHERE\Application\Platform\System\Session;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Protocol\Service\Entity\TblProtocol;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Session
 *
 * @package SPHERE\Application\Platform\System\Session
 */
class Session extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Aktive Sessions'))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __CLASS__.'::frontendSession'
            )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    /**
     * @return Stage
     */
    public function frontendSession()
    {

        $Stage = new Stage('Aktive Sessions', 'der aktuell angemeldete Benutzer');

        $Result = array();

        $tblSessionAll = Account::useService()->getSessionAll();
        if ($tblSessionAll) {
            array_walk($tblSessionAll, function (TblSession $tblSession) use (&$Result) {

                $tblAccount = $tblSession->getTblAccount();

                if ($tblSession->getEntityUpdate() && $tblSession->getEntityCreate()) {
                    $Interval = $tblSession->getEntityUpdate()->getTimestamp() - $tblSession->getEntityCreate()->getTimestamp();
                } else {
                    if (!$tblSession->getEntityUpdate() && $tblSession->getEntityCreate()) {
                        $Interval = time() - $tblSession->getEntityCreate()->getTimestamp();
                    } else {
                        $Interval = 0;
                    }
                }

                if (( $Activity = Protocol::useService()->getProtocolLastActivity($tblAccount) )) {
                    $Activity = current($Activity)->getEntityCreate();
                } else {
                    $Activity = '-NA-';
                }
                

                array_push($Result, array(
                    'Id'         => $tblSession->getId(),
                    'Consumer'   => ( $tblAccount->getServiceTblConsumer() ?
                        $tblAccount->getServiceTblConsumer()->getAcronym()
                        .'&nbsp;'.new Muted($tblAccount->getServiceTblConsumer()->getName())
                        : '-NA-'
                    ),
                    'Account'    => ( $tblAccount ? $tblAccount->getUsername() : '-NA-' ),
                    'TTL'        => gmdate("H:i:s", $tblSession->getTimeout() - time()),
                    'ActiveTime' => gmdate('H:i:s', $Interval),
                    'LoginTime'  => $tblSession->getEntityCreate(),
                    'LastAction' => $Activity,
                    'Identifier' => strtoupper($tblSession->getSession())
                ));

            });
        }

        $History = array();

        $tblProtocolAll = Protocol::useService()->getProtocolAllCreateSession();
        if ($tblProtocolAll) {
            array_walk($tblProtocolAll, function (TblProtocol $tblProtocol) use (&$History) {

                array_push($History, array(
                    'Consumer'  => $tblProtocol->getConsumerAcronym().'&nbsp;'.new Muted($tblProtocol->getConsumerName()),
                    'LoginTime' => $tblProtocol->getEntityCreate(),
                    'Account'   => $tblProtocol->getAccountUsername(),
                    'AccountId' => ( $tblProtocol->getServiceTblAccount() ? $tblProtocol->getServiceTblAccount()->getId() : '-NA-' )
                ));

            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new TableData($Result, null, array(
                                'Id'         => '#',
                                'Consumer'   => 'Mandant',
                                'Account'    => 'Benutzer',
                                'LoginTime'  => 'Anmeldung',
                                'LastAction' => 'Aktivität',
                                'ActiveTime' => 'Dauer',
                                'TTL'        => 'Timeout',
                                'Identifier' => 'Session'
                            )),
                        ))
                    ), new Title('Aktive Benutzer')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new TableData($History, null, array(
                                'LoginTime' => 'Zeitpunkt',
                                'AccountId' => 'Account',
                                'Account'   => 'Benutzer',
                                'Consumer'  => 'Mandant',
                            ), array(
                                'order'      => array(array(0, 'desc')),
                                'columnDefs' => array(
                                    array('type' => 'de_datetime', 'width' => '10%', 'targets' => 0),
                                    array('width' => '45%', 'targets' => 2),
                                    array('width' => '45%', 'targets' => 3)
                                )
                            )),
                            new Redirect(
                                '/Platform/System/Session', 30
                            )
                        ))
                    ), new Title('Protokoll der Anmeldungen')
                )
            ))
        );

        return $Stage;
    }
}
