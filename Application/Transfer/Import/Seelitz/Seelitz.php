<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 30.09.2016
 * Time: 09:33
 */

namespace SPHERE\Application\Transfer\Import\Seelitz;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Seelitz
 *
 * @package SPHERE\Application\Transfer\Import\Seelitz
 */
class Seelitz implements IModuleInterface
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
            __NAMESPACE__ . '/InterestedPerson', __NAMESPACE__ . '\Frontend::frontendInterestedPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Kindergarten', __NAMESPACE__ . '\Frontend::frontendKindergartenImport'
        ));

        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetStaff'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetStudent'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetInterestedPerson'), 2, 2);
        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetKindergarten'), 2, 2);
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
            'Seelitz', 'Schüler-Daten',
            new Standard('', '/Transfer/Import/Seelitz/Student', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetStaff()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Seelitz', 'Mitarbeiter/Lehrer',
            new Standard('', '/Transfer/Import/Seelitz/Staff', new Upload(), array(), 'Upload')
        );
    }


    /**
     * @return Thumbnail
     */
    public static function widgetInterestedPerson()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Seelitz', 'Interessenten',
            new Standard('', '/Transfer/Import/Seelitz/InterestedPerson', new Upload(), array(), 'Upload')
        );
    }

    /**
     * @return Thumbnail
     */
    public static function widgetKindergarten()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Seelitz', 'Kindergarten',
            new Standard('', '/Transfer/Import/Seelitz/Kindergarten', new Upload(), array(), 'Upload')
        );
    }
}
