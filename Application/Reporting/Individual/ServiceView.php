<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Individual\Service\Data;
use SPHERE\Application\Reporting\Individual\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class ServiceView
 *
 * @package SPHERE\Application\Reporting\Individual
 */
class ServiceView extends AbstractService
{

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

    // ToDO Vorbereitung, die View's anderweitig zu benutzen:
    /** @return false|array */
//    public function getViewEducationStudentAll(){return (new Data($this->getBinding()))->getViewEducationStudentAll();}
//    public function getViewGroupAll(){return (new Data($this->getBinding()))->getViewGroupAll();}
//    public function getViewGroupClubAll(){return (new Data($this->getBinding()))->getViewGroupClubAll();}
//    public function getViewGroupCustodyAll(){return (new Data($this->getBinding()))->getViewGroupCustodyAll();}
//    public function getViewGroupProspectAll(){return (new Data($this->getBinding()))->getViewGroupProspectAll();}
//    public function getViewGroupStudentBasicAll(){return (new Data($this->getBinding()))->getViewGroupStudentBasicAll();}
//    public function getViewGroupStudentIntegrationAll(){return (new Data($this->getBinding()))->getViewGroupStudentIntegrationAll();}
//    public function getViewGroupStudentSubjectAll(){return (new Data($this->getBinding()))->getViewGroupStudentSubjectAll();}
//    public function getViewGroupStudentTransferAll(){return (new Data($this->getBinding()))->getViewGroupStudentTransferAll();}
//    public function getViewGroupTeacherAll(){return (new Data($this->getBinding()))->getViewGroupTeacherAll();}
//    public function getViewPersonAll(){return (new Data($this->getBinding()))->getViewPersonAll();}

    public function getViewContactAddressAll(){return (new Data($this->getBinding()))->getViewContactAddressAll();}
    /**
     *  schnelle gruppierte Liste nach Städtenamen
     * @return false|array
     */
    public function getCityNameGroupByCityName(){return (new Data($this->getBinding()))->getCityNameGroupByCityName();}

    /**
     * @param TblGroup $tblGroup
     *
     * @return array|bool
     * array_keys:
     * <br/>TblPerson_Id
     * <br/>TblPerson_LastFirstName
     * <br/>TblCommon_Remark
     * <br/>Address
     * <br/>Identifier
     * <br/>Year
     * <br/>Level
     * <br/>SchoolOption
     */
    public function getPersonListByGroup(TblGroup $tblGroup){return (new Data($this->getBinding()))->getPersonListByGroup($tblGroup);}

//    public function getViewProspectCustodyAll(){return (new Data($this->getBinding()))->getViewProspectCustodyAll();}
//    public function getViewStudentAll(){return (new Data($this->getBinding()))->getViewStudentAll();}
//    public function getViewStudentAuthorizedAll(){return (new Data($this->getBinding()))->getViewStudentAuthorizedAll();}
//    public function getViewStudentCustodyAll(){return (new Data($this->getBinding()))->getViewStudentCustodyAll();}
}
