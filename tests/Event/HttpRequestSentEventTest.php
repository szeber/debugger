<?php
declare(strict_types=1);

namespace Szemul\Debugger\Test\Event;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Szemul\Debugger\Event\HttpRequestSentEvent;
use PHPUnit\Framework\TestCase;

class HttpRequestSentEventTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const REQUEST_URI_STRING = 'https://www.example.com/test';

    private float                                              $sutCreatedAtTimestamp;
    private HttpRequestSentEvent                               $sut;
    private RequestInterface|MockInterface|LegacyMockInterface $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request               = Mockery::mock(RequestInterface::class);
        $this->sutCreatedAtTimestamp = microtime(true);
        $this->sut                   = new HttpRequestSentEvent($this->request); // @phpstan-ignore-line
    }

    public function testGetTimestamp(): void
    {
        $this->assertEqualsWithDelta($this->sutCreatedAtTimestamp, $this->sut->getTimestamp(), 2);
    }

    public function testGetRequest(): void
    {
        $this->assertSame($this->request, $this->sut->getRequest());
    }

    public function testToString(): void
    {
        $this->expectRequestUriRetrieved();
        $this->assertSame('Sending HTTP request to ' . self::REQUEST_URI_STRING, (string)$this->sut);
    }

    private function expectRequestUriRetrieved(): void
    {
        $uri = Mockery::mock(UriInterface::class);
        $uri->shouldReceive('__toString')
            ->andReturn(self::REQUEST_URI_STRING);

        $this->request->shouldReceive('getUri')
            ->andReturn($uri);
    }
}
