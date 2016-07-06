<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreGroupGradeTypeList")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreGroupGradeTypeList extends Element
{

    const ATTR_TBL_GRADE_TYPE = 'tblGradeType';
    const ATTR_TBL_SCORE_GROUP = 'tblScoreGroup';

    /**
     * @Column(type="string")
     */
    protected $Multiplier;

    /**
     * @Column(type="bigint")
     */
    protected $tblGradeType;

    /**
     * @Column(type="bigint")
     */
    protected $tblScoreGroup;

    /**
     * @return string
     */
    public function getMultiplier()
    {

        return str_replace(',', '.', $this->Multiplier);
    }

    /**
     * @return string
     */
    public function getDisplayMultiplier()
    {

        return str_replace('.', ',', $this->Multiplier);
    }

    /**
     * @param string $Multiplier
     */
    public function setMultiplier($Multiplier)
    {

        $this->Multiplier = floatval(str_replace(',', '.' , $Multiplier));
    }

    /**
     * @return bool|TblGradeType
     */
    public function getTblGradeType()
    {

        if (null === $this->tblGradeType) {
            return false;
        } else {
            return Gradebook::useService()->getGradeTypeById($this->tblGradeType);
        }
    }

    /**
     * @param TblGradeType|null $tblGradeType
     */
    public function setTblGradeType($tblGradeType)
    {

        $this->tblGradeType = ( null === $tblGradeType ? null : $tblGradeType->getId() );
    }

    /**
     * @return bool|TblScoreGroup
     */
    public function getTblScoreGroup()
    {

        if (null === $this->tblScoreGroup) {
            return false;
        } else {
            return Gradebook::useService()->getScoreGroupById($this->tblScoreGroup);
        }
    }

    /**
     * @param TblScoreGroup|null $tblScoreGroup
     */
    public function setTblScoreGroup($tblScoreGroup)
    {

        $this->tblScoreGroup = ( null === $tblScoreGroup ? null : $tblScoreGroup->getId() );
    }

}
