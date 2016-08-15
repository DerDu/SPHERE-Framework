<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\AbstractNode;

/**
 * Class Node6
 *
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node6 extends AbstractNode
{

    /**
     * @param AbstractView[][] $List
     * @param int              $Timeout
     *
     * @return mixed
     */
    protected function parseResult($List, $Timeout = 60)
    {

        $this->setTimeout($Timeout);

        $Result = array();
        try {
            /** @var AbstractView $Node0 */
            foreach ($List[0] as $Node0) {
                $Key = $Node0->__get($this->getPath(0)[1]);
                if (( $MatchList = $this->filterNodeList($Key, $List, 1) )) {
                    /** @var AbstractView $Node1 */
                    foreach ($MatchList as $Node1) {
                        $Key = $Node1->__get($this->getPath(1)[1]);
                        if (( $MatchList = $this->filterNodeList($Key, $List, 2) )) {
                            /** @var AbstractView $Node2 */
                            foreach ($MatchList as $Node2) {
                                $Key = $Node2->__get($this->getPath(2)[1]);
                                if (( $MatchList = $this->filterNodeList($Key, $List, 3) )) {
                                    /** @var AbstractView $Node3 */
                                    foreach ($MatchList as $Node3) {
                                        $Key = $Node3->__get($this->getPath(3)[1]);
                                        if (( $MatchList = $this->filterNodeList($Key, $List, 4) )) {
                                            /** @var AbstractView $Node4 */
                                            foreach ($MatchList as $Node4) {
                                                $Key = $Node4->__get($this->getPath(4)[1]);
                                                if (( $MatchList = $this->filterNodeList($Key, $List, 5) )) {
                                                    /** @var AbstractView $Node5 */
                                                    foreach ($MatchList as $Node5) {
                                                        $Result[] = array(
                                                            $Node0,
                                                            $Node1,
                                                            $Node2,
                                                            $Node3,
                                                            $Node4,
                                                            $Node5,
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
                }
            }
        } catch (NodeException $E) {
            return $Result;
        }

        return $Result;
    }
}
