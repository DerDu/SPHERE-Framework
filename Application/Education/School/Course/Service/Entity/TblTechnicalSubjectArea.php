<?php

namespace SPHERE\Application\Education\School\Course\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblTechnicalSubjectArea")
 * @Cache(usage="READ_ONLY")
 */
class TblTechnicalSubjectArea extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_ACRONYM = 'Acronym';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Acronym;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getAcronym()
    {
        return $this->Acronym;
    }

    /**
     * @param string $Acronym
     */
    public function setAcronym($Acronym)
    {
        $this->Acronym = $Acronym;
    }
}