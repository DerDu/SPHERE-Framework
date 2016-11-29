<?php
namespace SPHERE\System\Saml\Environment;

/**
 * Class AbstractEnvironment
 * @package SPHERE\System\Saml\Setting
 */
abstract class AbstractEnvironment
{
    private $Settings = array();

    /**
     * @return array
     */
    public function getSettings()
    {
        return (array)$this->Settings;
    }

    /**
     * @param array $Settings
     */
    public function setSettings($Settings)
    {
        $this->Settings = (array)$Settings;
    }
}