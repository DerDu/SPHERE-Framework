<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\AbstractNode;

/**
 * Class Node4
 *
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node4 extends AbstractNode
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
                                        $Result[] = array(
                                            $Node0,
                                            $Node1,
                                            $Node2,
                                            $Node3,
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
        } catch (NodeException $E) {
            return $Result;
        }

        return $Result;
    }
}
