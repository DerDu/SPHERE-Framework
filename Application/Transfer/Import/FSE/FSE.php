<?php

namespace SPHERE\Application\Transfer\Import\FSE;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class FSE
 *
 * @package SPHERE\Application\Transfer\Import\FSE
 */
class FSE  implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Member', __NAMESPACE__ . '\Frontend::frontendMemberImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/MemberStaff', __NAMESPACE__ . '\Frontend::frontendMemberStaffImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Company', __NAMESPACE__ . '\Frontend::frontendCompanyImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList)
    {
        $consumer = 'FSE';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '01 - Mitglieder',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Member',
                new Select()
            )
        );

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '02 - Sonstige Mitglieder',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/MemberStaff',
                new Select()
            )
        );

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '03 - Schulen',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Company',
                new Select()
            )
        );

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => '04 - Schülerdaten',
            'Option' => new Standard(
                '',
                __NAMESPACE__ . '/Student',
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