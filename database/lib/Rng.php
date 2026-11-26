<?php

declare(strict_types=1);

namespace KlinikaID\Data;

final class Rng
{
    private int $counter = 0;

    public function __construct(private readonly string $seed)
    {
    }

    public function int(int $min, int $max): int
    {
        $span = $max - $min + 1;
        $h = hash('sha256', $this->seed . ':' . $this->counter++, true);
        $n = unpack('J', substr($h, 0, 8))[1] & PHP_INT_MAX;
        return $min + ($n % $span);
    }

    public function pick(array $items): mixed
    {
        return $items[$this->int(0, count($items) - 1)];
    }
}
