<?php
namespace SPHERE\Application\Corporation\Company\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCompany")
 * @Cache(usage="READ_ONLY")
 */
class TblCompany extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_EXTENDED_NAME = 'ExtendedName';
    const ATTR_DESCRIPTION = 'Description';
    const ATTR_IMPORT_ID = 'ImportId';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @Column(type="string")
     */
    protected $ImportId;

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
    public function getExtendedName()
    {

        return $this->ExtendedName;
    }

    /**
     * @param string $ExtendedName
     */
    public function setExtendedName($ExtendedName)
    {

        $this->ExtendedName = $ExtendedName;
    }

    /**
     * @return bool|TblGroup[]
     */
    public function fetchTblGroupAll()
    {

        return Group::useService()->getGroupAllByCompany($this);
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

    public function getDisplayName()
    {

        return $this->Name.( $this->ExtendedName != '' ? ' '.$this->ExtendedName : null );
    }

    /**
     * @return string
     */
    public function getImportId()
    {

        return $this->ImportId;
    }

    /**
     * @param string $ImportId
     */
    public function setImportId($ImportId)
    {

        $this->ImportId = $ImportId;
    }

    /**
     * @return bool|TblAddress
     */
    public function fetchMainAddress()
    {

        return Address::useService()->getAddressByCompany($this);
    }
}
