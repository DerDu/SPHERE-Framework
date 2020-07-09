<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class SelectBox
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class SelectBox extends AbstractField implements IFieldInterface
{
    const LIBRARY_SELECTER = 0;
    const LIBRARY_SELECT2 = 1;

    /** @var int $Library */
    private $Library = 1;
    /** @var string $Label */
    private $Label = '';
    /** @var array $Data */
    private $Data = array();
    /** @var null|IIconInterface $Icon */
    private $Icon = null;
    /** @var array $Configuration */
    private $Configuration = array();
    /**
     * @var int $minimumResultsForSearch
     * Alle Einträge werden berücksichtigt, auch -[ Nicht ausgewählt ]-
     */
    private $minimumResultsForSearch = 9;

    /**
     * @param string $Name
     * @param null|string $Label
     * @param array $Data array( value => title )
     * @param IIconInterface $Icon
     * @param bool $useAutoValue
     * @param int $useSort SORT_{NATURAL}|null
     */
    public function __construct(
        $Name,
        $Label = '',
        $Data = array(),
        IIconInterface $Icon = null,
        $useAutoValue = true,
        $useSort = SORT_NATURAL
    ) {

        $this->Name = $Name;
        $this->Label = $Label;
        $this->Icon = $Icon;
        $this->Configuration = json_encode( array(), JSON_FORCE_OBJECT );

        // Sanitize (wrong) entity list parameter (e.g. bool instead of entities or empty
        if (count($Data) == 1 && is_numeric(key($Data)) === false && current($Data) === false) {
            $Data = array();
        }
        if (empty( $Data )) {
            $Data[0] = '-[ Nicht verfügbar ]-';
        } else {
            // Data is Entity-List ?
            if (count($Data) == 1 && !is_numeric(key($Data))) {
                $Attribute = key($Data);
                $Sample = current($Data[$Attribute]);
                // Add Zero-Element -> '-[ Nicht ausgewählt ]-'
                if (is_object($Sample)) {
                    if ($Sample instanceof Element && $Sample->getId() ) {
                        /** @var Element $SampleClass */
                        $SampleClass = (new \ReflectionClass($Sample))->newInstanceWithoutConstructor();
                        $SampleClass->setId(0);
                        array_unshift($Data[$Attribute], $SampleClass);
                    }
                }
            }
        }

        // Data is Entity-List ?
        if (count($Data) == 1 && !is_numeric(key($Data))) {
            $Attribute = key($Data);
            $Convert = array();
            // Attribute is Twig-Template ?
            if (preg_match_all('/\{\%\s*(.*)\s*\%\}|\{\{(?!%)\s*((?:[^\s])*?)\s*(?<!%)\}\}/i',
                $Attribute,
                $Placeholder)
            ) {
                /** @var Element $Entity */
                foreach ((array)$Data[$Attribute] as $Entity) {
                    if (is_object($Entity)) {
                        if ($Entity->getId() === null) {
                            $Entity->setId(0);
                        }
                        $Template = Template::getTwigTemplateString($Attribute);
                        foreach ((array)$Placeholder[2] as $Variable) {
                            $Chain = explode('.', $Variable);
                            if (count($Chain) > 1) {
                                $Template->setVariable($Chain[0], $Entity->{'get'.$Chain[0]}());
                            } else {
                                if (method_exists($Entity, 'get'.$Variable)) {
                                    $Template->setVariable($Variable, $Entity->{'get'.$Variable}());
                                } else {
                                    if (property_exists($Entity, $Variable)) {
                                        $Template->setVariable($Variable, $Entity->{$Variable});
                                    } else {
                                        $Template->setVariable($Variable, null);
                                    }
                                }
                            }
                        }
                        $Convert[$Entity->getId()] = $Template->getContent();
                    }
                }
            } else {
                /** @var Element $Entity */
                foreach ((array)$Data[$Attribute] as $Entity) {
                    if (is_object($Entity)) {
                        if ($Entity->getId() === null) {
                            $Entity->setId(0);
                        }
                        if (method_exists($Entity, 'get'.$Attribute)) {
                            $Convert[$Entity->getId()] = $Entity->{'get'.$Attribute}();
                        } else {
                            $Convert[$Entity->getId()] = $Entity->{$Attribute};
                        }
                    }
                }
            }
            if (array_key_exists(0, $Convert) && $useAutoValue) {
                unset( $Convert[0] );
                if( $useSort !== null ) {
                    asort($Convert, $useSort);
                }
                $Keys = array_keys( $Convert );
                $Values = array_values( $Convert );
                array_unshift( $Keys, 0 );
                array_unshift( $Values, '-[ Nicht ausgewählt ]-');
                $Convert = array_combine( $Keys, $Values );
            } else {
                if( $useSort !== null ) {
                    asort($Convert, $useSort);
                }
            }
            $this->Data = $Convert;
        } else {
            if (array_key_exists(0, $Data) && $Data[0] != '-[ Nicht verfügbar ]-' && $useAutoValue) {
                unset( $Data[0] );
                if( $useSort !== null ) {
                    asort($Data, $useSort);
                }
                $Keys = array_keys( $Data );
                $Values = array_values( $Data );
                array_unshift( $Keys, 0 );
                array_unshift( $Values, '-[ Nicht ausgewählt ]-');
                $Data = array_combine( $Keys, $Values );
            } else {
                if( $useSort !== null ) {
                    asort($Data, $useSort);
                }
            }
            $this->Data = $Data;
        }
    }

    /**
     * @return int
     */
    public function getLibrary()
    {
        return $this->Library;
    }

    /**
     * @param int $Library LIBRARY_DEFAULT|LIBRARY_SELECT2
     * @param array $Configuration
     * @return SelectBox
     */
    public function configureLibrary($Library = self::LIBRARY_SELECTER, $Configuration = array())
    {
        $this->Library = $Library;
        $this->Configuration = json_encode( $Configuration, JSON_FORCE_OBJECT );
        return $this;
    }

    /**
     * @param int $minimumResultsForSearch
     * @return SelectBox
     */
    public function setMinimumResultForSerach($minimumResultsForSearch = 8)
    {
        $this->minimumResultsForSearch = $minimumResultsForSearch;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        switch ($this->getLibrary()) {
            case 1:
                // Icon disabled in Selectbox2
                $this->Icon = null;
                $this->Template = $this->getTemplate(__DIR__ . '/SelectBox.Select2.twig');
                break;
            default:
                $this->Template = $this->getTemplate(__DIR__ . '/SelectBox.twig');
                break;
        }

        foreach ($this->ErrorMessage as $Key => $Value) {
            $this->Template->setVariable($Key, $Value);
        }
        foreach ($this->SuccessMessage as $Key => $Value) {
            $this->Template->setVariable($Key, $Value);
        }

        // Erweitern der Configuration, wenn bereits eine mitgegeben wird
        $ConfigurationArray = json_decode($this->Configuration, true);
        $ConfigurationArray = array_merge($ConfigurationArray, array('minimumResultsForSearch' => $this->minimumResultsForSearch));
        $this->Configuration = json_encode($ConfigurationArray);

        $this->Template->setVariable('ElementName', $this->Name);
        $this->Template->setVariable('ElementLabel', $this->Label);
        $this->Template->setVariable('ElementData', $this->Data);
        $this->Template->setVariable('ElementConfiguration', $this->Configuration);
        if (null !== $this->Icon) {
            $this->Template->setVariable('ElementIcon', $this->Icon);
        }
        return parent::getContent();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->Label;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->Data;
    }
}
