<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\AbstractNode;

/**
 * Class Node1
 *
 * @package SPHERE\System\Database\Filter\Link\Repository
 */
class Node1 extends AbstractNode
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
                $Result[] = array(
                    $Node0,
                );
                if ($this->checkTimeout()) {
                    throw new NodeException();
                }
            }
        } catch (NodeException $E) {
            return $Result;
        }

        return $Result;
    }
}
