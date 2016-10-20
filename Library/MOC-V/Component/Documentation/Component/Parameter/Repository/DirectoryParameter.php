<?php
namespace MOC\V\Component\Documentation\Component\Parameter\Repository;

use MOC\V\Component\Documentation\Component\Exception\Repository\EmptyDirectoryException;
use MOC\V\Component\Documentation\Component\Exception\Repository\TypeDirectoryException;
use MOC\V\Component\Documentation\Component\IParameterInterface;
use MOC\V\Component\Documentation\Component\Parameter\Parameter;

/**
 * Class DirectoryParameter
 *
 * @package MOC\V\Component\Documentation\Component\Parameter\Repository
 */
class DirectoryParameter extends Parameter implements IParameterInterface
{

    /** @var string $Directory */
    private $Directory = null;

    /**
     * @param string $Directory
     */
    public function __construct($Directory)
    {

        $this->setDirectory($Directory);
    }

    /**
     * @return string
     */
    public function getDirectory()
    {

        return $this->Directory;
    }

    /**
     * @param string $Directory
     *
     * @throws \MOC\V\Component\Documentation\Component\Exception\Repository\EmptyDirectoryException
     * @throws \MOC\V\Component\Documentation\Component\Exception\Repository\TypeDirectoryException
     */
    public function setDirectory($Directory)
    {

        if (empty( $Directory )) {
            throw new EmptyDirectoryException();
        } else {
            $Directory = realpath( $Directory );
            $Directory = str_replace( '\\', DIRECTORY_SEPARATOR, $Directory );
            $Directory = str_replace( '/', DIRECTORY_SEPARATOR, $Directory );
            if (is_dir($Directory)) {
                $this->Directory = $Directory;
            } else {
                throw new TypeDirectoryException($Directory);
            }
        }
    }

}
