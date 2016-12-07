<?php
namespace MOC\V\Component\Packer\Exception;

/**
 * Class PackerTypeException
 *
 * @package MOC\V\Component\Packer\Exception
 */
class PackerTypeException extends PackerException
{

    /**
     * @param string $Message
     * @param int $Code
     * @param null $Previous
     */
    public function __construct($Message = "", $Code = 0, $Previous = null)
    {

        $Message = 'Packer type ' . $Message . ' not supported!';

        parent::__construct($Message, $Code, $Previous);
    }
}
