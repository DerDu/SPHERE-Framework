<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.05.2016
 * Time: 08:15
 */

namespace SPHERE\Application\People\Meta\Teacher;

use SPHERE\Application\People\Meta\Teacher\Service\Data;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\ViewPeopleMetaTeacher;
use SPHERE\Application\People\Meta\Teacher\Service\Setup;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{

    /**
     * @return false|ViewPeopleMetaTeacher[]
     */
    public function viewPeopleMetaTeacher()
    {
        return ( new Data($this->getBinding()) )->viewPeopleMetaTeacher();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblTeacher
     */
    public function updateMetaService(TblPerson $tblPerson, $Meta)
    {
        if ($tblTeacher = $this->getTeacherByPerson($tblPerson)) {
            return (new Data($this->getBinding()))->updateTeacher(
                $tblTeacher,
                $Meta['Acronym']
            );
        } else {
            return (new Data($this->getBinding()))->createTeacher(
                $tblPerson,
                $Meta['Acronym']
            );
        }
    }

    /**
     * @return false|TblTeacher[]
     */
    public function getTeacherAll()
    {

        return ( new Data($this->getBinding()) )->getTeacherAll();
    }

    /**
     * @param $Id
     *
     * @return false|TblTeacher
     */
    public function getTeacherById($Id)
    {

        return ( new Data($this->getBinding()) )->getTeacherById($Id);
    }

    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblTeacher
     */
    public function getTeacherByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getTeacherByPerson($tblPerson, $isForced);
    }

    /**
     * @param $Acronym
     *
     * @return false|TblTeacher
     */
    public function getTeacherByAcronym($Acronym)
    {

        return (new Data($this->getBinding()))->getTeacherByAcronym($Acronym);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Acronym
     *
     * @return TblTeacher
     */
    public function insertTeacher(
        TblPerson $tblPerson,
        $Acronym
    ) {

        return (new Data($this->getBinding()))->createTeacher($tblPerson, $Acronym);
    }

    /**
     * @param TblTeacher $tblTeacher
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyTeacher(TblTeacher $tblTeacher, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyTeacher($tblTeacher, $IsSoftRemove);
    }

    /**
     * @param TblTeacher $tblTeacher
     *
     * @return bool
     */
    public function restoreTeacher(TblTeacher $tblTeacher)
    {

        return (new Data($this->getBinding()))->restoreTeacher($tblTeacher);
    }
}