<?php
namespace SPHERE\System\Extension\Repository;

use SPHERE\System\Extension\Repository\Sorter\ISorterInterface;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

/**
 * Class Sorter
 *
 * @package SPHERE\System\Extension\Repository
 */
class Sorter
{

    const ORDER_ASC = 1;
    const ORDER_DESC = -1;

    /** @var array $List */
    private $List = array();

    /**
     * @param array $List
     *
     * @throws \Exception
     */
    public function __construct($List)
    {

        $this->List = $List;
    }

    /**
     * Natural String Compare
     *
     * @param string|array $PropertyList
     * @param int          $Order
     *
     * @deprecated use sortObjectBy with Sorter
     * @return array
     */
    public function sortObjectList($PropertyList, $Order = Sorter::ORDER_ASC)
    {

        return $this->sortObjectBy($PropertyList, new StringNaturalOrderSorter(), $Order);
    }

    /**
     * @param string                $Property Entity-Attribute (Getter)
     * @param ISorterInterface|null $Sorter
     * @param int                   $Order
     *
     * @return array
     */
    public function sortObjectBy($Property, ISorterInterface $Sorter = null, $Order = Sorter::ORDER_ASC)
    {

        if (null === $Sorter) {
            $Sorter = new StringNaturalOrderSorter();
        }

        usort($this->List, function ($A, $B) use ($Property, $Order, $Sorter) {

            switch ($Order) {
                case self::ORDER_ASC:
                    return $Sorter->sortAsc($Property, $A, $B);
                case self::ORDER_DESC:
                    return $Sorter->sortDesc($Property, $A, $B);
            }
            return 0;
        });

        return $this->List;
    }
}
