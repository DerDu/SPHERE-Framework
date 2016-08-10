<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 16:03
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblPrepareGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblPrepareGrade extends Element
{

    const ATTR_TBL_PREPARE_CERTIFICATE = 'tblPrepareCertificate';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_TEST_TYPE = 'serviceTblTestType';
    const ATTR_SERVICE_TBL_GRADE_TYPE = 'serviceTblGradeType';

    /**
     * @Column(type="bigint")
     */
    protected $tblPrepareCertificate;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTestType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGradeType;

    /**
     * @Column(type="string")
     */
    protected $Grade;

    /**
     * @return bool|TblPrepareCertificate
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

        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
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
     * @return bool|TblTestType
     */
    public function getServiceTblTestType()
    {

        if (null === $this->serviceTblTestType) {
            return false;
        } else {
            return Evaluation::useService()->getTestTypeById($this->serviceTblTestType);
        }
    }

    /**
     * @param TblTestType|null $tblTestType
     */
    public function setServiceTblTestType(TblTestType $tblTestType = null)
    {

        $this->serviceTblTestType = ( null === $tblTestType ? null : $tblTestType->getId() );
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
     * @return string
     */
    public function getGrade()
    {
        return $this->Grade;
    }

    /**
     * @param string $Grade
     */
    public function setGrade($Grade)
    {
        $this->Grade = $Grade;
    }

}