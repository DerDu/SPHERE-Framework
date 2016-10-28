<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:31
 */

namespace SPHERE\Application\Api\Document\Standard;

use SPHERE\Application\Api\Document\Creator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

/**
 * Class Standard
 *
 * @package SPHERE\Application\Api\Document\Standard
 */
class Standard extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/EnrollmentDocument/Create', __CLASS__ . '::createEnrollmentDocumentPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StudentCard/Create', __CLASS__.'::createStudentCardPdf'
        ));

//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/StudentCard/Download', __NAMESPACE__.'\Repository\StudentCardTwig::downloadStudentCard')
//        );
    }

    /**
     * @param null $PersonId
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createEnrollmentDocumentPdf($PersonId = null)
    {

        return Creator::createPdf($PersonId, __NAMESPACE__ . '\Repository\EnrollmentDocument');
    }

    /**
     * @param null $PersonId
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createStudentCardPdf($PersonId = null)
    {

        return Creator::createPdf($PersonId, __NAMESPACE__.'\Repository\StudentCard');
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
}