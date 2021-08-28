<?php
declare(strict_types=1);

namespace Szemul\Debugger\Test\Registry;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Szemul\Debugger\DebuggerInterface;
use Szemul\Debugger\Event\DebugEventInterface;
use Szemul\Debugger\Registry\DebuggerRegistry;

class DebuggerRegistryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandle(): void
    {
        $registry = new DebuggerRegistry();
        $debugger = $this->getDebugger();
        $event    = $this->getDebugEvent();

        $this->expectEventHandled($debugger, $event);

        $registry->addDebugger($debugger);

        $registry->handleEvent($event);
    }

    public function testAddAndRemove(): void
    {
        $registry  = new DebuggerRegistry();
        $debugger1 = $this->getDebugger();
        $debugger2 = $this->getDebugger();
        $event1    = $this->getDebugEvent();
        $event2    = $this->getDebugEvent();
        $event3    = $this->getDebugEvent();
        $this->expectEventHandled($debugger1, $event1);
        $this->expectEventHandled($debugger2, $event1);
        $this->expectEventHandled($debugger2, $event2);

        $registry->addDebugger($debugger1);
        $registry->addDebugger($debugger2);

        $registry->handleEvent($event1);
        $registry->removeDebugger($debugger1);

        $registry->handleEvent($event2);
        $registry->removeDebugger($debugger2);

        $registry->handleEvent($event3);
    }

    public function testRemoveWithoutAdding_shouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $registry = new DebuggerRegistry();
        $debugger = $this->getDebugger();

        $registry->removeDebugger($debugger);
    }

    private function getDebugger(): DebuggerInterface|MockInterface|LegacyMockInterface
    {
        return Mockery::mock(DebuggerInterface::class);
    }

    private function getDebugEvent(): DebugEventInterface|MockInterface|LegacyMockInterface
    {
        return Mockery::mock(DebugEventInterface::class);
    }

    private function expectEventHandled(
        DebuggerInterface|MockInterface|LegacyMockInterface $debugger,
        DebugEventInterface $event,
    ): void {
        // @phpstan-ignore-next-line
        $debugger->shouldReceive('handleEvent')
            ->with($event);
    }
}
