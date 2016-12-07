<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;
use SPHERE\System\Support\Support;
use SPHERE\System\Support\Type\Roadmap;

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

        $Config = (new Support(new Roadmap()))->getSupport();

        $this->Host = $Config->getHost();
        $this->Username = $Config->getUsername();
        $this->Password = $Config->getPassword();
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
