<?php
namespace SPHERE\Application\Api\Reporting\Custom;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Reporting\Custom
 */
class Custom implements IModuleInterface
{

    public static function registerModule()
    {

        $consumerAcronym = ( Consumer::useService()->getConsumerBySession() ? Consumer::useService()->getConsumerBySession()->getAcronym() : '' );

        /*
         * Chemnitz
         */
        if ($consumerAcronym === 'ESZC' || $consumerAcronym === 'DEMO') {
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Chemnitz/Common/ClassList/Download', __NAMESPACE__.'\Chemnitz\Common::downloadClassList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Chemnitz/Common/PrintClassList/Download',
                __NAMESPACE__.'\Chemnitz\Common::downloadPrintClassList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Chemnitz/Common/StaffList/Download', __NAMESPACE__.'\Chemnitz\Common::downloadStaffList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Chemnitz/Common/SchoolFeeList/Download',
                __NAMESPACE__.'\Chemnitz\Common::downloadSchoolFeeList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Chemnitz/Common/MedicList/Download', __NAMESPACE__.'\Chemnitz\Common::downloadMedicList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Chemnitz/Common/InterestedPersonList/Download',
                __NAMESPACE__.'\Chemnitz\Common::downloadInterestedPersonList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Chemnitz/Common/ParentTeacherConferenceList/Download',
                __NAMESPACE__.'\Chemnitz\Common::downloadParentTeacherConferenceList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Chemnitz/Common/ClubMemberList/Download',
                __NAMESPACE__.'\Chemnitz\Common::downloadClubMemberList'
            ));
        }

        /*
        * Hormersdorf
        */
        if ($consumerAcronym === 'FEGH' || $consumerAcronym === 'FESH') {
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Hormersdorf/Person/ClassList/Download', __NAMESPACE__.'\Hormersdorf\Person::downloadClassList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Hormersdorf/Person/StaffList/Download', __NAMESPACE__.'\Hormersdorf\Person::downloadStaffList'
            ));
        }
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

}
