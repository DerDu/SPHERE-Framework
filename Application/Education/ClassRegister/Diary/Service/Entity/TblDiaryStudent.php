<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 10.07.2019
 * Time: 14:59
 */

namespace SPHERE\Application\Education\ClassRegister\Diary\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblDiaryStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblDiaryStudent extends Element
{

    const ATTR_TBL_DIARY = 'tblDiary';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $tblDiary;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @return bool|TblDiary
     */
    public function getTblDiary()
    {

        if (null === $this->tblDiary) {
            return false;
        } else {
            return Diary::useService()->getDiaryById($this->tblDiary);
        }
    }

    /**
     * @param TblDiary|null $tblDiary
     */
    public function setTblDiary(TblDiary $tblDiary = null)
    {

        $this->tblDiary = (null === $tblDiary ? null : $tblDiary->getId());
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

        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }
}