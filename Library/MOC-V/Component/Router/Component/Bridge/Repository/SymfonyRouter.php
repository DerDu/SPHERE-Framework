<?php
namespace MOC\V\Component\Router\Component\Bridge\Repository;

use MOC\V\Component\Router\Component\Bridge\Bridge;
use MOC\V\Component\Router\Component\Exception\ComponentException;
use MOC\V\Component\Router\Component\IBridgeInterface;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use MOC\V\Core\AutoLoader\AutoLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class SymfonyRouter
 *
 * @package MOC\V\Component\Router\Component\Bridge
 */
class SymfonyRouter extends Bridge implements IBridgeInterface
{

    /** @var null|RequestContext $SymfonyRequestContext */
    private $SymfonyRequestContext = null;
    /** @var null|RouteCollection $SymfonyRouteCollection */
    private $SymfonyRouteCollection = null;
    /** @var null|UrlMatcher $SymfonyUrlMatcher */
    private $SymfonyUrlMatcher = null;
    /** @var null|EventDispatcher $SymfonyEventDispatcher */
    private $SymfonyEventDispatcher = null;
    /** @var null|HttpKernel $SymfonyHttpKernel */
    private $SymfonyHttpKernel = null;

    /**
     * @param string $BaseUrl
     */
    public function __construct($BaseUrl = '')
    {

        AutoLoader::getNamespaceAutoLoader('Symfony\Component', __DIR__.'/../../../Vendor/');
        AutoLoader::getNamespaceAutoLoader('Symfony\Component', __DIR__.'/../../../../../Core/HttpKernel/Vendor/');

        $this->SymfonyRouteCollection = new RouteCollection();
        $this->SymfonyRequestContext = new RequestContext();
        $this->SymfonyRequestContext->setBaseUrl($BaseUrl);
        $this->SymfonyUrlMatcher = new UrlMatcher($this->SymfonyRouteCollection, $this->SymfonyRequestContext);

        $this->SymfonyEventDispatcher = new EventDispatcher();
        $this->SymfonyEventDispatcher->addSubscriber(new RouterListener($this->SymfonyUrlMatcher));
        $this->SymfonyHttpKernel = new HttpKernel($this->SymfonyEventDispatcher, new ControllerResolver());
    }

    /**
     * @param RouteParameter $RouteOption
     *
     * @return IBridgeInterface
     */
    public function addRoute(RouteParameter $RouteOption)
    {

        $this->SymfonyRouteCollection->add($RouteOption->getPath(),
            new Route(
                $RouteOption->getPath(),
                array_merge(
                    array('_controller' => $RouteOption->getController()),
                    $RouteOption->getParameterDefault()
                ),
                $RouteOption->getParameterPattern()
            )
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getRouteList()
    {

        return $this->SymfonyRouteCollection->getIterator()->getArrayCopy();
    }

    /**
     * @param null|string $Path
     *
     * @return string
     * @throws \Exception
     */
    public function getRoute($Path = null)
    {

        try {
            $Response = $this->SymfonyHttpKernel->handle(Request::createFromGlobals());
            return $Response->getContent();
        } catch (\Exception $E) {
            throw new ComponentException($E->getMessage(), $E->getCode(), $E);
        }
    }
}
