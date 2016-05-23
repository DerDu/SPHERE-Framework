<?php
namespace SPHERE\System\Extension\Repository\Sorter\Object;

use SPHERE\System\Extension\Repository\Sorter\ISorterInterface;

/**
 * Class StringNaturalOrderSorter
 *
 * @package SPHERE\System\Extension\Repository\Sorter
 */
class StringNaturalOrderSorter implements ISorterInterface
{

    /**
     * @param string $Property (Getter)
     *
     * @param object $First
     * @param object $Second
     *
     * @return int -1,0,1
     */
    public function sortAsc($Property, $First, $Second)
    {

        if ($this->isSortable($Property, $First, $Second)) {
            return strnatcmp($this->getValue($Property, $First), $this->getValue($Property, $Second));
        }
        return 0;
    }

    /**
     * @param string $Property Entity-Attribute (Getter)
     * @param object $First
     * @param object $Second
     *
     * @return bool
     */
    final protected function isSortable($Property, $First, $Second)
    {

        if (method_exists($First, 'get'.$Property) && method_exists($Second, 'get'.$Property)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $Property Entity-Attribute (Getter)
     * @param object $Element
     *
     * @return mixed
     */
    final protected function getValue($Property, $Element)
    {

        return $Element->{'get'.$Property}();
    }

    /**
     * @param string $Property (Getter)
     *
     * @param object $First
     * @param object $Second
     *
     * @return int -1,0,1
     */
    public function sortDesc($Property, $First, $Second)
    {

        if ($this->isSortable($Property, $First, $Second)) {
            return strnatcmp($this->getValue($Property, $Second), $this->getValue($Property, $First));
        }
        return 0;
    }
}
