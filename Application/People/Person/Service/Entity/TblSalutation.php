<?php
namespace SPHERE\Application\People\Person\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSalutation")
 * @Cache(usage="READ_ONLY")
 */
class TblSalutation extends Element
{

    const ATTR_SALUTATION = 'Salutation';
    const VALUE_MAN = 'Herr';
    const VALUE_WOMAN = 'Frau';
    const VALUE_STUDENT = 'Schüler';

    /**
     * @Column(type="string")
     */
    protected $Salutation;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;

    /**
     * @param string $Salutation
     */
    public function __construct($Salutation)
    {

        $this->Salutation = $Salutation;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {

        return $this->Salutation;
    }

    /**
     * @param string $Salutation
     */
    public function setSalutation($Salutation)
    {

        $this->Salutation = $Salutation;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {

        return $this->IsLocked;
    }

    /**
     * @param bool $IsLocked
     */
    public function setLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }
}
