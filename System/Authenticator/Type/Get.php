<?php
namespace SPHERE\System\Authenticator\Type;

use SPHERE\System\Authenticator\ITypeInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Get
 *
 * @package SPHERE\System\Authenticator\Type
 */
class Get extends Extension implements ITypeInterface
{

    /** @var string $Secret */
    private $Secret = '';

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        $this->Secret = $Configuration['Secret'];
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return 'Get';
    }

    /**
     * @return bool|null
     */
    public function validateSignature()
    {

        $Global = $this->getGlobal();

        array_walk_recursive($Global->GET, array($this, 'preventXSS'));
        array_walk_recursive($Global->GET, array($this, 'trimInput'));

        // Respect jQuery no Cache Parameter
        if( isset( $Global->GET['_'] ) ) {
            unset( $Global->GET['_'] );
        }

        if (!empty( $Global->GET ) && !isset( $Global->GET['_Sign'] )) {
            $Global->GET = array();
            $Global->saveGet();
            return null;
        } else {
            if (isset( $Global->GET['_Sign'] )) {
                $Data = $Global->GET;
                $Signature = $Global->GET['_Sign'];
                unset( $Data['_Sign'] );
                $Check = $this->createSignature($Data);
                if ($Check['_Sign'] == $Signature) {
                    unset( $Global->GET['_Sign'] );
                    $Global->saveGet();
                    return true;
                } else {
                    $Global->GET = array();
                    $Global->saveGet();
                    return false;
                }
            } else {
                $Global->GET = array();
                $Global->saveGet();
                return true;
            }
        }
    }

    /**
     * @param array       $Data
     * @param null|string $Location
     *
     * @return array
     */
    public function createSignature($Data, $Location = null)
    {

        if (null === $Location) {
            $Location = $this->getRequest()->getPathInfo();
        }
        $Nonce = date('Ymd');
        array_push($Data, $Location);
        $Data = array_filter($Data, function($Value) {
            return ( $Value !== null && $Value !== false && $Value !== '' );
        });
        $Ordered = $this->sortData((array)$Data);
        $Signature = serialize($Ordered);
        $Signature = hash_hmac('sha256', $Signature, $Nonce.$this->Secret);
        array_pop($Data);
        $Data['_Sign'] = base64_encode($Signature);
        return $Data;
    }

    /**
     * @param $Data
     *
     * @return mixed
     */
    protected function sortData($Data)
    {

        array_walk($Data, function (&$V) {

            if (!is_string($V) && !is_array($V)) {
                $V = (string)$V;
            }
        });
        krsort($Data);
        return $Data;
    }

    /**
     * @param $Value
     */
    protected function trimInput(&$Value)
    {

        $Value = trim($Value);
    }

    /**
     * @param $Value
     */
    protected function preventXSS(&$Value)
    {

        $Value = strip_tags($Value);
    }
}
