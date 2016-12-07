<?php
namespace MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;

/**
 * Class File
 *
 * @package MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP
 */
class File extends Attributes
{

    /**
     * @return string
     */
    public function getPermission()
    {

        return $this->getPermission();
    }

    /**
     * @return string
     */
    public function getMode()
    {

        return $this->getMode();
    }

    /**
     * @return int
     */
    public function getLastAccess()
    {

        return $this->getLastAccess();
    }

    /**
     * @return int
     */
    public function getLastChange()
    {

        return $this->getLastChange();
    }

    /**
     * @return bool
     */
    public function existsFile()
    {

        return $this->getConnection()->existsFile($this->getName());
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->getName();
    }
}
