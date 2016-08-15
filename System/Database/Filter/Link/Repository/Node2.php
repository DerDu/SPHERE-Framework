<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\AbstractNode;

/**
 * Class Node2
 *
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node2 extends AbstractNode
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
                        $Result[] = array(
                            $Node0,
                            $Node1,
                        );
                        if ($this->checkTimeout()) {
                            throw new NodeException();
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
