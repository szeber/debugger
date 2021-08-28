<?php
declare(strict_types=1);

namespace Szemul\Debugger\Event;

use Psr\Http\Message\RequestInterface;

class HttpRequestSentEvent implements DebugEventInterface
{
    protected float $timestamp;

    public function __construct(protected RequestInterface $request)
    {
        $this->timestamp = microtime(true);
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function __toString(): string
    {
        return 'Sending HTTP request to ' . $this->request->getUri();
    }
}
