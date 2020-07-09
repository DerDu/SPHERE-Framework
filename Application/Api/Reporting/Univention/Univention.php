<?php
namespace SPHERE\Application\Api\Reporting\Univention;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Reporting\Univention
 */
class Univention implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SchoolList/Download',
            __NAMESPACE__.'/Univention::downloadSchoolList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/User/Download',
            __NAMESPACE__.'/Univention::downloadUserList'
        ));


    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @return string
     */
    public function downloadSchoolList()
    {

        $Acronym = Account::useService()->getMandantAcronym();
        $fileLocation = \SPHERE\Application\Setting\Univention\Univention::useService()->downlaodSchoolExcel();
        if($fileLocation){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                $Acronym."_schulimport_UCS.csv")->__toString();
        }
        return false;
    }

    /**
     * @return string
     */
    public function downloadUserList()
    {

        $Acronym = Account::useService()->getMandantAcronym();
        $fileLocation = \SPHERE\Application\Setting\Univention\Univention::useService()->downlaodAccountExcel();
        if($fileLocation){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                $Acronym."_userimport_UCS.csv")->__toString();
        }
        return false;
    }

}
