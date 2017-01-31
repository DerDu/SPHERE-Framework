<?php
namespace SPHERE\Application\People\Meta\Common;

use SPHERE\Application\People\Meta\Common\Service\Data;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Common\Service\Entity\ViewPeopleMetaCommon;
use SPHERE\Application\People\Meta\Common\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Common
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewPeopleMetaCommon[]
     */
    public function viewPeopleMetaCommon()
    {

        return (new Data($this->getBinding()))->viewPeopleMetaCommon();
    }
    
    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param string $Name
     *
     * @return TblCommonGender
     */
    public function createCommonGender($Name)
    {
        return (new Data($this->getBinding()))->createCommonGender($Name);
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param array          $Meta
     * @param null           $Group
     *
     * @return IFormInterface|string
     */
    public function createMeta(IFormInterface $Form = null, TblPerson $tblPerson, $Meta, $Group = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        $tblCommon = $this->getCommonByPerson($tblPerson);
        if ($tblCommon) {
            (new Data($this->getBinding()))->updateCommonBirthDates(
                $tblCommon->getTblCommonBirthDates(),
                $Meta['BirthDates']['Birthday'],
                $Meta['BirthDates']['Birthplace'],
                $Meta['BirthDates']['Gender']
            );
            (new Data($this->getBinding()))->updateCommonInformation(
                $tblCommon->getTblCommonInformation(),
                $Meta['Information']['Nationality'],
                $Meta['Information']['Denomination'],
                $Meta['Information']['IsAssistance'],
                $Meta['Information']['AssistanceActivity']
            );
            (new Data($this->getBinding()))->updateCommon(
                $tblCommon,
                $Meta['Remark']
            );
        } else {
            $tblCommonBirthDates = (new Data($this->getBinding()))->createCommonBirthDates(
                $Meta['BirthDates']['Birthday'],
                $Meta['BirthDates']['Birthplace'],
                $Meta['BirthDates']['Gender']
            );
            $tblCommonInformation = (new Data($this->getBinding()))->createCommonInformation(
                $Meta['Information']['Nationality'],
                $Meta['Information']['Denomination'],
                $Meta['Information']['IsAssistance'],
                $Meta['Information']['AssistanceActivity']
            );
            (new Data($this->getBinding()))->createCommon(
                $tblPerson,
                $tblCommonBirthDates,
                $tblCommonInformation,
                $Meta['Remark']
            );
        }
        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Daten wurde erfolgreich gespeichert')
        .new Redirect(null, Redirect::TIMEOUT_SUCCESS);
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCommon
     */
    public function getCommonByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getCommonByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Birthday
     * @param           $Birthplace
     * @param           $Gender
     * @param           $Nationality
     * @param           $Denomination
     * @param           $IsAssistance
     * @param           $AssistanceActivity
     *
     * @param           $Remark
     */
    public function insertMeta(
        TblPerson $tblPerson,
        $Birthday,
        $Birthplace,
        $Gender,
        $Nationality,
        $Denomination,
        $IsAssistance,
        $AssistanceActivity,
        $Remark
    ) {

        $tblCommonBirthDates = (new Data($this->getBinding()))->createCommonBirthDates(
            $Birthday,
            $Birthplace,
            $Gender
        );
        $tblCommonInformation = (new Data($this->getBinding()))->createCommonInformation(
            $Nationality,
            $Denomination,
            $IsAssistance,
            $AssistanceActivity
        );
        (new Data($this->getBinding()))->createCommon(
            $tblPerson,
            $tblCommonBirthDates,
            $tblCommonInformation,
            $Remark
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommon
     */
    public function getCommonById($Id)
    {

        return (new Data($this->getBinding()))->getCommonById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonGender
     */
    public function getCommonGenderById($Id)
    {

        return (new Data($this->getBinding()))->getCommonGenderById($Id);
    }

    /**
     * @return bool|TblCommonGender[]
     */
    public function getCommonGenderAll()
    {

        return (new Data($this->getBinding()))->getCommonGenderAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonBirthDates
     */
    public function getCommonBirthDatesById($Id)
    {

        return (new Data($this->getBinding()))->getCommonBirthDatesById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonInformation
     */
    public function getCommonInformationById($Id)
    {

        return (new Data($this->getBinding()))->getCommonInformationById($Id);
    }

    /**
     * @return bool|TblCommonInformation[]
     */
    public function getCommonInformationAll()
    {

        return (new Data($this->getBinding()))->getCommonInformationAll();
    }

    /**
     * @return bool|TblCommonBirthDates[]
     */
    public function getCommonBirthDatesAll()
    {

        return (new Data($this->getBinding()))->getCommonBirthDatesAll();
    }

    /**
     * @param TblCommon $tblCommon
     * @param string $Remark
     *
     * @return TblCommon
     */
    public function updateCommon(TblCommon $tblCommon, $Remark){

        return (new Data($this->getBinding()))->updateCommon( $tblCommon, $Remark );
    }

    /**
     * @param TblCommonBirthDates $tblCommonBirthDates
     * @param string              $Birthday
     * @param string              $Birthplace
     * @param int                 $Gender
     *
     * @return TblCommonBirthDates
     */
    public function updateCommonBirthDates(
        TblCommonBirthDates $tblCommonBirthDates,
        $Birthday,
        $Birthplace,
        $Gender
    ) {
        return (new Data($this->getBinding()))->updateCommonBirthDates( $tblCommonBirthDates, $Birthday, $Birthplace, $Gender );
    }

    /**
     * @param TblCommon $tblCommon
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyCommon(TblCommon $tblCommon, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyCommon($tblCommon, $IsSoftRemove);
    }

    /**
     * @param $Name
     *
     * @return false|TblCommonGender
     */
    public function getCommonGenderByName($Name)
    {

        return (new Data($this->getBinding()))->getCommonGenderByName($Name);
    }
}
