<?php

namespace SPHERE\Application\Api\Setting\UserAccount;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

class AccountUserExcel implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __NAMESPACE__.'\AccountUserExcel::downloadAccountList'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param null $GroupByTime
     *
     * @return bool|string
     */
    public function downloadAccountList($GroupByTime = null)
    {

        if ($GroupByTime) {
            $tblUserAccountList = Account::useService()->getUserAccountByTime((new \DateTime($GroupByTime)));
            if ($tblUserAccountList) {
                $tblUserAccount = current($tblUserAccountList);
                $Type = 'Schüler';
                $Time = new \DateTime();
                $Time = $Time->format('d-m-Y_H-i-s');
                if ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT) {
                    $Type = 'Schüler';
                } elseif ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY) {
                    $Type = 'Sorgeberechtigte';
                }
                $result = Account::useService()->getExcelData($tblUserAccountList);
                if ($result) {

                    $fileLocation = Account::useService()->createClassListExcel($result);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Zugang-Schulsoftware-".$Type."_".$Time.".xlsx")->__toString();
                }
            }
        }

        return false;
    }

}