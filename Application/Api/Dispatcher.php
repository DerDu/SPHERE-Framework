<?php
namespace SPHERE\Application\Api;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Error;
use SPHERE\System\Extension\Extension;

/**
 * Class Dispatcher
 *
 * @package SPHERE\Application\Api
 */
class Dispatcher extends Extension
{
    /** @var object $ApiClass */
    private $ApiClass = '';
    /** @var array $ApiMethod */
    private $ApiMethod = array();

    /**
     * Dispatcher constructor.
     *
     * @param string $__CLASS__
     * @throws \Exception
     */
    public function __construct($__CLASS__)
    {
        if( class_exists( $__CLASS__, true ) ) {
            $this->ApiClass = new $__CLASS__;
        } else {
            throw new \Exception( 'Missing API-Class ('.$__CLASS__.')' );
        }
    }

    /**
     * @param string $MethodName
     * @return $this
     * @throws \ReflectionException
     */
    public function registerMethod($MethodName)
    {
        $ReflectionClass = new \ReflectionObject($this->ApiClass);
        $ReflectionMethod = $ReflectionClass->getMethod($MethodName);
        $ReflectionParameter = $ReflectionMethod->getParameters();

        $ParameterList = array();
        foreach ($ReflectionParameter as $Parameter) {
            if ($Parameter->isDefaultValueAvailable()) {
                $Value = $Parameter->getDefaultValue();
            } else {
                $Value = null;
            }
            $ParameterList[$Parameter->getName()] = $Value;
        }
        $this->ApiMethod[$MethodName] = $ParameterList;

        return $this;
    }

    /**
     * @param string $MethodName
     * @return string
     * @throws \Exception
     */
    public function callMethod($MethodName)
    {

        if( isset( $this->ApiMethod[$MethodName] ) ) {
            $ApiMethod = $this->ApiMethod[$MethodName];

            $CallParameter = array();
            foreach ($ApiMethod as $Parameter => $Value) {
                if (isset($_REQUEST[$Parameter])) {
                    $CallParameter[$Parameter] = $_REQUEST[$Parameter];
                } else {
                    $CallParameter[$Parameter] = $Value;
                }
            }

            try {
                $Result = call_user_func_array(array($this->ApiClass, $MethodName), $CallParameter);
            } catch ( \Exception $Exception ) {

                $TraceList = '';
                foreach ((array)$Exception->getTrace() as $Trace) {
                    $TraceList .= nl2br('<samp class="text-info small">'
                        .( isset( $Trace['type'] ) && isset( $Trace['function'] ) ? 'Method: '.$Trace['type'].$Trace['function'] : 'Method: ' )
                        .( isset( $Trace['class'] ) ? '<br/>Class: '.$Trace['class'] : '<br/>Class: ' )
                        .( isset( $Trace['file'] ) ? '<br/>File: '.$Trace['file'] : '<br/>File: ' )
                        .( isset( $Trace['line'] ) ? '<br/>Line: '.$Trace['line'] : '<br/>Line: ' )
                        .'</samp><br/>');
                }
                $Result = '<hr/><samp class="text-danger"><div class="h6">'.get_class($Exception).'<br/><br/>'.nl2br($Exception->getMessage()).'</div>File: '.$Exception->getFile().'<br/>Line: '.$Exception->getLine().'</samp><hr/><div class="small">'.$TraceList.'</div>';
            }

        } else {
            $Result = new Error( 'Ajax-Error', 'Missing API-Method ('.$MethodName.')' );
        }

        if( $Result instanceof ITemplateInterface ) {
            $Result = $Result->__toString();
        } else if( is_object($Result) ) {
            $Result = (string)new Error( 'Ajax-Error', 'API-Method ('.$MethodName.') returned incompatiple JSON-Type ('.get_class($Result).')' );
        }
        return json_encode( $Result );
    }
}