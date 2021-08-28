<?php
declare(strict_types=1);

namespace Szemul\Debugger\Test\Event;

use Exception;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Szemul\Debugger\Event\HttpRequestSentEvent;
use Szemul\Debugger\Event\HttpResponseReceivedEvent;
use PHPUnit\Framework\TestCase;
use Throwable;

class HttpResponseReceivedEventTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const RESPONSE_BODY        = 'test';
    private const RESPONSE_BODY_LENGTH = 100;
    private const RESPONSE_HEADERS     = [
        'X-TEST' => ['test'],
    ];

    private float                                                  $sutCreatedAtTimestamp;
    private float                                                  $requestEventCreatedAt;
    private ResponseInterface|MockInterface|LegacyMockInterface    $response;
    private HttpRequestSentEvent|MockInterface|LegacyMockInterface $requestEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->response              = Mockery::mock(ResponseInterface::class);
        $this->requestEvent          = Mockery::mock(HttpRequestSentEvent::class);
        $this->requestEventCreatedAt = microtime(true) - 5;

        // @phpstan-ignore-next-line
        $this->requestEvent->shouldReceive('getTimestamp')
            ->withNoArgs()
            ->andReturn($this->requestEventCreatedAt);
    }

    public function testCreateWithoutResponseAndException_shouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getEventWithoutResponse();
    }

    public function testGetBodyWithResponse_shouldReturnTheBody(): void
    {
        $event = $this->getEvent();
        $this->expectBodyRetrievedFromResponse();

        $this->assertSame(self::RESPONSE_BODY, $event->getBody());
    }

    public function testGetBodyWithNoResponse_shouldReturnEmptyString(): void
    {
        $event = $this->getEventWithoutResponse(new Exception());

        $this->assertSame('', $event->getBody());
    }

    public function testGetBodyLengthWithResponse_shouldReturnTheLength(): void
    {
        $event = $this->getEvent();
        $this->expectBodyLengthRetrievedFromResponse();

        $this->assertSame(self::RESPONSE_BODY_LENGTH, $event->getBodyLength());
    }

    public function testGetBodyLengthWithNoResponse_shouldReturnZero(): void
    {
        $event = $this->getEventWithoutResponse(new Exception());

        $this->assertSame(0, $event->getBodyLength());
    }

    public function testGetHeadersWithResponse_shouldReturnTheHeaders(): void
    {
        $event = $this->getEvent();
        $this->expectHeadersRetrievedFromResponse();

        $this->assertSame(self::RESPONSE_HEADERS, $event->getHeaders());
    }

    public function testGetHeadersWithNoResponse_shouldReturnEmptyArray(): void
    {
        $event = $this->getEventWithoutResponse(new Exception());
        $this->assertSame([], $event->getHeaders());
    }

    public function testGetRequestSentEvent(): void
    {
        $this->assertSame($this->requestEvent, $this->getEvent()->getRequestSentEvent());
    }

    public function testGetResponse(): void
    {
        $this->assertSame($this->response, $this->getEvent()->getResponse());
    }

    public function testGetRuntime(): void
    {
        $event = $this->getEvent();
        $this->assertSame($event->getTimestamp() - $this->requestEventCreatedAt, $event->getRuntime());
    }

    public function testGetStatusCodeWithResponse_shouldReturnStatusCode(): void
    {
        $event = $this->getEvent();
        $this->expectResponseStatusCodeRetrieved(200);

        $this->assertSame(200, $event->getStatusCode());
    }

    public function testGetStatusCodeWithNoResponse_shouldReturnDefaultStatusCode(): void
    {
        $event = $this->getEventWithoutResponse(new Exception());

        $this->assertSame(HttpResponseReceivedEvent::NO_STATUS_CODE, $event->getStatusCode());
    }

    public function testGetThrowableWithNoException_shouldReturnNull(): void
    {
        $event = $this->getEvent();
        $this->assertNull($event->getThrowable());
    }

    public function testGetThrowableWithException_shouldReturnTheException(): void
    {
        $exception = new Exception();

        $event = $this->getEvent($exception);
        $this->assertSame($exception, $event->getThrowable());
    }

    public function testGetTimestamp(): void
    {
        $event = $this->getEvent();
        $this->assertEqualsWithDelta($this->sutCreatedAtTimestamp, $event->getTimestamp(), 2);
    }

    public function testIsSuccessfulWithNoExceptionAnd200Status_shouldReturnTrue(): void
    {
        $this->expectResponseStatusCodeRetrieved(200, 2);
        $event = $this->getEvent();
        $this->assertTrue($event->isSuccessful());
    }

    public function testIsSuccessfulWithNoExceptionAnd404Status_shouldReturnFalse(): void
    {
        $this->expectResponseStatusCodeRetrieved(404, 2);
        $event = $this->getEvent();
        $this->assertFalse($event->isSuccessful());
    }

    public function testIsSuccessfulWithException_shouldReturnFalse(): void
    {
        $event = $this->getEvent(new Exception());
        $this->assertFalse($event->isSuccessful());
    }

    public function testToStringWithResponse_shouldReturnString(): void
    {
        $event = $this->getEvent();
        $this->expectResponseStatusCodeRetrieved(200);

        $result = (string)$event;
        $this->assertStringContainsString('HTTP request completed', $result);
        $this->assertStringContainsString('status code 200', $result);
    }

    public function testToStringWithNoResponseButException_shouldReturnString(): void
    {
        $event = $this->getEventWithoutResponse(new Exception());

        $result = (string)$event;
        $this->assertStringContainsString('HTTP request failed', $result);
    }

    private function getEvent(?Throwable $exception = null): HttpResponseReceivedEvent|MockInterface|LegacyMockInterface
    {
        $this->sutCreatedAtTimestamp = microtime(true);

        return new HttpResponseReceivedEvent(
            $this->requestEvent,
            $this->response,
            $exception,
        );
    }

    private function getEventWithoutResponse(
        ?Throwable $exception = null,
    ): HttpResponseReceivedEvent|MockInterface|LegacyMockInterface {
        $this->sutCreatedAtTimestamp = microtime(true);

        return new HttpResponseReceivedEvent(
            $this->requestEvent,
            null,
            $exception,
        );
    }

    private function expectResponseStatusCodeRetrieved(int $statusCode, int $times = 1): void
    {
        // @phpstan-ignore-next-line
        $this->response->shouldReceive('getStatusCode')
            ->times($times)
            ->withNoArgs()
            ->andReturn($statusCode);
    }

    private function expectHeadersRetrievedFromResponse(): void
    {
        // @phpstan-ignore-next-line
        $this->response->shouldReceive('getHeaders')
            ->once()
            ->withNoArgs()
            ->andReturn(self::RESPONSE_HEADERS);
    }

    private function expectBodyRetrievedFromResponse(): void
    {
        $body = Mockery::mock(StreamInterface::class);

        // @phpstan-ignore-next-line
        $body->shouldReceive('__toString')
            ->once()
            ->withNoArgs()
            ->andReturn(self::RESPONSE_BODY);

        // @phpstan-ignore-next-line
        $this->response->shouldReceive('getBody')
            ->once()
            ->withNoArgs()
            ->andReturn($body);
    }

    private function expectBodyLengthRetrievedFromResponse(): void
    {
        $body = Mockery::mock(StreamInterface::class);

        // @phpstan-ignore-next-line
        $body->shouldReceive('getSize')
            ->once()
            ->withNoArgs()
            ->andReturn(self::RESPONSE_BODY_LENGTH);

        // @phpstan-ignore-next-line
        $this->response->shouldReceive('getBody')
            ->once()
            ->withNoArgs()
            ->andReturn($body);
    }
}
