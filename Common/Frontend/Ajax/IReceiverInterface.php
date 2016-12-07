<?php
namespace SPHERE\Common\Frontend\Ajax;

/**
 * Interface IReceiverInterface
 *
 * @package SPHERE\Common\Frontend\Ajax
 */
interface IReceiverInterface
{

    /**
     * @return string
     */
    public function getHandler();

    /**
     * @return string
     */
    public function getContainer();

    /**
     * @return string
     */
    public function getSelector();
}