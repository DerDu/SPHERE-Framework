<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Fitting\Element;

class Node1 extends AbstractNode
{

    protected function parseResult($List)
    {

        array_walk($List[0], function (Element $Node0) use (&$Result) {

            $Result[] = array(
                $Node0,
            );
        });
        return $Result;
    }
}
