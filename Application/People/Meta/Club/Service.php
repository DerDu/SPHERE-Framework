<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.05.2016
 * Time: 08:26
 */

namespace SPHERE\Application\People\Meta\Club;

use SPHERE\Application\People\Meta\Club\Service\Data;
use SPHERE\Application\People\Meta\Club\Service\Entity\TblClub;
use SPHERE\Application\People\Meta\Club\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{

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
     * @param IFormInterface $Form
     * @param TblPerson $tblPerson
     * @param array $Meta
     * @param null $Group
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

        $tblClub = $this->getClubByPerson($tblPerson);
        if ($tblClub) {
            (new Data($this->getBinding()))->updateClub(
                $tblClub,
                $Meta['Identifier'],
                $Meta['EntryDate'],
                $Meta['ExitDate'],
                $Meta['Remark']
            );
        } else {
            (new Data($this->getBinding()))->createClub(
                $tblPerson,
                $Meta['Identifier'],
                $Meta['EntryDate'],
                $Meta['ExitDate'],
                $Meta['Remark']
            );
        }
        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Daten wurde erfolgreich gespeichert')
        . new Redirect(null, Redirect::TIMEOUT_SUCCESS);
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblClub
     */
    public function getClubByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getClubByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Identifier
     * @param string $EntryDate
     * @param string $ExitDate
     * @param string $Remark
     */
    public function insertMeta(
        TblPerson $tblPerson,
        $Identifier,
        $EntryDate = '',
        $ExitDate = '',
        $Remark = ''
    ) {

        $tblClub = $this->getClubByPerson($tblPerson);
        if ($tblClub) {
            (new Data($this->getBinding()))->updateClub(
                $tblClub,
                $Identifier,
                $EntryDate,
                $ExitDate,
                $Remark
            );
        } else {
            (new Data($this->getBinding()))->createClub(
                $tblPerson,
                $Identifier,
                $EntryDate,
                $ExitDate,
                $Remark
            );
        }
    }

    /**
     * @param TblClub $tblClub
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyClub(TblClub $tblClub, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyClub($tblClub, $IsSoftRemove);
    }
}