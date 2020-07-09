<?php
namespace SPHERE\Application\Contact\Mail\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblToPerson")
 * @Cache(usage="READ_ONLY")
 */
class TblToPerson extends Element
{

    const ATT_TBL_TYPE = 'tblType';
    const ATT_TBL_MAIL = 'tblMail';
    const SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATT_IS_ACCOUNT_USER_ALIAS = 'IsAccountUserAlias';

    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblType;
    /**
     * @Column(type="bigint")
     */
    protected $tblMail;

    /**
     * @Column(type="boolean")
     */
    protected $IsAccountUserAlias;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
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
     * @return bool|TblType
     */
    public function getTblType()
    {

        if (null === $this->tblType) {
            return false;
        } else {
            return Mail::useService()->getTypeById($this->tblType);
        }
    }

    /**
     * @param null|TblType $tblType
     */
    public function setTblType(TblType $tblType = null)
    {

        $this->tblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblMail
     */
    public function getTblMail()
    {

        if (null === $this->tblMail) {
            return false;
        } else {
            return Mail::useService()->getMailById($this->tblMail);
        }
    }

    /**
     * @param null|TblMail $tblMail
     */
    public function setTblMail(TblMail $tblMail = null)
    {

        $this->tblMail = ( null === $tblMail ? null : $tblMail->getId() );
    }

    /**
     * @return boolean
     */
    public function isAccountUserAlias()
    {
        return $this->IsAccountUserAlias;
    }

    /**
     * @param boolean $IsAccountUserAlias
     */
    public function setIsAccountUserAlias($IsAccountUserAlias)
    {
        $this->IsAccountUserAlias = $IsAccountUserAlias;
    }
}
