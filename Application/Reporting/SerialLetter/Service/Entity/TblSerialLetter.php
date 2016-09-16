<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2016
 * Time: 14:53
 */

namespace SPHERE\Application\Reporting\SerialLetter\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblSerialLetter")
 * @Cache(usage="READ_ONLY")
 */
class TblSerialLetter extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Description;

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
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }
}