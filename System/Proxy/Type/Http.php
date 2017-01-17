<?php
namespace SPHERE\System\Proxy\Type;

use SPHERE\System\Proxy\ITypeInterface;

/**
 * Class Http
 *
 * @package SPHERE\System\Proxy\Type
 */
class Http implements ITypeInterface
{

    /** @var null|string $Host */
    private $Host = null;
    /** @var null|string $Port */
    private $Port = null;
    /** @var null|string $Username */
    private $Username = null;
    /** @var null|string $Password */
    private $Password = null;

    /**
     * @return null|string
     */
    public function getHost()
    {

        return $this->Host;
    }

    /**
     * @return null|string
     */
    public function getPort()
    {

        return $this->Port;
    }

    /**
     * @return null|string
     */
    public function getUsernamePassword()
    {

        $UserPass = $this->getUsername().':'.$this->getPassword();
        if ($UserPass == ':') {
            return null;
        } else {
            return $UserPass;
        }
    }

    /**
     * @return null|string
     */
    public function getUsername()
    {

        return $this->Username;
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {

        return $this->Password;
    }

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        if (isset( $Configuration['Host'] )) {
            $this->Host = $Configuration['Host'];
        }
        if (isset( $Configuration['Port'] )) {
            $this->Port = $Configuration['Port'];
        }
        if (isset( $Configuration['Username'] )) {
            $this->Username = $Configuration['Username'];
        }
        if (isset( $Configuration['Password'] )) {
            $this->Password = $Configuration['Password'];
        }

    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return 'Http';
    }
}
