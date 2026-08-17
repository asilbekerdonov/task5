<?php

namespace App\Support\Random;

final class DeterministicRandom
{
    private int $seed;
    private int $counter = 0;

    public function __construct(int $seed)
    {
        $this->seed = $seed;
    }

    private function nextRaw(): int
    {
        $hash = crc32($this->seed . ':' . $this->counter);
        $this->counter++;
        return $hash;
    }

    public function nextInt(int $min, int $max): int
    {
        if ($min > $max) {
            throw new \InvalidArgumentException("min must be <= max");
        }

        $range = $max - $min + 1;
        
        // Проверяем что range не стал float из-за переполнения
        if (is_float($range) || $range <= 0) {
            throw new \InvalidArgumentException("Range too large or overflowed: min={$min}, max={$max}");
        }

        return $min + ($this->nextRaw() % $range);
    }

    public function nextFloat(): float
    {
        return $this->nextRaw() / 4294967296.0;
    }

    public function pick(array $items): mixed
    {
        if (empty($items)) {
            throw new \InvalidArgumentException("Cannot pick from empty array");
        }
        $index = $this->nextInt(0, count($items) - 1);
        return array_values($items)[$index];
    }

    public function pickMultiple(array $items, int $count): array
    {
        $items = array_values($items);
        if ($count > count($items)) {
            throw new \InvalidArgumentException("Cannot pick more items than available");
        }
        
        $indices = range(0, count($items) - 1);
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $pickIndex = $this->nextInt(0, count($indices) - 1);
            $result[] = $items[$indices[$pickIndex]];
            array_splice($indices, $pickIndex, 1);
        }
        return $result;
    }

    public function weightedBool(float $probability): bool
    {
        return $this->nextFloat() < $probability;
    }
}