<?php
namespace SPHERE\Library\Script;

/**
 * Class Library
 *
 * @package SPHERE\Library\Script
 */
class Library
{
    /** @var string $Name */
    private $Name = '';
    /** @var string $Version */
    private $Version = '';
    /** @var string $Source */
    private $Source = '';
    /** @var string $Test */
    private $Test = '';

    /**
     * Library constructor.
     *
     * @param string $Name
     * @param string $Version
     * @param string $Source
     * @param string $Test
     */
    public function __construct($Name, $Version, $Source, $Test)
    {
        $this->setName($Name);
        $this->setVersion($Version);
        $this->setSource($Source);
        $this->setTest($Test);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = trim($Name);
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->Source;
    }

    /**
     * @param string $Source
     */
    public function setSource($Source)
    {
        $this->Source = trim($Source);
    }

    /**
     * @return string
     */
    public function getTest()
    {
        return $this->Test;
    }

    /**
     * @param string $Test
     */
    public function setTest($Test)
    {
        $this->Test = trim($Test);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->Version;
    }

    /**
     * @param string $Version
     */
    public function setVersion($Version)
    {
        $this->Version = trim($Version);
    }

    /**
     * @return string /Path/To/Library/Version e.g "/Library/Script/Repository/Name/x.x.x"
     */
    public function getLocation()
    {
        return '/'.trim( substr( $this->getSource(), 0, strpos( $this->getSource(), $this->getVersion() ) ), '/' ).'/'.$this->getVersion();
    }
}
