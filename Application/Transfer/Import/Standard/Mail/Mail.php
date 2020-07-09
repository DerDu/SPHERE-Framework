<?php

namespace SPHERE\Application\Transfer\Import\Standard\Mail;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;

/**
 * Class Mail
 * @package SPHERE\Application\Transfer\Import\Standard\Mail
 */
class Mail implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__  . '/Address', __NAMESPACE__ . '\Frontend::frontendMailImport'
        ));
    }

    /**
     * @param array $DataList
     *
     * @return array
     */
    public static function setLinks($DataList){
        $consumer = 'Standard';

        $DataList[] = array(
            'Consumer' => $consumer,
            'Name' => 'Emailadressen',
            'Option' => new Standard(
                '',
                __NAMESPACE__  . '/Address',
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