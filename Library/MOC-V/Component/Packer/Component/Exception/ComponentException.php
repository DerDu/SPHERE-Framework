<?php
namespace MOC\V\Component\Packer\Component\Exception;

use MOC\V\Component\Packer\Exception\PackerException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Component\Packer\Component\Exception
 */
class ComponentException extends PackerException
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
