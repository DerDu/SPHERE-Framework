<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Database\Filter\Link\Probe;

/**
 * Class Node5
 *
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node5 extends AbstractNode
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
                            $Key = $Node2->__get($this->getPath(2)[1]);
                            if (!($MatchList = $this->filterNodeList($Key, $List, 3))) {
                                if (!isset($SearchList[3]) || empty($SearchList[3])) {
                                    $Node3 = (new \ReflectionObject($ProbeList[3]->getEntity()))->newInstanceWithoutConstructor();
                                    $Node3->__set($this->getPath(3)[0], $Key);
                                    $MatchList = array(
                                        $Node3
                                    );
                                }
                            }
                            if (!empty($MatchList)) {
                                /** @var AbstractView $Node3 */
                                foreach ($MatchList as $Node3) {
                                    $Key = $Node3->__get($this->getPath(3)[1]);
                                    if (!($MatchList = $this->filterNodeList($Key, $List, 4))) {
                                        if (!isset($SearchList[4]) || empty($SearchList[4])) {
                                            $Node4 = (new \ReflectionObject($ProbeList[4]->getEntity()))->newInstanceWithoutConstructor();
                                            $Node4->__set($this->getPath(4)[0], $Key);
                                            $MatchList = array(
                                                $Node4
                                            );
                                        }
                                    }
                                    if (!empty($MatchList)) {
                                        /** @var AbstractView $Node4 */
                                        foreach ($MatchList as $Node4) {
                                            $Result[] = array(
                                                $Node0,
                                                $Node1,
                                                $Node2,
                                                $Node3,
                                                $Node4,
                                            );
                                            if ($this->checkTimeout()) {
                                                throw new NodeException();
                                            }
                                        }
                                    }
                                }
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
                            $Key = $Node2->__get($this->getPath(2)[1]);
                            if (($MatchList = $this->filterNodeList($Key, $List, 3))) {
                                /** @var AbstractView $Node3 */
                                foreach ($MatchList as $Node3) {
                                    $Key = $Node3->__get($this->getPath(3)[1]);
                                    if (($MatchList = $this->filterNodeList($Key, $List, 4))) {
                                        /** @var AbstractView $Node4 */
                                        foreach ($MatchList as $Node4) {
                                            $Result[] = array(
                                                $Node0,
                                                $Node1,
                                                $Node2,
                                                $Node3,
                                                $Node4,
                                            );
                                            if ($this->checkTimeout()) {
                                                throw new NodeException();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $Result;
    }
}
