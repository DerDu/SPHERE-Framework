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
 * @Table(name="tblAccountKey")
 * @Cache(usage="READ_ONLY")
 */
class TblAccountKey extends Element
{

    const ATTR_TBL_ACCOUNT_KEY_TYPE = 'tblAccountKeyType';

    /**
     * @Column(type="date")
     */
    protected $ValidFrom;
    /**
     * @Column(type="string")
     */
    protected $Value;
    /**
     * @Column(type="date")
     */
    protected $ValidTo;
    /**
     * @Column(type="text")
     */
    protected $Description;
    /**
     * @Column(type="integer")
     */
    protected $Code;
    /**
     * @Column(type="bigint")
     */
    protected $tblAccountKeyType;

    /**
     * @return string $validFrom
     */
    public function getValidFrom()
    {

        return $this->ValidFrom;
    }

    /**
     * @param \DateTime $ValidFrom
     */
    public function setValidFrom($ValidFrom)
    {

        $this->ValidFrom = $ValidFrom;
    }

    /**
     * @return string $Value
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return string $ValidTo
     */
    public function getValidTo()
    {

        return $this->ValidTo;
    }

    /**
     * @param \DateTime $ValidTo
     */
    public function setValidTo($ValidTo)
    {

        $this->ValidTo = $ValidTo;
    }

    /**
     * @return string $Description
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
     * @return integer $Code
     */
    public function getCode()
    {

        return $this->Code;
    }

    /**
     * @param integer $Code
     */
    public function setCode($Code)
    {

        $this->Code = $Code;
    }

    /**
     * @return bool|TblAccountKeyType
     */
    public function getTblAccountKey()
    {

        if (null === $this->tblAccountKeyType) {
            return false;
        } else {
            return Account::useService()->getAccountKeyTypeById($this->tblAccountKeyType);
        }
    }

    /**
     * @param bool|TblAccountKeyType $tblAccountKeyType
     */
    public function setTblAccountKeyType(TblAccountKeyType $tblAccountKeyType = null)
    {

        $this->tblAccountKeyType = ( null === $tblAccountKeyType ? null : $tblAccountKeyType->getId() );
    }
}
