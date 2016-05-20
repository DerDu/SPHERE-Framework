<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.05.2016
 * Time: 08:15
 */

namespace SPHERE\Application\People\Meta\Teacher\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblTeacher")
 * @Cache(usage="READ_ONLY")
 */
class TblTeacher extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_ACRONYM = 'Acronym';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="string")
     */
    protected $Acronym;

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
    public function getAcronym()
    {
        return $this->Acronym;
    }

    /**
     * @param string $Acronym
     */
    public function setAcronym($Acronym)
    {
        $this->Acronym = $Acronym;
    }

}