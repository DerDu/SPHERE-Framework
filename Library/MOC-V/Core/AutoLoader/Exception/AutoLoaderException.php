<?php
namespace MOC\V\Core\AutoLoader\Exception;

use Exception;

/**
 * Class AutoLoaderException
 *
 * @package MOC\V\Core\AutoLoader\Exception
 */
class AutoLoaderException extends Exception
{

    /**
     * AutoLoaderException constructor.
     *
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct($Message = "", $Code = 0, $Previous = null)
    {

        parent::__construct($Message, $Code, $Previous);
    }
}
