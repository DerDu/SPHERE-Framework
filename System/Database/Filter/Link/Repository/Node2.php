<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Fitting\Element;

class Node2 extends AbstractNode
{

    protected function parseResult($List)
    {

        array_walk($List[1], function (Element $Node1) use (&$Result, $List) {

            array_walk($List[0], function (Element $Node0) use (&$Result, $Node1) {

                $Data0 = $Node0->__toArray();
                $Data1 = $Node1->__toArray();

                if (
                ( $Data0[$this->getPath(0)[1]] == $Data1[$this->getPath(1)[0]] )
                ) {
                    $Result[] = array(
                        $Node0,
                        $Node1,
                    );
                }

            });
        });
        return $Result;
    }
}
