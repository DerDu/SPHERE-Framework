<?php
namespace SPHERE\Common;

use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use SPHERE\Application\Api\Api;
use SPHERE\Application\Billing\Billing;
use SPHERE\Application\Contact\Contact;
use SPHERE\Application\Corporation\Corporation;
use SPHERE\Application\Dispatcher;
use SPHERE\Application\Document\DataProtectionOrdinance;
use SPHERE\Application\Document\Document;
use SPHERE\Application\Document\LegalNotice;
use SPHERE\Application\Document\License;
use SPHERE\Application\Education\Education;
use SPHERE\Application\Manual\Manual;
use SPHERE\Application\People\People;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Platform;
use SPHERE\Application\Platform\System;
use SPHERE\Application\Reporting\Reporting;
use SPHERE\Application\Setting\Setting;
use SPHERE\Application\Transfer\Transfer;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Display;
use SPHERE\Common\Window\Error;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Authenticator\Type\Post;
use SPHERE\System\Authenticator\Type\Request;
use SPHERE\System\Cache\Handler\APCuHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Cache\Handler\OpCacheHandler;
use SPHERE\System\Cache\Handler\SmartyHandler;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\FileLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Main
 *
 * @package SPHERE\Common
 */
class Main extends Extension
{

    /** @var bool $EnableErrorHandler */
    public static $EnableErrorHandler = true;
    /** @var Display $Display */
    private static $Display = null;
    /** @var Dispatcher $Dispatcher */
    private static $Dispatcher = null;

    /**
     *
     */
    public function __construct()
    {

        self::initCloudCache();

        if (self::getDisplay() === null) {
            self::$Display = new Display();
        }
        if (self::getDispatcher() === null) {
            self::$Dispatcher = new Dispatcher(new Router());
        }
    }

    /**
     * @return string
     */
    public static function initCloudCache()
    {

        if (!isset($_SESSION['Memcached-Slot'])) {
            try {
                if (Consumer::useService()->getConsumerBySession()) {
                    $_SESSION['Memcached-Slot'] = Consumer::useService()->getConsumerBySession()->getAcronym();
                } else {
                    $_SESSION['Memcached-Slot'] = 'PUBLIC';
                }
            } catch (\Exception $Exception) {
                $_SESSION['Memcached-Slot'] = 'PUBLIC';
            }
        }
        return $_SESSION['Memcached-Slot'];
    }

    /**
     * @return Display
     */
    public static function getDisplay()
    {

        return self::$Display;
    }

    /**
     * @return Dispatcher
     */
    public static function getDispatcher()
    {

        return self::$Dispatcher;
    }

    public function runPlatform()
    {

        /**
         * REST-API
         */
        if (preg_match('!^/Api/!is', $this->getRequest()->getPathInfo())) {

            try {
                $this->getDebugger();
                $this->setErrorHandler();
                $this->setShutdownHandler();

                /**
                 * Register Cluster
                 */
                self::registerApiPlatform();

                if ($this->runAuthenticator()) {
                    if (Access::useService()->existsRightByName($this->getRequest()->getPathInfo())) {
                        if (!Access::useService()->hasAuthorization($this->getRequest()->getPathInfo())) {
                            header('HTTP/1.0 403 Forbidden: '.$this->getRequest()->getPathInfo());
                        } else {
                            echo self::getDispatcher()->fetchRoute(
                                $this->getRequest()->getPathInfo()
                            );
                        }
                    } else {
                        header('HTTP/1.0 511 Network Authentication Required: '.$this->getRequest()->getPathInfo());
                        self::getDisplay()->setContent(
                            self::getDispatcher()->fetchRoute(
                                $this->getRequest()->getPathInfo()
                            )
                        );
                        echo self::getDisplay()->getContent();
                    }
                } else {
                    header('HTTP/1.0 400 Bad Request: '.$this->getRequest()->getUrl());
                }
                exit(0);
            } catch (\Exception $Exception) {
                $this->runSelfHeal($Exception);
            }
        }
        /**
         * APPLICATION
         */
        try {
            $this->getDebugger();
            $this->setErrorHandler();
            $this->setShutdownHandler();

            /**
             * Register Cluster
             */
            self::registerApiPlatform();
            self::registerGuiPlatform();

            /**
             * Execute Request
             */
            if ($this->runAuthenticator()) {
                self::getDisplay()->setContent(
                    self::getDispatcher()->fetchRoute(
                        $this->getRequest()->getPathInfo()
                    )
                );
            }
        } catch (PDOException $Exception) {
            $this->runSelfHeal($Exception);
        } catch (InvalidFieldNameException $Exception) {
            $this->getCache(new APCuHandler())->clearCache();
            $this->getCache(new MemcachedHandler())->clearCache();
            $this->getCache(new MemoryHandler())->clearCache();
            $this->getCache(new OpCacheHandler())->clearCache();
            $this->getCache(new TwigHandler())->clearCache();
            $this->getCache(new SmartyHandler())->clearCache();
            $this->runSelfHeal($Exception);
        } catch (TableNotFoundException $Exception) {
            $this->runSelfHeal($Exception);
        } catch (\PDOException $Exception) {
            $this->runSelfHeal($Exception);
        } catch (\ErrorException $Exception) {
            self::getDisplay()->setException($Exception, 'Error');
        } catch (\Exception $Exception) {
            self::getDisplay()->setException($Exception, get_class($Exception));
        }

        try {
            echo self::getDisplay()->getContent();
            exit(0);
        } catch (\Exception $Exception) {
            $this->runSelfHeal($Exception);
        }
    }

    /**
     *
     */
    private function setErrorHandler()
    {

        if (self::$EnableErrorHandler) {
            set_error_handler(
                function ($Code, $Message, $File, $Line) {

                    if (!preg_match('!apc_store.*?was.*?on.*?gc-list.*?for!is', $Message)) {
                        throw new \ErrorException($Message, 0, $Code, $File, $Line);
                    }
                }, E_ALL
            );
        }
    }

    /**
     *
     */
    private function setShutdownHandler()
    {

        if (self::$EnableErrorHandler) {
            register_shutdown_function(
                function () {

                    $Error = error_get_last();
                    if (!$Error) {
                        return;
                    }
                    if (preg_match('!apc_store.*?was.*?on.*?gc-list.*?for!is', $Error['message'])) {
                        return;
                    }
                    $Display = new Display();
                    $Display->addServiceNavigation(
                        new Link(new Link\Route('/'), new Link\Name('Zurück zur Anwendung'))
                    );
                    $Display->setException(
                        new \ErrorException($Error['message'], 0, $Error['type'], $Error['file'], $Error['line']),
                        'Shutdown'
                    );
                    echo $Display->getContent(true);
                }
            );
        }
    }

    public static function registerApiPlatform()
    {

        Platform::registerCluster();
        Api::registerCluster();
    }

    /**
     * @return bool
     */
    private function runAuthenticator()
    {

        $Get = (new Authenticator(new Get()))->getAuthenticator();
        $Post = (new Authenticator(new Post()))->getAuthenticator();
        $Request = (new Authenticator(new Request()))->getAuthenticator();
        if (!($Get->validateSignature() && $Post->validateSignature() && $Request->validateSignature())) {
            self::getDisplay()->setClusterNavigation();
            self::getDisplay()->setApplicationNavigation();
            self::getDisplay()->setModuleNavigation();
            self::getDisplay()->setServiceNavigation(new Link(
                new Link\Route('/'),
                new Link\Name('Zurück zur Anwendung')
            ));
            self::getDisplay()->setContent(Dispatcher::fetchRoute('System/Assistance/Error/Authenticator'));
            return false;
        }
        return true;
    }

    /**
     * @param \Exception $Exception
     */
    public static function runSelfHeal(\Exception $Exception = null)
    {

//        Fehlerfunde nicht der gesuchte Fehler
//        (new DebuggerFactory())->createLogger(new FileLogger())->addLog('runSelfHeal Error: '.$Exception->getMessage());

        $Protocol = (new System\Database\Database())->frontendSetup(false, true);

        $Display = new Display();
        $Stage = new Stage(new Danger(new Hospital()) . ' Automatische Reparatur', 'Datenintegrität');
        $Stage->setMessage('Diese automatische Fehlerbehebung wird immer dann ausgeführt wenn die Integrität der gespeicherten Daten gefährdet sein könnte');
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Panel('Was ist das?', array(
                                (new Frontend\Message\Repository\Info(new Shield() . ' Es wird eine automatische Reparatur der Datenbank und eine Überprüfung der Daten durchgeführt')),
                                (new Frontend\Message\Repository\Success(new Success() . ' Sollte die gleiche Fehlermeldung nach einem erneuten Seitenaufruf nicht wieder auftauchen ist das Problem behoben worden')),
                                (new Frontend\Message\Repository\Danger(new HazardSign() . ' Sollte der Fehler damit nicht behoben werden, senden Sie bitte den angezeigten Fehlerbericht')),
                                (new Frontend\Message\Repository\Warning(new Info() . ' Bitte senden Sie den Bericht nur, wenn der ' . new Bold('gleiche') . ' Fehler mehrfach auftritt. Sollte ' . new Bold('kein') . ' Bericht verfügbar sein, wenden Sie sich bitte direkt an den Support.')),
                            ), Panel::PANEL_TYPE_PRIMARY,
                                new PullRight(strip_tags((new Redirect(self::getRequest()->getPathInfo(), 110)),
                                    '<div><a><script><span>'))
                            ),
                            ($Exception
                                ? new Panel('', array(
                                    new Error('Datenintegritätsprüfung', $Exception->getMessage()),
                                    $Protocol
                                ))
                                : ''
                            )
                        ))
                    )
                )
            )
        );

        $Display->setContent($Stage);

        echo $Display->getContent(true);
        exit(0);
    }

    public static function registerGuiPlatform()
    {

        People::registerCluster();
        Corporation::registerCluster();
        Education::registerCluster();
        Billing::registerCluster();
        Transfer::registerCluster();
        Contact::registerCluster();
        Setting::registerCluster();
        Manual::registerCluster();
        Reporting::registerCluster();
        Document::registerCluster();
        License::registerCluster();
        LegalNotice::registerCluster();
        DataProtectionOrdinance::registerCluster();
    }
}
