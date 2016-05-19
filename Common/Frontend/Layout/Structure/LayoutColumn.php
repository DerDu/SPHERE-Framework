<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\ILayoutInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class LayoutColumn
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutColumn extends Extension implements ILayoutInterface
{

    const GRID_OPTION_HIDDEN_XS = 'hidden-xs';
    const GRID_OPTION_HIDDEN_SM = 'hidden-sm';

    /** @var string|IFrontendInterface|IFrontendInterface[] $Frontend */
    private $Frontend = array();
    /** @var int $Size */
    private $Size = 12;
    /** @var array $GridOption */
    private $GridOption = array();

    /**
     * @param string|IFrontendInterface|IFrontendInterface[] $Frontend
     * @param int|array                                      $Size       int || array( xs, sm, md, lg )
     * @param array                                          $GridOption LayoutColumn::GRID_OPTION_[..]
     */
    public function __construct($Frontend, $Size = 12, $GridOption = array())
    {

        if (!is_array($Frontend)) {
            $Frontend = array($Frontend);
        }
        $this->Frontend = $Frontend;
        $this->Size = $Size;
        $this->GridOption = $GridOption;
    }

    /**
     * @return int
     */
    public function getSize()
    {

        return $this->Size;
    }

    /**
     * @return string
     */
    public function getGridOption()
    {

        return implode(' ', $this->GridOption);
    }

    /**
     * @return IFrontendInterface[]
     */
    public function getFrontend()
    {

        return $this->Frontend;
    }

    /**
     * @param string|IFrontendInterface $Frontend
     *
     * @return LayoutColumn
     */
    public function addFrontend($Frontend)
    {

        array_push($this->Frontend, $Frontend);
        return $this;
    }
}
