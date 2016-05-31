<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\Common\Cache\Cache;

class ApcCache extends \Doctrine\Common\Cache\ApcCache
{

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {

        if (function_exists('apc_fetch')) {
            return apc_fetch($id);
        }
        if (function_exists('apcu_fetch')) {
            return apcu_fetch($id);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {

        if (function_exists('apc_exists')) {
            return apc_exists($id);
        }
        if (function_exists('apcu_exists')) {
            return apcu_exists($id);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {

        if (function_exists('apc_store')) {
            return (bool)apc_store($id, $data, (int)$lifeTime);
        }
        if (function_exists('apcu_store')) {
            return (bool)apcu_store($id, $data, (int)$lifeTime);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {

        if (function_exists('apc_delete')) {
            return apc_delete($id);
        }
        if (function_exists('apcu_delete')) {
            return apcu_delete($id);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {

        if (function_exists('apc_clear_cache')) {
            return apc_clear_cache() && apc_clear_cache('user');
        }
        if (function_exists('apcu_clear_cache')) {
            return apcu_clear_cache();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetchMultiple(array $keys)
    {

        if (function_exists('apc_fetch')) {
            return apc_fetch($keys);
        }
        if (function_exists('apcu_fetch')) {
            return apcu_fetch($keys);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {

        if (function_exists('apc_cache_info')) {
            $info = apc_cache_info('', true);
        }
        if (function_exists('apcu_cache_info')) {
            $info = apcu_cache_info(true);
        }
        if (function_exists('apc_sma_info')) {
            $sma = apc_sma_info();
        }
        if (function_exists('apcu_sma_info')) {
            $sma = apcu_sma_info();
        }

        // @TODO - Temporary fix @see https://github.com/krakjoe/apcu/pull/42
        if (PHP_VERSION_ID >= 50500) {
            $info['num_hits'] = isset( $info['num_hits'] ) ? $info['num_hits'] : $info['nhits'];
            $info['num_misses'] = isset( $info['num_misses'] ) ? $info['num_misses'] : $info['nmisses'];
            $info['start_time'] = isset( $info['start_time'] ) ? $info['start_time'] : $info['stime'];
        }

        return array(
            Cache::STATS_HITS             => $info['num_hits'],
            Cache::STATS_MISSES           => $info['num_misses'],
            Cache::STATS_UPTIME           => $info['start_time'],
            Cache::STATS_MEMORY_USAGE     => $info['mem_size'],
            Cache::STATS_MEMORY_AVAILABLE => $sma['avail_mem'],
        );
    }
}
