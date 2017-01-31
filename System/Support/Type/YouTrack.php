<?php
namespace SPHERE\System\Support\Type;

use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Support\ITypeInterface;

/**
 * Class YouTrack
 *
 * @package SPHERE\System\Support\Type
 */
class YouTrack extends Extension implements ITypeInterface
{

    /** @var null|string $Host */
    private $Host = null;
    /** @var null|string $Username */
    private $Username = null;
    /** @var null|string $Password */
    private $Password = null;

    /** @var null|string $Cookie */
    private $Cookie = null;
    /** @var null|array $CookieList */
    private $CookieList = null;

    /**
     * @return null|string
     */
    public function getHost()
    {

        return $this->Host;
    }

    /**
     * @return null|string
     */
    public function getUsername()
    {

        return $this->Username;
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {

        return $this->Password;
    }

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        if (isset( $Configuration['Host'] )) {
            $this->Host = $Configuration['Host'];
        }
        if (isset( $Configuration['Username'] )) {
            $this->Username = $Configuration['Username'];
        }
        if (isset( $Configuration['Password'] )) {
            $this->Password = $Configuration['Password'];
        }

    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return 'YouTrack';
    }

    /**
     * @return FormGroup
     */
    public function ticketCurrent()
    {

        $Issues = $this->ticketList();

        foreach ((array)$Issues as $Index => $Content) {
            if (!isset( $Content[1] )) {
                $Content[1] = '';
            }
            if (!isset( $Content[1] )) {
                $Content[2] = '';
            }
            switch (strtoupper($Content[2])) {
                case 'ERFASST': {
                    $Label = 'label-primary';
                    break;
                }
                case 'ZU BESPRECHEN': {
                    $Label = 'label-warning';
                    break;
                }
                case 'OFFEN': {
                    $Label = 'label-danger';
                    break;
                }
                case 'IN BEARBEITUNG': {
                    $Label = 'label-success';
                    break;
                }
                default:
                    $Label = 'label-default';

            }

            $Issues[$Index] = new Info(
                '<strong>'.$Content[0].'</strong>'
                .'<div class="pull-right label '.$Label.'"><samp>'.$Content[2].'</samp></div>'
                .'<hr/><small>'.nl2br($Content[1]).'</small>'
            );
        }
        if (empty( $Issues )) {
            $Issues[0] = new Info('Keine Supportanfragen vorhanden');
        }
        krsort($Issues);
        return new FormGroup(
            new FormRow(
                new FormColumn(
                    $Issues
                )
            ), new Title('Tickets', 'Aktuelle Anfragen')
        );
    }

    /**
     * @return array
     */
    public function ticketList()
    {

        $this->ticketLogin();
        $CurlHandler = curl_init();
        curl_setopt($CurlHandler, CURLOPT_URL,
            $this->Host.'/rest/issue/byproject/SSW?filter='.urlencode('Status: -Gelöst Ersteller: KREDA-Support Ersteller: SWE-Kreda')
        );
        curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($CurlHandler, CURLOPT_HEADER, false);
        curl_setopt($CurlHandler, CURLOPT_VERBOSE, false);
        curl_setopt($CurlHandler, CURLOPT_COOKIE, $this->Cookie);
        curl_setopt($CurlHandler, CURLOPT_RETURNTRANSFER, 1);

        $Response = curl_exec($CurlHandler);
        curl_close($CurlHandler);

        $Response = simplexml_load_string($Response);

        $Summary = $Response->xpath('//issues/issue/field[@name="summary"]');
        $Description = $Response->xpath('//issues/issue/field[@name="description"]');
        $Status = $Response->xpath('//issues/issue/field[@name="State"]');

        $Issues = array();
        /**
         * [0] - Title
         */
        $Run = 0;
        foreach ($Summary as $Title) {
            foreach ($Title->children() as $Value) {
                $Issues[$Run] = array((string)$Value);
            }
            $Run++;
        }
        /**
         * [1] - Description
         */
        $Run = 0;
        foreach ($Description as $Message) {
            foreach ($Message->children() as $Value) {
                array_push($Issues[$Run], (string)$Value);
            }
            $Run++;
        }
        /**
         * [2] - Status
         */
        $Run = 0;
        foreach ($Status as $Message) {
            foreach ($Message->children() as $Value) {
                array_push($Issues[$Run], (string)$Value);
            }
            $Run++;
        }
        return $Issues;
    }

    /**
     * @throws \Exception
     * @return null
     */
    private function ticketLogin()
    {

        $CurlHandler = curl_init();
        curl_setopt($CurlHandler, CURLOPT_URL, $this->Host.'/rest/user/login');
        curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($CurlHandler, CURLOPT_POST, true);
        curl_setopt($CurlHandler, CURLOPT_POSTFIELDS,
            'login='.$this->Username.'&password='.$this->Password);
        curl_setopt($CurlHandler, CURLOPT_HEADER, false);
        curl_setopt($CurlHandler, CURLOPT_HEADERFUNCTION, array($this, 'ticketHeader'));
        curl_setopt($CurlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($CurlHandler, CURLOPT_TIMEOUT, 2);

        $Response = curl_exec($CurlHandler);
        $Response = simplexml_load_string($Response);

        if (false === $Response || $Response != 'ok') {
            throw new \Exception();
        }

        curl_close($CurlHandler);
    }

    /**
     * @param string $Summary
     * @param string $Description
     *
     * @throws \Exception
     * @return array
     */
    public function createTicket($Summary, $Description)
    {

        $Markdown = $this->getMarkdownify();
        $Markdown->setKeepHTML(false);
        $Summary = $Markdown->parseString($Summary);
        $Description = $Markdown->parseString($Description);

        $this->ticketLogin();
        $CurlHandler = curl_init();
        curl_setopt($CurlHandler, CURLOPT_URL,
            $this->Host.'/rest/issue?project=SSW&summary='.urlencode($Summary).'&description='.urlencode($Description)
        );
        curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($CurlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($CurlHandler, CURLOPT_HEADER, false);
        curl_setopt($CurlHandler, CURLOPT_VERBOSE, false);
        curl_setopt($CurlHandler, CURLOPT_PUT, true);
        curl_setopt($CurlHandler, CURLOPT_COOKIE, $this->Cookie);
        curl_setopt($CurlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($CurlHandler, CURLOPT_TIMEOUT, 2);

        $Response = curl_exec($CurlHandler);

        if (false === $Response) {
            throw new \Exception();
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
    private function ticketHeader(
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
