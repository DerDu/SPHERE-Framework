<?php
namespace MOC\V\Component\Packer\Exception;

use Exception;

/**
 * Class PackerException
 *
 * @package MOC\V\Component\Packer\Exception
 */
class PackerException extends Exception
{

    /**
     * @param string $Message
     * @param int $Code
     * @param null $Previous
     */
    public function __construct($Message = "", $Code = 0, $Previous = null)
    {

        parent::__construct($Message, $Code, $Previous);
    }
}
