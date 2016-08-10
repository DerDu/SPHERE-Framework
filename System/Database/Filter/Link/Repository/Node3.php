<?php
namespace SPHERE\System\Database\Filter\Link\Repository;

use SPHERE\System\Database\Filter\Link\AbstractNode;
use SPHERE\System\Database\Fitting\Element;

class Node3 extends AbstractNode
{

    protected function parseResult($List)
    {

        array_walk($List[2], function (Element $Node2) use (&$Result, $List) {

            array_walk($List[1], function (Element $Node1) use (&$Result, $List, $Node2) {

                array_walk($List[0], function (Element $Node0) use (&$Result, $Node1, $Node2) {

                    $Data0 = $Node0->__toArray();
                    $Data1 = $Node1->__toArray();
                    $Data2 = $Node2->__toArray();

                    if (
                        ( $Data0[$this->getPath(0)[1]] == $Data1[$this->getPath(1)[0]] )
                        && ( $Data1[$this->getPath(1)[1]] == $Data2[$this->getPath(2)[0]] )
                    ) {
                        $Result[] = array(
                            $Node0,
                            $Node1,
                            $Node2,
                        );
                    }

                });
            });
        });
        return $Result;
    }
}
