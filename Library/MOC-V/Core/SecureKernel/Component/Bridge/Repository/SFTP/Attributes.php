<?php
namespace MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;

use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;

/**
 * Class Attributes
 *
 * @package MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP
 */
abstract class Attributes
{

    /** @var string $Name */
    private $Name = '';
    /** @var string $Permission */
    private $Permission = '';
    /** @var string $Mode */
    private $Mode = '';
    /** @var int $LastAccess */
    private $LastAccess = 0;
    /** @var int $LastChange */
    private $LastChange = 0;
    /** @var null|SFTP $Connection */
    private $Connection = null;

    /**
     * @param SFTP  $Connection
     * @param array $Attributes
     */
    public function __construct(SFTP $Connection, $Attributes)
    {

        $this->setName($Attributes['filename']);
        $this->setPermission(substr(decoct($Attributes['permissions']), -4));
        $this->setMode(substr(decoct($Attributes['mode']), -4));
        $this->setLastAccess($Attributes['atime']);
        $this->setLastChange($Attributes['mtime']);
        $this->setConnection($Connection);
    }

    /**
     * @return string
     */
    protected function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     *
     * @return Attributes
     */
    protected function setName($Name)
    {

        $this->Name = $Name;
        return $this;
    }

    /**
     * @return string
     */
    protected function getPermission()
    {

        return $this->Permission;
    }

    /**
     * @param string $Permission
     *
     * @return Attributes
     */
    protected function setPermission($Permission)
    {

        $this->Permission = $Permission;
        return $this;
    }

    /**
     * @return string
     */
    protected function getMode()
    {

        return $this->Mode;
    }

    /**
     * @param string $Mode
     *
     * @return Attributes
     */
    protected function setMode($Mode)
    {

        $this->Mode = $Mode;
        return $this;
    }

    /**
     * @return int
     */
    protected function getLastAccess()
    {

        return $this->LastAccess;
    }

    /**
     * @param int $LastAccess
     *
     * @return Attributes
     */
    protected function setLastAccess($LastAccess)
    {

        $this->LastAccess = $LastAccess;
        return $this;
    }

    /**
     * @return int
     */
    protected function getLastChange()
    {

        return $this->LastChange;
    }

    /**
     * @param int $LastChange
     *
     * @return Attributes
     */
    protected function setLastChange($LastChange)
    {

        $this->LastChange = $LastChange;
        return $this;
    }

    /**
     * @return SFTP|null
     */
    protected function getConnection()
    {

        return $this->Connection;
    }

    /**
     * @param SFTP|null $Connection
     *
     * @return Attributes
     */
    protected function setConnection($Connection)
    {

        $this->Connection = $Connection;
        return $this;
    }
}
