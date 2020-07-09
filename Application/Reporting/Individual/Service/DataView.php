<?php

namespace SPHERE\Application\Reporting\Individual\Service;

use Doctrine\ORM\Query\Expr\Join;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class DataView
 *
 * @package SPHERE\Application\Reporting\Individual\Service
 */
class DataView extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    /** @return false|array */
//    public function getViewEducationStudentAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewEducationStudent');}
//    public function getViewGroupAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroup');}
//    public function getViewGroupClubAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupClub');}
//    public function getViewGroupCustodyAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupCustody');}
//    public function getViewGroupProspectAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupProspect');}
//    public function getViewGroupStudentBasicAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupStudentBasic');}
//    public function getViewGroupStudentIntegrationAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupStudentIntegration');}
//    public function getViewGroupStudentSubjectAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupStudentSubject');}
//    public function getViewGroupStudentTransferAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupStudentTransfer');}
//    public function getViewGroupTeacherAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupTeacher');}
//    public function getViewPersonAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewPerson');}
    //ToDO Funktion könnte für Adressen hinzufügen/bearbeiten verwendet werden. (Key's anpassen!)
    public function getViewContactAddressAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewContactAddress');}

    /**
     *  schnelle gruppierte Liste nach Städtenamen
     * @return false|array
     */
    public function getCityNameGroupByCityName()
    {
        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();

        $queryBuilder->select('vCA.TblCity_Name')
            ->from(__NAMESPACE__ . '\Entity\ViewContactAddress', 'vCA')
            ->groupBy('vCA.TblCity_Name');

        $query = $queryBuilder->getQuery();
        $resultList = $query->getResult();
        $result = array();
        if(!empty($resultList)){
            foreach($resultList as $resultSingle){
                $result[] = $resultSingle['TblCity_Name'];
            }
        }

        return (!empty($result) ? $result : false);
    }

    /**
     * @param TblGroup $tblGroup
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
    public function getPersonListByGroup(TblGroup $tblGroup)
    {

        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();

        $SelectString = 'vP.TblPerson_Id, vP.TblPerson_LastFirstName, vP.TblCommon_Remark, vPC.TblCity_Name,
         vPC.TblCity_Code, vPC.TblCity_District, vPC.TblAddress_StreetName, vPC.TblAddress_StreetNumber';

        if($tblGroup->getMetaTable() == TblGroup::META_TABLE_STUDENT){
            $SelectString .= ', vGSB.TblStudent_Identifier';
        }
        if($tblGroup->getMetaTable() == TblGroup::META_TABLE_PROSPECT){
            $SelectString .= ', vGP.TblProspectReservation_ReservationYear, vGP.TblType_NameA, vGP.TblType_NameB,
            vGP.TblProspectReservation_ReservationDivision';
        }

        $queryBuilder->select($SelectString)
            ->from(__NAMESPACE__ . '\Entity\ViewGroup', 'vG');
        $queryBuilder->leftJoin(__NAMESPACE__ . '\Entity\ViewPersonContact', 'vPC', Join::WITH,
            'vPC.TblPerson_Id = vG.TblPerson_Id'
        );
        $queryBuilder->leftJoin(__NAMESPACE__ . '\Entity\ViewPerson', 'vP', Join::WITH,
            'vP.TblPerson_Id = vG.TblPerson_Id'
        );

        $queryBuilder->Where($queryBuilder->expr()->eq('vG.TblGroup_Id', '?1'))
            ->setParameter(1, $tblGroup->getId());

        if($tblGroup->getMetaTable() == TblGroup::META_TABLE_STUDENT){
            $queryBuilder->leftJoin(__NAMESPACE__ . '\Entity\ViewGroupStudentBasic', 'vGSB', Join::WITH,
                'vGSB.TblPerson_Id = vG.TblPerson_Id'
            );
        }

        if($tblGroup->getMetaTable() == TblGroup::META_TABLE_PROSPECT){
            $queryBuilder->leftJoin(__NAMESPACE__ . '\Entity\ViewGroupProspect', 'vGP', Join::WITH,
                'vGP.TblPerson_Id = vG.TblPerson_Id'
            );
        }

        $query = $queryBuilder->getQuery();
        $resultList = $query->getResult();
        $tblContent = array();

        if(!empty($resultList)){
            array_walk($resultList, function($resultSingle) use (&$tblContent, $tblGroup){
                $item['TblPerson_Id'] = $resultSingle['TblPerson_Id'];
                $item['TblPerson_LastFirstName'] = $resultSingle['TblPerson_LastFirstName'];
                // ESZC special
                $item['TblCommon_Remark'] = $resultSingle['TblCommon_Remark'];
//                // address
//                $item['TblCity_Code'] = $resultSingle['TblCity_Code'];
//                $item['TblCity_Name'] = $resultSingle['TblCity_Name'];
//                $item['TblCity_District'] = $resultSingle['TblCity_District'];
//                $item['TblAddress_StreetName'] = $resultSingle['TblAddress_StreetName'];
//                $item['TblAddress_StreetNumber'] = $resultSingle['TblAddress_StreetNumber'];
                // address in one column
                $item['Address'] = $resultSingle['TblCity_Code'].' '.$resultSingle['TblCity_Name'].' '.
                    ($resultSingle['TblCity_District'] ? $resultSingle['TblCity_District'].' ' : '').
                    $resultSingle['TblAddress_StreetName'].' '.$resultSingle['TblAddress_StreetNumber'];
                // Student
                $item['Identifier'] = (isset($resultSingle['TblStudent_Identifier']) ? $resultSingle['TblStudent_Identifier'] : '');
                // Prospect
                $item['Year'] = (isset($resultSingle['TblProspectReservation_ReservationYear']) ? $resultSingle['TblProspectReservation_ReservationYear'] : '');
                $item['Level'] = (isset($resultSingle['TblProspectReservation_ReservationDivision']) ? $resultSingle['TblProspectReservation_ReservationDivision'] : '');
                    // SchoolType to one string
                $item['SchoolOption'] = '';
                if(isset($resultSingle['TblType_NameA'])
                    && $resultSingle['TblType_NameA']
                    && isset($resultSingle['TblType_NameB'])
                    && $resultSingle['TblType_NameB']) {
                    $item['SchoolOption'] = $resultSingle['TblType_NameA'].', '.$resultSingle['TblType_NameB'];
                } elseif(isset($resultSingle['TblType_NameA'])
                    && $resultSingle['TblType_NameA']) {
                    $item['SchoolOption'] = $resultSingle['TblType_NameA'];
                } elseif(isset($resultSingle['TblType_NameB'])
                    && $resultSingle['TblType_NameB']) {
                    $item['SchoolOption'] = $resultSingle['TblType_NameB'];
                }

                array_push($tblContent, $item);
            });
        }

        return (!empty($tblContent) ? $tblContent : false);
    }

//    public function getViewProspectCustodyAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewProspectCustody');}
//    public function getViewStudentAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudent');}
//    public function getViewStudentAuthorizedAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentAuthorized');}
//    public function getViewStudentCustodyAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentCustody');}
}
