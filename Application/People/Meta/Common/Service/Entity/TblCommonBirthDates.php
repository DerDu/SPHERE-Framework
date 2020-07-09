<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCommonBirthDates")
 * @Cache(usage="READ_ONLY")
 */
class TblCommonBirthDates extends Element
{
    /**
     * @deprecated
     */
    const VALUE_GENDER_NULL = 0;
    /**
     * @deprecated
     */
    const VALUE_GENDER_MALE = 1;
    /**
     * @deprecated
     */
    const VALUE_GENDER_FEMALE = 2;

    const ATTR_BIRTHPLACE = 'Birthplace';

    /**
     * @Column(type="datetime")
     */
    protected $Birthday;
    /**
     * @Column(type="string")
     */
    protected $Birthplace;
    /**
     * @deprecated
     * @Column(type="smallint")
     */
    protected $Gender;
    /**
     * @Column(type="bigint")
     */
    protected $tblCommonGender;

    /**
     * @param string $format
     *
     * @return string
     */
    public function getBirthday($format = 'd.m.Y')
    {

        if (null === $this->Birthday) {
            return false;
        }
        /** @var \DateTime $Birthday */
        $Birthday = $this->Birthday;
        if ($Birthday instanceof \DateTime) {
            return $Birthday->format($format);
        } else {
            return (string)$Birthday;
        }
    }

    /**
     * @param null|\DateTime $Birthday
     */
    public function setBirthday(\DateTime $Birthday = null)
    {

        $this->Birthday = $Birthday;
    }

    /**
     * @return string
     */
    public function getBirthplace()
    {

        return $this->Birthplace;
    }

    /**
     * @param string $Birthplace
     */
    public function setBirthplace($Birthplace)
    {

        $this->Birthplace = $Birthplace;
    }

    /**
     * @deprecated
     * @see getTblCommonGender
     * @return int
     */
    public function getGender()
    {

        if(($Gender = $this->getTblCommonGender())) {
            $Gender = $Gender->getId();
        } else {
            $Gender = $this->Gender;
        }
        return $Gender;
    }

    /**
     * @deprecated
     * @see setTblCommonGender
     * @param int $Gender
     */
    public function setGender($Gender)
    {
        $this->Gender = $Gender;
        switch ( $Gender ) {
            case self::VALUE_GENDER_MALE:
            case self::VALUE_GENDER_FEMALE:
                $Gender = Common::useService()->getCommonGenderById($Gender);
                break;
            default:
                $Gender = null;
        }
        $this->setTblCommonGender( $Gender );
    }

    /**
     * @return bool|TblCommonGender
     */
    public function getTblCommonGender()
    {

        if (null === $this->tblCommonGender) {
            return false;
        } else {
            return Common::useService()->getCommonGenderById($this->tblCommonGender);
        }
    }

    /**
     * @param null|TblCommonGender $tblCommonGender
     */
    public function setTblCommonGender(TblCommonGender $tblCommonGender = null)
    {

        $this->tblCommonGender = ( null === $tblCommonGender ? null : $tblCommonGender->getId() );
    }

    /**
     * Used internal to migrade $Gender to $tblCommonGender FK
     *
     * DO NOT USE THIS!
     *
     * @internal
     */
    final public function isGenderInSync()
    {
        return $this->Gender == $this->tblCommonGender;
    }
}
