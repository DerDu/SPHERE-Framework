<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalSubjectArea;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentTechnicalSchool")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTechnicalSchool extends Element
{
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTechnicalCourse;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSchoolDiploma;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSchoolType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTechnicalDiploma;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTechnicalType;
    /**
     * @Column(type="string")
     */
    protected $PraxisLessons;
    /**
     * @Column(type="string")
     */
    protected $DurationOfTraining;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTenseOfLesson;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTrainingStatus;
    /**
     * @Column(type="string")
     */
    protected $Remark;
    /**
     * @Column(type="string")
     */
    protected $YearOfSchoolDiploma;
    /**
     * @Column(type="string")
     */
    protected $YearOfTechnicalDiploma;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTechnicalSubjectArea;
    /**
     * @Column(type="boolean")
     */
    protected $HasFinancialAid;
    /**
     * @Column(type="string")
     */
    protected $FinancialAidApplicationYear;
    /**
     * @Column(type="string")
     */
    protected $FinancialAidBureau;

    /**
     * @return bool|TblTechnicalCourse
     */
    public function getServiceTblTechnicalCourse()
    {
        if (null === $this->serviceTblTechnicalCourse) {
            return false;
        } else {
            return Course::useService()->getTechnicalCourseById($this->serviceTblTechnicalCourse);
        }
    }

    /**
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     */
    public function setServiceTblTechnicalCourse(TblTechnicalCourse $tblTechnicalCourse = null)
    {
        $this->serviceTblTechnicalCourse = ( null === $tblTechnicalCourse ? null : $tblTechnicalCourse->getId() );
    }

    /**
     * @return bool|TblSchoolDiploma
     */
    public function getServiceTblSchoolDiploma()
    {
        if (null === $this->serviceTblSchoolDiploma) {
            return false;
        } else {
            return Course::useService()->getSchoolDiplomaById($this->serviceTblSchoolDiploma);
        }
    }

    /**
     * @param TblSchoolDiploma|null $tblSchoolDiploma
     */
    public function setServiceTblSchoolDiploma(TblSchoolDiploma $tblSchoolDiploma = null)
    {
        $this->serviceTblSchoolDiploma = ( null === $tblSchoolDiploma ? null : $tblSchoolDiploma->getId() );
    }

    /**
     * @return bool|TblTechnicalDiploma
     */
    public function getServiceTblTechnicalDiploma()
    {
        if (null === $this->serviceTblTechnicalDiploma) {
            return false;
        } else {
            return Course::useService()->getTechnicalDiplomaById($this->serviceTblTechnicalDiploma);
        }
    }

    /**
     * @param TblTechnicalDiploma|null $tblTechnicalDiploma
     */
    public function setServiceTblTechnicalDiploma(TblTechnicalDiploma $tblTechnicalDiploma = null)
    {
        $this->serviceTblTechnicalDiploma = ( null === $tblTechnicalDiploma ? null : $tblTechnicalDiploma->getId() );
    }

    /**
     * @return string
     */
    public function getPraxisLessons()
    {
        return $this->PraxisLessons;
    }

    /**
     * @param string $PraxisLessons
     */
    public function setPraxisLessons($PraxisLessons)
    {
        $this->PraxisLessons = $PraxisLessons;
    }

    /**
     * @return string
     */
    public function getDurationOfTraining()
    {
        return $this->DurationOfTraining;
    }

    /**
     * @param string $DurationOfTraining
     */
    public function setDurationOfTraining($DurationOfTraining)
    {
        $this->DurationOfTraining = $DurationOfTraining;
    }

    /**
     * @return bool|TblStudentTenseOfLesson
     */
    public function getTblStudentTenseOfLesson()
    {
        if (null === $this->tblStudentTenseOfLesson) {
            return false;
        } else {
            return Student::useService()->getStudentTenseOfLessonById($this->tblStudentTenseOfLesson);
        }
    }

    /**
     * @param null|TblStudentTenseOfLesson $tblStudentTenseOfLesson
     */
    public function setTblStudentTenseOfLesson(TblStudentTenseOfLesson $tblStudentTenseOfLesson = null)
    {
        $this->tblStudentTenseOfLesson = ( null === $tblStudentTenseOfLesson ? null : $tblStudentTenseOfLesson->getId() );
    }

    /**
     * @return bool|TblStudentTrainingStatus
     */
    public function getTblStudentTrainingStatus()
    {
        if (null === $this->tblStudentTrainingStatus) {
            return false;
        } else {
            return Student::useService()->getStudentTrainingStatusById($this->tblStudentTrainingStatus);
        }
    }

    /**
     * @param null|TblStudentTrainingStatus $tblStudentTrainingStatus
     */
    public function setTblStudentTrainingStatus(TblStudentTrainingStatus $tblStudentTrainingStatus = null)
    {
        $this->tblStudentTrainingStatus = ( null === $tblStudentTrainingStatus ? null : $tblStudentTrainingStatus->getId() );
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
     * @param TblType|null $tblType
     */
    public function setServiceTblSchoolType(TblType $tblType = null)
    {
        $this->serviceTblSchoolType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblTechnicalType()
    {
        if (null === $this->serviceTblTechnicalType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblTechnicalType);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceTblTechnicalType(TblType $tblType = null)
    {
        $this->serviceTblTechnicalType = ( null === $tblType ? null : $tblType->getId() );
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
     * @return string
     */
    public function getYearOfSchoolDiploma()
    {
        return $this->YearOfSchoolDiploma;
    }

    /**
     * @param string $YearOfSchoolDiploma
     */
    public function setYearOfSchoolDiploma($YearOfSchoolDiploma)
    {
        $this->YearOfSchoolDiploma = $YearOfSchoolDiploma;
    }

    /**
     * @return string
     */
    public function getYearOfTechnicalDiploma()
    {
        return $this->YearOfTechnicalDiploma;
    }

    /**
     * @param string $YearOfTechnicalDiploma
     */
    public function setYearOfTechnicalDiploma($YearOfTechnicalDiploma)
    {
        $this->YearOfTechnicalDiploma = $YearOfTechnicalDiploma;
    }

    /**
     * @return bool|TblTechnicalSubjectArea
     */
    public function getServiceTblTechnicalSubjectArea()
    {
        if (null === $this->serviceTblTechnicalSubjectArea) {
            return false;
        } else {
            return Course::useService()->getTechnicalSubjectAreaById($this->serviceTblTechnicalSubjectArea);
        }
    }

    /**
     * @param TblTechnicalSubjectArea|null $tblTechnicalSubjectArea
     */
    public function setServiceTblTechnicalSubjectArea(TblTechnicalSubjectArea $tblTechnicalSubjectArea = null)
    {
        $this->serviceTblTechnicalSubjectArea = ( null === $tblTechnicalSubjectArea ? null : $tblTechnicalSubjectArea->getId() );
    }

    /**
     * @return boolean
     */
    public function getHasFinancialAid()
    {
        return $this->HasFinancialAid;
    }

    /**
     * @param boolean $HasFinancialAid
     */
    public function setHasFinancialAid($HasFinancialAid)
    {
        $this->HasFinancialAid = (boolean) $HasFinancialAid;
    }

    /**
     * @return string
     */
    public function getFinancialAidApplicationYear()
    {
        return $this->FinancialAidApplicationYear;
    }

    /**
     * @param string $FinancialAidApplicationYear
     */
    public function setFinancialAidApplicationYear($FinancialAidApplicationYear)
    {
        $this->FinancialAidApplicationYear = $FinancialAidApplicationYear;
    }

    /**
     * @return string
     */
    public function getFinancialAidBureau()
    {
        return $this->FinancialAidBureau;
    }

    /**
     * @param string $FinancialAidBureau
     */
    public function setFinancialAidBureau($FinancialAidBureau)
    {
        $this->FinancialAidBureau = $FinancialAidBureau;
    }
}