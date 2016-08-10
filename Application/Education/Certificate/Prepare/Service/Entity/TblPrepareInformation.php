<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.07.2016
 * Time: 09:48
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblPrepareInformation")
 * @Cache(usage="READ_ONLY")
 */
class TblPrepareInformation extends Element
{

    const ATTR_TBL_PREPARE_CERTIFICATE = 'tblPrepareCertificate';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_FIELD = 'Field';

    /**
     * @Column(type="bigint")
     */
    protected $tblPrepareCertificate;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="text")
     */
    protected $Value;

    /**
     * @Column(type="string")
     */
    protected $Field;

    /**
     * @return false|TblPrepareCertificate
     */
    public function gettblPrepareCertificate()
    {

        if (null === $this->tblPrepareCertificate) {
            return false;
        } else {
            return Prepare::useService()->getPrepareById($this->tblPrepareCertificate);
        }
    }

    /**
     * @param TblPrepareCertificate|null $tblPrepareCertificate
     */
    public function settblPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate = null)
    {

        $this->tblPrepareCertificate = (null === $tblPrepareCertificate ? null : $tblPrepareCertificate->getId());
    }

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
     * @return string
     */
    public function getField()
    {
        return $this->Field;
    }

    /**
     * @param string $Field
     */
    public function setField($Field)
    {
        $this->Field = $Field;
    }
}