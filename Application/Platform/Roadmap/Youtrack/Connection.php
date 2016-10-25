<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;

use SPHERE\System\Extension\Extension;

/**
 * Class Connection
 * @package SPHERE\Application\Platform\Roadmap\Youtrack
 */
class Connection extends Extension
{

    /** @var null|Credentials $Credentials */
    private $Credentials = null;
    /** @var null|string $Cookie */
    private $Cookie = null;
    /** @var null|array $CookieList */
    private $CookieList = null;

    /**
     * Connection constructor.
     * @param Credentials $Credentials
     */
    protected function __construct(Credentials $Credentials)
    {

        $this->Credentials = $Credentials;
    }

    /**
     * @return null|Credentials
     */
    protected function getCredentials()
    {

        return $this->Credentials;
    }

    /**
     * @return null|string
     */
    protected function getCookie()
    {

        return $this->Cookie;
    }

    /**
     * @throws \Exception
     * @return void
     */
    protected function doLogin()
    {

        $CurlHandler = curl_init();
        curl_setopt($CurlHandler, CURLOPT_URL, $this->Credentials->getHost().'/rest/user/login');
        curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($CurlHandler, CURLOPT_POST, true);
        curl_setopt($CurlHandler, CURLOPT_POSTFIELDS,
            'login='.$this->Credentials->getUsername().'&password='.$this->Credentials->getPassword());
        curl_setopt($CurlHandler, CURLOPT_HEADER, false);
        curl_setopt($CurlHandler, CURLOPT_HEADERFUNCTION, array($this, 'parseHeader'));
        curl_setopt($CurlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($CurlHandler, CURLOPT_TIMEOUT, 2);

        $Response = curl_exec($CurlHandler);
        $Response = simplexml_load_string($Response);

        if (false === $Response || $Response != 'ok') {
            throw new \Exception( $Response );
        }

        curl_close($CurlHandler);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param \resource $CurlHandler
     * @param string    $String
     *
     * @return int
     */
    protected function parseHeader(
        /** @noinspection PhpUnusedParameterInspection */
        $CurlHandler,
        $String
    ) {

        $Length = strlen($String);
        if (!strncmp($String, "Set-Cookie:", 11)) {
            $CookieValue = trim(substr($String, 11, -1));
            $this->Cookie = explode("\n", $CookieValue);
            $this->Cookie = explode('=', $this->Cookie[0]);
            $CookieName = trim(array_shift($this->Cookie));
            $this->CookieList[$CookieName] = trim(implode('=', $this->Cookie));
        }
        $this->Cookie = "";
        if (trim($String) == "") {
            foreach ((array)$this->CookieList as $Key => $Value) {
                $this->Cookie .= "$Key=$Value; ";
            }
        }
        return $Length;
    }
}
