<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Tests\Debug;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Stopwatch\Stopwatch;

class TraceableEventDispatcherTest extends \PHPUnit_Framework_TestCase
{

    public function testStopwatchSections()
    {

        $dispatcher = new TraceableEventDispatcher(new EventDispatcher(), $stopwatch = new Stopwatch());
        $kernel = $this->getHttpKernel($dispatcher, function () {

            return new Response();
        });
        $request = Request::create('/');
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $events = $stopwatch->getSectionEvents($response->headers->get('X-Debug-Token'));
        $this->assertEquals(array(
            '__section__',
            'kernel.request',
            'kernel.controller',
            'controller',
            'kernel.response',
            'kernel.terminate',
        ), array_keys($events));
    }

    protected function getHttpKernel($dispatcher, $controller)
    {

        $resolver = $this->getMock('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');
        $resolver->expects($this->once())->method('getController')->will($this->returnValue($controller));
        $resolver->expects($this->once())->method('getArguments')->will($this->returnValue(array()));

        return new HttpKernel($dispatcher, $resolver);
    }

    public function testStopwatchCheckControllerOnRequestEvent()
    {

        $stopwatch = $this->getMockBuilder('Symfony\Component\Stopwatch\Stopwatch')
            ->setMethods(array('isStarted'))
            ->getMock();
        $stopwatch->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));

        $dispatcher = new TraceableEventDispatcher(new EventDispatcher(), $stopwatch);

        $kernel = $this->getHttpKernel($dispatcher, function () {

            return new Response();
        });
        $request = Request::create('/');
        $kernel->handle($request);
    }

    public function testStopwatchStopControllerOnRequestEvent()
    {

        $stopwatch = $this->getMockBuilder('Symfony\Component\Stopwatch\Stopwatch')
            ->setMethods(array('isStarted', 'stop', 'stopSection'))
            ->getMock();
        $stopwatch->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(true));
        $stopwatch->expects($this->once())
            ->method('stop');
        $stopwatch->expects($this->once())
            ->method('stopSection');

        $dispatcher = new TraceableEventDispatcher(new EventDispatcher(), $stopwatch);

        $kernel = $this->getHttpKernel($dispatcher, function () {

            return new Response();
        });
        $request = Request::create('/');
        $kernel->handle($request);
    }
}
