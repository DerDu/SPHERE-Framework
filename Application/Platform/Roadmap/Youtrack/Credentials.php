<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;

/**
 * Class Credentials
 *
 * @package SPHERE\Application\Platform\Roadmap\Youtrack
 */
class Credentials
{

    /** @var null|string $Host */
    private $Host = null;
    /** @var null|string $Username */
    private $Username = null;
    /** @var null|string $Password */
    private $Password = null;

    /**
     * Credentials constructor.
     */
    public function __construct()
    {

        $this->Host = 'https://ticket.swe.haus-der-edv.de';
        $this->Username = 'KREDA-Support';
        $this->Password = 'Professional';
    }

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
}
