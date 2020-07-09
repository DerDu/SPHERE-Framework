<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\Corporation\Company\Service\Data;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\Application\Corporation\Company\Service\Setup;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Corporation\Company
 */
class Service extends AbstractService
{
    /**
     * @return false|ViewCompany[]
     */
    public function viewCompany()
    {

        return ( new Data($this->getBinding()) )->viewCompany();
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

        return Group::useService()->countMemberByGroup($tblGroup);
    }

    /**
     * @param $Company
     *
     * @return bool|TblCompany
     */
    public function createCompanyService($Company)
    {
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

            return $tblCompany;
        } else {
            return false;
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $ExtendedName
     * @param string $ImportId
     *
     * @return TblCompany
     */
    public function insertCompany($Name, $Description = '', $ExtendedName = '', $ImportId = '')
    {

        return (new Data($this->getBinding()))->createCompany($Name, $ExtendedName, $Description, $ImportId);
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
     * @param TblCompany $tblCompany
     * @param $Company
     *
     * @return bool
     */
    public function updateCompanyService(TblCompany $tblCompany, $Company)
    {
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

            return true;
        } else {
            return false;
        }
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
     * @param string $Name
     * @param string $ExtendedName
     *
     * @return bool|TblCompany
     */
    public function getCompanyByName($Name, $ExtendedName)
    {

        return ( new Data($this->getBinding()) )->getCompanyByName($Name, $ExtendedName);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCompany
     */
    public function getCompanyListByName($Name)
    {

        return ( new Data($this->getBinding()) )->getCompanyListByName($Name);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool
     */
    public function removeCompany(TblCompany $tblCompany)
    {

        if(($tblMemberList = Group::useService()->getMemberAllByCompany($tblCompany))){
            foreach($tblMemberList as $tblMember)
            Group::useService()->removeMember($tblMember);
        }
        return (new Data($this->getBinding()))->removeCompany($tblCompany);
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

    /**
     * @param TblCompany $tblCompany
     * @param $Description
     *
     * @return bool
     */
    public function updateCompanyDescriptionWithoutForm(TblCompany $tblCompany, $Description = '')
    {

        return (new Data($this->getBinding()))->updateCompanyDescriptionWithoutForm($tblCompany, $Description);
    }

    /**
     * @param array  $ProcessList
     *
     * @return bool
     */
    public function updateCompanyAnonymousBulk(
        $ProcessList = array()
    ) {

        return (new Data($this->getBinding()))->updateCompanyAnonymousBulk($ProcessList);
    }

    /**
     * @param $Name
     *
     * @return false|TblCompany[]
     */
    public function getCompanyListLike($Name)
    {

        return (new Data($this->getBinding()))->getCompanyListLike($Name);
    }

    /**
     * @param integer $ImportId
     *
     * @return bool|TblCompany
     */
    public function getCompanyByImportId($ImportId)
    {
        return (new Data($this->getBinding()))->getCompanyByImportId($ImportId);
    }
}
