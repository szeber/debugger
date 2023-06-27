<?php
declare(strict_types=1);

namespace Szemul\Debugger\Event;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpResponseReceivedEvent implements DebugEventInterface
{
    public const NO_STATUS_CODE = 999;

    protected float $timestamp;
    protected float $runtime;

    public function __construct(
        protected HttpRequestSentEvent $requestSentEvent,
        protected ?ResponseInterface $response,
        protected ?Throwable $exception = null,
    ) {
        $this->timestamp = microtime(true);
        $this->runtime   = $this->timestamp - $this->requestSentEvent->getTimestamp();

        if (null === $response && null === $exception) {
            throw new InvalidArgumentException('Either the request or the exception must be set');
        }
    }

    public function getRequestSentEvent(): HttpRequestSentEvent
    {
        return $this->requestSentEvent;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getThrowable(): ?Throwable
    {
        return $this->exception;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function getStatusCode(): int
    {
        return $this->response?->getStatusCode() ?? self::NO_STATUS_CODE;
    }

    /** @return array<string,array<int,string>> */
    public function getHeaders(): array
    {
        return $this->response?->getHeaders() ?? [];
    }

    public function getBody(): string
    {
        return (string)$this->response?->getBody();
    }

    public function getBodyLength(): int
    {
        return $this->response?->getBody()?->getSize() ?? 0;
    }

    public function getRuntime(): float
    {
        return $this->runtime;
    }

    public function isSuccessful(): bool
    {
        return empty($this->exception) && $this->getStatusCode() >= 200 && $this->getStatusCode() < 400;
    }

    public function __toString(): string
    {
        if (null === $this->response) {
            return 'HTTP request failed in ' . number_format($this->getRuntime() * 1000, 2) . ' ms';
        }

        return 'HTTP request completed in ' . number_format($this->getRuntime() * 1000, 2) . ' ms with status code '
            . $this->response->getStatusCode();
    }
}
