<?php
namespace SPHERE\Common\Design;
use SPHERE\Common\Style;

/**
 * Interface IDesignInterface
 * @package SPHERE\Common\Design
 */
interface IDesignInterface
{
    /**
     * Design constructor.
     *
     * @param Style $Style
     */
    public function __construct( Style $Style );


}