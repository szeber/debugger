<?php
declare(strict_types=1);

namespace Szemul\Debugger\Registry;

use InvalidArgumentException;
use Szemul\Debugger\DebuggerInterface;
use Szemul\Debugger\Event\DebugEventInterface;

class DebuggerRegistry implements DebuggerInterface
{
    /** @var DebuggerInterface[] */
    private array $debuggers = [];

    public function addDebugger(DebuggerInterface $debugger): void
    {
        $this->debuggers[] = $debugger;
    }

    public function removeDebugger(DebuggerInterface $debugger): void
    {
        $index = array_search($debugger, $this->debuggers, true);

        if (false === $index) {
            throw new InvalidArgumentException('Debugger not found');
        }

        unset($this->debuggers[$index]);
    }

    public function handleEvent(DebugEventInterface $event): void
    {
        foreach ($this->debuggers as $debugger) {
            $debugger->handleEvent($event);
        }
    }
}
