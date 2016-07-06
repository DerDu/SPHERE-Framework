<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreType")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_IDENTIFIER = 'Identifier';
    const ATTR_PATTERN = 'Pattern';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Identifier;

    /**
     * @Column(type="string")
     */
    protected $Pattern;

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
    public function getIdentifier()
    {

        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->Pattern;
    }

    /**
     * @param string $Pattern
     */
    public function setPattern($Pattern)
    {
        $this->Pattern = $Pattern;
    }

}
