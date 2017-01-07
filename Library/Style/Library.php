<?php
namespace SPHERE\Library\Style;

/**
 * Class Library
 *
 * @package SPHERE\Library\JavaScript
 */
class Library
{
    /** @var string $Name */
    private $Name = '';
    /** @var string $Version */
    private $Version = '';
    /** @var string $Source */
    private $Source = '';

    /**
     * Library constructor.
     *
     * @param string $Name
     * @param string $Version
     * @param string $Source
     */
    public function __construct($Name, $Version, $Source)
    {
        $this->setName($Name);
        $this->setVersion($Version);
        $this->setSource($Source);
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
     * @return string /Path/To/Library/Version e.g "/Library/Style/Repository/Name/x.x.x"
     */
    public function getLocation()
    {
        return '/'.trim( substr( $this->getSource(), 0, strpos( $this->getSource(), $this->getVersion() ) ), '/' ).'/'.$this->getVersion();
    }
}
