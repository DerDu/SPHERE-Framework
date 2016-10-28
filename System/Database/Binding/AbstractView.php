<?php
namespace SPHERE\System\Database\Binding;

use SPHERE\System\Database\Fitting\Element;

/**
 * Class AbstractView
 *
 * @package SPHERE\System\Database\Binding
 */
abstract class AbstractView extends Element
{
    const DISABLE_PATTERN = '!(_Id$|_service|_tbl|Locked|MetaTable|^Id$|^Entity)!s';

    /** @var array $NameDefinitionList */
    private $NameDefinitionList = array();

    /** @var array $DisabledDefinitionList */
    private $DisableDefinitionList = array();

    /** @var AbstractView[] $ForeignViewList */
    private $ForeignViewList = array();

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
                        if (
                            !preg_match(self::DISABLE_PATTERN, $Key)
                            && !$this->getDisableDefinition( $Key )
                        ) {
                            // Replace Value with Getter-Logic Value
                            if( $Object->hasMethod( 'get'.$Property->getName() ) ) {
                                $Value = $this->{'get'.$Property->getName()}();
                            }
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
     * TODO: Abstract
     *
     * Use this method to set disabled Properties with "setDisabledProperty()"
     *
     * @return void
     */
    public function loadDisableDefinition(){}

    /**
     * @param string $PropertyName
     *
     * @return AbstractView
     */
    protected function setDisableDefinition($PropertyName)
    {

        $this->DisableDefinitionList[$PropertyName] = true;
        return $this;
    }

    /**
     * @param string $PropertyName
     *
     * @return string
     */
    public function getDisableDefinition($PropertyName)
    {

        $this->loadDisableDefinition();

        if (isset( $this->DisableDefinitionList[$PropertyName] )) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getForeignViewList()
    {

        $this->loadViewGraph();

        return $this->ForeignViewList;
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    abstract public function loadViewGraph();

    /**
     * @return string View-Class-Name of Class (incl. Namespace)
     */
    final public function getViewClassName()
    {

        return ( new \ReflectionObject($this) )->getName();
    }

    /**
     * @return array
     */
    public function getNameDefinitionList()
    {

        $this->loadNameDefinition();

        return $this->NameDefinitionList;
    }

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return $this->getViewObjectName();
    }

    /**
     * @return string View-Object-Name of Class
     */
    final public function getViewObjectName()
    {

        return ( new \ReflectionObject($this) )->getShortName();
    }

    /**
     * @param string       $PropertyName
     * @param AbstractView $ForeignView
     * @param string       $ForeignPropertyName
     *
     * @return AbstractView
     */
    public function addForeignView($PropertyName, AbstractView $ForeignView, $ForeignPropertyName)
    {

        if (!in_array($ForeignView->getViewObjectName(), $this->ForeignViewList)) {
            $this->ForeignViewList[$ForeignView->getViewObjectName()] = array(
                $PropertyName,
                $ForeignView,
                $ForeignPropertyName
            );
        }
        return $this;
    }

    /**
     * @return AbstractService
     */
    abstract public function getViewService();

    /**
     * @param AbstractView $ForeignView
     *
     * @return string
     */
    public function getForeignLinkPropertyParent(AbstractView $ForeignView)
    {

        $this->loadViewGraph();
        // Index 0 = THIS-View Property-Name
        return $this->ForeignViewList[$ForeignView->getViewObjectName()][0];
    }

    /**
     * @param AbstractView $ForeignView
     *
     * @return string
     */
    public function getForeignLinkPropertyChild(AbstractView $ForeignView)
    {

        $this->loadViewGraph();
        // Index 2 = Foreign-View Property-Name
        return $this->ForeignViewList[$ForeignView->getViewObjectName()][2];
    }

    /**
     * Magic Getter for Properties
     *
     * @param $PropertyName
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get($PropertyName)
    {

        if (!empty( $PropertyName )) {
            if (property_exists($this, $PropertyName)) {
                /** @noinspection PhpVariableVariableInspection */
                return $this->$PropertyName;
            }
        }
        throw new \Exception('Property-Getter '.$PropertyName.' not found in '.get_class($this));
    }

    /**
     * Magic Setter for Properties
     *
     * @param string $PropertyName
     * @param $Value
     *
     * @return mixed
     * @throws \Exception
     */
    public function __set($PropertyName, $Value)
    {

        if (!empty( $PropertyName )) {
            if (property_exists($this, $PropertyName)) {
                /** @noinspection PhpVariableVariableInspection */
                return $this->$PropertyName = $Value;
            }
        }
        throw new \Exception('Property-Setter '.$PropertyName.' not found in '.get_class($this));
    }

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
