<?php
namespace SPHERE\Application\Api\Document\Manual;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Manual
 *
 * @package SPHERE\Application\Api\Document\Manual
 */
class Manual implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __CLASS__ . '::downloadManual'
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
     * @return string
     */
    public function downloadManual()
    {

        // Create Tmp
        $File = Storage::createFilePointer('pdf');
        $File->setFileContent( file_get_contents( __DIR__.'/Handbuch.pdf' ) );
        $File->saveFile();

        return FileSystem::getDownload(
            $File->getRealPath(),
            "Handbuch.pdf"
        )->__toString();
    }
}