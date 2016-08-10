<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 06.07.2016
 * Time: 08:53
 */

namespace SPHERE\Application\Transfer\Import\Annaberg;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Annaberg
 *
 * @package SPHERE\Application\Transfer\Import\Annaberg
 */
class Annaberg implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Staff', __NAMESPACE__ . '\Frontend::frontendStaffImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Company', __NAMESPACE__ . '\Frontend::frontendCompanyImport'
        ));

        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetCompany'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetStudent'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetStaff'), 2, 2);
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @return Thumbnail
     */
    public static function widgetStudent()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Annaberg', 'Schüler',
            new Standard('', '/Transfer/Import/Annaberg/Student', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetStaff()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Annaberg', 'Mitarbeiter/Lehrer',
            new Standard('', '/Transfer/Import/Annaberg/Staff', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetCompany()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Annaberg', 'Schulen (Firmen)',
            new Standard('', '/Transfer/Import/Annaberg/Company', new Upload(), array(), 'Upload')
        );
    }

}