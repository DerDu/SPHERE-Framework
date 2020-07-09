<?php
namespace SPHERE\Application\Platform\System\Cache;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\System\Cache\Frontend\Status;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Handler\AbstractHandler;
use SPHERE\System\Cache\Handler\APCuHandler;
use SPHERE\System\Cache\Handler\CookieHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Cache\Handler\OpCacheHandler;
use SPHERE\System\Cache\Handler\SmartyHandler;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

/**
 * Class Cache
 *
 * @package SPHERE\Application\System\Platform\Cache
 */
class Cache extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Cache'))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                'Cache::frontendCache'
            )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    /**
     * @param bool $Clear
     *
     * @return Stage
     */
    public function frontendCache($Clear = false)
    {

        $Stage = new Stage('Cache', 'Status');

        $Stage->addButton(new Standard('Cache löschen', '/Platform/System/Cache', null, array('Clear' => true),
            'Cache leeren'));
        $Stage->addButton(new External('phpMemcachedAdmin',
            $this->getRequest()->getPathBase().'/UnitTest/Console/phpMemcachedAdmin-1.2.2'));

        $CacheStack = array(
            'Cookie'    => $this->getCache(new CookieHandler()),
            'Memcached' => $this->getCache(new MemcachedHandler()),
            'APCu'      => $this->getCache(new APCuHandler()),
            'Memory'    => $this->getCache(new MemoryHandler()),
            'OpCache'   => $this->getCache(new OpCacheHandler()),
            'Twig'      => $this->getCache(new TwigHandler()),
            'Smarty'    => $this->getCache(new SmartyHandler()),
        );

        if ($Clear) {
            /** @var AbstractHandler $Cache */
            foreach ($CacheStack as $Cache) {
                $Cache->clearCache();
            }
        }

        $CacheStatus = array();
        /** @var AbstractHandler $Cache */
        foreach ($CacheStack as $Name => $Cache) {
            if($Name === 'Twig'){
                $CacheStatus[] = new LayoutGroup(new LayoutRow(
                    new LayoutColumn(new Title(new Success(new Enable()).' '.$Name,
                    new Danger('Deactivated summary for getStatus() '.substr(strrchr(get_class($Cache), '\\'), 1).''))
                    )));
                continue;
            }
            $CacheStatus[] = new LayoutGroup(new LayoutRow(
                new LayoutColumn(new Status(
                    $Cache->getStatus()
                ))
            ), ( false === strpos(get_class($Cache), $Name.'Handler')
                ? new Title(new Danger(new Disable()).' '.$Name,
                    new Danger('Not available (Fallback: '.substr(strrchr(get_class($Cache), '\\'), 1).')'))
                : new Title(new Success(new Enable()).' '.$Name, new Success('Active'))
            ));
        }

        $Stage->setContent(
            new Layout(
                $CacheStatus
            )
        );
        return $Stage;
    }


}
