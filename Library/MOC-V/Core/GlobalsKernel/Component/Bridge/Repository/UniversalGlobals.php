<?php
namespace MOC\V\Core\GlobalsKernel\Component\Bridge\Repository;

use MOC\V\Core\GlobalsKernel\Component\Bridge\Bridge;
use MOC\V\Core\GlobalsKernel\Component\IBridgeInterface;
use MOC\V\Core\GlobalsKernel\Vendor\Universal\Globals;

/**
 * Class UniversalGlobals
 *
 * @package MOC\V\Core\GlobalsKernel\Component\Bridge
 */
class UniversalGlobals extends Bridge implements IBridgeInterface
{

    /** @var Globals $Instance */
    private static $Instance = null;

    /**
     *
     */
    public function __construct()
    {

        if (null === self::$Instance) {
            if (!isset($_GET)) {
                $_GET = array();
            }
            if (!isset($_POST)) {
                $_POST = array();
            }
            if (!isset($_SESSION)) {
                $_SESSION = array();
            }
            if (!isset($_SERVER)) {
                $_SERVER = array();
            }

            self::$Instance = new Globals($_GET, $_POST, $_SESSION, $_SERVER);
        }
    }

    /**
     * @return array
     */
    public function getGET()
    {

        return self::$Instance->getGET();
    }

    /**
     * @param array $GET
     */
    public function setGET($GET)
    {

        self::$Instance->setGET($GET);
    }

    /**
     * @return array
     */
    public function getPOST()
    {

        return self::$Instance->getPOST();
    }

    /**
     * @param array $POST
     */
    public function setPOST($POST)
    {

        self::$Instance->setPOST($POST);
    }

    /**
     * @return array
     */
    public function getSESSION()
    {

        return self::$Instance->getSESSION();
    }

    /**
     * @param array $SESSION
     */
    public function setSESSION($SESSION)
    {

        self::$Instance->setSESSION($SESSION);
    }

    /**
     * @return array
     */
    public function getSERVER()
    {

        return self::$Instance->getSERVER();
    }

    /**
     * @param array $SERVER
     */
    public function setSERVER($SERVER)
    {

        self::$Instance->setSERVER($SERVER);
    }
}
