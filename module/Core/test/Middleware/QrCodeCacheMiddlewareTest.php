<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Core\Middleware;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Server\RequestHandlerInterface;
use Shlinkio\Shlink\Core\Middleware\QrCodeCacheMiddleware;

class QrCodeCacheMiddlewareTest extends TestCase
{
    private QrCodeCacheMiddleware $middleware;
    private Cache $cache;

    public function setUp(): void
    {
        $this->cache = new ArrayCache();
        $this->middleware = new QrCodeCacheMiddleware($this->cache);
    }

    /** @test */
    public function noCachedPathFallsBackToNextMiddleware(): void
    {
        $delegate = $this->prophesize(RequestHandlerInterface::class);
        $delegate->handle(Argument::any())->willReturn(new Response())->shouldBeCalledOnce();

        $this->middleware->process((new ServerRequest())->withUri(new Uri('/foo/bar')), $delegate->reveal());

        $this->assertTrue($this->cache->contains('/foo/bar'));
    }

    /** @test */
    public function cachedPathReturnsCacheContent(): void
    {
        $isCalled = false;
        $uri = (new Uri())->withPath('/foo');
        $this->cache->save('/foo', ['body' => 'the body', 'content-type' => 'image/png']);
        $delegate = $this->prophesize(RequestHandlerInterface::class);

        $resp = $this->middleware->process((new ServerRequest())->withUri($uri), $delegate->reveal());

        $this->assertFalse($isCalled);
        $resp->getBody()->rewind();
        $this->assertEquals('the body', $resp->getBody()->getContents());
        $this->assertEquals('image/png', $resp->getHeaderLine('Content-Type'));
        $delegate->handle(Argument::any())->shouldHaveBeenCalledTimes(0);
    }
}
