<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Account\Account;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItemAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblItemAccount extends Element
{

    const ATTR_TBL_ITEM = 'tblItem';
    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';

    /**
     * @Column(type="bigint")
     */
    protected $tblItem;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;

    /**
     * @return bool|TblItem
     */
    public function getTblItem()
    {

        if (null === $this->tblItem) {
            return false;
        } else {
            return Item::useService()->getItemById($this->tblItem);
        }
    }

    /**
     * @param null|TblItem $tblItem
     */
    public function setTblItem(TblItem $tblItem = null)
    {

        $this->tblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceBillingAccount()
    {

        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceTblAccount);
        }
    }

    /**
     * @param TblAccount $tblAccount
     */
    public function setTblAccount(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccount = ( null === $tblAccount ? null : $tblAccount->getId() );
    }
}
