<?php
namespace SPHERE\System\Saml;

/**
 * Interface ITypeInterface
 *
 * @package SPHERE\System\Support
 */
interface ITypeInterface
{

    /**
     * @return string
     */
    public function getConfiguration();

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration);

    /**
     * @return null|\OneLogin_Saml2_Auth
     */
    public function getEnvironment();
}
