<?php
namespace SPHERE\Application\People\Meta\Custody;

use SPHERE\Application\People\Meta\Custody\Service\Data;
use SPHERE\Application\People\Meta\Custody\Service\Entity\TblCustody;
use SPHERE\Application\People\Meta\Custody\Service\Entity\ViewPeopleMetaCustody;
use SPHERE\Application\People\Meta\Custody\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Custody
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewPeopleMetaCustody[]
     */
    public function viewPeopleMetaCustody()
    {

        return ( new Data($this->getBinding()) )->viewPeopleMetaCustody();
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

        $tblCustody = $this->getCustodyByPerson($tblPerson);
        if ($tblCustody) {
            (new Data($this->getBinding()))->updateCustody(
                $tblCustody,
                $Meta['Remark'],
                $Meta['Occupation'],
                $Meta['Employment']
            );
        } else {
            (new Data($this->getBinding()))->createCustody(
                $tblPerson,
                $Meta['Remark'],
                $Meta['Occupation'],
                $Meta['Employment']
            );
        }
        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Daten wurde erfolgreich gespeichert')
        .new Redirect(null, Redirect::TIMEOUT_SUCCESS);
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCustody
     */
    public function getCustodyByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getCustodyByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Occupation
     * @param           $Employment
     * @param           $Remark
     */
    public function insertMeta(TblPerson $tblPerson, $Occupation, $Employment, $Remark)
    {

        (new Data($this->getBinding()))->createCustody($tblPerson, $Remark, $Occupation, $Employment);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCustody
     */
    public function getCustodyById($Id)
    {

        return (new Data($this->getBinding()))->getCustodyById($Id);
    }

    /**
     * @param TblCustody $tblCustody
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyCustody(TblCustody $tblCustody, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyCustody($tblCustody, $IsSoftRemove);
    }
}
