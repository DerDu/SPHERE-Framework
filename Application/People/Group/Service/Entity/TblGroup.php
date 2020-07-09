<?php
namespace SPHERE\Application\People\Group\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblGroup")
 * @Cache(usage="READ_ONLY")
 */
class TblGroup extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_IS_LOCKED = 'IsLocked';
    const ATTR_IS_CORE_GROUP = 'IsCoreGroup';
    const ATTR_META_TABLE = 'MetaTable';

    const META_TABLE_COMMON = 'COMMON';
    const META_TABLE_PROSPECT = 'PROSPECT';
    const META_TABLE_STUDENT = 'STUDENT';
    const META_TABLE_CUSTODY = 'CUSTODY';
    const META_TABLE_STAFF = 'STAFF';
    const META_TABLE_TEACHER = 'TEACHER';
    const META_TABLE_CLUB = 'CLUB';
    const META_TABLE_COMPANY_CONTACT = 'COMPANY_CONTACT';
    const META_TABLE_TUDOR = 'TUDOR';
    const META_TABLE_DEBTOR = 'DEBTOR';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;
    /**
     * @Column(type="string")
     */
    protected $MetaTable;
    /**
     * @Column(type="boolean")
     */
    protected $IsCoreGroup;

    /**
     * @param $Name
     */
    public function __construct($Name)
    {

        $this->setName($Name);
    }

    /**
     * @return string
     */
    public function getDescription($isShowCoreInfo = false)
    {
        if($isShowCoreInfo && $this->isCoreGroup()){
            return $this->Description.new Muted(' (Stammgruppe)');
        }
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return string
     */
    public function getRemark()
    {

        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {

        return (bool)$this->IsLocked;
    }

    /**
     * @param bool $IsLocked
     */
    public function setLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }

    /**
     * @return string
     */
    public function getMetaTable()
    {

        return $this->MetaTable;
    }

    /**
     * @param string $MetaTable
     */
    public function setMetaTable($MetaTable)
    {

        $this->MetaTable = $MetaTable;
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

        $this->Name = $Name;
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getTudors()
    {

        return Group::useService()->getTudors($this);
    }

    /**
     * @return bool
     */
    public function isCoreGroup()
    {

        return (bool)$this->IsCoreGroup;
    }

    /**
     * @param bool $IsCoreGroup
     */
    public function setCoreGroup($IsCoreGroup)
    {

        $this->IsCoreGroup = (bool)$IsCoreGroup;
    }
}
