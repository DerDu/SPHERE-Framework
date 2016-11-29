<?php
namespace SPHERE\System\Saml\Type;

use SPHERE\System\Extension\Extension;
use SPHERE\System\Saml\ITypeInterface;
use SPHERE\System\Saml\Environment\AbstractEnvironment;

/**
 * Class Saml
 *
 * @package SPHERE\System\Saml\Type
 */
class SamlEnvironment extends Extension implements ITypeInterface
{

    /** @var null|\OneLogin_Saml2_Auth $Environment */
    private $Environment = null;

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return 'Saml';
    }

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        if (isset($Configuration['Environment'])) {

            $EnvironmentFile = __DIR__ . '/../Environment/' . $Configuration['Environment'] . '.php';
            /** @noinspection PhpIncludeInspection */
            require_once($EnvironmentFile);

            $EnvironmentClass = 'SPHERE\System\Saml\Environment\\' . $Configuration['Environment'];

            /** @var AbstractEnvironment $EnvironmentInstance */
            $EnvironmentInstance = new $EnvironmentClass;

            $this->Environment = new \OneLogin_Saml2_Auth($EnvironmentInstance->getSettings());
        }
    }

    /**
     * @return null|\OneLogin_Saml2_Auth
     */
    public function getEnvironment()
    {
        return $this->Environment;
    }
}