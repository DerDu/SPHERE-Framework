<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.05.2016
 * Time: 13:41
 */

namespace SPHERE\Application\Api\Reporting\SerialLetter;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class SerialLetter
 * @package SPHERE\Application\Api\Reporting\SerialLetter
 */
class SerialLetter implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Download', __NAMESPACE__ . '\SerialLetter::downloadSerialLetter'
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
     * @param null $Id
     *
     * @return bool|string
     */
    public function downloadSerialLetter($Id = null)
    {

        $tblSerialLetter = \SPHERE\Application\Reporting\SerialLetter\SerialLetter::useService()->getSerialLetterById($Id);
        if ($tblSerialLetter) {
            $fileLocation = \SPHERE\Application\Reporting\SerialLetter\SerialLetter::useService()
                ->createSerialLetterExcel($tblSerialLetter);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Adressen für Serienbrief " . $tblSerialLetter->getName() . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
            }
        }

        return false;
    }
}