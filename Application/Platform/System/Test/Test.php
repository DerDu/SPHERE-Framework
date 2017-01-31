<?php
namespace SPHERE\Application\Platform\System\Test;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Test
 *
 * @package SPHERE\Application\System\Platform\Test
 */
class Test implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Frontend'), new Link\Name('Frontend-Test'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Upload'), new Link\Name('Upload-Test'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/DynamicFilter'), new Link\Name('DynamicFilter-Test'))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Frontend',
                __NAMESPACE__.'\Frontend::frontendPlatform'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Upload',
                __NAMESPACE__.'\Frontend::frontendUpload'
            )->setParameterDefault('FileUpload', false)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Upload/Delete',
                __NAMESPACE__.'\Frontend::frontendPictureDelete'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Upload/Delete/Check',
                __NAMESPACE__.'\Frontend::frontendPictureDeleteCheck'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/DynamicFilter',
                __NAMESPACE__.'\Frontend::frontendSandBox'
            )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'System', 'Test'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendProtocol()
    {

        $Stage = new Stage('Protokoll', 'Aktivit?ten');

        $ProtocolList = Protocol::useService()->getProtocolAll();
//        foreach($ProtocolList as $Key => &$Protocol)
//        {
//            $Protocol->Editor = 0;
//        }

        $Stage->setContent(
            new TableData($ProtocolList, null,
                array(
                    'Id'     => '#',
                    'Editor' => 'Editor',
                    'Origin' => 'Origin',
                    'Commit' => 'Commit'
                ),
                array(
                    "order"      => array(
                        array(0, 'desc')
                    ),
                    "columnDefs" => array(
                        array("orderable" => false, "targets" => 1),
                        array("orderable" => false, "targets" => 2),
                        array("orderable" => false, "targets" => 3)
                    )
                )
            )
        );

        return $Stage;
    }
}
