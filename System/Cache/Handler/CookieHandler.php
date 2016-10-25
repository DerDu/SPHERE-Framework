<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheStatus;

/**
 * Class CookieHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class CookieHandler extends AbstractFastCacheAdapter
{

    /**
     * CookieHandler constructor.
     */
    public function __construct()
    {

        $this->setCacheType(self::CACHE_TYPE_COOKIE);
    }

    /**
     * @param string $Key
     * @param mixed  $Value
     * @param int    $Timeout
     * @param string $Region
     *
     * @return HandlerInterface
     */
    public function setValue($Key, $Value, $Timeout = 0, $Region = 'Default')
    {

        if ($Value && function_exists('gzdeflate')) {
            $Value = gzdeflate(serialize($Value), 9);
        }
        return parent::setValue($Key, $Value, $Timeout, $Region);
    }

    /**
     * @param string $Key
     * @param string $Region
     *
     * @return null|mixed
     */
    public function getValue($Key, $Region = 'Default')
    {

        $Value = parent::getValue($Key, $Region);
        if ($Value && function_exists('gzinflate')) {
            $Value = unserialize(gzinflate($Value));
        }
        return $Value;
    }


    /**
     * @return CacheStatus
     */
    public function getStatus()
    {

        $Status = $this->getCacheStatus();
        if ($Status) {
            $Status = $this->grepStatusKey('!^phpFastCache_!is', $Status['data']);

            array_walk($Status, function (&$Item, $Index) {

                if (preg_match('!'.preg_quote('s:4:"size";i:', '!').'[0-9]+;!is', $Item)) {
                    $Item = strlen($Item) + strlen($Index);
                } else {
                    $Item = false;
                }
            });
            $Status = array_values(array_filter($Status));
            $Size = array_sum($Status);
            $Wasted = $this->grepCookieSize();
            $Available = 4000;

            return new CacheStatus(0, 0,
                $Available,
                $Size,
                $Available - $Size - $Wasted,
                $Wasted
            );
        }
        return new CacheStatus();
    }

    /**
     * @param string $Pattern
     * @param array  $Status
     * @param int    $Flags
     *
     * @return array
     */
    private function grepStatusKey($Pattern, $Status, $Flags = 0)
    {

        return array_intersect_key($Status, array_flip(preg_grep($Pattern, array_keys($Status), $Flags)));
    }

    /**
     * @return int
     */
    private function grepCookieSize()
    {

        $RawCookies = isset( $_SERVER['HTTP_COOKIE'] ) ? $_SERVER['HTTP_COOKIE'] : null;
        $RawCookies = preg_replace('!phpFastCache_.*?(;|$)!', '', $RawCookies);
        return ( strlen($RawCookies) * 8 );
    }
}
