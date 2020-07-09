<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificate")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificate extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_CERTIFICATE = 'Certificate';
    const SERVICE_TBL_CONSUMER = 'serviceTblConsumer';
    const SERVICE_TBL_COURSE = 'serviceTblCourse';
    const SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';
    const ATTR_IS_GRADE_INFORMATION = 'IsGradeInformation';
    const ATTR_TBL_CERTIFICATE_TYPE = 'tblCertificateType';
    const ATTR_IS_INFORMATION = 'IsInformation';
    const ATTR_IS_CHOSEN_DEFAULT = 'IsChosenDefault';
    const ATTR_IS_IGNORED_FOR_AUTO_SELECT = 'IsIgnoredForAutoSelect';
    const ATTR_IS_GRADE_VERBAL = 'IsGradeVerbal';

    const CERTIFICATE_TYPE_PRIMARY = 'Primary';
    const CERTIFICATE_TYPE_SECONDARY = 'Secondary';
    const CERTIFICATE_TYPE_GYM = 'Gym';
    const CERTIFICATE_TYPE_BERUFSFACHSCHULE = 'Berufsfachschule';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblConsumer;

    /**
     * @Column(type="string")
     */
    protected $Certificate;

    /**
     * @Column(type="boolean")
     */
    protected $IsGradeInformation;

    /**
     * @Column(type="bigint")
     */
    protected $tblCertificateType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCourse;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSchoolType;

    /**
     * @Column(type="boolean")
     */
    protected $IsInformation;

    /**
     * @Column(type="boolean")
     */
    protected $IsChosenDefault;

    /**
     * @Column(type="boolean")
     */
    protected $IsIgnoredForAutoSelect;

    /**
     * @Column(type="boolean")
     */
    protected $IsGradeVerbal;

    /**
     * @return bool|TblConsumer
     */
    public function getServiceTblConsumer()
    {

        if (null === $this->serviceTblConsumer) {
            return false;
        } else {
            return Consumer::useService()->getConsumerById($this->serviceTblConsumer);
        }
    }

    /**
     * @param TblConsumer|null $serviceTblConsumer
     */
    public function setServiceTblConsumer($serviceTblConsumer)
    {

        $this->serviceTblConsumer = ( null === $serviceTblConsumer ? null : $serviceTblConsumer->getId() );
    }

    /**
     * @param TblPerson   $tblPerson
     * @param TblDivision $tblDivision
     * @param bool        $IsSample
     *
     * @return bool|Certificate
     */
    public function getDocument(TblPerson $tblPerson, TblDivision $tblDivision, $IsSample = true)
    {

        $Class = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\'.$this->getCertificate();
        if (class_exists($Class)) {

            return new $Class($tblPerson, $tblDivision, $IsSample);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {

        return $this->Certificate;
    }

    /**
     * @param string $Certificate
     */
    public function setCertificate($Certificate)
    {

        $this->Certificate = $Certificate;
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
     * @return boolean
     */
    public function isGradeInformation()
    {
        return $this->IsGradeInformation;
    }

    /**
     * @param boolean $IsGradeInformation
     */
    public function setIsGradeInformation($IsGradeInformation)
    {
        $this->IsGradeInformation = $IsGradeInformation;
    }

    /**
     * @return string
     */
    public function getDisplayCategory()
    {

        return $this->isGradeInformation() ? 'Noteninformation' : 'Zeugnis';
    }

    /**
     * @return bool|TblCertificateType
     */
    public function getTblCertificateType()
    {

        if (null === $this->tblCertificateType) {
            return false;
        } else {
            return Generator::useService()->getCertificateTypeById($this->tblCertificateType);
        }
    }

    /**
     * @param TblCertificateType|null $tblCertificateType
     */
    public function setTblCertificateType(TblCertificateType $tblCertificateType = null)
    {

        $this->tblCertificateType = (null === $tblCertificateType ? null : $tblCertificateType->getId());
    }

    /**
     * @return bool|TblCourse
     */
    public function getServiceTblCourse()
    {

        if (null === $this->serviceTblCourse) {
            return false;
        } else {
            return Course::useService()->getCourseById($this->serviceTblCourse);
        }
    }

    /**
     * @param TblCourse|null $tblCourse
     */
    public function setServiceTblCourse(TblCourse $tblCourse = null)
    {

        $this->serviceTblCourse = (null === $tblCourse ? null : $tblCourse->getId());
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblSchoolType()
    {

        if (null === $this->serviceTblSchoolType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblSchoolType);
        }
    }

    /**
     * @param TblType|null $tblSchoolType
     */
    public function setServiceTblSchoolType(TblType $tblSchoolType = null)
    {

        $this->serviceTblSchoolType = (null === $tblSchoolType ? null : $tblSchoolType->getId());
    }

    /**
     * @return boolean
     */
    public function isInformation()
    {
        return $this->IsInformation;
    }

    /**
     * @param boolean $IsInformation
     */
    public function setIsInformation($IsInformation)
    {
        $this->IsInformation = $IsInformation;
    }

    /**
     * @return boolean
     */
    public function isChosenDefault()
    {
        return $this->IsChosenDefault;
    }

    /**
     * @param boolean $IsChosenDefault
     */
    public function setIsChosenDefault($IsChosenDefault)
    {
        $this->IsChosenDefault = $IsChosenDefault;
    }

    /**
     * @return boolean
     */
    public function getIsIgnoredForAutoSelect()
    {
        return $this->IsIgnoredForAutoSelect;
    }

    /**
     * @param boolean $IsIgnoredForAutoSelect
     */
    public function setIsIgnoredForAutoSelect($IsIgnoredForAutoSelect)
    {
        $this->IsIgnoredForAutoSelect = $IsIgnoredForAutoSelect;
    }

    /**
     * @return boolean
     */
    public function getIsGradeVerbal()
    {
        return $this->IsGradeVerbal;
    }

    /**
     * @param boolean $IsGradeVerbal
     */
    public function setIsGradeVerbal($IsGradeVerbal)
    {
        $this->IsGradeVerbal = $IsGradeVerbal;
    }
}
