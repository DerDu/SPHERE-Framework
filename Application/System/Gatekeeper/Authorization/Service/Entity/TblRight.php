<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblRight")
 */
class TblRight extends Element
{

    const ATTR_ROUTE = 'Route';

    /**
     * @Column(type="string")
     */
    protected $Route;

    /**
     * @param string $Route
     */
    function __construct( $Route )
    {

        $this->Route = $Route;
    }

    /**
     * @return string
     */
    public function getRoute()
    {

        return $this->Route;
    }

    /**
     * @param string $Route
     */
    public function setRoute( $Route )
    {

        $this->Route = $Route;
    }
}
