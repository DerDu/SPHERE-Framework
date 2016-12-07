<?php
namespace MOC\V\Component\Packer\Component\Exception\Repository;

use MOC\V\Component\Packer\Component\Exception\ComponentException;

/**
 * Class TypeFileException
 *
 * @package MOC\V\Component\Packer\Component\Exception
 */
class TypeFileException extends ComponentException
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
