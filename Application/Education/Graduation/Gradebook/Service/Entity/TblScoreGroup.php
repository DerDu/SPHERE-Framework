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
 * @Table(name="tblScoreGroup")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreGroup extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_IS_ACTIVE = 'IsActive';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Multiplier;

    /**
     * @Column(type="string")
     */
    protected $Round;

    /**
     * @Column(type="boolean")
     */
    protected $IsEveryGradeASingleGroup;

    /**
     * @Column(type="boolean")
     */
    protected $IsActive;

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
     * @return string
     */
    public function getRound()
    {

        return $this->Round;
    }

    /**
     * @param string $Round
     */
    public function setRound($Round)
    {

        $this->Round = $Round;
    }

    /**
     * @return boolean
     */
    public function isEveryGradeASingleGroup()
    {
        return $this->IsEveryGradeASingleGroup;
    }

    /**
     * @param boolean $IsEveryGradeASingleGroup
     */
    public function setIsEveryGradeASingleGroup($IsEveryGradeASingleGroup)
    {
        $this->IsEveryGradeASingleGroup = $IsEveryGradeASingleGroup;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->IsActive;
    }

    /**
     * @param boolean $IsActive
     */
    public function setIsActive($IsActive)
    {
        $this->IsActive = (boolean) $IsActive;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {

        return Gradebook::useService()->isScoreGroupUsed($this);
    }
}
