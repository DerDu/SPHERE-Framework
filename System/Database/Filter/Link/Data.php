<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Filter\Logic\AbstractLogic;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\System\Database\Filter\Link
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {

    }

    /**
     * Internal
     *
     * @param Element       $Entity
     * @param AbstractLogic $Logic
     *
     * @return Element[]
     */
    final public function getEntityAllByLogic(Element $Entity, AbstractLogic $Logic)
    {

        return parent::getEntityAllByLogic($Entity, $Logic);
    }

    /**
     * Internal
     *
     * @param Element       $Entity
     * @param AbstractLogic $Logic
     * @param string        $Column
     *
     * @return array
     */
    final public function getColumnAllByLogic(Element $Entity, AbstractLogic $Logic, $Column = 'Id')
    {

        return parent::getColumnAllByLogic($Entity, $Logic, $Column);
    }
}
