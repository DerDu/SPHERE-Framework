<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCity")
 * @Cache(usage="READ_ONLY")
 */
class TblCity extends Element
{

    const ATTR_CODE = 'Code';
    const ATTR_NAME = 'Name';
    const ATTR_DISTRICT = 'District';

    /**
     * @Column(type="string")
     */
    protected $Code;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $District;

    /**
     * @return string
     */
    public function getCode()
    {

        return $this->Code;
    }

    /**
     * @param string $Code
     */
    public function setCode($Code)
    {

        $this->Code = $Code;
    }

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

        $this->Name = trim($Name);
    }

    /**
     * @return string
     */
    public function getDistrict()
    {

        return $this->District;
    }

    /**
     * @param string $District
     */
    public function setDistrict($District)
    {

        $this->District = trim($District);
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        $district = trim($this->District);
        if ($district !== '') {
            return $this->Name . ' OT ' . preg_replace('!^OT\s!is', '', $district);
        }

        return $this->Name;
    }

    /**
     * Display District with "OT District"
     * @return string
     */
    public function getDisplayDistrict()
    {

        $district = trim($this->District);
        if ($district !== '') {
            return 'OT '.preg_replace('!^OT\s!is', '', $district);
        }
        return $district;
    }
}
