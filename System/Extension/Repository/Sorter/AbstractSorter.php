<?php
namespace SPHERE\System\Extension\Repository\Sorter;

use SPHERE\System\Database\Fitting\Element;

abstract class AbstractSorter implements ISorterInterface
{

    /**
     * @param string  $Property Entity-Attribute (Getter)
     *
     * @param Element $First
     * @param Element $Second
     *
     * @return int -1,0,1
     */
    abstract public function sortAsc($Property, Element $First, Element $Second);

    /**
     * @param string  $Property Entity-Attribute (Getter)
     *
     * @param Element $First
     * @param Element $Second
     *
     * @return int -1,0,1
     */
    abstract public function sortDesc($Property, Element $First, Element $Second);

    /**
     * @param string  $Property Entity-Attribute (Getter)
     * @param Element $First
     * @param Element $Second
     *
     * @return bool
     */
    final protected function isSortable($Property, Element $First, Element $Second)
    {

        if (method_exists($First, 'get'.$Property) && method_exists($Second, 'get'.$Property)) {
            return true;
        }
        return false;
    }

    /**
     * @param string  $Property Entity-Attribute (Getter)
     * @param Element $Element
     *
     * @return mixed
     */
    final protected function getValue($Property, Element $Element)
    {

        return $Element->{'get'.$Property}();
    }
}
