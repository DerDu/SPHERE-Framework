<?php

namespace SPHERE\Application\Api\Reporting\SerialLetter;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Response;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Reporting\SerialLetter\SerialLetter as SerialLetterApp;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Success;
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
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Exchange', __NAMESPACE__.'\SerialLetter::executeSerialPerson'
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

        $tblSerialLetter = SerialLetterApp::useService()->getSerialLetterById($Id);
        if ($tblSerialLetter) {
            $fileLocation = SerialLetterApp::useService()
                ->createSerialLetterExcel($tblSerialLetter);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Adressen-Serienbrief ".$tblSerialLetter->getName()." ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $Direction
     * @param null $Data
     * @param null $Additional
     *
     * @return Response
     */
    public function executeSerialPerson($Direction = null, $Data = null, $Additional = null)
    {

        if ($Data && $Direction) {
            if (!isset( $Data['Id'] ) || !isset( $Data['PersonId'] )) {
                return ( new Response() )->addError('Fehler!',
                    new HazardSign().' Die Zuweisung der Person konnte nicht aktualisiert werden.', 0);
            }
            $Id = $Data['Id'];
            $PersonId = $Data['PersonId'];

            if ($Direction['From'] == 'TableAvailable') {
                $Remove = false;
            } else {
                $Remove = true;
            }

            $tblSerialLetter = SerialLetterApp::useService()->getSerialLetterById($Id);
            if ($tblSerialLetter && null !== $PersonId && ( $tblPerson = Person::useService()->getPersonById($PersonId) )) {
                if ($Remove) {
                    // remove added Address for SerialLetter
                    SerialLetterApp::useService()->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);

                    SerialLetterApp::useService()->removeSerialPerson($tblSerialLetter, $tblPerson);
                } else {
                    SerialLetterApp::useService()->addSerialPerson($tblSerialLetter, $tblPerson);
                }
            }

            return ( new Response() )->addData(new Success().' Die Zuweisung der Person wurde erfolgreich aktualisiert.');
        }
        return ( new Response() )->addError('Fehler!',
            new HazardSign().' Die Zuweisung der Person konnte nicht aktualisiert werden.', 0);
    }
}