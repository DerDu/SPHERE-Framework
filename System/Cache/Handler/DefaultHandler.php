<?php
namespace SPHERE\System\Cache\Handler;

/**
 * Class DefaultHandler
 *
 * @package SPHERE\System\Cache\Handler
 */
class DefaultHandler extends MemoryHandler implements HandlerInterface
{

    /**
     * @return string
     */
    public function getSlot()
    {

        if (isset( $_SESSION['Memcached-Slot'] )) {
            return $_SESSION['Memcached-Slot'];
        }
        return 'PUBLIC';
    }

    /**
     * Internal
     *
     * Memcached exists
     *
     * @return bool
     */
    public function isEnabled()
    {

        return false;
    }

    public function clearSlot($Slot)
    {

    }
}
