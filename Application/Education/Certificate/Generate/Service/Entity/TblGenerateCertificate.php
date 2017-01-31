<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 12:02
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGenerateCertificate")
 * @Cache(usage="READ_ONLY")
 */
class TblGenerateCertificate extends Element
{

    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_SERVICE_TBL_CERTIFICATE_TYPE = 'serviceTblCertificateType';

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCertificateType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAppointedDateTask;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblBehaviorTask;

    /**
     * @Column(type="string")
     */
    protected $HeadmasterName;

    /**
     * @Column(type="boolean")
     */
    protected $IsDivisionTeacherAvailable;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

    /**
     * @return string
     */
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {

        if (null === $this->serviceTblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->serviceTblYear);
        }
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear = null)
    {

        $this->serviceTblYear = (null === $tblYear ? null : $tblYear->getId());
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
     * @return bool|TblTask
     */
    public function getServiceTblAppointedDateTask()
    {

        if (null === $this->serviceTblAppointedDateTask) {
            return false;
        } else {
            return Evaluation::useService()->getTaskById($this->serviceTblAppointedDateTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setServiceTblAppointedDateTask(TblTask $tblTask = null)
    {

        $this->serviceTblAppointedDateTask = (null === $tblTask ? null : $tblTask->getId());
    }

    /**
     * @return bool|TblTask
     */
    public function getServiceTblBehaviorTask()
    {

        if (null === $this->serviceTblBehaviorTask) {
            return false;
        } else {
            return Evaluation::useService()->getTaskById($this->serviceTblBehaviorTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setServiceTblBehaviorTask(TblTask $tblTask = null)
    {

        $this->serviceTblBehaviorTask = (null === $tblTask ? null : $tblTask->getId());
    }

    /**
     * @return mixed
     */
    public function getHeadmasterName()
    {
        return $this->HeadmasterName;
    }

    /**
     * @param mixed $HeadmasterName
     */
    public function setHeadmasterName($HeadmasterName)
    {
        $this->HeadmasterName = $HeadmasterName;
    }

    /**
     * @return boolean
     */
    public function isDivisionTeacherAvailable()
    {
        return $this->IsDivisionTeacherAvailable;
    }

    /**
     * @param boolean $IsDivisionTeacherAvailable
     */
    public function setIsDivisionTeacherAvailable($IsDivisionTeacherAvailable)
    {
        $this->IsDivisionTeacherAvailable = (boolean) $IsDivisionTeacherAvailable;
    }

    /**
     * @return bool|TblCertificateType
     */
    public function getServiceTblCertificateType()
    {

        if (null === $this->serviceTblCertificateType) {
            return false;
        } else {
            return Generator::useService()->getCertificateTypeById($this->serviceTblCertificateType);
        }
    }

    /**
     * @param TblCertificateType|null $tblCertificateType
     */
    public function setServiceTblCertificateType(TblCertificateType $tblCertificateType = null)
    {

        $this->serviceTblCertificateType = (null === $tblCertificateType ? null : $tblCertificateType->getId());
    }
}