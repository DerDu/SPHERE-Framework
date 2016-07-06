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
 * @Table(name="tblScoreRule")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRule extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Description;

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
     * @return false|TblGradeType[]
     */
    public function getGradeTypesAll()
    {

        $resultList = array();
        $tblScoreConditionAllByRule = Gradebook::useService()->getScoreConditionsByRule($this);
        if ($tblScoreConditionAllByRule) {
            foreach ($tblScoreConditionAllByRule as $tblScoreCondition){
                $tblGroupListByCondition = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreCondition);
                if ($tblGroupListByCondition){
                    foreach ($tblGroupListByCondition as $group){
                        $tblScoreGroupGradeTypeListByGroup = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($group->getTblScoreGroup());
                        if ($tblScoreGroupGradeTypeListByGroup){
                            foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeType){
                                if ($tblScoreGroupGradeType->getTblGradeType()) {
                                    $resultList[$tblScoreGroupGradeType->getTblGradeType()->getId()] = $tblScoreGroupGradeType->getTblGradeType();
                                }
                            }
                        }
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }
}
