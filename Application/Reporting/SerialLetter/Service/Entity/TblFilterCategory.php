<?php
namespace SPHERE\Application\Reporting\SerialLetter\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblFilterCategory")
 * @Cache(usage="READ_ONLY")
 */
class TblFilterCategory extends Element
{

    const IDENTIFIER_PERSON_GROUP = 'Personengruppe';
    const IDENTIFIER_PERSON_GROUP_STUDENT = 'Schüler';
    const IDENTIFIER_PERSON_GROUP_PROSPECT = 'Interessenten';
    const IDENTIFIER_COMPANY_GROUP = 'Firmengruppe';

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;

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
}