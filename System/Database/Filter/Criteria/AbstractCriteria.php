<?php
namespace SPHERE\System\Database\Filter\Criteria;

use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class AbstractCriteria
 *
 * @package SPHERE\System\Database\Filter\Criteria
 */
abstract class AbstractCriteria
{

    /** @var null|AbstractService $Service */
    private $Service = null;
    /** @var null|string $GetterAll */
    private $GetterAll = null;
    /** @var null|string $GetterId */
    private $GetterId = null;

    private $Title = '';
    private $FieldList = array();
    private $LinkList = array();

    /**
     * @param $PropertySearch
     * @return array|bool
     */
    public function findBy($PropertySearch)
    {

        Debugger::screenDump($PropertySearch);
        if (isset( $PropertySearch[$this->getCriteriaName()] )) {
            $PropertySearch = $this->sanitizeSearch($PropertySearch[$this->getCriteriaName()]);
            Debugger::screenDump($PropertySearch);
            $EntityList = $this->Service->{$this->GetterAll}();
            if ($EntityList) {
                array_walk($EntityList, function (Element &$Entity) use ($PropertySearch) {

                    foreach ($PropertySearch as $Property => $Value) {
                        if (method_exists($Entity, 'get'.$Property)) {
                            $Check = $Entity->{'get'.$Property}();
                            if (!preg_match('!'.$Value.'!is', $Check)) {
                                $Entity = false;
                                break;
                            }
                        } else {
                            throw new \Exception('Invalid Property '.$Property.' in '.get_class($Entity));
                        }
                    }
                });
                $EntityList = array_filter($EntityList);
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $EntityList;
    }

    /**
     * @return string
     */
    private function getCriteriaName()
    {

        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @param $PropertySearch
     * @param string $Delimiter
     * @return mixed
     */
    private function sanitizeSearch($PropertySearch, $Delimiter = '!')
    {

        if ($PropertySearch) {
            array_walk($PropertySearch, function (&$Value) use ($Delimiter) {

                if (empty( $Value )) {
                    $Value = '(.*)';
                } else {
                    $WordList = explode(' ', $Value);
                    array_walk($WordList, function (&$Item) use ($Delimiter) {

                        if (empty( $Item )) {
                            $Item = false;
                        } else {
                            $Item = preg_quote($Item, $Delimiter);
                        }
                    });
                    $WordList = array_filter($WordList);
                    if (empty( $WordList )) {
                        $Value = '(.*)';
                    } else {
                        $Value = '(?=.*'.implode(')(?=.*', $WordList).')';
                    }
                }
            });
        }
        return $PropertySearch;
    }

    /**
     * @param string $Title
     *
     * @return $this
     */
    public function setTitle($Title)
    {

        $this->Title = $Title;
        return $this;
    }

    /**
     * @return array
     */
    public function getDesignerGui()
    {

        // Sanitize Title
        if (empty( $this->Title )) {
            $this->Title = $this->getCriteriaName();
        }

        $Result = array(
            new Muted(new Small('In '.$this->Title.' nach folgendem suchen:'))
        );
        foreach ($this->FieldList as $Property => $Label) {
            // Sanitize Label
            if (empty( $Label )) {
                $Label = $Property;
            }
            array_push(
                $Result,
                new CheckBox('Designer['.$this->getCriteriaName().']['.$Property.']', $Label, true, new Search())
            );
        }

        $Content = array(
            new Panel(
                $this->Title, $Result, Panel::PANEL_TYPE_INFO
            )
        );

        /** @var AbstractCriteria $Criteria */
        foreach ($this->LinkList as $Criteria) {
            array_push($Content, $Criteria->getDesignerGui());
        }

        return $Content;
    }

    /**
     * @param $Setup
     * @return array
     */
    public function getSearchGui($Setup)
    {

        // Sanitize Title
        if (empty( $this->Title )) {
            $this->Title = $this->getCriteriaName();
        }

        $Result = array(
            new Muted(new Small('In '.$this->Title.' nach folgendem suchen:'))
        );

        $Content = array();

        if (isset( $Setup[$this->getCriteriaName()] )) {
            foreach ($Setup[$this->getCriteriaName()] as $Property => $Default) {
                // Sanitize Label
                $Label = $this->FieldList[$Property];
                array_push(
                    $Result,
                    new TextField('Search['.$this->getCriteriaName().']['.$Property.']', $Label, $Label, new Search())
                );
            }

            $Content = array(
                new Panel(
                    $this->Title, $Result, Panel::PANEL_TYPE_INFO
                )
            );

            /** @var AbstractCriteria $Criteria */
            foreach ($this->LinkList as $Criteria) {
                array_push($Content, $Criteria->getSearchGui($Setup));
            }
        }
        return $Content;
    }

    /**
     * @param AbstractService $Service
     * @return $this
     * @throws \Exception
     */
    protected function setupService(AbstractService $Service)
    {

        if ($this->isValidService($Service)) {
            $this->Service = $Service;
        } else {
            throw new \Exception('Invalid Service '.get_class($Service));
        }
        return $this;
    }

    /**
     * @param $Class
     *
     * @return bool
     */
    private function isValidService($Class)
    {

        $Service = new \ReflectionClass($Class);
        return $Service->isSubclassOf('\SPHERE\System\Database\Binding\AbstractService');
    }

    /**
     * @param $Method
     * @return $this
     * @throws \Exception
     */
    protected function setupGetterAll($Method)
    {

        if ($this->isValidGetter($Method)) {
            $this->GetterAll = $Method;
        } else {
            throw new \Exception('Invalid Getter '.$Method);
        }
        return $this;
    }

    /**
     * @param $Method
     *
     * @return bool
     */
    private function isValidGetter($Method)
    {

        $Service = new \ReflectionClass($this->Service);
        return $Service->hasMethod($Method);
    }

    /**
     * @param $Method
     * @return $this
     * @throws \Exception
     */
    protected function setupGetterId($Method)
    {

        if ($this->isValidGetter($Method)) {
            $this->GetterId = $Method;
        } else {
            throw new \Exception('Invalid Getter '.$Method);
        }
        return $this;
    }

    /**
     * @param string $Property
     * @param string $Label
     *
     * @return $this
     */
    protected function addField($Property, $Label = '')
    {

        $this->FieldList[$Property] = $Label;
        return $this;
    }

    /**
     * @param AbstractCriteria $Criteria
     * @return $this
     */
    protected function addLink(AbstractCriteria $Criteria)
    {

        array_push($this->LinkList, $Criteria);
        return $this;
    }
}
