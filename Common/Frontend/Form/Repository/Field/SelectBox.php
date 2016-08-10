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

    /**
     * @param string         $Name
     * @param null|string    $Label
     * @param array          $Data array( value => title )
     * @param IIconInterface $Icon
     */
    public function __construct(
        $Name,
        $Label = '',
        $Data = array(),
        IIconInterface $Icon = null
    ) {

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
        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/SelectBox.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
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
            if (array_key_exists(0, $Convert)) {
                unset( $Convert[0] );
                asort($Convert, SORT_NATURAL);

                $Keys = array_keys( $Convert );
                $Values = array_values( $Convert );
                array_unshift( $Keys, 0 );
                array_unshift( $Values, '-[ Nicht ausgewählt ]-');
                $Convert = array_combine( $Keys, $Values );
            } else {
                asort($Convert, SORT_NATURAL);
            }
            $this->Template->setVariable('ElementData', $Convert);
        } else {
            if (array_key_exists(0, $Data) && $Data[0] != '-[ Nicht verfügbar ]-') {
                unset( $Data[0] );
                asort($Data, SORT_NATURAL);

                $Keys = array_keys( $Data );
                $Values = array_values( $Data );
                array_unshift( $Keys, 0 );
                array_unshift( $Values, '-[ Nicht ausgewählt ]-');
                $Data = array_combine( $Keys, $Values );
            } else {
                asort($Data, SORT_NATURAL);
            }
            $this->Template->setVariable('ElementData', $Data);
        }
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
    }

}
