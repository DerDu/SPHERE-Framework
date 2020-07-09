<?php

namespace SPHERE\Application\Transfer\Import\EVMS;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class EVMS
 *
 * @package SPHERE\Application\Transfer\Import\EVMS
 */
class EVMS  implements IModuleInterface
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
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList)
    {
        $consumer = 'EVMS';

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
            'Name' => '02 - Lehrer',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Staff',
                new Select()
            )
        );

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '03 - Institution',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Company',
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