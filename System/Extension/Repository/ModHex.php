<?php
namespace SPHERE\System\Extension\Repository;

/**
 * Class ModHex
 *
 * Encapsulates decoding text with the ModHex encoding from Yubico.
 *
 * @package SPHERE\System\Extension\Repository
 */
class ModHex
{

    /** @var string $Key */
    private static $Key = "cbdefghijklnrtuv";
    /** @var string $String */
    private $String = '';

    /**
     * Use ModHex::withString()
     *
     * @param string $String
     * @throws \Exception
     */
    private function __construct($String)
    {
        if( !function_exists( 'gmp_init' ) ) {
            throw new \Exception('PHP: GMP Extension missing');
        }
        $this->String = $String;
    }

    /**
     * @param $String
     *
     * @return ModHex
     */
    final public static function withString($String)
    {

        return new ModHex($String);
    }

    /**
     * @return string
     */
    final public function getSerialNumber()
    {

        $String = $this->getIdentifier();
        $String = ( ( strlen($String) % 2 ) == 1 ? 'c'.$String : $String );
        $String = base64_encode($this->decodeString($String));
        $String = $this->convertBase64ToHex($String);
        return gmp_strval(gmp_init($String, 16));
    }

    /**
     * @return string
     */
    final public function getIdentifier()
    {

        return substr($this->String, 0, 12);
    }

    /**
     * @param string $String
     *
     * @return bool|string
     */
    final private function decodeString($String)
    {

        $Length = strlen($String);
        $Decoded = "";
        if ($Length % 2 != 0) {
            return false;
        }
        for ($Run = 0; $Run < $Length; $Run = $Run + 2) {
            $High = strpos(self::$Key, $String[$Run]);
            $Low = strpos(self::$Key, $String[$Run + 1]);
            if ($High === false || $Low === false) {
                return false;
            }
            $Decoded .= chr(( $High << 4 ) | $Low);
        }
        return $Decoded;
    }

    /**
     * @param string $String
     *
     * @return string
     */
    final private function convertBase64ToHex($String)
    {

        $Return = '';
        $Convert = base64_decode($String);
        $CharList = str_split($Convert);
        $Length = count($CharList);
        for ($Run = 0; $Run < $Length; $Run++) {
            $Return .= sprintf("%02x", ord($CharList[$Run]));
        }
        return $Return;
    }

}
