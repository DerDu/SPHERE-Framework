<?php
namespace SPHERE\Application\Reporting\Dynamic\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDynamicFilter")
 * @Cache(usage="READ_ONLY")
 */
class TblDynamicFilter extends Element
{

    const SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const PROPERTY_FILTER_NAME = 'FilterName';
    const PROPERTY_IS_PUBLIC = 'IsPublic';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="string")
     */
    protected $FilterName;
    /**
     * @Column(type="boolean")
     */
    protected $IsPublic;

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccount()
    {

        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceTblAccount);
        }
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccount = ( null === $tblAccount ? null : $tblAccount->getId() );
    }

    /**
     * @return string
     */
    public function getFilterName()
    {

        return $this->FilterName;
    }

    /**
     * @param string $FilterName
     *
     * @return TblDynamicFilter
     */
    public function setFilterName($FilterName)
    {

        $this->FilterName = $FilterName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {

        return (bool)$this->IsPublic;
    }

    /**
     * @param bool $IsPublic
     *
     * @return TblDynamicFilter
     */
    public function setPublic($IsPublic)
    {

        $this->IsPublic = (bool)$IsPublic;
        return $this;
    }
}
