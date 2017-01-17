<?php
namespace SPHERE\Common\Frontend\Form\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractField
 *
 * @package SPHERE\Common\Frontend\Form\Repository
 */
abstract class AbstractField extends Extension implements IFieldInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var bool $isForceDefaultValue */
    protected $isForceDefaultValue = false;

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->setPostValue($this->Template, $this->getName(), 'ElementValue');
        return $this->Template->getContent();
    }

    /**
     * @param IBridgeInterface $Template
     * @param string           $RequestKey
     * @param string           $VariableName
     *
     * @return AbstractField
     */
    protected function setPostValue(IBridgeInterface &$Template, $RequestKey, $VariableName)
    {

        if (preg_match('!^(.*?)\[(.*?)\]$!is', $RequestKey, $Match)) {
            if (false === strpos($Match[2], '[')) {
                if (isset( $this->getGlobal()->POST[$Match[1]][$Match[2]] )) {
                    $Template->setVariable($VariableName,
                        htmlentities($this->getGlobal()->POST[$Match[1]][$Match[2]], ENT_QUOTES));
                } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Match[2]] )) {
                    $Template->setVariable($VariableName,
                        htmlentities($this->getGlobal()->GET[$Match[1]][$Match[2]], ENT_QUOTES));
                }
            } else {
                /**
                 * Next dimension
                 */
                if (preg_match_all('!\]\[!is', $Match[2]) == 1) {
                    $Key = explode('][', $Match[2]);
                    if (isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]] )) {
                        $Template->setVariable($VariableName,
                            htmlentities($this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]],
                                ENT_QUOTES));
                    } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]] )) {
                        $Template->setVariable($VariableName,
                            htmlentities($this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]],
                                ENT_QUOTES));
                    }
                } else {
                    /**
                     * Next dimension
                     */
                    if (preg_match_all('!\]\[!is', $Match[2]) == 2) {
                        $Key = explode('][', $Match[2]);
                        if (isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )) {
                            $Template->setVariable($VariableName,
                                htmlentities($this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]],
                                    ENT_QUOTES));
                        } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )) {
                            $Template->setVariable($VariableName,
                                htmlentities($this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]],
                                    ENT_QUOTES));
                        }
                    } else {
                        /**
                         * Next dimension
                         */
                    }
                }
            }
        } else {
            if (isset( $this->getGlobal()->POST[$RequestKey] )) {
                $Template->setVariable($VariableName,
                    htmlentities($this->getGlobal()->POST[$RequestKey], ENT_QUOTES));
            } elseif (isset( $this->getGlobal()->GET[$RequestKey] )) {
                $Template->setVariable($VariableName,
                    htmlentities($this->getGlobal()->GET[$RequestKey], ENT_QUOTES));
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string         $Message
     * @param IIconInterface $Icon
     */
    public function setError($Message, IIconInterface $Icon = null)
    {

        $this->Template->setVariable('ElementGroup', 'has-error has-feedback');
        if (null !== $Icon) {
            $this->Template->setVariable('ElementFeedbackIcon',
                '<span class="'.$Icon->getValue().' form-control-feedback"></span>');
        }
        $this->Template->setVariable('ElementFeedbackMessage',
            '<span class="help-block text-left">'.$Message.'</span>');
    }

    /**
     * @param string         $Message
     * @param IIconInterface $Icon
     */
    public function setSuccess($Message, IIconInterface $Icon = null)
    {

        $this->Template->setVariable('ElementGroup', 'has-success has-feedback');
        if (null !== $Icon) {
            $this->Template->setVariable('ElementFeedbackIcon',
                '<span class="'.$Icon->getValue().' form-control-feedback"></span>');
        }
        $this->Template->setVariable('ElementFeedbackMessage',
            '<span class="help-block text-left">'.$Message.'</span>');
    }

    /**
     * @param mixed $Value
     * @param bool  $Force
     *
     * @return AbstractField
     */
    public function setDefaultValue($Value, $Force = false)
    {

        $Global = $this->getGlobal();
        if ($Force || !isset( $Global->POST[$this->getName()] )) {
            if( $Force ) {
                $this->isForceDefaultValue = true;
            }
            $Global->POST[$this->getName()] = $Value;
            $Global->savePost();
        }

        return $this;
    }

    /**
     * @param mixed $Value
     *
     * @return AbstractField
     */
    public function setPrefixValue($Value)
    {

        $this->Template->setVariable('ElementPrefix', $Value);
        return $this;
    }

    /**
     * @param string           $RequestKey
     * @param                  $Value
     *
     * @return bool
     */
    public function isChecked($RequestKey, $Value)
    {

        if (preg_match('!^(.*?)\[(.*?)\]$!is', $RequestKey, $Match)) {
            if (false === strpos($Match[2], '[')) {
                if (
                    isset( $this->getGlobal()->POST[$Match[1]][$Match[2]] )
                    && $this->getGlobal()->POST[$Match[1]][$Match[2]] == $Value
                ) {
                    return true;
                } elseif (
                    isset( $this->getGlobal()->GET[$Match[1]][$Match[2]] )
                    && $this->getGlobal()->GET[$Match[1]][$Match[2]] == $Value
                ) {
                    return true;
                }
            } else {
                /**
                 * Next dimension
                 */
                if (preg_match_all('!\]\[!is', $Match[2]) == 1) {
                    $Key = explode('][', $Match[2]);
                    if (
                        isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]] )
                        && $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]] == $Value
                    ) {
                        return true;
                    } elseif (
                        isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]] )
                        && $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]] == $Value
                    ) {
                        return true;
                    }
                } else {
                    /**
                     * Next dimension
                     */
                    if (preg_match_all('!\]\[!is', $Match[2]) == 2) {
                        $Key = explode('][', $Match[2]);
                        if (
                            isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )
                            && $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]] == $Value
                        ) {
                            return true;
                        } elseif (
                            isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )
                            && $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]] == $Value
                        ) {
                            return true;
                        }
                    } else {
                        /**
                         * Next dimension
                         */
                    }
                }
            }
        } else {
            if (
                isset( $this->getGlobal()->POST[$RequestKey] )
                && $this->getGlobal()->POST[$RequestKey] == $Value
            ) {
                return true;
            } elseif (
                isset( $this->getGlobal()->GET[$RequestKey] )
                && $this->getGlobal()->GET[$RequestKey] == $Value
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return AbstractField
     */
    public function setDisabled()
    {

        $this->Template->setVariable('Disabled', true);
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function setRequired()
    {

        $this->Template->setVariable('Required', true);
        return $this;
    }

    /**
     * Set Tabulator-Order (Index)
     *
     * @param int $Index
     *
     * @return AbstractField
     */
    public function setTabIndex($Index = 1)
    {

        $this->Template->setVariable('TabIndex', (int)$Index);
        return $this;
    }

    /**
     * Set Field to automatically Focus on page load
     *
     * @return AbstractField
     */
    public function setAutoFocus()
    {
        $this->Template->setVariable('AutoFocus', 'autofocus');
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function setInputAlignLeft()
    {
        $this->Template->setVariable( 'ElementClass', 'text-left' );
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function setInputAlignCenter()
    {
        $this->Template->setVariable( 'ElementClass', 'text-center' );
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function setInputAlignRight()
    {
        $this->Template->setVariable( 'ElementClass', 'text-right' );
        return $this;
    }
}
