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
                __NAMESPACE__.'/Hormersdorf/Person/ClassList/Download',
                __NAMESPACE__.'\Hormersdorf\Person::downloadClassList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Hormersdorf/Person/StaffList/Download',
                __NAMESPACE__.'\Hormersdorf\Person::downloadStaffList'
            ));
        }

        /*
         * Herrnhut
         */
        if ($consumerAcronym === 'EZGH' || $consumerAcronym === 'DEMO') {
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Herrnhut/Common/ProfileList/Download',
                __NAMESPACE__.'\Herrnhut\Common::downloadProfileList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Herrnhut/Common/SignList/Download',
                __NAMESPACE__.'\Herrnhut\Common::downloadSignList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Herrnhut/Common/LanguageList/Download',
                __NAMESPACE__.'\Herrnhut\Common::downloadLanguageList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Herrnhut/Common/ClassList/Download',
                __NAMESPACE__.'\Herrnhut\Common::downloadClassList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Herrnhut/Common/ExtendedClassList/Download',
                __NAMESPACE__.'\Herrnhut\Common::downloadExtendedClassList'
            ));
        }

        /*
         * Coswig
         */
        if ($consumerAcronym === 'EVSC' || $consumerAcronym === 'DEMO') {
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Coswig/Common/ClassList/Download',
                __NAMESPACE__.'\Coswig\Common::downloadClassList'
            ));
        }

        /*
         * Schneeberg
         */
        if ($consumerAcronym === 'ESS' || $consumerAcronym === 'DEMO') {
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Schneeberg/Person/ClassList/Download',
                __NAMESPACE__.'\Schneeberg\Person::downloadClassList'
            ));
        }

        /*
        * Radebeul
        */
        if ($consumerAcronym === 'EVSR' || $consumerAcronym === 'DEMO') {
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Radebeul/Person/ParentTeacherConferenceList/Download',
                __NAMESPACE__.'\Radebeul\Person::downloadParentTeacherConferenceList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Radebeul/Person/DenominationList/Download',
                __NAMESPACE__.'\Radebeul\Person::downloadDenominationList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Radebeul/Person/PhoneList/Download',
                __NAMESPACE__.'\Radebeul\Person::downloadPhoneList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Radebeul/Person/KindergartenList/Download',
                __NAMESPACE__.'\Radebeul\Person::downloadKindergartenList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Radebeul/Person/RegularSchoolList/Download',
                __NAMESPACE__.'\Radebeul\Person::downloadRegularSchoolList'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Radebeul/Person/DiseaseList/Download',
                __NAMESPACE__.'\Radebeul\Person::downloadDiseaseList'
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
