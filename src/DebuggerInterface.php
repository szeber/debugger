<?php
declare(strict_types=1);

namespace Szemul\Debugger;

use Szemul\Debugger\Event\DebugEventInterface;

interface DebuggerInterface
{
    public function handleEvent(DebugEventInterface $event): void;
}
