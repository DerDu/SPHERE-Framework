<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\Corporation\Company\Service\Data;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Company\Service\Setup;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Corporation\Company
 */
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
     * int
     */
    public function countCompanyAll()
    {

        return (new Data($this->getBinding()))->countCompanyAll();
    }

    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAll()
    {

        return (new Data($this->getBinding()))->getCompanyAll();
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countCompanyAllByGroup(TblGroup $tblGroup)
    {

        return Group::useService()->countCompanyAllByGroup($tblGroup);
    }

    /**
     * @param IFormInterface $Form
     * @param array $Company
     *
     * @return IFormInterface|Redirect
     */
    public function createCompany(IFormInterface $Form = null, $Company)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Company) {
            return $Form;
        }

        $Error = false;

        if (isset($Company['Name']) && empty($Company['Name'])) {
            $Form->setError('Company[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {

            if (($tblCompany = (new Data($this->getBinding()))->createCompany($Company['Name'],
                $Company['ExtendedName'],
                $Company['Description']))
            ) {
                // Add to Group
                if (isset($Company['Group'])) {
                    foreach ((array)$Company['Group'] as $tblGroup) {
                        Group::useService()->addGroupCompany(
                            Group::useService()->getGroupById($tblGroup), $tblCompany
                        );
                    }
                }
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Firma wurde erfolgreich erstellt')
                . new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblCompany->getId())
                );
            } else {
                return new Danger(new Ban() . ' Die Firma konnte nicht erstellt werden')
                . new Redirect('/Corporation/Company', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param        $Name
     * @param string $Description
     *
     * @return TblCompany
     */
    public function insertCompany($Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createCompany($Name, '', $Description);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCompany
     */
    public function getCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getCompanyById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param TblCompany $tblCompany
     * @param array $Company
     * @param null|int $Group
     *
     * @return IFormInterface|Redirect
     */
    public function updateCompany(IFormInterface $Form = null, TblCompany $tblCompany, $Company, $Group)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Company) {
            return $Form;
        }

        $Error = false;

        if (isset($Company['Name']) && empty($Company['Name'])) {
            $Form->setError('Company[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->updateCompany($tblCompany, $Company['Name'],
                $Company['ExtendedName'], $Company['Description'])
            ) {
                // Change Groups
                if (isset($Company['Group'])) {
                    // Remove all Groups
                    $tblGroupList = Group::useService()->getGroupAllByCompany($tblCompany);
                    foreach ($tblGroupList as $tblGroup) {
                        Group::useService()->removeGroupCompany($tblGroup, $tblCompany);
                    }
                    // Add current Groups
                    foreach ((array)$Company['Group'] as $tblGroup) {
                        Group::useService()->addGroupCompany(
                            Group::useService()->getGroupById($tblGroup), $tblCompany
                        );
                    }
                } else {
                    // Remove all Groups
                    $tblGroupList = Group::useService()->getGroupAllByCompany($tblCompany);
                    foreach ($tblGroupList as $tblGroup) {
                        Group::useService()->removeGroupCompany($tblGroup, $tblCompany);
                    }
                }
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Firma wurde erfolgreich aktualisiert')
                . new Redirect(null, Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger(new Ban() . ' Die Firma konnte nicht aktualisiert werden')
                . new Redirect('/Corporation/Company', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param string $Description
     *
     * @return bool|TblCompany
     */
    public function getCompanyByDescription($Description)
    {

        return (new Data($this->getBinding()))->getCompanyByDescription($Description);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool
     */
    public function destroyCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->destroyCompany($tblCompany);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Name
     * @param $ExtendedName
     * @param $Description
     *
     * @return bool
     */
    public function updateCompanyWithoutForm(TblCompany $tblCompany, $Name, $ExtendedName = '', $Description = '')
    {

        return (new Data($this->getBinding()))->updateCompany($tblCompany, $Name, $ExtendedName, $Description);
    }
}
