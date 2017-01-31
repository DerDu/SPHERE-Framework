<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:41
 */

namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateType")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Identifier;

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
}
