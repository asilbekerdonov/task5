<?php

namespace App\Domain\Movie\ValueObjects;

final class Seed
{
    private const MIN = 0;
    private const MAX = 281474976710655; // 2^48 - 1

    public readonly int $value;

    public function __construct(int $value)
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new \InvalidArgumentException(
                "Seed must be between 0 and " . self::MAX . " (48 bit), got: {$value}"
            );
        }
        $this->value = $value;
    }

    public static function random(): self
    {
        return new self(random_int(self::MIN, self::MAX));
    }
}