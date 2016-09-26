<?php
namespace SPHERE\Application\Education\Graduation\Evaluation\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblTest")
 * @Cache(usage="READ_ONLY")
 */
class TblTest extends Element
{

    const ATTR_DATE = 'Date';
    const ATTR_CORRECTION_DATE = 'CorrectionDate';
    const ATTR_RETURN_DATE = 'ReturnDate';
    const ATTR_TBL_TEST_TYPE = 'tblTestType';
    const ATTR_TBL_TASK = 'tblTask';
    const ATTR_SERVICE_TBL_GRADE_TYPE = 'serviceTblGradeType';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_SUBJECT_GROUP = 'serviceTblSubjectGroup';
    const ATTR_SERVICE_TBL_PERIOD = 'serviceTblPeriod';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="datetime")
     */
    protected $CorrectionDate;

    /**
     * @Column(type="datetime")
     */
    protected $ReturnDate;

    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGradeType;

    /**
     * @Column(type="bigint")
     */
    protected $tblTestType;

    /**
     * @Column(type="bigint")
     */
    protected $tblTask;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectGroup;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPeriod;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="boolean")
     */
    protected $IsContinues;

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
     * @return string
     */
    public function getCorrectionDate()
    {

        if (null === $this->CorrectionDate) {
            return false;
        }
        /** @var \DateTime $CorrectionDate */
        $CorrectionDate = $this->CorrectionDate;
        if ($CorrectionDate instanceof \DateTime) {
            return $CorrectionDate->format('d.m.Y');
        } else {
            return (string)$CorrectionDate;
        }
    }

    /**
     * @param null|\DateTime $CorrectionDate
     */
    public function setCorrectionDate(\DateTime $CorrectionDate = null)
    {

        $this->CorrectionDate = $CorrectionDate;
    }

    /**
     * @return string
     */
    public function getReturnDate()
    {

        if (null === $this->ReturnDate) {
            return false;
        }
        /** @var \DateTime $ReturnDate */
        $ReturnDate = $this->ReturnDate;
        if ($ReturnDate instanceof \DateTime) {
            return $ReturnDate->format('d.m.Y');
        } else {
            return (string)$ReturnDate;
        }
    }

    /**
     * @param null|\DateTime $ReturnDate
     */
    public function setReturnDate(\DateTime $ReturnDate = null)
    {

        $this->ReturnDate = $ReturnDate;
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
     * @return bool|TblGradeType
     */
    public function getServiceTblGradeType()
    {

        if (null === $this->serviceTblGradeType) {
            return false;
        } else {
            return Gradebook::useService()->getGradeTypeById($this->serviceTblGradeType);
        }
    }

    /**
     * @param TblGradeType|null $serviceTblGradeType
     */
    public function setServiceTblGradeType($serviceTblGradeType)
    {

        $this->serviceTblGradeType = ( null === $serviceTblGradeType ? null : $serviceTblGradeType->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {

        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubjectGroup
     */
    public function getServiceTblSubjectGroup()
    {

        if (null === $this->serviceTblSubjectGroup) {
            return false;
        } else {
            return Division::useService()->getSubjectGroupById($this->serviceTblSubjectGroup);
        }
    }

    /**
     * @param TblSubjectGroup|null $tblSubjectGroup
     */
    public function setServiceTblSubjectGroup(TblSubjectGroup $tblSubjectGroup = null)
    {

        $this->serviceTblSubjectGroup = ( null === $tblSubjectGroup ? null : $tblSubjectGroup->getId() );
    }

    /**
     * @return bool|TblPeriod
     */
    public function getServiceTblPeriod()
    {

        if (null === $this->serviceTblPeriod) {
            return false;
        } else {
            return Term::useService()->getPeriodById($this->serviceTblPeriod);
        }
    }

    /**
     * @param TblPeriod|null $tblPeriod
     */
    public function setServiceTblPeriod(TblPeriod $tblPeriod = null)
    {

        $this->serviceTblPeriod = ( null === $tblPeriod ? null : $tblPeriod->getId() );
    }

    /**
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {

        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {

        $this->serviceTblDivision = ( null === $tblDivision ? null : $tblDivision->getId() );
    }

    /**
     * @return bool|TblTestType
     */
    public function getTblTestType()
    {

        if (null === $this->tblTestType) {
            return false;
        } else {
            return Evaluation::useService()->getTestTypeById($this->tblTestType);
        }
    }

    /**
     * @param TblTestType|null $tblTestType
     */
    public function setTblTestType($tblTestType)
    {

        $this->tblTestType = ( null === $tblTestType ? null : $tblTestType->getId() );
    }

    /**
     * @return bool|TblTask
     */
    public function getTblTask()
    {

        if (null === $this->tblTask) {
            return false;
        } else {
            return Evaluation::useService()->getTaskById($this->tblTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setTblTask($tblTask)
    {

        $this->tblTask = ( null === $tblTask ? null : $tblTask->getId() );
    }

    /**
     * @return boolean
     */
    public function isContinues()
    {
        return $this->IsContinues;
    }

    /**
     * @param boolean $IsContinues
     */
    public function setIsContinues($IsContinues)
    {
        $this->IsContinues = (boolean) $IsContinues;
    }

    /**
     * @return false|TblTest[]
     */
    public function getLinkedTestAll()
    {

        return Evaluation::useService()->getTestLinkAllByTest($this);
    }
}
