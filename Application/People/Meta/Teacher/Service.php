<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.05.2016
 * Time: 08:15
 */

namespace SPHERE\Application\People\Meta\Teacher;

use SPHERE\Application\People\Meta\Teacher\Service\Data;
use SPHERE\Application\People\Meta\Teacher\Service\Setup;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
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
     * @return IFormInterface|Redirect
     */
    public function createMeta(IFormInterface $Form = null, TblPerson $tblPerson, $Meta, $Group = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        if (isset($Meta['Acronym']) && !empty($Meta['Acronym'])) {
            if ($this->getTeacherByAcronym($Meta['Acronym'])) {
                $Form->setError('Meta[Acronym]', 'Dieses Kürzel wird bereits verwendet');

                return $Form;
            }
        }

        $tblTeacher = $this->getTeacherByPerson($tblPerson);
        if ($tblTeacher) {
            (new Data($this->getBinding()))->updateTeacher(
                $tblTeacher,
                $Meta['Acronym']
            );
        } else {
            (new Data($this->getBinding()))->createTeacher(
                $tblPerson,
                $Meta['Acronym']
            );
        }
        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Daten wurde erfolgreich gespeichert')
        . new Redirect(null, Redirect::TIMEOUT_SUCCESS);
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblTeacher
     */
    public function getTeacherByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getTeacherByPerson($tblPerson);
    }

    /**
     * @param $Acronym
     *
     * @return false|\SPHERE\System\Database\Fitting\Element
     */
    public function getTeacherByAcronym($Acronym)
    {

        return (new Data($this->getBinding()))->getTeacherByAcronym($Acronym);
    }
}