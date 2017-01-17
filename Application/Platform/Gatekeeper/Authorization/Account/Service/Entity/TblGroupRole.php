<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblGroupRole")
 * @Cache(usage="READ_ONLY")
 */
class TblGroupRole extends Element
{

    const ATTR_TBL_GROUP = 'tblGroup';
    const SERVICE_TBL_ROLE = 'serviceTblRole';

    /**
     * @Column(type="bigint")
     */
    protected $tblGroup;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblRole;

    /**
     * @return bool|TblGroup
     */
    public function getTblGroup()
    {

        if (null === $this->tblGroup) {
            return false;
        } else {
            return Account::useService()->getGroupById($this->tblGroup);
        }
    }

    /**
     * @param null|TblGroup $tblGroup
     */
    public function setTblGroup(TblGroup $tblGroup = null)
    {

        $this->tblGroup = ( null === $tblGroup ? null : $tblGroup->getId() );
    }

    /**
     * @return bool|TblRole
     */
    public function getServiceTblRole()
    {

        if (null === $this->serviceTblRole) {
            return false;
        } else {
            return Access::useService()->getRoleById($this->serviceTblRole);
        }
    }

    /**
     * @param null|TblRole $tblRole
     */
    public function setServiceTblRole(TblRole $tblRole = null)
    {

        $this->serviceTblRole = ( null === $tblRole ? null : $tblRole->getId() );
    }
}
