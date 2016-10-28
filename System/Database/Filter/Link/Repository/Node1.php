<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Database\Filter\Link\Probe;

/**
 * Class Node1
 *
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node1 extends AbstractNode
{

    /**
     * @param array $List
     * @param array $ProbeList
     * @param array $SearchList
     *
     * @return array
     *
     * @throws NodeException
     */
    protected function outerJoin($List, $ProbeList = array(), $SearchList = array())
    {
        $Result = array();
        /** @var AbstractView $Node0 */
        foreach ($List[0] as $Node0) {
            $Result[] = array(
                $Node0,
            );
            if ($this->checkTimeout()) {
                throw new NodeException();
            }
        }
        return $Result;
    }

    /**
     * @param array $List
     *
     * @return array
     *
     * @throws NodeException
     */
    protected function innerJoin($List)
    {
        $Result = array();
        /** @var AbstractView $Node0 */
        foreach ($List[0] as $Node0) {
            $Result[] = array(
                $Node0,
            );
            if ($this->checkTimeout()) {
                throw new NodeException();
            }
        }
        return $Result;
    }
}
