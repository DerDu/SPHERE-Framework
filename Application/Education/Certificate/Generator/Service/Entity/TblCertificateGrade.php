<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateGrade extends Element
{

    const ATTR_LANE = 'Lane';
    const ATTR_RANKING = 'Ranking';
    const ATTR_TBL_CERTIFICATE = 'tblCertificate';
    const SERVICE_TBL_GRADE_TYPE = 'serviceTblGradeType';

    /**
     * @Column(type="bigint")
     */
    protected $tblCertificate;
    /**
     * @Column(type="boolean")
     */
    protected $IsEssential;
    /**
     * @Column(type="integer")
     */
    protected $Lane;
    /**
     * @Column(type="integer")
     */
    protected $Ranking;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGradeType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentLiberationCategory;

    /**
     * @return boolean
     */
    public function isEssential()
    {

        return (bool)$this->IsEssential;
    }

    /**
     * @param boolean $IsEssential
     */
    public function setEssential($IsEssential)
    {

        $this->IsEssential = (bool)$IsEssential;
    }

    /**
     * @return int
     */
    public function getLane()
    {

        return $this->Lane;
    }

    /**
     * @param int $Index
     */
    public function setLane($Index)
    {

        $this->Lane = $Index;
    }

    /**
     * @return int
     */
    public function getRanking()
    {

        return $this->Ranking;
    }

    /**
     * @param int $Index
     */
    public function setRanking($Index)
    {

        $this->Ranking = $Index;
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
     * @param TblGradeType|null $tblGradeType
     */
    public function setServiceTblGradeType(TblGradeType $tblGradeType = null)
    {

        $this->serviceTblGradeType = ( null === $tblGradeType ? null : $tblGradeType->getId() );
    }

    /**
     * @return bool|TblStudentLiberationCategory
     */
    public function getServiceTblStudentLiberationCategory()
    {

        if (null === $this->serviceTblStudentLiberationCategory) {
            return false;
        } else {
            return Student::useService()->getStudentLiberationCategoryById($this->serviceTblStudentLiberationCategory);
        }
    }

    /**
     * @param TblStudentLiberationCategory|null $tblStudentLiberationCategory
     */
    public function setServiceTblStudentLiberationCategory(TblStudentLiberationCategory $tblStudentLiberationCategory = null)
    {

        $this->serviceTblStudentLiberationCategory = ( null === $tblStudentLiberationCategory ? null : $tblStudentLiberationCategory->getId() );
    }

    /**
     * @return bool|TblCertificate
     */
    public function getTblCertificate()
    {

        if (null === $this->tblCertificate) {
            return false;
        } else {
            return Generator::useService()->getCertificateById($this->tblCertificate);
        }
    }

    /**
     * @param null|TblCertificate $tblCertificate
     */
    public function setTblCertificate(TblCertificate $tblCertificate = null)
    {

        $this->tblCertificate = ( null === $tblCertificate ? null : $tblCertificate->getId() );
    }
}
