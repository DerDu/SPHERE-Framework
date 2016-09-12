<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 15:52
 */

namespace SPHERE\Application\Api\Document\Custom;

use SPHERE\Application\Api\Document\Custom\Lebenswelt\Lebenswelt;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Document\Custom
 */
class Custom extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        $consumerAcronym = ( Consumer::useService()->getConsumerBySession() ? Consumer::useService()->getConsumerBySession()->getAcronym() : '' );
        // Lebenswelt
        if ($consumerAcronym === 'LWSZ' || $consumerAcronym === 'DEMO') {
            Lebenswelt::registerModule();
        }
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
}