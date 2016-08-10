<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;

/**
 * Class Pile
 *
 * @package SPHERE\System\Database\Filter\Pile
 */
class Pile
{

    /** @var array $PileList */
    private $PileList = array();

    /**
     * @param AbstractService $Service
     * @param Element         $Entity
     * @param null|string     $ParentProperty
     * @param null|string     $ChildProperty
     *
     * @return $this
     */
    public function addPile(AbstractService $Service, Element $Entity, $ParentProperty, $ChildProperty)
    {

        $this->PileList[] = array($Service, $Entity, $ParentProperty, $ChildProperty);
        return $this;
    }

    /**
     * @param array $Search array( PileIndex => array( 'Column' => array( 'Value', ... ), ... ), ... )
     *
     * @return array
     * @throws \Exception
     */
    public function searchPile($Search)
    {

        $Node = '\SPHERE\System\Database\Filter\Link\Repository\Node'.count($this->PileList);
        if (!class_exists($Node)) {
            throw new \Exception('No valid Search-Class for '.count($this->PileList).' Nodes found');
        } else {
            (new DebuggerFactory())->createLogger(new BenchmarkLogger())->addLog(
                'Valid Search-Class for '.count($this->PileList).' Nodes found'
            );
        }
        /** @var AbstractNode $Node */
        $Node = new $Node();
        foreach ($this->PileList as $Pile) {
            $Node->addProbe($Pile[0], $Pile[1]);
            $Node->addPath($Pile[2], $Pile[3]);
        }
        return $Node->searchData($Search);
    }
}
