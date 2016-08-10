<?php
namespace SPHERE\System\Database\Binding;

use SPHERE\System\Database\Fitting\Element;

abstract class AbstractView extends Element
{

    /** @var array $NameDefinitionList */
    private $NameDefinitionList = array();

    /**
     * @throws \Exception
     */
    public function __toView()
    {

        if (method_exists($this, 'getNameDefinition')) {
            $Object = new \ReflectionObject($this);
            $Array = get_object_vars($this);
            $Result = array();
            foreach ($Array as $Key => $Value) {
                if ($Object->hasProperty($Key)) {
                    $Property = $Object->getProperty($Key);
                    if ($Property->isProtected() || $Property->isPublic()) {
                        if (!preg_match('!(_Id|_service|_tbl|Locked|MetaTable|^Id$|^Entity)!s', $Key)) {
                            if ($Value instanceof \DateTime) {
                                $Result[$this->getNameDefinition($Key)] = $Value->format('d.m.Y H:i:s');
                            } else {
                                $Result[$this->getNameDefinition($Key)] = $Value;
                            }
                        }
                    }
                }
            }
        } else {
            $Result = $this->__toArray();
        }
        return $Result;
    }

    /**
     * @param string $PropertyName
     *
     * @return string
     */
    public function getNameDefinition($PropertyName)
    {

        $this->loadNameDefinition();

        if (isset( $this->NameDefinitionList[$PropertyName] )) {
            return $this->NameDefinitionList[$PropertyName];
        }
        return $PropertyName;
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    abstract public function loadNameDefinition();

    /**
     * @param string $PropertyName
     * @param string $DisplayName
     *
     * @return AbstractView
     */
    protected function setNameDefinition($PropertyName, $DisplayName)
    {

        $this->NameDefinitionList[$PropertyName] = $DisplayName;
        return $this;
    }
}
