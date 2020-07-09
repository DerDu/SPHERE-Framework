<?php
namespace SPHERE\Application\Setting\Univention\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblUnivention")
 * @Cache(usage="READ_ONLY")
 */
class TblUnivention extends Element
{

    const ATTR_TYPE = 'Type';

    const TYPE_VALUE_TOKEN = 'authorization';
    const TYPE_VALUE_USER = 'username';
    const TYPE_VALUE_PW = 'password';
    const TYPE_VALUE_SERVER = 'server';

    /**
     * @Column(type="string")
     */
    protected $Type;

    /**
     * @Column(type="text")
     */
    protected $Value;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param string $Type
     */
    public function setType($Type)
    {
        $this->Type = $Type;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }
}