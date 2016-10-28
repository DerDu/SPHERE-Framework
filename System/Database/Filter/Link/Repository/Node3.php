<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Database\Filter\Link\Probe;

/**
 * Class Node3
 *
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node3 extends AbstractNode
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
            $Key = $Node0->__get($this->getPath(0)[1]);
            if (!($MatchList = $this->filterNodeList($Key, $List, 1))) {
                if (!isset($SearchList[1]) || empty($SearchList[1])) {
                    $Node1 = (new \ReflectionObject($ProbeList[1]->getEntity()))->newInstanceWithoutConstructor();
                    $Node1->__set($this->getPath(1)[0], $Key);
                    $MatchList = array(
                        $Node1
                    );
                }
            }
            if (!empty($MatchList)) {
                /** @var AbstractView $Node1 */
                foreach ($MatchList as $Node1) {
                    $Key = $Node1->__get($this->getPath(1)[1]);
                    if (!($MatchList = $this->filterNodeList($Key, $List, 2))) {
                        if (!isset($SearchList[2]) || empty($SearchList[2])) {
                            $Node2 = (new \ReflectionObject($ProbeList[2]->getEntity()))->newInstanceWithoutConstructor();
                            $Node2->__set($this->getPath(2)[0], $Key);
                            $MatchList = array(
                                $Node2
                            );
                        }
                    }
                    if (!empty($MatchList)) {
                        /** @var AbstractView $Node2 */
                        foreach ($MatchList as $Node2) {
                            $Result[] = array(
                                $Node0,
                                $Node1,
                                $Node2,
                            );
                            if ($this->checkTimeout()) {
                                throw new NodeException();
                            }
                        }
                    }
                }
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
            $Key = $Node0->__get($this->getPath(0)[1]);
            if (($MatchList = $this->filterNodeList($Key, $List, 1))) {
                /** @var AbstractView $Node1 */
                foreach ($MatchList as $Node1) {
                    $Key = $Node1->__get($this->getPath(1)[1]);
                    if (($MatchList = $this->filterNodeList($Key, $List, 2))) {
                        /** @var AbstractView $Node2 */
                        foreach ($MatchList as $Node2) {
                            $Result[] = array(
                                $Node0,
                                $Node1,
                                $Node2,
                            );
                            if ($this->checkTimeout()) {
                                throw new NodeException();
                            }
                        }
                    }
                }
            }
        }
        return $Result;
    }
}
