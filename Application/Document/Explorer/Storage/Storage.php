<?php
namespace SPHERE\Application\Document\Explorer\Storage;

use SPHERE\Application\Document\Explorer\Storage\Writer\Writer;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\System\Database\Link\Identifier;

/**
 * @deprecated
 * Class Storage
 *
 * @package SPHERE\Application\Document\Explorer\Storage
 */
class Storage implements IModuleInterface
{

    /**
     * @deprecated
     */
    public static function registerModule()
    {

    }

    /**
     * @deprecated
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Document', 'Explorer', 'Storage', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @deprecated
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    /**
     * @deprecated
     * @return Writer
     */
    public static function useWriter()
    {

        return new Writer();
    }
}
