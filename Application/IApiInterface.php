<?php
namespace SPHERE\Application;
/**
 * Interface IApiInterface
 *
 * @package SPHERE\Application
 */
interface IApiInterface
{

    public static function registerApi();

    /**
     * @param string $MethodName Callable Method
     * @return string
     */
    public function ApiDispatcher($MethodName = '');
}