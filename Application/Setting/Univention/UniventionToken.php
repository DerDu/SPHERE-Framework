<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;

class UniventionToken
{
    private $server;
    private $username;
    private $password;
    private $curlhandle;

    public function __construct() {

        $this->server = '';
        $this->username = '';
        $this->password = '';
        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_SERVER))){
            $this->server = $tblUnivention->getValue();
        }
        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_USER))){
            $this->username = $tblUnivention->getValue();
        }
        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_PW))){
            $this->password = $tblUnivention->getValue();
        }

        $this->curlhandle = curl_init();
    }

    /**
     * @return bool|string
     */
    public function getVerify()
    {

        curl_reset($this->curlhandle);

        $Content = array(
            'username' => $this->username,
            'password' => $this->password
        );
//        http_build_query($Content);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL            => 'https://'.$this->server.'/token',
//            CURLOPT_USERPWD => $this->username . ':' . $this->password,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => array('Content.Type:application/x-www-form-urlencoded'),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => $Content
        ));


        // false technische schwierigkeiten
        // null Verbindung kann nicht aufgebaut werden
        // StdClass normal response
        $response = $this->execute($this->curlhandle);
        if($response){
            $StdClass = json_decode($response);
            if(is_object($StdClass)
                && !isset($StdClass->detail)
                && $StdClass->access_token){
                return $StdClass->access_token;
            }
            return false;
        } elseif($response === null){
            //ToDO Ausgabe Spezialisieren
            return false;
        }
        return false;
        // close curl resource to free up system resources
//        curl_close($ch);
    }

    private $retriableErrorCodes = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_HTTP_NOT_FOUND,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    ];

    /**
     * Executes a CURL request with optional retries and exception on failure
     *
     * @param  resource    $ch             curl handler
     * @param  int         $retries
     * @param  bool        $closeAfterDone
     * @return bool|string @see curl_exec
     */
    public function execute($ch, $retries = 5, $closeAfterDone = true)
    {
        while ($retries--) {
            $curlResponse = curl_exec($ch);
            if ($curlResponse === false) {
                $curlErrno = curl_errno($ch);
                if (false === in_array($curlErrno, $this->retriableErrorCodes, true) || !$retries) {
                    echo curl_error($ch);
                    if ($closeAfterDone) {
                        curl_close($ch);
                    }
                    return null; //throw new \RuntimeException(sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
                }
                continue;
            }
            if ($closeAfterDone) {
                curl_close($ch);
            }
            return $curlResponse;
        }
        return false;
    }
}