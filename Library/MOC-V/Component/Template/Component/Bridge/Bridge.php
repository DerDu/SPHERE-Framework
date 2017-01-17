<?php
namespace MOC\V\Component\Template\Component\Bridge;

use MOC\V\Component\Template\Component\IBridgeInterface;

/**
 * Class Bridge
 *
 * @package MOC\V\Component\Template\Component\Bridge
 */
abstract class Bridge implements IBridgeInterface
{

    /** @var array $VariableList */
    protected $VariableList = array();

    /**
     * @param string $Identifier
     * @param mixed  $Value
     *
     * @return IBridgeInterface
     */
    public function setVariable($Identifier, $Value)
    {

        $this->VariableList[$Identifier] = $Value;
        return $this;
    }

    /**
     * @param bool|false $Reload
     *
     * @return object
     */
    abstract public function createInstance($Reload = false);

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }
}
