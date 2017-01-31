<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 04.01.2017
 * Time: 08:33
 */

namespace SPHERE\Application\Transfer\Import\Schulstiftung;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Schulstiftung
 *
 * @package SPHERE\Application\Transfer\Import\Schulstiftung
 */
class Schulstiftung implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));

        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetStudent'), 2, 2);

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
            'Schulstiftung', 'Schüler-Daten',
            new Standard('', '/Transfer/Import/Schulstiftung/Student', new Upload(), array(), 'Upload')
        );
    }
}