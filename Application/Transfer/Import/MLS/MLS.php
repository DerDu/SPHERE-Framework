<?php

namespace SPHERE\Application\Transfer\Import\MLS;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class MLS
 *
 * @package SPHERE\Application\Transfer\Import\MLS
 */
class MLS  implements IModuleInterface
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
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList)
    {
        $consumer = 'MLS';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '01 - Schüler',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Student',
                new Select()
            )
        );

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '02 - Mitarbeiter',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Staff',
                new Select()
            )
        );

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '03 - Interessenten',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/InterestedPerson',
                new Select()
            )
        );

        return $DataList;
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
}