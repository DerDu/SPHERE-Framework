<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use SPHERE\Common\Frontend\Layout\ILayoutInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\System\Extension\Extension;

/**
 * Class LayoutGroup
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutGroup extends Extension implements ILayoutInterface
{

    /** @var LayoutRow[] $LayoutRow */
    private $LayoutRow = array();
    /** @var string $LayoutTitle */
    private $LayoutTitle = '';

    /**
     * @param LayoutRow|LayoutRow[] $LayoutRow
     * @param Title                 $LayoutTitle
     */
    public function __construct($LayoutRow, Title $LayoutTitle = null)
    {

        if (!is_array($LayoutRow)) {
            $LayoutRow = array($LayoutRow);
        }
        $this->LayoutRow = $LayoutRow;
        $this->LayoutTitle = $LayoutTitle;
    }

    /**
     * @param LayoutRow $LayoutRow
     *
     * @return LayoutGroup
     */
    public function addRow(LayoutRow $LayoutRow)
    {

        array_push($this->LayoutRow, $LayoutRow);
        return $this;
    }
    
    /**
     * @return string
     */
    public function getLayoutTitle()
    {

        return $this->LayoutTitle;
    }

    /**
     * @return LayoutRow[]
     */
    public function getLayoutRow()
    {

        return $this->LayoutRow;
    }
}
