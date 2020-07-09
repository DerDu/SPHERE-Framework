<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\System\Extension\Repository\Debugger;

class UniventionSchool
{
    private $curlhandle;
    private $server;
    private $token;

    public function __construct() {

        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_SERVER))){
            $this->server = $tblUnivention->getValue();
        }
        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_TOKEN))){
            $this->token = $tblUnivention->getValue();
        }

        $this->curlhandle = curl_init();
    }

    public function getAllSchools()
    {

        curl_reset($this->curlhandle);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/schools/',
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE
//            CURLOPT_POSTFIELDS => $Content
        ));

        /**
         * possible field's
          -administrative_servers
          -class_share_file_server
          -dc_name
          -dc_name_administrative
          -display_name
          -dn
          -educational_servers
          -home_share_file_server
          -name
          -ucsschool_roles
          -url
         **/

        $Json = $this->execute($this->curlhandle);
        Debugger::screenDump($Json);
        $StdClassArray = json_decode($Json);
        $schoolList = array();
        if($StdClassArray !== null && is_array($StdClassArray) && !empty($StdClassArray)){
            foreach($StdClassArray as $StdClass){
                $schoolList[$StdClass->name] = $StdClass->url;
            }
        }
        return (is_array($schoolList) && !empty($schoolList) ? $schoolList : false);
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