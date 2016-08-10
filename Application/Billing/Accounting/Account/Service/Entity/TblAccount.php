<?php
namespace SPHERE\Application\Billing\Accounting\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Account\Account;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblAccount extends Element
{

    const ATTR_IS_ACTIVE = 'IsActive';
    const ATTR_TBL_ACCOUNT_TYPE = 'tblAccountType';
    const ATTR_TBL_ACCOUNT_KEY = 'tblAccountKey';

    /**
     * @Column(type="string")
     */
    protected $Number;
    /**
     * @Column(type="text")
     */
    protected $Description;
    /**
     * @Column(type="boolean")
     */
    protected $IsActive;
    /**
     * @Column(type="bigint")
     */
    protected $tblAccountType;
    /**
     * @Column(type="bigint")
     */
    protected $tblAccountKey;

    /**
     * @return string
     */
    public function getNumber()
    {

        return $this->Number;
    }

    /**
     * @param string $Number
     */
    public function setNumber($Number)
    {

        $this->Number = $Number;
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

    /**
     * @return boolean $IsActive
     */
    public function isActive()
    {

        return $this->IsActive;
    }

    /**
     * @param boolean $IsActive
     */
    public function setActive($IsActive)
    {

        $this->IsActive = $IsActive;
    }

    /**
     * @return bool|TblAccountType
     */
    public function getTblAccountType()
    {

        if (null === $this->tblAccountType) {
            return false;
        } else {
            return Account::useService()->getAccountTypeById($this->tblAccountType);
        }
    }

    /**
     * @param null|TblAccountType $tblAccountType
     */
    public function setTblAccountType(TblAccountType $tblAccountType = null)
    {

        $this->tblAccountType = ( null === $tblAccountType ? null : $tblAccountType->getId() );
    }

    /**
     * @return bool|TblAccountKey
     */
    public function getTblAccountKey()
    {

        if (null === $this->tblAccountKey) {
            return false;
        } else {
            return Account::useService()->getAccountKeyById($this->tblAccountKey);
        }
    }

    /**
     * @param null|TblAccountKey $tblAccountKey
     */
    public function setTblAccountKey(TblAccountKey $tblAccountKey = null)
    {

        $this->tblAccountKey = ( null === $tblAccountKey ? null : $tblAccountKey->getId() );
    }

}
