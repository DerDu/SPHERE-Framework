<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.06.2016
 * Time: 14:23
 */

namespace SPHERE\Application\Transfer\Import\LebensweltZwenkau;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Zwenkau
 *
 * @package SPHERE\Application\Transfer\Import\LebensweltZwenkau
 */
class Zwenkau implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Person', __NAMESPACE__ . '\Frontend::frontendPersonImport'
        ));

        Main::getDispatcher()->registerWidget('Import', array(__CLASS__, 'widgetPerson'), 2, 2);
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
    public static function widgetPerson()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Lebenswelt Zwenkau', 'Personen-Daten',
            new Standard('', '/Transfer/Import/LebensweltZwenkau/Person', new Upload(), array(), 'Upload')
        );
    }
}
