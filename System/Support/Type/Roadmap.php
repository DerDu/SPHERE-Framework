<?php
namespace SPHERE\System\Support\Type;

use SPHERE\System\Extension\Extension;
use SPHERE\System\Support\ITypeInterface;

/**
 * Class Roadmap
 * @package SPHERE\System\Support\Type
 */
class Roadmap extends Extension  implements ITypeInterface
{

    /** @var null|string $Host */
    private $Host = null;
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
     * @return string
     */
    public function getConfiguration()
    {
        return 'YouTrack';
    }

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        if (isset( $Configuration['Host'] )) {
            $this->Host = $Configuration['Host'];
        }
        if (isset( $Configuration['Username'] )) {
            $this->Username = $Configuration['Username'];
        }
        if (isset( $Configuration['Password'] )) {
            $this->Password = $Configuration['Password'];
        }

    }

    /**
     * @param string $Summary
     * @param string $Description
     *
     * @throws \Exception
     * @return array
     */
    public function createTicket($Summary, $Description)
    {
        throw new \Exception('Not implemented');
    }
}